<h3><?= _('Friends') ?></h3>
<? if (empty($data)): ?>
    <div class="callout secondary text-center">
        <?= _('Friendlist is empty.') ?>
    </div>
<? else: ?>
    <div class="callout primary">
    <? foreach ($data as $i => $friend): ?>
        <a href="/user/profile.php?u=<?= $friend['id'] ?>"><?= User::get_display_name($friend['id']) ?></a>
        <? if (User::get_status($friend['id'])): ?>
            <span class="label success"><?= _('Online') ?></span>
        <? else: ?>
            <span class="label alert"><?= _('Offline') ?></span>
        <? endif; ?>
        <div class="button-group small">
            <a class="button primary" href="./letters.php?compose&amp;u=<?= $friend['id'] ?>"><?= _('Send letter') ?></a>
            <? if ($friend['fgs'] === false): ?>
            <? // @todo can it even be false? ?>
                <a onclick="return confirm('<?= _('Are you sure you want to add this user to friendlist?') ?>')" class="button primary" href="./friendlist.php?add=<?= $friend['id'] ?>&amp;redirect=<?= get_redirect(true) ?>"><?= _('Add friend') ?></a>
            <? elseif ($friend['fgs'] === 0): ?>
                <?= _('Your friend request is pending.') ?>
                <a onclick="return confirm('<?= _('Are you sure you want to cancel your friend request?') ?>')" class="button primary" href="./friendlist.php?cancel=<?= $friend['id'] ?>&amp;redirect=<?= get_redirect(true) ?>"><?= _('Cancel request') ?></a>
            <? elseif ($friend['fgs'] === 1): ?>
                <a onclick="return confirm('<?= _('Are you sure you want to accept this friend request?') ?>')" class="button primary" href="./friendlist.php?accept=<?= $friend['id'] ?>&amp;redirect=<?= get_redirect(true) ?>"><?= _('Accept request') ?></a>
                <a onclick="return confirm('<?= _('Are you sure you want to decline this friend request?') ?>')" class="button warning" href="./friendlist.php?decline=<?= $friend['id'] ?>&amp;redirect=<?= get_redirect(true) ?>"><?= _('Decline request') ?></a>
            <? else: ?>
                <a onclick="return confirm('<?= _('Are you sure you want to remove this user from friendlist?') ?>')" class="button alert" href="./friendlist.php?remove=<?= $friend['id'] ?>&amp;redirect=<?= get_redirect(true) ?>"><?= _('Remove friend') ?></a>
            <? endif; ?>
        </div>
    <? endforeach; ?>
    </div>
<? endif; ?>