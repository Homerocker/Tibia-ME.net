<?php
if ($data['editable'] != 1) {
    echo '<div class="callout warning text-center">';
    echo _('You don\'t have permission to edit this theme.');
    echo '</div>';
} else {
    ?>
    <form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post" enctype="multipart/form-data">
        <div class="callout primary">
            <?php
            if ($data['screenshot'] !== null) {
                echo '<a href="' . $data['screenshot'] . '">
                            <img class="thumbnail" src="' . $data['thumbnail'] . '" alt=""/>
                        </a><br/>';
            } else {
                echo '<img class="gallery-preview" src="/images/no_preview.gif" alt=""/><br/>';
            }
            echo _('Author');
            ?>: <?= User::get_link($data['authorID']) ?><br/>
    <?= sprintf(_('Downloads: %d'), $data['downloads']) ?>
    <label for="status"><?= _('Status') ?></label>
    <?php if (Perms::get(Perms::THEMES_MOD)) { ?>
                <select id="status" name="status">
                    <option value="moderation"<?= (($data['status'] == 'moderation') ? ' selected' : '') ?>><?= _('On moderation') ?></option>
                    <option value="checked"<?= (($data['status'] == 'checked') ? ' selected' : '') ?>><?= _('Checked') ?></option>
                    <option value="tested"<?= (($data['status'] == 'tested') ? ' selected' : '') ?>><?= _('Tested') ?></option>
                </select>
            <?php
            } else {
                switch ($data['status']) {
                    case 'moderation':
                        echo '<span class="red">', _('On moderation'), '</span><br/>';
                        break;
                    case 'checked':
                        echo '<span class="green">', _('Checked'), '</span><br/>';
                        break;
                    case 'tested':
                        echo '<span class="green">', _('Tested'), '</span><br/>';
                        break;
                }
            }
            if ($data['moderatorID'] !== null) {
                echo _('Moderator') . ': ' 
                        . '<a href="/user/profile.php?u=' . $data['moderatorID'] . '">' . User::get_display_name($data['moderatorID']) . '</a><br/>';
            }
            echo '<label for="screenshot">' . _('Replace screenshot') . '</label>';
            ?>
            <input id="screenshot" type="file" name="screenshot"/><br/>
            <label for="delete"><?= _('Delete') ?></label>
            <input id="delete" type="checkbox" name="delete"/><br/>
            <input type="hidden" name="theme_id" value="<?= $theme_id ?>"/>
            <input type="hidden" name="page" value="<?= $page ?>"/>
            <input class="button primary" type="submit" name="submit" value="<?= _('Save') ?>"/>
        </div>
    </form>
<?php
}