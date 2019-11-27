<?php

/**
 * Themes functions.
 *
 * @package themes
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright Copyright (c) 2008, Tibia-ME.net
 * @version 2.2
 */
class Themes {

    /**
     * @var int count of elements on current page
     */
    public $count = 0;

    /**
     * @var array array with data to be displayed on current page
     */
    public $data = array();

    /**
     * @var int count of pages
     * default is 0, which means there are no items to display
     * or Document->pages() is not used at current page
     */
    public $pages = 0;

    /**
     * Fetches themes.
     * @param int|string $limit count of themes per page
     * @param int|string $user_id optional ID of user whos themes we should fetch
     */
    public function fetch($limit = 5, $user_id = null) {
        $this->pages = $GLOBALS['db']->query('
                        SELECT COUNT(*)
                        FROM `themes`
                        ' . (($user_id !== null) ? 'WHERE `authorID` = \'' . $user_id . '\''
                            : ''))
                ->fetch_row();
        $this->pages = ceil($this->pages[0] / $limit);
        $sql = $GLOBALS['db']->query('
                SELECT *,
                (
                    SELECT COUNT(*)
                    FROM `comments`
                    WHERE `item_type` = \'theme\'
                    AND `item_id` = `themes`.`id`
                ) as `comments`
                FROM `themes`
                ' . (($user_id !== null) ? ' WHERE `authorID` = \'' . $user_id . '\''
                    : '') . '
                ORDER BY `themes`.`timestamp` DESC
                LIMIT ' . ((Document::s_get_page($this->pages) - 1) * $limit) . ', ' . $limit
        );
        if (Perms::get(Perms::THEMES_MOD)) {
            $downloadable = $editable = 1;
        }
        while ($row = $sql->fetch_assoc()) {
            $this->data[] = $row;
            if ($this->data[$this->count]['screenshot'] !== null) {
                $this->data[$this->count]['thumbnail'] = Images::thumbnail(
                                $_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/themes/' . $this->data[$this->count]['screenshot'],
                                CACHE_DIR
                                . '/themes', THEME_WIDTH, THEME_QUALITY
                );
            } else {
                $this->data[$this->count]['thumbnail'] = null;
            }
            if (isset($downloadable) || (($this->data[$this->count]['status'] == 'checked'
                    || $this->data[$this->count]['status'] == 'tested') || $this->AuthorID($this->data[$this->count]['id'])
                    == $_SESSION['user_id'])) {
                $this->data[$this->count]['downloadable'] = 1;
            } else {
                $this->data[$this->count]['downloadable'] = 0;
            }
            if (isset($editable) || ($this->data[$this->count]['status'] == 'moderation'
                    && $this->AuthorID($this->data[$this->count]['id']) == $_SESSION['user_id'])) {
                $this->data[$this->count]['editable'] = 1;
            } else {
                $this->data[$this->count]['editable'] = 0;
            }
            ++$this->count;
        }
    }

    public function fetch_edit($theme_id) {
        $sql = $GLOBALS['db']->query('
                    SELECT `authorID`,
                    `screenshot`,
                    `status`,
                    `downloads`,
                    `moderatorID`
                    FROM `themes`
                    WHERE `id` = \'' . $theme_id . '\'
                    LIMIT 1'
                )->fetch_assoc();
        $this->data = $sql;
        if ($this->data['screenshot'] !== null) {
            $this->data['screenshot'] = '/uploads/themes/'
                    . $this->data['screenshot'];
            $this->data['thumbnail'] = Images::thumbnail(
                            $_SERVER['DOCUMENT_ROOT'] . $this->data['screenshot'],
                            CACHE_DIR
                            . '/themes', THEME_WIDTH, THEME_QUALITY
            );
        } else {
            $this->data['thumbnail'] = null;
        }
        if (Perms::get(Perms::THEMES_MOD)) {
            $downloadable = $editable = 1;
        }
        if (isset($downloadable) || (($this->data['status'] == 'checked' || $this->data['status']
                == 'tested') || $this->AuthorID($theme_id) == $_SESSION['user_id'])) {
            $this->data['downloadable'] = 1;
        } else {
            $this->data['downloadable'] = 0;
        }
        if (isset($editable) || ($this->data['status'] == 'moderation' && $this->AuthorID($theme_id)
                == $_SESSION['user_id'])) {
            $this->data['editable'] = 1;
        } else {
            $this->data['editable'] = 0;
        }
    }

    /**
     * gets theme's author ID
     * @param int|string $themeID theme ID
     * @return int user ID
     */
    public static function AuthorID($themeID) {
        $sql = $GLOBALS['db']->query('select `authorID`
            from `themes`
            where `id` = \'' . $themeID . '\'')->fetch_row();
        return $sql[0];
    }

    /**
     * checks if provided variable is a valid theme ID
     * @param int|string $theme_id variable to be checked
     * @return boolean true if variable is correct theme ID, otherwise false
     */
    public static function Exists($theme_id) {
        if (!ctype_digit((string) $theme_id)) {
            return false;
        }
        $sql = $GLOBALS['db']->query('select COUNT(*)
                from `themes`
                where `id` = \'' . $theme_id . '\'')->fetch_row();
        if ($sql[0]) {
            return true;
        }
        return false;
    }

    /**
     * Retrieves theme author ID. This function "includes and extends" Themes::Exists(),
     * which means you usually want to use only one of them.
     * @param int|string $theme_id theme ID, may be unsafe
     * @return boolean|string user ID if theme exists, otherwise false
     */
    public static function get_owner_id($theme_id) {
        if (!ctype_digit((string) $theme_id)) {
            return false;
        }
        $sql = $GLOBALS['db']
                ->query('SELECT `authorID`
                    FROM `themes`
                    WHERE `id` = \'' . $theme_id . '\'
                    LIMIT 1')
                ->fetch_row();
        if ($sql === null) {
            return false;
        }
        return $sql[0];
    }

    /**
     * Removes theme with all its data (both FS and DB).
     * Deletes notifications, likes and comments.
     * Does not clear cache.
     * @param int|string $theme_id ID of theme to be removed
     * @param array $file optional array with names of files to be removed (theme files and screenshot).
     * If not specified, function will fetch this data from the database
     * @return boolean true
     */
    public static function remove($theme_id, $file = null) {
        if (!isset($file)) {
            $file = $GLOBALS['db']
                            ->query('SELECT CONCAT(`s60_hash`, \'.sis\'),
                `screenshot`
                FROM `themes`
                WHERE `id` = \'' . $theme_id . '\'')->fetch_row();
        }
        foreach ($file as $filename) {
            if (!empty($filename)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/themes/' . $filename);
            }
        }
        $GLOBALS['db']->query('DELETE FROM `themes` WHERE `id` = \'' . $theme_id . '\'');
        Comments::delete('theme', $theme_id);
        Notifications::remove('themeComment', $theme_id);
        Notifications::remove('themeLike', $theme_id);
        $GLOBALS['db']->query('DELETE FROM `likes` WHERE `type` = \'theme\' AND `target_id` = \'' . $theme_id . '\'');
        return true;
    }

    public static function edit() {
        $sql = $GLOBALS['db']->query('
                    SELECT CONCAT(`s60_hash`, \'.sis\') as `s60_file`,
                    `authorID`,
                    `screenshot`,
                    `status`
                    FROM `themes`
                    WHERE `id` = \'' . $_REQUEST['theme_id'] . '\''
                )->fetch_assoc();
        if (!Perms::get(Perms::THEMES_MOD) && $_SESSION['user_id'] != $sql['authorID']) {
            Document::reload($_SERVER['SCRIPT_NAME'] . '?theme_id=' . $_REQUEST['theme_id']);
        }
        if (isset($_POST['delete'])) {
            Themes::remove($_REQUEST['theme_id'],
                    array($sql['s60_file'], $sql['screenshot']));
            Document::reload_msg(_('Theme has been deleted.'),
                    './?page=' . Document::s_get_page());
        }
        if (isset($_POST['status']) && Perms::get(Perms::THEMES_MOD) && ($_POST['status']
                == 'moderation' || $_POST['status'] == 'tested' || $_POST['status']
                == 'checked')) {
            $status = '\'' . $_POST['status'] . '\'';
            $moderatorID = $_SESSION['user_id'];
            $moderationTime = '\'' . $_SERVER['REQUEST_TIME'] . '\'';
        } else {
            $status = '\'moderation\'';
            $moderatorID = 'NULL';
            $moderationTime = 'NULL';
        }
        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] != 4) {
            if (!empty($sql['screenshot'])) {
                $GLOBALS['db']->query('UPDATE themes SET screenshot = NULL WHERE id = ' . $_REQUEST['theme_id']);
                unlink($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/themes/' . $sql['screenshot']);
            }
            $screenshot = Uploader::upload('screenshot',
                            array('type' => 'image', 'upload_dir' => '/themes'));
            if ($screenshot['error'] !== false) {
                Document::reload_msg($screenshot['error'],
                        $_SERVER['SCRIPT_NAME'] . '?theme_id=' . $_REQUEST['theme_id'] . '&page=' . Document::s_get_page());
            }
            $screenshot = '\'' . $screenshot['filename'] . '\'';
        } else {
            $screenshot = ($sql['screenshot'] === null) ? 'NULL' : '\'' . $sql['screenshot'] . '\'';
        }
        $GLOBALS['db']->query('UPDATE `themes`
            SET `status` = ' . $status . ',
            `moderatorID` = ' . $moderatorID . ',
            `moderationTime` = ' . $moderationTime . ',
            `screenshot` = ' . $screenshot . '
            WHERE `id` = \'' . $_REQUEST['theme_id'] . '\'');
        Document::reload_msg(_('Changes saved.'),
                $_SERVER['SCRIPT_NAME'] . '?theme_id=' . $_REQUEST['theme_id'] . '&page=' . Document::s_get_page());
    }

}
