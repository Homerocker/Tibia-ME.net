<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('Emoticons'));
$pages = $db->query('select COUNT(distinct `image`) from `smilies`')->fetch_row()[0];
$pages = ceil($pages/20);
$document->get_page($pages);
$sql = $db->query('select * from `smilies` group by `image` order by `code` limit '.(($document->page-1)*20).', 20');
$data = array();
while ($row = $sql->fetch_assoc()) {
    $data[] = $row;
}
$document->assign(array(
    'data' => $data
));
$document->display('emoticons');
$document->pages();