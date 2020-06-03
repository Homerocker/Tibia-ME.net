<h3><?= _('What is VIP?') ?></h3>
<div class="callout primary">
    VIP is <b>permanent</b> character status that you receive after any purchase (any amount of Platinum or Premium).
    <ul>
        <li>Priority Login</li>
        <li>Stamina Bonus (+50% Experience)</li>
        <li>Highscores</li>
    </ul>
</div>
<h3><?= _('What is Premium?') ?></h3>
<div class="callout primary">
    Premium is renewable subscription that gives you <b>temporary</b> access to most of game features. When your Premium time expires
    your character remains VIP.
    <ul>
        <li>Full access to additional islands</li>
        <li>Portal usage</li>
        <li>20 friendlist slots</li>
        <li>20 ignorelist slots</li>
        <li>20 letters in Mailbox</li>
        <li>10 trade offers</li>
        <li>9 depot pages</li>
        <li>50% of EP loss protection upon death (can be upgraded to 80% or 100%)</li>
        <li>Guild Founding</li>
        <li>8 Platinum per day of your Premium Time! (Example: 30 days * 8 Platinum = 240 Platinum)</li>
    </ul>
</div>
<h3><?= _('What is Platinum?') ?></h3>
<div class="callout primary">
    Platinum is game currency that allows you to buy <b>permanent</b> access to game features of your choice.
    <ul>
        <li>Buy permanent access to individual islands</li>
        <li>Buy portal usage permanently</li>
        <li>Buy up to 50 friendlist slots</li>
        <li>Buy up to 50 letters in Mailbox</li>
        <li>Buy up to 20 trade offers</li>
        <li>Buy up to 9 depot pages</li>
        <li>Buy a life insurance with 50%, 80% or 100% of EP loss protection upon death</li>
        <li>Buy additional Rerolls whenever you need them</li>
        <li>Buy guild founding</li>
    </ul>
</div>

<h3><?= _('How to buy') ?></h3>
<div class="callout primary">

    <?= sprintf(_('Visit %s or <a href="%s">buy from us</a>. Our prices may differ. We accept WebMoney, PayPal, wire transfers and more! Please check our <a href="%s">User Agreement</a> before you purchase from us.'),
        '<a href="https://payments.cipsoft.com/tibiame/?section=payment" target="_blank">www.tibiame.com</a>', './payment.php', './user/agreement.php?redirect=' . $_SERVER['PHP_SELF'] . '#payments')
    ?>
    <div class="button-group align-center">
        <a class="button primary" href="./payment.php"><?= _('Purchase') ?></a>
    </div>
</div>
<h3><?= _('Our prices') ?></h3>
<div class="grid-x grid-padding-x grid-padding-y">
    <?php
    foreach (Pricing::PRICES['platinum'] as $amount => $price_array) {
        echo '<div class="cell large-4 medium-4 small-6">';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<td class="text-center" colspan="2">';
        echo $amount;
        echo '</td>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach (['WMR', 'WMZ', 'WME'] as $currency) {
            echo '<tr>';
            echo '<td>' . Pricing::get_ISO_currency_code($currency) . '</td>';
            echo '<td>';
            $price = Pricing::get_price('platinum', $amount, $currency);
            echo $price['price'];
            if ($price['discount_pct']) {
                echo ' <span class="small">(-' . $price['discount_pct'] . '%)</span>';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
    ?>
</div>