<h3><?= _('Guilds') ?></h3>
<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout primary">
        <label for="world"><?= _('World') ?></label>
        <select id="world" name="world">
            <option value=""><?= _('all') ?></option>
            <? for ($i = 1; $i <= WORLDS; ++$i): ?>
                <option value="<?= $i ?>"<? ($i == $world ? ' selected' : '')
                ?>><?= $i ?></option>
                    <? endfor; ?>
        </select>
        <input class="button primary" type="submit" value="<?= _('Confirm') ?>"/>
    </div>
</form>
<table>
    <thead>
        <tr>
            <td><?= _('Rank') ?></td>
            <td><?= _('Guild') ?></td>
            <td><?= _('World') ?></td>
            <td><?= _('Experience') ?></td>
        </tr>
    </thead>
    <tbody>
        <? if (empty($data)): ?>
            <tr>
                <td class="text-center" colspan="4"><?= _('No data.') ?></td>
            </tr>
        <? else: ?>
            <? foreach ($data as $key => $guild): ?>
                <tr>
                    <td>#<?= $guild['rank'] ?></td>
                    <td>
                        <a href="./guilds.php?guild=<?= $guild['name'] ?>&amp;world=<?= $guild['world'] ?>"><?= $guild['name'] ?></a>
                    </td>
                    <td><?= $guild['world'] ?></td>
                    <td><?= Scores::ep_format($guild['exp']) ?></td>
                </tr>
            <? endforeach; ?>
        <? endif; ?>
    </tbody>
</table>