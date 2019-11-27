<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <label for="message"><?= _('Message') ?></label>
        <textarea id="message" name="message" rows="7"><?= $message ?></textarea>
        <input type="hidden" name="action" value="postedit"/>
        <input type="hidden" name="p" value="<?= $post_id ?>"/>
        <input type="hidden" name="page" value="<?= $page ?>"/>
        <div class="button-group">
            <input class="button primary" type="submit" value="<?= _('Save') ?>"/>
            <a class="button warning" href="./viewtopic.php?t=<?= $topicID ?>&amp;page=<?= $page ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>