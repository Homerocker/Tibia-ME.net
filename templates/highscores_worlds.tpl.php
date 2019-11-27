<? if (isset($world)): ?>
    <h3><?= date('F d', strtotime(Scores::date(-1))) ?></h3>
    <? if (empty($data)): ?>
        <div class="callout primary text-center"><?= _('No data.') ?></div>
    <? else: ?>
        <div class="callout primary">
            <?= _('World') ?>: <?= $world ?><br/>
            <img src="/images/icons/ep.png" alt="<?= _('EP') ?>"/>&nbsp;<?=
            Scores::ep_format($data['gain'], true)
            ?><br/>
            &nbsp;&nbsp;&nbsp;&nbsp;<?=
            sprintf(ngettext('%d character', '%d characters',
                            $data['characters']), $data['characters'])
            ?><br/>
            <?= _('Max gain') ?>: <?=
            Scores::ep_format($data['maxGain'], true)
            ?><br/>
            &nbsp;&nbsp;&nbsp;&nbsp;<?= date('d.m.Y', strtotime($data['maxGainDate'])) ?><br/>
            <?= _('Min gain') ?>: <?=
            Scores::ep_format($data['minGain'], true)
            ?><br/>
            &nbsp;&nbsp;&nbsp;&nbsp;<?= date('d.m.Y', strtotime($data['minGainDate'])) ?>
        </div>
    <? endif; ?>
<? else: ?>
    <table>
        <caption><?= date('F d', strtotime(Scores::date(-1))) ?></caption>
        <thead>
            <tr>
                <td><?= _('World') ?></td>
                <td><?= _('Experience') ?></td>
                <td><?= _('Characters') ?></td>
            </tr>
        </thead>
        <tbody>
            <? if (empty($data)): ?>
                <tr>
                    <td class="text-center" colspan="3"><?= _('No data.') ?></td>
                </tr>
            <? endif; ?>
            <? foreach ($data as $array): ?>
                <tr>
                    <td>
                        <a href = "<?= $_SERVER['SCRIPT_NAME'] ?>?world=<?= $array['world'] ?>">
                            w<?= $array['world'] ?>
                        </a>
                    </td>
                    <td>
                        <?=
                        Scores::ep_format($array['gain'], true)
                        ?>
                    </td>
                    <td>
                        <?= $array['characters'] ?>
                    </td>
                </tr>
            <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>