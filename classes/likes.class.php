<?php

class Likes
{

    const TARGET_TYPES = ['artwork', 'screenshot', 'photo', 'theme'];

    public static function display($target_type, $target_id)
    {
        $likes = self::get_likes($target_type, $target_id);
        if ($likes['up'] + $likes['down'] == 0) {
            $p = -1;
        } elseif ($likes['up'] == 0 && $likes['down'] > 0) {
            $p = 0;
        } else {
            $p = $likes['up'] * (100 / ($likes['down'] + $likes['up']));
        }
        if ($_SESSION['user_id']) {
            $liked = $GLOBALS['db']->query('SELECT `like`
            FROM `likes`
            WHERE `user_id` = ' . $_SESSION['user_id'] . '
            AND `type` = \'' . $target_type . '\'
            AND `target_id` = ' . $target_id)->fetch_row()[0] ?? -1;
        } else {
            $liked = null;
        }
        $template = new Templates;
        $template->assign(array(
            'p' => $p,
            'up' => $likes['up'],
            'down' => $likes['down'],
            'liked' => $liked,
            'target_type' => $target_type,
            'target_id' => $target_id
        ));
        $template->display('likes');
    }

    public static function toggle($target_type, $target_id, $like)
    {
        if (!$_SESSION['user_id'] || !in_array($target_type, self::TARGET_TYPES)
            || !is_int_string($target_id) || !is_int_string($like) || !in_array($like,
                [0, 1]) || !($target_owner_id = self::get_target_owner_id($target_type, $target_id))) {
            return false;
        }
        $liked = $GLOBALS['db']->query('SELECT `like`
            FROM `likes`
            WHERE `user_id` = \'' . $_SESSION['user_id'] . '\'
            AND `type` = \'' . $target_type . '\'
            AND `target_id` = \'' . $target_id . '\'')->fetch_row()[0];
        if ($liked === null) {
            // adding new like or dislike
            $GLOBALS['db']->query('INSERT INTO `likes` (user_id, type, target_id, `like`) VALUES (' . $_SESSION['user_id'] . ', \'' . $target_type . '\', ' . $target_id . ', ' . $like . ')');
        } elseif ($like == $liked) {
            // removing like or dislike
            $GLOBALS['db']->query('DELETE FROM `likes` WHERE user_id = ' . $_SESSION['user_id'] . ' AND type = \'' . $target_type . '\' AND target_id = ' . $target_id);
            $like = -1;
            // removing entry from unviewed notifications
            Notifications::user_remove($target_type . 'Like', $target_id);
        } else {
            // updating existing like or dislike
            $GLOBALS['db']->query('UPDATE `likes` SET `like` = ' . $like . ' WHERE user_id = ' . $_SESSION['user_id'] . ' AND type=\'' . $target_type . '\' AND target_id = ' . $target_id);
        }
        if ($like == 1) {
            Notifications::create($target_type . 'Like', $target_id,
                $target_owner_id);
        }
        return array_merge(self::get_likes($target_type, $target_id),
            ['like' => $like]);
    }

    private static function get_likes($target_type, $target_id)
    {
        return $GLOBALS['db']->query('SELECT COUNT(CASE WHEN `like` = 1 THEN 1 END) as `up`, COUNT(CASE WHEN `like` = 0 THEN 1 END) as `down` FROM `likes` WHERE type = \'' . $target_type . '\' AND target_id = ' . $target_id)->fetch_assoc();
    }

    private static function get_target_owner_id($target_type, $target_id)
    {
        switch ($target_type) {
            case 'photo':
                return Album::get_photo_owner_id($target_id);
            case 'screenshot':
                return Screenshots::get_owner_id($target_id);
            case 'theme':
                return Themes::AuthorID($target_id);
            case 'artwork':
                return Artworks::uploader_id($target_id);
        }
        return null;
    }

}
