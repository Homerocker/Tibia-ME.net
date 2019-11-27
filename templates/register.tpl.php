<h3><?= _('Register') ?></h3>
<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <label for="nickname"><?= _('Nickname') ?></label>
        <input type="text" id="nickname" name="nickname" size="10" maxlength="10" value="<?= $nickname ?>"/>
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
        <label for="password"><?= _('Password') ?> (<?= _('at least 5 characters') ?>)</label>
        <input type="password" id="password" name="password" maxlength="20"/>
        <label for="email">Email (<?= _('optional') ?>)</label>
        <input type="text" id="email" maxlength="64" name="email" value="<?= $email ?>"/>
        <label for="hide_email"><?= _('Hide email') ?></label>
        <div class="switch">
            <input class="switch-input" id="hide_email" type="checkbox" name="hide_email"<?= ($hide_email
                        ? ' checked' : '')
            ?>/>
            <label class="switch-paddle" for="hide_email">
            </label>
        </div>
        <? $rand = [rand(0, 9), rand(0, 9)]; $_SESSION['captcha'] = array_sum($rand); ?>
        <div class="input-group">
            <label for="captcha" class="input-group-label"><?= $rand[0] ?>+<?= $rand[1] ?>=</label>
            <input id="captcha" class="input-group-field" type="number" name="captcha" maxlength="2"/>
        </div>
        <input type="hidden" name="agreement" value="accepted"/>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars(get_redirect(false)) ?>"/>
        <input class="button primary" type="submit" name="submit" value="<?= _('Register') ?>"/>
    </div>
</form>