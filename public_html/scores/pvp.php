<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$world = get_world(1);
$document = new Document('PvP', [array(_('Search'), './search.php')]);
$scores = new Scores;
$document->assign(array(
    'world' => $world,
    'data' => $document->slice_data($scores->get_pvp($world), 50)
));
$document->display('highscores_pvp');
$document->pages('world', $world);