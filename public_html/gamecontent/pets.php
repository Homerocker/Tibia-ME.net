<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('Pets'));
$pets = new GameContentPets;
$pets->fetch();
$document->assign('pets', $pets->data);
$document->display('game_content_pets');

