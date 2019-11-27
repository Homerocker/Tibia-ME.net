<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('Contacts'));
$document->display('contacts');