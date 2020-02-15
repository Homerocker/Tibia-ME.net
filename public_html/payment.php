<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (isset($_POST['LMI_PREREQUEST']) && $_POST['LMI_PREREQUEST'] == 1) {
    $currency = substr($_POST['LMI_PAYEE_PURSE'], 0, 1);
    if ($_POST['AMOUNT'] != GameCodes::get_total(GameCodes::get_bundle($_POST['amount']))) {
        exit(_('Product not available.'));
    }
    $price = Pricing::get_price($_POST['TYPE'], $_POST['AMOUNT'],'WM' . substr($_POST['LMI_PAYEE_PURSE'], 0, 1));
    if ($price > $_POST['LMI_PAYMENT_AMOUNT']) {
        exit(_('Price changed.'));
    }
    $gc = new GameCodes;
    if (($result = $gc->activate($_POST['TYPE'] . ':' . $_POST['AMOUNT'],
            $_POST['NICKNAME'], $_POST['WORLD'], 1, false)) !== true) {
        exit(sprintf(_('Error: %s'), htmlspecialchars($result)));
    } else {
        exit('YES');
    }
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
    if ($_POST['AMOUNT'] != GameCodes::get_total(GameCodes::get_bundle($_POST['amount']))) {
        exit(_('Product not available.'));
    }
    $price = Pricing::get_price($_POST['TYPE'], $_POST['AMOUNT'], 'WM' . substr($_POST['LMI_PAYEE_PURSE'], 0, 1));
    if ($price > $_POST['LMI_PAYMENT_AMOUNT']) {
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
    $gc = new GameCodes;
    $gc->activate($_POST['AMOUNT'], $_POST['NICKNAME'],
            $_POST['WORLD'],true);
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
    if ($_GET['us_amount'] != GameCodes::get_total(GameCodes::get_bundle($_GET['us_amount']))) {
        exit(_('Product not available.'));
    }
    $price = Pricing::get_price($_GET['us_type'], $_GET['us_amount'], 'WMZ');
    if ($price > $_GET['AMOUNT']) {
        exit('Price changed.');
    }
    $gc = new GameCodes;
    $result = $gc->activate($_GET['us_amount'],
            $_GET['us_nickname'], $_GET['us_world'], true);
    exit(($result ? "YES" : "FAILURE"));
}
$doc = new Document(_('Payment'), array(
    array(_('Platinum'), './platinum.php'),
    array(_('Premium'), './premium.php')
));
if (isset($_GET['success'])) {
    $doc->display('payment_success');
} elseif (isset($_GET['fail'])) {
    $doc->display('payment_fail');
} elseif (!isset($_GET['currency']) || !in_array($_GET['currency'],
                ['WMR', 'WMU', 'WMZ', 'WME', 'FK'])) {
    $doc->display('payment_method');
} elseif (!isset($_GET['nickname']) || !isset($_GET['world']) || !isset($_GET['amount'])) {
    $doc->assign([
        'currency' => $_GET['currency']
    ]);
    $doc->display('payment_product');
} elseif (isset($_GET['amount'])) {
    if (in_array($_GET['currency'], ['WMR', 'WMU', 'WMZ', 'WME'])) {
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
            'sum' => $sum,
            'desc' => 'Platinum ' . $amount . ' ' . trim($_GET['nickname']) . ' w' . $_GET['world'],
            'sign' => md5('36731:' . $sum . ':ifb0rudi:' . 'Platinum ' . $amount . ' ' . trim($_GET['nickname']) . ' w' . $_GET['world']),
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
    $doc->display('invalid_request');
}
