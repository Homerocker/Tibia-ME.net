<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
if (!empty($_GET['d']) && Forum::post_exists($_GET['d'])) {
    $page = Document::s_get_page();
    $sql = $db->query('SELECT `id`,
            `posterID`,
            `topicID`
            FROM `forumPosts`
            WHERE `id` = \'' . intval($_GET['d']) . '\'')
            ->fetch_assoc();
    if (!Perms::get(Perms::FORUM_MOD) && Forum::topic_locked($sql['topicID'])) {
        Document::reload_msg(_('This thread is closed, you cannot delete posts.'),
                $_SERVER['PHP_SELF'] . '?t=' . $sql['topicID'] . '&page=' . $page);
    }
    $topicID = $sql['topicID'];
    $postID = $sql['id'];
    $posterID = $sql['posterID'];
    $sql = $db->query('SELECT `id`,
            `time`
            FROM `forumPosts`
            WHERE `topicID` = \'' . $topicID . '\'
            ORDER BY `time` DESC LIMIT 1')->fetch_assoc();
    if (!Perms::get(Perms::FORUM_MOD) && ($posterID != $_SESSION['user_id'] || $postID
            != $sql['id'] || $_SERVER['REQUEST_TIME'] - $sql['time'] > 300)) {
        Document::reload_msg(_('Sorry, this message cannot be deleted.'),
                $_SERVER['PHP_SELF'] . '?t=' . $row['topicID'] . '&page=' . $page);
    }
    $db->query('DELETE FROM `forumPosts` WHERE `id` = \'' . $postID . '\' LIMIT 1');
    Document::reload_msg(_('The message has been deleted.'),
            $_SERVER['PHP_SELF'] . '?t=' . $topicID . '&page=' . $page);
} elseif (!empty($_GET['td']) && Forum::topic_exists($_GET['td'])) {
    $topicID = intval($_GET['td']);
    $forumID = Forum::get_forum_id($topicID);
    if (!Perms::get(Perms::FORUM_MOD) || ($forumID == 6 && !Perms::get(Perms::POST_NEWS))) {
        Document::reload_msg(_('You don\'t have permission to delete this thread.'),
                $_SERVER['PHP_SELF'] . '?t=' . $topicID);
    }
    Forum::topic_remove($topicID);
    Document::reload_msg(_('Thread removed.'), 'viewforum.php?f=' . $forumID);
} elseif (!empty($_GET['open']) && Perms::get(Perms::FORUM_MOD) && Forum::topic_exists($_GET['open'])) {
    Forum::thread_close($_GET['open'], false);
    Document::reload_msg(_('Topic has been opened.'),
            $_SERVER['PHP_SELF'] . '?t=' . $_GET['open']);
} elseif (!empty($_GET['close']) && Perms::get(Perms::FORUM_MOD) && Forum::topic_exists($_GET['close'])) {
    Forum::thread_close($_GET['close']);
    Document::reload_msg(_('Topic has been closed.'),
            $_SERVER['PHP_SELF'] . '?t=' . $_GET['close']);
}

if (!isset($_GET['t']) || !Forum::topic_exists($_GET['t'])) {
    Document::reload_msg(_('Thread not found.'), './');
}
$forum = new Forum;
if (isset($_GET['watch']) && $_SESSION['user_id']) {
    if ($forum->watch($_GET['t'], $_GET['watch'])) {
        Document::reload_msg(_('You will be notified on new replies in this thread.'),
                $_SERVER['PHP_SELF'] . '?t=' . $_GET['t'] . '&page=' . (isset($_GET['page'])
                            ? $_GET['page'] : 1));
    } else {
        Document::reload_msg(_('You will no longer be notified on new replies in this thread.'),
                $_SERVER['PHP_SELF'] . '?t=' . $_GET['t'] . '&page=' . (isset($_GET['page'])
                            ? $_GET['page'] : 1));
    }
}
if (!empty($_POST['message']) && $_SESSION['user_id']) {
    if (Forum::topic_locked($_GET['t']) && !Perms::get(Perms::FORUM_MOD)) {
        Document::reload_msg(_('This thread is closed, you cannot post here.'),
                $_SERVER['PHP_SELF'] . '?t=' . $_GET['t']);
    }
    Forum::ForumPostReply($_GET['t'], $_POST['message']);
}

$forum->fetch_posts($_GET['t']);
$navi = array(
    array(_('Forum'), './'),
    array($forum->topic['forum_title'], './viewforum.php?f=' . $forum->topic['forum_id'])
);
$document = new Document($forum->topic['title'], $navi);
$document->get_page($forum->pages);
$document->assign(array(
    'page' => $document->page,
    'data' => $forum->data,
    'topic' => $forum->topic
));
$document->display('forum_viewtopic');
$document->pages('t', $_GET['t']);
