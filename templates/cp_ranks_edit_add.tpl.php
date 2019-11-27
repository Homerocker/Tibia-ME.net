<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
    <div class="callout primary">
        <label for="name"><?= _('Rank') ?></label>
        <input id="name" type="text" name="name" value="<?= htmlspecialchars($rank['name']) ?>"/>
        <label for="prefix"><?= _('Prefix') ?></label>
        <input id="prefix" type="text" name="prefix" value="<?= $rank['prefix'] ?>"/>
        <fieldset>
            <legend><?= _('Color') ?></legend>
            <?php
            foreach (Ranks::$colors as $color => $name) {
                if ($rank['color'] == $color) {
                    echo '<input type="radio" name="color" value="', $color, '" checked/>';
                } else {
                    echo '<input type="radio" name="color" value="', $color, '"/>';
                }
                if ($color !== null) {
                    echo '<span class="', $color,'">';
                }
                echo $name;
                if ($color !== null) {
                    echo '</span>';
                }
                echo '<br/>';
            }?>
        </fieldset>
        <fieldset>
            <legend><?= _('Permissions') ?></legend>
            <input type="checkbox" name="perms[]" value="<?= Perms::ALBUM_MOD ?>"<? if (in_array(Perms::ALBUM_MOD, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('moderate album') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::ARTWORKS_MOD ?>"<? if (in_array(Perms::ARTWORKS_MOD, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('moderate artworks') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::SCREENSHOTS_MOD ?>"<? if (in_array(Perms::SCREENSHOTS_MOD, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('moderate screenshots') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::THEMES_MOD ?>"<? if (in_array(Perms::THEMES_MOD, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('moderate themes') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::FORUM_MOD ?>"<? if (in_array(Perms::FORUM_MOD, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('moderate forum') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::FORUM_HIDDEN_ACCESS ?>"<? if (in_array(Perms::FORUM_HIDDEN_ACCESS, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('access hidden forums') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::USERS_BAN ?>"<? if (in_array(Perms::USERS_BAN, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('ban users') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::USERS_PROFILE_EDIT ?>"<? if (in_array(Perms::USERS_PROFILE_EDIT, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('edit user profiles') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::CP_ACCESS ?>"<? if (in_array(Perms::CP_ACCESS, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('access Control Panel') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::IGNORE_BAN ?>"<? if (in_array(Perms::IGNORE_BAN, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('ignore bans') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::POST_NEWS ?>"<? if (in_array(Perms::POST_NEWS, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('post news') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::RANKS_ASSIGN ?>"<? if (in_array(Perms::RANKS_ASSIGN, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('assign ranks') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::GAMECODES_ADD ?>"<? if (in_array(Perms::GAMECODES_ADD, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('add game codes') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::GAMECODES_ACTIVATE ?>"<? if (in_array(Perms::GAMECODES_ACTIVATE, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('activate game codes') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::MAINTENANCE ?>"<? if (in_array(Perms::MAINTENANCE, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('maintenance') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::GAMECONTENT_SYNC ?>"<? if (in_array(Perms::GAMECONTENT_SYNC, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('sync encyclopedia') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::GEO_DATA_UPDATE ?>"<? if (in_array(Perms::GEO_DATA_UPDATE, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('update geo data') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::CALENDAR_EDIT ?>"<? if (in_array(Perms::CALENDAR_EDIT, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('edit calendar') ?><br/>
            <input type="checkbox" name="perms[]" value="<?= Perms::FORUM_MOD ?>"<? if (in_array(Perms::CHAT_MOD, $rank['perms'])): echo ' checked'; endif; ?>/> <?= _('moderate chat') ?><br/>
            <?php
            if (isset($_GET['add'])) {
                echo '<input type="hidden" name="add"/>';
            } else {
                echo '<input type="hidden" name="edit" value="', $rank['id'], '"/>';
            }
            ?>
        </fieldset>
        <div class="button-group">
            <input class="button primary" type="submit" value="<?= _('Save') ?>"/>
            <a class="button warning" href="<?= $_SERVER['PHP_SELF'] ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>