<?php
foreach ($data as $i => $ban) {
    echo '<div class="callout primary">';
    echo '<a href="' . $_SERVER['PHP_SELF'] . '?view=' . $ban['id'] . '">';
    echo User::get_display_name($ban['userID']), '<br/>';
    echo User::date($ban['bannedTime']), '<br/>';
    echo _('Reason') . ': ' . Banishments::Reason($ban['reason']) . '<br/>';
    echo _('Expires') . ': ' . User::date($ban['expirationTime'], 'd.m.Y') . '<br/>';
    if ($_SERVER['REQUEST_TIME'] >= $ban['expirationTime'] || $ban['unbannedModeratorID'] !== null) {
        echo '<span class="label warning">' . _('Expired') . '</span>';
    } else {
        echo '<span class="label alert">' . _('Active') . '</span>';
    }
    echo '</a>';
    echo '</div>';
}