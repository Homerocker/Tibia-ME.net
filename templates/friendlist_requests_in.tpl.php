<?php

if (empty($data)) {
    echo '<div class="callout secondary text-center">';
    echo _('No pending friend requests.');
    echo '</div>';
} else {
    foreach ($data as $i => $friend) {
        echo '<div class="callout primary">';
        echo _('From') . ' ';
        echo '<a href="/user/profile.php?u='.$friend['id'].'">', User::get_display_name($friend['id']), '</a>';
        if (isset($friend['message'])) {
            echo _('Message'), ':<br/>', $friend['message'], '<br/>';
        }
        echo '<div class="button-group">';
        echo '<a class="button success" href="', $_SERVER['SCRIPT_NAME'], '?accept=', $friend['id'], '">', _('Accept'), '</a>';
        echo '<a class="button warning" href="', $_SERVER['SCRIPT_NAME'], '?decline=', $friend['id'], '">', _('Decline'), '</a>';
        echo '</div>';
        echo '</div>';
    }
}