<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <?= _('Forum') ?></b>: <a href="./viewforum.php?f=<?= $_REQUEST['f'] ?>"><?= $forum_title ?></a><br/>
        <label for="topic_title"><?= _('Topic title') ?></label>
        <input id="topic_title" type="text" name="topicTitle" value="<?= $topic_title ?>"/>
        <label for="message"><?= _('Message') ?></label>
        <textarea id="message" name="topicMessage" rows="10"><?= $message ?></textarea>
        <fieldset>
        <legend><?= _('Topic type') ?></legend>
        <input id="normal" type="radio" name="topicType" value="normal"<?php if ($topic_type == 'normal'): echo ' checked'; endif; ?>/><label for="normal"><?= _('Normal') ?></label><br/>
        <? if ($moderate):
            if (!$sticky_limit_reached): ?>
                <input id="sticky" type="radio" name="topicType" value="sticky"<?php if ($topic_type == 'sticky') { echo ' checked'; } ?>/><label for="sticky"><?= _('Sticky') ?></label><br/>
            <? endif; ?>
            <input id="announcement" type="radio" name="topicType" value="announcement"<?php if ($topic_type == 'announcement') { echo ' checked'; } ?>/><label for="announcement"><?= _('Announcement') ?></label><br/>
        <? endif; ?>
        <input type="hidden" name="f" value="<?= $_REQUEST['f'] ?>"/>
        <div class="button-group">
            <input class="button success" type="submit" value="<?= _('Create') ?>"/>
            <a class="button warning" href="./viewforum.php?f=<?= $_REQUEST['f'] ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>