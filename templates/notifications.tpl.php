<?php

if (empty($data)) {
    echo '<div class="callout secondary text-center">';
    echo _('No notifications.');
    echo '</div>';
} else {
    echo '<h3>';
    echo _('Notifications');
    echo '</h3>';
    foreach ($data as $i => $notification) {
        echo '<div class="callout primary">';
        if ($notification['viewed'] == 0) {
            echo '<b>';
        }
        switch ($notification['type']) {
            case 'forumPost':
                if ($notification['users_count'] == 1) {
                    printf(_('%s posted at <a href="%s">forum</a>.'), User::get_link($notification['users_id'][0]), '/forum/viewtopic.php?t=' . $notification['target_id'] . '&amp;page=last');
                } elseif ($notification['users_count'] == 2) {
                    printf(_('%s and %s posted at <a href="%s">forum</a>.'), User::get_link($notification['users_id'][0]), User::get_link($notification['users_id'][1]), '/forum/viewtopic.php?t=' . $notification['target_id'] . '&amp;page=last');
                } else {
                    printf(ngettext('%s and %d other user posted at <a href="%s">forum</a>.', '%s and %d other users posted at <a href="%s">forum</a>.', ($notification['users_count'] - 1)), User::get_link($notification['users_id'][0]), ($notification['users_count'] - 1), '/forum/viewtopic.php?t=' . $notification['target_id'] . '&amp;page=last');
                }
                break;
            case 'photoComment':
                if ($notification['users_count'] == 1) {
                    printf(_('%s commented on <a href="%s">photo</a>.'), User::get_link($notification['users_id'][0]), '/album/comments.php?photo_id=' . $notification['target_id']);
                } elseif ($notification['users_count'] == 2) {
                    printf(_('%s and %s commented on <a href="%s">photo</a>.'), User::get_link($notification['users_id'][0]), User::get_link($notification['users_id'][1]), '/album/comments.php?photo_id=' . $notification['target_id']);
                } else {
                    printf(ngettext('%s and %d other user commented on <a href="%s">photo</a>.', '%s and %d other users commented on <a href="%s">photo</a>.', ($notification['users_count'] - 1)), User::get_link($notification['users_id'][0]), ($notification['users_count'] - 1), '/album/comments.php?photo_id=' . $notification['target_id']);
                }
                break;
            case 'screenshotComment':
                if ($notification['users_count'] == 1) {
                    printf(_('%s commented on <a href="%s">screenshot</a>.'), User::get_link($notification['users_id'][0]), '/screenshots/comments.php?screenshot_id=' . $notification['target_id']);
                } elseif ($notification['users_count'] == 2) {
                    printf(_('%s and %s commented on <a href="%s">screenshot</a>.'), User::get_link($notification['users_id'][0]), User::get_link($notification['users_id'][1]), '/screenshots/comments.php?screenshot_id=' . $notification['target_id']);
                } else {
                    printf(ngettext('%s and %d other user commented on <a href="%s">screenshot</a>.', '%s and %d other users commented on <a href="%s">screenshot</a>.', ($notification['users_count'] - 1)), User::get_link($notification['users_id'][0]), ($notification['users_count'] - 1), '/screenshots/comments.php?screenshot_id=' . $notification['target_id']);
                }
                break;
            case 'themeComment':
                if ($notification['users_count'] == 1) {
                    printf(_('%s commented on <a href="%s">theme</a>.'), User::get_link($notification['users_id'][0]), '/themes/comments.php?theme_id=' . $notification['target_id']);
                } elseif ($notification['users_count'] == 2) {
                    printf(_('%s and %s commented on <a href="%s">theme</a>.'), User::get_link($notification['users_id'][0]), User::get_link($notification['users_id'][1]), '/themes/comments.php?theme_id=' . $notification['target_id']);
                } else {
                    printf(ngettext('%s and %d other user commented on <a href="%s">theme</a>.', '%s and %d other users commented on <a href="%s">theme</a>.', ($notification['users_count'] - 1)), User::get_link($notification['users_id'][0]), ($notification['users_count'] - 1), '/themes/comments.php?theme_id=' . $notification['target_id']);
                }
                break;
            case 'screenshotLike':
                if ($notification['users_count'] == 1) {
                    printf(_('%s likes your <a href="%s">screenshot</a>.'), User::get_link($notification['users_id'][0]), '/screenshots/comments.php?screenshot_id=' . $notification['target_id']);
                } elseif ($notification['users_count'] == 2) {
                    printf(_('%s and %s like your <a href="%s">screenshot</a>.'), User::get_link($notification['users_id'][0]), User::get_link($notification['users_id'][1]), '/screenshots/comments.php?screenshot_id=' . $notification['target_id']);
                } else {
                    printf(ngettext('%s and %d other user like your <a href="%s">screenshot</a>.', '%s and %d other users like your <a href="%s">screenshot</a>.', ($notification['users_count'] - 1)), User::get_link($notification['users_id'][0]), ($notification['users_count'] - 1), '/screenshots/comments.php?screenshot_id=' . $notification['target_id']);
                }
                break;
            case 'photoLike':
                if ($notification['users_count'] == 1) {
                    printf(_('%s likes your <a href="%s">photo</a>.'), User::get_link($notification['users_id'][0]), '/album/comments.php?photo_id=' . $notification['target_id']);
                } elseif ($notification['users_count'] == 2) {
                    printf(_('%s and %s like your <a href="%s">photo</a>.'), User::get_link($notification['users_id'][0]), User::get_link($notification['users_id'][1]), '/album/comments.php?photo_id=' . $notification['target_id']);
                } else {
                    printf(ngettext('%s and %d other user like your <a href="%s">photo</a>.', '%s and %d other users like your <a href="%s">photo</a>.', ($notification['users_count'] - 1)), User::get_link($notification['users_id'][0]), ($notification['users_count'] - 1), '/album/comments.php?photo_id=' . $notification['target_id']);
                }
                break;
            case 'themeLike':
                if ($notification['users_count'] == 1) {
                    printf(_('%s likes your <a href="%s">theme</a>.'), User::get_link($notification['users_id'][0]), '/themes/comments.php?theme_id=' . $notification['target_id']);
                } elseif ($notification['users_count'] == 2) {
                    printf(_('%s and %s like your <a href="%s">theme</a>.'), User::get_link($notification['users_id'][0]), User::get_link($notification['users_id'][1]), '/themes/comments.php?theme_id=' . $notification['target_id']);
                } else {
                    printf(ngettext('%s and %d other user like your <a href="%s">theme</a>.', '%s and %d other users like your <a href="%s">theme</a>.', ($notification['users_count'] - 1)), User::get_link($notification['users_id'][0]), ($notification['users_count'] - 1), '/themes/comments.php?theme_id=' . $notification['target_id']);
                }
                break;
            case 'friendAccept':
                if ($notification['users_count'] == 1) {
                    printf(_('%s accepted your friend request.'), User::get_link($notification['users_id'][0]));
                } elseif ($notification['users_count'] == 2) {
                    printf(_('%s and %s accepted your friend requests.'), User::get_link($notification['users_id'][0]), User::get_link($notification['users_id'][1]));
                } else {
                    printf(ngettext('%s and %d other user accepted your friend requests.', '%s and %d other users accepted your friend requests.', ($notification['users_count'] - 1)), User::get_link($notification['users_id'][0]), ($notification['users_count'] - 1));
                }
                if ($notification['viewed'] == 0) {
                    Notifications::view('friendAccept', $notification['target_id']);
                }
                break;
            case 'artworkComment':
                if ($notification['users_count'] == 1) {
                    printf(_('%s commented on <a href="%s">artwork</a>.'), User::get_link($notification['users_id'][0]), '/artworks/comments.php?artwork_id=' . $notification['target_id']);
                } elseif ($notification['users_count'] == 2) {
                    printf(_('%s and %s commented on <a href="%s">artwork</a>.'), User::get_link($notification['users_id'][0]), User::get_link($notification['users_id'][1]), '/artworks/comments.php?artwork_id=' . $notification['target_id']);
                } else {
                    printf(ngettext('%s and %d other user commented on <a href="%s">artwork</a>.', '%s and %d other users commented on <a href="%s">artwork</a>.', ($notification['users_count'] - 1)), User::get_link($notification['users_id'][0]), ($notification['users_count'] - 1), '/artworks/comments.php?artwork_id=' . $notification['target_id']);
                }
                break;
            case 'artworkLike':
                if ($notification['users_count'] == 1) {
                    printf(_('%s likes your <a href="%s">artwork</a>.'), User::get_link($notification['users_id'][0]), '/artworks/comments.php?artwork_id=' . $notification['target_id']);
                } elseif ($notification['users_count'] == 2) {
                    printf(_('%s and %s like your <a href="%s">artwork</a>.'), User::get_link($notification['users_id'][0]), User::get_link($notification['users_id'][1]), '/artworks/comments.php?artwork_id=' . $notification['target_id']);
                } else {
                    printf(ngettext('%s and %d other user like your <a href="%s">artwork</a>.', '%s and %d other users like your <a href="%s">artwork</a>.', ($notification['users_count'] - 1)), User::get_link($notification['users_id'][0]), ($notification['users_count'] - 1), '/artworks/comments.php?artwork_id=' . $notification['target_id']);
                }
                break;
        }

        if ($notification['viewed'] == 0) {
            echo '</b>';
        }
        echo '<br/>', $notification['timestamp'];
        echo '</div>';
    }
}