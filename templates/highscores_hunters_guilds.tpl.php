<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout primary">
        <label for="world"><?= _('World') ?></label>
        <select id="world" name="world">
            <option value=""><?= _('all') ?></option>
            <? for ($i = 1; $i <= WORLDS; ++$i): ?>
                <option value="<?= $i ?>"<?= ($world == $i ? ' selected' : '') ?>><?= $i ?></option>
            <? endfor; ?>
        </select>
        <label for="period"><?= _('Period') ?></label>
        <select id="period" name="period">
            <? foreach ($periods as $k => $v): ?>
                <option value="<?= $k ?>"<?= ($k == $period ? ' selected' : '') ?>><?= $v ?></option>
            <? endforeach; ?>
        </select>
        <input type="hidden" name="type" value="guilds"/>
        <input class="button primary" type="submit" value="<?= _('Confirm') ?>"/>
    </div>
</form>
<table>
    <thead>
        <tr>
            <td><?= _('Guild') ?></td>
            <td><?= _('Experience') ?></td>
        </tr>
    </thead>
    <tbody>
        <? if (empty($data)): ?>
        <tr>
            <td class="text-center" colspan="2"><?= _('No data.') ?></td>
        </tr>
        <? else: ?>
            <? foreach ($data as $array): ?>
                <tr>
                    <td class="nowrap">
                        <?= $array['rank'] ?>. 
                        <a href="./guilds.php?guild=<?= $array['name'] ?>&amp;world=<?= $array['world'] ?>">
                            <?= $array['name'] ?>&nbsp;w<?= $array['world'] ?>
                </a>
                    </td>
                    <td>
                        <?= Scores::ep_format($array['exp']) ?> 
                        <?= Scores::ep_format($array['exp_gain'], true) ?>
                    </td>
                </tr>
            <? endforeach; ?>
        <? endif; ?>
    </tbody>
</table>