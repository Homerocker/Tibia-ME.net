<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$gamecodes = new GameCodes;
if (isset($_POST['gamecodes']) && Perms::get(Perms::GAMECODES_ADD)) {
    $gamecodes->add($_POST['gamecodes'], $_POST['type'], $_POST['amount']);
} elseif ($gamecodes->mode === GameCodes::MODE_ACTIVATE_CONFIRMATION) {
    $gamecodes->activate($_GET['code_type'], $_GET['nickname'], $_GET['world'], $_GET['multiplier'], false);
} elseif ($gamecodes->mode === GameCodes::MODE_ACTIVATE_CONFIRMED) {
    $gamecodes->activate($_POST['code_type'], $_POST['nickname'], $_POST['world'], $_POST['multiplier'], true);
}
$document = new Document(_('Game codes'), [array(_('Control Panel'), './')]);
if ($gamecodes->get_mode() === GameCodes::MODE_ACTIVATE_CONFIRMATION) {
    $document->assign(array(
        'nickname' => $gamecodes->nickname,
        'world' => $gamecodes->world,
        'code_type' => $gamecodes->type . ':' . $gamecodes->amount,
        'multiplier' => $gamecodes->multiplier
    ));
    $document->display('cp_gamecodes_activate_confirm');
} else {
    $codes_available = $gamecodes->get_codes();
    $document->assign(array(
        'codes' => $gamecodes->codes,
        'codes_available' => $codes_available,
        'history' => $gamecodes->get_history(),
        'overview' => $gamecodes->get_overview($codes_available),
    ));
    $document->display('cp_gamecodes');
}

