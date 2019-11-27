<h3><?php echo $title; ?></h3>
<div class="grid-x grid-padding-x">
<?php
foreach ($data as $key => $topic) { ?>
    <div class="cell large-6">
        <div class="callout primary break-word">
            <h5 class="no-margin break-word "><a class="inline-flex" href="./viewtopic.php?t=<?= $topic['id'] ?>"><?= htmlspecialchars($topic['title'], ENT_COMPAT, 'UTF-8') ?>&nbsp;<span class="label secondary"><?= $topic['posts'] ?></span></a></h5>
            <? if (in_array(true, $topic['status'])) { ?>
            <div>
                <?php foreach($topic['status'] as $flag => $value) {
                    if ($value != true) {
                        continue;
                    }
                    switch ($flag) {
                        case 'unread':
                            echo '<span class="label success">' . _('new') . '</span>';
                            break;
                        case 'sticky':
                            echo '<span class="label primary">' . _('sticky') . '</span>';
                            break;
                        case 'announcement':
                            echo '<span class="label primary">' . _('announcement') . '</span>';
                            break;
                        case 'closed':
                            echo '<span class="label alert">' . _('closed') . '</span>';
                            break;
                        case 'moved':
                            echo '<span class="label secondary">' . _('moved') . '</span>';
                            break;
                    }
                } ?>
            </div>
            <? } ?>
            <?= _('Author') ?>: <a href="/user/profile.php?u=<?= $topic['authorID'] ?>"><?= User::get_display_name($topic['authorID']) ?></a><br/>
            <?= User::date($topic['time'], 'd.m.Y H:i') ?><br/>
            <?= sprintf(_('Views: %d'), $topic['views']) ?><br/>
            <? if ($topic['posterID']) { ?>
                <?= _('Latest post') ?>: <a href="./viewtopic.php?t=<?= $topic['id'] ?>&amp;page=last"><?= User::get_display_name($topic['posterID']) ?></a><br/>
                <?= User::date($topic['last_post_timestamp'], 'd.m.Y H:i') ?>
            <? } ?>
        </div>
    </div>
    <?php } ?>
</div>