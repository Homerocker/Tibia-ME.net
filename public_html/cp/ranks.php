<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
CP::auth(Perms::RANKS_ASSIGN);
$ranks = new Ranks;
if ($ranks->mode === Ranks::MODE_EDIT_POST || $ranks->mode === Ranks::MODE_ADD_POST) {
    if ($ranks->save($ranks->mode === Ranks::MODE_EDIT_POST ? $_POST['edit'] : null, $_POST['name'], $_POST['prefix'],
                    $_POST['color'], isset($_POST['perms']) ? $_POST['perms'] : null)) {
        Document::reload_msg(_('Changes saved.'));
    } else {
        Document::msg($ranks->error);
    }
} elseif ($ranks->mode === Ranks::MODE_DEL_POST) {
    if ($ranks->rank_id == 1) {
        Document::reload_msg(_('Cannot delete system default rank.'));
    }
    $ranks->delete($ranks->rank_id, isset($_POST['assign']) ? $_POST['assign'] : 1);
    Document::reload_msg(_('Changes saved.'));
}
$navi = array(
    array(_('Control Panel'), './')
        );
if (isset($_GET['add']) || $ranks->mode === Ranks::MODE_EDIT_GET || $ranks->mode == Ranks::MODE_DEL_GET) {
    $navi[] = array(_('Ranks'), $_SERVER['PHP_SELF']);
}
$document = new Document(_('Ranks'), $navi);

if (isset($_GET['add'])) {
    $document->assign('rank', $ranks->get());
    $document->display('cp_ranks_edit_add');
} elseif ($ranks->mode === Ranks::MODE_EDIT_GET) {
    $document->assign('rank', $ranks->get($ranks->rank_id));
    $document->display('cp_ranks_edit_add');
} elseif ($ranks->mode === Ranks::MODE_DEL_GET) {
    $document->assign(array(
        'users' => $ranks->count_users($ranks->rank_id),
        'ranks' => Ranks::get_list(),
        'rank' => $ranks->get($ranks->rank_id)
    ));
    $document->display('cp_ranks_delete');
} else {
    $document->assign('ranks', Ranks::get_list());
    $document->display('cp_ranks');
}
