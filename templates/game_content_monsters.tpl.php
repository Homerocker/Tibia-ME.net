<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout secondary input-group">
        <label for="name" class="input-group-label"><?= _('Name') ?></label>
        <input class="input-group-field" type="text" name="name" value="<?= $name ?>"/>
        <div class="input-group-button">
            <input class="button primary" type="submit" value="<?= _('Search') ?>"/>
        </div>
    </div>
</form>

<table class="stack">
    <thead>
        <tr class="text-small">
            <td><?= _('Monster') ?></td>
            <td><?= _('Award') ?></td>
            <td><?= _('HP') ?></td>
            <td><?= _('Walkspeed') ?></td>
            <td><?= _('Attack') ?></td>
            <td><?= _('Weakness') ?></td>
            <td><?= _('Islands') ?></td>
            <td><?= _('Drops') ?></td>
        </tr>
    </thead>
    <tbody>
<? if (empty($data)): ?>
        <tr>
            <td colspan="9">
                <?= _('No items to display.') ?>
            </td>
        </tr>
<? else: ?>
    <? foreach ($data as $i => $item): ?>
        <tr>
            <td>
        <? if ($item['icon'] !== null): ?>
                <img src="<?= $item['icon'] ?>" alt=""/> 
        <? endif; ?>
                <?= htmlspecialchars($item['name']) ?>
            </td>
            <td>
                <span class="nowrap"><img src="/images/icons/ep.png" alt="<?= _('EP') ?>"><?= $item['exp'] ?></span>
                <span class="nowrap"><img src="/images/icons/gold.gif" alt="<?= _('Gold') ?>"/><?= $item['gold'] ?></span>
            </td>
            <td>
            <?= $item['hp'] ?><span class="hide-for-large"> <?= _('HP') ?></span>
            </td>
            <td>
                <span class="hide-for-large"><?= _('Walkspeed') ?>: </span><?= ($item['walkspeed'] > 0 ? '+' . $item['walkspeed']
                    : $item['walkspeed']) ?>
            </td>
            <td>
                <span class="hide-for-large"><?= _('Attack') ?>: </span>
        <? foreach (DMG_ELEMENTS as $element): ?>
            <? if ($item['attack_' . $element] == 1): ?>
                    <img src="/images/icons/<?= $element ?>.png" alt="<?= $element ?>"/>
            <? endif; ?>
        <? endforeach; ?><br/>
            <? if ($item['spell'] !== null): ?>
                <span class="hide-for-large"><br/><?= _('Spell') ?>: </span>
                <?= $item['spell'] ?>
            <? endif; ?>
            <? if ($item['spell'] !== null || $item['spell_energy'] == 1 || $item['spell_fire']
                        == 1 || $item['spell_hit'] == 1 || $item['spell_soul'] == 1 || $item['spell_ice']
                        == 1): ?>
                <? if ($item['spell'] === null): ?>
                    <span class="hide-for-large"><br/><?= _('Spell') ?>: </span>
                <? endif; ?>
                <? foreach (DMG_ELEMENTS as $element): ?>
                    <? if ($item['spell_' . $element] == 1): ?>
                        <img src="/images/icons/<?= $element ?>.png" alt="<?= $element ?>"/>
                    <? endif; ?>
                <? endforeach; ?>
            <? endif; ?>
            <? if ($item['skill'] !== null): ?>
                <span class="hide-for-large"><br/><?= _('Skill') ?>: </span><?= $item['skill'] ?>
            <? endif; ?>
            </td>
            <td>
                <span class="hide-for-large"><?= _('Weakness') ?>: </span>
            <? foreach (DMG_ELEMENTS as $element): ?>
                <? for ($i = 0; $i < $item['sens_' . $element]; ++$i): ?>
                    <img src="/images/icons/<?= $element ?>.png" alt="<?= $element ?>"/>
                <? endfor; ?>
            <? endforeach; ?>
            </td>
            <td>
            <? if (!empty($item['islands'])): ?>
                <span class="hide-for-large"><?= _('Islands') ?>: </span>
                <? foreach ($item['islands'] as $i => $island):
                    if ($i != 0):
                        echo ', ';
                    endif;
                    echo $island;
                endforeach; ?>
            <? endif; ?>
            </td>
            <td>
            <? if (!empty($item['loot'])): ?>
                <span class="hide-for-large"><?= _('Drops') ?>: </span>
                <? foreach ($item['loot'] as $i => $loot):
                    if ($i != 0):
                        echo ', '; 
                    endif;
                    if ($loot['weapon']):
                        echo '<a href="./weapons.php?name=' . urlencode($loot['item_name']) . '">' . $loot['item_name'] . '</a>';
                    elseif ($loot['armour']):
                        echo '<a href="./armours.php?name=' . urlencode($loot['item_name']) . '">' . $loot['item_name'] . '</a>';
                    else:
                        echo $loot['item_name'];
                    endif;
                endforeach; ?>
            <? endif; ?>
            </td>
        </tr>
    <? endforeach; ?>
<? endif; ?>
    </tbody>
</table>