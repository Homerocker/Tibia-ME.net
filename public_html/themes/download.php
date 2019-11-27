<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (!isset($_GET['theme_id']) || !Themes::Exists($_GET['theme_id'])) {
    Document::reload_msg(_('Invalid request.'), './');
}
$sql = $db->query('SELECT `s60_hash`, `downloads`
    FROM `themes`
    WHERE `id` = \'' . $_GET['theme_id'] . '\'')->fetch_assoc();
$db->query('UPDATE `themes` SET `downloads` = \'' . ($sql['downloads'] + 1) . '\' WHERE `id` = \'' . $_GET['theme_id'] . '\'');
Document::reload(UPLOAD_DIR . '/themes/' . $sql['s60_hash'] . '.sis');