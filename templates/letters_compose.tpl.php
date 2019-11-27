<?php
if (!empty($error)) {
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
<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <label for="nickname"><?= _('Nickname') ?></label>
        <input id="nickname" type="text"<?= (isset($u) ? ' disabled' : ' name="nickname"') ?> maxlength="10" value="<?= $nickname ?>"/>
        <label for="world"><?= _('World') ?></label>
        <select id="world" name="world"<?php if (isset($u)) { echo ' disabled'; } ?>>
            <option value=""></option>
            <?php
            if (isset($u)) {
                echo '<option selected>' . $world . '</option>';
            } else {
                for ($i = 1; $i <= WORLDS; ++$i) {
                    echo '<option value="' . $i . '"' . (($i == $world) ? ' selected' : '') . '>' . $i . '</option>';
                }
            }
            ?>
        </select>
    </div>
    <h3>
        <?= _('Message') ?>
    </h3>
    <div class="callout primary">
        <label for="subject"><?= _('Subject') ?></label>
        <input id="subject" type="text"<?= ($replyto_count ? ' disabled' : ' name="subject"') ?> maxlength="32" value="<?= ($replyto_count ? '[Re:'.$replyto_count.']&nbsp;'.$subject : $subject) ?>"/>
        <label for="message"><?= _('Text') ?></label>
        <textarea id="message" name="message" rows="7"><?= $message ?></textarea>
        <?php
        if (isset($u)) {
            echo '<input type="hidden" name="u" value="' . $u . '"/>';
        }
        if ($replyto_count) {
            // @todo is $subject htmlspecialchar'ed?
            echo '<input type="hidden" name="subject" value="'.$subject.'"/>';
            echo '<input type="hidden" name="compose" value="'.$_REQUEST['compose'].'"/>';
        } else {
            echo '<input type="hidden" name="compose"/>';
        }
        ?>
        <div class="button-group align-right">
            <input class="button success" type="submit" name="submit" value="<?= _('Send') ?>"/>
            <a class="button warning" href="<?= $_SERVER['PHP_SELF'] ?>?folder=<?= $folder ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>