<h3><?= _('Log in') ?></h3>
<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
            <label for="nickname"><?= _('Nickname') ?></label>
            <input type="text" id="nickname" name="nickname" maxlength="10" value="<?= $nickname ?>"/>
            <label for="world"><?= _('World') ?></label>
            <select id="world" name="world">
                <option value=""><?= _('select world') ?></option>
                <?php
                for ($i = 1; $i <= WORLDS; ++$i) {
                    echo '<option value="' . $i . '"' . (($i == $world) ? ' selected="selected"'
                                : '') . '>' . $i . '</option>';
                }
                ?>
            </select>
            <label for="password"><?= _('Password') ?></label>
            <input type="password" id="password" name="password" maxlength="20"/>
            <label for="use_token"><?= _('Remember me on this device.') ?></label>
            <div class="switch">
                <input class="switch-input" id="use_token" type="checkbox" name="use_token" value="1"<?php if ($use_token) echo ' checked'; ?>/>
                <label class="switch-paddle" for="use_token">
                </label>
            </div>
            
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>"/>
        <input class="button primary" type="submit" name="submit" value="<?= _('Log in') ?>"/>
    </div>
</form>