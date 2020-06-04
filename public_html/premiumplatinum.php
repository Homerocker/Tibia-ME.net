<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('Premium & Platinum'));
$document->assign([
    'icons' => icons_shuffle(2, 1)
]);
$document->display('premiumplatinum');
