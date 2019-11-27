<?php
require $_SERVER['DOCUMENT_ROOT'].'/../config.php';
CP::auth(Perms::MAINTENANCE);
$cp = new CP;
$cp->set_maintenance();
$document = new Document(_('Control Panel'), [array(_('Control Panel'), './')]);
$document->assign(array(
    'message' => $cp->message,
    'time' => $cp->time,
    'time_type' => $cp->time_type
));
$document->display('cp_maintenance');