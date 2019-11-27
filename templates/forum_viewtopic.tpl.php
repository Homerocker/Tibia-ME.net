<div class="button-group stacked-for-small small align-right">
<?php
if ($topic['authorID'] == $_SESSION['user_id']
        || ($topic['forum_id'] != 6
        && Perms::get(Perms::FORUM_MOD))) {
    if (Perms::get(Perms::FORUM_MOD) && $topic['forum_id'] != 6) {
        echo '<a class="button primary" href="./posting.php?mv=' . $_GET['t'] .'">'._('Move topic').'</a>';
    }
    echo '<a class="button primary" href="./posting.php?t=' . $_GET['t'] .'">'._('Edit topic').'</a>';
}
if ($_SESSION['user_id']) {
    // @todo move to controller/model
    if ($GLOBALS['db']->query('SELECT COUNT(*)
        FROM `forum_topics_watch`
        WHERE `userID` = \'' . $_SESSION['user_id'] . '\'
        AND `topicID` = \'' . $topic['id'] . '\'')->fetch_row()[0] == 0) {
        echo '<a class="button primary" href="./posting.php?t=' . $_GET['t'] .'&amp;watch=1">'._('Watch for replies').'</a>';
    } else {
        echo '<a onclick="return confirm(\''. htmlspecialchars(_('Are you sure you want to stop watching for new replies in this thread?')) .'\')" class="button primary" href="./posting.php?t=' . $_GET['t'] .'&amp;watch=0">'._('Stop watching').'</a>';
    }
}?>
<? if ($topic['forum_id'] != 6 && Perms::get(Perms::FORUM_MOD)): ?>
    <? if ($topic['locked']): ?>
    <a class="button primary" onclick="return confirm('<?= htmlspecialchars(_('Are you sure you want to open this topic?')) ?>')" href="<?= $_SERVER['PHP_SELF'] ?>?open=<?= $topic['id'] ?>"><?= _('Open topic') ?></a>
    <? else: ?>
        <a class="button warning" onclick="return confirm('<?= htmlspecialchars(_('Are you sure you want to close this topic?')) ?>')" href="<?= $_SERVER['PHP_SELF'] ?>?close=<?= $topic['id'] ?>"><?= _('Close topic') ?></a>
    <? endif; ?>
        <a class="button alert" onclick="return confirm('<?= htmlspecialchars(_('Are you sure you want to delete this topic?')) ?>')" href="<?= $_SERVER['PHP_SELF'] ?>?td=<?= $topic['id'] ?>"><?= _('Delete topic') ?></a>
<? endif; ?>
</div>
<div class="grid-x grid-padding-x callout primary">
    <? if ($page === 1): ?>
        <div class="cell medium-4 large-3">
            <?php
            if (User::gender_icon($topic['authorID'])) {
                echo '&nbsp;';
            }
            echo User::get_link($topic['authorID']) . '<br/>';
            echo (User::get_status($topic['authorID']) ? '<span class="label success">' . _('Online') . '</span>' : '<span class="label alert">' . _('Offline') . '</span>') . '<br/>';
            printf(_('<b>Posts</b>: %d'), $topic['posts']);
            echo '<br/>' . $topic['time'];
            if ($topic['edit_count']) {
                echo '<p><small>' . nl2br(sprintf(ngettext('Edited by %1$s on %2$s;'."\n".'edited %3$d time in total', 'Edited by %1$s on %2$s;'."\n".'edited %3$d times in total', $topic['edit_count']), $topic['edit_user'], $topic['edit_datetime'], $topic['edit_count'])) . '</small></p>';
            }
            ?>
        </div>

        <div class="cell medium-8 large-9 comment-wrapper">
            <?= $topic['message'] ?>
            <? if (!empty($topic['signature'])): ?>
                <div class="signature"><hr/><?= $topic['signature'] ?></div>
            <? endif; ?>
        </div>
        <? if ($_SESSION['user_id'] && (!Forum::topic_locked($topic['id']) || Perms::get(Perms::FORUM_MOD))) { ?>
            <div class="cell button-group tiny align-right">
                <a class="button primary" href="./posting.php?qt=<?= $topic['id'] ?>&amp;page=<?= $page ?>"><?= _('Quote') ?></a>
            </div>
        <? }
    endif;

