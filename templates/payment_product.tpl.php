<form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
    <div class="callout primary">
        <!--<select name="product">
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
                        echo ' (', number_format(round((new Pricing)->get_rate(($_GET['currency'] == 'FK' ? 'WMZ' : $_GET['currency']), 'WME', Pricing::$pricing[$type][$amount]['price']) * (100 - Pricing::$pricing[$type][$amount]['discount_pct']) / 100, 2), 2, '.', ''), ($_GET['currency'] == 'FK' ? 'USD' : $_GET['currency']), ')';
                        echo '</option>';
                    }
                }
            }
            ?>
        </select>-->
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
        <label for="desired_amount"><?= _('Platinum amount') ?></label>
        <input id="desired_amount" type="number" value="100" oninput="get_platinum_bundle($(this).val(), '<?= $_GET['currency'] ?>')"/>
        <div class="text-center b">You will receive <span id="amount_display">0</span> platinum for <span class="nowrap" id="price">USD 0</span>.</div>
        <input type="hidden" id="amount" name="amount" value="100"/>
        <input type="hidden" name="currency" value="<?= $_GET['currency'] ?>"/>
        <input class="button primary" type="submit" value="<?= _('Proceed') ?>"/>
    </div>
</form>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        get_platinum_bundle($("#desired_amount").val(), "<?= $_GET['currency'] ?>");
    });
</script>