<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2015, Tibia-ME.net
 */
class Perms
{

    const ALBUM_MOD = 1, USERS_BAN = 2, FORUM_MOD = 3, FORUM_HIDDEN_ACCESS = 4, USERS_PROFILE_EDIT
        = 5, SCREENSHOTS_MOD = 6, THEMES_MOD = 7, ARTWORKS_MOD = 8, CP_ACCESS
        = 9, IGNORE_BAN = 10, POST_NEWS = 11, RANKS_ASSIGN = 12, GAMECODES_ADD = 13, GAMECODES_ACTIVATE = 14, MAINTENANCE = 15, GAMECONTENT_SYNC = 16, GEO_DATA_UPDATE = 17, CALENDAR_EDIT = 18, CHAT_MOD = 19;

    private static $perms = array();

    public static function set($rank_id)
    {
        $sql = $GLOBALS['db']->query('SELECT perm_id FROM ranks_perms'
            . ' WHERE rank_id = ' . $rank_id);
        while ($row = $sql->fetch_row()) {
            self::$perms[] = $row[0];
        }
    }

    public static function get($perm_id)
    {
        return in_array($perm_id, self::$perms);
    }

    /**
     * @param int $perm_id
     * @return string|boolean perm constant name or false
     * @deprecated
     */
    public static function get_name($perm_id)
    {
        return array_search($perm_id, (new ReflectionClass('Perms'))->getConstants());
    }

}