foreach ($data as $index => $post): ?>
    <? if ($index != 0 || $page == 1): ?>
        <div class="cell"><hr style="margin-top:0;"/></div>
    <? endif; ?>
    <div class="cell medium-4 large-3">
        <?php
        if (User::gender_icon($post['posterID'])) {
            echo '&nbsp;';
        }
        echo User::get_link($post['posterID']) . '<br/>';
        echo (User::get_status($post['posterID']) ? '<span class="label success">' . _('Online') . '</span>' : '<span class="label alert">' . _('Offline') . '</span>') . '<br/>';
        printf(_('<b>Posts</b>: %d'), $post['posts']);
        echo '<br/>' . User::date($post['time']);
        if ($post['edit_count']) {
            echo '<p><small>' . nl2br(sprintf(ngettext('Edited by %1$s on %2$s;'."\n".'edited %3$d time in total', 'Edited by %1$s on %2$s;'."\n".'edited %3$d times in total', $post['edit_count']), User::get_link($post['edit_userID']), User::date($post['edit_timestamp']), $post['edit_count'])) . '</small></p>';
        }
        ?>
    </div>
    <div class="cell medium-8 large-9 comment-wrapper">
        <?= $post['message'] ?>
        <? if (!empty($post['signature'])): ?>
            <div class="signature">
                <hr/>
                <?= $post['signature'] ?>
            </div>
        <? endif; ?>
    </div>
    <?php
    if ($_SESSION['user_id'] && (!Forum::topic_locked($topic['id']) || Perms::get(Perms::FORUM_MOD))) {
        echo '<div class="cell button-group tiny align-right">';
        echo '<a class="button primary" href="posting.php?q=' . $post['id'] . '&amp;page=' . $page . '">' . _('Quote') . '</a>';
        if (($_SESSION['user_id'] == $post['posterID'] && $_SERVER['REQUEST_TIME'] - $post['time'] < 600) || Perms::get(Perms::FORUM_MOD)) {
            $query = $GLOBALS['db']->query('SELECT `id`
                FROM `forumPosts`
                WHERE `topicID` = \'' . $topic['id'] . '\'
                ORDER BY `time` DESC LIMIT 1')->fetch_row();
            if (Perms::get(Perms::FORUM_MOD) || $post['id'] == $query[0]) {
                echo '<a class="button primary" href="posting.php?p=' . $post['id'] . '&amp;page=' . $page . '">' . _('Edit') . '</a>';
                echo '<a class="button alert" href="'.$_SERVER['PHP_SELF'].'?d=' . $post['id'] . '&amp;page=' . $page . '" onclick="return confirm(\''._('Are you sure you want to delete this post?').'\')">' . _('Delete') . '</a>';
            }
        }
        echo '</div>';
    }
    endforeach; ?>
</div>
<?php
if (Forum::topic_locked($topic['id'])) { ?>
    <div class="callout warning text-center">
        <?= _('This thread is closed, you cannot post here.') ?>
    </div>
<?php
}
if (!$_SESSION['user_id']) {
    echo '<div class="callout warning text-center">';
    echo _('Log in to post messages.');
    echo '</div>';
} elseif (!Forum::topic_locked($topic['id'])
            || Perms::get(Perms::FORUM_MOD)) {
        echo '<form action="' . $_SERVER['SCRIPT_NAME'] . '?t=' . $topic['id'] . '" method="post">
            <div><textarea name="message" rows="6"></textarea></div>
            <div class="text-right"><input type="submit" class="button primary" value="' . _('Reply') . '"/></div>
            </form>';
}