<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('Platinum'), array(
    array(_('Premium'), './premium.php')
));
$document->assign('icons', icons_shuffle(2, 1));
$document->assign('prices', (new Pricing)->get_prices('platinum'));
$document->display('platinum');