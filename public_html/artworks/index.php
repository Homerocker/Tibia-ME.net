<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (isset($_GET['delete']) && Artworks::artwork_exists($_GET['delete'])) {
    if (!Perms::get(Perms::ARTWORKS_MOD)) {
        Document::reload_msg(_('You don\'t have permission to delete this artwork.'),
                $_SERVER['PHP_SELF'] . '?page=' . Document::s_get_page());
    }
    Artworks::remove($_GET['delete']);
    Document::reload_msg(_('Artwork has been deleted.'),
            $_SERVER['PHP_SELF'] . '?page=' . Document::s_get_page());
}
$document = new Document(_('Artworks'));
$artworks = new Artworks;
$artworks->fetch(12);
$document->assign(array(
    'data' => $artworks->data,
    'page' => $artworks->page
));
$document->get_page($artworks->pages);
$document->display('artworks_index');
$document->pages();
