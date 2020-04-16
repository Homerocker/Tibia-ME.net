<? if (isset($news)): ?>
    <div class="callout primary">
        <h3><?= $news['title'] ?> (<?= $news['date'] ?>)</h3>
        <a class="button primary small"
           href="./forum/viewtopic.php?t=<?= $news['id'] ?>"><?= sprintf(ngettext('%d comment', '%d comments',
                $news['comments']), $news['comments']) ?></a>
    </div>
<? endif; ?>

<div class="callout primary">
    <? foreach ($news_official as $i => $article): ?>
        <? if ($i == 1): ?>
            <div class="display-none" id="news_official">
        <? endif; ?>
        <h3><?= $article['headline'] ?> (<?= $article['date'] ?>)</h3>
        <p><?= $article['body'] ?></p>
        <? if ($i != 0 && $i + 1 == count($news_official)): ?>
            </div>
        <? endif; ?>
    <? endforeach; ?>
    <button class="button primary small" onclick="toggle('news_official', 'news_expand', 'news_collapse')">
        <span id="news_expand"><?= _('view all') ?></span>
        <span id="news_collapse" class="display-none"><?= _('hide') ?></span>
    </button>
</div>
<h3><?= _('Chat') ?></h3>
<div id="chat" class="callout secondary no-margin break-word" style="height: 18rem; overflow-y: scroll;">
</div>
<form action="javascript:void(0)">
    <div class="input-group">
        <input id="chat_message" class="input-group-field text-small" type="text" style="border-top: none;"
               placeholder="<?= _('Type your message') ?>" autocomplete="off" maxlength="600"/>
        <div class="input-group-button">
            <input type="submit" class="button small"
                   onclick="chat_send('chat_message', '<?= $_SESSION['user_nickname'] ?>', <?= $_SESSION['user_world'] ?? 'null' ?>)"
                   value="<?= _('Send') ?>"/>
        </div>
    </div>
</form>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        chat_update();
    });
