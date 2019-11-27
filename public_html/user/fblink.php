<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
Auth::RequireLogin();
Profile::facebook_link();