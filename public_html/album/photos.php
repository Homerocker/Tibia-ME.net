<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
// @todo: check which values we fetch from database for each mode, remove unused
if (isset($_GET['delete'])) {
    if (!Album::photo_exists($_GET['delete'])) {
        Document::reload_msg(_('Invalid request.'), './');
    }
    $sql = $db->query('SELECT CONCAT_WS(\'.\', album_photos.hash, album_photos.extension) as `file`,
        `album_photos`.`albumID`,
        `album_albums`.`userID`,
        `album_albums`.`title`
        FROM `album_photos`, `album_albums`
        WHERE `album_photos`.`id` = \'' . $_GET['delete'] . '\'
        AND `album_photos`.`albumID` = `album_albums`.`id`')
            ->fetch_assoc();
    if ($sql['userID'] != $_SESSION['user_id'] && !Perms::get(Perms::ALBUM_MOD)) {
        Document::reload_msg(_('You don\'t have permission to delete this album.'),
                $_SERVER['PHP_SELF'] . '?album_id=' . $sql['albumID'] . '&page=' . Document::s_get_page());
    }
    Album::photo_remove($_GET['delete'], $sql['file']);
    Document::reload_msg(_('Photo has been deleted.'),
            $_SERVER['PHP_SELF'] . '?album_id=' . $sql['albumID'] . '&page=' . Document::s_get_page());
} elseif (isset($_REQUEST['rotate'])) {
    if (!Album::photo_exists($_REQUEST['rotate'])) {
        Document::reload_msg(_('Invalid request.'), './');
    }
    $sql = $db->query('SELECT `album_photos`.`albumID`,
        `album_albums`.`userID`,
        `album_photos`.`hash`,
        `album_photos`.`extension`,
        `album_photos`.`resolution`
        FROM `album_photos`, `album_albums`
        WHERE `album_photos`.`id` = \'' . $_REQUEST['rotate'] . '\'
        AND `album_photos`.`albumID` = `album_albums`.`id`')
            ->fetch_assoc();
    if ($sql['extension'] == 'gif' && Images::is_animated($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/photos/' . $sql['hash'] . '.' . $sql['extension'])) {
        Document::reload_msg(_('Cannot rotate animated GIF image.'),
                $_SERVER['PHP_SELF'] . '?album_id=' . $sql['albumID'] . '&page=' . Document::s_get_page());
    }
    if ($sql['userID'] != $_SESSION['user_id']) {
        Document::reload_msg(_('You don\'t have permission to rotate this photo.'),
                $_SERVER['PHP_SELF'] . '?album_id=' . $sql['albumID'] . '&page=' . Document::s_get_page());
    }
    $angle = (isset($_REQUEST['angle']) && $_REQUEST['angle'] == 270) ? 270 : 90;
    $photo_rotate_preview = Album::rotate($sql['hash'] . '.' . $sql['extension'],
                    $angle);
    if (isset($photo_rotate_preview['error'])) {
        Document::reload_msg($photo_rotate_preview['error'],
                $_SERVER['PHP_SELF'] . '?album_id=' . $sql['albumID'] . '&page=' . Document::s_get_page());
    }
    if (isset($_POST['submit'])) {
        $sql['resolution'] = explode('x', $sql['resolution']);
        clearstatcache();
        $db->query('UPDATE `album_photos` SET `hash` = \'' . $photo_rotate_preview['hash'] . '\', `filesize` = ' . $db->quote(filesize($photo_rotate_preview['path'])) . ', `resolution` = ' . $db->quote($sql['resolution'][1] . 'x' . $sql['resolution'][0]) . ' WHERE `id` = \'' . $_REQUEST['rotate'] . '\'');
        rename($photo_rotate_preview['path'],
                $_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/photos/' . $photo_rotate_preview['hash'] . '.' . $sql['extension']);
        unlink($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/photos/' . $sql['hash'] . '.' . $sql['extension']);
        Document::reload_msg(_('Photo has been rotated.'),
                $_SERVER['PHP_SELF'] . '?album_id=' . $sql['albumID'] . '&page=' . Document::s_get_page());
    }
    $photo_rotate_preview['thumbnail'] = Images::thumbnail($photo_rotate_preview['path'],
                    CACHE_DIR . '/photos', PHOTO_MEDIUM_WIDTH,
                    PHOTO_MEDIUM_QUALITY);
    $mode = 'rotate';
} elseif (isset($_REQUEST['move'])) {
    if (!Album::photo_exists($_REQUEST['move'])) {
        Document::reload_msg(_('Invalid request.'), './');
    }
    $sql = $db->query('SELECT CONCAT_WS(\'.\', album_photos.hash, album_photos.extension) as file,
        `album_photos`.`albumID`,
        `album_albums`.`userID`,
        `album_albums`.`title`
        FROM `album_photos`,
        `album_albums`
        WHERE `album_photos`.`id` = \'' . $_REQUEST['move'] . '\'
        AND `album_photos`.`albumID` = `album_albums`.`id`')
            ->fetch_assoc();
    if ($sql['userID'] != $_SESSION['user_id']) {
        Document::reload_msg(_('You don\'t have permission to move this photo.'),
                $_SERVER['PHP_SELF'] . '?album_id=' . $sql['albumID'] . '&page=' . Document::s_get_page());
    }
    if (isset($_POST['submit'])) {
        if (empty($_POST['moveto'])) {
            Document::reload_msg(_('Please specify new folder.'),
                    $_SERVER['PHP_SELF'] . '?move=' . $_REQUEST['move'] . '&page=' . Document::s_get_page());
        }
        if (!Album::album_exists($_POST['moveto'])) {
            Document::reload_msg(_('Invalid folder.'),
                    $_SERVER['PHP_SELF'] . '?move=' . $_REQUEST['move'] . '&page=' . Document::s_get_page());
        }
        $db->query('UPDATE `album_photos`
            SET `albumID` = \'' . $_POST['moveto'] . '\'
            WHERE `id` = \'' . $_REQUEST['move'] . '\'');
        Document::reload_msg(_('Photo has been moved.'),
                $_SERVER['PHP_SELF'] . '?album_id=' . $_POST['moveto'] . '&page=' . Document::s_get_page());
    }
    $mode = 'move';
} elseif (isset($_GET['avatar'])) {
    if (!Album::photo_exists($_GET['avatar'])) {
        Document::reload_msg(_('Invalid request.'), './');
    }
    $sql = $db->query('SELECT CONCAT_WS(\'.\', album_photos.hash, album_photos.extension) as `file`,
        `album_photos`.`albumID`,
        `album_albums`.`userID`,
        `album_albums`.`title`
        FROM `album_photos`,
        `album_albums`
        WHERE `album_photos`.`id` = \'' . $_GET['avatar'] . '\'
        AND `album_photos`.`albumID` = `album_albums`.`id`')
            ->fetch_assoc();
    if ($sql['userID'] != $_SESSION['user_id']) {
        Document::reload_msg(
                _('You don\'t have permission to set this photo as avatar.'),
                $_SERVER['PHP_SELF']
                . '?album_id=' . $sql['albumID'] . '&page=' . Document::s_get_page()
        );
    }
    $db->query('UPDATE `user_profile`
            SET `avatarID` = \'' . $_GET['avatar'] . '\'
            WHERE `id` = \'' . $_SESSION['user_id'] . '\'');
    Document::reload_msg(_('Changes saved.'),
            $_SERVER['PHP_SELF'] . '?album_id=' . $sql['albumID'] . '&page=' . Document::s_get_page());
} elseif (isset($_POST['upload']) && isset($_POST['album_id']) && Album::album_exists($_POST['album_id'])) {
    $sql = $db->query('SELECT `userID`
        FROM `album_albums`
        WHERE `id` = \'' . $_POST['album_id'] . '\'')->fetch_row();
    if ($sql[0] != $_SESSION['user_id']) {
        Document::reload_msg(_('You don\'t have permission to upload photos to this album.'),
                $_SERVER['PHP_SELF']
                . '?album_id=' . $sql[0] . '&page=' . Document::s_get_page());
    }
    $result = '';
    for ($i = 0; $i < 5; ++$i) {
        $upload = Uploader::upload('photo',
                        array('type' => 'image', 'index' => $i));
        if ($upload['error'] !== false) {
            $result .= (($i > 0) ? '<br/>' : '') . $upload['error'];
        } else {
            $db->query('INSERT INTO `album_photos` (
                    `albumID`, `hash`, `extension`, `filesize`, `resolution`, `timestamp`
                ) VALUES (
                    ' . $_POST['album_id'] . ',
                    \'' . $upload['hash'] . '\',
                    \'' . $upload['extension'] . '\',
                    \'' . $upload['filesize'] . '\',
                    \'' . $upload['resolution'] . '\',
                    UNIX_TIMESTAMP()
                )');
            $db->query('INSERT INTO `comments_watch`
                (
                    `user_id`,
                    `target_type`,
                    `target_id`
                ) VALUES (
                    ' . $_SESSION['user_id'] . ',
                    \'photo\',
                    ' . $db->insert_id . '
                )');
            $result .= (($i != 0) ? '<br/>' : '') . _('File uploaded.');
        }
    }
    Document::reload_msg($result,
            $_SERVER['PHP_SELF'] . '?album_id=' . $_POST['album_id'] . '&page=last');
} else {
    if (!isset($_REQUEST['album_id']) || !Album::album_exists($_REQUEST['album_id'])) {
        Document::reload_msg(_('Invalid request.'), './');
    }
    $sql = $db->query('SELECT `album_albums`.`friends_only`,
        `album_albums`.`userID`,
        `album_albums`.`title`,
        `album_albums`.`password`
        FROM `album_albums`
        WHERE `album_albums`.`id` = \'' . $_REQUEST['album_id'] . '\'')
            ->fetch_assoc();
    if ($sql['userID'] != $_SESSION['user_id'] && !Perms::get(Perms::ALBUM_MOD)) {
        require $_SERVER['DOCUMENT_ROOT'] . '/../classes/friendlist.class.php';
        if ($sql['friends_only'] && Friendlist::get_status($sql['userID']) !== true) {
            Document::reload_msg(
                    _('You don\'t have permission to view this album.'),
                    './albums.php?u=' . $sql['userID']
            );
        }
        if (isset($sql['password']) && (!isset($_SESSION['album_password']) || $_SESSION['album_password']
                != $sql['password'])) {
            if (isset($_POST['submit'])) {
                if ($_POST['password'] != $sql['password']) {
                    Document::reload_msg(_('Incorrect password.'),
                            './albums.php?u=' . $sql['userID']);
                }
                $_SESSION['album_password'] = $_POST['password'];
            } else {
                $mode = 'password';
            }
        }
    }
    if (!isset($mode)) {
        $mode = null;
    }
}
$navi[] = array(
    $sql['userID'] == $_SESSION['user_id'] ? array(_('My photos'), './albums.php')
        : array(User::get_link($sql['userID'], false), './albums.php?u=' . $sql['userID'])
);
if ($mode == null) {
    $album = new Album;
    $album->photos($_REQUEST['album_id'], 12);
}
if ($mode == null && $_SESSION['user_id'] == $album->userID || Perms::get(Perms::ALBUM_MOD)) {
    $navi = array_merge($navi,
            array(array(_('Edit album'), './albums.php?id=' . $_REQUEST['album_id']),
        array(_('Delete album'), './albums.php?delete=' . $_REQUEST['album_id'])));
}
$document = new Document(_('Photo album'), $navi);
switch ($mode) {
    case 'rotate':
        $document->assign(array(
            'thumbnail' => $photo_rotate_preview['thumbnail'],
            'path' => $photo_rotate_preview['path'],
            'angle' => $angle,
            'page' => $document->page,
            'album_id' => $sql['albumID'],
            'photo_id' => $_REQUEST['rotate']
        ));
        $document->display('album_rotate');
        break;
    case 'move':
        $document->assign(array(
            'album_id' => $sql['albumID'],
            'page' => $document->page,
            'photo_id' => $_REQUEST['move'],
            'n' => (is_array($_REQUEST['move']) ? count($_REQUEST['move']) : 1),
            'folders' => Album::fetch_move_folders($sql['albumID'])
        ));
        $document->display('album_move');
        break;
    case 'password':
        $document->assign('user_id', $sql['userID']);
        $document->display('album_password');
        break;
    default:
        $document->assign(array(
            'data' => $album->data,
            'u' => $album->userID,
            'page' => $document->page,
            'album_allow_comments' => $album->album_allow_comments
        ));
        $document->display('album_photos');
        if ($_SESSION['user_id'] == $album->userID) {
            $document->display('album_upload');
        }
        $document->get_page($album->pages);
        $document->pages('album_id', $_REQUEST['album_id']);
}
