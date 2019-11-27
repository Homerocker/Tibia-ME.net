<div class="grid-x grid-padding-y grid-padding-x">
    <? if ($liked !== null): ?>
    <div class="cell small-4 nowrap text-right">
        <a<?= ($liked == 1 ? ' class="like_voted"' : '') ?> id="like<?= $target_id ?>" onclick="like_toggle('<?= $target_type ?>', <?= $target_id ?>, 1)" href="javascript:void(0);">
            <img src="/images/like.png" alt="<?= _('Like') ?>"/>
            &nbsp;<span class="text-small" id="rating<?= $target_id ?>up"><?= $up ?></span>
        </a>
    </div>
    <? endif; ?>
    
    <div class="cell small-4 text-center">
        <img id="rating<?= $target_id ?>" src="/images/rating.php?p=<?= $p ?>" alt=""/>
    </div>
    
    <? if ($liked !== null): ?>
    <div class="cell small-4 nowrap text-left">
        <a<?= ($liked == 0 ? ' class="like_voted"' : '') ?> id="dislike<?= $target_id ?>" onclick="like_toggle('<?= $target_type ?>', <?= $target_id ?>, 0)" href="javascript:void(0);">
            <span class="text-small" id="rating<?= $target_id ?>down"><?= $down ?></span>&nbsp;
            <img src="/images/dislike.png" alt="<?= _('Dislike') ?>"/>
        </a>
    </div>
    <? endif; ?>
</div>