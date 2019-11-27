<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
set_time_limit(180);
$document = new Document(_('Armour Calc'));
$document->assign(array(
    'items' => GameContent::get_armors_list()
));
$document->display('calc');
