<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (empty($_GET['dbxpath'])) {
    Document::reload_msg(_('No file specified.'), '/');
}
$dbxpath = $_GET['dbxpath'];
$dirname = pathinfo($dbxpath, PATHINFO_DIRNAME);
if (ctype_digit(basename($dirname))) {
    $dirname = dirname($dirname);
}
Document::reload(UPLOAD_DIR . $dirname . '/' . pathinfo($dbxpath, PATHINFO_BASENAME));
