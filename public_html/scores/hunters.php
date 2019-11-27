<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$world = get_world();
$type = (isset($_GET['type']) && $_GET['type'] == 'guilds') ? 'guilds' : null;
$period = _get('period', Scores::date());

$scores = new Scores;
$periods = $scores->get_hunters_periods($type == 'guilds');
if (!array_key_exists($period, $periods)) {
    $period = array_keys($periods)[0];
}
if ($type == 'guilds') {
    $data = $scores->get_hunters_guilds($world, $period);
} else {
    $vocation = get_vocation();
    $sort = _get('sort', 'gain');
    $data = $scores->get_hunters($world, $vocation, $period, $sort);
}


$document = new Document(_('Hunters'), [($type=='guilds' ? array(_('Characters'), $_SERVER['PHP_SELF'] . '?world=' . $world) : array(_('Guilds'), $_SERVER['PHP_SELF'] . '?type=guilds&amp;world=' . $world))]);

$document->assign(array(
    'data' => $document->slice_data($data, 100),
    'world' => $world,
    'period' => $scores->get_period(),
    'periods' => $periods
));

if ($type == 'guilds') {
    $document->display('highscores_hunters_guilds');
    $document->pages([
        'type' => $type,
        'world' => $world,
        'period' => $scores->get_period()
    ]);
} else {
    $document->assign([
        'vocation' => $vocation,
        'sort' => $sort
    ]);
    $document->display('highscores_hunters_characters');
    $document->pages([
        'type' => $type,
        'world' => $world,
        'vocation' => $vocation,
        'period' => $period,
        'sort' => $sort
    ]);
}
