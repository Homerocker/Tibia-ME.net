<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$vocation = get_vocation();
if ($vocation === null) {
    Document::reload_msg(_('Invalid request.'), '/');
}
$document = new Document;
$content = new GameContent;
$content->set_vocation($vocation);
$content->fetch('skills');
$document->assign(array(
    'data' => $content->data,
    'vocation' => $content->vocation
));
$document->display('game_content_skills');
