<?php

/**
 * Controlls all comments.
 * Supports comments fetch, delete and post functions, as well as comments watch state triggering.
 *
 * Was written to be used with default comments.php, comments.tpl.php, comments_delete.tpl.php and comments_report.tpl.php
 *
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 */
class Comments {

    /**
     * @var int $pages count of pages
     */
    public $pages = 0;

    /**
     * @var int $count count of comments on current page
     */
    public $count = 0;

    /**
     * @var array $data array with comments data
     */
    public $data = array();

    /**
     * @var string $item_table table with commented stuff
     */
    private $item_table;

    /**
     * @var int|string $item_id ID of commented stuff
     */
    public $item_id;

    /**
     * @var string|boolean $comment either comment text or false if wrong comment ID specified
     */
    public $comment;

    /**
     * @var int|string $comment_id comment ID
     */
    public $comment_id;

    /**
     * @var int|string $page current page
     */
    private $page;

    /**
     * @var string $item_type item type (ex.: 'theme')
     */
    public $item_type;

    /**
     * @var string $item_id_fiend name of $comments_table field which contains item id
     */
    public $item_id_field;

    /**
     * @var string $query_string query string
     */
    public $query_string;

    /**
     * item real owner id
     * @var boolean|int $item_owner_id
     */
    private $item_owner_id = false;

    /**
     *
     * @var string|null original file relative path
     */
    public $path;

    /**
     * commented item thumbnail returned by Images::thumbnail()
     * @var string|null $thumbnail
     */
    public $thumbnail;

    /**
     * thumbnail width
     * @var int|string $thumbnail_width
     */
    public $thumbnail_width;

    /**
     * New comments watch status.
     * 1 - comments are watched,
     * 0 - comments are not watched,
     * false - user is not authorized
     * @var boolean|int $watch
     */
    public $watch = false;

    /**
     * comments edit permission for current user
     * @var boolean|int $editable
     */
    public $editable;

    /**
     * comments report permission/state for current user
     * @var boolean|int $reportable
     * @deprecated
     */
    public $reportable = 0;

    /**
     * checks $item_type, checks if commented item exists, sets common variables, deletes/posts comment if necessary
     * @param string $item_type item type (artwork, photo, screenshot, theme)
     */
    public function __construct($item_type) {
        $this->item_type = $item_type;
        if (isset($_REQUEST[$this->item_type . '_id'])) {
            $this->item_id = $_REQUEST[$this->item_type . '_id'];
            switch ($this->item_type) {
                case 'artwork':
                    $this->item_owner_id = Artworks::get_owner_id($this->item_id);
                    if ($this->item_owner_id === false) {
                        break;
                    }
                    $this->item_id_field = 'artworkID';
                    $this->item_table = 'artworks';
                    break;
                case 'photo':
                    $this->item_owner_id = Album::get_photo_owner_id($this->item_id);
                    if ($this->item_owner_id === false) {
                        break;
                    }
                    $this->item_id_field = 'photoID';
                    $this->item_table = 'album_photos';
                    $sql = $GLOBALS['db']
                            ->query('SELECT `album_albums`.`id` as `album_id`,
                                `album_albums`.`title` as `album_title`,
                                `album_albums`.`userID` as `owner_id`
                                FROM `album_albums`,
                                `album_photos`
                                WHERE `album_albums`.`id` = `album_photos`.`albumID`
                                AND `album_photos`.`id` = \'' . $this->item_id . '\'')
                            ->fetch_assoc();
                    foreach ($sql as $name => $value) {
                        $this->$name = $value;
                    }
                    break;
                case 'screenshot':
                    $this->item_owner_id = Screenshots::get_owner_id($this->item_id);
                    if ($this->item_owner_id === false) {
                        break;
                    }
                    $this->item_id_field = 'screenshotID';
                    $this->item_table = 'screenshots';
                    break;
                case 'theme':
                    $this->item_owner_id = Themes::get_owner_id($this->item_id);
                    if ($this->item_owner_id === false) {
                        break;
                    }
                    $this->item_id_field = 'themeID';
                    $this->item_table = 'themes';
                    break;
                default:
                    log_error('invalid \'item_type\' parameter');
                    Document::reload('/error_handler.php?500');
                    return;
            }
        }
        if ($this->item_owner_id === false) {
            Document::reload_msg(_('Invalid request.'),
                    dirname($_SERVER['SCRIPT_NAME']) . '/./');
            return;
        }
        $this->page = isset($_REQUEST['page']) ? urlencode($this->page) : 1;
        $this->set_query_string();
        if ($_SESSION['user_id']) {
            if (isset($_POST['delete']) && ctype_digit($_POST['delete']) && $this->delete_check_permissions('post')) {
                $this->delete($_POST['delete']);
                Document::reload_msg(_('Comment deleted.'),
                        $_SERVER['SCRIPT_NAME'] . $this->query_string);
            } elseif (isset($_POST['report']) && ctype_digit($_POST['report'])) {
                $this->report();
            }
        }
        if (isset($_POST['comment'][0]) && $_SESSION['user_id']) {
            $this->post();
        }
        if (isset($_GET['watch'])) {
            $this->watch($_GET['watch']);
        }
    }

