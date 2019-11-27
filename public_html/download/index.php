<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(_('Download'));
$TibiameComParser = new TibiameComParser;
$document->assign('version', $TibiameComParser->get_clients_versions());
$document->display('download');