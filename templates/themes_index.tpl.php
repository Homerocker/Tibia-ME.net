<?php
if (empty($data)) {
    echo '<div class="callout secondary text-center">';
    echo _('No themes to display.');
    echo '</div>';
} else {
    echo '<div class="grid-x grid-padding-x grid-padding-y">';
    foreach ($data as $theme) {
        echo '<div class="cell medium-6 large-4">';
        echo '<div class="card">';
        echo '<div class="card-section text-center">';
        if ($theme['screenshot'] !== null) {
            echo '<a href="' . UPLOAD_DIR . '/themes/' . $theme['screenshot'] . '">
                <img class="thumbnail" src="' . $theme['thumbnail'] . '" alt=""/>
            </a>';
        } else {
            echo '<img class="thumbnail" src="/images/no_preview.gif" alt=""/>';
        }
        Likes::display('theme', $theme['id']);
        echo sprintf(_('Author: %s'),
                '<a href="/user/profile.php?u=' . $theme['authorID'] . '">' . User::get_display_name($theme['authorID']) . '</a>') . '<br/>';
        if ($theme['timestamp']) {
            echo User::date($theme['timestamp']), '<br/>';
        }
        echo _('Status') . ': <span class="' . (($theme['status'] == 'checked' || $theme['status']
        == 'tested') ? 'green' : 'red') . '" style="font-weight: bold;">';
        switch ($theme['status']) {
            case 'moderation':
                echo _('On moderation');
                break;
            case 'checked':
                echo _('Checked');
                break;
            case 'tested':
                echo _('Tested');
                break;
        }
        echo '</span><br/>';
        if ($theme['moderatorID'] !== null) {
            echo sprintf(_('Moderator: %s'),
                    '<a href="/user/profile.php?u=' . $theme['moderatorID'] . '">' . User::get_display_name($theme['moderatorID']) . '</a>') . '<br/>';
        }
        echo sprintf(_('Downloads: %d'), $theme['downloads']);
        echo '<div class="button-group small stacked">';
        echo '<a class="button primary" href="./comments.php?theme_id=' . $theme['id'] . (($u !== null)
                    ? '&amp;u=' . $u : '') . '">';
        if ($theme['comments'] == 0) {
            echo _('no comments');
        } else {
            printf(ngettext('%d comment', '%d comments', $theme['comments']),
                    $theme['comments']);
        }
        echo '</a>';
        if ($theme['downloadable'] == 1) {
            echo '<a class="button primary" href="./download.php?theme_id=' . $theme['id'] . '">' . _('Download') . '</a>';
        }
        if ($theme['editable'] == 1) {
            echo '<a class="button primary" href="./edit.php?theme_id=' . $theme['id'] . '&amp;page=' . $page . '">' . _('Edit') . '</a>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
}
?>
<form action="./upload.php">
    <div>
        <input class="button primary" type="submit" value="<?= _('Upload') ?>"/>
    </div>
</form>