<?php

$_GET['search'] = trim($_GET['search']);
if (empty($_GET['search'])) {
    header('Location: ./');
    exit();
}
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('Search'), [array(_('Forum'), './')]);
$document->display('forum_searchbox');
$forum = new Forum;
$forum->search($_GET['search'], 20);
$document->get_page($forum->pages);
$document->assign(array(
    'data' => $forum->data,
    'results' => $forum->results
));
$document->display('forum_search');
$document->pages('search', $_GET['search']);