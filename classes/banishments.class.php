<?php

class Banishments
{

    public $count = 0, $data = array();

    public static function Reason($int)
    {
        if ($int == 1) {
            return _('Spam');
        } elseif ($int == 2) {
            return _('Offensive statements');
        } elseif ($int == 3) {
            return _('Rules violation');
        }
        return false;
    }

    public static function ban()
    {
        if (!Auth::user_exists($_POST['ban']) || !isset($_POST['reason']) || !in_array($_POST['reason'], array(1, 2, 3)) || !isset($_POST['expirationType']) || !in_array($_POST['expirationType'], array('t', 'p')) || !isset($_POST['expirationTimeType']) || !in_array($_POST['expirationTimeType'], array('d', 'h', 'm'))) {
            return false;
        }
        if ($GLOBALS['db']->query('SELECT COUNT(*) FROM banishments WHERE userID = ' . intval($_POST['ban']) . ' AND expirationTime > ' . $_SERVER['REQUEST_TIME'])->fetch_row()[0] > 0) {
            // @todo we should probably allow more than 1 active banishments at a time
            return false;
        }
        if ($_POST['expirationType'] == 't') {
            if (empty($_POST['expirationTime'])) {
                if (isset($_POST['forumPost']))
                    Document::reload_msg(_('Please specify correct expiration time.'), $_SERVER['PHP_SELF'] . '?ban=' . $_POST['ban'] . '&forumPost=' . $_POST['forumPost']);
                else
                    Document::reload_msg(_('Please specify correct expiration time.'), $_SERVER['PHP_SELF'] . '?ban=' . $_POST['ban']);
            }
            if ($_POST['expirationTimeType'] == 'm')
                $expiration = 60 * intval($_POST['expirationTime']) + $_SERVER['REQUEST_TIME'];
            elseif ($_POST['expirationTimeType'] == 'h')
                $expiration = 3600 * intval($_POST['expirationTime']) + $_SERVER['REQUEST_TIME'];
            elseif ($_POST['expirationTimeType'] == 'd')
                $expiration = 24 * 3600 * intval($_POST['expirationTime']) + $_SERVER['REQUEST_TIME'];
            if ($expiration <= $_SERVER['REQUEST_TIME']) {
                if (isset($_POST['forumPost']))
                    Document::reload_msg(_('Please specify correct expiration time.'), $_SERVER['PHP_SELF'] . '?ban=' . $_POST['ban'] . '&forumPost=' . $_POST['forumPost']);
                else
                    Document::reload_msg(_('Please specify correct expiration time.'), $_SERVER['PHP_SELF'] . '?ban=' . $_POST['ban']);
            }
        } else
            $expiration = 2147483647;

        if (!empty($_POST['description'])) {
            if (isset($_POST['forumPost'])) {
                $sql = $GLOBALS['db']->query('select `message` from `forumPosts` where `id` = \'' . intval($_POST['forumPost']) . '\' and `posterID` = \'' . intval($_POST['ban']) . '\'');
                if ($sql->num_rows) {
                    $sql = $sql->fetch_row();
                    $GLOBALS['db']->query('INSERT INTO `banishments` (`userID`, `expirationTime`, `reason`, `description`, `forumPost`, `bannedTime`, `bannedModeratorID`) VALUES (\'' . intval($_POST['ban']) . '\', \'' . intval($expiration) . '\', \'' . $_POST['reason'] . '\', \'' . $GLOBALS['db']->real_escape_string($_POST['description']) . '\', \'' . $sql[0] . '\', \'' . $_SERVER['REQUEST_TIME'] . '\', \'' . $_SESSION['user_id'] . '\')');
                    Document::reload_msg(sprintf(_('%s has been banished.'), User::get_link($_POST['ban'], false)), $_SERVER['PHP_SELF'] . '?u=' . $_POST['ban']);
                }
            }
            $GLOBALS['db']->query('INSERT INTO `banishments` (`userID`, `expirationTime`, `reason`, `description`, `bannedTime`, `bannedModeratorID`) VALUES (\'' . intval($_POST['ban']) . '\', \'' . intval($expiration) . '\', \'' . $_POST['reason'] . '\', \'' . $GLOBALS['db']->real_escape_string($_POST['description']) . '\', \'' . $_SERVER['REQUEST_TIME'] . '\', \'' . $_SESSION['user_id'] . '\')');
        } else {
            if (isset($_POST['forumPost'])) {
                $sql = $GLOBALS['db']->query('select `message` from `forumPosts` where `id` = \'' . intval($_POST['forumPost']) . '\' and `posterID` = \'' . intval($_POST['ban']) . '\'');
                if ($sql->num_rows) {
                    $sql = $sql->fetch_row();
                    $GLOBALS['db']->query('INSERT INTO `banishments` (`userID`, `expirationTime`, `reason`, `forumPost`, `bannedTime`, `bannedModeratorID`) VALUES (\'' . intval($_POST['ban']) . '\', \'' . intval($expiration) . '\', \'' . $_POST['reason'] . '\', \'' . $sql[0] . '\', \'' . $_SERVER['REQUEST_TIME'] . '\', \'' . $_SESSION['user_id'] . '\')');
                    Document::reload_msg(sprintf(_('%s has been banished.'), User::get_link($_POST['ban'], false)), $_SERVER['PHP_SELF'] . '?u=' . $_POST['ban']);
                }
            }
            $GLOBALS['db']->query('INSERT INTO `banishments` (`userID`, `expirationTime`, `reason`, `bannedTime`, `bannedModeratorID`) VALUES (\'' . intval($_POST['ban']) . '\', \'' . intval($expiration) . '\', \'' . $_POST['reason'] . '\', \'' . $_SERVER['REQUEST_TIME'] . '\', \'' . $_SESSION['user_id'] . '\')');
        }
    }

    // @todo looks sexy but not used
    private static function user_is_banned($user_id)
    {
        $sql = $GLOBALS['db']->query('SELECT COUNT(*) FROM `banishments` WHERE `userID` = '
            . $user_id . ' AND `expirationTime` > ' . $_SERVER['REQUEST_TIME']
            . ' AND `unbannedModeratorID` IS NULL')->fetch_row();
        return (bool)$sql[0];
    }

    public function fetch($user_id, $page, $per_page)
    {
        if ($user_id === null) {
            // fetching all bans with unique user id
            $sql = $GLOBALS['db']->query('SELECT * FROM banishments WHERE expirationTime > '
                . $_SERVER['REQUEST_TIME']
                . ' AND unbannedModeratorID IS NULL GROUP BY userID ORDER BY bannedTime DESC LIMIT '
                . (($page - 1) * $per_page) . ', ' . $per_page);
        } else {
            // fetching bans for specific user id
            $sql = $GLOBALS['db']->query('SELECT * FROM banishments WHERE `userID` = ' . $user_id
                . ' ORDER BY `bannedTime` DESC LIMIT ' . (($page - 1) * $per_page)
                . ', ' . $per_page);
        }
        while ($row = $sql->fetch_assoc()) {
            $this->data[] = $row;
            ++$this->count;
        }
    }

}
