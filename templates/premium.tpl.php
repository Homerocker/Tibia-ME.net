<h3><?= _('What is Premium?') ?></h3>
<div class="callout primary">
    <ul>
        <li><?= _('+50% Experience') ?></li>
        <li><?= _('Life insurance with 50% of EP loss protection upon death (can be upgraded to 80% or 100%)') ?></li>
        <li><?= _('Full access to all islands') ?></li>
        <li><?= _('Portal usage') ?></li>
        <li><?= _('Possibility to get listed in the Highscores') ?></li>
        <li><?= _('Guild founding') ?></li>
        <li><?= _('More entries in your friends and ignorelist (20 instead of 10)') ?></li>
        <li><?= _('Receive up to 20 letters in your inbox (instead of 10)') ?></li>
        <li><?= _('Always get online, even when the game server is full') ?></li>
        <li><?= _('10 trade offers') ?></li>
        <li><?= _('8 Platinum per day of your Premium Time (Example: 30 days * 8 Platinum = 240 Platinum)') ?></li>
    </ul>
</div>
<h3><?= _('How to buy') ?></h3>
<div class="callout primary">
    <?=
    sprintf(_('Visit www.tibiame.com or <a href="%s">contact us</a>. Our prices may differ. We accept WebMoney, PayPal and bank transfers. Please check our <a href="%s">User Agreement</a> before you purchase from us.'),
            './contacts.php',
            './user/agreement.php?redirect=' . $_SERVER['PHP_SELF'] . '#payments')
    ?>
</div>
<h3><?= _('Our prices') ?></h3>
<div class="grid-x grid-padding-x grid-padding-y">
    <?php

    foreach (Pricing::PRICES['premium'] as $amount => $price_array) {
        echo '<div class="cell large-3 medium-6 small-6">';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<td class="text-center" colspan="2">';
        echo $amount;
        echo '</td>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach (['WMR', 'WMU', 'WMZ', 'WME'] as $currency) {
            echo '<tr>';
            echo '<td>' . Pricing::get_ISO_currency_code($currency) . '</td>';
            echo '<td>';
            $price = Pricing::get_price('premium', $amount, $currency);
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
