<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2012, Tibia-ME.net
 */
class Artworks {

    public $data = array();

    public function __construct($artwork_id = null) {
        if (isset($artwork_id)) {
            $this->id = $artwork_id;
        }
    }

    public function fetch($limit) {
        $this->pages = $GLOBALS['db']->query('SELECT COUNT(*) FROM `artworks`');
        $this->pages = $this->pages->fetch_row();
        $this->pages = ceil($this->pages[0] / $limit);
        $this->page = Document::s_get_page($this->pages);
        $sql = $GLOBALS['db']->query('SELECT `artworks`.*,
            (
                SELECT COUNT(*) FROM `comments`
                WHERE `item_type` = \'artwork\'
                AND `item_id` = `artworks`.`id`
            ) as `comments`
            FROM `artworks` ORDER BY `artworks`.`timestamp` DESC
            LIMIT ' . (($this->page - 1) * $limit) . ', ' . $limit);
        while ($row = $sql->fetch_assoc()) {
            $row['thumbnail'] = Images::thumbnail(
                            $_SERVER['DOCUMENT_ROOT'].UPLOAD_DIR . '/artworks/' . $row['hash'] . '.' . $row['extension'], CACHE_DIR
                            . '/artworks', ARTWORK_WIDTH, ARTWORK_QUALITY
            );
            $this->data[] = $row;
        }
    }

    public static function artwork_exists($artwork_id) {
        if (!ctype_digit((string) $artwork_id)) {
            return false;
        }
        $sql = $GLOBALS['db']->query('SELECT COUNT(*) FROM `artworks` WHERE `id` = \'' . $artwork_id . '\'')->fetch_row();
        if ($sql[0]) {
            return true;
        }
        return false;
    }

    public static function uploader_id($artwork_id) {
        $sql = $GLOBALS['db']->query('
            SELECT `uploaderID`
            FROM `artworks`
            WHERE `id` = \'' . $artwork_id . '\''
        );
        $sql = $sql->fetch_row();
        if ($sql === null) {
            return false;
        } else {
            return $sql[0];
        }
    }

    public function upload() {

        if (!isset($_POST['upload'])) {
            if ($this->id !== 'auto_increment') {
                // EDITING ARTWORK (form is not submitted, POST request is not sent)
                // fetching data to check permissions and generate thumbnail
                $sql = $GLOBALS['db']->query('
                        SELECT concat_ws(\'.\', hash, extension) as `filename`,
                        `uploaderID`
                        FROM `artworks`
                        WHERE `id` = \'' . $this->id . '\''
                        )->fetch_assoc();
                // checking if user has permission to edit this artwork
                if ($_SESSION['user_id'] != $sql['uploaderID'] && !Perms::get(Perms::ARTWORKS_MOD)) {
                    Document::reload_msg(_('You don\'t have permission to edit this artwork.'), './');
                    return;
                }
                // thumbnail
                $this->thumbnail = Images::thumbnail(
                                $_SERVER['DOCUMENT_ROOT'].UPLOAD_DIR . '/artworks/'
                                . $sql['filename'], CACHE_DIR
                                . '/artworks', ARTWORK_WIDTH, ARTWORK_QUALITY
                );
            }
            return;
        } elseif ($this->id !== 'auto_increment') {
            // EDITING ARTWORK (form is submitted, POST request is sent)
            // fetching data to check permissions
            $sql = $GLOBALS['db']->query('
                    SELECT `uploaderID`
                    FROM `artworks`
                    WHERE `id` = \'' . $this->id . '\''
                    )->fetch_row();
            if ($_SESSION['user_id'] != $sql[0] && !Perms::get(Perms::ARTWORKS_MOD)) {
                Document::reload_msg(_('You don\'t have permission to edit this artwork.'), './');
                return;
            }
        }

        $upload = Uploader::upload(
                        'artwork', array('type' => 'image')
        );
        if ($upload['error'] !== false) {
            return $upload['error'];
        }

        if ($this->id == 'auto_increment') {
            $GLOBALS['db']->query('
                INSERT INTO `artworks`
                (
                    `hash`,
                    `extension`,
                    `filesize`,
                    `resolution`,
                    `uploaderID`,
                    `timestamp`
                )
                VALUES
                (
                    \'' . $upload['hash'] . '\',
                    \'' . $upload['extension'] . '\',
                    \'' . $upload['filesize'] . '\',
                    \'' . $upload['resolution'] . '\',
                    \'' . $_SESSION['user_id'] . '\',
                    \'' . $_SERVER['REQUEST_TIME'] . '\'
                )'
            );
            $insert_id = $GLOBALS['db']->insert_id;
            $GLOBALS['db']->query('INSERT INTO `comments_watch`
                (
                    `user_id`,
                    `target_type`,
                    `target_id`
                ) VALUES (
                    ' . $_SESSION['user_id'] . ',
                    \'artwork\',
                    ' . $insert_id . '
                )');
            Document::reload_msg(_('Artwork has been uploaded.'), $_SERVER['SCRIPT_NAME'] . '?id=' . $insert_id);
            return true;
        } else {
            $GLOBALS['db']->query('
                UPDATE `artworks`
                SET `hash` = \'' . $upload['hash'] . '\',
                    `extension` = \'' . $upload['extension'] . '\',
                    filesize = ' . $upload['filesize'] . ',
                    resolution = \'' . $upload['resolution'] . '\'
                    WHERE `id` = \'' . $this->id . '\''
            );
            Document::reload_msg(_('Changes saved.'), $_SERVER['SCRIPT_NAME'] . '?id=' . $this->id);
            return true;
        }
    }

    /**
     * Removes artwork from filesystem and database.
     * @param int|string $artwork_id valid artwork ID
     */
    public static function remove($artwork_id) {
        $sql = $GLOBALS['db']
                ->query('SELECT concat_ws(\'.\', hash, extension) as filename
                    FROM `artworks`
                    WHERE `id` = \'' . $artwork_id . '\'')
                ->fetch_row();
        unlink($_SERVER['DOCUMENT_ROOT'].UPLOAD_DIR.'/artworks/' . $sql[0]);
        $GLOBALS['db']->query('DELETE FROM `artworks` WHERE `id` = \'' . $artwork_id . '\'');
        Notifications::remove('artworkComment', $artwork_id);
        Notifications::remove('artworkLike', $artwork_id);
        $GLOBALS['db']->query('DELETE FROM `likes` WHERE `type` = \'artwork\' AND `target_id` = \'' . $artwork_id . '\'');
        Comments::delete('artwork', $artwork_id);
    }

    /**
     * Retrieves artwork uploader ID. This function "includes and extends" Artworks::artwork_exists(),
     * which means you usually want to use only one of them.
     * @param int|string $artwork_id artwork ID, may be unsafe
     * @return boolean|string user ID if artwork exists, otherwise false
     */
    public static function get_owner_id($artwork_id) {
        if (!ctype_digit((string) $artwork_id)) {
            return false;
        }
        $sql = $GLOBALS['db']
                ->query('SELECT `uploaderID`
                    FROM `artworks`
                    WHERE `id` = \'' . $artwork_id . '\'
                    LIMIT 1')
                ->fetch_row();
        if ($sql === null) {
            return false;
        }
        return $sql[0];
    }
    
    public static function artwork_exists_by_hash($hash) {
        return (bool) $GLOBALS['db']->query('SELECT COUNT(*) FROM artworks WHERE hash = ' . $GLOBALS['db']->quote($hash))->fetch_row()[0];
    }

}
