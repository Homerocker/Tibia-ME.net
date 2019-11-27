<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';

$document = new Document(_('Moderators'));
$user = new User;
$user->fetch_by_rank('Moderator');
$document->assign(array(
    'user_ids' => $user->data,
    'count' => $user->count
));
$document->display('staff');