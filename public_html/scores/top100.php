<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('TOP 100'), [array(_('Search'), './search.php')]);
$world = get_world();
$vocation = get_vocation();
$scores = new Scores;
$document->assign([
    'data' => $document->slice_data($scores->get_top100($world, $vocation), 50),
    'world' => $world,
    'vocation' => $vocation
]);
$document->display('highscores_top100');
$document->pages(array(
    'world' => $world,
    'vocation' => $vocation
));