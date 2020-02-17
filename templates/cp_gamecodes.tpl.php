<?php
if (isset($form_add)) {
    ?>
    <h3><?= _('Add') ?></h3>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <div class="callout primary">
            <? $form_add->field('gamecodes')->display(_('Game codes')) ?>
            <? $form_add->field('amount')->display(_('Amount')) ?>
            <? $form_add->field('submit')->display(_('Add')) ?>
        </div>
    </form>
    <?php
}
?>
    <h3>
        <?= _('Overview') ?>
    </h3>
<?php
if (empty($overview)) {
    echo '<div class="callout secondary text-center">';
    echo _('No items to display.');
    echo '</div>';
} else {
    echo '<div class="callout primary">';
    foreach ($overview as $amount => $count) {
        echo $count, '&times;', sprintf(_('%d Platinum'), $amount), '<br/>';
    }
    echo '</div>';
}
if (isset($form_activate)) {
    ?>
    <h3>
        <?= _('Activate') ?>
    </h3>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
        <div class="callout primary">
            <? $form_activate->field('nickname')->display(_('Nickname')) ?>
            <? $form_activate->field('world')->display(_('World')) ?>
            <? $form_activate->field('amount')->display(_('Amount')) ?>
            <? $form_activate->field('submit')->display(_('Activate')) ?>
        </div>
    </form>
    <?php
}
?>
    <h3>
        <?= _('History') ?>
    </h3>
<?php
if (empty($history)) {
    echo '<div class="callout secondary text-center">';
    echo _('No items to display.');
    echo '</div>';
}
foreach ($history as $i => $code) {
    echo '<div class="callout primary">';
    if ($code['failed']) {
        echo '<b><span class="red">', _('FAILURE'), '</span></b><br/>';
    } else {
        echo '<span class="green">', _('SUCCESS'), '</span><br/>';
    }
    echo User::date($code['used_timestamp'], 'Y-m-d H:m'), '<br/>';
    if ($code['failed']) {
        echo '<span class="red text-small">';
    } else {
        echo '<span class="text-small">';
    }
    echo $code['code'], '</span><br/>';
    echo '<b>', sprintf(_('%d Platinum'), $code['amount']), '</b><br/>';
    echo $code['nickname'], '&nbsp;', _('world'), '&nbsp;', $code['world'], '<br/>';
    echo sprintf(_('Used by: %s'), User::get_link($code['modified_mod_id'])), '<br/>';
    // @todo restore failed gamecodes
    //if ($code['failed']) {
    //    echo '<a href="?restore=', $code['code'], '">', _('Restore'), '</a>';
    //}
    echo '</div>';
}
?>
    <h3>
        <?= _('Available') ?>
    </h3>
<?php
if (empty($codes_available)) {
    echo '<div class="callout secondary text-center">';
    echo _('No items to display.');
    echo '</div>';
}
foreach ($codes_available as $i => $code) {
    echo '<div class="callout primary">';
    if ($code['failed']) {
        echo '<span class="red text-small">';
    } else {
        echo '<span class="text-small">';
    }
    echo $code['code'], '</span><br/>';
    echo '<b>', sprintf(_('%d Platinum'), $code['amount']), '</b><br/>';
    echo sprintf(_('Added by %s'), User::get_link($code['added_mod_id'])), '<br/>';
    //echo '<a href="?delete=', $code['code'], '">', _('Delete'), '</a>';
    echo '</div>';
}