<form action="<?= $_SERVER['SCRIPT_NAME']; ?>" method="post">
    <div class="callout primary">
        <label for="message"><?= _('Message') ?></label>
        <textarea id="message" name="message" rows="5"><?= htmlspecialchars($message, ENT_COMPAT, 'UTF-8') ?></textarea>
        <label for="time"><?= _('Time') ?></label>
        <div class="input-group">
            <input class="input-group-field" id="time" type="number" name="time" maxlength="3" value="<?= htmlspecialchars($time, ENT_COMPAT, 'UTF-8') ?>"/>
            <select class="input-group-field" name="time_type">
                <option value="m"><?= _('minutes') ?></option>
                <option value="h"<?php if ($time_type == 'h') {
                echo ' selected';
            } ?>><?= _('hours') ?></option>
            </select>
        </div>
        <div class="button-group">
            <input class="button primary" type="submit" value="<?= _('Enable') ?>"/>
            <a class="button primary" href="<?= $_SERVER['SCRIPT_NAME']; ?>?disable"><?= _('Disable') ?></a>
        </div>
    </div>
</form>