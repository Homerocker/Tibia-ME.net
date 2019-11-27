<? if (isset($error)): ?>
    <div class="callout warning">
        <? foreach ($error as $key => $value): ?>
            <? if ($key != 0): ?>
                <br/>
            <? endif ?>
            <?= $value ?>
        <? endforeach;
        ?>
    </div>
<? endif ?>
<? if ($data['editable']): ?>
    <form action="<?= $_SERVER['SCRIPT_NAME'] ?>?u=<?= $data['id'] ?>" method="post">
    <? endif ?>
    <div class="callout primary">
        <?= (User::gender_icon($data['id']) ? '&nbsp;' : '') ?>

        <b><?= $data['nickname'] ?></b><br/>
        <?= User::rank($data['id']) ?><br/>
        [ID: <?= $data['id'] ?>]<br/>

        <?=
        (User::get_status($data['id']) ? '<span class="label success">' . _('Online') . '</span>'
                    : '<span class="label alert">' . _('Offline') . '</span>')
        ?><br/>

        <? if (!empty($data['avatar'])): ?>
            <img src="<?=
            Images::thumbnail($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/photos/' . $data['avatar'],
                    CACHE_DIR . '/avatars', 110, 70)
            ?>" width="110" alt=""/><br/>
                 <? if ($data['editable']): ?>
                <a onclick="return confirm('<?= htmlspecialchars(_('Are you sure you want to remove avatar?')) ?>')" class="button primary" href="<?= $_SERVER['SCRIPT_NAME'] ?>?u=<?= $data['id'] ?>&amp;avatar_remove"><?= _('Remove avatar') ?></a><br/>
            <? endif ?>
        <? endif ?>

        <? if ($data['banned']): ?>
            <span class="label alert"><?= _('Banned') ?></span><br/>
        <? endif ?>

        <?= _('World') ?>: <?= $data['world'] ?><br/>

        <? if ($data['editable']): ?>
            <label for="guild"><?= _('Guild') ?></label>
        <? else: ?>
            <b><?= _('Guild') ?></b>:<br/>
        <? endif ?>
        <? if (!empty($data['real_guild'])): ?>
            <? if ($data['editable']): ?>
                <input type="hidden" name="guild" value="<?= $data['real_guild'] ?>"/>
                <input type="text" id="guild" size="10" disabled value="<?= $data['real_guild'] ?>"/>
            <? else: ?>
                <?= $data['real_guild'] ?><br/>
            <? endif ?>
        <? elseif ($data['editable']): ?>
            <input type="text" id="guild" name="guild" size="10" maxlength="10" value="<?= $data['guild'] ?>"/>
        <? elseif (!empty($data['guild'])): ?>
            <?= $data['guild'] ?><br/>
        <? endif ?>

        <? if ($data['editable']): ?>
            <label for="vocation"><?= _('Vocation') ?></label>
        <? else: ?>
            <b><?= _('Vocation') ?></b>:<br/>
        <? endif ?>
        <? if (!empty($data['real_vocation'])): ?>
            <input type="hidden" name="vocation" value="<?= $data['real_vocation'] ?>"/>
            <select id="vocation" disabled="disabled">
                <option selected="selected">
                    <? if ($data['real_vocation'] == 'warrior'): ?>
                        <?= _('warrior') ?>
                    <? else: ?>
                        <?= _('wizard') ?>
                    <? endif ?>
                </option>
            </select>
        <? elseif ($data['editable']): ?>
            <select id="vocation" name="vocation">
                <option value=""><?= _('not specified') ?></option>
                <? if ($data['vocation'] == 'warrior'): ?>
                    <option value="warrior" selected="selected"><?= _('warrior') ?></option>
                <? else: ?>
                    <option value="warrior">
                        <?= _('warrior') ?></option>
                <? endif ?>
                <? if ($data['vocation'] == 'wizard'): ?>
                    <option value="wizard" selected="selected">
                        <?= _('wizard') ?></option>
                <? else: ?>
                    <option value="wizard"><?= _('wizard') ?></option>
                <? endif ?>
            </select>
        <? elseif ($data['vocation'] == 'warrior'): ?>
            <?= _('warrior') ?>
        <? elseif ($data['vocation'] == 'wizard'): ?>
            <?= _('wizard') ?>
        <? else: ?>
            <?= _('not specified') ?>
        <? endif; ?>
        <br/>

        <? if (Perms::get(Perms::RANKS_ASSIGN)): ?>
            <label for="rank"><?= _('Rank') ?></label> 
            <?
            $ranks = Ranks::get_list(null, $_SESSION['user_rank'])
            ?>
            <select id="rank" name="rank">
                <? foreach ($ranks as $rank): ?>
                    <? if ($data['rank'] == $rank['id']): ?>
                        <option value="<?= $rank['id'] ?>" selected><?= _($rank['name']) ?></option>
                    <? else: ?>
                        <option value="<?= $rank['id'] ?>"><?= _($rank['name']) ?></option>
                    <? endif; ?>
                <? endforeach; ?>
            </select>
        <? endif; ?>

        <? if ($data['editable']): ?>
            <label for="name"><?= _('Name') ?></label>
            <input id="name" type="text" name="name"
                   value="<?= htmlspecialchars($data['name']) ?>"/>
               <? else: ?>
            <b><?= _('Name') ?></b>:<br/>
            <?= $data['name'] ?><br/>
        <? endif; ?>

        <? if ($data['editable']): ?>
            <label for="gender"><?= _('Gender') ?></label>
            <select id="gender" name="gender">
                <option value=""><?= _('not specified') ?></option>
                <? if ($data['gender'] == 'male'): ?>
                    <option value="male" selected="selected"><?= _('Male') ?></option>
                <? else: ?>
                    <option value="male"><?= _('Male') ?></option>
                <? endif ?>
                <? if ($data['gender'] == 'female'): ?>
                    <option value="female" selected><?= _('Female') ?></option>s
                <? else: ?>
                    <option value="female"><?= _('Female') ?></option>
                <? endif ?>
            </select><br/>
        <? else: ?>
            <b><?= _('Gender') ?></b>:<br/>
            <?
            switch ($data['gender']) {
                case 'male':
                    echo _('Male') . '<br/>';
                    break;
                case 'female':
                    echo _('Female') . '<br/>';
                    break;
                default:
                    echo _('not specified') . '<br/>';
            }
            ?>
        <? endif; ?>

        <? if ($data['editable']): ?>
            <label for="relationship"><?= _('Relationship status') ?></label>
            <select id="relationship" name="relationship">
                <option value=""><?= _('not specified') ?></option>
                <option value="single"<?=
                ($data['relationship'] == 'single' ? ' selected' : '')
                ?>><?= _('Single') ?></option>
                        <? if ($data['relationship'] == 'relationship'): ?>
                    <option value="relationship" selected="selected"><?= _('In relationship') ?></option>
                <? else: ?>
                    <option value="relationship"><?= _('In relationship') ?></option>
                <? endif ?>
                <? if ($data['relationship'] == 'engaged'): ?>
                    <option value="engaged" selected="selected"><?= _('Engaged') ?></option>
                <? else: ?>
                    <option value="engaged"><?= _('Engaged') ?></option>
                <? endif ?>
                <? if ($data['relationship'] == 'married'): ?>
                    <option value="married" selected="selected"><?= _('Married') ?></option>
                <? else: ?>
                    <option value="married"><?= _('Married') ?></option>
                <? endif ?>
                <? if ($data['relationship'] == 'complicated'): ?>
                    <option value="complicated" selected="selected"><?= _('It\'s complicated') ?></option>';
                <? else: ?>
                    <option value="complicated"><?= _('It\'s complicated') ?></option>
                <? endif ?>
                <? if ($data['relationship'] == 'searching'): ?>
                    <option value="searching" selected="selected"><?= _('Actively searching') ?></option>
                <? else: ?>
                    <option value="searching"><?= _('Actively searching') ?></option>
                <? endif ?>
            </select><br/>
        <? else: ?>
            <b><?= _('Relationship status') ?></b>:<br/>
            <?
            switch ($data['relationship']) {
                case 'single':
                    echo _('Single'), '<br/>';
                    break;
                case 'relationship':
                    echo _('In relationship'), '<br/>';
                    break;
                case 'engaged':
                    echo _('Engaged'), '<br/>';
                    break;
                case 'married':
                    echo _('Married'), '<br/>';
                    break;
                case 'complicated':
                    echo _('It\'s complicated'), '<br/>';
                    break;
                case 'searching':
                    echo _('Actively searching'), '<br/>';
                    break;
                default:
                    echo _('not specified'), '<br/>';
            }
            ?>
        <? endif; ?>

        <? if ($data['editable']): ?>
            <label for="birthday_d"><?= _('Birthday') ?></label>
            <div class="grid-x">
                <select class="cell small-4" id="birthday_d" name="birthday_d">
                    <option value=""></option>
                    <? for ($i = 1; $i <= 31; ++$i): ?>
                        <? if ($i == $data['birthday_d']): ?>
                            <option value="<?= $i ?>" selected="selected">
                                <?= $i ?></option>
                        <? else: ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <? endif ?>
                    <? endfor; ?>
                </select>

                <select class="cell small-4" name="birthday_m">
                    <option value=""></option>
                    <? for ($i = 1; $i <= 12; ++$i): ?>
                        <?
                        switch ($i):
                            case 1:
                                $month = _('January');
                                break;
                            case 2:
                                $month = _('February');
                                break;
                            case 3:
                                $month = _('March');
                                break;
                            case 4:
                                $month = _('April');
                                break;
                            case 5:
                                $month = _('May');
                                break;
                            case 6:
                                $month = _('June');
                                break;
                            case 7:
                                $month = _('July');
                                break;
                            case 8:
                                $month = _('August');
                                break;
                            case 9:
                                $month = _('September');
                                break;
                            case 10:
                                $month = _('October');
                                break;
                            case 11:
                                $month = _('November');
                                break;
                            case 12:
                                $month = _('December');
                                break;
                        endswitch;
                        ?>
                        <option value="<?= $i ?>"<?=
                        ($i == $data['birthday_m'] ? ' selected' : '')
                        ?>><?= $month ?></option>
                            <? endfor;
                            ?>
                </select>

                <select class="cell small-4" name="birthday_y">
                    <option value=""></option>
                    <? $min_year = date('Y') - 120 ?>
                    <?
                    for ($i = date('Y') - 3; $i >= $min_year; --$i):
                        ?>
                        <option value="<?= $i ?>"<?=
                        ($data['birthday_y'] == $i ? ' selected' : '')
                        ?>><?= $i ?></option>
                            <? endfor; ?>
                </select>
            </div>
            <label for="hide_age"><?= _('Hide age') ?></label>
            <div class="switch">
                <input class="switch-input" id="hide_age" value="1" type="checkbox" name="hide_age"<?=
                ($data['hide_age'] ? ' checked' : '')
                ?>>
                <label class="switch-paddle" for="hide_age">
                </label>
            </div>
        <? elseif (!empty($data['birthday'])): ?>
            <b><?= _('Birthday') ?></b>:<br/>
            <?=
            strftime(($data['hide_age'] ? '%e %B' : '%e %B %Y'),
                    strtotime($data['birthday']))
            ?><br/>
        <? else: ?>
            <?= _('not specified') ?><br/>
        <? endif ?>
        <? if (isset($data['age'])): ?>
            <b><?= _('Age') ?></b>: <?= $data['age'] ?><br/>
        <? endif ?>

        <? if ($data['editable']): ?>
            <label for="location"><?= _('Location') ?></label>
            <select id="location" name="country_id">
                <option value=""></option>
                <?
                foreach ($countries as $country_id => $country):
                    ?>
                    <option value="<?= $country_id ?>"<?=
                    ($country_id == $data['country_id'] ? ' selected' : '')
                    ?>>
                                <?= htmlspecialchars($country) ?>
                    </option>
                <? endforeach; ?>
            </select>
        <? elseif (isset($data['country'])): ?>
            <b><?= _('Location') ?></b>:<br/>
            <?= $data['country'] ?><br/>
        <? endif ?>

        <b><?= _('Joined') ?></b>:<br/>
        <? if (!empty($data['joined'])): ?>
            <?= User::date($data['joined']) ?><br/>
        <? else:
            ?>
            <?= _('Before 27th of August, 2009') ?><br/>
        <? endif ?>

        <b><?= _('Last visit') ?></b>:<br/>
        <? if (!empty($data['lastvisit'])): ?>
            <?= User::date($data['lastvisit']) ?><br/>
        <? else: ?>
            <?= _('no data') ?><br/>
        <? endif ?>

        <b><?= _('Total online time') ?></b>:<br/>
        <?=
        sprintf(_('%dh %dmin %dsec'), floor($data['online_time'] / 3600),
                floor(($data['online_time'] - 3600 * floor($data['online_time'] / 3600))
                        / 60),
                ($data['online_time'] - floor($data['online_time'] / 3600) * 3600
                - floor(($data['online_time'] - 3600 * floor($data['online_time']
                                / 3600)) / 60) * 60))
        ?><br/>

        <? if ($data['editable']): ?>
            <label for="email">E-mail</label>
            <input type="text" id="email" name="email" value="<?= htmlspecialchars($data['email']) ?>"/>
            <label for="hide_email"><?= _('Hide email') ?></label>
            <div class="switch">
                <input class="switch-input" id="hide_email" type="checkbox" value="1" name="hide_email"<?=
                ($data['hide_email'] ? ' checked' : '')
                ?>>
                <label class="switch-paddle" for="hide_email">
                </label>
            </div>
        <? elseif (!empty($data['email']) && !$data['hide_email']): ?>
            <b>E-mail</b>:<br/>
            <a href="mailto:<?= $data['email'] ?>"><?= $data['email'] ?></a><br/>
        <? endif ?>

        <? if ($data['editable']): ?>
            <label for="phone">WhatsApp/Telegram</label>
            <input type="text" id="phone" name="whatsapp" value="<?= htmlspecialchars($data['whatsapp']) ?>"/>
        <? elseif (!empty($data['whatsapp'])): ?>
            <b>WhatsApp</b>:<br/>
            <?= $data['whatsapp'] ?><br/>
        <? endif ?>

        <? if ($data['editable']): ?>
            <label for="icq">ICQ</label>
            <input type="text" id="icq" name="icq" value="<?= $data['icq'] ?>"/>
        <? elseif (!empty($data['icq'])): ?>
            <b>ICQ</b>:<br/>
            <?= $data['icq'] ?><br/>
        <? endif ?>

        <? if ($data['editable']): ?>
            <label for="yahoo">Yahoo</label>
            <input type="text" id="yahoo" name="yahoo" value="<?= htmlspecialchars($data['yahoo']) ?>"/>
        <? elseif (!empty($data['yahoo'])): ?>
            <b>Yahoo</b>:<br/>
            <?= $data['yahoo'] ?><br/>
        <? endif ?>

        <? if ($data['editable']): ?>
            <label for="skype">Skype</label>
            <input type="text" id="skype" name="skype" value="<?= htmlspecialchars($data['skype']) ?>"/>
        <? elseif (!empty($data['skype'])): ?>
            <b>Skype</b>:<br/>
            <?= $data['skype'] ?><br/>
        <? endif ?>

        <? if ($data['facebook'] !== null): ?>
            <b>Facebook</b>:<br/>
            <img src="https://graph.facebook.com/<?= $data['facebook']['id'] ?>/picture/" alt=""/><br/>
            <a href="http://facebook.com/<?= $data['facebook']['id'] ?>"><?= $data['facebook']['name'] ?></a>
            <? if ($data['id'] == $_SESSION['user_id']): ?>
                &nbsp;[<a onclick="return confirm('<?= htmlspecialchars(_('Are you sure you want to unlink your Facebook account?')) ?>')" href="<?= $_SERVER['SCRIPT_NAME'] ?>?FBunlink">&times;</a>]
            <? endif ?>
            <br/>
        <? elseif ($_SESSION['user_id'] == $data['id']): ?>
            <b>Facebook</b>:&nbsp;[<a href="./fblink.php">+</a>]<br/>
        <? endif ?>

        <? if ($data['vk'] !== null): ?>
            <b>VKontakte</b>:<br/>
            <img src="<?= $data['vk']['photo_50'] ?>" alt=""/><br/>
            <a href="http://vk.com/id<?= $data['vk']['uid'] ?>"><?= $data['vk']['first_name'] ?>&nbsp;<?= $data['vk']['last_name'] ?></a>
            <? if ($data['id'] == $_SESSION['user_id']): ?>
                &nbsp;[<a onclick="return confirm('<?= htmlspecialchars(_('Are you sure you want to unlink your VK account?')) ?>')" href="<?= $_SERVER['SCRIPT_NAME'] ?>?VKunlink">&times;</a>]
            <? endif ?>
            <br/>
        <? elseif ($_SESSION['user_id'] == $data['id']): ?>
            <b>VK</b>:&nbsp;[<a href="https://oauth.vk.com/authorize?client_id=4020135&amp;scope=&amp;redirect_uri=<?= urlencode('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . '?VKlink') ?>&amp;response_type=code&amp;v=5.4">+</a>]<br/>
        <? endif ?>
        <? if ($data['editable']): ?>
            <label for="signature"><?= _('Signature') ?></label>
            <textarea rows="5" id="signature" name="signature"><?= htmlspecialchars($data['signature']) ?></textarea>
        <? elseif (!empty($data['signature'])): ?>
            <b><?= _('Signature') ?></b>:<br/>
            <?= $data['signature'] ?><br/>
        <? endif ?>

        <b><?= _('Posts at forum') ?></b>: <?= $data['forum_posts'] ?><br/>

        <? if ($data['photos']): ?>
            <a href="/album/albums.php?u=<?= $data['id'] ?>"><b><?= _('Photos') ?></b>: <?= $data['photos'] ?></a><br/>
        <? else: ?>
            <b><?= _('Photos') ?></b>: 0<br/>
        <? endif ?>
        <? if ($data['screenshots']): ?>
            <a href="/screenshots/?u=<?= $data['id'] ?>"><b><?= _('Screenshots') ?></b>: <?= $data['screenshots'] ?></a><br/>
        <? else: ?>
            <b><?= _('Screenshots') ?></b>: 0<br/>
        <? endif ?>
        <? if ($data['themes']): ?>
            <a href="/themes/?u=<?= $data['id'] ?>"><b><?= _('Themes') ?></b>: <?= $data['themes'] ?></a><br/>
        <? else: ?>
            <b><?= _('Themes') ?></b>: 0<br/>
        <? endif ?>
        <? if ($data['friends']): ?>
            <a href="friendlist.php?u=<?= $data['id'] ?>"><b><?= _('Friends') ?></b>: <?= $data['friends'] ?></a><br/>
        <? else: ?>
            <b><?= _('Friends') ?></b>: 0<br/>
        <? endif ?>
        <? if ($data['banishments']): ?>
            <a href="banishments.php?u=<?= $data['id'] ?>"><b><?= _('Banishments') ?></b>: <?= $data['banishments'] ?></a><br/>
        <? else: ?>
            <b><?= _('Banishments') ?></b>: 0<br/>
        <? endif ?>
            <div class="button-group stacked-for-small">
        <? if ($data['editable']): ?>
            <input type="submit" class="button success" value="<?= _('Save') ?>"/>
        <? endif; ?>
            <? if ($data['id'] != $_SESSION['user_id']): ?>
                <? if (!$data['is_friend']): ?>
                    <a onclick="return confirm('<?= _('Are you sure you want to add this user to friendlist?') ?>')" class="button primary" href="./friendlist.php?add=<?= $data['id'] ?>&amp;redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>"><?= _('Add friend') ?></a>
                <? else: ?>
                    <a onclick="return confirm('<?= _('Are you sure you want to remove this user from friendlist?') ?>')" class="button primary" href="./friendlist.php?remove=<?= $data['id'] ?>&amp;redirect=<?= get_redirect(true) ?>"><?= _('Remove friend') ?></a>
                <? endif; ?>
                <a class="button primary" href="./letters.php?compose&amp;u=<?= $data['id'] ?>"><?= _('Send letter') ?></a>
            <? endif; ?>
            <?
            if (Perms::get(Perms::USERS_BAN) && !$data['banned']):
                ?>
                <a class="button primary" href="./banishments.php?ban=<?= $data['id'] ?>"><?= _('Ban') ?></a>
            <? endif ?>
            <? if (Perms::get(Perms::FORUM_MOD)): ?>
                <a class="button alert" onclick="return confirm('<?=
                   htmlspecialchars(sprintf(_('Are you sure you want to delete all posts and topics by this user?')))
                   ?>')" href="<?= $_SERVER['SCRIPT_NAME'] ?>?delete_forum_posts=<?= $data['id'] ?>"><?= _('Delete all posts') ?></a>
               <? endif ?>
        </div>
    </div>
    <? if ($data['editable']): ?>
    </form>
    <? endif;