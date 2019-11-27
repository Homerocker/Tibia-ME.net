<?php

/**
 * Forum functions.
 *
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2011 (c) Tibia-ME.net
 */
class Forum
{

    /**
     * @var array $data
     */
    public $data = array();

    /**
     * @var int $count
     */
    public $count = 0;

    /**
     * @var int $pages
     */
    public $pages = 0;

    /**
     * @var array $error
     */
    public $error = array();

    /**
     * @var string $message
     */
    public $message;

    /**
     * @var string $topic_title
     */
    public $topic_title;

    /**
     * @var string $topic_type
     */
    public $topic_type = 'normal';

    public static function forum_exists($forum_id)
    {
        if (!ctype_digit((string)$forum_id)) {
            return false;
        }
        return (bool)$GLOBALS['db']->query('SELECT COUNT(*)
                FROM `forums` WHERE `id` = \'' . $forum_id . '\'')->fetch_row()[0];
    }

    public static function CountStickyTopics($forumID, $topicID = null)
    {
        if (!empty($topicID)) {
            $sql = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `forumTopics`
                WHERE `type` = \'sticky\'
                AND `forumID` = \'' . $forumID . '\'
                AND `id` != \'' . $topicID . '\'')->fetch_row();
        } else {
            $sql = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `forumTopics`
                WHERE `type` = \'sticky\'
                AND `forumID` = \'' . $forumID . '\'')->fetch_row();
        }
        return $sql[0];
    }

    public static function get_forum_id($topic_id)
    {
        $sql = $GLOBALS['db']->query('SELECT `forumID`
            FROM `forumTopics`
            WHERE `id` = \'' . $topic_id . '\'')->fetch_row();
        return $sql[0];
    }

    /**
     * Passes string (message) fetched from database through various filters to make it safe and ready for output.
     * @param string $string
     * @return string input string with parsed bbcodes and smilies, passed through htmlspecialchars()
     */
    public static function MessageHandler($string)
    {
        $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');

        // replacing source image URLs with thumbnail URLs
        $string = preg_replace_callback('/\[img\](.+)\[\/img\]/isU',
            function ($path) {
                $path = $path[1];
                if (parse_url($path, PHP_URL_HOST) === null) {
                    $path = $_SERVER['DOCUMENT_ROOT'] . $path;
                }
                return '[img=' . $path . ']' . Images::thumbnail($path, CACHE_DIR,
                        BBCODE_IMG_MAX_WIDTH, BBCODE_IMG_QUALITY,
                        BBCODE_IMG_MAX_HEIGHT) . '[/img]';
            }, $string);

        // BBCodes
        $array = array(
            '/\[quote=&quot;(.+)&quot;\](.+)\[\/quote\]/isU' => '<span class="small"><b>\1 ' . _('wrote') . ':</b></span><div class="callout secondary">\2</div>',
            '/\[quote=(.+)\](.+)\[\/quote\]/isU' => '<span class="small"><b>\1 ' . _('wrote') . ':</b></span><div class="callout secondary">\2</div>',
            '/\[quote\](.+)\[\/quote\]/isU' => '<div class="callout secondary">\1</div>',
            '/\[url=&quot;(.+)&quot;\](.+)\[\/url\]/isU' => '<a href="\1" target="_blank">\2</a>',
            '/\[url=(.+)\](.+)\[\/url\]/isU' => '<a href="\1" target="_blank">\2</a>',
            '/\[url\](.+)\[\/url\]/isU' => '<a href="\1" target="_blank">\1</a>',
            '/\[b\](.+)\[\/b\]/isU' => '<b>\1</b>',
            '/\[s\](.+)\[\/s\]/isU' => '<s>\1</s>',
            '/\[color=&quot;(.+)&quot;\](.+)\[\/color\]/isU' => '<span style="font-color: \1;">\2</span>',
            '/\[color=(.+)\](.+)\[\/color\]/isU' => '<span style="font-color: \1;">\2</span>',
            '/\[img=(.+)\](.+)\[\/img\]/isU' => '<a href="\1"><img src="\2" style="max-width: 100%;" alt=""/></a>',
            '/\[spoiler=(.+)\](.+)\[\/spoiler\]/isU' => '<div class="callout secondary pointer" onclick="toggle(this)"><span class="label primary">spoiler</span> \1</span></div><div class="callout secondary display-none">\2</div>',
            '/\[spoiler\](.+)\[\/spoiler\]/isU' => '<div class="callout secondary pointer" onclick="toggle(this)"><span class="label primary">spoiler</span> ' . _('Click to show') . '</div><div class="callout secondary display-none">\1</div>'
            //'~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~' => '<a href="\0" target="_blank">\0</a>'
        );
        foreach ($array as $expression => $replacement) {
            while (preg_match($expression, $string)) {
                $string = preg_replace($expression, $replacement, $string);
            }
        }

        // handling line breaks
        $string = nl2br(preg_replace('/(?:(?:\r\n|\r|\n)\s*){3,}/s', "\n\n",
            $string));

        // smilies
        $array = Cache::read('smilies');
        if ($array === false) {
            $sql = $GLOBALS['db']->query('SELECT * FROM `smilies`');
            $array = array();
            while ($row = $sql->fetch_assoc()) {
                $array[$row['code']] = '<img src="' . SMILIES_DIR . '/' . $row['image'] . '" alt="' . $row['code'] . '"/>';
            }
            Cache::write($array, 'smilies');
        }
        return str_ireplace(array_keys($array), array_values($array), $string);
    }

    public static function topic_exists($topic_id)
    {
        if (!ctype_digit((string)$topic_id)) {
            return false;
        }
        return (bool)$GLOBALS['db']->query('SELECT COUNT(*) FROM `forumTopics` WHERE `id` = ' . $topic_id .' AND movedID IS NULL')->fetch_row()[0];
    }

    public static function topic_remove($topic_id)
    {
        $GLOBALS['db']->query('DELETE FROM `forumTopics` WHERE `id` = \'' . $topic_id . '\' OR `movedID` = \'' . $topic_id . '\'');
        $GLOBALS['db']->query('DELETE FROM `forumPosts` WHERE `topicID` = \'' . $topic_id . '\'');
        Notifications::remove('forumPost', $topic_id);
        $GLOBALS['db']->query('DELETE FROM `forum_topics_watch` WHERE `topicID` = \'' . $topic_id . '\'');
        $GLOBALS['db']->query('DELETE FROM `forumTopicsRead` WHERE `topicID` = \'' . $topic_id . '\'');
        return true;
    }

    /**
     * @deprecated
     * Checks forum topic for new messages and returns appropriate icon info.
     * @param array $sql an array returned by mysqli::fetch_assoc() in $this->fetch_topics()
     * @return array|boolean false if user is not authorized, otherwise an array with 2 elements: icon filename and title
     */
    private function topic_icon($sql)
    {
        if (!empty($sql['movedID'])) {
            return array('moved.png', 'moved');
        } elseif ($sql['locked'] == 1) {
            if ($_SESSION['user_id'] && $this->count_unread_thread($sql['id'])) {
                return array('locked_new.png', 'new');
            }
            return array('locked.png', 'no new');
        }
        switch ($sql['type']) {
            case 'announcement':
                if ($_SESSION['user_id'] && $this->count_unread_thread($sql['id'])) {
                    return array('announcement_new.png', 'new');
                }
                return array('announcement.png', 'no new');
            case 'sticky':
                if ($_SESSION['user_id'] && $this->count_unread_thread($sql['id'])) {
                    return array('sticky_new.png', 'new');
                }
                return array('sticky.png', 'no new');
            default:
                if ($_SESSION['user_id'] && $this->count_unread_thread($sql['id'])) {
                    return array('new.png', 'new');
                }
                return array('no_new.png', 'no new');
        }
    }

    /**
     * Returns an array with forum topic flags
     * @param array $sql_assoc array returned by mysqli fetch_assoc()
     * @return array
     */
    private function get_topic_status($sql_assoc)
    {
        return [
            'unread' => $_SESSION['user_id'] && $this->count_unread_thread($sql_assoc['id']),
            'announcement' => $sql_assoc['type'] == 'announcement',
            'sticky' => $sql_assoc['type'] == 'sticky',
            'closed' => $sql_assoc['locked'] == 1
        ];
    }

    /**
     * @deprecated
     * Checks forum board for new messages and returns appropriate icon info.
     * @param int|string $forum_id forumID
     * @return boolean|array false if user is not authorized, otherwise an array with 2 elements: icon filename and icon title
     */
    private function board_icon($forum_id)
    {
        if (!$_SESSION['user_id']) {
            return false;
        }
        return $this->count_unread_board($forum_id) ? array('new.png', 'new') : array(
            'no_new.png', 'no new');
    }

    /**
     * Returns an array with forum board flags
     * @param int $board_id
     * @return array
     */
    private function get_board_status($board_id)
    {
        return [
            'unread' => $_SESSION['user_id'] && $this->count_unread_board($board_id),
            'hidden' => ($GLOBALS['db']->query('SELECT `attribute` FROM forums WHERE id = ' . $board_id)->fetch_row()[0] == 'hidden')
        ];
    }

    public static function topic_locked($topic_id)
    {
        $sql = $GLOBALS['db']->query('SELECT COUNT(*)
            FROM `forumTopics`
            WHERE `id` = \'' . $topic_id . '\'
            AND `locked` = \'1\'')->fetch_row();
        if ($sql[0] == 0) {
            return false;
        }
        return true;
    }

    public static function thread_close($topic_id, $close = true)
    {
        $GLOBALS['db']->query('UPDATE forumTopics SET locked = ' . ($close ? 1 : 0) . ' WHERE id = ' . $topic_id);
    }

    public static function post_exists($post_id)
    {
        if (!ctype_digit((string)$post_id)) {
            return false;
        }
        $sql = $GLOBALS['db']->query('
                       SELECT COUNT(*)
                        FROM `forumPosts`
                        WHERE `id` = \'' . $post_id . '\''
        )->fetch_row();
        if ($sql[0]) {
            return true;
        }
        return false;
    }

    public static function CreateForum($forum_title)
    {
        $GLOBALS['db']->query('INSERT INTO `forums` (`title`) VALUES (\'' . $GLOBALS['db']->real_escape_string($forum_title) . '\')');
        header('Location: ./viewforum.php?f=' . $GLOBALS['db']->insert_id);
        exit();
        return true;
    }

    public static function ForumPostReply($topicID, $message)
    {
        $message = trim($message);
        $message = $GLOBALS['db']->real_escape_string($message);
        if ($GLOBALS['db']->query('SELECT COUNT(*)
            FROM `forumPosts`
            WHERE `topicID` = \'' . $topicID . '\'')->fetch_row()[0]) {
            $sql = $GLOBALS['db']->query('SELECT `message`
                FROM `forumPosts`
                WHERE `topicID` = \'' . $topicID . '\'
                AND posterID = ' . $_SESSION['user_id'] . '
                ORDER BY `time` DESC LIMIT 1')->fetch_row();
            if ($message == $sql[0]) {
                Document::reload_msg(_('This message had been already posted.'),
                    './viewtopic.php?t=' . $topicID . '&page=last');
                return false;
            }
        }
        if ($GLOBALS['db']->query('SELECT COUNT(*)
            FROM `forum_topics_watch`
            WHERE `topicID` = \'' . $topicID . '\'
            AND `userID` = \'' . $_SESSION['user_id'] . '\'')->fetch_row()[0] == 0) {
            $GLOBALS['db']->query('INSERT INTO `forum_topics_watch` (`topicID`, `userID`) VALUES (\'' . $topicID . '\', \'' . $_SESSION['user_id'] . '\')');
        }
        $GLOBALS['db']->query('INSERT INTO `forumPosts` (`topicID`, `posterID`, `message`, `time`) VALUES (\'' . $topicID . '\', \'' . $_SESSION['user_id'] . '\', \'' . $message . '\', \'' . $_SERVER['REQUEST_TIME'] . '\')');
        $GLOBALS['db']->query('UPDATE `forumTopics` SET `last_post_timestamp` = \'' . $_SERVER['REQUEST_TIME'] . '\' WHERE `id` = \'' . $topicID . '\'');
        $sql = $GLOBALS['db']->query('SELECT `authorID`
            FROM `forumTopics`
            WHERE `id` = \'' . $topicID . '\'')->fetch_row()[0];
        Notifications::create('forumPost', $topicID, $sql);
        Document::reload('./viewtopic.php?t=' . $topicID . '&page=last');
    }

    /**
     * Fetches forum boards and puts data into $this->data().
     */
    public function fetch_forums()
    {
        $sql = $GLOBALS['db']->query('SELECT `forums`.*,
            (
                SELECT COUNT(*)
                FROM `forumTopics`
                WHERE `forumID` = `forums`.`id`
            ) as `topics`,
            (
                SELECT COUNT(*)
                FROM `forumPosts`,
                `forumTopics`
                WHERE `forumTopics`.`forumID` = `forums`.`id`
                AND `forumTopics`.`id` = `forumPosts`.`topicID`
            ) as `posts`
            FROM `forums`
            ' . (Perms::get(Perms::FORUM_HIDDEN_ACCESS) ? '' : ' WHERE `forums`.`id` != \'5\''));
        while ($row = $sql->fetch_assoc()) {
            $row['status'] = $this->get_board_status($row['id']);
            $this->data[] = $row;
        }
    }

    public function markallasread($forum_id = null)
    {
        if ($forum_id === null) {
            $sql = $GLOBALS['db']->query('SELECT `id` FROM `forumTopics`');
            while ($row = $sql->fetch_row()) {
                if ($GLOBALS['db']->query('select COUNT(*)
                    from `forumTopicsRead`
                    where `topicID` = \'' . $row[0] . '\'
                    and `userID` = \'' . $_SESSION['user_id'] . '\'')
                        ->fetch_row()[0] == 0) {
                    $GLOBALS['db']->query('INSERT INTO `forumTopicsRead` (`topicID`, `userID`) VALUES (\'' . $row[0] . '\', \'' . $_SESSION['user_id'] . '\')');
                }
            }
            header('Location: ' . $_SERVER['SCRIPT_NAME']);
            exit;
        } else {
            $sql = $GLOBALS['db']->query('SELECT `id` FROM `forumTopics` WHERE `forumID` = \'' . $forum_id . '\'');
            while ($row = $sql->fetch_row()) {
                $value = $GLOBALS['db']->query('select COUNT(*)
                    from `forumTopicsRead`
                    where `topicID` = \'' . $row[0] . '\'
                    and `userID` = \'' . $_SESSION['user_id'] . '\'')
                    ->fetch_row();
                if ($value[0] == 0) {
                    $GLOBALS['db']->query('INSERT INTO `forumTopicsRead` (`topicID`, `userID`) VALUES (\'' . $row[0] . '\', \'' . $_SESSION['user_id'] . '\')');
                }
            }
            header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?f=' . $forum_id);
            exit;
        }
    }

    public function fetch_topics($forum_id)
    {
        $this->title = $GLOBALS['db']->query('
            SELECT `title`
            FROM `forums`
            WHERE `id` = \'' . $forum_id . '\'')->fetch_row()[0];
        $sticky = $GLOBALS['db']->query('
            SELECT COUNT(*)
            FROM `forumTopics`
            WHERE `forumID` = \'' . $forum_id . '\'
            AND `type` = \'sticky\'')->fetch_row()[0];
        $this->pages = $GLOBALS['db']->query('
            SELECT COUNT(*)
            FROM `forumTopics`
            WHERE `forumID` = \'' . $forum_id . '\'
            AND `type` != \'sticky\'')->fetch_row()[0];
        $topics_per_page = $_SESSION['user_forum_topics_per_page'] - $sticky;
        $this->pages = ceil($this->pages / $topics_per_page);
        $page = Document::s_get_page($this->pages);

        $order_by = ($forum_id == 6) ? 'time' : 'last_post_timestamp';

        // fetching announcements
        $sql = $GLOBALS['db']->query('SELECT *
            FROM `forumTopics`
            WHERE `forumID` = \'' . $forum_id . '\'
            AND `type` = \'announcement\'
            ORDER BY `' . $order_by . '` DESC
            LIMIT ' . (($page - 1) * $topics_per_page) . ', ' . $topics_per_page);
        while ($row = $sql->fetch_assoc()) {
            $this->fetch_topics_parse_data($row);
        }

        $topics_per_page -= $this->count;

        // fetching sticky topics
        $sql = $GLOBALS['db']->query('SELECT `forumTopics`.*
            FROM `forumTopics`
            WHERE `forumID` = \'' . $forum_id . '\'
            AND `type` = \'sticky\'
            ORDER BY `' . $order_by . '` DESC');
        while ($row = $sql->fetch_assoc()) {
            $this->fetch_topics_parse_data($row);
        }

        // fetching normal topics
        if ($topics_per_page > 0) {
            $sql = $GLOBALS['db']->query('SELECT *
                FROM `forumTopics`
                WHERE `forumID` = \'' . $forum_id . '\'
                AND `type` = \'normal\'
                ORDER BY `' . $order_by . '`
                DESC LIMIT ' . (($page - 1) * $topics_per_page) . ', ' . $topics_per_page);
            while ($row = $sql->fetch_assoc()) {
                $this->fetch_topics_parse_data($row);
            }
        }
    }

    /**
     * Puts data fetched with mysqli::fetch_assoc() into $this->data(). Used inside of $this->fetch_topics().
     * @param array $row an associative array returned by mysqli::fetch_assoc()
     */
    private function fetch_topics_parse_data($row)
    {
        $moved = (bool)$row['movedID'];
        while (!empty($row['movedID'])) {
            $row = $GLOBALS['db']->query('
                    SELECT *
                    FROM `forumTopics`
                    WHERE `id` = \'' . $row['movedID'] . '\'')
                ->fetch_assoc();
        }
        $row['status'] = $this->get_topic_status($row);
        $row['status']['moved'] = $moved;
        list($row['posterID'], $row['posts']) = $GLOBALS['db']->query('
            SELECT
            (
                SELECT `posterID`
                FROM `forumPosts`
                WHERE `topicID` = \'' . $row['id'] . '\'
                ORDER BY `time` DESC LIMIT 1
            ),
            (
                SELECT COUNT(*)
                FROM `forumPosts`
                WHERE `topicID` = \'' . $row['id'] . '\'
            )')->fetch_row();
        $this->data[] = $row;
    }

    /**
     * Fetches posts and topic data, marks topic as read, updates topic views counter,
     * marks topic reply notification as read if current page is last.
     * @param int|string $topic_id topic ID
     * @return boolean true
     */
    public function fetch_posts($topic_id)
    {
        $sql = $GLOBALS['db']->query('
            SELECT `forumTopics`.*,
            `forumTopics`.`authorID` as `author_id`,
            (
                SELECT (
                    SELECT COUNT(*)
                    FROM `forumPosts`
                    WHERE `posterID` = `author_id`
                ) + (
                    SELECT COUNT(*)
                    FROM `forumTopics`
                    WHERE `authorID` = `author_id`
                )
            ) as `posts`,
            `forums`.`title` as `forum_title`,
            `forums`.`id` as `forum_id`,
            `user_profile`.`signature`
            FROM `forumTopics`,
            `forums`,
            `user_profile`
            WHERE `forumTopics`.`id` = \'' . $topic_id . '\'
            AND `forums`.`id` = `forumTopics`.`forumID`
            AND `user_profile`.`id` = `forumTopics`.`authorID`')
            ->fetch_assoc();
        $this->topic = array(
            'id' => $sql['id'],
            'message' => $this->MessageHandler($sql['message']),
            'time' => User::date($sql['time']),
            'posts' => $sql['posts'],
            'authorID' => $sql['authorID'],
            'edit_count' => $sql['edit_count'],
            'edit_user' => User::get_link($sql['edit_userID']),
            'edit_datetime' => User::date($sql['edit_timestamp']),
            'title' => htmlspecialchars($sql['title'], ENT_COMPAT, 'UTF-8'),
            'forum_title' => htmlspecialchars($sql['forum_title'], ENT_COMPAT,
                'UTF-8'),
            'forum_id' => $sql['forum_id'],
            'signature' => self::MessageHandler($sql['signature']),
            'locked' => $sql['locked']
        );
        if ($this->topic['forum_id'] == 5 && !Perms::get(Perms::FORUM_HIDDEN_ACCESS)) {
            Document::reload_msg(_('You don\'t have permission to access this forum.'),
                './');
        }
        $this->pages = $GLOBALS['db']->query('
                    SELECT COUNT(*)
                    FROM `forumPosts`
                    WHERE `topicID` = \'' . $topic_id . '\''
        )->fetch_row();
        $this->pages = ceil(($this->pages[0] + 1) / $_SESSION['user_forum_posts_per_page']);
        $page = Document::s_get_page($this->pages);
        $sql = $GLOBALS['db']->query('SELECT `forumPosts`.*,
            `forumPosts`.`posterID` as `poster_id`,
            (
                SELECT (
                    SELECT COUNT(*)
                    FROM `forumPosts`
                    WHERE `posterID` = `poster_id`
                ) + (
                    SELECT COUNT(*)
                    FROM `forumTopics`
                    WHERE `authorID` = `poster_id`
                )
            ) as `posts`,
            `user_profile`.`signature`
            FROM `forumPosts`,
            `user_profile`
            WHERE `forumPosts`.`topicID` = \'' . $topic_id . '\'
            AND `user_profile`.`id` = `forumPosts`.`posterID`
            ORDER BY `forumPosts`.`time`
            LIMIT ' . ((($page - 1) * $_SESSION['user_forum_posts_per_page']) - (($page
                    == 1) ? 0 : 1)) . ', ' . (($page == 1) ? ($_SESSION['user_forum_posts_per_page']
                - 1) : $_SESSION['user_forum_posts_per_page']));
        while ($row = $sql->fetch_assoc()) {
            $this->data[] = array(
                'id' => $row['id'],
                'posts' => $row['posts'],
                'time' => $row['time'],
                'posterID' => $row['posterID'],
                'message' => self::MessageHandler($row['message']),
                'edit_count' => $row['edit_count'],
                'edit_userID' => $row['edit_userID'],
                'edit_timestamp' => $row['edit_timestamp'],
                'signature' => self::MessageHandler($row['signature'])
            );
        }
        // marking topic as read
        if ($_SESSION['user_id']) {
            if ($GLOBALS['db']->query('SELECT COUNT(*)
                FROM `forumTopicsRead`
                WHERE `topicID` = \'' . $topic_id . '\'
                AND `userID` = \'' . $_SESSION['user_id'] . '\'')->fetch_row()[0]
                == 0) {
                $GLOBALS['db']->query('INSERT INTO `forumTopicsRead` (`userID`, `topicID`) VALUES (\'' . $_SESSION['user_id'] . '\', \'' . $topic_id . '\')');
            } else {
                $GLOBALS['db']->query('UPDATE forumTopicsRead SET timestamp = ' . $_SERVER['REQUEST_TIME'] . ' WHERE userID = ' . $_SESSION['user_id'] . ' AND topicID = ' . $topic_id);
            }
        }
        // updating topic views counter
        $GLOBALS['db']->query('UPDATE `forumTopics`
            SET `views` = `views`+1
            WHERE `id` = \'' . $topic_id . '\'');
        // marking topic reply notification as read
        if ($page == $this->pages) {
            Notifications::view('forumPost', $topic_id);
        }
        return true;
    }

    /**
     * Changes topic watch status.
     * @param int|string $topic_id
     * @param int|string $watch 1 to watch topic, 0 to stop watching
     * @return boolean new watch state
     */
    public function watch($topic_id, $watch = 1)
    {
        if ($watch == 1) {
            $sql = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `forum_topics_watch`
                WHERE `userID` = \'' . $_SESSION['user_id'] . '\'
                AND `topicID` = \'' . $topic_id . '\'')->fetch_row();
            if ($sql[0] == 0) {
                $GLOBALS['db']->query('INSERT INTO `forum_topics_watch` (`userID`, `topicID`) VALUES (\''
                    . $_SESSION['user_id'] . '\', \'' . $topic_id . '\')');
            }
            return true;
        } else {
            $sql = $GLOBALS['db']
                ->query('SELECT `id`
                        FROM `notifications`
                        WHERE `user_id` = \'' . $_SESSION['user_id'] . '\'
                        AND `type` = \'forumPost\'
                        AND `target_id` = \'' . $topic_id . '\'
                        AND viewed = \'0\'')
                ->fetch_row();
            if ($sql !== null) {
                $GLOBALS['db']
                    ->query('DELETE FROM `notifications_updates`
                            WHERE `notification_id` = ' . $sql[0] . ' LIMIT 1');
                $GLOBALS['db']
                    ->query('DELETE FROM `notifications`
                            WHERE `id` = ' . $sql[0] . ' LIMIT 1');
            }
            $GLOBALS['db']->query('DELETE FROM `forum_topics_watch` WHERE `userID` = \''
                . $_SESSION['user_id'] . '\' AND `topicID` = \'' . $topic_id . '\'');
            return false;
        }
    }

    /**
     * @param string $query search query
     */
    public function search($query, $limit = 20)
    {
        $query = explode(' ', $query);
        $sql = '';
        foreach ($query as $i => $word) {
            if ($i == 0) {
                $sql .= '`forumPosts`.`message` LIKE \'%' . $GLOBALS['db']->real_escape_string($word) . '%\'';
            } else {
                $sql .= ' AND `forumPosts`.`message` LIKE \'%' . $GLOBALS['db']->real_escape_string($word) . '%\'';
            }
        }
        if (Perms::get(Perms::FORUM_HIDDEN_ACCESS)) {
            $this->pages = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `forumPosts` WHERE ' . $sql)->fetch_row();
        } else {
            $this->pages = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `forumPosts`, `forumTopics`, `forums`
                WHERE `forumPosts`.`topicID` = `forumTopics`.`id`
                AND `forumTopics`.`forumID` = `forums`.`id`
                AND `forums`.`id` != \'5\' AND ' . $sql)->fetch_row();
        }
        $this->results = $this->pages[0];
        $this->pages = ceil($this->results / $limit);
        $page = Document::s_get_page($this->pages);
        if (Perms::get(Perms::FORUM_HIDDEN_ACCESS)) {
            $sql = $GLOBALS['db']->query('SELECT `forumPosts`.*,
                `forumTopics`.`forumID`,
                `forums`.`title` as `forum_title`,
                `forumTopics`.`title` as `topic_title`
                FROM `forumPosts`, `forumTopics`, `forums`
                WHERE ' . $sql . '
                AND `forumTopics`.`id` = `forumPosts`.`topicID`
                AND `forums`.`id` = `forumTopics`.`forumID`
                LIMIT ' . (($page - 1) * $limit) . ', ' . $limit);
        } else {
            $sql = $GLOBALS['db']->query('SELECT `forumPosts`.*,
                `forumTopics`.`title` as `topic_title`,
                `forums`.`title` as `forum_title`,
                `forumTopics`.`forumID`
                FROM `forumPosts`, `forumTopics`, `forums`
                WHERE `forumPosts`.`topicID` = `forumTopics`.`id`
                AND `forumTopics`.`forumID` = `forums`.`id`
                AND `forums`.`id` != \'5\'
                AND ' . $sql . '
                LIMIT ' . (($page - 1) * $limit) . ', ' . $limit);
        }
        while ($row = $sql->fetch_assoc()) {
            $this->data[] = array(
                'poster' => User::get_link($row['posterID']),
                'time' => User::date($row['time']),
                'message' => $this->search_result_highlight($row['message'],
                    $query),
                'topicID' => $row['topicID'],
                'forumID' => $row['forumID'],
                'forum_title' => htmlspecialchars($row['forum_title'],
                    ENT_COMPAT, 'UTF-8'),
                'topic_title' => htmlspecialchars($row['topic_title'],
                    ENT_COMPAT, 'UTF-8')
            );
            ++$this->count;
        }
    }

    private function search_result_highlight($message, $search)
    {
        $message = strip_tags(self::MessageHandler($message));
        if (strlen($message) > 256) {
            $message = substr($message, 0, 256);
        }
        foreach ($search as $word) {
            $message = str_ireplace($word,
                '<span class="search_result_highlight">' . $word . '</span>',
                $message);
        }
        return $message;
    }

    public static function post_edit()
    {
        $sql = $GLOBALS['db']->query('SELECT `topicID` as `topic_id`,
            `posterID` as `poster_id`,
            `edit_count`,
            `message`
            FROM `forumPosts`
            WHERE `id` = \'' . $_POST['p'] . '\'')->fetch_assoc();
        foreach ($sql as $var => $value) {
            ${$var} = $value;
        }
        if (!Perms::get(Perms::FORUM_MOD) && self::topic_locked($topic_id)) {
            Document::reload_msg(_('This thread is closed, you cannot edit posts.'),
                './viewtopic.php?t=' . $topic_id);
        }
        $sql = $GLOBALS['db']->query('SELECT *
            FROM `forumPosts`
            WHERE `topicID` = \'' . $topic_id . '\'
            ORDER BY `time` DESC
            LIMIT 1')->fetch_assoc();
        if ($_POST['p'] != $sql['id'] || $poster_id != $_SESSION['user_id']) {
            ++$edit_count;
        }
        if (($poster_id == $_SESSION['user_id'] || Perms::get(Perms::FORUM_MOD))
            && $_POST['message'] != $message) {
            $GLOBALS['db']->query('UPDATE `forumPosts`
                SET `message` = \'' . $GLOBALS['db']->real_escape_string($_POST['message']) . '\',
                `edit_count` = \'' . $edit_count . '\',
                `edit_timestamp` = \'' . $_SERVER['REQUEST_TIME'] . '\',
                `edit_userID` = \'' . $_SESSION['user_id'] . '\'
                WHERE `id` = \'' . $_POST['p'] . '\'');
        }
        Document::reload_msg(_('Changes saved.'),
            './viewtopic.php?t=' . $topic_id . '&page=last');
    }

    public function topic_create()
    {
        if ($_REQUEST['f'] == 6 && !Perms::get(Perms::POST_NEWS)) {
            Document::reload_msg(_('You don\'t have permission to create new threads here.'),
                './viewforum.php?f=' . $_REQUEST['f']);
        }
        if (isset($_POST['topicTitle'])) {
            $this->topic_title = trim($_POST['topicTitle']);
        } else {
            return;
        }
        if (empty($this->topic_title)) {
            $this->error[] = _('Please specify topic title.');
        }
        if (strlen($this->topic_title) > 64) {
            $this->error[] = _('Topic title is too long.');
        }
        if (isset($_POST['topicMessage'])) {
            $this->message = trim($_POST['topicMessage']);
        }
        if (empty($this->message)) {
            $this->error[] = _('Please specify topic message.');
        }

        if (isset($_POST['topicType']) && Perms::get(Perms::FORUM_MOD)) {
            if ($_POST['topicType'] == 'sticky') {
                if (self::CountStickyTopics($_REQUEST['f']) >= 5) {
                    $this->error[] = _('You cannot create more than 5 sticky topics in this forum.');
                } else {
                    $this->topic_type = 'sticky';
                }
            } elseif ($_POST['topicType'] == 'announcement') {
                $this->topic_type = 'announcement';
            }
        }

        if (!empty($this->error)) {
            foreach ($this->error as $error) {
                Document::msg($error);
            }
            return false;
        }
        // exception for news section
        if ($_REQUEST['f'] != 6) {
            $sql = $GLOBALS['db']->query('SELECT `id`
                FROM `forumTopics`
                WHERE `title` = \'' . $GLOBALS['db']->real_escape_string($_POST['topicTitle']) . '\'
                AND `forumID` = \'' . $_REQUEST['f'] . '\'')->fetch_row();
            if ($sql !== null) {
                Document::reload_msg(_('Topic with this name already exists.'),
                    './viewtopic.php?t=' . $sql[0]);
            }
        }

        $GLOBALS['db']
            ->query('INSERT INTO `forumTopics`
            (
                `forumID`,
                `type`,
                `title`,
                `authorID`,
                `time`,
                `message`,
                `last_post_timestamp`
            ) VALUES (
                ' . $_REQUEST['f'] . ',
                \'' . $this->topic_type . '\',
                \'' . $GLOBALS['db']->real_escape_string($this->topic_title) . '\',
                ' . $_SESSION['user_id'] . ',
                UNIX_TIMESTAMP(NOW()),
                \'' . $GLOBALS['db']->real_escape_string($this->message) . '\',
                UNIX_TIMESTAMP(NOW())
            )');
        $thread_id = $GLOBALS['db']->insert_id;
        $this->watch($thread_id, 1);
        Document::reload_msg(_('Thread created.'),
            './viewtopic.php?t=' . $thread_id);
    }

    public static function count_unread()
    {
        if (!$_SESSION['user_id']) {
            // unauthorized user, counting last 24h messages
            return $GLOBALS['db']->query('SELECT (SELECT COUNT(*)'
                . ' FROM forumTopics WHERE movedID IS NULL AND time > '
                . ($_SERVER['REQUEST_TIME'] - 86400) . ') + (SELECT COUNT(*)'
                . ' FROM forumPosts WHERE time > '
                . ($_SERVER['REQUEST_TIME'] - 86400) . ')')->fetch_row()[0];
        } else {
            return $GLOBALS['db']->query('SELECT (SELECT COUNT(*) FROM forumPosts, forumTopics t' . (Perms::get(Perms::FORUM_HIDDEN_ACCESS) ? '' : ', forums') . ' WHERE forumPosts.time > COALESCE((SELECT timestamp FROM forumTopicsRead WHERE topicID = t.id AND userID = ' . $_SESSION['user_id'] . '), 0) AND forumPosts.topicID = t.id' . (Perms::get(Perms::FORUM_HIDDEN_ACCESS) ? '' : ' AND t.forumID = forums.id AND (forums.attribute IS NULL OR forums.attribute != \'hidden\')') . ') + (SELECT COUNT(*) FROM forumTopics t' . (Perms::get(Perms::FORUM_HIDDEN_ACCESS) ? '' : ', forums') . ' WHERE' . (Perms::get(Perms::FORUM_HIDDEN_ACCESS) ? '' : ' t.forumID = forums.id AND') . ' t.time > COALESCE((SELECT timestamp FROM forumTopicsRead WHERE topicID = t.id AND userID = ' . $_SESSION['user_id'] . '), 0)' . (Perms::get(Perms::FORUM_HIDDEN_ACCESS) ? '' : ' AND (forums.attribute IS NULL OR forums.attribute != \'hidden\')') . ')')->fetch_row()[0];
        }
    }

    private function count_unread_thread($topic_id)
    {
        return $GLOBALS['db']->query('SELECT (SELECT COUNT(*) FROM forumPosts WHERE'
            . ' topicID = ' . $topic_id . ' AND time > COALESCE((SELECT'
            . ' timestamp FROM forumTopicsRead WHERE topicID = ' . $topic_id
            . ' AND userID = ' . $_SESSION['user_id'] . '), 0))'
            . ' + IF(((SELECT time FROM forumTopics WHERE id = ' . $topic_id
            . ') > COALESCE((SELECT timestamp FROM forumTopicsRead'
            . ' WHERE topicID = ' . $topic_id . ' AND userID = '
            . $_SESSION['user_id'] . '), 0)), 1, 0)')->fetch_row()[0];
    }

    private function count_unread_board($board_id)
    {
        return $GLOBALS['db']->query('SELECT (SELECT COUNT(*)'
            . ' FROM forumTopics t WHERE forumID = '
            . $board_id . ' AND NOT EXISTS (SELECT *'
            . ' FROM forumTopics WHERE movedID = t.id)'
            . ' AND NOT EXISTS (SELECT *'
            . ' FROM forumTopicsRead WHERE userID = '
            . $_SESSION['user_id'] . ' AND topicID = t.id))'
            . ' + (SELECT COUNT(*) FROM forumPosts'
            . ' JOIN forumTopics'
            . ' ON forumPosts.topicID = forumTopics.id'
            . ' WHERE forumTopics.forumID = ' . $board_id
            . ' AND forumPosts.time > COALESCE((SELECT timestamp'
            . ' FROM forumTopicsRead WHERE topicID = forumTopics.id'
            . ' AND userID = ' . $_SESSION['user_id'] . '), 0))')
            ->fetch_row()[0];
    }

    public static function update_lastview($topicID)
    {
        $GLOBALS['db']->query('INSERT INTO forumTopicsRead'
            . ' (userID, topicID, timestamp)'
            . ' VALUES (' . $_SESSION['user_id'] . ', ' . $topicID . ', '
            . $_SERVER['REQUEST_TIME'] . ')'
            . ' ON DUPLICATE KEY UPDATE timestamp = VALUES(timestamp)');
    }

    public static function count_total()
    {
        return $GLOBALS['db']->query('SELECT (SELECT COUNT(*) FROM forumPosts' . (Perms::get(Perms::FORUM_HIDDEN_ACCESS) ? '' : ', forumTopics, forums WHERE forumPosts.topicID = forumTopics.id AND forumTopics.forumID = forums.id AND (forums.attribute IS NULL OR forums.attribute != \'hidden\')') . ') + (SELECT COUNT(*) FROM forumTopics' . (Perms::get(Perms::FORUM_HIDDEN_ACCESS) ? '' : ', forums') . ' WHERE forumTopics.movedID IS NULL' . (Perms::get(Perms::FORUM_HIDDEN_ACCESS) ? '' : ' AND forumTopics.forumID = forums.id AND (forums.attribute IS NULL OR forums.attribute != \'hidden\')') . ')')->fetch_row()[0];
    }

}
