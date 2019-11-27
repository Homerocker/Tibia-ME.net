<div class="callout primary">
    <a href="/user/profile.php?u=<?= $ban['userID'] ?>"><?= User::get_display_name($ban['userID']) ?></a><br/>
    <?= User::date($ban['bannedTime']) ?><br/>
    <?= $expired ? '<span class="label warning">' . _('Expired') . '</span>' : '<span class="label alert">' . _('Active') . '</span>' ?><br/>
    <?= sprintf(_('<b>Banned by</b>:<br/>%s'), '<a href="/user/profile.php?u=' . $ban['bannedModeratorID'] . '">' . User::get_display_name($ban['bannedModeratorID']) . '</a>') ?><br/>
    <b><?= _('Reason') ?></b>:<br/>
    <?= Banishments::Reason($ban['reason']) ?><br/>
    <?php
    if ($ban['description']) {
        echo '<b>', _('Description'), '</b>:<br/>', Forum::MessageHandler($ban['description']), '<br/>';
    }
    if ($ban['forumPost']) {
        echo '<b>', _('Message'), '</b>:<br/>', Forum::MessageHandler($ban['forumPost']), '<br/>';
    }
    ?>
    <b><?= _('Expires') ?></b>:<br/><?= User::date($ban['expirationTime']) ?>
    <?php
    if ($ban['unbannedModeratorID']) {
        echo '<br/>', sprintf(_('<b>Unbanned by</b>:<br/>%s'), '<a href="/user/profile.php?u=' . $ban['unbannedModeratorID'] . '">' . User::get_display_name($ban['unbannedModeratorID'] . '</a>'));
    } elseif (Perms::get(Perms::USERS_BAN) && $_SERVER['REQUEST_TIME'] < $ban['expirationTime']) {
        echo '<br/><a onclick="return confirm(\'' . htmlspecialchars(_('Are you sure you want to deactivate this banishment?')) . '\')" class="button warning" href="', $_SERVER['PHP_SELF'], '?unban=', $ban['id'], '">', _('Unban'), '</a>';
    }
echo '</div>';