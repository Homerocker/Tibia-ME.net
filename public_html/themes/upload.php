<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
Auth::RequireLogin();
if (isset($_FILES['theme']) && isset($_FILES['screenshot'])) {
    if ($_FILES['screenshot']['error'] != 4) {
        $screenshot = Uploader::upload('screenshot', array('type' => 'image', 'upload_dir' => '/themes'));
        if ($screenshot['error'] !== false) {
            Document::reload_msg($screenshot['error']);
        }
    }
    $theme = Uploader::upload('theme', array('type' => 's60'));
    if ($theme['error'] !== false) {
        if (isset($screenshot)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/themes/' . $screenshot['hash'] . '.' . $screenshot['ext']);
        }
        Document::reload_msg($theme['error']);
    }
    $db->query('INSERT INTO `themes` (
            `authorID`,
            `s60_hash`,
            `timestamp`
        ) VALUES (
        \'' . $_SESSION['user_id'] . '\', 
        \'' . $theme['hash'] . '\',
        \'' . $_SERVER['REQUEST_TIME'] . '\')');
    $db->query('INSERT INTO `comments_watch` (
            `user_id`,
            `target_type`,
            `target_id`
        ) VALUES (
            ' . $_SESSION['user_id'] . ',
            \'theme\',
            ' . $db->insert_id . '
        )');
    if ($_FILES['screenshot']['error'] != 4) {
        $db->query('UPDATE `themes` SET `screenshot` = \'' . $screenshot['filename'] . '\' WHERE `id` = \'' . $GLOBALS['db']->insert_id . '\'');
    }
    Document::reload_msg(_('Your theme has been successfully uploaded.'));
}
$document = new Document(_('Upload theme'), [[_('Themes'), './?page=' . Document::s_get_page()]]);
$document->display('themes_upload');