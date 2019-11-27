<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout primary">
        <select name="month" onchange="this.form.submit()">
            <?php
            for ($i = -1; $i <= 10; ++$i) {
                list($j, $k) = Date::get_m($i);
                echo '<option value="' . $j . '"'.($j == $m ? ' selected="selected"' : '').'>' . Calendar::get_month_name($j) . ' '.$k.'</option>';
            }
            ?>
        </select>
    </div>
</form>
<table class="text-center">
    <thead>
<tr>
    <td class="text-small text-center"><?= _('Mon') ?></td>
    <td class="text-small text-center"><?= _('Tue') ?></td>
    <td class="text-small text-center"><?= _('Wed') ?></td>
    <td class="text-small text-center"><?= _('Thu') ?></td>
    <td class="text-small text-center"><?= _('Fri') ?></td>
    <td class="red text-small text-center"><?= _('Sat') ?></td>
    <td class="red text-small text-center"><?= _('Sun') ?></td>
</tr>
</thead>
<tbody>
<?php
foreach ($layout as $i => $date) {
    if (($i + 1) % 7 == 1) {
        echo '<tr>';
    }
    echo '<td id="' . $i . '" class="'.(($date != null && explode('-', $date)[1] != $m) ? 'text-small grey' : 'b').'">';
    if ($date != null) {
        echo intval(explode('-', $date)[2]);
    }
    echo '</td>';
    if (($i + 1) % 7 == 0) {
        echo '</tr>';
    }
}
echo '</tbody>';
echo '</table>';
echo '<h3>' . _('Upcoming events') . '</h3>';
if (empty($events)) {
    echo '<div class="callout secondary text-center">', _('No upcoming events this month.'), '</div>';
}
foreach ($events as $i => $event) {
    echo '<div class="callout primary">';
    echo '<script type="text/javascript">';
    foreach ($layout as $i => $date) {
        if ($date == $event['start_date'] 
                || ($event['end_date'] !== null 
                && $date > $event['start_date'] 
                && $date <= $event['end_date'])) {
            echo 'document.addEventListener(\'DOMContentLoaded\', function () {';
            echo '$("#' . $i . '").addClass("cal-event");';
            echo '$("#' . $i . '").prop("title", $("#' . $i . '").prop("title")+"\n' . htmlspecialchars($event['title']) . '");';
            echo '});';
        }
    }
    echo '</script>';

    echo '<div class="b">' . htmlspecialchars($event['title']) . '</div>';
    echo User::date(strtotime(implode('-',
                            [
        $event['start_year'] ?? date('Y'),
        $event['start_month'],
        $event['start_day']
            ])), 'd.m.Y');
    if (isset($event['end_day'], $event['end_month'])) {
        echo '&nbsp;–&nbsp;' . User::date(strtotime(implode('-',
                                [
            isset($event['start_year']) ? ($event['end_month'] >= $event['start_month']
                                ? $event['start_year'] : $event['start_year']
                            + 1) : date('Y'),
            $event['end_month'],
            $event['end_day']
                ])), 'd.m.Y');
    }
    if ($event['is_confirmed']) {
        echo '<div class="green" title="' . _('Event date has been officially announced.') . '">' . _('Confirmed') . '</div>';
    } else {
        echo '<div class="red" title="' . _('Event date may change.') . '">' . _('Unconfirmed') . '</div>';
    }
    if (!empty($event['description'])) {
        echo htmlspecialchars($event['description']);
    }
    if (Perms::get(Perms::CALENDAR_EDIT)) {
        echo '<div class="button-group small">';
        echo '<a class="button primary" href="' . $_SERVER['SCRIPT_NAME'] . '?id=' . $event['id'] . '&amp;month=' . $m . '">' . _('Edit') . '</a>';
        echo '<a class="button alert" onclick="return confirm(\''. _('Are you sure you want to delete this event?') .'\')" href="' . $_SERVER['SCRIPT_NAME'] . '?del=' . $event['id'] . '&amp;month=' . $m . '">', _('Delete'), '</a>';
        echo '</div>';
    }
    echo '</div>';
}
if (Perms::get(Perms::CALENDAR_EDIT)) {
    echo '<a class="button primary" href="' . $_SERVER['SCRIPT_NAME'] . '?id&amp;month=' . $m . '">' . _('Add event') . '</a>';
}
?>

