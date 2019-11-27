<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
session_write_close();
if (isset($_POST['set']) && isset($_POST['item_id'])) {
    $set = new GameContentSet(json_decode($_POST['set'], true));
    $set->set_item($_POST['item_id']);
} elseif (isset($_POST['set']) && isset($_POST['stat_raise']) && isset($_POST['stat_priority'])) {
    $set = new GameContentSet(json_decode($_POST['set'], true));
    $set->set_params($_POST['vocation'], $_POST['def_level'], $_POST['stat_priority']);
    $set->raise_stat($_POST['stat_raise']);
} elseif (isset($_POST['set']) && isset($_POST['ignore']) && isset($_POST['vocation']) && isset($_POST['def_level']) && isset($_POST['stat_priority']) && isset($_POST['incl_upgraded']) && isset($_POST['evenly'])) {
    $set = new GameContentSet(json_decode($_POST['set'], true));
    $set->set_params($_POST['vocation'], $_POST['def_level'], $_POST['stat_priority'], $_POST['incl_upgraded'], $_POST['evenly']);
    $set->ignore_item($_POST['ignore']);
} elseif (isset($_POST['set']) && isset($_POST['unignore']) && isset($_POST['vocation']) && isset($_POST['def_level']) && isset($_POST['stat_priority']) && isset($_POST['incl_upgraded']) && isset($_POST['evenly'])) {
    $set = new GameContentSet(json_decode($_POST['set'], true));
    $set->set_params($_POST['vocation'], $_POST['def_level'], $_POST['stat_priority'], $_POST['incl_upgraded'], $_POST['evenly']);
    $set->unignore_item($_POST['unignore']);
} else {
    $set = new GameContentSet;
    $set->set_params(_post('vocation'), _post('def_level'), _post('stat_priority'), _post('incl_upgraded'), _post('evenly'));
    $set->get_bis();
}
echo json_encode($set);
