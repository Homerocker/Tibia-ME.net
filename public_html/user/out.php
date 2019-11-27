<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (!empty($_COOKIE['token'])) {
    Auth::remove_token($_COOKIE['token']);
}
session_unset();
session_destroy();
header('Location: /');