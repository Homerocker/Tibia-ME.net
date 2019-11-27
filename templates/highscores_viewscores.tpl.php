<h3><?= $char_data['nickname'] ?>, w<?= $char_data['world'] ?></h3>
<div class="callout primary">
    <img src="/images/icons/armour_<?= $char_data['vocation'] ?>.png" alt="<?= _($char_data['vocation']) ?>"/>&nbsp;<?=
    (($char_data['vocation'] == 'warrior') ? _('warrior') : _('wizard'))
    ?><br/>
    <?php
    if (isset($char_data['level'])) {
        echo '<img src="/images/icons/level.png" alt="' . _('Level') . '"/>&nbsp;', $char_data['level'], '<br/>';
    }
    if (isset($char_data['exp'])) {
        echo '<img src="/images/icons/ep.png" alt="' . _('EP') . '"/>&nbsp;', Scores::ep_format($char_data['exp']), '<br/>';
    }
    if (isset($char_data['exp'])) {
        echo _('Next level'), ': ', Scores::ep_format(Scores::next_level($char_data['exp'])), '<br/>';
    }
    echo _('Global rank'), ': ', (isset($char_data['rank_global']) ? $char_data['rank_global']
        : '?'), ' (<img src="/images/icons/armour_' . $char_data['vocation'] . '.png" alt="' . $char_data['vocation'] . '"/>', (isset($char_data['rank_global_vocation'])
        ? $char_data['rank_global_vocation'] : '?'), ')<br/>';
    echo sprintf(_('w%d rank'), $char_data['world']), ': ', (isset($char_data['rank_world']) ? $char_data['rank_world']
        : '?'), ' (<img src="/images/icons/armour_' . $char_data['vocation'] . '.png" alt="' . $char_data['vocation'] . '"/>', (isset($char_data['rank_world_vocation'])
        ? $char_data['rank_world_vocation'] : '?'), ')<br/>';
    if (isset($char_data['pvp_rank'])) {
        echo _('PvP rank'), ': ' . $char_data['pvp_rank'], '<br/>';
    }
    echo _('Achievements rank'), ': ', (isset($char_data['achievements_rank_global'])
        ? $char_data['achievements_rank_global'] : '?'), ' (<img src="/images/icons/armour_' . $char_data['vocation'] . '.png" alt="' . $char_data['vocation'] . '"/>', (isset($char_data['achievements_rank_vocation'])
        ? $char_data['achievements_rank_vocation'] : '?'), ')';
    if (isset($char_data['achievements_points'])) {
        echo '<br/>&nbsp;&nbsp;', sprintf(ngettext('%d point', '%d points',
            $char_data['achievements_points']),
            $char_data['achievements_points']);
    }
    if (isset($char_data['guild'])) {
        echo '<br/>', _('Guild'), ': <a href="./guilds.php?guild=', $char_data['guild'], '&amp;world=', $char_data['world'], '">', $char_data['guild'], '</a>';
    }
    echo '<br/>';
    if ($char_data['lastupdate'] >= 24) {
        if ($char_data['lastupdate'] >= 72) {
            echo '<span class="red">';
        }
        printf(ngettext('Updated %d day ago.', 'Updated %d days ago.',
            floor($char_data['lastupdate'] / 24)),
            floor($char_data['lastupdate'] / 24));
        if ($char_data['lastupdate'] >= 72) {
            echo '</span>';
        }
    } elseif ($char_data['lastupdate'] >= 1) {
        printf(ngettext('Updated %d hour ago.', 'Updated %d hours ago.',
            $char_data['lastupdate']), $char_data['lastupdate']);
    } else {
        echo _('Updated less than hour ago.');
    }
    ?>
</div>
<? if ($chart_data !== '[]'): ?>
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = <?= $chart_data ?>;
            data.map(function (arr) {
                arr[0] = new Date(arr[0][0], arr[0][1] - 1, arr[0][2]);
                return arr;
            });
            data = google.visualization.arrayToDataTable(data, true);

            var options = {
                'legend': 'none'
            };

            var chart = new google.visualization.LineChart(document.getElementById('chart'));

            chart.draw(data, options);
        }
    </script>
    <div style="height: 400px;" class="callout secondary text-center" id="chart"><?= _('Loading chart data...') ?></div>
