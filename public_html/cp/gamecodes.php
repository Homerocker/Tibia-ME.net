<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$gamecodes = new GameCodes;
if (Perms::get(Perms::GAMECODES_ADD)) {
    $form_add = new Form('add_gamecodes');
    $form_add->addtextarea('gamecodes', 'gamecodes', 10);
    $form_add->field('gamecodes')->set_description(_('each game code on a new line'));
    $form_add->addinput('amount', 'number', 'amount');
    $form_add->field('submit')->event('onclick', 'return confirm(\'' . _('Are you sure you want to add specified gamecodes?') . '\')');
    if ($form_add->submit()) {
        $gamecodes->add($_POST['gamecodes'], $_POST['amount']);
    }
}

if (Perms::get(Perms::GAMECODES_ACTIVATE)) {
    $form_activate = new Form('activate_bundle');
    $form_activate->addinput('nickname', 'text', 'nickname', 2, 10);
    $form_activate->addselect('world', 'world', function () {
        $worlds = [null => null];
        for ($i = 1; $i <= WORLDS; ++$i) {
            $worlds[$i] = $i;
        }
        return $worlds;
    });
    $form_activate->addinput('amount', 'number', 'amount');

    $form_activate_confirm = new Form('bundle_activate_confirm');
    $form_activate_confirm->addinput('nickname', 'hidden', 'nickname');
    $form_activate_confirm->addinput('world', 'hidden', 'world');
    $form_activate_confirm->addinput('amount', 'hidden', 'amount');
    if ($form_activate_confirm->submit()) {
        $bundle = new PlatinumBundle($_POST['amount']);
        $activated = $bundle->activate($_POST['nickname'], $_POST['world']);
        if ($activated === true) {
            Document::reload_msg(_('Gamecodes activated.'));
        } else {
            Document::reload_msg(sprintf(_('%d Platinum activated.'), $activated));
        }
    } elseif ($form_activate->submit()) {
        $bundle = new PlatinumBundle($_GET['amount']);
    }
}
$document = new Document(_('Game codes'), [array(_('Control Panel'), './')]);

if (Perms::get(Perms::GAMECODES_ACTIVATE) && $form_activate->submit()) {
    $form_activate_confirm->field('nickname')->value($form_activate->field('nickname')->value());
    $form_activate_confirm->field('world')->value($form_activate->field('world')->value());
    $form_activate_confirm->field('amount')->value($bundle->get_amount());
    $document->assign(array(
        'form' => $form_activate_confirm
    ));
    $document->display('cp_gamecodes_activate_confirm');
} else {
    $codes_available = GameCodes::get_codes_available();
    if (Perms::get(Perms::GAMECODES_ACTIVATE)) {
        $document->assign('form_activate', $form_activate);
    }
    if (Perms::get(Perms::GAMECODES_ADD)) {
        $document->assign('form_add', $form_add);
    }
    $document->assign(array(
        'codes' => $gamecodes->codes,
        'codes_available' => $codes_available,
        'history' => $gamecodes->get_history(),
        'overview' => GameCodes::get_overview($codes_available),
    ));
    $document->display('cp_gamecodes');
}

