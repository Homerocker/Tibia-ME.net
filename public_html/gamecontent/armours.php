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
$level_min = empty($_GET['level_min']) ? null : $_GET['level_min'];
$level_max = empty($_GET['level_max']) ? null : $_GET['level_max'];
$content->set_level($level_min, $level_max);
if (!empty($_GET['slot'])) {
    $content->set_slot($_GET['slot']);
}
if (!empty($_GET['type']) && is_array($_GET['type'])) {
    $content->set_type($_GET['type']);
}
if (!empty($_GET['sort'])) {
    $content->set_sort($_GET['sort']);
}
if (!empty($_GET['order'])) {
    $content->set_order($_GET['order']);
}
$content->fetch('armours');
$document->assign(array(
    'data' => $content->data,
    'vocation' => $content->vocation,
    'slot' => $content->slot,
    'type' => $content->type,
    'level_min' => $content->level_min,
    'level_max' => $content->level_max,
    'sort' => $content->sort,
    'order' => $content->order
));
$document->display('game_content_armours');