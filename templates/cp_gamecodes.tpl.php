<?php
if (Perms::get(Perms::GAMECODES_ADD)) {
?>
    <h3><?= _('Add') ?></h3>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <div class="callout primary">
            <label for="gamecodes"><?= _('Game codes') ?></label>
            <p class="help-text">(<?= _('each game code on a new line') ?>)</p>
            <textarea id="gamecodes" name="gamecodes" rows="10"><?= htmlspecialchars($codes) ?></textarea>
            <label for="type"><?= _('Type') ?></label>
            <select id="type" name="type">
                <option value="platinum"><?= _('Platinum') ?></option>
                <?php
                if ($type === 'premium') {
                    echo '<option value="premium" selected="selected">', _('Premium'), '</option>';
                } else {
                    echo '<option value="premium">', _('Premium'), '</option>';
                }
                ?>
            </select>
            <label for="amount"><?= _('Amount') ?></label>
            <input id="amount" type="number" name="amount" maxlength="5" value="<?= htmlspecialchars($amount) ?>"/>
            <input class="button primary" type="submit" value="<?= _('Add') ?>"/>
        </div>
    </form>
    <?php
}
?>
<h3>
    <?= _('Overview') ?>
</h3>
<?php
if (empty($overview)) {
    echo '<div class="callout secondary text-center">';
    echo _('No items to display.');
    echo '</div>';
} else {
    echo '<div class="callout primary">';
    foreach ($overview as $type => $codes) {
        foreach ($codes as $amount => $count) {
            echo $count, '&times;';
            if ($type === 'premium') {
                echo sprintf(ngettext('%d day Premium', '%d days Premium', $amount), $amount), '<br/>';
            } else {
                echo sprintf(_('%d Platinum'), $amount), '<br/>';
            }
        }
    }
    echo '</div>';
}
if (Perms::get(Perms::GAMECODES_ACTIVATE)) {
    ?>
    <h3>
        <?= _('Activate') ?>
    </h3>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
        <div class="callout primary">
            <label for="nickname"><?= _('Nickname') ?></label>
            <input id="nickname" type="text" name="nickname" maxlength="10" value="<?= $nickname ?>"/>
            <label for="world"><?= _('World') ?></label>
            <select id="world" name="world">
                <option value=""><?= _('select world') ?></option>
                <?php
                for($i = 1; $i <= WORLDS; ++$i) {
                    if ($world == $i) {
                        echo '<option value="', $i, '" selected="selected">', $i, '</option>';
                    } else {
                        echo '<option value="', $i, '">', $i, '</option>';
                    }
                }
                ?>
            </select>
            <label><?= _('Game code') ?></label>
            <div class="input-group">
                <select class="input-group-field" name="code_type">
                    <option value=""><?= _('select code type') ?></option>
                    <?php
                    foreach ($overview as $type => $codes) {
                        foreach (array_keys($codes) as $amount) {
                            echo '<option value="', $type, ':', $amount, '"', ($type . ':' . $amount === $code_type ? ' selected' : ''), '>';
                            if ($type === 'premium') {
                                echo sprintf(ngettext('%d day Premium', '%d days Premium', $amount), $amount);
                            } else {
                                echo sprintf(_('%d Platinum'), $amount);
                            }
                            echo '</option>';
                        }
                    }
                    ?>
                </select>
                <select class="input-group-field" name="multiplier">
                    <?php
                    for ($i = 1; $i <= 50; ++$i) {
                        echo '<option value="', $i, '"', ($multiplier == $i ? ' selected' : ''), '>&times;', $i, '</option>';
                    }
                    ?>
                </select>
            </div>
            <input class="button primary" type="submit" value="<?= _('Activate') ?>"/>
        </div>
    </form>
    <?php
}
?>
<h3>
    <?= _('History') ?>
</h3>
<?php
if (empty($history)) {
    echo '<div class="callout secondary text-center">';
    echo _('No items to display.');
    echo '</div>';
}
foreach ($history as $i => $code) {
    echo '<div class="callout primary">';
    if ($code['failed']) {
        echo '<b><span class="red">', _('FAILURE'), '</span></b><br/>';
    } else {
        echo '<span class="green">', _('SUCCESS'), '</span><br/>';
    }
    echo User::date($code['used_timestamp'], 'Y-m-d H:m'), '<br/>';
    if ($code['failed']) {
        echo '<span class="red text-small">';
    } else {
        echo '<span class="text-small">';
    }
    echo $code['code'], '</span><br/>';
    if ($code['type'] === 'premium') {
        echo '<b>', sprintf(ngettext('%d day Premium', '%d days Premium', $code['amount']), $code['amount']), '</b><br/>';
    } else {
        echo '<b>', sprintf(_('%d Platinum'), $code['amount']), '</b><br/>';
    }
    echo $code['nickname'], '&nbsp;', _('world'), '&nbsp;', $code['world'], '<br/>';
    echo sprintf(_('Used by: %s'), User::get_link($code['modified_mod_id'])), '<br/>';
    // @todo restore failed gamecodes
    //if ($code['failed']) {
    //    echo '<a href="?restore=', $code['code'], '">', _('Restore'), '</a>';
    //}
    echo '</div>';
}
?>
<h3>
    <?= _('Available') ?>
</h3>
<?php
if (empty($codes_available)) {
    echo '<div class="callout secondary text-center">';
    echo _('No items to display.');
    echo '</div>';
}
foreach ($codes_available as $i => $code) {
    echo '<div class="callout primary">';
    if ($code['failed']) {
        echo '<span class="red text-small">';
    } else {
        echo '<span class="text-small">';
    }
    echo $code['code'], '</span><br/>';
    if ($code['type'] === 'premium') {
        echo '<b>', sprintf(ngettext('%d day Premium', '%d days Premium', $code['amount']), $code['amount']), '</b><br/>';
    } else {
        echo '<b>', sprintf(_('%d Platinum'), $code['amount']), '</b><br/>';
    }
    echo sprintf(_('Added by %s'), User::get_link($code['added_mod_id'])), '<br/>';
    //echo '<a href="?delete=', $code['code'], '">', _('Delete'), '</a>';
    echo '</div>';
}