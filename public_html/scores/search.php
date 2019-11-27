<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';

$document = new Document(_('Search'), [array(_('Highscores'), './', true)]);
$document->display('highscores_searchbox');

if (isset($_GET['nickname'])) {
    if (!Auth::CheckNickname($_GET['nickname'])) {
        $document->assign('results', 0);
        $document->display('highscores_search_results');
    } else {
        $world = get_world();
        $scores = new Scores;
        $data = $scores->search($_GET['nickname'], $world);
        $document->assign(array(
            'data' => $document->slice_data($data, 50),
            'results' => count($data)
        ));
        $document->display('highscores_search_results');
        $document->pages(array(
            'nickname' => $_GET['nickname'],
            'world' => get_world()
        ));
    }
}