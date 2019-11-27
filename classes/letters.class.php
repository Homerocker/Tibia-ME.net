<?php

/**
 * Private messaging (aka Letters) functions.
 *
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 */
class Letters {

    /**
     * @var array $data fetched data
     */
    public $data = array();

    /**
     * @var int $pages count of pages
     */
    public $pages = 0;

    /**
     * @var int $replyto_count reply counter
     */
    public $replyto_count = 0;

    /**
     * @var array $error errors
     */
    public $error = array();

    /**
     * @var string|null $subject letter subject
     */
    public $subject = null;

    /**
     * @var string|null $message letter message
     */
    public $message = null;

    public $nickname = null;

    public $world = null;

    /**
     * Fetches folder data
     * @param string $folder folder name
     */
    public function fetch_folder($folder, $limit = 20) {
        switch ($folder) {
            case 'inbox':
                $this->pages = $GLOBALS['db']->query('
                                    SELECT COUNT(*)
                                    FROM `letters`
                                    WHERE `to` = \'' . $_SESSION['user_id'] . '\'
                                    AND `recipient_display` = \'1\'
                                ')->fetch_row()[0];
                $this->pages = ceil($this->pages / $limit);
                $sql = $GLOBALS['db']->query('
                            SELECT `id`,
                            `from`,
                            `subject`,
                            `message`,
                            `flag`,
                            `timestamp`,
                            `replyto`,
                            recipient_saved
                            FROM `letters`
                            WHERE `to` = \'' . $_SESSION['user_id'] . '\'
                            AND `recipient_display` = \'1\'
                            ORDER BY `timestamp` DESC
                            LIMIT ' . ((Document::s_get_page($this->pages) - 1) * $limit) . ', ' . $limit
                );
                break;
            case 'outbox':
                $this->pages = $GLOBALS['db']->query('
                                    SELECT COUNT(*)
                                    FROM `letters`
                                    WHERE `from` = \'' . $_SESSION['user_id'] . '\'
                                    AND `flag` = \'1\'
                                    AND `sender_display` = \'1\'
                                ')->fetch_row()[0];
                $this->pages = ceil($this->pages / $limit);
                $sql = $GLOBALS['db']->query('
                            SELECT `id`,
                            `to`,
                            `subject`,
                            `message`,
                            `flag`,
                            `timestamp`,
                            `replyto`
                            FROM `letters`
                            WHERE `from` = \'' . $_SESSION['user_id'] . '\'
                            AND `flag` = \'1\'
                            AND `sender_display` = \'1\'
                            ORDER BY `timestamp` DESC
                            LIMIT ' . ((Document::s_get_page($this->pages) - 1) * $limit) . ', ' . $limit
                );
                break;
            case 'sentbox':
                $this->pages = $GLOBALS['db']->query('
                                    SELECT COUNT(*)
                                    FROM `letters`
                                    WHERE `from` = \'' . $_SESSION['user_id'] . '\'
                                    AND `flag` = \'0\'
                                    AND `sender_display` = \'1\'
                                ')->fetch_row();
                $this->pages = ceil($this->pages[0] / $limit);
                $sql = $GLOBALS['db']->query('
                            SELECT `id`,
                            `to`,
                            `subject`,
                            `message`,
                            `flag`,
                            `timestamp`,
                            `replyto`
                            FROM `letters`
                            WHERE `from` = \'' . $_SESSION['user_id'] . '\'
                            AND `flag` = \'0\'
                            AND `sender_display` = \'1\'
                            ORDER BY `timestamp` DESC
                            LIMIT ' . ((Document::s_get_page($this->pages) - 1) * $limit) . ', ' . $limit
                );
                break;
            case 'savebox':
                $this->pages = $GLOBALS['db']->query('
                                    SELECT COUNT(*)
                                    FROM `letters`
                                    WHERE (
                                        `to` = \'' . $_SESSION['user_id'] . '\'
                                        AND `recipient_saved` = \'1\'
                                    ) OR (
                                        `from` = \'' . $_SESSION['user_id'] . '\'
                                        AND `sender_saved` = \'1\'
                                    )
                                ')->fetch_row();
                $this->pages = ceil($this->pages[0] / $limit);
                $sql = $GLOBALS['db']->query('
                            SELECT `id`,
                            `from`,
                            `to`,
                            `subject`,
                            `message`,
                            `flag`,
                            `timestamp`,
                            `replyto`
                            FROM `letters`
                            WHERE (
                                `to` = \'' . $_SESSION['user_id'] . '\'
                                AND `recipient_saved` = \'1\'
                            ) OR (
                                `from` = \'' . $_SESSION['user_id'] . '\'
                                AND `sender_saved` = \'1\'
                            ) ORDER BY `timestamp` DESC
                            LIMIT ' . ((Document::s_get_page($this->pages) - 1) * $limit) . ', ' . $limit
                );
                break;
            default:
                log_error('invalid \'folder\'');
                return;
        }
        while ($row = $sql->fetch_assoc()) {
            // adding "Re" to subject if neccessary
            if ($row['replyto'] !== null) {
                $replies = $GLOBALS['db']->query('SELECT COUNT(*)
                    FROM `letters`
                    WHERE `replyto` = \'' . $row['replyto'] . '\'
                    AND `timestamp` < \'' . $row['timestamp'] . '\'')
                        ->fetch_row()[0];
                $row['subject'] = '[Re:' . ($replies + 1) . ']&nbsp;' . $row['subject'];
            }
            // converting sender and recipient IDs into links to their profiles
            if (isset($row['to'])) {
                $row['to'] = User::get_link($row['to']);
            }
            if (isset($row['from'])) {
                $row['from'] = User::get_link($row['from']);
            }
            // time
            $row['timestamp'] = User::date($row['timestamp']);
            $this->data[] = $row;
        }
    }

    /**
     * checks if letter ID is valid
     * @param int|string $letter_id letter ID
     * @return boolean true if letter exists, otherwise false
     */
    public function exists($letter_id) {
        if (!ctype_digit((string) $letter_id)) {
            return false;
        }
        $sql = $GLOBALS['db']->query('
                    SELECT COUNT(*)
                    FROM `letters`
                    WHERE `id` = \'' . $letter_id . '\''
                )->fetch_row();
        if ($sql[0]) {
            return true;
        }
        return false;
    }

    /**
     * fetches letter data
     * @param int|string $letter_id letter ID
     * @param string $folder folder
     */
    public function view($letter_id, $folder) {
        $sql = $GLOBALS['db']->query('
                        SELECT `from`,
                        `to`,
                        `subject`,
                        `message`,
                        `flag`,
                        timestamp'
                        . (($folder == 'inbox') ? ', `recipient_display`, `recipient_saved` as `saved`' : '')
                        . (($folder == 'outbox' || $folder == 'sentbox') ? ', `sender_display`, `sender_saved` as `saved`' : '')
                        . (($folder == 'savebox') ? ', `sender_saved`, `recipient_saved`' : '')
                        . 'FROM `letters`
                        WHERE `id` = \'' . $letter_id . '\'
                        LIMIT 1')
                ->fetch_assoc();
        if ($sql === null || ($folder == 'inbox' && ($sql['to'] != $_SESSION['user_id'] || !$sql['recipient_display'])) || (($folder == 'sentbox' || $folder == 'outbox') && ($sql['from'] != $_SESSION['user_id'] || !$sql['sender_display'])) || ($folder == 'savebox' && ($sql['from'] != $_SESSION['user_id'] || !$sql['sender_saved']) && ($sql['to'] != $_SESSION['user_id'] || !$sql['recipient_saved']))) {
            $this->data['error'] = _('Letter not found.');
            return;
        }
        $this->data['from'] = $sql['from'];
        $this->data['to'] = User::get_link($sql['to']);
        $this->data['subject'] = htmlspecialchars($sql['subject'], ENT_COMPAT, 'UTF-8');
        $this->data['message'] = Forum::MessageHandler($sql['message']);
        $this->data['timestamp'] = $sql['timestamp'];
        if (isset($sql['saved'])) {
            $this->data['saved'] = $sql['saved'];
        }
        if ($sql['flag'] && $sql['to'] == $_SESSION['user_id']) {
            $GLOBALS['db']->query('UPDATE `letters`
                SET `flag` = \'0\'
                WHERE `id` = \'' . $letter_id . '\'');
        }
    }

    public function fetch($letters_id) {
        if (is_array($letters_id)) {
            //$sql = $GLOBALS['db']->query('SELECT ');
        } else {
            $sql = $GLOBALS['db']->query('SELECT `message`
                FROM `letters`
                WHERE `id` = \'' . $letters_id . '\'')->fetch_assoc();
            return $sql['message'];
        }
    }

    public function edit() {
        $sql = $GLOBALS['db']->query('SELECT `to`,
                `from`,
                `subject`,
                `message`,
                `replyto`,
                `flag`
                FROM `letters`
                WHERE `id` = \'' . $_REQUEST['edit'] . '\'')
                ->fetch_assoc();
        if ($sql['from'] != $_SESSION['user_id']) {
            Document::reload_msg(_('You don\'t have permission to edit this letter.'), $_SERVER['SCRIPT_NAME'] . '?folder=outbox&view=' . $_REQUEST['edit']);
        }
        if (!$sql['flag']) {
            Document::reload_msg(_('This letter had been delivered. You cannot edit it.'), $_SERVER['SCRIPT_NAME'] . '?folder=sentbox&view=' . $_REQUEST['edit']);
        }
        $user_get_data = User::get_data($sql['to']);
        $this->nickname = $user_get_data[0];
        $this->world = $user_get_data[1];
        $this->subject = $sql['subject'];
        $this->message = $sql['message'];
        $this->set_subject();
        $this->set_message();
        if (!empty($this->error)) {
            return;
        }
        if (isset($_POST['submit'])) {
            $GLOBALS['db']->query('UPDATE `letters`
                SET `subject` = \'' . $GLOBALS['db']->real_escape_string($this->subject) . '\',
                `message` = \'' . $GLOBALS['db']->real_escape_string($this->message) . '\'
                WHERE `id` = \'' . $_REQUEST['edit'] . '\'
                AND `flag` = \'1\'');
            Document::reload_msg(
                    _('Changes saved.'), $_SERVER['SCRIPT_NAME']
                    . '?folder=outbox&view=' . $_REQUEST['edit']);
        }
    }

    /**
     * Removes letter from user's Savebox. Uses $_REQUEST['delete'] as letter ID.
     * @return boolean true on success, otherwise false
     */
    private function unsave() {
        $sql = $GLOBALS['db']->query('SELECT
                `to`,
                `from`
                FROM `letters`
                WHERE `id` = \'' . $_REQUEST['delete'] . '\'
                LIMIT 1')->fetch_assoc();
        if ($sql['to'] == $_SESSION['user_id']) {
            $GLOBALS['db']->query('UPDATE `letters`
                SET `recipient_saved` = \'0\'
                WHERE `id` = \'' . $_REQUEST['delete'] . '\'
                LIMIT 1');
        } elseif ($sql['from'] == $_SESSION['user_id']) {
            $GLOBALS['db']->query('UPDATE `letters`
                SET `sender_saved` = \'0\'
                WHERE `id` = \'' . $_REQUEST['delete'] . '\'
                LIMIT 1');
        } else {
            return false;
        }
        if ($GLOBALS['db']->affected_rows == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Removes letter. Uses $_REQUEST['delete'] as letter ID.
     * @param string $folder One of folders: inbox, outbox or sentbox.
     */
    public function delete($folder) {
        if ($folder == 'savebox') {
            if ($this->unsave()) {
                Document::reload_msg(_('This letter has been removed from Savebox.'), $_SERVER['SCRIPT_NAME'] . '?folder=' . $folder . '&page=' . Document::s_get_page());
            } else {
                Document::reload_msg(_('Could not delete this letter.'), $_SERVER['SCRIPT_NAME'] . '?folder=' . $folder . '&view=' . $_REQUEST['delete'] . '&page=' . Document::s_get_page());
            }
        }
        $GLOBALS['db']->query('UPDATE `letters`
            SET `' . (($folder == 'inbox') ? 'recipient_display' : 'sender_display') . '` = \'0\'
            WHERE `id` = \'' . $_REQUEST['delete'] . '\'
            LIMIT 1');
        if ($GLOBALS['db']->affected_rows == 1) {
            switch ($folder) {
                case 'inbox':
                    Document::reload_msg(_('This letter has been deleted from your Inbox.'), $_SERVER['SCRIPT_NAME'] . '?folder=' . $folder . '&page=' . Document::s_get_page());
                    break;
                case 'outbox':
                    Document::reload_msg(_('This letter has been deleted from your Outbox.'), $_SERVER['SCRIPT_NAME'] . '?folder=' . $folder . '&page=' . Document::s_get_page());
                    break;
                case 'sentbox':
                    Document::reload_msg(_('This letter has been deleted from your Sentbox.'), $_SERVER['SCRIPT_NAME'] . '?folder=' . $folder . '&page=' . Document::s_get_page());
                    break;
            }
        } else {
            Document::reload_msg(_('Could not delete this letter.'), $_SERVER['SCRIPT_NAME'] . '?folder=' . $folder . '&view=' . $_REQUEST['delete'] . '&page=' . Document::s_get_page());
        }
    }

    /**
     * Saves letter. User $_REQUEST['save'] as letter ID.
     * @param string $folder One of folders: inbox, outbox or sentbox.
     */
    public function save($folder) {
        $GLOBALS['db']->query('UPDATE `letters`
            SET `' . (($folder == 'inbox') ? 'recipient_saved' : 'sender_saved') . '` = \'1\'
            WHERE `id` = \'' . $_REQUEST['save'] . '\'
            AND `' . (($folder == 'inbox') ? 'to' : 'from') . '` = \'' . $_SESSION['user_id'] . '\'
            LIMIT 1');
        Document::reload_msg((($GLOBALS['db']->affected_rows == 1) ? _('Letter has been saved.') : _('Could not save letter.')), $_SERVER['SCRIPT_NAME'] . '?folder=' . $folder . '&view=' . $_REQUEST['save']);
    }

    /**
     * Sets variables for compose form. Sends letters.
     * @param int|string $replyto optional ID of letter to reply
     */
    public function compose() {
        $this->u = Auth::get_u(null);
        if (isset($this->u)) {
            if ($this->u == $_SESSION['user_id']) {
                $this->error[] = _('You cannot send letters to youself.');
            }
            $user = User::get_data($this->u);
            if ($user === false) {
                $this->error[] = _('Invalid recipient.');
            }
            $this->nickname = $user[0];
            $this->world = $user[1];
        } else {
            if (!empty($_POST['nickname'])) {
                $this->nickname = $_POST['nickname'];
            } else {
                $this->nickname = null;
            }
            if (!empty($_POST['world'])) {
                $this->world = intval($_POST['world']);
            } else {
                $this->world = null;
            }
            if ($this->nickname == $_SESSION['user_nickname'] && $this->world == $_SESSION['user_world']) {
                $this->error[] = _('You cannot send letters to yourself.');
            }
        }

        if (isset($_REQUEST['compose']) && ctype_digit($_REQUEST['compose'])) {
            $sql = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `letters`
                WHERE `to` = \'' . $_SESSION['user_id'] . '\'
                AND `id` = \'' . $_REQUEST['compose'] . '\'')->fetch_row();
            if ($sql[0]) {
                list($this->subject, $replyto, $this->replyto_count) = $GLOBALS['db']
                        ->query('SELECT `subject`, `replyto` as `replyto_temp`,
                (
                    SELECT COUNT(*)
                    FROM `letters`
                    WHERE `replyto` = `replyto_temp`
                ) as `reply_count`
                FROM `letters`
                WHERE `id` = \'' . $_REQUEST['compose'] . '\'')
                        ->fetch_row();
                if ($replyto === null) {
                    $replyto = $_REQUEST['compose'];
                }
                ++$this->replyto_count;
            }
        }

        $this->set_subject();
        $this->set_message();

        if (!empty($this->error)) {
            return;
        }

        if (isset($_POST['submit'])) {
            if (!isset($this->u)) {
                if (isset($this->nickname) && isset($this->world)) {
                    $u = User::get_id($this->nickname, $this->world, false);
                    if (!$u) {
                        $this->error[] = _('Invalid recipient.');
                        return;
                    } elseif ($u == $_SESSION['user_id']) {
                        $this->error[] = _('You cannot send letters to yourself.');
                        return;
                    }
                } else {
                    $this->error[] = _('Invalid recipient.');
                    return;
                }
            } else {
                $u = $this->u;
            }

            if (!empty($this->error)) {
                return;
            }

            $GLOBALS['db']->query('INSERT INTO `letters` (
                    `from`,
                    `to`,
                    `subject`,
                    `message`,
                    `timestamp`
                    ' . ($this->replyto_count ? ', `replyto`' : '') . '
                ) VALUES (
                    ' . $_SESSION['user_id'] . ',
                    ' . $u . ',
                    \'' . $GLOBALS['db']->real_escape_string($this->subject) . '\',
                    \'' . $GLOBALS['db']->real_escape_string($this->message) . '\',
                    UNIX_TIMESTAMP()
                    ' . ($this->replyto_count ? ', ' . $replyto : '') . '
                )');
            Notifications::mail($u, 'letter');
            Document::reload_msg(_('Letter has been sent.'), $_SERVER['SCRIPT_NAME'] . '?folder=outbox&view=' . $GLOBALS['db']->insert_id);
        }
    }

    /**
     * Sets $this->subject to value sent by client.
     * Adds error message to $this->error if value is not set or too long.
     * @return boolean false if value is not set or too long, otherwise true
     */
    private function set_subject() {
        if (!isset($_POST['submit'])) {
            return false;
        } elseif (isset($_POST['subject'])) {
            $this->subject = trim($_POST['subject']);
        }
        if (empty($this->subject)) {
            $this->error[] = _('No subject specified.');
            return false;
        } elseif (isset($this->subject[32])) {
            $this->error[] = _('Subject is too long.');
            return false;
        }
        return true;
    }

    /**
     * Sets $this->message to value sent by client.
     * Adds error message to $this->error if value is not set or too long.
     * @return boolean false if value is not set or too long, otherwise true
     */
    private function set_message() {
        if (!isset($_POST['submit'])) {
            return false;
        } elseif (isset($_POST['message'])) {
            $this->message = trim($_POST['message']);
        }
        if (empty($this->message)) {
            $this->error[] = _('No message specified.');
            return false;
        } elseif (isset($this->message[10000])) {
            $this->error[] = _('Message is too long.');
            return false;
        }
        return true;
    }

}
