<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
session_write_close();
CP::auth(Perms::GAMECONTENT_SYNC);
if (!isset($_GET['cat']) || !in_array($_GET['cat'], array('armours', 'weapons', 'monsters', 'spells', 'skills_warrior', 'skills_wizard', 'pets', 'food'))) {
    Document::reload_msg(_('Invalid request.'), './');
}
$sync = new GameContent;
Document::reload_msg($sync->sync($_GET['cat']) ? _('Sync complete.') : _('Unknown error.'), './');
