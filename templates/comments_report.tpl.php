<div class="callout primary text-center">
    <div class="quote"><?= $comment ?></div>
    <?= _('Are you sure you want to report this comment for rules violation?') ?><br/>
    <form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
        <div>
            <input type="hidden" name="<?= $item_type ?>_id" value="<?= $item_id ?>"/>
            <input type="hidden" name="report" value="<?=$comment_id?>"/>
            <input type="hidden" name="page" value="<?= $page ?>"/>
            <input type="submit" value="<?=_('Report')?>"/>
        </div>
    </form>
    <form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
        <div>
            <input type="hidden" name="<?= $item_type ?>_id" value="<?= $item_id ?>"/>
            <input type="hidden" name="page" value="<?= $page ?>"/>
            <input type="submit" value="<?= _('Cancel') ?>"/>
        </div>
    </form>
</div>
