<?php

error_reporting(-1);

define('SERVER_NAME', 'tibia-me.net');

if ($_SERVER['SERVER_NAME'] !== SERVER_NAME && $_SERVER['SERVER_NAME'] !== 'localhost'
    && $_SERVER['SERVER_NAME']
    !== '192.168.100.2') {
    header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . SERVER_NAME . $_SERVER['REQUEST_URI']);
    exit;
}

define('SITE_NAME', str_replace(['tibiame', 'tibia-me'], ['TibiaME', 'Tibia-ME'], SERVER_NAME));
define('SERVER_ADMIN', 'support@' . SERVER_NAME);

foreach ([
             'session.cookie_httponly' => 1,
             'session.gc_maxlifetime' => 1200,
             'session.cookie_lifetime' => 0,
             'memory_limit' => '320M',
             'zlib.output_compression' => 'On',
             'default_socket_timeout' => 10,
             'max_execution_time' => 30,
             'user_agent' => SITE_NAME . ' Parser',
             'display_errors' => (int)!($_SERVER['SERVER_NAME'] == SERVER_NAME),
             'session.sid_length' => 32,
             'session.sid_bits_per_character' => 5
         ] as $k => $v) {
    if (ini_set($k, $v) === false) {
        error_log('Could not set \'' . $k . '\'');
    }
}

date_default_timezone_set('Europe/Berlin');

if (!session_start()) {
    exit(_('Could not start session.'));
}

require __DIR__ . '/core.php';
spl_autoload_register('autoload', true, true);

$db = new DB;

$db->slow_query_log(0.1);

define('ARTWORK_WIDTH', 360);
define('ARTWORK_QUALITY', 80);
define('CACHE_DIR', '/cache');
define('IMAGES_DIR', '/images');
define('PHOTO_MINI_QUALITY', 80);
define('PHOTO_MINI_WIDTH', 360);
define('PHOTO_MEDIUM_QUALITY', 85);
define('PHOTO_MEDIUM_WIDTH', 360);
define('SCREENSHOT_WIDTH', 360);
define('SCREENSHOT_QUALITY', 80);
define('SERVER_TIMEZONE', round(date('Z') / 3600));
define('THEME_WIDTH', 360);
define('THEME_QUALITY', 80);
define('WORLDS', file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../worlds.dat'));
define('ARMOUR_SLOTS', array('head', 'shield', 'legs', 'torso', 'amulet', 'ring'));
define('DMG_ELEMENTS', array('hit', 'fire', 'ice', 'energy', 'soul'));
define('UPLOAD_DIR', '/uploads');
define('UPLOADS_JPEG_MAX_QUALITY', 94);
define('BBCODE_IMG_QUALITY', 80);
define('BBCODE_IMG_MAX_WIDTH', 360);
define('BBCODE_IMG_MAX_HEIGHT', 300);

/*
 * delete smilies.dat from CACHE_DIR upon changing this
 * otherwise old SMILIES_DIR will be used
 */
define('SMILIES_DIR', '/images/smilies');

// disable libxml errors (e.g. DOMDocument invalid HTML markup)
libxml_use_internal_errors(true);

mb_internal_encoding('UTF-8');

define('LOCALES', [
    'en_US' => 'English',
    'ru_RU' => 'Русский',
    'pt_PT' => 'Português',
    'pl_PL' => 'Polski'
    //'ms_MY' => 'Melayu'
]);

define('LOCALES_ALIASES', [
    'en' => 'en_US',
    'en_GB' => 'en_US',
    'pt' => 'pt_PT',
    'pt_BR' => 'pt_PT',
    'ru' => 'ru_RU',
    'pl' => 'pl_PL',
    //'ms' => 'ms_MY'
]);

if (empty($_SESSION['user_id']) && !empty($_COOKIE['token'])) {
    $_SESSION['user_id'] = Auth::verify_token();

    if ($_SESSION['user_id']) {
        Auth::set_env_vars($_SESSION['user_id']);
    }
}

if (!empty($_SESSION['user_id'])) {
    if (isset($_COOKIE['token'])) {
        Auth::touch_token();
    }
    $sql = $db->query('
        SELECT `user_profile`.`lastvisit`,
        `user_profile`.`online_time`,
        `user_profile`.`whereis`,
        `user_profile`.`rank`,
        `user_settings`.`agreement_accepted`
        FROM `user_profile`, `user_settings`
        WHERE `user_profile`.`id` = \'' . $_SESSION['user_id'] . '\'
        AND `user_settings`.`id` = `user_profile`.`id` LIMIT 1')
        ->fetch_assoc();
    Perms::set($sql['rank']);
    if (!defined('AGREEMENT_ACCEPTED')) {
        define('AGREEMENT_ACCEPTED', $sql['agreement_accepted']);
    }
    $_SESSION['user_rank'] = $sql['rank'];
    $query = 'UPDATE `user_profile` SET `lastvisit` = \'' . $_SERVER['REQUEST_TIME'] . '\'';
    if ($sql['lastvisit'] > $_SERVER['REQUEST_TIME'] - 300) {
        $query .= ', `online_time` = \'' . ($sql['online_time'] + ($_SERVER['REQUEST_TIME']
                    - $sql['lastvisit'])) . '\'';
    }
    $whereis_exclude = array(
        '/user/toolbar/icon_letters.php',
        '/scores/image.php',
        '/gamecontent/calc/api.php'
    );
    if ($sql['whereis'] != $_SERVER['SCRIPT_NAME'] && !in_array($_SERVER['SCRIPT_NAME'], $whereis_exclude)) {
        $query .= ', `whereis` = \'' . $db->real_escape_string($_SERVER['SCRIPT_NAME']) . '\'';
    }
    $query .= ' WHERE `id` = \'' . $_SESSION['user_id'] . '\'';
    $db->query($query);
} elseif (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 0;
    $_SESSION['user_nickname'] = _('Guest') . mt_rand(100000, 999999);
    $_SESSION['user_forum_posts_per_page'] = 10;
    $_SESSION['user_forum_topics_per_page'] = 10;
    $_SESSION['user_timezone'] = 0;
    $_SESSION['icons_client_type'] = 'classic';
}
define('ICONS_DIR', '/images/icons/' . $_SESSION['icons_client_type']);

if ($_SESSION['user_id'] == 0) {
    if (!empty($_COOKIE['PHPSESSID'])) {
        $sql = $db->query('SELECT COUNT(*) FROM `guests_activity`'
            . ' WHERE `session_id` = \'' . session_id() . '\'')->fetch_row()[0];
        if (!$sql) {
            if ($bot = Bot()) {
                $db->query('INSERT INTO `guests_activity` (`session_id`, `name`, `time`) VALUES (\'' . session_id() . '\', \'' . $bot . '\', \'' . $_SERVER['REQUEST_TIME'] . '\')');
            } else {
                $db->query('INSERT INTO `guests_activity` (`session_id`, `time`) VALUES (\'' . session_id() . '\',  \'' . $_SERVER['REQUEST_TIME'] . '\')');
                $_SESSION['user_nickname'] = 'Guest' . $GLOBALS['db']->insert_id;
            }
        } else {
            $db->query('UPDATE `guests_activity` SET `time` = \'' . $_SERVER['REQUEST_TIME'] . '\' WHERE `session_id` = \'' . session_id() . '\'');
        }
    } else {
        $_SESSION['user_nickname'] = 'Guest0';
    }
}
define('DEFAULT_LOCALE', 'en_US');
User::set_locale();
bindtextdomain('default', __DIR__ . '/locale');
textdomain('default');
bind_textdomain_codeset('default', 'UTF-8');

