<?php
if (empty($data)) {
    echo '<div class="callout secondary text-center">';
    echo _('No mutual friends.');
    echo '</div>';
} else {
    foreach ($data as $i => $friend) {
        echo '<div class="callout primary">';
        echo '<a href="/user/profile.php?u=', $friend['id'], '">', User::get_display_name($friend['id']), '</a><br/>';
        echo User::online_status($friend['id']);
        echo '<div class="button-group">';
        echo '<a class="button primary" href="./letters.php?compose&amp;u=' . $friend['id'] . '">' . _('Send letter') . '</a>';
        echo '<a class="button warning" href="' . $_SERVER['SCRIPT_NAME'] . '?remove=' . $friend['id'] . '">' . _('Remove friend') . '</a>';
        echo '</div>';
        echo '</div>';
    }
}