<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 * @version 2.3.07
 */
class Notifications {

    public $data = array();

    /**
     * Marks notification as read.
     * @param string|array $type either notification type as string, or an array with 2 elements that contain notification types
     * @param type $target_id notification target ID
     */
    public static function view($type, $target_id = null) {
        if (is_array($type)) {
            $GLOBALS['db']->query('UPDATE `notifications`
                SET `viewed` = \'1\'
                WHERE `user_id` = \'' . $_SESSION['user_id'] . '\'
                AND (
                    `type` = \'' . $type[0] . '\'
                    OR `type` = \'' . $type[1] . '\'
                ) AND `target_id` = \'' . $target_id . '\'
                AND `viewed` = \'0\'');
        } else {
            $GLOBALS['db']->query('UPDATE `notifications`
                SET `viewed` = \'1\'
                WHERE `user_id` = \'' . $_SESSION['user_id'] . '\'
                AND `type` = \'' . $type . '\'
                ' . (($type == 'friendAccept') ? '' : 'AND `target_id` = \'' . $target_id . '\'') . '
                AND `viewed` = \'0\'');
        }
    }

    /**
     * Creates/updates notifications.
     * @param string $type notification type
     * @param int|string $target_id notification target ID
     * @param int|string $target_owner_id notification target owner ID
     */
    public static function create($type, $target_id, $target_owner_id) {
        // fetching unviewed notifications,
        // checking if current user has triggered them before
        // ignore own notifications
        // this is only required for forum and comments notifications
        if ($type == 'forumPost' || substr($type, -7) == 'Comment') {
            $sql = $GLOBALS['db']->query('SELECT `notifications`.`id`,
                (
                    SELECT COUNT(*)
                    FROM `notifications_updates`
                    WHERE `notification_id` = `notifications`.`id`
                    AND `user_id` = ' . $_SESSION['user_id'] . '
                ) as `count`
                FROM `notifications`
                WHERE `notifications`.`viewed` = 0
                AND `notifications`.`type` = \'' . $type . '\'
                AND `notifications`.`target_id` = \'' . $target_id . '\'
                AND `notifications`.`user_id` != \'' . $_SESSION['user_id'] . '\'');
            while ($row = $sql->fetch_row()) {
                if ($row[1] == 0) {
                    // updating unviewed notification
                    // if it hasn't been triggered by the current user before
                    $GLOBALS['db']->query('INSERT INTO `notifications_updates`
                    (
                        `notification_id`,
                        `user_id`
                    ) VALUES
                    (
                        ' . $row[0] . ',
                        ' . $_SESSION['user_id'] . '
                    )');
                    $GLOBALS['db']->query('UPDATE `notifications`
                    SET `users_count` = `users_count` + 1,
                    `timestamp` = UNIX_TIMESTAMP(NOW())
                    WHERE `id` = ' . $row[0]);
                }
            }

            if ($type == 'forumPost') {
                // selecting users that are watching for replies in the forum thread
                // except current user
                $sql = $GLOBALS['db']->query('SELECT `userID`
                    FROM `forum_topics_watch`
                    WHERE `topicID` = ' . $target_id . '
                    AND `userID` != ' . $_SESSION['user_id']);
            } else {
                // selecting users that are watching for new comments for current target
                // except current user
                $sql = $GLOBALS['db']->query('SELECT `user_id`
                    FROM `comments_watch`
                    WHERE `target_type` = \'' . substr($type, 0, strlen($type) - 7) . '\'
                    AND `target_id` = \'' . $target_id . '\'
                    AND `user_id` != \'' . $_SESSION['user_id'] . '\'');
            }
            while ($row = $sql->fetch_row()) {
                // do not update existing notifications as they have been updated above
                // checking if unviewed notification exists
                // if it doesn't - create it and add current user
                // if it does exist - it has been updated above and no further action required
                if ($GLOBALS['db']
                                ->query('SELECT COUNT(*)
                                    FROM `notifications`
                                    WHERE `type` = \'' . $type . '\'
                                    AND `target_id` = \'' . $target_id . '\'
                                    AND `user_id` = \'' . $row[0] . '\'
                                    AND `viewed` = 0 LIMIT 1')
                                ->fetch_row() == array(0 => '0')) {
                    $GLOBALS['db']->query('INSERT INTO `notifications` (
                            `user_id`,
                            `type`,
                            `target_id`,
                            `timestamp`,
                            `users_count`
                        ) VALUES(
                            \'' . $row[0] . '\',
                            \'' . $type . '\',
                            \'' . $target_id . '\',
                            UNIX_TIMESTAMP(NOW()),
                            1
                        )');
                    $GLOBALS['db']->query('INSERT INTO `notifications_updates`
                        (
                            `notification_id`,
                            `user_id`
                        ) VALUES (
                            ' . $GLOBALS['db']->insert_id . ',
                            ' . $_SESSION['user_id'] . '
                        )');
                }
                // do not update existing notifications as they have been updated above
            }
        } elseif ($_SESSION['user_id'] != $target_owner_id) { // if current user is target owner - do not trigger his own notifications
            // likes and friend requests go here
            // create notification for $target_owner_id
            $sql = $GLOBALS['db']->query('SELECT `notifications`.`id`,
                    (
                        SELECT COUNT(*)
                        FROM `notifications_updates`
                        WHERE `notification_id` = `notifications`.`id`
                        AND `user_id` = ' . $_SESSION['user_id'] . '
                    ) as `count`
                    FROM `notifications`
                    WHERE `notifications`.`type` = \'' . $type . '\'
                    ' . (($type == 'friendAccept') ? '' : 'AND `notifications`.`target_id` = \'' . $target_id . '\'') . '
                    AND `notifications`.`user_id` = \'' . $target_owner_id . '\'
                    AND `notifications`.`viewed` = 0
                    LIMIT 1')->fetch_row();
            if ($sql === null) {
                $GLOBALS['db']->query('INSERT INTO `notifications` (
                        `user_id`,
                        `type`,
                        `target_id`,
                        `timestamp`
                    ) VALUES (
                        \'' . $target_owner_id . '\',
                        \'' . $type . '\',
                        ' . (($type == 'friendAccept') ? 'NULL' : '\'' . $target_id . '\'') . ',
                        UNIX_TIMESTAMP(NOW())
                    )');
                $GLOBALS['db']->query('INSERT INTO `notifications_updates`
                    (
                        `notification_id`,
                        `user_id`
                    ) VALUES (
                        ' . $GLOBALS['db']->insert_id . ',
                        ' . $_SESSION['user_id'] . '
                    )');
            } elseif ($sql[1] == 0) {
                $GLOBALS['db']->query('UPDATE `notifications`
                    SET `users_count` = `users_count` + 1,
                        `timestamp` = UNIX_TIMESTAMP(NOW())
                        WHERE `id` = \'' . $sql[0] . '\'
                        LIMIT 1');
                $GLOBALS['db']->query('INSERT INTO `notifications_updates`
                    (
                        `notification_id`,
                        `user_id`
                    ) VALUES (
                        ' . $sql[0] . ',
                        ' . $_SESSION['user_id'] . '
                    )');
            }
        }
    }

    public function fetch($limit = 10) {
        $this->pages = $GLOBALS['db']
                ->query('SELECT COUNT(*)
                    FROM `notifications`
                    WHERE `user_id` = \'' . $_SESSION['user_id'] . '\'')
                ->fetch_row();
        if ($this->pages[0] == 0) {
            $this->pages = 0;
            return;
        }
        $this->pages = ceil($this->pages[0] / 10);
        $sql = $GLOBALS['db']->query('SELECT *
                FROM `notifications`
                WHERE `user_id` = \'' . $_SESSION['user_id'] . '\'
                ORDER BY `timestamp` DESC
                LIMIT ' . ((Document::s_get_page($this->pages) - 1) * $limit) . ', ' . $limit);
        while ($row = $sql->fetch_assoc()) {
            $row['timestamp'] = User::date($row['timestamp']);
            $sql2 = $GLOBALS['db']
                    ->query('SELECT `user_id`
                        FROM `notifications_updates`
                        WHERE `notification_id` = ' . $row['id']);
            if ($sql2->num_rows) {
                $row['users_id'] = array();
            }
            while ($row2 = $sql2->fetch_row()) {
                $row['users_id'][] = $row2[0];
            }
            $this->data[] = $row;
        }
    }

    /**
     * Removes current user from specified notifications. Deletes notification if there are no other users left.
     * @param string $notification_type
     * @param int|string $target_id
     */
    public static function user_remove($notification_type, $target_id) {
        $sql = $GLOBALS['db']->query('SELECT `notifications`.`id`,
            (
                SELECT `id`
                FROM `notifications_updates`
                WHERE `notification_id` = `notifications`.`id`
                AND `user_id` = ' . $_SESSION['user_id'] . '
            ) as `updates_id`,
            (
                SELECT COUNT(*)
                FROM `notifications_updates`
                WHERE `notification_id` = `notifications`.`id`
            ) as `updates_count`
            FROM `notifications`
            WHERE `type` = \'' . $notification_type . '\'
            AND `target_id` = \'' . $target_id . '\'
            AND `viewed` = \'0\'');
        while ($row = $sql->fetch_assoc()) {
            if ($row['updates_id'] !== null) {
                $GLOBALS['db']->query('DELETE FROM `notifications_updates`
                    WHERE `id` = ' . $row['updates_id'] . ' LIMIT 1');
                if ($row['updates_count'] == 1) {
                    $GLOBALS['db']->query('DELETE FROM `notifications`
                        WHERE `id` = \'' . $row['id'] . '\' LIMIT 1');
                } else {
                    $GLOBALS['db']->query('UPDATE `notifications`
                        SET `users_count` = `users_count` - 1
                        WHERE `id` = ' . $row['id'] . ' LIMIT 1');
                }
            }
        }
    }

    /**
     * Sends email notification.
     * @param string|int $user_id must be safe to use in mysql query
     * @param string $type notification type (letter)
     * @return false if notifications disabled, otherwise see mail()
     */
    public static function mail($user_id, $type) {
        $sql = 'SELECT `users`.`nickname`,
            `users`.`email`';
        switch ($type) {
            case 'letter':
                $sql .= ', `user_settings`.`letters_notify`';
                break;
        }
        $sql = $GLOBALS['db']->query($sql . ', `user_settings`.`locale`
            FROM `users`,
            `user_settings`
            WHERE `user_settings`.`id` = `users`.`id`
            AND `users`.`id` = ' . $user_id . ' LIMIT 1')->fetch_assoc();
        if ($type == 'letter' && !$sql['letters_notify']) {
            return false;
        }
        $locale = setlocale(LC_MESSAGES, 0);
        User::set_locale($sql['locale']);
        switch ($type) {
            case 'letter':
                $subject = _('New Private Message has arrived');
                $message = sprintf(_("Hello %s,\r\n\r\nYou have received a new private message to your account on Tibia-ME.net and you have requested that you be notified on this event. You can view your new message by clicking on the following link:\r\nhttp://wap.tibia-me.net/user/letters.php\r\n\r\nRemember that you can always choose not to be notified of new messages by changing the appropriate setting in your profile.\r\nhttp://wap.tibia-me.net/user/settings.php\r\n\r\nKind Regards,\r\nTibia-ME.net Support\r\nhttp://wap.tibia-me.net"), $sql['nickname']);
                break;
            case 'lostpassword':
                $subject = _('Password recovery confirmation');
                $message = sprintf(_("Hello %s,\r\n\r\nYou have requested a new password for your account on Tibia-ME.net.\r\n\r\nClick the following link to continue new password activation:\r\nhttp://wap.tibia-me.net/user/lostpassword.php?user=%d&v=%s\r\n\r\nIf you did not request a new password, just ignore this message.\r\n\r\nKind Regards,\r\nTibia-ME.net Support\r\nhttp://wap.tibia-me.net"), $sql['nickname'], $user_id, func_get_arg(2));
                break;
            default:
                log_error('unexpected notification type');
                User::set_locale();
                return false;
        }
        // RFC 2047
        $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        User::set_locale($locale);
        $message = wordwrap($message, 70, "\r\n", true);
        $headers = 'From: Tibia-ME.net Notifier <contact@tibia-me.net>' . "\r\n";
        $headers .= 'Subject: ' . $subject . "\r\n";
        $headers .= 'Reply-To: contact@tibia-me.net' . "\r\n";
        $headers .= 'Date: ' . date('r') . "\r\n";
        $headers .= 'Content-type: text/plain; charset=UTF-8';
        return mail($sql['nickname'] . ' <' . $sql['email'] . '>', $subject, $message, $headers, '-fcontact@tibia-me.net');
    }

    /**
     * Completely removes ALL notifications for specified target type and ID,
     * also removes entry from `comments_watch` if required.
     * @param string $type
     * @param string|int $target_id must be safe to use in mysql query
     */
    public static function remove($type, $target_id) {
        $sql = $GLOBALS['db']->query('SELECT `id`
            FROM `notifications`
            WHERE `type` = \'' . $type . '\'
            AND `target_id` = \'' . $target_id . '\'');
        while ($row = $sql->fetch_row()) {
            $GLOBALS['db']->query('DELETE FROM `notifications_updates`
                WHERE `notification_id` = ' . $row[0] . ' LIMIT 1');
        }
        $GLOBALS['db']->query('DELETE FROM `notifications` WHERE `type` = \'' . $type . '\'
                AND `target_id` = \'' . $target_id . '\'');
        if (substr($type, -7) == 'Comment') {
            $GLOBALS['db']->query('DELETE FROM `comments_watch`
                WHERE `target_type` = \'' . substr($type, 0, strlen($type) - 7) . '\'
                    AND `target_id` = ' . $target_id . ' LIMIT 1');
        }
    }

}