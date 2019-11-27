<?php

if (empty($data)) {
    echo '<div class="callout secondary text-center">';
    echo _('No pending friend requests.');
    echo '</div>';
} else {
    foreach ($data as $i => $request) {
        echo '<div class="callout primary">';
        echo _('To'), ' ';
        echo '<a href="/user/profile.php?u='.$request['id'].'">',  User::get_display_name($request['id']), '</a><br/>';
        if (isset($request['message'])) {
            echo _('Message') , ':<br/>' , $request['message'], '<br/>';
        }
        echo '<div class="button-group">';
        echo '<a class="button primary" href="', $_SERVER['SCRIPT_NAME'], '?edit=', $request['id'], '">', _('Edit message'), '</a>';
        echo '<a class="button warning" href="' . $_SERVER['SCRIPT_NAME'] . '?cancel=' . $request['id'] . '">' . _('Cancel request') . '</a>';
        echo '</div>';
        echo '</div>';
    }
}