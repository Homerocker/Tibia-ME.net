<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$u = Auth::get_u();
if (!$u) {
    Auth::RequireLogin();
}
if (isset($_REQUEST['id']) && Album::album_exists($_REQUEST['id'])) {
    $mode = 'edit';
    $album = new Album;
    if (isset($_POST['submit'])) {
        $album->album_update();
    } else {
        $album->album_fetch_data($_REQUEST['id']);
    }
} elseif (isset($_GET['delete']) && Album::album_exists($_GET['delete'])) {
    $album = new Album;
    $album->album_delete();
} elseif (isset($_GET['mode'])) {
    $mode = $_GET['mode'];
    switch ($mode) {
        case 'create':
            $album = new Album;
            $album->create_album();
            break;
        default:
            $mode = null;
    }
} else {
    $mode = null;
}
$navi = array(
    array(_('Photo album'), './')
);
if ($u != $_SESSION['user_id']) {
    $navi[] = array(User::get_display_name($u), '/user/profile.php?u=' . $u);
}
if ($mode !== null || $u != $_SESSION['user_id']) {
    $navi[] = array(
        _('My photos'), $_SERVER['PHP_SELF'], $u != $_SESSION['user_id']
    );
}
if ($u == $_SESSION['user_id']) {
    if ($mode === null) {
        $navi[] = array(
            _('Create album'),
            $_SERVER['PHP_SELF'] . '?mode=create'
        );
    }
}
$document = new Document(_('Photo album'), $navi);
switch ($mode) {
    case 'create':
        $document->assign(array(
            'form' => $album->form,
            'mode' => $mode
        ));
        $document->display('album_create_edit');
        break;
    case 'edit':
        $document->assign(array(
            'form' => $album->data,
            'album_id' => $_REQUEST['id']
        ));
        $document->display('album_create_edit');
        break;
    default:
        $album = new Album;
        $album->albums($u, 12);
        $document->assign(array(
            'data' => $album->data,
            'u' => $u
        ));
        $document->display('album_albums');
        $document->get_page($album->pages);
}
$document->pages('u', $u);
