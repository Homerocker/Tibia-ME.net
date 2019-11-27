<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (!empty($_POST['themeID']) && isset($_POST['themeStatus']) && (($_SESSION['user_id'] == Themes::AuthorID(intval($_POST['themeID'])) && in_array($_POST['themeStatus'], array('moderation', 'deleted'))) || (Perms::get(Perms::THEMES_MOD) && $_POST['themeStatus'] == 'deleted'))) {
    if ($_POST['themeStatus'] == 'deleted') {
        $sql = 'SELECT * FROM `themes` WHERE `id` = \'' . intval($_POST['themeID']) . '\'';
        $sql = $db->query($sql);
        $row = $sql->fetch_assoc();
        if (!empty($row['s60_hash'])) {
            unlink($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/themes/' . $row['s60_hash'] . '.sis');
        }
        if (!empty($row['screenshot'])) {
            unlink($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/themes/' . $row['screenshot']);
        }
        $db->query('DELETE FROM `themes` WHERE `id` = \'' . $row['id'] . '\'');
    } else {
        $db->query('UPDATE `themes` SET `status` = \'' . $db->real_escape_string($_POST['themeStatus']) . '\' WHERE `id` = \'' . $db->real_escape_string($_POST['themeID']) . '\'');
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
$u = Auth::get_u(null);
if ($u === null) {
    $navi = array(
        array(_('My themes'), $_SERVER['PHP_SELF'] . '?u=' . $_SESSION['user_id'])
    );
} elseif ($u == $_SESSION['user_id']) {
    $navi = array(
        array(_('Themes'), './', 1)
    );
} else {
    $navi = array(
        array(_('Themes'), './', 1),
        array(_('My themes'), $_SERVER['PHP_SELF'] . '?u=' . $_SESSION['user_id'])
    );
}
$document = new Document(_('Themes'), $navi);
$themes = new Themes();
$themes->fetch(12, $u);
$document->get_page($themes->pages);
$document->assign(array(
    'data' => $themes->data,
    'page' => $document->page,
    'u' => $u
));
$document->display('themes_index');
$document->pages();