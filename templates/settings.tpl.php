<h3><?= _('Settings') ?></h3>
<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <label for="tz"><?= _('Timezone') ?></label>
        <select id="tz" name="timezone">
            <? foreach ($timezones as $timezone): ?>
                <option value="<?= $timezone ?>"<?=
                ($_SESSION['user_timezone'] == $timezone ? ' selected' : '')
                ?>>
                            <? $timezone = strval($timezone) ?>
                            <?
                            if (strstr($timezone, '.')):
                                if ($timezone < 0):
                                    $h = ceil($timezone);
                                elseif ($timezone > 0):
                                    $h = '+' . floor($timezone);
                                endif;
                                $m = abs($timezone * 60) - abs($h * 60);
                                ?>
                        GMT <?= $h ?>:<?= $m; ?>
                        <?
                    else:
                        if ($timezone > 0):
                            $timezone = '+' . $timezone;
                            ?>
                        <? endif; ?>
                        GMT
                        <? if ($timezone != 0): ?>
                            &nbsp;<?= $timezone ?>
                        <? endif; ?>
                    <? endif; ?>
                </option>
            <? endforeach; ?>
        </select>
        <label for="hide_email"><?= _('Hide email') ?></label>
        <div class="switch">
            <input class="switch-input" id="hide_email" type="checkbox" name="hide_email" value="1"<?=
            ($data['hide_email'] == 1 ? ' checked' : '')
            ?>/>
            <label class="switch-paddle" for="hide_email">
            </label>
        </div>

        <label for="letters_notify"><?= _('Notify on letters') ?></label>
        <div class="switch">
            <input class="switch-input" id="letters_notify" type="checkbox" name="letters_notify" value="1"<?=
            ($data['letters_notify'] == 1 ? ' checked' : '')
            ?>/>
            <label class="switch-paddle" for="letters_notify">
            </label>
        </div>

        <label for="locale"><?= _('Language') ?></label>
        <select id="locale" name="locale">
            <?php
            foreach (LOCALES as $key => $language) {
                echo '<option value="', $key, '"', (($_SESSION['locale'] == $key)
                            ? ' selected="selected"' : ''), '>', $language, '</option>';
            }
            ?>
        </select>

        <label for="album_allow_comments"><?= _('Allow album comments') ?></label>
        <div class="switch">
            <input class="switch-input" id="album_allow_comments" type="checkbox" name="album_allow_comments" value="1"<?=
            ($data['album_allow_comments'] == 1 ? ' checked' : '')
            ?>/>
            <label class="switch-paddle" for="album_allow_comments">
            </label>
        </div>

        <label for="album_comments_notify"><?= _('Notify on new comments') ?></label>
        <div class="switch">
            <input class="switch-input" id="album_comments_notify" type="checkbox" name="album_comments_notify" value="1"<?=
            ($data['album_comments_notify'] == 1 ? ' checked' : '')
            ?>/>
            <label class="switch-paddle" for="album_comments_notify">
            </label>
        </div>

        <label for="forum_posts_per_page"><?= _('Forum posts per page') ?></label>
        <input type="number" id="forum_posts_per_page" name="forum_posts_per_page" size="2" maxlength="2" value="<?= $data['forum_posts_per_page'] ?>"/>
        <label for="forum_topics_per_page"><?= _('Forum topics per page') ?></label>
        <input type="number" id="forum_topics_per_page" name="forum_topics_per_page" size="2" maxlength="2" value="<?= $data['forum_topics_per_page'] ?>"/>

        <? if (!empty($devices)): ?>
            <h3><?= _('Saved devices') ?></h3>
            <div class="grid-x grid-padding-x">
                <? foreach ($devices as $id => $device): ?>
                    <div class="cell medium-6">
                        <? if (isset($device['browser'])): ?>
                            <? if (isset($device['platform'])): ?>
                                <?=
                                sprintf(_('%s on %s'),
                                        isset($device['version']) ? $device['browser'] . ' ' . $device['version']
                                                    : $device['browser'],
                                        $device['platform'])
                                ?><br/>
                            <? else: ?>
                                <?= $device['browser'] ?><br/>
                            <? endif; ?>
                        <? elseif (isset($device['platform'])): ?>
                            <?= $device['platform'] ?><br/>
                        <? endif; ?>
                        <? if (isset($device['country_code'])): ?>
                            <?=
                            sprintf(_('Location: %s'),
                                    geo::getCountries($device['country_code']))
                            ?><br/>
                        <? endif; ?>
                        <? if (isset($device['created'])): ?>
                            <?=
                            sprintf(_('Saved: %s'),
                                    User::date($device['created']))
                            ?><br/>
                        <? endif; ?>
                        <?=
                        sprintf(_('Last visit: %s'),
                                User::date($device['timestamp']))
                        ?>
                        <div class="switch">
                            <input class="switch-input" id="device_<?= $id ?>" type="checkbox" name="devices[]" value="<?= htmlspecialchars($device['token']) ?>" checked/>
                            <label class="switch-paddle" for="device_<?= $id ?>"></label>
                        </div>
                    </div>
                <? endforeach; ?>
            </div>
        <? endif; ?>
        <input type="submit" class="button primary" name="submit" value="<?= _('Save') ?>"/>
    </div>
</form>