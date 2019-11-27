<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (!isset($_GET['characterID']) || !ctype_digit((string) $_GET['characterID'])) {
    Document::reload_msg(_('Invalid request.'), './');
}
$scores = new Scores;
$char_data = $scores->get_char_data($_GET['characterID']);
if ($char_data === null) {
    Document::reload_msg(_('Invalid request.'), './');
}
$document = new Document($char_data['nickname'] . ', w' . $char_data['world'], [[_('Highscores'), './']]);
$data = $scores->get_char_exp_history($_GET['characterID']);
$chart_data = $scores->get_exp_chart_data($data);
$document->assign([
    'char_data' => $char_data,
    'exp_history' => $document->slice_data($data, 50),
    'char_performance' => $scores->get_char_performance($data),
    'chart_data' => $chart_data
]);
$document->display('highscores_viewscores');
$document->pages('characterID', $_GET['characterID']);