<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$world = get_world();
$vocation = get_vocation();
$document = new Document(_('Achievements'), [array(_('Search'), './search.php')]);
$scores = new Scores;
$document->assign(array(
    'world' => $world,
    'vocation' => $vocation,
    'data' => $document->slice_data($scores->get_achievements($world, $vocation),
            50)
));
$document->display('highscores_achievements');
$document->pages(array(
    'world' => $world,
    'vocation' => $vocation
));
