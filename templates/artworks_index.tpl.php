<h3><?=_('Artworks')?></h3>
<?php if (empty($data)): ?>
    <div class="callout secondary text-center"><?=_('No artworks available.')?></div>
<? else: ?>
    <div class="grid-x grid-padding-x grid-padding-y">
    <? foreach ($data as $artwork): ?>
        <div class="cell medium-6 large-4">
            <div class="card">
                <div class="card-section text-center">
            <a href="<?= UPLOAD_DIR ?>/artworks/<?= $artwork['hash'] ?>.<?= $artwork['extension'] ?>">
                <img class="thumbnail" src="<?=$artwork['thumbnail']?>" alt=""/>
            </a>
        <?php Likes::display('artwork', $artwork['id']); ?>
            <b><?=_('Author')?></b>: <?=User::get_link($artwork['uploaderID'])?><br/>
            <?= User::date($artwork['timestamp']) ?><br/>
            <b><?=_('File size')?></b>: <?=(($artwork['filesize'] >= 1048576)
                    ? (round(($artwork['filesize'] / 1048576), 1)._('mb'))
                    : (ceil($artwork['filesize'] / 1024)._('kb')))?><br/>
            <b><?=_('Resolution')?></b>: <?=$artwork['resolution']?>
            <div class="button-group small align-center">
                <a class="button primary" href="./comments.php?artwork_id=<?=$artwork['id']?>">
                    <?=_('Comments')?> (<?=$artwork['comments']?>)
                </a>
            <?php if (Perms::get(Perms::ARTWORKS_MOD)
                    || Artworks::uploader_id($artwork['id'])
                    == $_SESSION['user_id']): ?>
            <?php /* <a href="./upload.php?id=<?=$artwork['id']?>"><?=_('Edit')?></a><br/> */ ?>
                    <a class="button alert" onclick="return confirm('<?= htmlspecialchars(_('Are you sure you want to delete this artwork?')) ?>')" href="<?=$_SERVER['SCRIPT_NAME']?>?delete=<?=$artwork['id']?>&amp;page=<?=$page?>">
                        <?=_('Delete')?>
                    </a>
            <?php endif; ?>
                </div>
        </div>
            </div>
        </div>
    <? endforeach; ?>
    </div>
<? endif;
if (!isset($delete)):
    if ($_SESSION['user_id']): ?>
        <div class="text-center">
            <a class="button primary" href="./upload.php"><?=_('Upload artwork')?></a>
        </div>
    <?php else:
        echo '<div class="callout secondary text-center">',_('Log in to upload artworks.'), '</div>';
    endif;
endif;