<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if ($_SESSION['user_id'] == 1) {
	phpinfo();
}