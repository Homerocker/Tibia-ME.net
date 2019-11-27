<?php
// @todo merge with index.php
// @todo implement edit (aka replace) (button currently disabled)
require_once $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
Auth::RequireLogin();
$id = (isset($_GET['id']) && $_GET['id'] != 'auto_increment'
        && Artworks::artwork_exists($_GET['id'])) ? $_GET['id'] : 'auto_increment';
if ($id != 'auto_increment'
        && !Perms::get(Perms::ARTWORKS_MOD)
        && Artworks::uploader_id($id) != $_SESSION['user_id']) {
    Document::reload_msg(_('You don\'t have permission to edit this artwork.'), './');
}
$document = new Document(_('Artworks'), [array(_('Artworks'), './')]);
$artworks = new Artworks ($id);
$result = $artworks->upload();
$document->assign(array(
    'artwork_id' => $id,
    'result' => $result,
    'thumbnail' => (isset($artworks->thumbnail) ? $artworks->thumbnail : null)
));
$document->display('artworks_upload');