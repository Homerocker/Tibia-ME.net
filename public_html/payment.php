<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (isset($_POST['LMI_PREREQUEST']) && $_POST['LMI_PREREQUEST'] == 1) {
    $bundle = new PlatinumBundle($_POST['amount']);
    if ($_POST['AMOUNT'] != $bundle->get_amount()) {
        exit(_('Product not available.'));
    }
    if ($bundle->get_price('WM' . $_POST['LMI_PAYEE_PURSE'][0])['price'] > $_POST['LMI_PAYMENT_AMOUNT']) {
        exit(_('Price changed.'));
    }
    if (!Auth::CheckNickname($_POST['NICKNAME'])) {
        exit(_('Invalid nickname.'));
    }
    if (!Auth::check_world($_POST['WORLD'])) {
        exit(_('Invalid world.'));
    }
    exit('YES');
} elseif (isset($_POST['LMI_HASH'])) {
    $fields = [
        'LMI_PAYEE_PURSE',
        'LMI_PAYMENT_AMOUNT',
        'TYPE',
        'AMOUNT',
        'NICKNAME',
        'WORLD',
        'LMI_MODE',
        'LMI_PAYMENT_NO',
        'LMI_SYS_INVS_NO',
        'LMI_SYS_TRANS_NO',
        'LMI_SYS_TRANS_DATE',
        'LMI_PAYER_PURSE',
        'LMI_PAYER_WM'
    ];
    foreach ($fields as $field) {
        if (!isset($_POST[$field])) {
            exit(_('Invalid request.'));
        }
    }
    $bundle = new PlatinumBundle($_POST['amount']);
    if ($_POST['AMOUNT'] != $bundle->get_amount()) {
        exit(_('Product not available.'));
    }
    if ($bundle->get_price('WM' . $_POST['LMI_PAYEE_PURSE'][0])['price'] > $_POST['LMI_PAYMENT_AMOUNT']) {
        exit(_('Price changed.'));
    }
    if ($_POST['LMI_HASH'] !== strtoupper(hash('sha256',
            implode('',
                [
                    $_POST['LMI_PAYEE_PURSE'],
                    $_POST['LMI_PAYMENT_AMOUNT'],
                    $_POST['LMI_PAYMENT_NO'],
                    0,
                    $_POST['LMI_SYS_INVS_NO'],
                    $_POST['LMI_SYS_TRANS_NO'],
                    $_POST['LMI_SYS_TRANS_DATE'],
                    'CjUPR82u9g4AT3eNjEc87nK9LFNJf2Xuqpmvp4fvk6ngCzz5sw',
                    $_POST['LMI_PAYER_PURSE'],
                    $_POST['LMI_PAYER_WM']
                ])))) {
        exit(_('Checksum mismatch.'));
    }
    $bundle->activate($_POST['NICKNAME'], $_POST['WORLD']);
    exit;
} elseif (isset($_GET['SIGN'])) {
    foreach ([
                 'AMOUNT',
                 'MERCHANT_ORDER_ID',
                 'us_type',
                 'us_amount',
                 'us_nickname',
                 'us_world'
             ] as $field) {
        if (!isset($_GET[$field])) {
            exit(_('Invalid request.'));
        }
    }
    if ($_GET['SIGN'] != md5('36731:' . $_GET['AMOUNT'] . ':7vd0lik9:' . $_GET['MERCHANT_ORDER_ID'])) {
        exit("Signature mismatch.");
    }
    $bundle = new PlatinumBundle($_GET['us_amount']);
    $amount = $bundle->get_amount();
    if ($_GET['us_amount'] != $amount) {
        exit(_('Product not available.'));
    }
    if ($bundle->get_price('WMZ')['price'] > $_GET['AMOUNT']) {
        exit('Price changed.');
    }

    exit(($bundle->activate($_GET['us_nickname'], $_GET['us_world']) === true ? "YES" : "FAILURE"));
}
$doc = new Document(_('Payment'), array(
    array(_('Platinum'), './platinum.php'),
    array(_('Premium'), './premium.php')
));
if (isset($_GET['success'])) {
    $doc->display('payment_success');
} elseif (isset($_GET['fail'])) {
    $doc->display('payment_fail');
} else {
    $form = new Form('purchase');
    $form->addinput('nickname', 'text', 'nickname', 2, 10);
    $form->field('nickname')->validate('Auth::CheckNickname');
    $form->addselect('world', 'world', static function() {
        $worlds = [null => null];
        for ($i = 1; $i <= WORLDS; ++$i) {
            $worlds[$i] = $i;
        }
        return $worlds;
    });
    $form->addselect('currency', 'currency', [
        'FK' => 'FK (' . _('Credit Card') . ', Qiwi, Yandex, ' . _('etc.') . ')',
        'WMR' => 'WMR / ' . _('Ruble') . ' (' . _('Russia') . ')',
        'WMZ' => 'WMZ / ' . _('US dollar') . ' (' . _('other') . ')',
        'WME' => 'WME / ' . _('Euro') . ' (' . _('other') . ')'
    ]);
    $form->addinput('desired_amount', 'number', 'desired_amount');
    $form->addinput('amount', 'hidden', 'amount');
    $form->field('desired_amount')->event('oninput', 'get_platinum_bundle($(this).val(), $("#currency").val())');
    $form->field('desired_amount')->value(100);
    if ($form->submit()) {
        if (in_array($_GET['currency'], ['WMR', 'WMZ', 'WME'])) {
            $doc->assign([
                'LMI_PAYEE_PURSE' => Pricing::PURSES[$_GET['currency']],
                'LMI_PAYMENT_AMOUNT' => Pricing::get_price('platinum', $_GET['amount'], $_GET['currency']),
                'LMI_PAYMENT_DESC_BASE64' => base64_encode('Platinum ' . $_GET['amount'] . ' ' . trim($_GET['nickname']) . ' w' . $_GET['world']),
                'action' => 'https://merchant.' . ($_SESSION['locale'] == 'ru_RU' ? 'webmoney.ru'
                        : 'wmtransfer.com') . '/lmi/payment.asp',
                'method' => 'POST'
            ]);
        } elseif ($_GET['currency'] == 'FK') {
            $sum = Pricing::get_price('platinum', $_GET['amount'], $_GET['currency']);
            $doc->assign([
                'action' => 'https://www.free-kassa.ru/merchant/cash.php',
                'sum' => $sum['price'],
                'desc' => 'Platinum ' . $_GET['amount'] . ' ' . trim($_GET['nickname']) . ' w' . $_GET['world'],
                'sign' => md5('36731:' . $sum['price'] . ':ifb0rudi:' . 'Platinum ' . $_GET['amount'] . ' ' . trim($_GET['nickname']) . ' w' . $_GET['world']),
                'lang' => ($_SESSION['locale'] == 'ru_RU' ? 'ru' : 'en'),
                'method' => 'GET'
            ]);
        } else {
            $doc->display('invalid_request');
            exit;
        }
        $doc->assign([
            'NICKNAME' => trim($_GET['nickname']),
            'WORLD' => $_GET['world'],
            'TYPE' => 'platinum',
            'AMOUNT' => $_GET['amount']
        ]);
        $doc->display('payment_confirm');
    } else {
        $doc->assign('form', $form);
        $doc->display('payment');
    }
}
