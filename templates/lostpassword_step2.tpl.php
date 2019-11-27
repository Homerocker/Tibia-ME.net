<h3><?= _('Recover password') ?></h3>
<form action="<?= $_SERVER['PHP_SELF'] ?>?user=<?= $id ?>&amp;v=<?= $_GET['v'] ?>" method="post">
    <div class="callout primary">
        <?= _('Nickname') ?>:<br/>
        <?= $nickname ?><br/>
        <?= _('World') ?>:<br/>
        <?= $world ?><br/>
        <label for="password"><?= _('New password') ?> (<?= _('at least 5 characters') ?>)</label>
        <input type="text" id="password" name="password" maxlength="20"/>
        <input class="button primary" type="submit" value="<?= _('Confirm') ?>"/>
    </div>
</form>