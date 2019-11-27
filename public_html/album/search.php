<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if ($_SERVER['SCRIPT_NAME'] == '/album/search.php') {
    $document = new Document(_('Search'), array(
            array(_('Photo album'), './'),
            array(_('My photos'), './albums.php')
        ));
    $document->display('album_searchbox');
    if (!empty($_GET['search'])) {
        $album = new Album;
        $album->search(12);
        $document->assign(array(
            'data' => $album->data,
            'results' => $album->results
        ));
        $document->display('album_search');
        $document->pages('search', $_GET['search']);
    }
} else {
    // @todo not used?
    $template = new Templates;
    $template->display('album_searchbox');
}