<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
Auth::RequireLogin(false);
$nickname = $_REQUEST['nickname'] ?? null;
$world = get_world();
$use_token = empty($_REQUEST['use_token']) ? 0 : 1;
if (isset($_POST['submit'])) {
    $password = $_POST['password'] ?? null;
    $user_id = Auth::login($nickname, $world, $password, $use_token);
    if (!is_int($user_id)) {
        // if not an integer, then it should be an error message
        Document::reload_msg($user_id,
                $_SERVER['PHP_SELF'] . '?nickname=' . urlencode($nickname) . '&world=' . $world . '&use_token=' . $use_token . '&redirect=' . get_redirect(true,
                        '/'));
    }
}
if (isset($user_id)) {
    $redirect = get_redirect(false, '/');
}
$document = new Document((isset($user_id) ? _('Welcome') : _('Log in')), (isset($redirect) && $redirect !== false ? [[_('Continue'), $redirect]] : null), false);
$document->assign(array(
    'nickname' => $nickname,
    'redirect' => get_redirect(false)
));
if (isset($user_id)) {
    $lastvisit = $db->query('
        SELECT `lastvisit`
        FROM `user_profile`
        WHERE `id` = \'' . $user_id . '\'
        AND `lastvisit` IS NOT NULL
        LIMIT 1
    ')->fetch_row()[0];
    $news = array_filter($db->query('
        SELECT
        (
            SELECT COUNT(*)
            FROM `album_photos`
            WHERE `timestamp` > \'' . $lastvisit . '\'
        ) as `photos`,
        (
            SELECT COUNT(*)
            FROM `artworks`
            WHERE `timestamp` > \'' . $lastvisit . '\'
        ) as `artworks`,
        (
            SELECT COUNT(*)
            FROM `forumTopics`
            WHERE `forumID` != \'6\'
            AND `time` > \'' . $lastvisit . '\'
        ) as `topics`,
        (
            SELECT COUNT(*)
            FROM `letters`
            WHERE `to` = \'' . $user_id . '\'
            AND `timestamp` > \'' . $lastvisit . '\'
        ) as `letters`,
        (
            SELECT COUNT(*)
            FROM `themes`
            WHERE `timestamp` > \'' . $lastvisit . '\'
        ) as `themes`,
        (
            SELECT COUNT(*)
            FROM `forumTopics`
            WHERE `forumID` = \'6\'
            AND `time` > \'' . $lastvisit . '\'
        ) as `news`'
            )->fetch_assoc());
    $document->assign(array(
        'news' => (!empty($news) ? $news : null),
        'lastvisit' => (($lastvisit !== null) ? User::date($lastvisit) : null)
    ));
    $document->display('login_welcome');
} else {
    $document->assign(array(
        'world' => $world,
        'use_token' => $use_token
    ));
    $document->display('login');
}
