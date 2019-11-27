<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <label for="moveto"><?= _('Select album') ?></label>
        <select id="moveto" name="moveto">
            <?php
            echo '<option value=""></option>';
            foreach ($folders as $id => $title) {
                echo '<option value="'.$id.'">'.$title.'</option>';
            }
            ?>
        </select>
        <input type="hidden" name="move" value="<?= $photo_id ?>"/>
        <div class="button-group">
            <input class="button primary" type="submit" name="submit" value="<?= _('Move') ?>"/>
            <a class="button warning" href="<?= $_SERVER['PHP_SELF'] ?>?album_id=<?= $album_id ?>&amp;page=<?= $page ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>