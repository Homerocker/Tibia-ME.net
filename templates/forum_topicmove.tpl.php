<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <label for="moveto"><?= _('Move to') ?></label>
        <select id="moveto" name="moveto">
            <?php
            foreach ($moveto as $id => $title) {
                // is title htmlspecialchars'ed?
                echo '<option value="', $id, '">', $title, '</option>';
            }
            ?>
        </select>
        <input type="hidden" name="action" value="mv"/>
        <input type="hidden" name="topicID" value="<?= $_GET['mv'] ?>"/>
        <div class="button-group">
            <input class="button success" style="display: table-cell;" type="submit" value="<?= _('Move') ?>"/>
            <a class="button warning" href="./viewtopic.php?t=<?= $_GET['mv'] ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>