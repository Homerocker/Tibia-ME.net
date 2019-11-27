<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('Worlds'));
$world = get_world();
$scores = new Scores;
$document->assign('data', $document->slice_data($scores->get_worlds($world), 50));
if (isset($world)) {
    $document->assign('world', $world);
}
$document->display('highscores_worlds');
$document->pages([
    'world' => $world
]);
