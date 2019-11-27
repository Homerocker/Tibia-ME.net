<h3>
    <?= ($event['id'] == null ? _('Add event') : _('Edit event')) ?>
</h3>
<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <label for="title"><?= _('Title') ?></label>
        <input id="title" type="text" name="title" value="<?= $event['title'] ?>"/>
        <label for="description"><?= _('Description') ?> (<?= _('optional') ?>)</label>
        <textarea id="description" name="description" rows="5"><?= $event['description'] ?></textarea>
        <label for="start_day"><?= _('Start date') ?></label>
        <div class="input-group">
            <select class="input-group-field" name="start_day" id="start_day">
                <?php
                for ($i = 1; $i <= 31; ++$i) {
                    echo '<option value="' . $i . '"' . ($i == $event['start_day']
                                ? ' selected="selected"' : '') . '>' . $i . '</option>';
                }
                ?>
            </select>
            <select class="input-group-field" name="start_month" class="bl">
                <?php
                for ($i = 1; $i <= 12; ++$i) {
                    echo '<option value="' . $i . '"' . ($i == $event['start_month']
                                ? ' selected="selected"' : '') . '>';
                    echo Calendar::get_month_name($i);
                    echo '</option>';
                }
                ?>
            </select>
            <select class="input-group-field" name="start_year" class="bl">
                <option value="0"><?= _('every year') ?></option>
                <?php
                for ($i = 2003; $i <= date('Y') + 1; ++$i) {
                    echo '<option value="' . $i . '"' . ($i == $event['start_year']
                                ? ' selected="selected"' : '') . '>' . $i . '</option>';
                }
                ?>
            </select>
        </div>
        <label for="end_day"><?= _('End date') ?></label>
        <div class="input-group">
            <select class="input-group-field" name="end_day" id="end_day">
                <option value="">–</option>
                <?php
                for ($i = 1; $i <= 31; ++$i) {
                    echo '<option value="' . $i . '"' . ($i == $event['end_day']
                                ? ' selected="selected"' : '') . '>' . $i . '</option>';
                }
                ?>
            </select>
            <select class="input-group-field" name="end_month" class="bl">
                <option value="">–</option>
                <?php
                for ($i = 1; $i <= 12; ++$i) {
                    echo '<option value="' . $i . '"' . ($i == $event['end_month']
                                ? ' selected="selected"' : '') . '>';
                    switch ($i) {
                        case 1:
                            echo _('January');
                            break;
                        case 2:
                            echo _('February');
                            break;
                        case 3:
                            echo _('March');
                            break;
                        case 4:
                            echo _('April');
                            break;
                        case 5:
                            echo _('May');
                            break;
                        case 6:
                            echo _('June');
                            break;
                        case 7:
                            echo _('July');
                            break;
                        case 8:
                            echo _('August');
                            break;
                        case 9:
                            echo _('September');
                            break;
                        case 10:
                            echo _('October');
                            break;
                        case 11:
                            echo _('November');
                            break;
                        case 12:
                            echo _('December');
                            break;
                    }
                    echo '</option>';
                }
                ?>
            </select>
        </div>
        <label for="is_confirmed"><?= _('Event date is confirmed') ?></label>
        <div class="switch">
            <input class="switch-input" type="checkbox" id="is_confirmed" name="is_confirmed"<?= ($event['is_confirmed']
                            ? ' checked' : '') ?>/>
            <label class="switch-paddle" for="is_confirmed">
            </label>
        </div>
        <input type="hidden" name="id" value="<?= $event['id'] ?>"/>
        <input type="hidden" name="month" value="<?= $m ?>"/>
        <div class="button-group">
            <input class="button primary" type="submit" value="<?= _('Save') ?>"/>
            <a class="button warning" href="<?= $_SERVER['SCRIPT_NAME'] ?>?month=<?= $m ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>