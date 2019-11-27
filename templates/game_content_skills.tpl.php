<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout secondary">
        <label for="vocation"><?= _('Vocation') ?></label>
        <select name="vocation" onchange="this.form.submit()">
            <option value="warrior"><?= _('warrior') ?></option>
            <option value="wizard"<?= ($vocation == 'wizard' ? ' selected' : '') ?>><?= _('wizard') ?></option>
        </select>
    </div>
</form>
<? if (empty($data)): ?>
    <div class="callout secondary text-center">
        <?= _('No items to display.') ?>
    </div>
<? else: ?>
    <table class="hover text-small-for-small-only">
        <thead>
        <tr>
            <td><?= _('Skill') ?></td>
            <td><?= _('Level') ?></td>
            <td><?= _('Description') ?></td>
        </tr>
        </thead>
        <tbody>
        <? foreach ($data as $i => $skill): ?>
            <? if ($skill['skill_level'] == 1): ?>
                <tr onclick="toggle('<?= htmlentities(str_replace(' ', '-', $skill['name'])) ?>')" class="pointer">
            <? else: ?>
                <tr class="display-none"></tr>
                <tr class="display-none <?= htmlentities(str_replace(' ', '-', $skill['name'])) ?>">
            <? endif; ?>
            <td<?= ($skill['skill_level'] != 1 ? ' class="text-right"' : '') ?>><?= $skill['name'] ?></td>
            <td><?= $skill['char_level'] ?></td>
            <td><?= _(str_replace('%', '&#37;', $skill['description'])) ?></td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>