<?php

/**
 * This file is used to edit posts and topics and also to post new topics.
 * This is not used to leave new posts or delete anything.
 *
 * @author Molodoy <molodoy@tibia-me.net>
 * @copyright 2010 (c) Tibia-ME.net
 */
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
Auth::RequireLogin();
// EDITING POST
if (_post('action') == 'postedit' && !empty($_POST['p']) && Forum::post_exists($_POST['p'])
        && !empty($_POST['message'])) {
    Forum::post_edit();
}
// QUOTE
elseif (((_post('action') == 'quote' && isset($_POST['topicID'])) || (_post('action')
        == 'quotetopic' && isset($_POST['forumID']))) && !empty($_POST['message'])
        && !empty($_POST['quote'])) {
    if ($_POST['action'] == 'quote') {
        $sql = $db->query('select `posterID`, `message`, `topicID` from `forumPosts` where `id` = \'' . intval($_POST['quote']) . '\'');
        if (!$sql->num_rows) {
            Document::reload_msg(_('Post not found.'),
                    './viewtopic.php?t=' . $_POST['topicID']);
        }
        $sql = $sql->fetch_row();
        $topicID = $sql[2];
    } else {
        $topicID = intval($_POST['quote']);
        $sql = $db->query('select `authorID`, `message` from `forumTopics` where `id` = \'' . $topicID . '\'');
        if (!$sql->num_rows)
                Document::reload_msg(_('Thread not found.'),
                    './viewforum.php?f=' . $_POST['forumID']);
        $sql = $sql->fetch_row();
    }
    if (!Perms::get(Perms::FORUM_MOD) && Forum::topic_locked($topicID)) {
        Document::reload_msg(_('This thread is closed, you cannot post here.'),
                './viewtopic.php?t=' . $topicID);
    }
    Forum::ForumPostReply($topicID,
            '[quote="' . str_replace('&nbsp;', ' ',
                    strip_tags(User::get_link($sql[0], false))) . '"]' . $sql[1] . '[/quote]' . $_POST['message']);
}
// TOPIC EDIT
elseif (_post('action') == 'topicedit' && !empty($_POST['topicID']) && Forum::topic_exists($_POST['topicID'])
        && !empty($_POST['topicType'])) {
    $page = Document::s_get_page();
    $sql = $db->query('SELECT *
            FROM `forumTopics`
            WHERE `id` = \'' . intval($_POST['topicID']) . '\'')
            ->fetch_assoc();
    $query = $db->query('SELECT COUNT(*)
            FROM `forumPosts`
            WHERE `topicID` = \'' . $sql['id'] . '\'')->fetch_row();
    if ((($_SESSION['user_id'] != $sql['authorID'] || $_SERVER['REQUEST_TIME'] - 300
            > $sql['time'] || $query[0]) && !Perms::get(Perms::FORUM_MOD)) || (Forum::get_forum_id($sql['id'])
            == 6 && !Perms::get(Perms::POST_NEWS))) {
        Document::reload_msg(_('You don\'t have permission to edit this thread.'),
                './viewtopic.php?t=' . $sql['id'] . '&page=' . $page);
    }
    if ($query[0] || $_SERVER['REQUEST_TIME'] - 300 > $sql['time']) {
        ++$sql['edit_count'];
    }
    if (empty($_POST['topicTitle']) || empty($_POST['topicMessage'])) {
        Document::reload_msg(_('Please specify topic title and message.'),
                $_SERVER['PHP_SELF'] . '?t=' . $sql['id'] . '&page=' . $page);
    }
    if (!Perms::get(Perms::FORUM_MOD) && Forum::topic_locked($sql['id'])) {
        Document::reload_msg(_('This thread is closed, you cannot edit it.'),
                './viewtopic.php?t=' . $sql['id'] . '&page=' . $page);
    }
    $forum_id = Forum::get_forum_id($sql['id']);
    // exception for news section
    if ($forum_id != 6) {
        $query = $db->query('SELECT `id`
                FROM `forumTopics`
                WHERE `title` = \'' . $db->real_escape_string($_POST['topicTitle']) . '\'
                AND `forumID` = ' . $forum_id . '
                AND `id` != \'' . $sql['id'] . '\'')->fetch_row();
        if ($query !== null) {
            Document::reload_msg(_('Topic with this name already exists.'),
                    './viewtopic.php?t=' . $query[0] . '&page=' . $page);
        }
    }

    if (!Perms::get(Perms::FORUM_MOD) && $_POST['topicType'] != $sql['type']) {
        $topicType = 'normal';
    } elseif ($_POST['topicType'] == 'normal' || $_POST['topicType'] == 'announcement'
            || $_POST['topicType'] == 'sticky') {
        $topicType = $_POST['topicType'];
    } else {
        $topicType = $sql['type'];
    }

    if ($topicType == 'sticky' && Forum::CountStickyTopics($sql['forumID'],
                    $sql['id']) > 4) {
        $topicType = 'normal';
        Document::msg(_('You cannot create more than 5 sticky topics in this forum.'));
    }

    if ($sql['message'] != $_POST['topicMessage'] || $sql['title'] != $_POST['topicTitle']) {
        $db->query('UPDATE `forumTopics` SET `title` = \'' . $db->real_escape_string($_POST['topicTitle']) . '\', `message` = \'' . $db->real_escape_string($_POST['topicMessage']) . '\', `type` = \'' . $topicType . '\', `edit_timestamp` = \'' . $_SERVER['REQUEST_TIME'] . '\', `edit_count` = \'' . $sql['edit_count'] . '\', `edit_userID` = \'' . $_SESSION['user_id'] . '\' WHERE `id` = \'' . $sql['id'] . '\'');
    } elseif ($topicType != $sql['type']) {
        $db->query('UPDATE `forumTopics` SET `type` = \'' . $topicType . '\' WHERE `id` = \'' . $sql['id'] . '\'');
    }
    Document::reload_msg(_('Changes saved.'),
            './viewtopic.php?t=' . $sql['id'] . '&page=' . $page);
}
// MOVING TOPIC
elseif (_post('action') == 'mv' && isset($_POST['topicID']) && Forum::topic_exists($_POST['topicID'])
        && isset($_POST['moveto']) && Forum::forum_exists($_POST['moveto']) && $_POST['moveto']
        != Forum::get_forum_id($_POST['topicID']) && $_POST['moveto'] != 6 && Perms::get(Perms::FORUM_MOD)) {
    $sql = $db->query('
            SELECT COUNT(*)
            FROM `forumTopics`
            WHERE `movedID` = \'' . $_POST['topicID'] . '\'
            AND `forumID` = \'' . Forum::get_forum_id($_POST['topicID']) . '\'')
            ->fetch_row();
    if ($sql[0] == 0) {
        $sql = $db->query('
                SELECT `forumID`, `last_post_timestamp`
                FROM `forumTopics`
                WHERE `id` = \'' . $_POST['topicID'] . '\'
                LIMIT 1')->fetch_row();
        $db->query('INSERT INTO `forumTopics`
                (`forumID`, `movedID`, `last_post_timestamp`)
                VALUES
                (\'' . $sql[0] . '\', \'' . $_POST['topicID'] . '\', \'' . $sql[1] . '\')'
        );
    }
    $db->query('DELETE FROM `forumTopics` WHERE `movedID` = \'' . $_POST['topicID'] . '\' AND `forumID` = \'' . $_POST['moveto'] . '\'');
    $db->query('UPDATE `forumTopics` SET `forumID` = \'' . $_POST['moveto'] . '\' WHERE `id` = \'' . $_POST['topicID'] . '\'');
    Document::reload_msg(_('Topic has been moved.'),
            './viewtopic.php?t=' . $_POST['topicID']);
}
// POSTING TOPIC
elseif (isset($_REQUEST['f']) && Forum::forum_exists($_REQUEST['f'])) {
    $forum = new Forum;
    $forum->topic_create();
    $mode = 'createtopic';
    $page_title = _('Create topic');
}
if (isset($mode)) {
    goto skipmodeselect;
}
if (!empty($_GET['t']) && Forum::topic_exists($_GET['t'])) {
    $mode = 'topicedit';
    $topicID = intval($_GET['t']);
    $page_title = _('Edit topic');
} elseif (!empty($_GET['p']) && Forum::post_exists($_GET['p'])) {
    $mode = 'postedit';
    $page_title = _('Edit post');
} elseif (!empty($_GET['f']) && Forum::forum_exists($_GET['f'])) {
    $mode = 'createtopic';
    $forumID = intval($_GET['f']);
    $page_title = _('Create topic');
} elseif (!empty($_GET['q']) && Forum::post_exists($_GET['q'])) {
    $mode = 'quote';
    $page_title = _('Quote message');
} elseif (isset($_GET['qt']) && Forum::topic_exists($_GET['qt'])) {
    $mode = 'quotetopic';
    $page_title = _('Quote message');
} elseif (isset($_GET['mv']) && Forum::topic_exists($_GET['mv'])) {
    $mode = 'topicmove';
    $page_title = _('Move topic');
} else {
    $mode = null;
    $page_title = _('Error');
}
skipmodeselect:

    $navi = [array(_('Forum'), './', true)];
if ($mode =='createtopic') {
    if (intval($_REQUEST['f']) == 6 && !Perms::get(Perms::POST_NEWS)) {
        Document::reload_msg(_('You don\'t have permission to post new threads here.'),
                './viewforum.php?f=' . $forumID);
    }
    $sql = $db->query('SELECT *
        FROM `forums`
        WHERE `id` = \'' . $_REQUEST['f'] . '\'')->fetch_assoc();
    $navi[] = array(htmlspecialchars($sql['title'], ENT_COMPAT, 'UTF-8'), './viewforum.php?f=' . $_REQUEST['f']);
}
$document = new Document($page_title, $navi);
if ($mode == 'topicedit') {
    $page = Document::s_get_page();
    $sql = $db->query('SELECT `authorID`,
        `forumID`,
        `title`,
        `type`,
        `locked`,
        `message`,
        `time`
        FROM `forumTopics`
        WHERE `id` = \'' . $topicID . '\'')->fetch_assoc();
    $topicPosts = $db->query('SELECT COUNT(*)
        FROM `forumPosts`
        WHERE `topicID` = \'' . intval($_GET['t']) . '\'')->fetch_row();
    if ((Forum::get_forum_id($topicID) == 6 && !Perms::get(Perms::POST_NEWS)) || (($_SESSION['user_id']
            != $sql['authorID'] || $_SERVER['REQUEST_TIME'] - 300 > $sql['time']
            || $topicPosts[0]) && !Perms::get(Perms::FORUM_MOD))) {
        Document::reload_msg(_('You don\'t have permission to edit this thread.'),
                './viewtopic.php?t=' . $topicID);
    }
    $document->assign(array(
        'page' => $page,
        'title' => htmlspecialchars($sql['title'], ENT_COMPAT, 'UTF-8'),
        'type' => $sql['type'],
        'sticky_limit_reached' => (Forum::CountStickyTopics($sql['forumID'],
                $topicID) >= 5),
        'moderate' => Perms::get(Perms::FORUM_MOD),
        'locked' => $sql['locked'],
        'message' => htmlspecialchars($sql['message'], ENT_COMPAT, 'UTF-8')
    ));
    $document->display('forum_topicedit');
} elseif ($mode == 'postedit') {
    $sql = $db->query('SELECT `message`,
        `topicID`
        FROM `forumPosts`
        WHERE `id` = \'' . intval($_GET['p']) . '\'')->fetch_assoc();
    $document->assign(array(
        'message' => htmlspecialchars($sql['message'], ENT_COMPAT, 'UTF-8'),
        'post_id' => intval($_GET['p']),
        'topicID' => $sql['topicID'],
        'page' => $document->page
    ));
    $document->display('forum_postedit');
} elseif ($mode == 'createtopic') {
    $document->assign(array(
        'sticky_limit_reached' => (Forum::CountStickyTopics($_REQUEST['f']) >= 5),
        'moderate' => Perms::get(Perms::FORUM_MOD),
        'forum_title' => htmlspecialchars($sql['title'], ENT_COMPAT, 'UTF-8'),
        'topic_title' => htmlspecialchars($forum->topic_title, ENT_COMPAT,
                'UTF-8'),
        'message' => htmlspecialchars($forum->message, ENT_COMPAT, 'UTF-8'),
        'topic_type' => $forum->topic_type
    ));
    $document->display('forum_createtopic');
} elseif ($mode == 'quote' || $mode == 'quotetopic') {
    if ($mode == 'quote') {
        $quoteID = intval($_GET['q']);
        $sql = $db->query('SELECT `topicID`,
            `message`
            FROM `forumPosts`
            WHERE `id` = \'' . $quoteID . '\'')->fetch_assoc();
        $topicID = $sql['topicID'];
    } else {
        $quoteID = intval($_GET['qt']);
        $topicID = $quoteID;
        $sql = $db->query('SELECT `message`, `forumID`
            FROM `forumTopics`
            WHERE `id` = \'' . $quoteID . '\'')->fetch_assoc();
        $document->assign('forumID', $sql['forumID']);
    }
    $document->assign(array(
        'message' => Forum::MessageHandler($sql['message']),
        'topicID' => $topicID,
        'quoteID' => $quoteID,
        'mode' => $mode,
        'page' => $document->page
    ));
    $document->display('forum_quote');
} elseif ($mode == 'topicmove') {
    if (!Perms::get(Perms::FORUM_MOD) || Forum::get_forum_id($_GET['mv']) == 6) {
        Document::reload_msg(_('You don\'t have permission to move this thread.'),
                './viewtopic.php?t=' . $_GET['mv']);
    }
    $sql = $db->query('SELECT `id`, `title`
        FROM `forums`
        WHERE `id` != \'' . Forum::get_forum_id($_GET['mv']) . '\'
        AND `id` != \'6\'');
    $moveto = array();
    while ($row = $sql->fetch_row()) {
        $moveto[$row[0]] = $row[1];
    }
    $document->assign(array(
        'moveto' => $moveto
    ));
    $document->display('forum_topicmove');
} else {
    Document::reload_msg(_('Invalid request.'), './');
}
