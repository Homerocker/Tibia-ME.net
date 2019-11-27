<? if (empty($data)):
    echo '<div class="callout secondary text-center">';
    if ($u == $_SESSION['user_id']) {
        echo _('You have no albums.');
    } else {
        echo _('This user has no albums.');
    }
    echo '</div>';
else: ?>
<div class="grid-x grid-padding-x grid-padding-y">
    <? foreach ($data as $key => $album): ?>
    <div class="cell medium-6 large-4">
        <div class="card">
            <div class="card-section text-center">
        <a href="./photos.php?album_id=<?= $album['id'] ?>">
        <? // @todo don't show previews if access is not granted ?>
        <img class="thumbnail" src="<?= $album['thumbnail'] ?>" alt=""/><br/>
        <?= $album['title'] ?></a><br/>
        <? if (!empty($album['description'])): ?>
            <i><?= $album['description'] ?></i><br/>
        <? endif; ?>
        <? if (!empty($album['password'])): ?>
            <span class="label alert"><?= _('password') ?></span><br/>
        <? elseif ($album['friends_only'] == 1): ?>
             <span class="label warning"><?= _('friends only') ?></span><br/>
        <? endif; ?>
        <? if (!empty($album['password']) && $_SESSION['user_id'] == $u): ?>
            <?= _('Password') ?>: <?= $album['password'] ?><br/>
        <? endif; ?>
        <? if ($album['photos'] == 0): ?>
            <?= _('no photos') ?><br/>
        <? else: ?>
            <?= sprintf(ngettext('%d photo', '%d photos', $album['photos']), $album['photos']) ?><br/>
        <? endif; ?>
        <? if ($album['album_allow_comments'] == 0): ?>
            <?= _('comments disallowed') ?>
        <? elseif ($album['comments'] == 0): ?>
            <?= _('no comments') ?>
        <? else: ?>
            <?= sprintf(ngettext('%d comment', '%d comments', $album['comments']), $album['comments']) ?>
        <? endif; ?>
            </div>
        </div>
    </div>
    <? endforeach; ?>
</div>
<? endif;