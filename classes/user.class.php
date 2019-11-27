<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 */
class User {

    public $data = array();
    public $count = 0;
    public $total_counter;

    /**
     * Returns date depending on user timezone.
     * @param int|string $timestamp optional UNIX timestamp
     * @param string $dateformat The format of the outputted date string. See date() formatting options.
     * @return string date depending on user timezone, and format and timestamp if specified
     */
    public static function date($timestamp = null, $dateformat = 'd.m.Y H:i') {
        if ($timestamp === null) {
            $timestamp = $_SERVER['REQUEST_TIME'];
        }
        $timestamp = $timestamp - 3600 * SERVER_TIMEZONE + 3600 * $_SESSION['user_timezone'];
        return date($dateformat, $timestamp);
    }

    /**
     * Outputs html code of user icon depending on gender and vocation.
     * @param int|string $userID
     * @return boolean true if gender and vocation are specified, otherwise false
     */
    public static function gender_icon($user_id) {
        $sql = $GLOBALS['db']->query('
            SELECT `gender`,
            `vocation`
            FROM `user_profile`
            WHERE `id` = \'' . $user_id . '\'')->fetch_row();
        if (empty($sql[0]) || empty($sql[1])) {
            return false;
        } elseif ($sql[0] == 'male') {
            if ($sql[1] == 'warrior') {
                $icon = 'warrior_male.png';
            } else {
                $icon = 'wizard_male.png';
            }
        } else {
            if ($sql[1] == 'warrior') {
                $icon = 'warrior_female.png';
            } else {
                $icon = 'wizard_female.png';
            }
        }
        echo '<img src="' . IMAGES_DIR . '/icons/' . $_SESSION['icons_client_type'] . '/characters/' . $icon . '" alt=""/>';
        return true;
    }

    /**
     * @deprecated since version 2.3
     * @param int|string $user_id
     * @return string html code of online status message
     */
    public static function online_status($user_id) {
        $sql = $GLOBALS['db']->query('
            SELECT `lastvisit`,
            `whereis`
            FROM `user_profile`
            WHERE `id` = \'' . $user_id . '\'')->fetch_assoc();
        if ($_SERVER['REQUEST_TIME'] - $sql['lastvisit'] < 300 && $sql['whereis']
                != '/user/out.php') {
            return '[<span class="green small" style="font-weight: bold;">' . _('Online') . '</span>]';
        } else {
            return '[<span class="red small" style="font-weight: bold;">' . _('Offline') . '</span>]';
        }
    }

