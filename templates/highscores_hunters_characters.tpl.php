<h3><?= _('TOP hunters') ?></h3>
<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout primary">
        <label for="world"><?= _('World') ?></label>
        <select id="world" name="world">
            <option value=""><?= _('all') ?></option>
            <? for ($i = 1; $i <= WORLDS; ++$i): ?>
                <option value="<?= $i ?>"<?= ($world == $i ? ' selected' : '') ?>><?= $i ?></option>
            <? endfor; ?>
        </select>
        <label for="vocation"><?= _('Vocation') ?></label>
        <select id="vocation" name="vocation">
            <option value=""><?= _('all') ?></option>
            <option value="warrior"<?= ($vocation == 'warrior' ? ' selected' : '') ?>><?= _('warriors') ?></option>
            <option value="wizard"<?= ($vocation == 'wizard' ? ' selected' : '') ?>><?= _('wizards') ?></option>
        </select>
        <label for="period"><?= _('Period') ?></label>
        <select id="period" name="period">
            <? foreach ($periods as $k => $v): ?>
                <option value="<?= $k ?>"<?= ($k == $period ? ' selected' : '') ?>><?= $v ?></option>
            <? endforeach; ?>
        </select>
        <label for="sort"><?= _('Sort') ?></label>
        <select id="sort" name="sort">
            <option value="gain"><?= _('gained EP') ?></option>
            <option value="loss"<?= ($sort == 'loss') ? ' selected' : '' ?>><?= _('lost EP') ?></option>
            <option value="level"<?= ($sort == 'level') ? ' selected' : '' ?>><?= _('level') ?></option>
        </select>
        <input class="button primary" type="submit" value="<?= _('Confirm') ?>"/>
    </div>
</form>
<table class="text-small-for-small-only">
    <thead>
        <tr>
            <td><?= _('Character') ?></td>
            <td><?= _('Level') ?></td>
            <td><?= _('Experience') ?></td>
        </tr>
    </thead>
    <tbody>
        <? if (empty($data)): ?>
            <tr>
                <td colspan="4"><?= _('No data.') ?></td>
            </tr>
        <? else: ?>
            <? foreach ($data as $key => $array): ?>
                <tr>
                    <td class="nowrap"><?= $array['rank'] ?>.
                        <a href="./viewscores.php?characterID=<?= $array['id'] ?>">
                            <img src="/images/icons/armour_<?= $array['vocation'] ?>.png" alt="<?= $array['vocation'] ?>"/>
                            &nbsp;<?= $array['nickname'] ?>&nbsp;w<?= $array['world'] ?>
                        </a>
                    </td>
                    <td>
                        <?= $array['level'] ?>
                    </td>
                    <td>
                        <?=
                        Scores::ep_format($array['exp_gain'], true)
                        ?>
                    </td>
                </tr>
            <? endforeach; ?>
        <? endif; ?>
    </tbody>
</table>