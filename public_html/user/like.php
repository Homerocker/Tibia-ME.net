<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
Auth::RequireLogin();

Likes::like();

header('Location: ' . get_redirect(false, '/'));