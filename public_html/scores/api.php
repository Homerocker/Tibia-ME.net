<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$sql = $db->query("select * from `scores_characters` where `nickname` = '".$_GET['nickname']."' limit 1");
echo json_encode($sql->fetch_assoc());