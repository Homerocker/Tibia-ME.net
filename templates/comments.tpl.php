<?php
if ($thumbnail !== null) {
    echo '<div class="flex-container align-center">';
    echo '<div class="card text-center">';
    echo '<div class="card-section">';
    echo '<a href="' . UPLOAD_DIR . $path . '">';
    echo '<img class="thumbnail" src="' . $thumbnail . '" alt=""/>';
    echo '</a>';
    Likes::display($item_type, $item_id);
    if ($watch !== false) {
        if ($watch == 0) {
            echo '<a class="button primary" href="', $_SERVER['SCRIPT_NAME'], '?', $item_type, '_id=', $item_id, '&amp;page=', $page, '&amp;watch=1">', _('Watch for new comments'), '</a>';
        } else {
            echo '<a class="button primary" onclick="return confirm(\'' . _('Are you sure you want to stop watching for new comments?') . '\')" href="', $_SERVER['SCRIPT_NAME'], '?', $item_type, '_id=', $item_id, '&amp;page=', $page, '&amp;watch=0">', _('Stop watching for new comments'), '</a>';
        }
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
if (empty($data)):
    ?>
    <div class="callout secondary text-center">
        <?= _('No one has commented yet.') ?>
    </div>
<? else: ?>
    <div class="grid-x callout primary">
        <? foreach ($data as $i => $row): ?>
            <? if ($i != 0): ?>
                <div class="cell"><hr/></div>
            <? endif; ?>
            <div class="cell medium-4">
                <a href="/user/profile.php?u=<?= $row['user_id'] ?>"><?= User::get_display_name($row['user_id']) ?></a><br/>
                <?= User::date($row['timestamp']) ?>
            </div>
            <div class="cell medium-8">
                <?= Forum::MessageHandler($row['comment']) ?>
                <?php if ($editable || $row['editable']): ?>
                    <div class="text-right">
                        <a class="button alert" onclick="return confirm('<?= htmlspecialchars(_('Are you sure you want to delete this comment?')) ?>')" href="<?= $_SERVER['SCRIPT_NAME'] ?>?<?= $item_type ?>_id=<?= $item_id ?>&amp;page=<?= $page ?>&amp;delete=<?= $row['id'] ?>"><?= _('Delete') ?></a>
                    </div>
                <? endif; ?>
            </div>
            <?
            /*
              if ($reportable) {
              if (!$row['reported']) {
              ?>
              <a href="<?= $_SERVER['SCRIPT_NAME'] ?>?<?= $item_type ?>_id=<?= $item_id ?>&amp;page=<?= $page ?>&amp;report=<?= $row['id'] ?>"><?= _('Report') ?></a>
              <?php
              } else {
              echo 'Reported';
              }
              }
             * 
             */
            ?>
        <? endforeach; ?>
    </div>
<? endif; ?>
<?php
if ($_SESSION['user_id']) {
    ?>
    <form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
        <div>
            <textarea name="comment" rows="5"><?= $comment ?></textarea>
            <input type="hidden" name="<?= $item_type ?>_id" value="<?= $item_id ?>"/>
            <input type="hidden" name="page" value="<?= $page ?>"/>
            <input class="button primary" type="submit" value="<?= _('Reply') ?>"/>
        </div>
    </form>
    <?php
} else {
    echo '<div class="warning text-center">';
    echo _('Log in to post your comments.');
    echo '</div>';
}
?>