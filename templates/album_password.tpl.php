<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <label for="password"><?= _('Password') ?></label>
        <input id="password" type="password" name="password"/>
        <input type="hidden" name="album_id" value="<?= $_REQUEST['album_id'] ?>"/>
        <div class="button-group">
            <input class="button primary" type="submit" name="submit" value="<?= _('Confirm') ?>"/>
            <a class="button warning" href="./?u=<?= $user_id ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>