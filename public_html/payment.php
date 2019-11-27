<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (isset($_POST['LMI_PREREQUEST']) && $_POST['LMI_PREREQUEST'] == 1) {
    $currency = substr($_POST['LMI_PAYEE_PURSE'], 0, 1);
    if (!isset(Pricing::$pricing[$_POST['TYPE']][$_POST['AMOUNT']])) {
        exit(_('Invalid product.'));
    }
    $price = number_format(round((new Pricing)->get_rate('WM' . substr($_POST['LMI_PAYEE_PURSE'],
                                    0, 1),
                            Pricing::$pricing[$_POST['TYPE']][$_POST['AMOUNT']]['currency'],
                            Pricing::$pricing[$_POST['TYPE']][$_POST['AMOUNT']]['price'])
                    * (100 - Pricing::$pricing[$_POST['TYPE']][$_POST['AMOUNT']]['discount_pct'])
                    / 100, 2), 2, '.', '');
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
    if (!isset(Pricing::$pricing[$_POST['TYPE']][$_POST['AMOUNT']])) {
        exit(_('Invalid request.'));
    }
    $price = number_format(round((new Pricing)->get_rate('WM' . substr($_POST['LMI_PAYEE_PURSE'],
                                    0, 1),
                            Pricing::$pricing[$_POST['TYPE']][$_POST['AMOUNT']]['currency'],
                            Pricing::$pricing[$_POST['TYPE']][$_POST['AMOUNT']]['price'])
                    * (100 - Pricing::$pricing[$_POST['TYPE']][$_POST['AMOUNT']]['discount_pct'])
                    / 100, 2), 2, '.', '');
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
    $gc->activate($_POST['TYPE'] . ':' . $_POST['AMOUNT'], $_POST['NICKNAME'],
            $_POST['WORLD'], 1, true);
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
    if (!isset(Pricing::$pricing[$_GET['us_type']][$_GET['us_amount']])) {
        exit("Price not found.");
    }
    $price = number_format(round((new Pricing)->get_rate('WMZ',
                            Pricing::$pricing[$_GET['us_type']][$_GET['us_amount']]['currency'],
                            Pricing::$pricing[$_GET['us_type']][$_GET['us_amount']]['price'])
                    * (100 - Pricing::$pricing[$_GET['us_type']][$_GET['us_amount']]['discount_pct'])
                    / 100, 2), 2, '.', '');
    if ($price > $_GET['AMOUNT']) {
        exit("Low amount.");
    }
    $gc = new GameCodes;
    $result = $gc->activate($_GET['us_type'] . ':' . $_GET['us_amount'],
            $_GET['us_nickname'], $_GET['us_world'], 1, true);
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
} elseif (!isset($_GET['nickname']) || !isset($_GET['world']) || !isset($_GET['product'])) {
    $doc->assign([
        'currency' => $_GET['currency'],
        'products' => (new GameCodes)->get_overview()
    ]);
    $doc->display('payment_product');
} elseif (isset($_GET['product']) && strpos($_GET['product'], ':') !== false && count(list($type, $amount)
        = explode(':', $_GET['product'])) == 2 && isset(Pricing::$pricing[$type][$amount])) {
    $pricing = new Pricing;
    switch ($_GET['currency']) {
        case 'WMR':
            $doc->assign([
                'LMI_PAYEE_PURSE' => 'R161889717079',
                'LMI_PAYMENT_AMOUNT' => number_format(round($pricing->get_rate($_GET['currency'],
                                        Pricing::$pricing[$type][$amount]['currency'],
                                        Pricing::$pricing[$type][$amount]['price'])
                                * (100 - Pricing::$pricing[$type][$amount]['discount_pct'])
                                / 100, 2), 2, '.', '')
            ]);
            break;
        case 'WMU':
            $doc->assign([
                'LMI_PAYEE_PURSE' => 'U425132255059',
                'LMI_PAYMENT_AMOUNT' => number_format(round($pricing->get_rate($_GET['currency'],
                                        Pricing::$pricing[$type][$amount]['currency'],
                                        Pricing::$pricing[$type][$amount]['price'])
                                * (100 - Pricing::$pricing[$type][$amount]['discount_pct'])
                                / 100, 2), 2, '.', '')
            ]);
            break;
        case 'WMZ':
            $doc->assign([
                'LMI_PAYEE_PURSE' => 'Z264253741048',
                'LMI_PAYMENT_AMOUNT' => number_format(round($pricing->get_rate($_GET['currency'],
                                        Pricing::$pricing[$type][$amount]['currency'],
                                        Pricing::$pricing[$type][$amount]['price'])
                                * (100 - Pricing::$pricing[$type][$amount]['discount_pct'])
                                / 100, 2), 2, '.', '')
            ]);
            break;
        case 'WME':
            $doc->assign([
                'LMI_PAYEE_PURSE' => 'E192093820321',
                'LMI_PAYMENT_AMOUNT' => number_format(round($pricing->get_rate($_GET['currency'],
                                        Pricing::$pricing[$type][$amount]['currency'],
                                        Pricing::$pricing[$type][$amount]['price'])
                                * (100 - Pricing::$pricing[$type][$amount]['discount_pct'])
                                / 100, 2), 2, '.', '')
            ]);
            break;
        case 'FK':
            $sum = number_format(round($pricing->get_rate('WMZ',
                                    Pricing::$pricing[$type][$amount]['currency'],
                                    Pricing::$pricing[$type][$amount]['price']) * (100
                            - Pricing::$pricing[$type][$amount]['discount_pct'])
                            / 100, 2), 2, '.', '');
            $doc->assign([
                'action' => 'https://www.free-kassa.ru/merchant/cash.php',
                'sum' => $sum,
                'desc' => ucfirst($type) . ' ' . $amount . ' ' . trim($_GET['nickname']) . ' w' . $_GET['world'],
                'sign' => md5('36731:' . $sum . ':ifb0rudi:' . ucfirst($type) . ' ' . $amount . ' ' . trim($_GET['nickname']) . ' w' . $_GET['world']),
                'lang' => $_SESSION['locale'] == 'ru_RU' ? 'ru' : 'en',
                'method' => 'GET'
            ]);
            break;
        default:
            $doc->display('invalid_request');
            exit;
    }
    if (in_array($_GET['currency'], ['WMR', 'WMU', 'WMZ', 'WME'])) {
        $doc->assign([
            'LMI_PAYMENT_DESC_BASE64' => base64_encode(ucfirst($type) . ' ' . $amount . ' ' . trim($_GET['nickname']) . ' w' . $_GET['world']),
            'action' => 'https://merchant.' . ($_SESSION['locale'] == 'ru_RU' ? 'webmoney.ru'
                        : 'wmtransfer.com') . '/lmi/payment.asp',
            'method' => 'POST'
        ]);
    }
    $doc->assign([
        'NICKNAME' => trim($_GET['nickname']),
        'WORLD' => $_GET['world'],
        'TYPE' => $type,
        'AMOUNT' => $amount
    ]);
    $doc->display('payment_confirm');
} else {
    $doc->display('invalid_request');
}
