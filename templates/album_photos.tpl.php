<?php
if (empty($data)) {
    echo '<div class="callout secondary text-center">';
    echo _('This album is empty.');
    echo '</div>';
} else {
    /*
    if ($count >= 1
            && ($_SESSION['user_id'] == $u
                    || Perms::get(Perms::ALBUM_MOD))) {
        echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post">';
    }
     * 
     */
    echo '<div class="grid-x grid-padding-x grid-padding-y">';
    foreach ($data as $key => $photo) {
        ?>
<div class="cell medium-6 large-4">
    <div class="card">
        <div class="card-section text-center">
            <a href="<?=$photo['file']?>">
                <img src="<?= $photo['thumbnail'] ?>" style="max-width: <?= PHOTO_MEDIUM_WIDTH ?>;"
                     alt="" class="thumbnail<?= ($photo['is_avatar'] ? ' avatar' : '') ?>"/>
            </a>
            <?php Likes::display('photo', $photo['id']); ?>
            <?= User::date($photo['timestamp']) ?><br/>
            <?= sprintf(_('File size: %dkb'), $photo['filesize']) ?><br/>
            <?= _('Resolution') ?>: <?= $photo['resolution'] ?>
            <?php
            if (!$album_allow_comments) {
                echo '<br/>', _('comments disabled');
            }
        echo '<div class="button-group align-center stacked small">';
        if ($album_allow_comments || $_SESSION['user_id'] == $u || Perms::get(Perms::ALBUM_MOD)) {
            if ($album_allow_comments) {
                echo '<a class="button primary" href="./comments.php?photo_id=' . $photo['id'] . '">' . _('Comments') . ' (' . $photo['comments'] . ')</a>';
            }
            if ($_SESSION['user_id'] == $u || Perms::get(Perms::ALBUM_MOD)) {
                if ($_SESSION['user_id'] == $u) {
                    if ($photo['extension'] != 'gif' || !Images::is_animated($_SERVER['DOCUMENT_ROOT'] . $photo['file'])) {
                    echo '<a class="button primary" href="' . $_SERVER['SCRIPT_NAME'] . '?rotate=' . $photo['id'] . '&amp;page=' . $page . '">' . _('Rotate left') . '</a>
                        <a class="button primary" href="' . $_SERVER['SCRIPT_NAME'] . '?rotate=' . $photo['id'] . '&amp;angle=270&amp;page=' . $page . '">' . _('Rotate right') . '</a>';
                    }
                    echo '<a onclick="return confirm(\''.htmlspecialchars(_('Are you sure you want to set this photo as avatar?')).'\')" class="button primary" href="' . $_SERVER['SCRIPT_NAME'] . '?avatar=' . $photo['id'] . '&amp;page=' . $page . '">' . _('Set as avatar') . '</a>';
                    $value = $GLOBALS['db']->query('SELECT COUNT(*)
                        FROM `album_albums`
                        WHERE `userID` = \'' . $u . '\'
                        AND `id` != \'' . $_REQUEST['album_id'] . '\'')
                            ->fetch_row();
                    if ($value[0]) {
                        echo '<a class="button primary" href="' . $_SERVER['SCRIPT_NAME'] . '?move=' . $photo['id'] . '&amp;page=' . $page . '">' . _('Move') . '</a>';
                    }
                }
                echo '<a onclick="return confirm(\''.htmlspecialchars(_('Are you sure you want to delete this photo?')).'\')" class="button alert" href="' . $_SERVER['SCRIPT_NAME'] . '?delete=' . $photo['id'] . '&amp;album_id='.$_REQUEST['album_id'].'&amp;page=' . $page . '">' . _('Delete') . '</a>';
                /*
                if ($count >= 1) {
                    echo '<br/>', _('Mark'), ': <input type="checkbox" name="mark[]" value="'.$photo['id'].'"/>';
                }
                 * 
                 */
            }
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
    /*
    if ($count >= 1
            && ($_SESSION['user_id'] == $u
                    || Perms::get(Perms::ALBUM_MOD))) {
        echo _('With selected'), ':<br/>';
        echo '<select name="selection">';
        echo '<option value="delete">'._('Delete').'</option>';
        echo '<option value="move">',_('Move'),'</option>';
        echo '</select>';
        echo '<input type="submit" value="OK"/>';
        echo '</form>';
    }
     */
}