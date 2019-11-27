<h3><?= _('What is Platinum?') ?></h3>
<div class="callout primary">
    <?= _('Included') ?>:
    <ul>
        <li><?= _('+50% Experience') ?></li>
        <li><?= _('Possibility to get listed in the Highscores') ?></li>
        <li><?= _('Always get online, even when the game server is full') ?></li>
    </ul>
    <?= _('Buyable') ?>:
    <ul>
        <li><?= _('Life insurance with 50%, 80% or 100% of EP loss protection upon death') ?></li>
        <li><?= _('Full access to individual islands') ?></li>
        <li><?= _('Portal usage') ?></li>
        <li><?= _('Up to 40 additional friendlist slots') ?></li>
        <li><?= _('Up to 40 additional mailbox slots') ?></li>
        <li><?= _('Up to 20 trade offers') ?></li>
        <li><?= _('Up to 6 additional depot pages') ?></li>
        <li><?= _('Additional Rerolls') ?></li>
        <li><?= _('Guild founding') ?></li>
    </ul>
</div>
<h3><?= _('How to buy') ?></h3>
<div class="callout primary">
    <?= sprintf(_('Visit www.tibiame.com or <a href="%s">buy from us</a>. Our prices may differ. We accept WebMoney, PayPal, wire transfers and more! Please check our <a href="%s">User Agreement</a> before you purchase from us.'),
            './payment.php', './user/agreement.php?redirect=' . $_SERVER['PHP_SELF'] . '#payments')
    ?>
    <div class="button-group align-center">
        <a class="button primary" href="./payment.php"><?= _('Purchase') ?></a>
    </div>
</div>
<h3><?= _('Our prices') ?></h3>
<div class="grid-x grid-padding-x grid-padding-y">
    <?php
    if (isset($prices)) {
        foreach ($prices as $amount => $price_array) {
            echo '<div class="cell large-4 medium-4 small-12">';
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<td class="text-center" colspan="2">';
            echo $amount;
            echo '</td>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach ($price_array as $currency => $price) {
                echo '<tr>';
                echo '<td>' . $currency . '</td>';
                echo '<td>' . $price . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        }
    }
    ?>
</div>