<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
Auth::RequireLogin();
$document = new Document(_('Notifications'));
$notifications = new Notifications;
$notifications->fetch(20);
$document->assign(array(
    'data' => $notifications->data
));
$document->display('notifications');
$document->get_page($notifications->pages);
$document->pages();