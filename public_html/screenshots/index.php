<?php

/**
 *  Comments UI.
 * @package screenshots
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 * @version 2.3.07
 */
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$u = Auth::get_u(null);
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'delete') {
    if (!isset($_REQUEST['screenshot_id'])) {
        Document::reload_msg(_('Invalid request.'),
                $_SERVER['PHP_SELF'] . '?' . (isset($u) ? 'u=' . $u . '&' : '') . 'page=' . (isset($_REQUEST['page'])
                            ? $_REQUEST['page'] : 1));
    }
    $sql = $db->query('
                SELECT `authorID`,
                CONCAT_WS(\'.\', `hash`, `extension`) as filename
                FROM `screenshots`
                WHERE `id` = \'' . $_REQUEST['screenshot_id'] . '\'')
            ->fetch_assoc();
    if ($sql === null) {
        Document::reload_msg(_('Screenshot not found.'),
                $_SERVER['PHP_SELF'] . '?' . (isset($u) ? 'u=' . $u . '&' : '') . 'page=' . (isset($_REQUEST['page'])
                            ? $_REQUEST['page'] : 1));
    }
    if (!Perms::get(Perms::SCREENSHOTS_MOD) && $sql['authorID'] != $_SESSION['user_id']) {
        Document::reload_msg(_('You don\'t have permission to delete this screenshot.'),
                $_SERVER['PHP_SELF'] . '?' . (isset($u) ? 'u=' . $u . '&' : '') . 'page=' . (isset($_REQUEST['page'])
                            ? $_REQUEST['page'] : 1));
    }
    Screenshots::remove($_REQUEST['screenshot_id'], $sql['filename']);
    Document::reload_msg(_('The screenshot has been deleted.'),
                $_SERVER['PHP_SELF'] . '?' . (isset($u) ? 'u=' . $u . '&' : '') . 'page=' . (isset($_POST['page'])
                            ? $_POST['page'] : 1));
} elseif (isset($_FILES['screenshot'])) {
    $upload = Uploader::upload('screenshot', array('type' => 'image'));
    if ($upload['error'] === false) {
        $db->query('
            INSERT INTO `screenshots`
            (
                `extension`,
                `authorID`,
                `hash`,
                `filesize`,
                `timestamp`
            ) VALUES (
                \'' . $upload['extension'] . '\',
                \'' . $_SESSION['user_id'] . '\',
                \'' . $upload['hash'] . '\',
                \'' . $upload['filesize'] . '\',
                \'' . $_SERVER['REQUEST_TIME'] . '\'
            )'
        );
        $db->query('INSERT INTO `comments_watch` (
                `user_id`,
                `target_type`,
                `target_id`
            ) VALUES (
                ' . $_SESSION['user_id'] . ',
                \'screenshot\',
                ' . $db->insert_id . '
            )');
        Document::reload_msg(_('Screenshot has been uploaded.'),
                $_SERVER['PHP_SELF'] . '?' . (isset($u) ? 'u=' . $u . '&' : '') . 'page=' . (isset($_REQUEST['page'])
                            ? $_REQUEST['page'] : 1));
    } else {
        Document::reload_msg($upload['error'],
                $_SERVER['PHP_SELF'] . '?' . (isset($u) ? 'u=' . $u . '&' : '') . 'page=' . (isset($_REQUEST['page'])
                            ? $_REQUEST['page'] : 1));
    }
} else {
    $mode = null;
}
if (isset($u) || isset($mode)) {
    $navi = array(
        [_('Screenshots'), $_SERVER['PHP_SELF'] . (isset($_GET['page']) ? '?page=' . $_GET['page']
            : '')]
    );
} else {
    $navi = [];
}
if (isset($u) && isset($mode)) {
    $navi[] = array(
        ($u == $_SESSION['user_id']) ? _('My screenshots') : User::get_link($u,
                false), $_SERVER['PHP_SELF'] . '?u=' . $u . (isset($_GET['page'])
            ? '&amp;page=' . $_GET['page'] : '')
    );
}
if (isset($mode) && $mode == 'comments_delete') {
    $navi[] = array(
        _('Comments'),
        $_SERVER['PHP_SELF'] . '?mode=comments&amp;screenshot_id=' . $sql['screenshotID'] . (isset($_GET['page'])
            ? '&amp;page=' . $_GET['page'] : '')
    );
} elseif ($_SESSION['user_id'] && (!isset($u) || $u != $_SESSION['user_id'])) {
    $navi[] = array(
        _('My screenshots'),
        $_SERVER['PHP_SELF'] . '?u=' . $_SESSION['user_id'] . (isset($_GET['page'])
            ? '&amp;page=' . $_GET['page'] : '')
    );
}
$document = new Document(_('Screenshots'), $navi);

switch ($mode) {
    default:
        $screenshots = new Screenshots(12, $u);
        $document->get_page($screenshots->pages);
        $document->assign(array(
            'count' => $screenshots->count - 1,
            'data' => $screenshots->data,
            'user_id' => $u
        ));
        $document->display('screenshots_index');
        if (isset($u)) {
            $document->pages('u', $u);
        } else {
            $document->pages();
        }
}