    /**
     * sets $query_string
     * @return boolean true
     */
    private function set_query_string() {
        $this->query_string = '?' . $this->item_type . '_id=' . $this->item_id
                . '&page=' . $this->page;
        return true;
    }

    /**
     * fetches comments
     * @param int|string $limit count of comments per page
     */
    public function fetch($limit = 15) {
        switch ($this->item_type) {
            case 'artwork':
                $thumbnail = array('CONCAT_WS(\'.\', `hash`, `extension`)', ARTWORK_WIDTH,
                    ARTWORK_QUALITY);
                $this->editable = Perms::get(Perms::ARTWORKS_MOD);
                break;
            case 'photo':
                $thumbnail = array('CONCAT_WS(\'.\', `hash`, `extension`)', PHOTO_MEDIUM_WIDTH,
                    PHOTO_MEDIUM_QUALITY);
                $this->editable = Perms::get(Perms::ALBUM_MOD);
                break;
            case 'screenshot':
                $thumbnail = array('CONCAT_WS(\'.\', `hash`, `extension`)', SCREENSHOT_WIDTH,
                    SCREENSHOT_QUALITY);
                $this->editable = Perms::get(Perms::SCREENSHOTS_MOD);
                break;
            case 'theme':
                $thumbnail = array('`screenshot`', THEME_WIDTH, THEME_QUALITY);
                $this->editable = Perms::get(Perms::THEMES_MOD);
                break;
        }
        $this->pages = $GLOBALS['db']->query('
                            SELECT COUNT(*)
                            FROM `comments`
                            WHERE `item_type` = \'' . $this->item_type . '\'
                            AND `item_id` = \'' . $this->item_id . '\''
        );
        $this->pages = $this->pages->fetch_row();
        $this->pages = ceil($this->pages[0] / $limit);
        $sql = 'SELECT ' . $thumbnail[0];
        $sql = $GLOBALS['db']
                ->query($sql . ' FROM `' . $this->item_table
                        . '` WHERE `id` = \'' . $this->item_id . '\' LIMIT 1')
                ->fetch_row();
        $this->page = Document::s_get_page($this->pages);
        if ($sql[0] !== null) {
            $thumbnail[0] = $sql[0];
            $this->set_query_string();
            $this->thumbnail_width = $thumbnail[1];
            $this->path = '/' . $this->item_type . 's/'
                    . $thumbnail[0];
            $this->thumbnail = Images::thumbnail(
                            $_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . $this->path,
                            CACHE_DIR
                            . '/' . $this->item_type . 's', $thumbnail[1],
                            $thumbnail[2]);
        }
        $this->set_query_string();
        $sql = $GLOBALS['db']->query('SELECT
                `id`,
                `user_id`,
                `comment`,
                `timestamp`
                FROM `comments`
                WHERE `item_type` = \'' . $this->item_type . '\'
                AND `item_id` = \'' . $this->item_id . '\'
                ORDER BY `timestamp` ASC
                LIMIT ' . (($this->page - 1) * $limit) . ', ' . $limit
        );

        //if ($_SESSION['user_id']) {
        //    $this->reportable = 1;
        //}

        while ($row = $sql->fetch_assoc()) {
            $this->data[] = $row;
            if (!$this->editable) {
                if ($this->page == 1 && $this->count == 0    // first comment on first page (last posted)
                        && $this->data[0]['user_id'] == $_SESSION['user_id'] && $this->data[0]['timestamp']
                        > $_SERVER['REQUEST_TIME'] - 300) {
                    $this->data[0]['editable'] = 1;
                } else {
                    $this->data[$this->count]['editable'] = 0;
                }
            }

            /*
              if ($this->reportable) {
              list($this->data[$this->count]['reported']) = $GLOBALS['db']
              ->query('SELECT COUNT(*) FROM `comments_reports`'
              . ' WHERE `user_id` = ' . $_SESSION['user_id']
              . ' AND `comment_id` = ' . $this->data[$this->count]['id'])->fetch_row();
              }
             * 
             */

            ++$this->count;
        }

        if ($_SESSION['user_id']) {
            $sql = $GLOBALS['db']
                    ->query('SELECT COUNT(*)
                        FROM `comments_watch`
                        WHERE `user_id` = \'' . $_SESSION['user_id'] . '\'
                        AND `target_type` = \'' . $this->item_type . '\'
                        AND `target_id` = \'' . $this->item_id . '\'')
                    ->fetch_row();
            $this->watch = ($sql[0] == 0) ? 0 : 1;
        }
    }

