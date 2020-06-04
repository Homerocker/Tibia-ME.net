<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (isset($_GET['httperror'])) {
    switch ($_GET['httperror']) {
        case 403:
            $url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($url_path === $_SERVER['PHP_SELF']) {
                break;
            }
            Document::reload_msg(sprintf(_('You don\'t have permission to access <i>%s</i> on this server.'), $url_path));
            break;
        case 404:
            $url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($url_path === $_SERVER['PHP_SELF']) {
                break;
            }
            Document::reload_msg(sprintf(_('File <i>%s</i> not found on this server.'), $url_path));
            break;
    }
}

$document = new Document(SITE_NAME);
$sql = $db->query('SELECT *
    FROM `forumTopics`
    WHERE `forumID` = \'6\'
    ORDER BY `time` DESC LIMIT 1')->fetch_assoc();
if ($sql) {
    if ($sql['edit_timestamp'] > $sql['time']) {
        $sql['time'] = $sql['edit_timestamp'];
    }
    if ($sql['time'] >= $_SERVER['REQUEST_TIME'] - 2592000) {
        // move $news_comments to the main query ^^
        $news_comments = $db->query('SELECT COUNT(*)
            FROM `forumPosts`
            WHERE `topicID` = \'' . $sql['id'] . '\'');
        $news_comments = $news_comments->fetch_row();
        $document->assign(array(
            'news' => array(
                'id' => $sql['id'],
                'title' => Forum::MessageHandler($sql['title']),
                'date' => date('d.m.Y', $sql['time']),
                'comments' => $news_comments[0]
            )
        ));
    }
}
$screenshots_total = $db->query('SELECT COUNT(*) FROM `screenshots`');
$screenshots_total = $screenshots_total->fetch_row();
$screenshots_new = $db->query('SELECT COUNT(*)
    FROM `screenshots`
    WHERE `timestamp` >= \'' . strtotime(Scores::date()) . '\'');
$screenshots_new = $screenshots_new->fetch_row();
$album_total = $db->query('SELECT COUNT(*) FROM `album_photos`');
$album_total = $album_total->fetch_row();
$album_new = $db->query('SELECT COUNT(*)
    FROM `album_photos`
    WHERE `timestamp` >= \'' . strtotime(Scores::date()) . '\'');
$album_new = $album_new->fetch_row();
$themes_total = $db->query('SELECT COUNT(*)
    FROM `themes`
    WHERE `status` = \'checked\' OR `status` = \'tested\'');
$themes_total = $themes_total->fetch_row();
$themes_new = $db->query('SELECT COUNT(*)
    FROM `themes`
    WHERE (
        `status` = \'checked\'
        OR `status` = \'tested\'
    ) AND `moderationTime` >= \'' . strtotime(Scores::date()) . '\'');
$themes_new = $themes_new->fetch_row();
$artworks_total = $db->query('SELECT COUNT(*) FROM `artworks`');
$artworks_total = $artworks_total->fetch_row();
$artworks_new = $db->query('SELECT COUNT(*)
    FROM `artworks`
    WHERE `timestamp` >= \'' . strtotime(Scores::date()) . '\'');
$artworks_new = $artworks_new->fetch_row();
if ($_SESSION['user_id']) {
    $friends_online = $db->query('SELECT COUNT(*)
        FROM `friendlist`, `user_profile`
        WHERE (
            `friendlist`.`userID` = \'' . $_SESSION['user_id'] . '\'
            AND `user_profile`.`id` = `friendlist`.`friendID`
            AND `user_profile`.`lastvisit` >= ' . ($_SERVER['REQUEST_TIME'] - 300) . '
        ) OR (
            `friendlist`.`friendID` = \'' . $_SESSION['user_id'] . '\'
            AND `user_profile`.`id` = `friendlist`.`userID`
            AND `user_profile`.`lastvisit` >= ' . ($_SERVER['REQUEST_TIME'] - 300) . '
        ) AND `accepted` = \'1\'');
    $friends_online = $friends_online->fetch_row();
    $char_id = Scores::get_char_id($_SESSION['user_nickname'], $_SESSION['user_world']);
    if ($char_id) {
        $guild_name = $db->query('SELECT guild FROM `scores_characters_guilds`'
                        . ' WHERE characterID = ' . $char_id)->fetch_row()[0] ?? $db->query('SELECT guild FROM user_profile WHERE id = '
                        . $_SESSION['user_id'] . ' AND guild IN'
                        . ' (SELECT name FROM scores_guilds WHERE world = ' . $_SESSION['user_world'] . ')')->fetch_row()[0];
    } else {
        $guild_name = null;
    }
} else {
    // @todo shouldn't be empty array?
    $friends_online = array('');
    $char_id = $guild_name = null;
}
$platinum_discount = max(array_map(function ($arr) {
    $amount = array_search($arr, Pricing::PRICES);
            return ((new PlatinumBundle($amount))->get_amount() >= $amount)
                        ? $arr['discount_pct'] : 0;
        }, Pricing::PRICES));
$document->assign(array(
    'screenshots_total' => $screenshots_total[0],
    'screenshots_new' => $screenshots_new[0],
    'album_total' => $album_total[0],
    'album_new' => $album_new[0],
    'forum_total' => Forum::count_total(),
    'forum_new' => Forum::count_unread(),
    'themes_total' => $themes_total[0],
    'themes_new' => $themes_new[0],
    'artworks_total' => $artworks_total[0],
    'artworks_new' => $artworks_new[0],
    'friends_online' => $friends_online[0],
    'char_id' => $char_id,
    'guild_name' => $guild_name,
    'news_official' => TibiameComParser::fetch_news(),
    'platinum_discount' => $platinum_discount
));

$document->display('index');
