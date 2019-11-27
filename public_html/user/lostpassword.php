<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if ($_SESSION['user_id']) {
    Document::reload_msg(_('You are already authorized.'), '/');
}
$step = (isset($_GET['user']) && isset($_GET['v'])) ? 2 : 1;

if ($step == 1 && isset($_POST['nickname']) && isset($_POST['world']) && isset($_POST['email'])) {
    $sql = $db->query('SELECT `id`, `nickname`, `world`, `email`
        FROM `users`
        WHERE `nickname` = \'' . $db->real_escape_string($_POST['nickname']) . '\'
        AND `world` = \'' . intval($_POST['world']) . '\'')->fetch_row();
    if (!$sql) {
        Document::reload_msg(_('User not found.'));
    } elseif (empty($sql[3])) {
        Document::reload_msg(_('This account cannot be recovered as it has no email set.'));
    } elseif (strtolower($sql[3]) != strtolower($_POST['email'])) {
        Document::reload_msg(_('Invalid email.'));
    } else {
        $validation_key = '';
        $keys = 'abcdefghijklmnopqrstuvwxyz1234567890';
        for ($i = 1; $i <= 32; ++$i) {
            $validation_key .= $keys[mt_rand(0, 35)];
        }
        $db->query('UPDATE `users` SET `validation_key` = \'' . $validation_key . '\' WHERE `id` = \'' . $sql[0] . '\'');
        if (Notifications::mail($sql[0], 'lostpassword', $validation_key)) {
            Document::reload_msg(_('Confirmation code has been sent to your email.'), $_SERVER['PHP_SELF']);
        } else {
            Document::reload_msg(_('We could not send you confirmation code, please send us a mail to support@tibia-me.net.'), $_SERVER['PHP_SELF']);
        }
    }
} elseif ($step == 2 && isset($_POST['password'])) {
    $sql = $db->query('SELECT * FROM `users` WHERE `id` = \'' . intval($_GET['user']) . '\'');
    if ($sql->num_rows) {
        $sql = $sql->fetch_assoc();
        if (empty($_GET['v']) || $_GET['v'] != $sql['validation_key']) {
            Document::reload_msg(_('Invalid confirmation key.'));
        }
        if (strlen($_POST['password']) < 5) {
            Document::reload_msg(_('Your new password should contain at least 5 characters.'), $_SERVER['PHP_SELF'] . '?user=' . $_GET['user'] . '&amp;v=' . $_GET['v']);
        }
        $db->query('UPDATE `users` SET `password` = \'' . password_hash($_POST['password'], PASSWORD_BCRYPT) . '\', `validation_key` = NULL, `login_tries` = \'0\' WHERE `id` = \'' . $sql['id'] . '\'');
        $db->query('DELETE FROM user_tokens WHERE user_id = ' . $sql['id']);
        Document::reload_msg(_('Your password has been changed.'), './login.php');
    } else {
        Document::reload_msg(_('User not found or email is incorrect.'));
    }
}

$document = new Document(_('Password recovery'));
if ($step == 1) {
    $document->display('lostpassword_step1');
} elseif ($step == 2) {
    list($id, $nickname, $world) = $db->query('SELECT `id`, `nickname`, `world`
        FROM `users`
        WHERE `id` = \'' . intval($_GET['user']) . '\'')->fetch_row();
    $document->assign([
        'id' => $id,
        'nickname' => $nickname,
        'world' => $world
    ]);
    $document->display('lostpassword_step2');
}