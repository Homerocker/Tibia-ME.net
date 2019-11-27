<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2012, Tibia-ME.net
 * @todo add search filters for fetch()
 */
class Friendlist {

    public $data = array();

    public $total_counter;

    public $online_counter;

    public $mutual_counter;

    public $requests_in_counter;

    public $requests_out_counter;

    public $pages = 1;

    private $user_id;

    /**
     * Checks if users are friends.
     * @param int|string $friend_id
     * @return boolean|int true if friend request is accepted,
     * 0 if outcoming friend request is pending,
     * 1 if incoming friend request is pending,
     * false if friend request is not found.
     * !!!This function may return Boolean TRUE or FALSE,
     * but may also return a non-Boolean values which evaluate to TRUE or FALSE.
     * Use the === operator for testing the return value of this function.
     */
    public static function get_status ($friend_id) {
        if (!ctype_digit((string) $friend_id)) {
            return false;
        }
        $return = $GLOBALS['db']->query('SELECT `accepted`,
            `userID`
            FROM `friendlist` WHERE (
                `userID` = \'' . $_SESSION['user_id'] . '\'
                AND `friendID` = \'' . $friend_id . '\'
            ) OR (
                `userID` = \'' . $friend_id . '\'
                AND `friendID` = \'' . $_SESSION['user_id'] . '\'
            ) LIMIT 1')->fetch_row();
        return ($return === null) ? false : (($return[0] == 1) ? true : (($return[1] == $_SESSION['user_id']) ? 0 : 1));
    }

    public static function request_accept ($request_id) {
        $sql = $GLOBALS['db']->query('SELECT `userID`
            FROM `friendlist`
            WHERE `id` = \'' . $request_id . '\'')->fetch_row();
        $GLOBALS['db']->query('UPDATE `friendlist` SET `accepted` = \'1\' WHERE `id` = \'' . $request_id . '\'');
        Notifications::create('friendAccept', null, $sql[0]);
        return true;
    }

    /**
     * Fetches friends.
     * @param string|null $type requests_in, requests_out, online, mutual
     */
    public function fetch ($type = null) {
        switch ($type) {
            case 'requests_in':
                $sql = 'FROM `friendlist`
                    WHERE `friendID` = \'' . $_SESSION['user_id'] . '\'
                    AND `accepted` = 0';
                $this->pages = ceil($this->requests_in_counter / 15);
                break;
            case 'requests_out':
                $sql = 'FROM `friendlist`
                    WHERE `userID` = \'' . $_SESSION['user_id'] . '\'
                    AND `accepted` = 0';
                $this->pages = ceil($this->requests_out_counter / 15);
                break;
            case 'online':
                $sql = 'FROM `friendlist`,
                    `user_profile`
                    WHERE (
                        (
                            `friendlist`.`userID` = \'' . $this->user_id . '\'
                            AND `user_profile`.`id` = `friendlist`.`friendID`
                        ) OR (
                            `friendlist`.`friendID` = \'' . $this->user_id . '\'
                            AND `user_profile`.`id` = `friendlist`.`userID`
                        )
                    ) AND `friendlist`.`accepted` = 1
                    AND `user_profile`.`lastvisit` >= \''
                        . ($_SERVER['REQUEST_TIME'] - 300) . '\'';
                $this->pages = ceil($this->online_counter / 15);
                break;
            case 'mutual':
                $sql = 'FROM `friendlist`
                    WHERE (
                        `userID` = \'' . $_SESSION['user_id'] . '\'
                        AND `friendID` IN (
                            SELECT IF(
                                `friendID` = \'' . $this->user_id . '\',
                                `userID`,
                                `friendID`
                            ) FROM `friendlist`
                            WHERE `userID` = \'' . $this->user_id . '\'
                            OR `friendID` = \'' . $this->user_id . '\'
                        )
                    ) OR (
                        `friendID` = \'' . $_SESSION['user_id'] . '\'
                        AND `userID` IN (
                            SELECT IF(
                                `friendID` = \'' . $this->user_id . '\',
                                `userID`,
                                `friendID`
                            ) FROM `friendlist`
                            WHERE `userID` = \'' . $this->user_id . '\'
                            OR `friendID` = \'' . $this->user_id . '\'
                        )
                    )';
                $this->pages = ceil($this->mutual_counter / 15);
                break;
            default:
                $sql = 'FROM `friendlist`
                    WHERE (
                        `userID` = ' . $this->user_id . '
                        OR `friendID` = ' . $this->user_id . '
                    ) AND `accepted` = 1';
                $this->pages = ceil($this->total_counter / 15);
        }
        $sql = $GLOBALS['db']->query('SELECT `friendlist`.`friendID`,
                `friendlist`.`userID`'
                . $sql . ' LIMIT '
                . ((Document::s_get_page($this->pages) - 1) * 15) . ', 15');
        while ($row = $sql->fetch_assoc()) {
            $row['id']
                    = ($row['friendID']
                    == $this->user_id
                    || $type
                    == 'mutual') ? $row['userID'] : $row['friendID'];
            unset($row['userID'], $row['friendID']);
            if ($type == 'requests_in'
                    || $type == 'requests_out') {
            }
            $row['fgs'] = ($this->user_id
                    == $_SESSION['user_id']) ? true : self::get_status($row['id']);
            $this->data[] = $row;
        }
    }

    public function add () {
        if ($_GET['add'] == $_SESSION['user_id']) {
            Document::reload_msg(_('You cannot add yourself to your friendlist.'));
        }
        $status = self::get_status($_GET['add']);
        if ($status === true) {
            Document::reload_msg(
                    _('You are already friends with this user.'), get_redirect(
                            false, $_SERVER['SCRIPT_NAME']));
        } elseif ($status === 0) {
            Document::reload_msg(
                    _('Friend request had been already sent to this user.'), get_redirect(
                            false, $_SERVER['SCRIPT_NAME']));
        } elseif ($status === 1) {
            Document::reload($_SERVER['PHP_SELF'] . '?accept=' . $_GET['add']);
        }
        $GLOBALS['db']
                ->query('INSERT INTO `friendlist` (
                        `userID`,
                        `friendID`
                    ) VALUES (
                        \'' . $_SESSION['user_id'] . '\',
                        \'' . $_GET['add'] . '\'
                    )');
        Document::reload_msg(
                _('Friend request has been sent.'), get_redirect(
                        false, $_SERVER['SCRIPT_NAME']));
    }

    public function decline () {
        if (self::get_status($_GET['decline']) !== 1) {
            Document::reload_msg(_('Request not found.'), $_SERVER['SCRIPT_NAME']
                    . '?act=requests_in');
        }
            $GLOBALS['db']
                    ->query('DELETE FROM `friendlist`
                        WHERE `userID` = \'' . $_GET['decline'] . '\'
                        AND `friendID` = \'' . $_SESSION['user_id'] . '\'
                        LIMIT 1');
            Document::reload_msg(_('Request declined.'), $_SERVER['SCRIPT_NAME']
                    . '?act=requests_in');
    }

    public function accept () {
        if (self::get_status($_GET['accept']) !== 1) {
            Document::reload_msg(_('Request not found.'), $_SERVER['SCRIPT_NAME']
                    . '?act=requests_in');
        }
        $GLOBALS['db']
                ->query('UPDATE `friendlist`
                    SET `accepted` = 1
                    WHERE `userID` = \'' . $_GET['accept'] . '\'
                    AND `friendID` = \'' . $_SESSION['user_id'] . '\'
                    LIMIT 1');
        Notifications::create('friendAccept', null, $_GET['accept']);
        Document::reload_msg(_('Request accepted.'), $_SERVER['SCRIPT_NAME']
                . '?act=requests_in');
    }

    public function cancel () {
        if (self::get_status($_GET['cancel']) !== 0) {
            Document::reload_msg(_('Request not found.'), $_SERVER['SCRIPT_NAME']
                    . '?act=requests_out');
        }
            $GLOBALS['db']
                    ->query('DELETE FROM `friendlist`
                        WHERE `userID` = \'' . $_SESSION['user_id'] . '\'
                        AND `friendID` = \'' . $_GET['cancel'] . '\'
                        AND `accepted` = 0
                        LIMIT 1');
            Document::reload_msg(_('Request cancelled.'), $_SERVER['SCRIPT_NAME']
                    . '?act=requests_out');
    }

    public function __construct ($user_id = null) {
        if ($user_id === null) {
            $user_id = $_SESSION['user_id'];
        }
        $this->user_id = $user_id;
        $this->total_counter = $GLOBALS['db']->query('SELECT COUNT(*)
            FROM `friendlist`
            WHERE (
                `userID` = \'' . $user_id . '\'
                OR `friendID` = \'' . $user_id . '\'
            ) AND `accepted` = \'1\'')->fetch_row();
        $this->total_counter = $this->total_counter[0];
        if ($this->total_counter) {
            $this->online_counter = $GLOBALS['db']->query('SELECT COUNT(*)
            FROM `friendlist`, `user_profile`
            WHERE (
                `friendlist`.`userID` = \'' . $user_id . '\'
                AND `user_profile`.`id` = `friendlist`.`friendID`
                AND `user_profile`.`lastvisit` >= ' . ($_SERVER['REQUEST_TIME'] - 300) . '
            ) OR (
                `friendlist`.`friendID` = \'' . $user_id . '\'
                AND `user_profile`.`id` = `friendlist`.`userID`
                AND `user_profile`.`lastvisit` >= ' . ($_SERVER['REQUEST_TIME'] - 300) . '
            ) AND `accepted` = \'1\'')->fetch_row();
            $this->online_counter = $this->online_counter[0];
        }
        if ($user_id == $_SESSION['user_id']) {
            $this->requests_in_counter = $GLOBALS['db']
                            ->query('SELECT COUNT(*)
                                FROM `friendlist`
                                WHERE `friendID` = \'' . $_SESSION['user_id'] . '\'
                                AND `accepted` = \'0\'')->fetch_row();
            $this->requests_in_counter = $this->requests_in_counter[0];
            $this->requests_out_counter = $GLOBALS['db']
                            ->query('SELECT COUNT(*)
                                FROM `friendlist`
                                WHERE `userID` = \'' . $_SESSION['user_id'] . '\'
                                AND `accepted` = \'0\'')->fetch_row();
            $this->requests_out_counter = $this->requests_out_counter[0];
        } else {
            $this->mutual_counter = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `friendlist`
                WHERE (
                    `userID` = \'' . $_SESSION['user_id'] . '\'
                    AND `friendID` IN (
                        SELECT IF(
                            `friendID` = \'' . $user_id . '\',
                            `userID`,
                            `friendID`
                        ) FROM `friendlist`
                        WHERE `userID` = \'' . $user_id . '\'
                        OR `friendID` = \'' . $user_id . '\'
                    )
                ) OR (
                    `friendID` = \'' . $_SESSION['user_id'] . '\'
                    AND `userID` IN (
                        SELECT IF(
                            `friendID` = \'' . $user_id . '\',
                            `userID`,
                            `friendID`
                        ) FROM `friendlist`
                        WHERE `userID` = \'' . $user_id . '\'
                        OR `friendID` = \'' . $user_id . '\'
                    )
                )')->fetch_row();
            $this->mutual_counter = $this->mutual_counter[0];
        }
    }

    public function remove () {
        if (self::get_status($_GET['remove']) !== true) {
            Document::reload_msg(_('This user is not in your friendlist.'), $_SERVER['SCRIPT_NAME']);
        }
            $GLOBALS['db']->query('DELETE FROM `friendlist`
                WHERE ((
                    `userID` = \'' . $_SESSION['user_id'] . '\'
                    AND `friendID` = \'' . $_GET['remove'] . '\'
                ) OR (
                    `userID` = \'' . $_GET['remove'] . '\'
                    AND `friendID` = \'' . $_SESSION['user_id'] . '\'
                )) AND `accepted` = 1 LIMIT 1');
            Document::reload_msg(_('User has been removed from your friendlist.'), $_SERVER['SCRIPT_NAME']);
    }

}