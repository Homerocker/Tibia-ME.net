<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';

$document = new Document;
$content = new GameContent;
if (!empty($_GET['name'])) {
    $content->set_name($_GET['name']);
}
$content->fetch('monsters');
$document->assign(array(
    'data' => $content->data,
    'name' => $content->name
));
$document->display('game_content_monsters');