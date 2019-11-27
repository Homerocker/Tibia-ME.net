<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <div class="callout secondary"><?= $message ?></div>
        <label for="message"><?= _('Message') ?></label>
        <textarea id="message" name="message" rows="5"></textarea>
        <input type="hidden" name="action" value="<?= $mode ?>"/>
        <input type="hidden" name="quote" value="<?= $quoteID ?>"/>
        <?php
        if ($mode == 'quote') {
            echo '<input type="hidden" name="topicID" value="' . $topicID . '"/>';
        } else {
            echo '<input type="hidden" name="forumID" value="' . $forumID . '"/>';
        }
        ?>
        <div class="button-group">
            <input class="button success" type="submit" value="<?= _('Send') ?>"/>
            <a class="button warning" href="./viewtopic.php?t=<?= $topicID ?>&amp;page=<?= $page ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>