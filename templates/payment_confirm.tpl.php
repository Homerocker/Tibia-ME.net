<form action="<?= $action ?>" method="<?= $method ?>">
    <div class="callout primary">
        <?= _('Nickname') ?>: <b><?= $NICKNAME ?></b><br/>
        <?= _('World') ?>: <b><?= $WORLD ?></b><br/>
        <?= _('Product') ?>: <b><?= ucfirst($TYPE) ?> <?= $AMOUNT ?></b><br/>
        <?php
        if ($_GET['currency'] == 'FK') {
            echo '<input type="hidden" name="m" value="36731"/>'
            . '<input type="hidden" name="oa" value="', $sum, '"/>'
            . '<input type="hidden" name="o" value="', htmlspecialchars($desc), '"/>'
            . '<input type="hidden" name="s" value="', $sign, '"/>'
            . '<input type="hidden" name="lang" value="', $lang, '"/>';
        } else {
            echo '<input type="hidden" name="LMI_PAYEE_PURSE" value="', $LMI_PAYEE_PURSE, '"/>'
            . '<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="', $LMI_PAYMENT_AMOUNT, '"/>'
            . '<input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="', htmlspecialchars($LMI_PAYMENT_DESC_BASE64), '"/>';
        }
        echo '<input type="hidden" name="' . ($_GET['currency'] == 'FK' ? 'us_nickname' : 'NICKNAME') . '" value="', $NICKNAME, '"/>'
        . '<input type="hidden" name="' . ($_GET['currency'] == 'FK' ? 'us_world' : 'WORLD') . '" value="', $WORLD, '"/>'
        . '<input type="hidden" name="' . ($_GET['currency'] == 'FK' ? 'us_type' : 'TYPE') . '" value="', $TYPE, '"/>'
        . '<input type="hidden" name="' . ($_GET['currency'] == 'FK' ? 'us_amount' : 'AMOUNT') . '" value="', $AMOUNT, '"/>'
        . '<input class="button primary" type="submit" value="', _('Proceed to payment'), '"/>';
        ?>
    </div>
</form>