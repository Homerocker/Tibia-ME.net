<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
Auth::RequireLogin();
$user = new User;
if (isset($_GET['act']) && $_GET['act'] == 'viewonline') {
    $mode = 'viewonline';
    $page_title = _('Users online');
    $user->memberlist(1);
} else {
    $mode = 'view';
    $page_title = _('Memberlist');
    $search_nickname = isset($_GET['nickname']) ? $_GET['nickname'] : null;
    $search_world = isset($_GET['world']) ? intval($_GET['world']) : null;
    $user->memberlist(0, $search_nickname, $search_world);
}
$document = new Document($page_title,
        $mode == 'viewonline' ? [[sprintf(_('Users: %d'), $user->total_counter), $_SERVER['PHP_SELF']]]
            : null);

$document->assign(array(
    'data' => $user->data,
    'total_registered' => $user->total_counter
));

if ($mode == 'viewonline') {
    $document->display('viewonline');
} else {
    if (isset($user->search_results)) {
        $document->assign('search_results', $user->search_results);
    }
    $document->display('memberlist');
    $document->get_page($user->pages);
    $document->pages(array(
        'nickname' => $search_nickname,
        'world' => $search_world
    ));
}