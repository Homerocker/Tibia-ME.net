<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
CP::auth();
if (isset($_GET['geoupdate'])) {
    CP::auth(Perms::GEO_DATA_UPDATE);
    Document::reload_msg(geo::update_database() ? _('Geo data locales updated.')
                        : _('Update failed.'));
} elseif (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] === 'delete_cache') {
    Filesystem::emptydir($_SERVER['DOCUMENT_ROOT'] . CACHE_DIR, true, false);
    Document::reload();
}
$document = new Document(_('Control Panel'));
$document->assign(array(
    'disk_usage' => Filesystem::get_disk_usage(),
));
$document->display('cp_index');
