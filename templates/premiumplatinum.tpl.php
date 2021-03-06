<h3><?= _('What is VIP?') ?></h3>
<div class="callout primary">
    <?= _('VIP is <b>permanent</b> character status that you receive after any purchase (any amount of Platinum or Premium).') ?>
    <ul>
        <li><?= _('Priority Login') ?></li>
        <li><?= _('Stamina Bonus (+50% Experience)') ?></li>
        <li><?= _('Highscores') ?></li>
    </ul>
</div>
<h3><?= _('What is Premium?') ?></h3>
<div class="callout primary">
    <?= _('Premium is renewable subscription that gives you <b>temporary</b> access to most of game features. When your Premium time expires your character remains VIP.') ?>
    <ul>
        <li><?= _('Full access to additional islands') ?></li>
        <li><?= _('Portal usage') ?></li>
        <li><?= _('20 friendlist slots') ?></li>
        <li><?= _('20 ignorelist slots') ?></li>
        <li><?= _('20 letters in Mailbox') ?></li>
        <li><?= _('10 trade offers') ?></li>
        <li><?= _('9 depot pages') ?></li>
        <li><?= _('50% of EP loss protection upon death (can be upgraded to 80% or 100%)') ?></li>
        <li><?= _('Guild Founding') ?></li>
        <li><?= _('8 Platinum per day of your Premium time (Example: 30 days * 8 Platinum = 240 Platinum)') ?></li>
    </ul>
</div>
<h3><?= _('What is Platinum?') ?></h3>
<div class="callout primary">
    <?= _('Platinum is game currency that allows you to buy <b>permanent</b> access to game features of your choice.') ?>
    <ul>
        <li><?= _('Buy permanent access to individual islands') ?></li>
        <li><?= _('Buy portal usage permanently') ?></li>
        <li><?= _('Buy up to 50 friendlist slots') ?></li>
        <li><?= _('Buy up to 50 letters in Mailbox') ?></li>
        <li><?= _('Buy up to 20 trade offers') ?></li>
        <li><?= _('Buy up to 9 depot pages') ?></li>
        <li><?= _('Buy a life insurance with 50%, 80% or 100% of EP loss protection upon death') ?></li>
        <li><?= _('Buy additional Rerolls whenever you need them') ?></li>
        <li><?= _('Buy guild founding') ?></li>
    </ul>
</div>

<div class="button-group align-center">
    <? if (!empty(GameCodes::get_codes_available())): ?>
        <a class="button primary" href="./payment.php"><? printf(_('Buy on %s'), 'tibia-me.net') ?></a>
    <? endif; ?>
    <a class="button primary" target="_blank"
       href="https://payments.cipsoft.com/tibiame/?section=payment"><? printf(_('Buy on %s'), 'tibiame.com') ?></a>
</div>
<? $codes = GameCodes::get_overview() ?>
<? if (!empty($codes)): ?>
    <h3><?= _('Our prices') ?></h3>
    <div class="grid-x grid-padding-x grid-padding-y">
        <?php
        foreach (Pricing::PRICES as $amount => $price_array) {
            if (!isset($codes[$amount])) {
                continue;
            }
            echo '<div class="cell large-4 medium-4 small-6">';
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<td class="text-center" colspan="2">';
            printf(_('%d Platinum'), $amount);
            echo '</td>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach (['WMR', 'WMZ', 'WME'] as $currency) {
                echo '<tr>';
                echo '<td>' . Pricing::get_ISO_currency_code($currency) . '</td>';
                echo '<td>';
                $price = Pricing::get_price($amount, $currency);
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
<? endif; ?>