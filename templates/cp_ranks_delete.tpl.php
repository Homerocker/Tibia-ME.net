<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
    <div class="callout primary">
        <?php
        if ($users > 0) {
            printf(ngettext('This rank is assigned to %d user. Please assign them another rank:', 'This rank is assigned to %d users. Please assign them another rank:', $users), $users);
            ?>
            <select name="assign">
                <?php
                foreach ($ranks as $rank_temp) {
                    if ($rank_temp['id'] !== $id) {
                        echo '<option value="', $rank_temp['id'], '">', htmlspecialchars($rank_temp['name']), '</option>';
                    }
                }
                ?>
            </select>
        <?php
        } else {
            echo sprintf(_('Are you sure you want to delete rank "%s"?'), $rank['name']), '<br/>';
        }
        ?>
        <input type="hidden" name="delete" value="<?= $rank['id'] ?>"/>
        <div class="button-group">
            <input class="button alert" type="submit" value="<?= _('Delete') ?>"/>
            <a class="button warning" href="<?= $_SERVER['PHP_SELF'] ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>