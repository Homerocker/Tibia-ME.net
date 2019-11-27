<?php
require $_SERVER['DOCUMENT_ROOT'].'/../config.php';
CP::auth(Perms::ALBUM_MOD);

$pages = $db->query('SELECT COUNT(*) FROM `album_photos`')->fetch_row();
$page = Document::s_get_page(ceil($pages[0]/600));

if (isset($_POST['delete'])) {
    $i = 0;
    foreach ($_POST['id'] as $id) {
        ++$i;
        Album::photo_remove($id);
    }
    Document::reload_msg($i.' photos removed', $_SERVER['PHP_SELF'].'?page='.$page);
}

$document = new Document(_('Control panel'));

$sql = $db->query('SELECT CONCAT_WS(\'.\', album_photos.hash, album_photos.extension) as filename, album_albums.userID, album_photos.id FROM `album_photos`, album_albums where album_albums.id = album_photos.albumID LIMIT '.(($document->page-1)*600).', 600');
echo '<form action="'.$_SERVER['PHP_SELF'].'?page='.$document->page.'" method="post">';
while ($row = $sql->fetch_assoc()) {
    echo '<div class="border-bottom-solid">';
    echo '<img src="'.Images::thumbnail($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/photos/' . $row['filename'], CACHE_DIR.'/photos', PHOTO_MEDIUM_WIDTH, PHOTO_MEDIUM_QUALITY).'" width="'.PHOTO_MEDIUM_WIDTH.'" alt=""/>';
    echo '<input type="checkbox" name="id[]" value="'.$row['id'].'"></div>';
}
echo '<input type="submit" name="delete" value="'._('Delete').'">
    </form>';

?>