</script>
<div class="grid-x grid-padding-x grid-padding-y">
    <div class="cell medium-6 large-4">
        <ul>
            <li><a href="./download"><b><?= _('Download') ?></b></a></li>
            <li><a href="./calendar.php"><?= _('Events calendar') ?></a></li>
            <li><a href="./forum"><?= _('Forum') ?>&nbsp;<span
                            class="label secondary"><?= $forum_total ?></span><?php if ($forum_new): ?><span
                            class="label success">+<?= $forum_new ?></span><?php endif; ?></a></li>
            <li><a href="./screenshots"><?= _('Screenshots') ?>&nbsp;<span
                            class="label secondary"><?= $screenshots_total ?></span><?php if ($screenshots_new): ?><span
                            class="label success">+<?= $screenshots_new ?></span><?php endif; ?></a></li>
            <li><a href="./album"><?= _('Photo album') ?>&nbsp;<span
                            class="label secondary"><?= $album_total ?></span><?php if ($album_new): ?><span
                            class="label success">+<?= $album_new ?></span><?php endif; ?></a></li>
            <li><a href="./themes"><?= _('Themes') ?> (S60v2)&nbsp;<span
                            class="label secondary"><?= $themes_total ?></span><?php if ($themes_new): ?><span
                            class="label success">+<?= $themes_new ?></span><?php endif; ?></a></li>
            <li><a href="./artworks"><?= _('Artworks') ?>&nbsp;<span
                            class="label secondary"><?= $artworks_total ?></span><?php if ($artworks_new): ?><span
                            class="label success">+<?= $artworks_new ?></span><?php endif; ?></a></li>
            <?php if ($_SESSION['user_id']): ?>
                <li><a href="./user/memberlist.php"><?= _('Memberlist') ?></a></li>
            <?php endif; ?>
            <?php if (Perms::get(Perms::USERS_BAN)): ?>
                <li><a href="./user/banishments.php?u=0"><?= _('Banlist') ?></a></li>
            <?php endif; ?>
        </ul>
    </div>


    <div class="cell medium-6 large-8 hide-for-small-only">
        <div class="callout">
            <?= _('TibiaME is the first massively multiplayer online role-playing game for mobile devices. Come together with hundreds of players and experience adventures in a colourful virtual world! Along with your friends you explore the mysterious land of TibiaME, fight your way through hordes of evil creatures and solve ancient riddles to find untold treasures. With every monster you defeat you will grow in strength and power. TibiaME is based on the successful online role-playing game Tibia which attracts thousands of players every day.') ?>
        </div>
    </div>

    <div class="cell medium-6 large-4">
        <h4><?= _('Highscores') ?></h4>
        <ul>
            <?php
            if ($char_id) {
                echo '<li><a href="./scores/viewscores.php?characterID=', $char_id, '">', $_SESSION['user_nickname'], '&nbsp;w', $_SESSION['user_world'], '</a></li>';
                if ($guild_name) {
                    echo '<li><a href="./scores/guilds.php?guild=', urlencode($guild_name), '&amp;world=', $_SESSION['user_world'], '">', htmlspecialchars($guild_name), '&nbsp;w', $_SESSION['user_world'], '</a></li>';
                }
            }
            ?>
            <li><a href="./scores/top100.php"><?= _('TOP 100') ?></a></li>
            <li><a href="./scores/hunters.php"><?= _('Hunters') ?></a></li>
            <li><a href="./scores/pvp.php">PvP</a></li>
            <li><a href="./scores/achievements.php"><?= _('Achievements') ?></a></li>
            <li><a href="./scores/guilds.php"><?= _('Guilds') ?></a></li>
            <li><a href="./scores/worlds.php"><?= _('Worlds') ?></a></li>
        </ul>
    </div>

    <div class="cell medium-6 large-4">
        <h4><?= _('Encyclopedia') ?></h4>
        <ul>
            <li><a class="b" href="./gamecontent/calc"><?= _('Armour Calculator') ?></a></li>
            <li><a href="./gamecontent/weapons.php?vocation=warrior"><?= _('Weapons') ?></a></li>
            <li><a href="./gamecontent/armours.php"><?= _('Armours') ?></a></li>
            <li><a href="./gamecontent/monsters.php"><?= _('Monsters') ?></a></li>
            <li><a href="./gamecontent/spells.php"><?= _('Spells') ?></a></li>
            <li><a href="./gamecontent/skills.php?vocation=warrior"><?= _('Skills') ?></a></li>
            <li><a href="./gamecontent/pets.php"><?= _('Pets') ?></a></li>
            <li><a href="./gamecontent/food.php"><?= _('Food and potions') ?></a></li>
        </ul>
    </div>

    <div class="cell medium-6 large-4">
        <h4><?= _('Payment') ?></h4>
        <ul>
            <li><a href="./premium.php"><?= _('Premium') ?></a></li>
            <li><? if ($platinum_discount): ?><span class="label success">-<?= $platinum_discount ?>
                    %</span>&nbsp;<? endif; ?><a href="./platinum.php"><?= _('Platinum') ?></a></li>
        </ul>
    </div>

    <div class="cell medium-6 large-4">
        <h4><?= _('Related sites') ?></h4>
        <ul>
            <li><a href="http://www.tibiame.com" target="_blank"><?= _('Official website') ?></a></li>
            <li class="show-for-small-only"><a href="http://tochki.su/tibiame/mobile/"
                                               target="_blank">Tochki.su/TibiaME</a></li>
            <li class="show-for-medium"><a href="http://tochki.su/tibiame/" target="_blank">Tochki.su/TibiaME</a></li>
        </ul>
    </div>

    <div class="cell medium-6 large-4">
        <h4><?= _('About us') ?></h4>
        <ul>
            <li><a href="./contacts.php"><?= _('Contacts') ?></a></li>
            <li><a href="./staff.php"><?= _('Moderators') ?></a></li>
            <li><a href="./user/agreement.php"><?= _('User agreement') ?></a></li>
            <?php if (Perms::get(Perms::CP_ACCESS)): ?>
                <li><a href="./cp"><b><?= _('Control Panel') ?></b></a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>