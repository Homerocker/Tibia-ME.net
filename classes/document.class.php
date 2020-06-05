<?php

class Document extends Templates
{
    /**
     * @author Molodoy <molodoy3561@gmail.com>
     * @copyright (c) 2012, Tibia-ME.net
     */

    /**
     * @var int current page, default is 1
     */
    public $page = 1;

    /**
     * @var int count of pages available (default is 0, if no items to be displayed or variable is not used at current page)
     */
    private $pages = 0;

    /**
     * opens buffer, stores page header, user menu, notifications, banishment message, maintenance work message, etc.
     * @param string $page_title page title
     */
    public function __construct($page_title = SITE_NAME, $navi = null,
                                $show_ads = true)
    {
        ob_start();
        if (isset($_REQUEST['page']) && ctype_digit($_REQUEST['page']) && $_REQUEST['page']
            > 1) {
            $this->page = $_REQUEST['page'];
        }
        $maintenance_msg = get_maintenance_message();

        if ($_SESSION['user_id']) {
            if ($_SERVER['SCRIPT_NAME'] == '/user/letters.php' && ((isset($_GET['folder'])
                        && $_GET['folder'] == 'inbox') || empty($_SERVER['QUERY_STRING']))) {
                $inbox_unread = 0;
            } else {
                $inbox_unread = $GLOBALS['db']->query('select COUNT(*)
                    from `letters`
                    where `to` = \'' . $_SESSION['user_id'] . '\'
                    and `flag` = \'1\'
                    and `recipient_display` = \'1\'')->fetch_row()[0];
            }
            $friend_requests = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `friendlist`
                WHERE `friendID` = \'' . $_SESSION['user_id'] . '\'
                AND `accepted` = \'0\'')->fetch_row()[0];
            if ($friend_requests == 0) {
                $friends_count = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `friendlist`
                WHERE (`friendID` = \'' . $_SESSION['user_id'] . '\'
                    OR userID = \'' . $_SESSION['user_id'] . '\')
                AND `accepted` = \'1\'')->fetch_row()[0];
                $this->assign('friends_count', $friends_count);
            } else {
                $this->assign('friend_requests', $friend_requests);
            }
            $notifications = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `notifications`
                WHERE `user_id` = \'' . $_SESSION['user_id'] . '\'
                AND `viewed` = \'0\'')->fetch_row()[0];
            $this->assign(array(
                'inbox_unread' => $inbox_unread,
                'notifications' => $notifications
            ));
        }

        $users_online = $GLOBALS['db']->query('select COUNT(*) from `user_profile`'
            . ' where `lastvisit` >= ' . ($_SERVER['REQUEST_TIME'] - 300)
            . ' AND `whereis` != \'/user/out.php\'')->fetch_row()[0];
        $guests_online = $GLOBALS['db']->query('SELECT COUNT(*)
                    FROM `guests_activity`
                    WHERE `name` IS NULL
                    AND `time` >= \'' . ($_SERVER['REQUEST_TIME'] - 300) . '\'')
            ->fetch_row()[0];

        $this->assign(array(
            'page_title' => $page_title,
            'maintenance_msg' => $maintenance_msg,
            'navi' => $navi,
            'show_ads' => $show_ads,
            'total_online' => $users_online + $guests_online,
            'registered_online' => $users_online,
            'guests_online' => $guests_online
        ));
        $this->display('page_header');

        if ($maintenance_msg !== null && $_SESSION['user_id'] != 1) {
            // @todo not all maintenance messages should exit?
            $this->display('user_notification');
            exit;
        }

