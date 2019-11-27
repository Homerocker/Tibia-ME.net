<div class="callout primary">
    <?= sprintf(_('Guild: %s'), htmlspecialchars($guild_info['name'])) ?><br/>
    <?= sprintf(_('World: %d'), $guild_info['world']) ?><br/>
    <?= sprintf(_('Rank: %d'), $guild_info['rank']) ?><br/>
    <?= sprintf(_('EP: %d'), $guild_info['exp']) ?>
</div>
<? if (!empty($members)): ?>
<table>
    <caption><?= _('Known guild members') ?></caption>
    <thead class="pointer" onclick="toggle('guild-members', 'expander')">
        <tr>
            <td><?= _('Character') ?></td>
            <td><?= _('Level') ?></td>
            <td><?= _('Rank') ?></td>
        </tr>
    </thead>
    <tbody id="expander" class="text-center pointer" onclick="toggle('guild-members', 'expander')">
        <tr>
            <td colspan="3"><?= _('Click to show') ?></td>
        </tr>
    </tbody>
    <tbody id="guild-members" class="display-none">
        <? foreach ($members as $i => $char): ?>
        <tr>
            <td><img src="/images/icons/armour_<?= $char['vocation'] ?>.png" alt=""/>&nbsp;<a href="./viewscores.php?characterID=<?= $char['id'] ?>"><?= $char['nickname'] ?></a></td>
            <td><?= $char['level'] ?></td>
            <td><?= ($char['rank_global'] ?? '?') ?> (<?= ($char['rank_vocation'] ?? '?') ?>)</td>
        </tr>
        <? endforeach; ?>
    </tbody>
</table>
<? endif; ?>
<table>
    <caption><?= _('Guild history') ?></caption>
    <thead>
        <tr>
            <td><?= _('Date') ?></td>
            <td><?= _('Rank') ?></td>
            <td><?= _('Experience') ?></td>
        </tr>
    </thead>
    <tbody>
        <? if (empty($history)): ?>
            <tr>
                <td colspan="3"><?= _('No data.') ?></td>
            </tr>
        <? else: ?>
            <? foreach ($history as $date => $hist): ?>
                <tr>
                    <td>
                        <?=
                        date('M d', strtotime($date))
                        ?>
                    </td>
                    <td>
                        #<?= ($hist[0]['rank'] ?? '?') ?>
                    </td>
                    <td>
                        <?=
                        Scores::ep_format($hist[0]['exp'] ?? null)
                        ?> <?=
                        Scores::ep_format($hist[23]['exp_gain_daily'] ?? null,
                                true)
                        ?>
                    </td>
                </tr>
            <? endforeach; ?>
        <? endif; ?>
    </tbody>
</table>