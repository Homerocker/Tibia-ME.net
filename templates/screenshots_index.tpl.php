<?php if (!$_SESSION['user_id']) { ?>
    <div class="callout secondary text-center">
        <?= _('Please log in to upload screenshots.') ?>
    </div>
<?php } else { ?>
    <form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post" enctype="multipart/form-data">
        <div class="callout primary">
            <?php
            if (isset($user_id)) {
                echo '<input type="hidden" name="u" value="', $user_id, '"/>';
            }
            if (isset($_GET['page'])) {
                echo '<input type="hidden" name="page" value="', $_GET['page'], '"/>';
            }
            ?>
            <div class="input-group">
                <input class="input-group-field" name="screenshot" type="file"/>
                <div class="input-group-button">
                    <input type="submit" class="button" value="<?= _('Upload') ?>"/>
                </div>
            </div>
        </div>
    </form>
<?php } ?>
<?php
if (empty($data)) {
    echo '<div class="callout primary text-center">';
    if (isset($user_id) && $user_id == $_SESSION['user_id']) {
        echo _('You don\'t have any screenshots yet.');
    } elseif (isset($user_id)) {
        echo _('This user doesn\'t have any screenshots yet.');
    } else {
        echo _('No screenshots.');
    }
    echo '</div>';
} else {
    echo '<div class="grid-x grid-padding-x grid-padding-y">';
    foreach ($data as $key => $screenshot) {
        ?>
        <div class="cell medium-6 large-4">
            <div class="card">
                <div class="card-section text-center">
                    <a href="<?= UPLOAD_DIR ?>/screenshots/<?= $screenshot['hash'] ?>.<?= $screenshot['extension'] ?>">
                        <img class="thumbnail" src="<?= $screenshot['thumbnail'] ?>" alt=""/>
                    </a><br/>
                    <?php Likes::display('screenshot',
                            $screenshot['id']) ?>
                    <?= _('Author') ?>: <?= User::get_link($screenshot['authorID']) ?><br/>
                    <?= User::date($screenshot['timestamp']) ?><br/>
                    <?=
                    sprintf(_('File size: %dkb'),
                            ceil($screenshot['filesize'] / 1024))
                    ?><br/>
                    <div class="button-group small align-center">
                        <a class="button primary" href="./comments.php?screenshot_id=<?= $screenshot['id'] ?>">
                        <?= _('Comments') ?>&nbsp;(<?= $screenshot['comments'] ?>)
                        </a>
                           <?php if ($screenshot['editable']): ?>
                            <a class="button alert" onclick="return confirm('<?= _('Are you sure you want to delete this screenshot?') ?>')" href="<?= $_SERVER['SCRIPT_NAME'] ?>?<?=
                               (isset($user_id) ? 'u=' . $user_id . '&amp;' : '')
                               ?>mode=delete&amp;screenshot_id=<?= $screenshot['id'] ?><?=
                               isset($_GET['page']) ? '&amp;page=' . $_GET['page']
                                           : ''
                               ?>">
                            <?= _('Delete') ?>
                            </a>
        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    echo '</div>';
}