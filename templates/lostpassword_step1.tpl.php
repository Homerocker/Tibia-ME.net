<h3><?= _('Recover password') ?></h3>
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
    <div class="callout primary">
        <label for="nickname"><?= _('Nickname') ?></label>
        <input type="text" id="nickname" name="nickname" maxlength="10"/>
        <label for="world"><?= _('World') ?></label>
        <select id="world" name="world">
            <option value=""><?= _('select world') ?></option>
            <? for ($i = 1; $i <= WORLDS; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
            <? endfor; ?>
        </select>
        <label for="email">Email</label>
        <input type="text" id="email" name="email" maxlength="64"/>
        <input class="button primary" type="submit" value="<?= _('Confirm') ?>"/>
    </div>
</form>