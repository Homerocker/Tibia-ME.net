<h3><?= _('Creator') ?></h3>
<ul>
    <li><a href="/user/profile.php?u=1"><?= User::get_display_name(1) ?></a></li>
</ul>
<h3><?= _('Moderators') ?></h3>
<ul>
    <?php
    foreach ($user_ids as $i => $user_id) {
        echo '<li><a href="/user/profile.php?u=', $user_id, '">', User::get_display_name($user_id), '</a></li>';
    }
    ?>
</ul>
<h3><?= _('Translators') ?></h3>
<ul>
    <li><a href="/user/profile.php?u=1"><?= User::get_display_name(1) ?></a> (Russian)</li>
    <li><a href="/user/profile.php?u=47689"><?= User::get_display_name(47689) ?></a> (Português)</li>
    <li><a href="/user/profile.php?u=47248"><?= User::get_display_name(47248) ?></a> (Polish)</li>
</ul>
<!--
<div class="callout primary">
    <p><span class="red">We are actively searching for new translators for the following languages:</span><br/>
    <b>Indonesian</b><br/>
    <b>Malay</b><br/>
    <b>Hindi</b><br/>
    <b>Tagalog</b></p>
    <p>All translators gain access to TibiaME closed Beta Tests available to official fansites.</p>
    <p>Please send your applications to <span class="nowrap"><?= SERVER_ADMIN ?></span>.</p>
</div>
-->