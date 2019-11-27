<form action="<?= $_SERVER['SCRIPT_NAME'] ?><?= ((isset($mode) && $mode == 'create') ? '?mode=create' : '') ?>" method="post">
    <div class="callout primary">
        <label for="title"><?php echo _('Title'); ?></label>
        <input id="title" type="text" name="title" maxlength="24" value="<?php echo htmlspecialchars($form['title'],
        ENT_COMPAT, 'UTF-8'); ?>"/>
        <label for="description"><?php echo _('Description'); ?></label>
        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($form['description'],
        ENT_COMPAT, 'UTF-8'); ?></textarea>
        <label for="password"><?php echo _('Password'); ?> (<?= _('optional') ?>)</label>
        <input id="password" type="text" name="password" maxlength="8" value="<?php echo htmlspecialchars($form['password'],
        ENT_COMPAT, 'UTF-8'); ?>"/>
        <label for="friends_only"><?php echo _('For friends only'); ?></label>
        <div class="switch">
            <input class="switch-input" id="friends_only" type="checkbox" name="friends_only" value="1"<? if ($form['friends_only']): echo ' checked'; endif; ?>/>
            <label class="switch-paddle" for="friends_only"></label>
        </div>
        <div class="button-group">
            <input class="button primary" type="submit" name="submit" value="<?= ($mode == 'create' ? _('Create') : _('Save')) ?>"/>
            <a class="button warning" href="<?= $_SERVER['PHP_SELF'] ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>