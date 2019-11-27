<div class="callout secondary">
    <?= _('You are only allowed to replace .mbm files in your theme. Themes with .r01, .app or other files will be removed.') ?>
</div>
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data">
    <div class="callout primary">
        <label for="theme">SIS</label>
        <input id="theme" type="file" name="theme"/>
        <label for="screenshot"><?= _('Screenshot') ?></label>
        <input id="screenshot" type="file" name="screenshot"/>
        <input class="button primary" type="submit" value="<?= _('Upload') ?>"/>
    </div>
</form>