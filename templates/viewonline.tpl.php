<h4><?= _('Who\'s online') ?></h4>
<?php
if (empty($data)) {
    echo '<div class="callout secondary text-center">';
    echo _('No registered users online.');
    echo '</div>';
} else {
    foreach ($data as $i => $user) {
        echo '<div class="callout primary">';
        echo isset($user['id']) ? '<a href="/user/profile.php?u=' . $user['id'] . '">' . User::get_display_name($user['id']) . '</a>' : $user['name'];
        echo '</div>';
    }
}