<table class="stack-for-small">
    <thead>
        <tr class="text-small">
            <td><?= _('Pet') ?></td>
            <td><?= _('Damage') ?></td>
            <td><?= _('Protection') ?></td>
            <td><?= _('Weakness') ?></td>
            <td><?= _('HP') ?></td>
            <td><?= ('Attack') ?></td>
            <td><?= _('Defense') ?></td>
        </tr>
    </thead>
    <tbody>
<? if (empty($pets)): ?>
        <tr class="text-center">
            <td colspan="7"><?= _('No data.') ?></td>
        </tr>
<? else: ?>
    <? foreach ($pets as $i => $pet): ?>
        <tr>
            <td>
        <? if (isset($pet['icon'])): ?>
            <img src="<?= $pet['icon'] ?>" alt=""/>
        <? endif; ?>
            <?= $pet['name'] ?>
            </td>
            <td>
        <span class="show-for-small-only"><?= _('Damage') ?>: </span>
        <? foreach (DMG_ELEMENTS as $element): ?>
            <? if ($pet['dmg_' . $element]): ?>
                <img src="/images/icons/<?= $element ?>.png" alt="<?= $element ?>"/>
            <? endif; ?>
        <? endforeach; ?>
            </td>
            <td>
        <span class="show-for-small-only"><?= _('Protection') ?>: </span>
        <? foreach (DMG_ELEMENTS as $element): ?>
            <? if ($pet['prot_' . $element]): ?>
                <img src="/images/icons/<?= $element ?>.png" alt="<?= $element ?>"/>
            <? endif; ?>
        <? endforeach; ?>
            </td>
            <td>
        <span class="show-for-small-only"><?= _('Weakness') ?>: </span>
        <? foreach (DMG_ELEMENTS as $element): ?>
            <? if ($pet['weak_' . $element]): ?>
                <img src="/images/icons/<?= $element ?>.png" alt="<?= $element ?>"/>
            <? endif; ?>
        <? endforeach; ?>
            </td>
            <td>
        <?= $pet['stats'][0]['hp'] ?><span class="show-for-small-only"> <?= _('HP') ?></span>
            </td>
            <td>
        <span class="show-for-small-only"><?= _('Attack') ?>: </span>
        <?= $pet['stats'][0]['attack'] ?>
            </td>
            <td>
        <span class="show-for-small-only"><?= _('Defense') ?>: </span>
        <?= $pet['stats'][0]['defense'] ?>
            </td>
        </tr>
    <? endforeach; ?>
<? endif; ?>
    </tbody>
</table>