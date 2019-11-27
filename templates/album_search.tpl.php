<div class="callout secondary text-center">
    <? if ($results == 0): ?>
        <?= _('Your search returned no results.') ?>
    <? else: ?>
        <?= sprintf(ngettext('Your search returned %d result.', 'Your search returned %d results.', $results), $results) ?>
    <? endif; ?>
</div>
<? if ($results != 0): ?>
    <div class="grid-x grid-padding-x grid-padding-y">
    <? foreach ($data as $user): ?>
        <div class="cell medium-6 large-4">
            <div class="card">
                <div class="card-section text-center">
                    <a href="./albums.php?u=<?php echo $user['userID']; ?>">
                        <img class="thumbnail" src="<?= $user['thumbnail'] ?>" alt=""/>
                    </a><br/>
                    <?= $user['nickname'] ?>, w<?= $user['world'] ?><br/>
                    <?= sprintf(ngettext('%d album', '%d albums', $user['albums']), $user['albums']) ?><br/>
                    <?= sprintf(ngettext('%d photo', '%d photos', $user['photos']), $user['photos']) ?><br/>
                    <? if ($user['album_allow_comments'] == 0): ?>
                        <?= _('comments disabled') ?>
                    <? elseif ($user['comments'] == 0): ?>
                        <?= _('no comments') ?>
                    <? else: ?>
                        <?= sprintf(ngettext('%d comment', '%d comments', $user['comments']), $user['comments']) ?>
                    <? endif; ?>
                    <br/>
                    <div class="button-group stacked-for-small">
                        <a class="button primary" href="/user/profile.php?u=<?=$user['userID']?>"><?=_('Profile')?></a><br/>
                        <a class="button primary" href="/user/letters.php?act=compose&amp;u=<?=$user['userID']?>"><?=_('Send letter')?></a>
                    </div>
                </div>
            </div>
        </div>
    <? endforeach; ?>
    </div>
<? endif; ?>