    /**
     * @deprecated
     * @see User::get_display_name()
     */
    public static function get_link($user_id, $link = 1) {
        if (!ctype_digit((string) $user_id)) {
            return false;
        }
        if (!$link || !$user_id) {
            return self::get_display_name($user_id);
        }
        $sql = $GLOBALS['db']->query('SELECT `users`.`nickname`,
                    `users`.`world`,
                    `user_profile`.`rank`,
                    ranks.prefix, ranks.color
                    FROM `users`, `user_profile`, ranks
                    WHERE `users`.`id` = `user_profile`.`id`
                    AND `users`.`id` = \'' . $user_id . '\'
                    AND ranks.id = user_profile.rank
                    LIMIT 1')->fetch_assoc();
        if (!$sql) {
            return false;
        }

        $nickname = $sql['nickname'] . '&nbsp;w' . $sql['world'];
        if (!empty($sql['prefix'])) {
            $nickname = $sql['prefix'] . '-' . $nickname;
        }

        if ($link) {
            return '<a href="/user/profile.php?u=' . $user_id . '"' . ($sql['color']
                        ? ' class="' . $sql['color'] . '"' : '') . '>' . $nickname . '</a>';
        } else {
            return $sql['color'] ? '<span class="' . $sql['color'] . '">' . $nickname . '</span>'
                        : $nickname;
        }
    }

    /**
     * @return string|boolean name and world string
     */
    public static function get_display_name($user_id) {
        if (!ctype_digit((string) $user_id)) {
            return false;
        }
        if ($user_id == 0) {
            return _('Guest');
        }
        $sql = $GLOBALS['db']->query('SELECT `users`.`nickname`,
                    `users`.`world`,
                    `user_profile`.`rank`,
                    ranks.prefix, ranks.color
                    FROM `users`, `user_profile`, ranks
                    WHERE `users`.`id` = `user_profile`.`id`
                    AND `users`.`id` = \'' . $user_id . '\'
                    AND ranks.id = user_profile.rank
                    LIMIT 1')->fetch_assoc();
        if (!$sql) {
            return false;
        }

        $nickname = $sql['nickname'] . '&nbsp;w' . $sql['world'];
        if (!empty($sql['prefix'])) {
            $nickname = $sql['prefix'] . '-' . $nickname;
        }

        return $sql['color'] ? '<span class="' . $sql['color'] . '">' . $nickname . '</span>'
                    : $nickname;
    }

    public function memberlist($online_only = 0, $search_nickname = null,
            $search_world = null) {
        $this->total_counter = $GLOBALS['db']->query('SELECT COUNT(*)
            FROM `users`')->fetch_row()[0];
        $sql = 'SELECT `users`.`id` FROM `users`';
        if ($online_only) {
            $sql = $GLOBALS['db']->query('SELECT `users`.`id`
                FROM `users`,
                `user_profile`
                WHERE `users`.`id` = `user_profile`.`id`
                AND `user_profile`.`lastvisit` >= \'' . ($_SERVER['REQUEST_TIME']
                    - 300) . '\'
                AND `user_profile`.`whereis` != \'/user/out.php\'
                ORDER BY `users`.`nickname`, `users`.`world`');
        } else {
            $sql = 'FROM `users`';
            if (!empty($search_nickname) || !empty($search_world)) {
                if (!empty($search_nickname)) {
                    $sql .= ' WHERE `nickname` LIKE \'%' . $GLOBALS['db']->real_escape_string($search_nickname) . '%\'';
                }
                if (!empty($search_world)) {
                    if (!empty($search_nickname)) {
                        $sql .= ' AND';
                    } else {
                        $sql .= ' WHERE';
                    }
                    $sql .= ' `world` = \'' . $search_world . '\'';
                }
                $this->search_results = $GLOBALS['db']
                                ->query('SELECT COUNT(*) ' . $sql)->fetch_row()[0];
                $this->pages = ceil($this->search_results / 80);
            } else {
                $this->pages = ceil($this->total_counter / 80);
            }
            $sql = $GLOBALS['db']->query('SELECT `id` ' . $sql . ' ORDER BY `nickname` ASC, `world` ASC
                LIMIT ' . ((Document::s_get_page($this->pages) - 1) * 80) . ', 80');
        }
        while ($row = $sql->fetch_assoc()) {
            $this->data[] = $row;
        }
        // for list of users online we want to display bots too
        if ($online_only) {
            $sql = $GLOBALS['db']->query('SELECT DISTINCT `name`
                FROM `guests_activity`
                WHERE `name` IS NOT NULL AND `time` >= \'' . ($_SERVER['REQUEST_TIME']
                    - 300) . '\'');
            while ($row = $sql->fetch_assoc()) {
                $this->data[] = $row;
            }
        }
        return;
    }

    /**
     * Returns user ID or false.
     * @param string $nickname nickname
     * @param string|int $world world
     * @param boolean $escape_string toggles string escaping
     * @return int|boolean user ID or false
     */
    public static function get_id($nickname, $world, $escape_string = true) {
        $nickname = preg_replace('/(.*)-(.*)/i', "\$2", $nickname);
        $sql = $GLOBALS['db']->query('SELECT `id`
            FROM `users`
            WHERE `nickname` = \'' . ($escape_string ? $GLOBALS['db']->real_escape_string($nickname)
                            : $nickname) . '\'
            AND `world` = \'' . ($escape_string ? intval($world) : $world) . '\'
            LIMIT 1')->fetch_row();
        if ($sql === null) {
            return false;
        }
        return $sql[0];
    }

    /**
     * Gets user online status.
     * @param int|string $user_id valid user ID
     * @return boolean true is user is online, otherwise false
     */
    public static function get_status($user_id) {
        $sql = $GLOBALS['db']
                        ->query('SELECT `lastvisit`,
                            `whereis`
                            FROM `user_profile`
                            WHERE `id` = \'' . $user_id . '\'
                            LIMIT 1')->fetch_assoc();
        if ($_SERVER['REQUEST_TIME'] - $sql['lastvisit'] < 300 && $sql['whereis']
                != '/user/out.php') {
            return true;
        }
        return false;
    }

    /**
     * Fetches user nickname and world.
     * @param int|string $user_id user ID
     * @return boolean|array a numerical array with user nickname and world on success, otherwise false
     */
    public static function get_data($user_id) {
        if (!ctype_digit((string) $user_id)) {
            return false;
        }
        $sql = $GLOBALS['db']->query('
                SELECT `nickname`,
                `world`
                FROM `users`
                WHERE `id` = \'' . $user_id . '\'
                LIMIT 1
            ')->fetch_row();
        return ($sql === null) ? false : $sql;
    }

    /**
     * Fetches users with specific rank.
     * @param string $rank case-sensitive rank name (see Ranks::get_id_by_name())
     */
    public function fetch_by_rank($rank) {
        $sql = $sql = $GLOBALS['db']->query('SELECT `id`
            FROM `user_profile`
            WHERE `rank` = ' . Ranks::get_id_by_name($rank));
        while ($row = $sql->fetch_row()) {
            $this->data[] = $row[0];
            ++$this->count;
        }
    }

    /**
     * @param null|string $locale in language_territory format
     */
    public static function set_locale($locale = null) {
        if ($locale !== null && array_key_exists($locale, LOCALES)) {
            $_SESSION['locale'] = $locale;
        } elseif (isset($_GET['lang']) && array_key_exists($_GET['lang'],
                        LOCALES)) {
            if (!$_SESSION['user_id']) {
                $_SESSION['locale'] = $_GET['lang'];
            }
            $locale = $_GET['lang'];
        } elseif (isset($_SESSION['locale'])) {
            $locale = $_SESSION['locale'];
        } else {
            $locale = $_SESSION['locale'] = self::get_header_locale();
        }
        if (!setlocale(LC_ALL, $locale . '.utf8', $locale . '.UTF-8', $locale)) {
            log_error('could not set locale ' . $locale);
            if ($locale != DEFAULT_LOCALE && !setlocale(LC_ALL,
                            DEFAULT_LOCALE . '.utf8', DEFAULT_LOCALE . '.UTF-8',
                            DEFAULT_LOCALE)) {
                log_error('could not set default locale ' . DEFAULT_LOCALE);
            }
        }
        if (!setlocale(LC_NUMERIC, 'C.UTF-8', 'C.utf8', 'C')) {
            log_error('could not set LC_NUMERIC locale');
        }
    }

    private static function get_header_locale(): string {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return DEFAULT_LOCALE;
        }
        $locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        if (array_key_exists($locale, LOCALES)) {
            return $locale;
        }
        if (array_key_exists($locale, LOCALES_ALIASES)) {
            return LOCALES_ALIASES[$locale];
        }
        return DEFAULT_LOCALE;
    }

    /**
     * @return string value for xml:lang based on current user locale
     */
    public static function get_xml_lang() {
        $locale = explode('_',
                (isset($_GET['lang']) && array_key_exists($_GET['lang'], LOCALES))
                    ?
                $_GET['lang'] : (isset($_SESSION['locale']) ? $_SESSION['locale']
                    : DEFAULT_LOCALE));
        return $locale[0];
    }

    public static function rank($userID) {
        $rank = $GLOBALS['db']->query('SELECT ranks.name, ranks.color FROM user_profile, ranks WHERE ranks.id = user_profile.rank AND user_profile.id = ' . $userID)->fetch_assoc();
        if ($rank['color'] !== null) {
            return '<span class="' . $rank['color'] . '">' . _($rank['name']) . '</span>';
        }
        return _($rank['name']);
    }

    /**
     * 
     * @deprecated since version 2.10.0
     */
    public static function is_opera_mini() {
        return (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'],
                        'Opera Mini') !== false || (strpos($_SERVER['HTTP_USER_AGENT'],
                        'OPR') !== false && strpos($_SERVER['HTTP_USER_AGENT'],
                        'Mobile') !== false)));
    }

    /**
     * Likes and dislikes.
     * @param string $target_type
     * @param int $target_id
     * @param int $like 1 for like, 0 for dislike, -1 for unvote
     */
    public static function like($target_type, $target_id, $like) {
        switch ($target_type) {
            case 'photo':
                if (!Album::photo_exists($target_id)) {
                    return false;
                }
                if ($like == 1) {
                    $authorID = Album::get_photo_owner_id($target_id);
                }
                break;
            case 'screenshot':
                if (!Screenshots::screenshot_exists($target_id)) {
                    return false;
                }
                if ($like == 1) {
                    $authorID = Screenshots::get_owner_id($target_id);
                }
                break;
            case 'theme':
                if (!Themes::Exists($target_id)) {
                    return false;
                }
                if ($like == 1) {
                    $authorID = Themes::AuthorID($target_id);
                }
                break;
            case 'artwork':
                if (!Artworks::artwork_exists($target_id)) {
                    return false;
                }
                if ($like == 1) {
                    $authorID = Artworks::uploader_id($target_id);
                }
                break;
            default:
                return false;
        }
        $GLOBALS['db']->query('DELETE FROM `likes` WHERE `user_id` = \'' . $_SESSION['user_id'] . '\' AND `type` = \'' . $target_type . '\' AND `target_id` = \'' . $target_id . '\'');
        if ($like == 0 || $like == 1) {
            $GLOBALS['db']->query('INSERT INTO `likes` (`user_id`, `type`, `target_id`, `like`) VALUES (\'' . $_SESSION['user_id'] . '\', \'' . $target_type . '\', \'' . $target_id . '\', \'' . $like . '\')');
            if ($like == 1) {
                Notifications::create($target_type . 'Like', $target_id,
                        $authorID);
            }
        } else {
            // removing entry from unviewed notifications
            Notifications::user_remove($target_type . 'Like', $target_id);
        }
        return true;
    }

}