<? endif; ?>
<? if (isset($char_data['max_gain_date_daily'])): ?>
    <div class="callout primary">
        <?= _('Max daily gain') ?>:<br/>
        <?= (new DateTime($char_data['max_gain_date_daily']))->format('d.m.Y') ?>
        , <?= $char_data['max_gain_level_daily'] ?>lvl, <?=
        Scores::ep_format($char_data['max_gain_daily'], true)
        ?><br/>
        <?= _('Min daily gain') ?>:<br/>
        <?= (new DateTime($char_data['min_gain_date_daily']))->format('d.m.Y') ?>
        , <?= $char_data['min_gain_level_daily'] ?>lvl, <?=
        Scores::ep_format($char_data['min_gain_daily'], true)
        ?>
        <? foreach ([7, 30, 90, 180] as $period): ?>
        <? if ($char_performance[$period]['gain'] === null): continue; endif; ?>
        <br/><?= sprintf(ngettext('Last %d day', 'Last %d days', $period), $period) ?>
        : <?= Scores::ep_format($char_performance[$period]['gain'], true) ?>
        <? if ($char_performance[$period]['performance'] > 0): ?>
        <span class="green">&uarr;
            <? elseif ($char_performance[$period]['performance'] < 0): ?>
            <span class="red">&darr;
                <? elseif ($char_performance[$period]['performance'] !== null): ?>
                <span class="grey">
            <? endif; ?>
                    <? if ($char_performance[$period]['performance'] !== null): ?>
                        <?= round($char_performance[$period]['performance']) ?>%
                    <? endif; ?>
                    <? if ($char_performance[$period]['performance'] !== null): ?>
                        </span>
            <? endif; ?>
                <? endforeach; ?>
    </div>
<? endif; ?>
<table class="text-small-for-small-only hover">
    <thead>
    <tr>
        <td><?= _('Date') ?></td>
        <td><?= _('Rank') ?></td>
        <td><?= _('Level') ?></td>
        <td><?= _('Experience') ?></td>
        <td class="show-for-medium"></td>
    </tr>
    </thead>
    <tbody>
    <? if (empty($exp_history)): ?>
        <tr>
            <td colspan="4"><?= _('No data.') ?></td>
        </tr>
    <? else: ?>
        <? $i = 0; ?>
        <? foreach ($exp_history as $date => $hist): ?>
            <? ++$i; ?>
            <tr class="pointer" onclick="toggle('<?= $date ?>')">
                <td><span class="nowrap"><?=
                        strftime('%d %b', strtotime($date))
                        ?></span> <span class="nowrap">00:05</span></td>
                <td>#<?= ($hist[0]['rank_global'] ?? '?') ?>&nbsp;<span class="nowrap">(<img
                                src="/images/icons/armour_<?= $char_data['vocation'] ?>.png"
                                alt="<?= $char_data['vocation'] ?>"/><?=
                        ($hist[0]['rank_vocation'] ?? '?')
                        ?>)</span></td>
                <td><?= ($hist[0]['level'] ?? '?')
                    ?></td>
                <td><?=
                    Scores::ep_format($hist[0]['exp'] ?? null)
                    ?><span class="show-for-small-only"> <?=
                        Scores::ep_format($hist[Scores::date() == $date ? Scores::dateg()
                                - 1 : 23]['exp_gain_daily'] ?? null,
                            true)
                        ?><?=
                        (Scores::date() == $date ? ' <span class="label primary">' . sprintf('%02d',
                                Scores::dateg()) . ':05' . '</span>'
                            : '')
                        ?></span></td>
                <td class="show-for-medium">
                    <?=
                    Scores::ep_format($hist[Scores::date() == $date ? Scores::dateg()
                            - 1 : 23]['exp_gain_daily'] ?? null,
                        true)
                    ?><?=
                    (Scores::date() == $date ? ' <span class="label primary">' . sprintf('%02d',
                            Scores::dateg()) . ':05' . '</span>'
                        : '')
                    ?>
                </td>
            </tr>
            <? foreach ($hist as $hour => $hourly): ?>
                <tr class="display-none"></tr>
                <tr class="display-none <?= $date ?>">
                    <td class="text-right"><?= sprintf('%02d', $hour) ?>:05</td>
                    <td>
                        #<?= ($hourly['rank_global'] ?? '?') ?>&nbsp;<span class="nowrap">(<img src="/images/icons/armour_<?= $char_data['vocation'] ?>.png" alt="<?= $char_data['vocation'] ?>"/><?= ($hourly['rank_vocation'] ?? '?') ?>)</span>
                    </td>
                    <td><?= $hourly['level'] ?></td>
                    <td><?= Scores::ep_format($hourly['exp']) ?>
                        <span class="show-for-small-only"> <?= Scores::ep_format($hourly['exp_gain_hourly'], true) ?></span>
                    </td>
                    <td class="show-for-medium">
                        <?= Scores::ep_format($hourly['exp_gain_hourly'], true) ?>
                    </td>
                </tr>
            <? endforeach; ?>
        <? endforeach; ?>
    <? endif; ?>
    </tbody>
</table>