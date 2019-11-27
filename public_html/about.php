<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('What is TibiaME?'));
$document->display('about');
