<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$forum = new Forum;
if (isset($_GET['act'])
        && $_GET['act'] == 'markallasread'
        && $_SESSION['user_id']) {
    $forum->markallasread();
}
$document = new Document(_('Forum'), array(
    array(_('Forum')),
    array(_('Mark all as read'), $_SERVER['PHP_SELF'] . '?act=markallasread')
));
$document->display('forum_searchbox');
$forum->fetch_forums();
$document->assign(array(
    'data' => $forum->data,
    'count' => $forum->count
));
$document->display('forum_index');