<?php

/**
 * @package album
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 */
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('Photo album'), [[_('My photos'), './albums.php']]);
$album = new Album;
if (isset($_GET['order']) && in_array($_GET['order'], array('date', 'nickname', 'comments'))) {
    $order = $_GET['order'];
} else {
    $order = 'date';
}
$world = get_world();
$album->users(12, $order, $world);
$document->display('album_searchbox');
$document->assign(array(
    'data' => $album->data,
    'order' => $order,
    'world' => $world
));
$document->display('album_index');
$document->get_page($album->pages);
$document->pages(array(
    'order' => $order,
    'world' => $world
));
