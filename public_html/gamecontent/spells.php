<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';

$document = new Document;
$content = new GameContent;
if (!empty($_GET['name'])) {
    $content->set_name($_GET['name']);
}
if (!empty($_GET['vocation'])) {
    $content->set_vocation($_GET['vocation']);
}
if (!empty($_GET['type'])) {
    $content->set_spell_type($_GET['type']);
}
if (!empty($_GET['target'])) {
    $content->set_target($_GET['target']);
}
if (!empty($_GET['order'])) {
    $content->set_order($_GET['order']);
}
$content->fetch('spells');
$document->assign(array(
    'data' => $content->data,
    'name' => $content->name,
    'vocation' => $content->vocation,
    'type' => $content->type,
    'target' => $content->target,
    'order' => $content->order
));
$document->display('game_content_spells');