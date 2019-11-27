<h3>
    <?php
    switch ($folder) {
        case 'inbox':
            echo _('Inbox');
            break;
        case 'outbox':
            echo _('Outbox');
            break;
        case 'sentbox':
            echo _('Sentbox');
            break;
        case 'savebox':
            echo _('Savebox');
            break;
    }
    ?>
</h3>
<ul class="tabs" data-tabs>
    <li class="tabs-title<?= ($folder == 'inbox' ? ' is-active' : '') ?>"><a href="<?= $_SERVER['PHP_SELF'] ?>?folder=inbox"<?= ($folder == 'inbox' ? ' aria-selected="true"' : '' ) ?>><?= _('Incoming') ?></a></li>
    <li class="tabs-title<?= ($folder == 'outbox' ? ' is-active' : '') ?>"><a href="<?= $_SERVER['PHP_SELF'] ?>?folder=outbox"<?= ($folder == 'outbox' ? ' aria-selected="true"' : '' ) ?>><?= _('Outgoing') ?></a></li>
    <li class="tabs-title<?= ($folder == 'sentbox' ? ' is-active' : '') ?>"><a href="<?= $_SERVER['PHP_SELF'] ?>?folder=sentbox"<?= ($folder == 'sentbox' ? ' aria-selected="true"' : '' ) ?>><?= _('Sent') ?></a></li>
    <li class="tabs-title<?= ($folder == 'savebox' ? ' is-active' : '') ?>"><a href="<?= $_SERVER['PHP_SELF'] ?>?folder=savebox"<?= ($folder == 'savebox' ? ' aria-selected="true"' : '' ) ?>><?= _('Saved') ?></a></li>
</ul>
<? if (isset($data['error'])) {
    echo '<div class="callout warning text-center">';
    echo $data['error'];
    echo '</div>';
} else {
    echo '<div class="callout primary">';
    echo _('From') . ': ' . $sender . '<br/>';
    echo _('To') . ': ' . $data['to'] . '<br/>';
    echo User::date($data['timestamp']), '<br/>';
    echo _('Subject') . ': ' . $data['subject'] . '<br/><br/>';
    echo $data['message'];
    echo '</div>';
    if ($folder != 'savebox' && $data['saved']) {
        echo '<div class="callout secondary text-center">';
        echo _('This letter is saved.');
        echo '</div>';
    }
    echo '<div class="button-group stacked-for-small">';
    if ($folder == 'inbox') {
        echo '<a class="button primary" href="'.$_SERVER['SCRIPT_NAME'].'?folder=inbox&amp;u='.$data['from'].'&amp;compose='.$_GET['view'].'">'._('Reply').'</a>';
    }
    if ($folder != 'savebox' && !$data['saved']) {
        echo '<a onclick="return confirm(\''.htmlspecialchars(_('Are you sure you want to save this letter?')).'\')" class="button primary" href="' . $_SERVER['SCRIPT_NAME'] . '?folder=' . $folder . '&amp;page=' . $page . '&amp;save=' . $_GET['view'] . '">' . _('Save') . '</a>';
    }
    if ($folder == 'outbox') {
        echo '<a class="button primary" href="'.$_SERVER['SCRIPT_NAME'].'?folder='.$folder.'&amp;edit='.$_GET['view'].'">'._('Edit').'</a>';
    }
    echo '<a onclick="return confirm(\''.htmlspecialchars(_('Are you sure you want to delete this letter?')).'\')" class="button alert" href="' . $_SERVER['SCRIPT_NAME'] . '?folder=' . $folder . '&amp;delete=' . $_GET['view'] . '&amp;page='.$page.'">' . _('Delete') . '</a>';
    echo '</div>';
}
?>