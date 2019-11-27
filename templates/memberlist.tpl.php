<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout secondary">
        <label for="nickname"><?= _('Nickname') ?></label>
        <input type="text" id="nickname" name="nickname" maxlength="10"<?php if (!empty($_GET['nickname'])) {
            echo ' value="' . htmlspecialchars($_GET['nickname'], ENT_COMPAT, 'UTF-8') . '"';
        } ?>/>
        <label for="world"><?= _('World') ?></label>
        <select id="world" name="world">
            <option value=""><?= _('all') ?></option>
            <?php
            for ($world = 1; $world <= WORLDS; $world++) {
                if (isset($_GET['world']) && $_GET['world'] == $world) {
                    echo '<option value="' . $world . '" selected>' . $world . '</option>';
                } else {
                    echo '<option value="' . $world . '">' . $world . '</option>';
                }
            }
            ?>
        </select>
        <input class="button primary" type="submit" value="<?= _('Search') ?>"/>
    </div>
</form>
<h3><?= _('Total registered users') ?>: <?= $total_registered ?></h3>
<?php
if (isset($search_results)) {
    echo '<div class="callout secondary text-center">';
    if ($search_results == 0) {
        echo _('Your search returned no results.');
    } else {
        printf(ngettext('Your search returned %d result.', 'Your search returned %d results.', $search_results), $search_results);
    }
    echo '</div>';
}
if (!isset($search_results) || $search_results != 0) {
    echo '<div class="grid-x grid-padding-x grid-padding-y">';
    foreach ($data as $i => $user) {
        if (($i + 1) % 10 == 1) {
            echo '<div class="cell medium-4 large-3">';
        } 
        echo User::get_link($user['id']), '<br/>';
        if (($i + 1) % 10 == 0 || $i + 1 == count($data)) {
            echo '</div>';
        } 
    }
    echo '</div>';
}
?>