<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('Premium'), array(
    array(_('Platinum'), './platinum.php')
));
$document->assign('icon', icons_shuffle(1, 1)[0]);
$document->assign('prices', (new Pricing)->get_prices('premium'));
$document->display('premium');