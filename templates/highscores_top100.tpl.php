<h3><?= _('TOP 100') ?></h3>
<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout primary">
        <label for="world"><?= _('World'); ?></label>
        <select id="world" name="world">
            <option value=""><?= _('all') ?></option>
            <? for ($i = 1; $i <= WORLDS; ++$i): ?>
                <option value="<?= $i ?>"<?=
                ($world == $i ? ' selected="selected"' : '')
                ?>><?= $i ?></option>
                    <? endfor; ?>
        </select>
        <label for="vocation"><?= _('Vocation'); ?></label>
        <select id="vocation" name="vocation">
            <option value=""><?= _('all') ?></option>
            <option value="warrior"<?= ($vocation == 'warrior') ? ' selected' : ''
                    ?>><?= _('warriors') ?></option>
            <option value="wizard"<?= ($vocation == 'wizard') ? ' selected' : ''
                    ?>><?= _('wizards') ?></option>
        </select>
        <input class="button primary" type="submit" value="Ok"/>
    </div>
</form>
<table class="stack-for-small">
    <thead>
        <tr>
            <td><?= _('Character') ?></td>
            <td><?= _('Experience') ?></td>
            <td><?= _('Level') ?></td>
        </tr>
    </thead>
    <tbody>
        <? if (empty($data)): ?>
            <tr>
                <td colspan="4" class="text-center">
                    <?= _('No data.') ?>
                </td>
            </tr>
        <? else: ?>
            <? foreach ($data as $key => $char): ?>
                <tr>
                    <td>
                        <?= ($char['rank'] ?? '?') ?>.&nbsp;
                        <a href="./viewscores.php?characterID=<?= $char['id'] ?>"><img src="/images/icons/armour_<?= $char['vocation'] ?>.png" alt="<?= $char['vocation'] ?>"/>&nbsp;<?= $char['nickname'] ?>,&nbsp;w<?= $char['world'] ?></a>
                    </td>
                    <td><img class="show-for-small-only" src="/images/icons/ep.png" alt="<?= _('EP') ?>"/><?= Scores::ep_format($char['exp']) ?></td>
                    <td><img class="show-for-small-only" src="/images/icons/level.png" alt="<?= _('Level') ?>"/><?= $char['level'] ?></div>
                </tr>
            <? endforeach; ?>
        <? endif; ?>
    </tbody>
</table>