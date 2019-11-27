<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
    <div class="callout primary">
        <?= _('Nickname') ?>:<br/>
        <b><?= $nickname ?></b><br/>
        <?= _('World') ?>:<br/>
        <b><?= $world ?></b><br/>
        <?= _('Game code') ?>:<br/>
        <b>
        <?php
        list($type, $amount) = explode(':', $code_type);
        if ($type === 'premium') {
            echo sprintf(ngettext('%d day Premium', '%d days Premium', $amount*$multiplier), $amount*$multiplier), '<br/>';
        } else {
            echo sprintf(_('%d Platinum'), $amount*$multiplier), '<br/>';
        }
        ?>
        </b>
        <input type="hidden" name="nickname" value="<?= $nickname ?>"/>
        <input type="hidden" name="world" value="<?= $world ?>"/>
        <input type="hidden" name="code_type" value="<?= $code_type ?>"/>
        <input type="hidden" name="multiplier" value="<?= $multiplier ?>"/>
        <div class="button-group">
                <input class="button alert" type="submit" value="<?= _('Confirm') ?>"/>
                <a class="button warning" href="<?= $_SERVER['PHP_SELF'] ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>