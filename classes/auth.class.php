<?php

class Auth
{

    public static function login($nickname, $world, $password, $use_token = 0)
    {
        if (!self::CheckNickname($nickname)) {
            return _('Invalid nickname.');
        }
        if (!self::check_world($world)) {
            return _('Invalid world.');
        }
        $nickname = ucfirst(strtolower($nickname));
        $sql = $GLOBALS['db']->query('SELECT `id`,
            `password`,
            `login_tries`,
            `validation_key`
            FROM `users`
            WHERE `nickname` = \'' . $GLOBALS['db']->real_escape_string($nickname) . '\'
            AND `world` = \'' . $world . '\'')->fetch_assoc();
        if ($sql === null) {
            return _('User not found.');
        }
        if ($sql['login_tries'] >= 5) {
            return _('Incorrect password was entered too many times. Please use password recovery.');
        }
        if (!password_verify($password, $sql['password'])) {
            $GLOBALS['db']->query('UPDATE `users` SET `login_tries` = \'' . ($sql['login_tries'] + 1) . '\' WHERE `id` = \'' . $sql['id'] . '\'');
            return _('Incorrect password.');
        }
        $_SESSION['user_id'] = $sql['id'];
        $GLOBALS['db']->query('DELETE FROM `guests_activity` WHERE `session_id` = \'' . session_id() . '\'');
        if ($sql['login_tries'] || $sql['validation_key']) {
            $GLOBALS['db']->query('UPDATE `users` SET `login_tries` = \'0\', `validation_key` = NULL WHERE `id` = \'' . $sql['id'] . '\'');
        }

        self::set_env_vars($_SESSION['user_id'], $nickname, $world);

        if ($use_token) {
            self::set_token();
        }

        return intval($_SESSION['user_id']);
    }

    /**
     *
     * @param string $nickname
     * @return boolean true if nickname contains a-z letters only (case insensitive) and is 2-10 symbols long, otherwise false
     */
    public static function CheckNickname($nickname)
    {
        return preg_match('/^[a-z]{2,10}$/i', $nickname);
    }

    /**
     * @param int|string $world
     * @return boolean true if $world contains digits only and represents existing world number, otherwise false
     */
    public static function check_world($world)
    {
        return (ctype_digit((string)$world) && $world >= 1 && $world <= WORLDS);
    }

    public static function register($nickname, $world, $password, $email = null, $hide_email = 1)
    {
        if (!self::CheckNickname($nickname)) {
            return _('Invalid nickname.');
        }
        if (!self::check_world($world)) {
            return _('Invalid world.');
        }
        if (!empty($email)) {
            if (isset($email[64])) {
                return _('Invalid email.');
            }
            $email = filter_var($email, FILTER_VALIDATE_EMAIL);
            if ($email === false) {
                return _('Invalid email.');
            } else {
                $email = $GLOBALS['db']->real_escape_string($email);
            }
        }

        if (!isset($password[4])) {
            return _('Password is too short.');
        }

        $nickname = $GLOBALS['db']->real_escape_string(ucfirst(strtolower($nickname)));
        $sql = $GLOBALS['db']->query('SELECT COUNT(*) FROM `users` WHERE `nickname` = \'' . $nickname . '\' AND `world` = \'' . $world . '\'');
        $sql = $sql->fetch_row();
        if ($sql[0]) {
            return _('This nickname is already in use.');
        }

        $password = password_hash($password, PASSWORD_BCRYPT);

        $GLOBALS['db']->query('INSERT INTO `users` (`nickname`, `world`, `password`, `email`) VALUES (\'' . $nickname . '\', \'' . $world . '\', \'' . $password . '\', \'' . $email . '\')');
        $id = $GLOBALS['db']->insert_id;
        $GLOBALS['db']->query('INSERT INTO `user_profile` (`id`, `joined`) VALUES (\'' . $id . '\',  \'' . $_SERVER['REQUEST_TIME'] . '\')');
        $GLOBALS['db']->query('INSERT INTO `user_settings` (`id`, `hide_email`, `agreement_accepted`) VALUES (\'' . $id . '\', \'' . $hide_email . '\', \'1\')');
        Document::reload_msg(sprintf(_('Your account has been just created, %s!'), stripslashes($nickname)), '/user/login.php?redirect=' . get_redirect(false, '/'));
        return true;
    }

