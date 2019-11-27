<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (isset($_GET['markasread'])
        && Forum::forum_exists($_GET['markasread'])) {
    $forum = new Forum;
    $forum->markallasread($_GET['markasread']);
}
if (!isset($_GET['f'])
        || !Forum::forum_exists($_GET['f'])) {
    Document::reload_msg(_('Forum not found.'), './');
}
if ($_GET['f'] == 5
        && !Perms::get(Perms::FORUM_HIDDEN_ACCESS)) {
    Document::reload_msg(_('Forum not found.'), './');
}
$forum = new Forum;
$forum->fetch_topics($_GET['f']);
$document = new Document(sprintf(_('Forum: %s'), $forum->title), array(
    array(_('Forum'), './'),
    array($forum->title),
    ((($_GET['f'] != 6 && $_SESSION['user_id']) || Perms::get(Perms::POST_NEWS)) ? array(_('Post new topic'), './posting.php?f=' . $_GET['f']) : null)
));
$document->assign(array(
    'title' => $forum->title,
    'count' => $forum->count - 1,
    'data' => $forum->data
));
$document->display('forum_viewforum');
$document->get_page($forum->pages);
$document->pages(array(
    'f' => $_GET['f']
));