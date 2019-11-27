<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$world = get_world();
$scores = new Scores;
if (isset($_GET['guild']) && $world !== null) {
    $guild_info = $scores->get_guild_info($_GET['guild'], $world);
    if (!$guild_info) {
        Document::reload_msg(_('Guild not found.'),
                $_SERVER['SCRIPT_NAME'] . '?world=' . $world);
    }
}

$document = new Document(isset($guild_info) ? $guild_info['name'] . ' w' . $guild_info['world']
            : _('Guilds'), (isset($guild_info) ? [[_('Guilds'), $_SERVER['SCRIPT_NAME'] . '?world=' . $world, true]] : null));
if (isset($guild_info)) {
    $document->assign([
        'guild_info' => $guild_info,
        'history' => $document->slice_data($scores->get_guild_exp_history($guild_info['name'], $guild_info['world']), 50),
        'members' => $scores->get_guild_members($guild_info['name'], $guild_info['world'])
    ]);
    $document->display('highscores_guild');
    $document->pages([
        'guild' => $guild_info['name'],
        'world' => $world
    ]);
} else {
    $document->assign(array(
        'world' => $world,
        'data' => $document->slice_data($scores->get_guilds($world), 50)
    ));
    $document->display('highscores_guilds');
    $document->pages([
        'world' => $world
    ]);
}
