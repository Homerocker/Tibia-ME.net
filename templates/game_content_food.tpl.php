<table class="text-small-for-small-only">
    <caption><?= _('Food and potions') ?></caption>
    <thead>
        <tr>
            <td><?= _('Item') ?></td>
            <td><?= _('Effect') ?></td>
            <td><?= _('Duration') ?></td>
            <td><?= _('Description') ?></td>
        </tr>
    </thead>
    <tbody>
<? if (empty($data)): ?>
        <tr class="text-center">
            <td colspan="4"><?= _('No items to display.') ?></td>
        </tr>
<? else: ?>
    <? foreach ($data as $i => $food): ?>
        <tr>
            <td>
            <? if ($food['icon'] !== null): ?>
                <img src="<?= $food['icon'] ?>" alt=""/>
            <? endif; ?>
            <?= htmlspecialchars($food['name']) ?>
            <? if ($food['vocation'] !== null): ?>
                <img src="/images/icons/armr_<?= substr($food['vocation'], 0, 3) ?>.gif" alt="<?= $food['vocation'] ?>"/>
            <? endif; ?>
            </td>
            <td>
            <? if ($food['hp'] !== '0'): ?>
                <span class="nowrap"><img src="/images/icons/hp.gif" alt="HP"/><?= $food['hp'] ?? '100%' ?></span>
            <? endif; ?>
            <? if ($food['mp'] !== '0'): ?>
                <? if ($food['hp'] != '0'):
                    echo ' ';
                endif; ?>
                <span class="nowrap"><img src="/images/icons/mana.gif" alt="<?= _('Mana') ?>"/><?= $food['mp'] ?? '100%' ?></span>
            <? endif; ?>
            </td>
            <td>
            <? if (!empty($food['duration'])): ?>
                <?= _($food['duration']) ?>
            <? endif; ?>
            </td>
            <td class="text-small-for-small-only">
            <? if (!empty($food['description'])): ?>
                <?= _($food['description']) ?>
            <? endif; ?>
            </td>
        </tr>
    <? endforeach; ?>
<? endif; ?>
    </tbody>
</table>