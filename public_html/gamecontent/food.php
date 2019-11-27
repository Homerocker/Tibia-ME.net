<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$doc = new Document(_('Food and potions'));
$gc = new GameContent;
$gc->fetch('food');
$doc->assign(array(
    'data' => $gc->data
));
$doc->display('game_content_food');