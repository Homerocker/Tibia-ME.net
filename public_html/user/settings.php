<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
Auth::RequireLogin();
$timezones = array(-12, -11, -10, -9, -8, -7, -6, -5, -4.5, -4, -3.5, -3, -2, -1, 0, 1, 2, 3, 3.5, 4, 4.5, 5, 5.5, 5.75, 6, 6.5, 7, 8, 9, 9.5, 10, 11, 12, 13);
if (isset($_POST['submit'])) {
    $forum_posts_per_page = intval($_POST['forum_posts_per_page'] ?? 20);
    $forum_topics_per_page = intval($_POST['forum_topics_per_page'] ?? 15);
    $timezone = $db->real_escape_string($_POST['timezone'] ?? 0);
    if (!in_array($timezone, $timezones)) {
        $timezone = 0;
    }
    if (empty($forum_posts_per_page) || $forum_posts_per_page < 5)
        $forum_posts_per_page = 5;
    elseif ($forum_posts_per_page > 50)
        $forum_posts_per_page = 50;
    if (empty($forum_topics_per_page) || $forum_topics_per_page < 10)
        $forum_topics_per_page = 10;
    elseif ($forum_topics_per_page > 50)
        $forum_topics_per_page = 50;
    $db->query('UPDATE `user_settings`
        SET `timezone` = \'' . $timezone . '\',
        `album_allow_comments` = \'' . intval($_POST['album_allow_comments'] ?? 0) . '\',
        `album_comments_notify` = \'' . intval($_POST['album_comments_notify'] ?? 0) . '\',
        `hide_email` = \'' . intval($_POST['hide_email'] ?? 0) . '\',
        `letters_notify` = \'' . intval($_POST['letters_notify'] ?? 0) . '\',
        `locale` = \'' . $db->real_escape_string($_POST['locale'] ?? DEFAULT_LOCALE) . '\',
        `forum_posts_per_page` = \'' . $forum_posts_per_page . '\',
        `forum_topics_per_page` = \'' . $forum_topics_per_page . '\'
        WHERE `id` = \'' . $_SESSION['user_id'] . '\'');
    $_SESSION['user_timezone'] = $timezone;
    User::set_locale($_POST['locale'] ?? DEFAULT_LOCALE);
    $_SESSION['user_forum_posts_per_page'] = $forum_posts_per_page;
    $_SESSION['user_forum_topics_per_page'] = $forum_topics_per_page;
    if (isset($_POST['devices']) && is_array($_POST['devices'])) {
        $db->query('DELETE FROM user_tokens WHERE user_id = ' . $_SESSION['user_id'] . ' AND token NOT IN (' . implode(',', $db->quote($_POST['devices'])) . ')');
    } else {
        $db->query('DELETE FROM user_tokens WHERE user_id = ' . $_SESSION['user_id']);
    }
    Document::reload_msg(_('Changes saved.'), $_SERVER['PHP_SELF']);
}

$document = new Document(_('Settings'));
$devices = [];
$query = $db->query('SELECT * FROM user_tokens WHERE user_id = ' . $_SESSION['user_id']);
while ($row = $query->fetch_assoc()) {
    $devices[] = $row;
}
$document->assign(array(
    'data' => $db->query('SELECT * FROM `user_settings` WHERE `id` = \'' . $_SESSION['user_id'] . '\'')->fetch_assoc(),
    'timezones' => $timezones,
    'devices' => $devices
));
$document->display('settings');