    public static function RequireLogin($require_login = true)
    {
        if (!$require_login && $_SESSION['user_id']) {
            Document::reload_msg(_('This page is only for non-authorized users.'), '/');
        } elseif ($require_login && !$_SESSION['user_id']) {
            Document::reload_msg(_('You must be logged in to view this page.'), '/user/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        }
        return true;
    }

    /**
     * Checks $_REQUEST['u'] for valid user id.
     * If user id is not set or not valid, returns 1st agrument.
     * If 1st argument is not passed, returns current user id.
     * @return mixed either user id or 1st agrument
     */
    public static function get_u()
    {
        if (isset($_REQUEST['u']) && self::user_exists($_REQUEST['u'])) {
            return $_REQUEST['u'];
        } elseif (func_num_args()) {
            // func_get_arg() is here for a reason
            return func_get_arg(0);
        } else {
            return $_SESSION['user_id'];
        }
    }

    public static function user_exists($user_id)
    {
        if (!ctype_digit((string)$user_id)) {
            return false;
        }
        $sql = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `users` WHERE `id` = \'' . $user_id . '\'')->fetch_row();
        if ($sql[0]) {
            return true;
        }
        return false;
    }

    public static function set_env_vars($user_id, $nickname = null, $world = null)
    {
        if ($nickname === null || $world === null) {
            list($_SESSION['user_nickname'], $_SESSION['user_world']) = $GLOBALS['db']->query('SELECT nickname, world FROM users WHERE id = ' . $user_id)->fetch_row();
        } else {
            $_SESSION['user_nickname'] = $nickname;
            $_SESSION['user_world'] = $world;
        }
        $sql = $GLOBALS['db']->query('SELECT user_settings.timezone,'
            . ' user_settings.forum_topics_per_page,'
            . ' user_settings.forum_posts_per_page, user_settings.style,'
            . ' user_settings.locale, user_settings.icons_client_type,'
            . ' user_settings.javascript, user_settings.agreement_accepted,'
            . ' user_profile.lastvisit'
            . ' FROM user_profile, user_settings'
            . ' WHERE user_profile.id = user_settings.id'
            . ' AND user_settings.id = ' . $user_id)->fetch_assoc();
        if ($sql['lastvisit'] > $_SERVER['REQUEST_TIME'] - 300) {
            $GLOBALS['db']->query('UPDATE user_profile SET lastvisit = '
                . $_SERVER['REQUEST_TIME'] . ' WHERE id = '
                . $_SESSION['user_id']);
        }
        $_SESSION['user_timezone'] = $sql['timezone'];
        $_SESSION['user_forum_posts_per_page'] = $sql['forum_posts_per_page'];
        $_SESSION['user_forum_topics_per_page'] = $sql['forum_topics_per_page'];
        $_SESSION['icons_client_type'] = $sql['icons_client_type'];
        define('AGREEMENT_ACCEPTED', $sql['agreement_accepted']);

        User::set_locale($sql['locale']);
    }

    public static function set_token()
    {
        $token = bin2hex(openssl_random_pseudo_bytes(125));
        $browscap = Browscap::get_browser();
        $GLOBALS['db']->insert('user_tokens', [
            'user_id' => $_SESSION['user_id'],
            'token' => $token,
            'timestamp' => $_SERVER['REQUEST_TIME'],
            'created' => $_SERVER['REQUEST_TIME'],
            'browser' => $browscap->browser ?? null,
            'version' => $browscap->version ?? null,
            'platform' => $browscap->platform ?? null,
            'country_code' => $_SERVER['GEOIP_COUNTRY_CODE'] ?? null
        ]);
        // tricky hack
        $GLOBALS['db']->query('DELETE FROM user_tokens WHERE user_id = ' . $_SESSION['user_id'] . ' AND token NOT IN (SELECT token FROM (SELECT token FROM user_tokens WHERE user_id = ' . $_SESSION['user_id'] . ' ORDER BY timestamp DESC LIMIT 30) foo)');
        setcookie('token', $token, 2147483647, '/', '', false, true);
    }

    public static function verify_token()
    {
        return $GLOBALS['db']->query('SELECT user_id FROM user_tokens WHERE token = ' . $GLOBALS['db']->quote($_COOKIE['token']))->fetch_row()[0];
    }

    public static function remove_token($token)
    {
        if ($token === $_COOKIE['token']) {
            setcookie('token', 0, 0, '/', '', false, true);
        }
        $GLOBALS['db']->query('DELETE FROM user_tokens WHERE token = ' . $GLOBALS['db']->quote($token));
    }

    /**
     * Updates token last activity time and other data.
     */
    public static function touch_token()
    {
        $browscap = Browscap::get_browser();
        $GLOBALS['db']->update('user_tokens', [
            'timestamp' => $_SERVER['REQUEST_TIME'],
            'browser' => $browscap->browser ?? null,
            'version' => $browscap->version ?? null,
            'platform' => $browscap->platform ?? null,
            'country_code' => $_SERVER['GEOIP_COUNTRY_CODE'] ?? null
        ], 'WHERE user_id = ' . $_SESSION['user_id'] . ' AND token = ' . $GLOBALS['db']->quote($_COOKIE['token']));

    }

}
