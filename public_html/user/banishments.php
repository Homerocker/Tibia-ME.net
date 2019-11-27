<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (Perms::get(Perms::USERS_BAN)) {
    if (isset($_POST['ban'])) {
        Banishments::ban();
        Document::reload_msg(sprintf(_('%s has been banished.'), User::get_link($_POST['ban'], false)), $_SERVER['PHP_SELF'] . '?u=' . $_POST['ban']);
    } elseif (isset($_GET['unban'])) {
        $db->query('update `banishments` set `unbannedModeratorID` = \'' . $_SESSION['user_id'] . '\', `unbannedTime` = \'' . $_SERVER['REQUEST_TIME'] . '\' where `id` = \'' . intval($_GET['unban']) . '\' and `unbannedModeratorID` is null and `expirationTime` > \'' . $_SERVER['REQUEST_TIME'] . '\'');
        header('Location: ' . $_SERVER['PHP_SELF'] . '?view=' . $_GET['unban']);
        exit();
    }
}
$u = Auth::get_u(null);
$document = new Document(_('Banishments'));
if (isset($_GET['ban']) && Perms::get(Perms::USERS_BAN)) {
    $sql = $db->query('SELECT COUNT(*)
        FROM `banishments`
        WHERE `userID` = \'' . intval($_GET['ban']) . '\'
        AND `expirationTime` > \'' . $_SERVER['REQUEST_TIME'] . '\'
        AND `unbannedModeratorID` IS NULL')->fetch_row();
    if ($sql[0]) {
        Document::reload_msg(_('This user has an active banishment.'), $_SERVER['PHP_SELF'] . '?u=' . $_GET['ban']);
    }
    $sql = $db->query('SELECT * FROM `users` WHERE `id` = \'' . intval($_GET['ban']) . '\'')->fetch_assoc();
    if (!$sql) {
        $document->error(_('User not found.'));
    } else {
        $document->assign(array(
            'sql' => $sql
        ));
        $document->display('banishments_banform');
    }
} elseif (isset($_GET['view']) || isset($_GET['unban'])) {
    $sql = $db->query('SELECT * FROM `banishments` WHERE `id` = '
                    . intval(isset($_GET['unban']) ? $_GET['unban'] : $_GET['view']))
            ->fetch_assoc();
    if (!$sql) {
        $document->error(_('Banishment not found.'));
    } else {
        if (isset($_GET['view']) && $sql['userID'] == $_SESSION['user_id'] && $sql['viewed'] == 0) {
            $db->query('update banishments set viewed = 1 where id = ' . $sql['id']);
            // @todo why the hack redirect?
            //header('Location: ' . $_SERVER['PHP_SELF'] . '?view=' . $_GET['view']);
            //exit;
        }
        if (Perms::get(Perms::USERS_BAN) && $_SERVER['REQUEST_TIME'] < $sql['expirationTime'] && isset($_GET['unban'])) {
            $document->display('banishments_unban');
        } else {
            $document->assign(array(
                'ban' => $sql,
                'expired' => $_SERVER['REQUEST_TIME'] >= $sql['expirationTime'] || !empty($sql['unbannedModeratorID'])
            ));
            $document->display('banishments_view');
        }
    }
} else {
    $banishments = new Banishments;
    $banishments->fetch($u ? $u : null, $document->page, 15);
    if ($banishments->count === 0) {
        if ($u) {
            if ($u == $_SESSION['user_id']) {
                $document->error(_('You have no banishments.'));
            } else {
                $document->error(sprintf(_('%s has no banishments.'), User::get_link($u, false)));
            }
        } else {
            $document->error(_('There are no banished users.'));
        }
    } else {
        $document->assign(array(
            'data' => $banishments->data,
            'count' => $banishments->count - 1
        ));
        $document->display('banishments_list');
    }
}
$document->pages('u', $u);
