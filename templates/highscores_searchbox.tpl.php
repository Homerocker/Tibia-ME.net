<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout primary">
        <label for="nickname"><?= _('Nickname') ?></label>
        <input type="text" id="nickname" name="nickname" maxlength="10" value="<?= (isset($_GET['nickname']) ? htmlspecialchars($_GET['nickname']) : '') ?>"/>
        <label for="world"><?= _('World') ?></label>
        <select id="world" name="world">
            <option value=""><?= _('any') ?></option>
            <?php
            for ($i = 1; $i <= WORLDS; ++$i) {
                if (isset($_GET['world']) && $_GET['world'] == $i) {
                    echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
                } else {
                    echo '<option value="'.$i.'">'.$i.'</option>';
                }
            }
            ?>
        </select>
        <input class="button primary" type="submit" value="<?= _('Search') ?>"/>
    </div>
</form>