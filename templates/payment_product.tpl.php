<form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
    <div class="callout primary">
        <?= _('If desired product is not listed here you may contact us to purchase it with different payment method.') ?><br/>
        <select name="product">
            <?php
            if (empty($products)) {
                echo '<option value="">',_('No items to display.'),'</option>';
            } else {
                foreach ($products as $type => $codes) {
                    foreach (array_keys($codes) as $amount) {
                        echo '<option value="', $type, ':', $amount, '">';
                        if ($type === 'premium') {
                            echo sprintf(ngettext('%d day Premium', '%d days Premium', $amount), $amount);
                        } else {
                            echo sprintf(_('%d Platinum'), $amount);
                        }
                        echo ' (', number_format(round((new Pricing)->get_rate(($_GET['currency'] == 'FK' ? 'WMZ' : $_GET['currency']), Pricing::$pricing[$type][$amount]['currency'], Pricing::$pricing[$type][$amount]['price']) * (100 - Pricing::$pricing[$type][$amount]['discount_pct']) / 100, 2), 2, '.', ''), ($_GET['currency'] == 'FK' ? 'USD' : $_GET['currency']), ')';
                        echo '</option>';
                    }
                }
            }
            ?>
        </select>
        <label for="nickname"><?= _('Nickname') ?></label>
        <input id="nickname" type="text" name="nickname" maxlength="10"/>
        <label for="world"><?= _('World') ?></label>
        <select id="world" name="world">
            <option></option>
            <?php
            for ($i = 1; $i <= WORLDS; ++$i) {
                echo '<option value="', $i, '">', $i, '</option>';
            }
            ?>
        </select>
        <input type="hidden" name="currency" value="<?= $_GET['currency'] ?>"/>
        <input class="button primary" type="submit" value="<?= _('Proceed') ?>"/>
    </div>
</form>