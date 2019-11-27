<form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
    <div class="callout primary">
        <label for="currency"><?= _('Payment method') ?></label>
        <select id="currency" name="currency">
            <option value="FK">FK (<?= _('Credit Card') ?>, Qiwi, Yandex, <?= _('etc.') ?>)</option>
            <option value="WMR">WMR / <?= _('Ruble') ?> (<?= _('Russia') ?>)</option>
            <option value="WMU">WMU / <?= _('Hryvnia') ?> (<?= _('Ukraine') ?>)</option>
            <option value="WMZ">WMZ / <?= _('US dollar') ?> (<?= _('other') ?>)</option>
            <option value="WME">WME / <?= _('Euro') ?> (<?= _('other') ?>)</option>
        </select>
        <input class="button primary" type="submit" value="<?= _('Proceed') ?>"/>
    </div>
</form>