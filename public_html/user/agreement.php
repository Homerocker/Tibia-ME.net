<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (isset($_GET['accept'])) {
    if ($_SESSION['user_id']) {
        $db->query('UPDATE user_settings SET agreement_accepted = 1 WHERE id = ' . $_SESSION['user_id']);
        Document::reload_msg(_('Agreement accepted.'), get_redirect(false, '/'));
    } elseif (_get('act') == 'register') {
        header('Location: ./register.php?agreement=accepted&redirect=' . get_redirect(false, '/'));
    } else {
        // redirecting unauthorized user back to home page coz he could only come from there
        header('Location: /');
    }
    exit;
} elseif (isset($_GET['decline'])) {
    if ($_SESSION['user_id']) {
        $db->query('UPDATE `user_settings` SET `agreement_accepted` = \'0\' WHERE `id` = \'' . $_SESSION['user_id'] . '\'');
        header('Location: ./out.php');
    } else {
        header('Location: /');
    }
    exit;
}
$document = new Document(_('User Agreement'));
$document->display('agreement');
