<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <a href="<?= $path ?>"><img class="thumbnail" src="<?= $thumbnail ?>" alt=""/></a><br/>
        <?= _('Are you sure you want to save rotated photo? This may slightly reduce image quality.') ?>
        <input type="hidden" name="page" value="<?= $page ?>"/>
        <input type="hidden" name="rotate" value="<?= $photo_id ?>"/>
        <input type="hidden" name="angle" value="<?= $angle ?>"/>
        <div class="button-group">
            <input class="button primary" type="submit" name="submit" value="<?= _('Save') ?>"/>
            <a class="button warning" href="<?= $_SERVER['SCRIPT_NAME'] ?>?album_id=<?= $album_id ?>&amp;page=<?= $page ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>