<?php

/**
 * Photo Album functions.
 *
 * @package album
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 * @version 2.3.02
 */
class Album {

    public $count = 0;

    /**
     * @var array $data an array with fetched data 
     */
    public $data = array();
    public $pages = 0;
    public $error = array();

    /**
     * @var array $form HTTP form data 
     */
    public $form = array();

    public function users($limit = 10, $order = 'date', $world = null) {
        switch ($order) {
            case 'comments':
                if ($world === null) {
                    $this->pages = $GLOBALS['db']->
                            query('SELECT COUNT(distinct `album_albums`.`userID`)
                                FROM `album_albums`,
                                `album_photos`,
                                `comments`
                                WHERE `album_albums`.`id` = `album_photos`.`albumID`
                                AND `comments`.`item_type` = \'photo\'
                                AND `album_photos`.`id` = `comments`.`item_id`');
                    $this->pages = $this->pages->fetch_row();
                    $this->pages = ceil($this->pages[0] / $limit);
                    $sql = $GLOBALS['db']->query('select `album_albums`.`userID`, `users`.`nickname`, `users`.`world`, `user_settings`.`album_allow_comments` from `album_albums`, `album_photos`, `comments`, `users`, `user_settings` where `album_albums`.`id` = `album_photos`.`albumID` and `album_photos`.`id` = `comments`.`item_id` AND `comments`.`item_type` = \'photo\' and `album_albums`.`userID` = `users`.`id` and `user_settings`.`id` = `users`.`id` group by `album_albums`.`userID` order by MAX(`comments`.`timestamp`) desc limit ' . ((Document::s_get_page($this->pages)
                            - 1) * $limit) . ', ' . $limit);
                } else {
                    $this->pages = $GLOBALS['db']->
                            query('SELECT COUNT(distinct `album_albums`.`userID`)
                                FROM `album_albums`,
                                `album_photos`,
                                `comments`,
                                `users`
                                WHERE `album_albums`.`id` = `album_photos`.`albumID`
                                AND `album_photos`.`id` = `comments`.`item_id`
                                AND `comments`.`item_type` = \'photo\'
                                AND `album_albums`.`userID` = `users`.`id`
                                AND `users`.`world` = \'' . $world . '\'');
                    $this->pages = $this->pages->fetch_row();
                    $this->pages = ceil($this->pages[0] / $limit);
                    $sql = $GLOBALS['db']->query('select `album_albums`.`userID`, `users`.`nickname`, `users`.`world`, `user_settings`.`album_allow_comments` from `album_albums`, `album_photos`, `comments`, `users`, `user_settings` where `album_albums`.`id` = `album_photos`.`albumID` and `album_photos`.`id` = `comments`.`item_id` AND `comments`.`item_type` = \'photo\' and `album_albums`.`userID` = `users`.`id` and `user_settings`.`id` = `users`.`id` and `users`.`world` = \'' . $world . '\' group by `album_albums`.`userID` order by MAX(`comments`.`timestamp`) desc limit ' . ((Document::s_get_page($this->pages)
                            - 1) * $limit) . ', ' . $limit);
                }
                break;
            case 'nickname':
                if ($world === null) {
                    $this->pages = $GLOBALS['db']->query('SELECT COUNT(distinct `album_albums`.`userID`) FROM `users`, `album_albums`, `album_photos` WHERE `album_albums`.`userID` = `users`.`id` and `album_photos`.`albumID` = `album_albums`.`id`');
                    $this->pages = $this->pages->fetch_row();
                    $this->pages = ceil($this->pages[0] / $limit);
                    $sql = $GLOBALS['db']->query('SELECT `album_albums`.`userID`, `users`.`nickname`, `users`.`world`, `user_settings`.`album_allow_comments` FROM `users`, `album_albums`, `album_photos`, `user_settings` WHERE `album_albums`.`userID` = `users`.`id` and `album_photos`.`albumID` = `album_albums`.`id` and `user_settings`.`id` = `users`.`id` GROUP BY `album_albums`.`userID` ORDER BY `users`.`nickname` ASC LIMIT ' . ((Document::s_get_page($this->pages)
                            - 1) * $limit) . ', ' . $limit);
                } else {
                    $this->pages = $GLOBALS['db']->query('SELECT COUNT(distinct `album_albums`.`userID`) FROM `users`, `album_albums`, `album_photos` WHERE `album_albums`.`userID` = `users`.`id` and `album_photos`.`albumID` = `album_albums`.`id` and `users`.`world` = \'' . $world . '\'');
                    $this->pages = $this->pages->fetch_row();
                    $this->pages = ceil($this->pages[0] / $limit);
                    $sql = $GLOBALS['db']->query('SELECT `album_albums`.`userID`, `users`.`nickname`, `users`.`world`, `user_settings`.`album_allow_comments` FROM `users`, `album_albums`, `album_photos`, `user_settings` WHERE `album_albums`.`userID` = `users`.`id` and `album_photos`.`albumID` = `album_albums`.`id` and `user_settings`.`id` = `users`.`id` and `users`.`world` = \'' . $world . '\' GROUP BY `album_albums`.`userID` ORDER BY `users`.`nickname` ASC LIMIT ' . ((Document::s_get_page($this->pages)
                            - 1) * $limit) . ', ' . $limit);
                }
                break;
            default:
                if ($world === null) {
                    $this->pages = $GLOBALS['db']->query('SELECT COUNT(distinct `album_albums`.`userID`) FROM `album_photos`, `album_albums` WHERE `album_photos`.`albumID` = `album_albums`.`id`');
                    $this->pages = $this->pages->fetch_row();
                    $this->pages = ceil($this->pages[0] / $limit);
                    $sql = $GLOBALS['db']->query('SELECT `album_albums`.`userID`, `users`.`nickname`, `users`.`world`, `user_settings`.`album_allow_comments` FROM `album_photos`, `album_albums`, `users`, `user_settings` WHERE `album_photos`.`albumID` = `album_albums`.`id` AND `album_albums`.`userID` = `users`.`id` and `user_settings`.`id` = `users`.`id` GROUP BY `album_albums`.`userID` ORDER BY MAX(`album_photos`.`timestamp`) DESC LIMIT ' . ((Document::s_get_page($this->pages)
                            - 1) * $limit) . ', ' . $limit);
                } else {
                    $this->pages = $GLOBALS['db']->query('SELECT COUNT(distinct `album_albums`.`userID`) FROM `album_photos`, `album_albums`, `users` WHERE `album_photos`.`albumID` = `album_albums`.`id` AND `album_albums`.`userID` = `users`.`id` AND `users`.`world` = \'' . $world . '\'');
                    $this->pages = $this->pages->fetch_row();
                    $this->pages = ceil($this->pages[0] / $limit);
                    $sql = $GLOBALS['db']->query('SELECT `album_albums`.`userID`, `users`.`nickname`, `users`.`world`, `user_settings`.`album_allow_comments` FROM `album_photos`, `album_albums`, `users`, `user_settings` WHERE `album_photos`.`albumID` = `album_albums`.`id` AND `album_albums`.`userID` = `users`.`id` and `user_settings`.`id` = `users`.`id` AND `users`.`world` = \'' . $world . '\' GROUP BY `album_albums`.`userID` ORDER BY MAX(`album_photos`.`timestamp`) DESC LIMIT ' . ((Document::s_get_page($this->pages)
                            - 1) * $limit) . ', ' . $limit);
                }
        }
        while ($row = $sql->fetch_assoc()) {
            $this->data[] = $row;
            // generating thumbnail and adding its path to the array
            if ($order == 'comments') {
                // displaying thumbnail of the lastest commented photo as album cover
                $query = $GLOBALS['db']->query('
                        SELECT CONCAT_WS(\'.\', album_photos.hash, album_photos.extension) as filename,
                        (
                            SELECT COUNT(*)
                            FROM `album_albums`
                            WHERE `userID` = \'' . $this->data[$this->count]['userID'] . '\'
                        ) as `albums`,
                        (
                            SELECT COUNT(*)
                            FROM `album_photos`, `album_albums`
                            WHERE `album_albums`.`userID` = \'' . $this->data[$this->count]['userID'] . '\'
                            AND `album_photos`.`albumID` = `album_albums`.`id`
                        ) as `photos`,
                        (
                            SELECT COUNT(*)
                            FROM `comments`,
                            `album_photos`,
                            `album_albums`
                            WHERE `album_albums`.`userID` = \'' . $this->data[$this->count]['userID'] . '\'
                            AND `album_photos`.`albumID` = `album_albums`.`id`
                            AND `comments`.`item_type` = \'photo\'
                            AND `comments`.`item_id` = `album_photos`.`id`
                        ) as `comments`
                        FROM `comments`,
                        `album_photos`,
                        `album_albums`
                        WHERE `album_albums`.`userID` = \'' . $this->data[$this->count]['userID'] . '\'
                        AND `album_albums`.`id` = `album_photos`.`albumID`
                        AND `comments`.`item_type` = \'photo\'
                        AND `comments`.`item_id` = `album_photos`.`id`
                        ORDER BY `comments`.`timestamp` DESC
                        LIMIT 1'
                        )->fetch_assoc();
            } else {
                // displaying thumbnail of (one of) the lastest uploaded photo(s) as album cover
                $query = $GLOBALS['db']->query('
                            SELECT CONCAT_WS(\'.\', album_photos.hash, album_photos.extension) as filename,
                            (
                                SELECT COUNT(*)
                                FROM `album_albums`
                                WHERE `userID` = \'' . $this->data[$this->count]['userID'] . '\'
                            ) as `albums`,
                            (
                                SELECT COUNT(*)
                                FROM `album_photos`, `album_albums`
                                WHERE `album_albums`.`userID` = \'' . $this->data[$this->count]['userID'] . '\'
                                AND `album_photos`.`albumID` = `album_albums`.`id`
                            ) as `photos`,
                            (
                                SELECT COUNT(*)
                                FROM `comments`,
                                `album_photos`,
                                `album_albums`
                                WHERE `album_albums`.`userID` = \'' . $this->data[$this->count]['userID'] . '\'
                                AND `album_photos`.`albumID` = `album_albums`.`id`
                                AND `comments`.`item_type` = \'photo\'
                                AND `comments`.`item_id` = `album_photos`.`id`
                            ) as `comments`
                            FROM `album_photos`,
                            `album_albums`
                            WHERE `album_albums`.`userID` = \'' . $this->data[$this->count]['userID'] . '\'
                            AND `album_albums`.`id` = `album_photos`.`albumID`
                            ORDER BY `album_photos`.`timestamp` DESC
                            LIMIT 1'
                        )->fetch_assoc();
            }
            foreach ($query as $key => $value) {
                if ($key === 'filename') {
                    $this->data[$this->count]['thumbnail'] = Images::thumbnail(
                                    $_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/photos/'
                                    . $value, CACHE_DIR . '/photos',
                                    PHOTO_MINI_WIDTH, PHOTO_MINI_QUALITY
                    );
                } else {
                    $this->data[$this->count][$key] = $value;
                }
            }
            ++$this->count;
        }
    }

    public function albums($user_id, $limit = 5) {
        $this->pages = $GLOBALS['db']->query('
                    SELECT COUNT(*)
                    FROM `album_albums`
                    WHERE `userID` = \'' . $user_id . '\''
        );
        $this->pages = $this->pages->fetch_row();
        $this->pages = ceil($this->pages[0] / $limit);
        $sql = $GLOBALS['db']->query('SELECT `album_albums`.`id`,
                `album_albums`.`id` as `album_id`,
                `album_albums`.`title`,
                `album_albums`.`description`,
                `album_albums`.`password`,
                `album_albums`.`friends_only`,
                CONCAT_WS(\'.\', album_photos.hash, album_photos.extension) as filename,
                (
                    SELECT COUNT(*)
                    FROM `album_photos`
                    WHERE `albumID` = `album_albums`.`id`
                ) as `photos`,
                (
                    SELECT COUNT(*)
                    FROM `comments`, `album_photos`
                    WHERE `comments`.`item_type` = \'photo\'
                    AND `comments`.`item_id` = `album_photos`.`id`
                    AND `album_photos`.`albumID` = `album_albums`.`id`
                ) as `comments`,
                `user_settings`.`album_allow_comments`
                FROM `album_albums`, `album_photos`, `user_settings`
                WHERE `album_albums`.`userID` = \'' . $user_id . '\'
                AND `user_settings`.`id` = `album_albums`.`userID`
                AND `album_photos`.`albumID` = `album_albums`.`id`
                AND `album_photos`.`timestamp` =
                (
                    SELECT MAX( `timestamp` )
                    FROM `album_photos`
                    WHERE `albumID` = `album_albums`.`id`
                )
                GROUP BY `album_albums`.`id`
                ORDER BY `album_photos`.`timestamp` DESC
                LIMIT ' . ((Document::s_get_page($this->pages) - 1) * $limit) . ', ' . $limit
        );
        while ($row = $sql->fetch_assoc()) {
            $this->data[] = $row;
            $this->data[$this->count]['thumbnail'] = Images::thumbnail(
                            $_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/photos/'
                            . $this->data[$this->count]['filename'],
                            CACHE_DIR
                            . '/photos', PHOTO_MEDIUM_WIDTH,
                            PHOTO_MEDIUM_QUALITY
            );
            $this->data[$this->count]['title'] = htmlspecialchars(
                    $this->data[$this->count]['title'], ENT_COMPAT, 'UTF-8'
            );
            $this->data[$this->count]['description'] = htmlspecialchars(
                    $this->data[$this->count]['description'], ENT_COMPAT,
                    'UTF-8'
            );
            ++$this->count;
        }
        if ($user_id == $_SESSION['user_id']) {
            $sql = $GLOBALS['db']->query('SELECT `id`, `title`, `description`,
                        `password`, `friends_only`
                        FROM `album_albums`
                        WHERE `userID` = \'' . $user_id . '\'
                        AND (
                            SELECT COUNT(*)
                            FROM `album_photos`
                            WHERE `albumID` = `album_albums`.`id`
                        ) = 0'
            );
            while ($row = $sql->fetch_assoc()) {
                $this->data[] = $row;
                $this->data[$this->count]['photos'] = 0;
                $this->data[$this->count]['comments'] = 0;
                $this->data[$this->count]['thumbnail'] = '';
                $this->data[$this->count]['album_allow_comments'] = 1;
                $this->data[$this->count]['title'] = htmlspecialchars(
                        $this->data[$this->count]['title'], ENT_COMPAT, 'UTF-8'
                );
                $this->data[$this->count]['description'] = htmlspecialchars(
                        $this->data[$this->count]['description'], ENT_COMPAT,
                        'UTF-8'
                );
                ++$this->count;
            }
        }
    }

    public function photos($album_id, $limit = 12) {
        $this->pages = $GLOBALS['db']->query('
                        SELECT COUNT(*)
                        FROM `album_photos`
                        WHERE `albumID` = \'' . $album_id . '\''
        );
        $this->pages = $this->pages->fetch_row();
        $this->pages = ceil($this->pages[0] / $limit);
        $sql = $GLOBALS['db']->query('SELECT `album_photos`.`id`,
                album_photos.hash,
                album_photos.extension,
                `album_photos`.`timestamp`,
                album_albums.userID,
                (
                    SELECT COUNT(*)
                    FROM `comments`
                    WHERE `item_type` = \'photo\'
                    AND `item_id` = `album_photos`.`id`
                ) as `comments`,
                (
                    SELECT COUNT(*)
                    FROM `user_profile`
                    WHERE `avatarID` = `album_photos`.`id`
                    AND `id` = `album_albums`.`userID`
                ) as `is_avatar`,
                `album_photos`.`filesize`,
                `album_photos`.`resolution`
                FROM `album_photos`, `album_albums`
                WHERE `album_photos`.`albumID` = `album_albums`.`id`
                AND `album_photos`.`albumID` = \'' . $album_id . '\'
                ORDER BY `album_photos`.`timestamp` DESC
                LIMIT ' . ((Document::s_get_page($this->pages) - 1) * $limit) . ', ' . $limit
        );
        while ($row = $sql->fetch_assoc()) {
            $row['thumbnail'] = Images::thumbnail(
                            $_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/photos/'
                            . $row['hash'].'.'.$row['extension'],
                            CACHE_DIR
                            . '/photos', PHOTO_MEDIUM_WIDTH,
                            PHOTO_MEDIUM_QUALITY
            );
            $row['file'] = UPLOAD_DIR . '/photos/'
                    . $row['hash'].'.'.$row['extension'];
            $row['filesize'] = ceil($row['filesize']
                    / 1024);
            $this->data[] = $row;
        }
        $sql = $GLOBALS['db']->query('
            SELECT `album_albums`.`title`,
            (
                    SELECT COUNT(*)
                    FROM `album_albums`
                    WHERE `userID` = `album_albums`.`userID`
                    AND `id` != `album_albums`.`id`
            ) as `other_albums`,
            `user_settings`.`album_allow_comments`,
            `album_albums`.`userID`
            FROM `album_albums`,
            `user_settings`
            WHERE `album_albums`.`id` = \'' . $album_id . '\'
            AND `user_settings`.`id` = `album_albums`.`userID`');
        $sql = $sql->fetch_assoc();
        foreach ($sql as $key => $value) {
            $this->$key = $value;
        }
    }

    public function search($limit) {
        if (empty($_GET['search']) || strpos($_GET['search'], '%')) {
            $this->results = 0;
            return;
        }
        $this->results = $GLOBALS['db']->query('
                    SELECT COUNT(DISTINCT `users`.`id`)
                    FROM `users`,
                    `album_albums`,
                    `album_photos`
                    WHERE `album_albums`.`userID` = `users`.`id`
                    AND `album_photos`.`albumID` = `album_albums`.`id`
                    AND `users`.`nickname` LIKE \'%' . $GLOBALS['db']->real_escape_string($_GET['search']) . '%\''
        );
        $this->results = $this->results->fetch_row();
        $this->results = $this->results[0];
        $this->pages = ceil($this->results / $limit);
        $sql = $GLOBALS['db']->query('
                    SELECT DISTINCT `album_albums`.`userID`,
                    `users`.`nickname`,
                    `users`.`world`,
                    `user_settings`.`album_allow_comments`
                    FROM `users`,
                    `album_albums`,
                    `album_photos`,
                    `user_settings`
                    WHERE `album_albums`.`userID` = `users`.`id`
                    AND `user_settings`.`id` = `users`.`id`
                    AND `album_photos`.`albumID` = `album_albums`.`id`
                    AND `users`.`nickname` LIKE \'%' . $GLOBALS['db']->real_escape_string($_GET['search']) . '%\'
                    ORDER BY `users`.`nickname` ASC
                    LIMIT ' . ((Document::s_get_page($this->pages) - 1) * $limit) . ', ' . $limit
        );
        while ($row = $sql->fetch_assoc()) {
            $this->data[] = $row;
            // generating thumbnail and adding its path to the array
            // displaying thumbnail of (one of) the lastest uploaded photo(s) as album cover
            $query = $GLOBALS['db']->query('
                                SELECT CONCAT_WS(\'.\', album_photos.hash, album_photos.extension) as filename,
                                (
                                    SELECT COUNT(*)
                                    FROM `album_albums`
                                    WHERE `userID` = \'' . $this->data[$this->count]['userID'] . '\'
                                ) as `albums`,
                                (
                                    SELECT COUNT(*)
                                    FROM `album_photos`, `album_albums`
                                    WHERE `album_albums`.`userID` = \'' . $this->data[$this->count]['userID'] . '\'
                                    AND `album_photos`.`albumID` = `album_albums`.`id`
                                ) as `photos`,
                                (
                                    SELECT COUNT(*)
                                    FROM `comments`,
                                    `album_photos`,
                                    `album_albums`
                                    WHERE `album_albums`.`userID` = \'' . $this->data[$this->count]['userID'] . '\'
                                    AND `album_photos`.`albumID` = `album_albums`.`id`
                                    AND `comments`.`item_type` = \'photo\'
                                    AND `comments`.`item_id` = `album_photos`.`id`
                                ) as `comments`
                                FROM `album_photos`,
                                `album_albums`
                                WHERE `album_albums`.`userID` = \'' . $this->data[$this->count]['userID'] . '\'
                                AND `album_albums`.`id` = `album_photos`.`albumID`
                                ORDER BY `album_photos`.`timestamp` DESC
                                LIMIT 1'
                    )->fetch_assoc();
            foreach ($query as $key => $value) {
                if ($key == 'filename') {
                    $this->data[$this->count]['thumbnail'] = Images::thumbnail(
                                    $_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/photos/' . $value,
                                    CACHE_DIR
                                    . '/photos', PHOTO_MINI_WIDTH,
                                    PHOTO_MINI_QUALITY
                    );
                } else {
                    $this->data[$this->count][$key] = $value;
                }
            }
            ++$this->count;
        }
    }

    public static function get_album_owner_id($album_id) {
        $sql = $GLOBALS['db']->query('SELECT `album_albums`.`userID` FROM `album_albums`, `album_photos` WHERE `album_albums`.`id` = \'' . $album_id . '\' AND `album_albums`.`id` = `album_photos`.`albumID`');
        $sql = $sql->fetch_row();
        return $sql[0];
    }

    public static function album_exists($album_id) {
        if (!ctype_digit((string) $album_id)) {
            return false;
        }
        $sql = $GLOBALS['db']
                ->query('SELECT COUNT(*)
                    FROM `album_albums`
                    WHERE `id` = \'' . $album_id . '\'')
                ->fetch_row();
        if ($sql[0]) {
            return true;
        }
        return false;
    }

    public static function photo_exists($photo_id) {
        if (!ctype_digit((string) $photo_id)) {
            return false;
        }
        $sql = $GLOBALS['db']->query('SELECT COUNT(*)
                FROM `album_photos` WHERE `id` = \'' . $photo_id . '\'')->fetch_row();
        if ($sql[0]) {
            return true;
        }
        return false;
    }

    /**
     * Retrieves photo author ID. This function "includes and extends" Album::photo_exists(),
     * which means you usually want to use only one of them.
     * @param int|string $photo_id photo ID, may be unsafe
     * @return boolean|string user ID if photo exists, otherwise false
     */
    public static function get_photo_owner_id($photo_id) {
        if (!ctype_digit((string) $photo_id)) {
            return false;
        }
        $sql = $GLOBALS['db']
                ->query('SELECT `album_albums`.`userID`
                    FROM `album_photos`, `album_albums`
                    WHERE `album_photos`.`id` = \'' . $photo_id . '\'
                    AND `album_photos`.`albumID` = `album_albums`.`id`
                    LIMIT 1')
                ->fetch_row();
        if ($sql === null) {
            return false;
        }
        return $sql[0];
    }

    public static function photo_remove($photo_id) {
        $query = $GLOBALS['db']->query('SELECT CONCAT_WS(\'.\', album_photos.hash, album_photos.extension) as filename, album_albums.userID FROM `album_photos`, album_albums WHERE album_albums.id = album_photos.albumID and album_photos.`id` = \'' . $photo_id . '\'')->fetch_assoc();
        $GLOBALS['db']->query('DELETE FROM `album_photos` WHERE `id` = \'' . $photo_id . '\'');
        unlink($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/photos/' . $query['filename']);
        Comments::delete('photo', $photo_id);
        $GLOBALS['db']->query('UPDATE `user_profile` SET `avatarID` = NULL WHERE `avatarID` = \'' . $photo_id . '\'');
        Notifications::remove('photoComment', $photo_id);
        Notifications::remove('photoLike', $photo_id);
        $GLOBALS['db']->query('DELETE FROM `likes` WHERE `type` = \'photo\' AND `target_id` = \'' . $photo_id . '\'');
    }

    public static function album_title_navi_format($title) {
        if (isset($title[20])) {
            $title = mb_substr($title, 0, 14, 'UTF-8') . '...';
        }
        return htmlspecialchars($title, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Fetches album data that can be used in album edit form.
     * @param int|string $album_id album ID 
     */
    public function album_fetch_data($album_id) {
        $sql = $GLOBALS['db']->query('
                SELECT `title`,
                `description`,
                `password`,
                `friends_only`
                FROM `album_albums`
                WHERE `id` = \'' . $album_id . '\'
            ');
        $sql = $sql->fetch_assoc();
        $this->data = $sql;
        $this->data['id'] = $album_id;
    }

    public function album_update() {
        $_POST = array_map('trim', $_POST);
        if (!empty($_POST['title'])) {
            $this->data['title'] = $_POST['title'];
            if (isset($this->data['title'][32])) {
                $this->error[] = _('Title is too long.');
            }
        } else {
            $this->error[] = _('No title specified.');
        }

        if (!empty($_POST['description'])) {
            $this->data['description'] = $_POST['description'];
            if (isset($this->data['description'][256])) {
                $this->error[] = _('Description is too long.');
            }
        } else {
            $this->data['description'] = null;
        }

        if (!empty($_POST['password'])) {
            $this->data['password'] = $_POST['password'];
            if (!preg_match('/^[a-z0-9]*$/i', $this->data['password'])) {
                $this->error[] = _('Password contains illegal characters.');
            }
            if (!isset($this->data['password'][5])) {
                $this->error[] = _('Password is too short.');
            } elseif (isset($this->data['password'][8])) {
                $this->error[] = _('Password is too long.');
            }
        } else {
            $this->data['password'] = null;
        }
        if (isset($_POST['friends_only'])) {
            $this->data['friends_only'] = 1;
        } else {
            $this->data['friends_only'] = 0;
        }
        if (!empty($this->error)) {
            Document::msg($this->error);
            return;
        }

        $GLOBALS['db']->query('UPDATE `album_albums`
            SET `title` = \'' . $GLOBALS['db']->real_escape_string($this->data['title']) . '\',
                `description` = ' . (isset($this->data['description']) ? '\'' . $GLOBALS['db']->real_escape_string($this->data['description']) . '\''
                            : 'NULL') . ',
                `password` = ' . (isset($this->data['password']) ? '\'' . $this->data['password'] . '\''
                            : 'NULL') . ',
                `friends_only` = \'' . $this->data['friends_only'] . '\'
             WHERE `id` = \'' . $_REQUEST['id'] . '\'');

        Document::reload_msg(_('Changes saved.'),
                '/album/photos.php?album_id=' . $_REQUEST['id']);
    }

    /**
     * Either creates album and redirects to it or sets variables for album create form.
     */
    public function create_album() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->form = array(
                'title' => null,
                'description' => null,
                'password' => null,
                'friends_only' => null
            );
            return;
        }
        $_POST = array_map('trim', $_POST);
        // TITLE
        if (!empty($_POST['title'])) {
            if (isset($_POST['title'][24])) {
                $this->error[] = _('Album title is too long.');
            }
            $this->form['title'] = $_POST['title'];
        } else {
            $this->form['title'] = null;
            $this->error[] = _('Album title not specified.');
        }

        // DESCRIPTION
        if (!empty($_POST['description'])) {
            if (isset($_POST['description'][256])) {
                $this->error[] = _('Description is too long.');
            }
            $this->form['description'] = $_POST['description'];
        } else {
            $this->form['description'] = null;
        }

        // PASSWORD
        if (!empty($_POST['password'])) {
            $this->form['password'] = $_POST['password'];
            $this->validate_password($this->form['password']);
        } else {
            $this->form['password'] = null;
        }
        // FRIENDS ONLY
        if (isset($_POST['friends_only']) && $_POST['friends_only'] == 1) {
            $this->form['friends_only'] = 1;
        } else {
            $this->form['friends_only'] = 0;
        }

        // checking for errors
        if (!empty($this->error)) {
            Document::msg($this->error);
            return;
        }

        // adding new album to the database and redirecting to it if $this->error array is still empty
        $GLOBALS['db']->query('INSERT INTO `album_albums`
                (
                    `userID`,
                    `title`,
                    `description`,
                    `password`,
                    `friends_only`
                ) VALUES (
                    \'' . $_SESSION['user_id'] . '\',
                    \'' . $GLOBALS['db']->real_escape_string($this->form['title']) . '\',
                    ' . (($this->form['description'] === null) ? 'NULL' : '\'' . $GLOBALS['db']->real_escape_string($this->form['description']) . '\'') . ',
                    ' . (($this->form['password'] === null) ? 'NULL' : '\'' . $GLOBALS['db']->real_escape_string($this->form['password']) . '\'') . ',
                    ' . $this->form['friends_only'] . '
                )');
        Document::reload_msg(_('Album created.'),
                './photos.php?album_id=' . $GLOBALS['db']->insert_id);
    }

    /**
     * Validates album password. Adds error message to $this->error. Error message depends on locale.
     * @param string $pwd password to validate
     * @return boolean true if provided password is valid, otherwise false
     */
    private function validate_password($pwd) {
        if (!preg_match('/^[a-zA-Z0-9]+$/', $pwd)) {
            $this->error[] = _('Password contains illegal characters.');
            return false;
        } elseif (!isset($pwd[5])) {
            $this->error[] = _('Password is too short.');
            return false;
        } elseif (isset($pwd[8])) {
            $this->error[] = _('Password is too long.');
            return false;
        }
        return true;
    }

    /**
     * @param string $filename original file name with extension
     * @param int $angle
     * @return array
     */
    public static function rotate($filename, $angle = 90) {
        if (!($tempnam = tempnam(sys_get_temp_dir(), 'ROT'))) {
            return array('error' => _('Unknown error.'));
        }
        if (!Images::rotate($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/photos/' . $filename,
                        $angle, $tempnam)) {
            return array('error' => _('Unknown error.'));
        }
        $hash = hash_file(Uploader::HASH_ALGO, $tempnam);
        if (self::db_photo_exists($hash)) {
            // rotated image already exists in database
            return array('error' => _('Rotated image already exists.'));
        }
        return array(
            'path' => $tempnam,
            'hash' => $hash
        );
    }

    private static function db_photo_exists($hash) {
        $sql = $GLOBALS['db']->query('
            SELECT COUNT(*)
            FROM `album_photos`
            WHERE `hash` = \'' . $hash . '\'');
        $sql = $sql->fetch_row();
        if ($sql[0]) {
            return true;
        }
        return false;
    }

    public static function fetch_move_folders($current_album_id) {
        $sql = $GLOBALS['db']->query('SELECT `id`, `title`
            FROM `album_albums`
            WHERE `userID` = \'' . $_SESSION['user_id'] . '\'
            AND `id` != \'' . $current_album_id . '\'');
        $folders = array();
        while ($row = $sql->fetch_row()) {
            $folders[$row[0]] = self::album_title_navi_format($row[1]);
        }
        return $folders;
    }

    public function album_delete() {
        $sql = $GLOBALS['db']->query('SELECT `title`, `userID`
                FROM `album_albums`
                WHERE `id` = \'' . $_REQUEST['delete'] . '\'
                LIMIT 1')->fetch_row();
        if (!Perms::get(Perms::ALBUM_MOD) && $_SESSION['user_id'] != $sql[1]) {
            Document::reload_msg(
                    _('You don\'t have permission to delete this album.'),
                    './photos.php?album_id='
                    . $_REQUEST['delete']);
        }
        if (isset($_POST['submit'])) {
            $user_id = $sql[1];
        } else {
            $this->title = $sql[0];
            return;
        }
        $sql = $GLOBALS['db']
                ->query('SELECT CONCAT_WS(\'.\', album_photos.hash, album_photos.extension) as filename, album_albums.userID
            FROM `album_photos`, album_albums WHERE album_albums.id = album_photos.albumID and album_photos.`albumID` = \'' . $_REQUEST['delete'] . '\'');
        while ($row = $sql->fetch_row()) {
            unlink($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . '/photos/' . $row[0]);
        }
        $GLOBALS['db']->query('DELETE FROM `album_photos`
            WHERE `albumID` = \'' . $_REQUEST['delete'] . '\' LIMIT 1');
        $GLOBALS['db']->query('DELETE FROM `album_albums`
            WHERE `id` = \'' . $_REQUEST['delete'] . '\'');
        Document::reload_msg(_('Album has been deleted.'),
                $_SERVER['SCRIPT_NAME'] . '?u=' . $user_id);
    }

    public static function get_filename_owner_id($filename) {
        return $GLOBALS['db']->query('SELECT album_albums.userID FROM album_albums, album_photos'
                        . ' WHERE album_albums.id = album_photos.albumID AND CONCAT_WS(\'.\', album_photos.hash, album_photos.extension) = '
                        . $GLOBALS['db']->quote($filename) . ' LIMIT 1')->fetch_row()[0];
    }

    public static function photo_exists_by_hash($hash) {
        return (bool) $GLOBALS['db']->query('SELECT COUNT(*) FROM album_photos WHERE hash = ' . $GLOBALS['db']->quote($hash))->fetch_row()[0];
    }

}
