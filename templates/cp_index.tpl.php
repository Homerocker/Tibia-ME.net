<h3><?= _('Control Panel') ?></h3>
<div class="callout primary">
    <a href="./maintenance.php"><?= _('Maintenance work') ?></a><br/>
    <a href="./ranks.php"><?= _('Ranks') ?></a><br/>
    <a href="./album.php"><?= _('Delete photos') ?></a><br/>
    <a href="./style_preview.php"><?= _('Style preview') ?></a><br/>
    <a href="./sync.php?cat=weapons"><?= _('Sync weapons') ?></a><br/>
    <a href="./sync.php?cat=armours"><?= _('Sync armours') ?></a><br/>
    <a href="./sync.php?cat=monsters"><?= _('Sync monsters') ?></a><br/>
    <a href="./sync.php?cat=spells"><?= _('Sync spells') ?></a><br/>
    <a href="./sync.php?cat=skills_warrior"><?= _('Sync warrior skills') ?></a><br/>
    <a href="./sync.php?cat=skills_wizard"><?= _('Sync wizard skills') ?></a><br/>
    <a href="./sync.php?cat=pets"><?= _('Sync pets') ?></a><br/>
    <a href="./sync.php?cat=food"><?= _('Sync food') ?></a><br/>
    <a href="./selftest.php"><?= _('Verify configuration') ?></a><br/>
    <a href="./gamecodes.php"><?= _('Game codes') ?></a><br/>
    <a href="<?= $_SERVER['SCRIPT_NAME'] ?>?geoupdate"><?= _('Update geo data') ?></a>
</div>
<h3><?= _('Disk usage') ?></h3>
<div class="callout primary">
    <?= _('Cache') ?>: <?= Filesystem::format_size($disk_usage['used_cache']) ?>
    <?php
    if ($disk_usage['used_cache'] !== 0) {
        echo '<br/><a class="button primary small" href="', $_SERVER['SCRIPT_NAME'], '?delete_cache">', _('Clear cache'), '</a>';
    }
    ?>
    <br/><?= _('Total used') ?>: <?= Filesystem::format_size($disk_usage['beget_used']) ?> <img src="<?= Filesystem::usage_bar($disk_usage['beget_used'], $disk_usage['beget_total']) ?>" alt=""/>
</div>