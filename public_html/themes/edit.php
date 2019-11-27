<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (!isset($_REQUEST['theme_id'])
        || !Themes::Exists($_REQUEST['theme_id'])) {
    Document::reload_msg(_('Invalid request.'), './');
}
if (isset($_POST['submit'])) {
    Themes::edit();
}
$document = new Document(_('Themes'), [[_('Themes'), './?page=' . Document::s_get_page()]]);
$theme = new Themes();
$theme->fetch_edit($_REQUEST['theme_id']);
$document->assign(array(
    'data' => $theme->data,
    'page' => $document->page,
    'theme_id' => $_REQUEST['theme_id']
));
$document->display('themes_edit');