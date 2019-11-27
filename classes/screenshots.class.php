<?php

class Screenshots {

    public $count = 0;
    public $data = array();
    public $pages = 0;

    public function __construct($limit = 12, $user_id = null) {
        $this->pages = $GLOBALS['db']->query('
                        SELECT COUNT(*)
                        FROM `screenshots`
                        ' . (($user_id !== null) ? 'WHERE `authorID` = \'' . $user_id . '\''
                            : ''))
                ->fetch_row();
        $this->pages = ceil($this->pages[0] / $limit);
        $sql = $GLOBALS['db']->query('SELECT *,
                (
                    SELECT COUNT(*)
                    FROM `comments`
                    WHERE `item_type` = \'screenshot\'
                    AND `item_id` = `screenshots`.`id`
                ) as `comments`
                FROM `screenshots`
                ' . (($user_id !== null) ? 'WHERE `authorID` = \'' . $user_id . '\''
                    : '') . '
                ORDER BY `timestamp` DESC
                LIMIT ' . ((Document::s_get_page($this->pages) - 1) * $limit) . ', ' . $limit);
        while ($row = $sql->fetch_assoc()) {
            $this->data[] = $row;
            $this->data[$this->count]['thumbnail'] = Images::thumbnail(
                            $_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/screenshots/' . $row['hash'] . '.' . $row['extension'],
                            CACHE_DIR
                            . '/screenshots', SCREENSHOT_WIDTH,
                            SCREENSHOT_QUALITY
            );
            $this->data[$this->count]['editable'] = (
                    Perms::get(Perms::SCREENSHOTS_MOD) || $_SESSION['user_id'] == $this->data[$this->count]['authorID']
                    ) ? true : false;
            ++$this->count;
        }
    }

    /**
     * Retrieves screenshot author ID. This function "includes and extends" Screenshots::screenshot_exists(),
     * which means you usually want to use only one of them.
     * @param int|string $screenshot_id screenshot ID, may be unsafe
     * @return boolean|string user ID if screenshot exists, otherwise false
     */
    public static function get_owner_id($screenshot_id) {
        if (!ctype_digit((string) $screenshot_id)) {
            return false;
        }
        $sql = $GLOBALS['db']
                ->query('SELECT `authorID`
                    FROM `screenshots`
                    WHERE `id` = \'' . $screenshot_id . '\'
                    LIMIT 1')
                ->fetch_row();
        if ($sql === null) {
            return false;
        }
        return $sql[0];
    }

    public static function screenshot_exists($screenshot_id) {
        if (!ctype_digit((string) $screenshot_id)) {
            return false;
        }
        $sql = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `screenshots`
                WHERE `id` = \'' . $screenshot_id . '\'')->fetch_row();
        if ($sql[0]) {
            return true;
        }
        return false;
    }

    public static function remove($screenshot_id, $filename = null) {
        if (!isset($filename)) {
            $filename = $GLOBALS['db']->query('SELECT CONCAT_WS(\'.\', hash, extension) as filename
                FROM `screenshots`
                WHERE `id` = \'' . $screenshot_id . '\'')->fetch_row()[0];
        }
        unlink($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/screenshots/' . $filename);
        $GLOBALS['db']->query('DELETE FROM `screenshots` WHERE `id` = \'' . $screenshot_id . '\'');
        Comments::delete('screenshot', $screenshot_id);
        Notifications::remove('screenshotComment', $screenshot_id);
        Notifications::remove('screenshotLike', $screenshot_id);
        $GLOBALS['db']->query('DELETE FROM `likes` WHERE `type` = \'screenshot\' AND `target_id` = \'' . $screenshot_id . '\'');
    }

    public static function screenshot_exists_by_hash($hash) {
        return (bool) $GLOBALS['db']->query('SELECT COUNT(*) FROM screenshots WHERE hash = ' . $GLOBALS['db']->quote($hash))->fetch_row()[0];
    }

}
