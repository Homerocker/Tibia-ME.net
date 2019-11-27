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
<?php
if (empty($data)) {
    echo '<div class="callout secondary text-center">';
    echo _('This folder is empty.');
    echo '</div>';
} else {
    echo '<div class="callout primary">';
    echo '<div class="grid-x grid-padding-x">';
    foreach ($data as $key => $letter): ?>
        <? if ($key != 0): ?>
            <div class="cell">
                <hr/>
            </div>
        <? endif; ?>
        <div class="cell medium-4 large-3">
            <?php
            switch ($folder) {
                case 'inbox':
                    echo _('From') . ' ' . $letter['from'] . '<br/>';
                    break;
                case 'outbox':
                case 'sentbox':
                    echo _('To') . ' ' . $letter['to'] . '<br/>';
                    break;
                case 'savebox':
                    echo _('From') . ' ' . $letter['from'] . '<br/>';
                    echo _('To') . ' ' . $letter['to'] . '<br/>';
                    break;
            }
            ?>
            <?= $letter['timestamp'] ?>
        </div>
        <div class="cell medium-8 large-9">
            <? if ($letter['flag']): ?>
                <? switch ($folder):
                    case 'inbox': ?>
                        <span class="label success"><?= _('new') ?></span>
                        <? break;
                    case 'outbox': ?>
                        <span class="label alert"><?= _('unread') ?></span>
                        <? break;
                    default:
                        break;
                endswitch; ?>
            <? endif; ?>
            <a href="<?= $_SERVER['SCRIPT_NAME'] ?>?folder=<?= $folder ?>&amp;view=<?= $letter['id'] ?>&amp;page=<?= $page ?>">
                <?= $letter['subject'] ?>
            </a>
        </div>
    <? endforeach; ?>
        </div>
    </div>
        <?php
}