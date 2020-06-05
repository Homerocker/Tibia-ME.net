<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if ($_SESSION['user_id'] != 1) {
    exit;
}
$sql = $db->query('select id, icon from game_content_armours');
$i = $j = 0;
while ($row = $sql->fetch_assoc()) {
    if (!empty($row['icon'])) {
        ++$i;
        $basename = pathinfo($row['icon'], PATHINFO_BASENAME);
        $row['icon'] = '/armours/' . $basename;
        $db->query('update game_content_armours set icon = ' . $db->quote($row['icon']) . ' where id = ' . $row['id']);
        /*
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/hires' . $row['icon'])) {
            ++$j;
            copy($_SERVER['DOCUMENT_ROOT'] . '/hires' . $row['icon'],
                    $_SERVER['DOCUMENT_ROOT'] . ICONS_DIR . '/hires' . $row['icon']);
            images::compress($_SERVER['DOCUMENT_ROOT'] . ICONS_DIR . '/hires' . $row['icon'],
                    true);
        } else {
            echo $basename . '<br/>';
        }
         * 
         */
    }
}
echo "$j of $i HiRes icons exist<br/>";

$sql = $db->query('select icon from game_content_weapons');
$i = $j = 0;
while ($row = $sql->fetch_assoc()) {
    if (!empty($row['icon'])) {
        ++$i;
        $basename = pathinfo($row['icon'], PATHINFO_BASENAME);
        $db->query('update game_content_weapons set icon = ' . $db->quote('/weapons/' . $basename) . ' where icon = ' . $db->quote($row['icon']));
        /*
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/hires' . '/weapons/' . $basename)) {
            ++$j;
            copy($_SERVER['DOCUMENT_ROOT'] . '/hires' . '/weapons/' . $basename,
                    $_SERVER['DOCUMENT_ROOT'] . ICONS_DIR . '/hires' . '/weapons/' . $basename);
            images::compress($_SERVER['DOCUMENT_ROOT'] . ICONS_DIR . '/hires' . '/weapons/' . $basename,
                    true);
        } else {
            echo $basename . '<br/>';
        }
         * 
         */
    }
}
echo "$j of $i HiRes icons exist<br/>";

