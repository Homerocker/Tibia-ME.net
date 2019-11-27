<?php
if ($error) {
    echo '<div class="callout warning text-center">';
    foreach ($error as $i => $error_msg) {
        echo (($i != 0) ? '<br/>' : ''), $error_msg;
    }
    echo '</div>';
}
?>
<h3>
    <?= _('Recipient') ?>
</h3>
<div class="callout primary">
    <b><?= _('Nickname') ?></b>:<br/>
    <input type="text" size="10" value="<?= $nickname ?>" disabled="disabled"/><br/>
    <b><?= _('World') ?></b>: <select disabled="disabled"><option><?= $world ?></option></select>
</div>
<h3>
    <?= _('Message') ?>
</h3>
<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <label for="subject"><?= _('Subject') ?></label>
        <input id="subject" type="text" name="subject" maxlength="32" value="<?= $subject ?>"<?php if ($replyto_count) {
        echo ' disabled';
    } ?>/>
        <label for="message"><?= _('Message') ?></label>
        <textarea id="message" name="message" rows="5"><?= $message ?></textarea>
        <input type="hidden" name="edit" value="<?= $_REQUEST['edit'] ?>"/>
        <div class="button-group">
            <input class="button primary" type="submit" name="submit" value="<?= _('Confirm') ?>"/>
            <a class="button warning" href="<?= $_SERVER['SCRIPT_NAME'] ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>