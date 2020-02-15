<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('Platinum'), [array(_('Premium'), './premium.php')]);
$document->assign([
    'icons' => icons_shuffle(2, 1)
]);
$document->display('platinum');
