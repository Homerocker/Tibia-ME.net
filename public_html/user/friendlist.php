<?php

/**
 * Friendlist.
 * 
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2012, Tibia-ME.net
 */
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
Auth::RequireLogin();

// fetching user ID
$u = Auth::get_u();

// fetching requested action
if (isset($_GET['add']) && Auth::user_exists($_GET['add'])) {
    $friendlist = new Friendlist;
    $friendlist->add();
} elseif (isset($_GET['remove'])) {
    $friendlist = new Friendlist;
    $friendlist->remove();
} elseif (isset($_GET['accept'])) {
    $friendlist = new Friendlist;
    $friendlist->accept();
} elseif (isset($_GET['decline'])) {
    $friendlist = new Friendlist;
    $friendlist->decline();
} elseif (isset($_GET['act']) && $_GET['act'] == 'viewmutual' && $u != $_SESSION['user_id']) {
    $friendlist = new Friendlist($u);
    $friendlist->fetch('mutual');
    $mode = 'viewmutual';
    $title = _('Mutual friends');
    $navi = [[_('Friendlist'), $_SERVER['PHP_SELF']]];
} elseif (isset($_GET['act']) && $_GET['act'] == 'viewonline') {
    $friendlist = new Friendlist($u);
    $friendlist->fetch('online');
    $mode = 'viewonline';
    $title = _('Friends online');
    $navi = [[_('Friends'), $_SERVER['PHP_SELF']]];
} elseif (isset($_GET['act']) && $_GET['act'] == 'requests_in') {
    $mode = 'requests_in';
    $title = _('Requests');
    $navi = [[_('Friends'), $_SERVER['PHP_SELF']]];
    $friendlist = new Friendlist;
    $friendlist->fetch('requests_in');
} elseif (isset($_GET['act']) && $_GET['act'] == 'requests_out') {
    $friendlist = new Friendlist;
    $friendlist->fetch('requests_out');
    $mode = 'requests_out';
    $title = _('My requests');
    $navi = [[_('Friends'), $_SERVER['PHP_SELF']]];
} elseif (isset($_GET['cancel'])) {
    $friendlist = new Friendlist;
    $friendlist->cancel();
} else {
    $friendlist = new Friendlist($u);
    $friendlist->fetch();
    $mode = 'view';
    $title = _('Friends');
}

if ($u != $_SESSION['user_id']) {
    $navi[] = array(User::get_link($u, 0), './profile.php?u=' . $u);
}
if ($mode != 'view') {
    $navi[] = array(sprintf(_('Friends: %d'), $friendlist->total_counter),
        $_SERVER['PHP_SELF'] . '?u=' . $u);
}
if ($u != $_SESSION['user_id']) {
    if ($mode != 'viewmutual' && $friendlist->mutual_counter) {
        $navi[] = array(sprintf(_('Mutual: %d'), $friendlist->mutual_counter), $_SERVER['PHP_SELF']
            . '?act=viewmutual&amp;u=' . $u);
    }
}
if (($mode == 'view' || $mode == 'viewmutual') && $friendlist->total_counter) {
    if ($friendlist->online_counter) {
        $navi[] = array(sprintf(_('Online: %d'), $friendlist->online_counter),
            $_SERVER['PHP_SELF'] . '?act=viewonline&amp;u=' . $u);
    }
}
if ($u == $_SESSION['user_id']) {
    if ($friendlist->requests_in_counter) {
        if ($mode != 'requests_in') {
            $navi[] = array(sprintf(_('Requests: %d'),
                        $friendlist->requests_in_counter),
                $_SERVER['PHP_SELF'] . '?act=requests_in');
        }
    }
    if ($friendlist->requests_out_counter) {
        if ($mode != 'requests_out') {
            $navi[] = array(
                array(sprintf(_('Your requests: %d'),
                            $friendlist->requests_out_counter),
                    $_SERVER['PHP_SELF'] . '?act=requests_out')
            );
        }
    }
}
$document = new Document($title, $navi ?? null);

switch ($mode) {
    // ADD
    case 'add':
        $document->assign(array(
            'nickname' => $friendlist->nickname,
            'world' => $friendlist->world,
            'redirect' => get_redirect(true, $_SERVER['PHP_SELF'])
        ));
        $document->display('friendlist_add');
        break;
// VIEW MUTUAL
    case 'viewmutual':
        $document->assign(array(
            'data' => $friendlist->data
        ));
        $document->display('friendlist_mutual');
        $document->get_page($friendlist->pages);
        $document->pages(array(
            'act' => 'viewmutual',
            'u' => $u
        ));
        break;
// VIEWONLINE
    case 'viewonline':
        $document->assign(array(
            'data' => $friendlist->data
        ));
        $document->display('friendlist_online');
        $document->get_page($friendlist->pages);
        $document->pages(array(
            'act' => 'viewonline',
            'u' => $u
        ));
        break;
// INCOMING REQUESTS
    case 'requests_in':
        $document->assign(array(
            'data' => $friendlist->data
        ));
        $document->display('friendlist_requests_in');
        $document->get_page($friendlist->pages);
        $document->pages('act', 'requests_in');
        break;
// OUTGOING REQUESTS
    case 'requests_out':
        $document->assign(array(
            'data' => $friendlist->data
        ));
        $document->display('friendlist_requests_out');
        $document->get_page($friendlist->pages);
        $document->pages('act', 'requests_out');
        break;
// VIEW (default)
    default:
        $document->assign(array(
            'data' => $friendlist->data
        ));
        if (isset($friendlist->search_results)) {
            $document->assign('search_results', $friendlist->search_results);
        }
        $document->display('friendlist');
        $document->get_page($friendlist->pages);
        $document->pages('u', $u);
}