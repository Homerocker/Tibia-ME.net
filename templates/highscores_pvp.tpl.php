<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout primary">
        <label for="world"><?= _('World') ?></label>
        <select id="world" name="world">
            <? for ($i = 1; $i <= WORLDS; ++$i): ?>
                <option value="<?= $i ?>"<?= ($world == $i ? ' selected' : '')
                ?>><?= $i ?></option>
                    <? endfor; ?>
        </select>
        <input class="button primary" type="submit" value="Ok"/>
    </div>
</form>
<table>
    <thead>
        <tr>
            <td><?= _('Character') ?></td>
            <td><?= _('Level') ?></td>
            <td><?= _('Quota') ?></td>
        </tr>
    </thead>
    <tbody>
        <? if (empty($data)): ?>
            <tr>
                <td class="text-center"><?= _('No data.') ?></td>
            </tr>
        <? else: ?>
            <? foreach ($data as $array): ?>
                <tr>
                    <td>
                        <?= $array['rank'] ?>. 
                        <a href="./viewscores.php?characterID=<?= $array['id'] ?>">
                            <img src="/images/icons/armour_<?= $array['vocation'] ?>.png" alt="<?= $array['vocation'] ?>"/>&nbsp;<?= $array['nickname'] ?>
                        </a>
                    </td>
                    <td>
                        <?= $array['level'] ?>
                    </td>
                    <td>
                        <?= $array['quota'] ?>
                    </td>
                </tr>
            <? endforeach; ?>
        <? endif; ?>
    </tbody>
</table>