    /**
     * deletes comment
     * @param int|string either comment ID or item type and item ID, all params must be safe to use in mysql query
     */
    public static function delete() {
        $args = func_get_args();
        if (isset($args[1])) {
            $GLOBALS['db']->query('DELETE FROM `comments` WHERE `item_type` = \'' . $args[0] . '\' AND `item_id` = ' . $args[1]);
        } else {
            $GLOBALS['db']->query('DELETE FROM `comments` WHERE `id` = ' . $args[0]);
        }
    }

    /**
     * checks if comment exists and returns its text
     * @param int|string $comment_id comment ID
     * @return boolean|string false on failture, comment text on success
     */
    private function get_comment_text($comment_id) {
        $sql = $GLOBALS['db']->query('
            SELECT `comment`
            FROM `comments`
            WHERE `id` = \'' . $comment_id . '\'
            LIMIT 1');
        $sql = $sql->fetch_row();
        if ($sql === null) {
            return false;
        }
        return $sql[0];
    }

    /**
     * checks if user has permission to delete comment
     * @param string $request_method method used to send data, can be 'get' or 'post'
     * @return boolean true if permissions check pass, otherwise refreshes page and outputs error message
     */
    public function delete_check_permissions($request_method) {
        switch ($request_method) {
            case 'get':
                $delete = $_GET['delete'];
                break;
            case 'post':
                $delete = $_POST['delete'];
                break;
            default:
                log_error('invalid \'request_method\' parameter');
                Document::reload('/error_handler.php?500');
                return;
        }
        $editable = 0;
        switch ($this->item_type) {
            case 'artwork':
                if (Perms::get(Perms::ARTWORKS_MOD)) {
                    $editable = 1;
                }
                break;
            case 'photo':
                if (Perms::get(Perms::ALBUM_MOD)) {
                    $editable = 1;
                }
                break;
            case 'screenshot':
                if (Perms::get(Perms::SCREENSHOTS_MOD)) {
                    $editable = 1;
                }
                break;
            case 'theme':
                if (Perms::get(Perms::THEMES_MOD)) {
                    $editable = 1;
                }
                break;
        }
        if (!$editable) {
            $sql = $GLOBALS['db']->query('SELECT `id`,
                        `user_id`,
                        `timestamp`
                        FROM `comments`
                        WHERE `item_type` = \'' . $this->item_type . '\'
                        AND `item_id` = \'' . $this->item_id . '\'
                        ORDER BY `timestamp` DESC
                        LIMIT 1'
                    )->fetch_row();
            if ($sql === null || $sql[0] != $delete || $sql[1] != $_SESSION['user_id']
                    || $sql[2] < $_SERVER['REQUEST_TIME'] - 600) {
                Document::reload_msg(_('You don\'t have permission to delete this comment.'),
                        $_SERVER['SCRIPT_NAME'] . $this->query_string);
            }
        }
        return true;
    }

    /**
     * checks if comment is empty, checks captcha, checks if comment had been already posted, posts new comment
     * @return boolean true or false
     */
    private function post() {
        $this->comment = trim($_POST['comment']);
        if (!isset($this->comment[0])) {
            return false;
        }
        $sql = $GLOBALS['db']->query('
                    SELECT `user_id`,
                    `comment`
                    FROM `comments`
                    WHERE `item_type` = \'' . $this->item_type . '\'
                    AND `item_id` = \'' . $this->item_id . '\'
                    ORDER BY `timestamp` DESC
                    LIMIT 1'
                )->fetch_row();
        if ($sql !== null && $sql[0] == $_SESSION['user_id'] && $sql[1] == $this->comment) {
            Document::reload_msg(_('This comment had been already posted.'),
                    $_SERVER['SCRIPT_NAME'] . $this->query_string);
            return false;
        }
        $GLOBALS['db']->query('INSERT INTO `comments`
            (
                `item_type`,
                `user_id`,
                `item_id`,
                `comment`,
                `timestamp`
            ) VALUES (
                \'' . $this->item_type . '\',
                \'' . $_SESSION['user_id'] . '\',
                \'' . $this->item_id . '\',
                \'' . $GLOBALS['db']->real_escape_string($this->comment) . '\',
                \'' . $_SERVER['REQUEST_TIME'] . '\'
            )'
        );
        Notifications::create($this->item_type . 'Comment', $this->item_id,
                $this->item_owner_id);
        $this->watch(1, true);
        $this->page = 1;
        Document::reload_msg(_('Your comment has been posted.'),
                $_SERVER['SCRIPT_NAME'] . $this->query_string);
        return true;
    }

    /**
     * Changes comments watch status.
     * @param int|string $watch 1 to watch for new comments, 0 to stop watching
     * @param boolean $no_redir if set to true function does not redirect with message, default is fault. Only works if $watch is set to 1.
     * @return boolean true
     */
    public function watch($watch = 1, $no_redir = false) {
        if ($watch == 1) {
            $sql = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `comments_watch`
                WHERE `user_id` = \'' . $_SESSION['user_id'] . '\'
                AND `target_type` = \'' . $this->item_type . '\'
                AND `target_id` = \'' . $this->item_id . '\'')->fetch_row();
            if ($sql[0] == 0) {
                $GLOBALS['db']->query('INSERT INTO `comments_watch` (
                        `user_id`,
                        `target_type`,
                        `target_id`
                    ) VALUES (
                        \'' . $_SESSION['user_id'] . '\',
                        \'' . $this->item_type . '\',
                        \'' . $this->item_id . '\'
                    )');
            }
            if (!$no_redir) {
                Document::msg(_('You will be notified on new comments.'));
            }
        } else {
            $sql = $GLOBALS['db']
                    ->query('SELECT `id`
                        FROM `notifications`
                        WHERE `user_id` = \'' . $_SESSION['user_id'] . '\'
                        AND `type` = \'' . $this->item_type . 'Comment\'
                        AND `target_id` = \'' . $this->item_id . '\'
                        AND viewed = \'0\'')
                    ->fetch_row();
            if ($sql !== null) {
                $GLOBALS['db']
                        ->query('DELETE FROM `notifications_updates`
                            WHERE `notification_id` = ' . $sql[0] . ' LIMIT 1');
                $GLOBALS['db']
                        ->query('DELETE FROM `notifications`
                            WHERE `id` = ' . $sql[0] . ' LIMIT 1');
            }
            $GLOBALS['db']->query('DELETE FROM `comments_watch`
                WHERE `user_id` = \'' . $_SESSION['user_id'] . '\'
                AND `target_type` = \'' . $this->item_type . '\'
                AND `target_id` = \'' . $this->item_id . '\'
                LIMIT 1');
            Document::reload_msg(_('You will no longer be notified on new comments.'),
                    $_SERVER['SCRIPT_NAME'] . $this->query_string);
        }
        return true;
    }

    /**
     * @todo finish and add to changelog
     * @deprecated
     */
    public function report() {
        $GLOBALS['db']->query('INSERT INTO `comments_reports`'
                . ' (`comment_id`, `user_id`, `timestamp`) VALUES'
                . ' (' . $_POST['report'] . ', ' . $_SESSION['user_id']
                . ', UNIX_TIMESTAMP())');
        Document::reload_msg(_('Comment reported.'),
                $_SERVER['SCRIPT_NAME'] . $this->query_string);
    }

    /**
     * checks if comment report confirmation form should be displayed, sets $this->comment if so
     * @return boolean true or false
     * @deprecated
     */
    public function report_form() {
        if (isset($_GET['report']) && ctype_digit($_GET['report']) && $_SESSION['user_id']) {
            $this->comment = $this->get_comment_text($_GET['report']);
            if ($this->comment !== false) {
                $this->comment_id = $_GET['report'];
                return true;
            }
        }
        return false;
    }

}
