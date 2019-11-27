<form action="<?= $_SERVER['SCRIPT_NAME'] ?>?page=<?= $page ?>" method="post">
    <div class="callout primary">
        <label for="topic_title"><?= _('Topic title') ?></label>
        <input id="topic_title" type="text" name="topicTitle" value="<?= $title ?>"/>
        <label for="message"><?= _('Message') ?></label>
        <textarea id="message" name="topicMessage" rows="10"><?= $message ?></textarea>
        <fieldset>
            <legend><?= _('Topic type') ?></legend>
            <?php
            if ($type == 'normal' || ($type == 'sticky' && $sticky_limit_reached)) {
                echo '<input id="normal" type="radio" name="topicType" value="normal" checked/>';
            } else {
                echo '<input type="radio" name="topicType" value="normal"/>';
            }
            echo '<label for="normal">' . _('Normal') . '</label><br/>';
            if (!$sticky_limit_reached) {
                if ($type == 'sticky') {
                    echo '<input id="sticky" type="radio" name="topicType" value="sticky" checked="checked"/>';
                    echo '<label for="sticky">' . _('Sticky') . '</label><br/>';
                } elseif ($moderate) {
                    echo '<input id="sticky" type="radio" name="topicType" value="sticky"/>';
                    echo '<label for="sticky">' . _('Sticky') . '</label><br/>';
                }
            }
            if ($type == 'announcement') {
                echo '<input id="announcement" type="radio" name="topicType" value="announcement" checked="checked"/>';
                echo '<label for="announcement">' . _('Announcement') . '</label><br/>';
            } elseif ($moderate) {
                echo '<input id="announcement" type="radio" name="topicType" value="announcement"/>';
                echo '<label for="announcement">' . _('Announcement') . '</label><br/>';
            }
            ?>
        </fieldset>
        <input type="hidden" name="action" value="topicedit"/>
        <input type="hidden" name="topicID" value="<?= $_GET['t'] ?>"/>
        <input type="hidden" name="page" value="<?= $page ?>"/>
        <div class="button-group">
            <input class="button success" type="submit" value="<?= _('Save') ?>"/>
            <a class="button warning" href="./viewtopic.php?t=<?= $_GET['t'] ?>&amp;page=<?= $page ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>