// toolbar and banishments
        if ($_SESSION['user_id']) {
            if (!AGREEMENT_ACCEPTED) {
                $this->display('user_notification');
                $this->display('agreement');
                exit;
            }
            $sql = $GLOBALS['db']->query('SELECT *
                FROM `banishments`
                WHERE `userID` = ' . $_SESSION['user_id'] . '
                AND (
                    (
                        `expirationTime` > ' . $_SERVER['REQUEST_TIME'] . '
                        AND `unbannedModeratorID` IS NULL
                    ) OR `viewed` = 0
                ) ORDER BY `expirationTime` DESC LIMIT 1')->fetch_assoc();
            if ($sql !== null) {
                $this->assign(array(
                    'banishment_id' => $sql['id'],
                    'banishment_exp_datetime' => User::date($sql['expirationTime']),
                    'banishment_expired' => $_SERVER['REQUEST_TIME'] > $sql['expirationTime']
                ));
                $this->display('user_notification');
                $this->display('user_banishment');
                if (!Perms::get(Perms::IGNORE_BAN) && $_SERVER['SCRIPT_NAME'] != '/user/banishments.php') {
                    exit;
                }
            }
        } else {
            $users_online = $GLOBALS['db']->
            query('SELECT COUNT(*)
                        FROM `user_profile`
                        WHERE `lastvisit` >= \'' . ($_SERVER['REQUEST_TIME'] - 300) . '\' AND `whereis` != \'/user/out.php\'')
                ->fetch_row()[0];
            $guests_online = $GLOBALS['db']->
            query('SELECT COUNT(*)
                        FROM `guests_activity`
                        WHERE `name` IS NULL
                        AND `time` >= \'' . ($_SERVER['REQUEST_TIME'] - 300) . '\'')
                ->fetch_row()[0];
            $this->assign(array(
                'total_online' => $users_online + $guests_online,
                'registered_online' => $users_online,
                'guests_online' => $guests_online,
            ));
        }
        $this->display('user_notification');
    }

    /**
     * outputs data from buffer and closes it, closes database connection, displays page footer
     */
    public function __destruct()
    {
        $this->display('page_footer');
    }

    /**
     * returns current page number, number may be invalid if count of pages is not specified
     * @param int $pages optional count of pages
     * @return int page number
     */
    public static function s_get_page($pages = null)
    {
        if (isset($_REQUEST['page'])) {
            if ($_REQUEST['page'] === 'last') {
                if (isset($pages) && $pages > 0) {
                    return $pages;
                }
            } elseif (is_int_string($_REQUEST['page']) && $_REQUEST['page'] >= 1
                && (empty($pages) || $_REQUEST['page'] <= $pages)) {
                return (int)$_REQUEST['page'];
            }
        }
        return 1;
    }

    /**
     * @deprecated since version 2.10.1, see Document::slice_data()
     * uses static function Document::s_get_page() to set current page and pages count
     * default limit set to 1 for backward compatibility
     * @param int $count total items to display
     * @param $limit count of items to display per page
     */
    public function get_page($pages)
    {
        $this->pages = (int)$pages;
        $this->page = (int)$this->s_get_page($this->pages);
    }

    /**
     * displays links to navigate between pages, can send additional variables in query string
     * @param string|array $params optional either variable name or an array with variables names and values
     * @param string $params2 optional variable value, used only if $params is not an array
     * @return boolean false if there's no more than 1 page, otherwise see Document::display()
     */
    public function pages($params = null, $params2 = null)
    {
        if ($this->pages <= 1) {
            return false;
        }
        if ($params !== null) {
            if (is_array($params)) {
                foreach ($params as $name => $value) {
                    if (isset($query)) {
                        $query .= '&amp;';
                    } else {
                        $query = '?';
                    }
                    $query .= urlencode($name) . '=' . urlencode($value);
                }
                if ($this->pages > 5 || ($this->pages === 5 && $this->pages !== 3)
                    || ($this->pages === 4 && ($this->page === 1 || $this->page
                            === $this->pages))) {
                    $this->assign('params', $params);
                }
            } elseif ($params2 !== null) {
                $query = '?' . urlencode($params) . '=' . urlencode($params2);
                if ($this->pages > 5 || ($this->pages === 5 && $this->pages !== 3)
                    || ($this->pages === 4 && ($this->page === 1 || $this->page
                            === $this->pages))) {
                    $this->assign('params', array($params => $params2));
                }
            }
        }
        if (isset($query)) {
            $query .= '&amp;page=';
        } else {
            $query = '?page=';
        }
        $this->assign(array(
            'query' => $query,
            'page' => $this->page,
            'pages' => $this->pages,
        ));
        return $this->display('pages_bottom');
    }

    /**
     * reloads page
     * @param string $url optional url to be loaded
     */
    public static function reload($url = null)
    {
        if ($url === null) {
            $url = $_SERVER['SCRIPT_NAME'];
        }
        /*
          if (!empty($_REQUEST['redirect'])) {
          $query = parse_str(parse_url($url, PHP_URL_QUERY), $query);
          $query['redirect'] = $_REQUEST['redirect'];
          $url = array_shift(explode('?', $url)) . '?' . http_build_query($query);
          }
         *
         */
        header('Location: ' . $url);
        exit;
    }

    /**
     * reloads page and displays notification
     * @param string|array $msg message to be displayed
     * @param string $url optional url to be loaded
     */
    public static function reload_msg($msg, $url = null)
    {
        if ($url === null) {
            $url = $_SERVER['SCRIPT_NAME'];
        }
        self::msg($msg);
        header('Location: ' . $url);
        exit;
    }

    public static function msg($msg)
    {
        if (is_array($msg)) {
            return array_walk($msg, array('self', __FUNCTION__));
        }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['notification']) || array_search($msg,
                $_SESSION['notification']) === false) {
            $_SESSION['notification'][] = $msg;
        }
        return true;
    }

    /**
     * Displays error message template.
     * @param string $msg error message
     */
    public function error($msg, $exit = false)
    {
        $this->assign('msg', $msg);
        $this->display('error');
    }

    /**
     * Gets portion of data to display on current page.
     * Also updates pages counters.
     * @param array $data full data
     * @param int $limit number of items to display per page
     * @return array sliced data
     */
    public function slice_data($data, $limit)
    {
        $this->pages = (int)ceil(count($data) / $limit);
        $this->page = (int)$this->s_get_page($this->pages);
        return array_slice($data, ($this->page - 1) * $limit, $limit);
    }

}
