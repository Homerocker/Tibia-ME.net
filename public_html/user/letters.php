<?php

/**
 * Letters model.
 *
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 * @version 2.3.01
 */
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
Auth::RequireLogin();

/*
 * set $folder
 */
if (isset($_REQUEST['folder'])) {
    switch ($_REQUEST['folder']) {
        case 'outbox':
            $folder = 'outbox';
            $folder_title = _('Outbox');
            break;
        case 'sentbox':
            $folder = 'sentbox';
            $folder_title = _('Sentbox');
            break;
        case 'savebox':
            $folder = 'savebox';
            $folder_title = _('Savebox');
            break;
        default:
            $folder = 'inbox';
            $folder_title = _('Inbox');
    }
} else {
    $folder = 'inbox';
    $folder_title = _('Inbox');
}

$letters = new Letters;

/*
 * set $mode
 */
if (isset($_GET['delete']) && $letters->exists($_GET['delete'])) {
    $letters->delete($folder);
} elseif (isset($_GET['save']) && $letters->exists($_GET['save'])) {
    $letters->save($folder);
} elseif (isset($_REQUEST['edit']) && $letters->exists($_REQUEST['edit'])) {
    $letters->edit();
    $mode = 'edit';
    $document = new Document(_('Edit letter'), [_('Letters'), $_SERVER['PHP_SELF']]);
} elseif (isset($_GET['view']) && $letters->exists($_GET['view'])) {
    $mode = 'view';
    $document = new Document(_('Reading letter'), [array($folder_title, $_SERVER['PHP_SELF'] . '?folder=' . $folder . '&amp;page=' . Document::s_get_page())]);
} elseif (isset($_REQUEST['compose'])) {
    $letters->compose();
    $mode = 'compose';
    $document = new Document(_('Compose'), [array(_('Letters'), $_SERVER['PHP_SELF'])]);
} else {
    $mode = null;
    $document = new Document($folder_title);
}

switch ($mode) {
    case 'edit':
        $document->assign(array(
            'subject' => $letters->subject,
            'message' => $letters->message,
            'replyto_count' => $letters->replyto_count,
            'nickname' => $letters->nickname,
            'world' => $letters->world,
            'error' => $letters->error
        ));
        $document->display('letters_edit');
        break;
    case 'view':
        $letters->view($_GET['view'], $folder);
        if (isset($letters->data['error'])) {
            $document->assign(array(
                'data' => $letters->data,
                'folder' => $folder
            ));
        } else {
            $document->assign(array(
                'data' => $letters->data,
                'sender' => User::get_link($letters->data['from']),
                'folder' => $folder,
                'page' => $document->page
            ));
        }
        $document->display('letters_view');
        break;
    case 'compose':
        $document->assign(array(
            'nickname' => htmlspecialchars($letters->nickname, ENT_COMPAT, 'UTF-8'),
            'world' => $letters->world,
            'subject' => htmlspecialchars($letters->subject, ENT_COMPAT, 'UTF-8'),
            'message' => htmlspecialchars($letters->message, ENT_COMPAT, 'UTF-8'),
            'u' => $letters->u,
            'folder' => $folder,
            'error' => $letters->error,
            'replyto_count' => $letters->replyto_count,
        ));
        $document->display('letters_compose');
        break;
    default:
        $letters->fetch_folder($folder, 20);
        $document->get_page($letters->pages);
        $document->assign(array(
            'folder' => $folder,
            'page' => $document->page,
            'data' => $letters->data
        ));
        $document->display('letters_folder');
        $document->get_page($letters->pages);
        $document->pages();
}