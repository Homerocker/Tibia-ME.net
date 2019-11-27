<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2010, Tibia-ME.net
 */
class Scores
{

    const KEEP_DATA_DAYS = 365;
    const KEEP_HOURLY_DATA_DAYS = 120;

    /**
     * @var int $pages count of pages
     */
    public $pages = 0;

    /**
     * @var int $results total search results counter
     */
    public $results = 0;
    private $period;

    public static function get_ep_by_level(int $level): int
    {
        return intval(5 / 6 * pow($level, 4) - 5 * pow($level, 3) + 115 / 6 * pow($level,
                2) - 25 * $level + 10);
    }

    public static function get_level_by_ep($ep = 0)
    {
        // 0 by default to prevent eternal looping in case of empty parameter sent

        $level = 2;

        while ($get_ep = self::get_ep_by_level($level)) {
            if ($get_ep > $ep) {
                return ($level - 1);
            } elseif ($get_ep == $ep) {
                return $level;
            } else {
                ++$level;
            }
        }
    }

    /**
     * Returns highscores last update date based on data in database.
     * @param int $day_offset
     * @return string|null date in MySQL DATE format, or NULL if database is empty
     */
    public static function date($day_offset = 0)
    {
        return Date::modify($GLOBALS['db']->query('SELECT GREATEST('
            . 'IFNULL((SELECT MAX(date) FROM scores_guilds), 0)'
            . ', IFNULL((SELECT MAX(date) FROM scores_highscores), 0)'
            . ', IFNULL((SELECT MAX(date) FROM scores_worlds), 0))')->fetch_row()[0], $day_offset);
    }

    public static function dateg()
    {
        return $GLOBALS['db']->query('SELECT GREATEST('
            . 'IFNULL((SELECT MAX(hour) FROM scores_highscores WHERE date = '
            . $GLOBALS['db']->quote(self::date()) . '), 0)'
            . ', IFNULL((SELECT MAX(hour) FROM scores_guilds WHERE date = '
            . $GLOBALS['db']->quote(self::date()) . '), 0))')->fetch_row()[0];
    }

    public static function ep_format($ep, $format_as_gain = false)
    {
        /*
          $length = strlen($ep);
          if ($length > 4) {
          $k = floor($length / 3);
          switch ($length - $k * 3) {
          case 0:
          $ep = round($ep / pow(10, --$k * 3), 1);
          break;
          case 1:
          $ep = round($ep / pow(10, --$k * 3));
          break;
          case 2:
          $ep = round($ep / pow(10, $k * 3), 1);
          break;
          }
          }
         */

        if (!$format_as_gain) {
            return '<span class="nowrap">' . (is_numeric($ep) ? ($ep == 0 ? '0' : number_format($ep,
                    0, ',', ' ')) : '?') . '</span>';
        } else {
            if (!is_numeric($ep)) {
                $ep = '<span class="nowrap" style="font-color: grey;">?';
            } elseif ($ep == 0) {
                $ep = '<span class="nowrap" style="font-color: grey;">+0';
            } elseif ($ep > 0) {
                $ep = '<span class="green nowrap">+' . number_format($ep, 0,
                        ',', ' ');
            } else {
                $ep = '<span class="red nowrap">' . number_format($ep, 0, ',',
                        ' ');
            }
            /*
              for ($i = 0; $i < $k; ++$i) {
              $ep .= 'k';
              }
             * 
             */
            $ep .= '</span>';
        }
        return $ep;
    }

    /**
     * counts amount of EP required to reach next level
     * @param int|string $ep current EP
     * @return int EP left to gain
     */
    public static function next_level($ep)
    {
        return (self::get_ep_by_level(self::get_level_by_ep($ep) + 1) - $ep);
    }

    /**
     * Gets character id from highscores database. Nickname and world should be safe to use with mysqli::query().
     * @param string $nickname
     * @param int|string $world
     * @return string|null char id or null
     * @todo previously returned false, make sure null doesn't break anything
     */
    public static function get_char_id($nickname, $world)
    {
        return $GLOBALS['db']->query('SELECT `id`
            FROM `scores_characters`
            WHERE `nickname` = \'' . $nickname . '\'
            AND `world` = \'' . $world . '\'
            LIMIT 1')->fetch_row()[0];
    }

    public function get_top100($world = null, $vocation = null)
    {
        $sql = 'SELECT scores_highscores.exp,'
            . ' scores_highscores.level,'
            . ' scores_highscores.rank_vocation,'
            . ' scores_highscores.rank_global,'
            . ' scores_characters.id,'
            . ' scores_characters.nickname,'
            . ' scores_characters.world,'
            . ' scores_characters.vocation'
            . ' FROM scores_highscores JOIN scores_characters'
            . ' ON scores_characters.id = scores_highscores.characterID'
            . ' WHERE scores_highscores.date = \'' . $this->date() . '\''
            . ' AND hour = ' . $this->dateg();
        if (isset($world)) {
            // specifying world
            $sql .= ' AND `scores_characters`.`world` = \'' . $world . '\'';
        }
        if (isset($vocation)) {
            // specifying vocation
            $sql .= ' AND `scores_characters`.`vocation` = \'' . $vocation . '\'';
        }
        $sql .= ' ORDER BY `scores_highscores`.`exp` DESC';
        $data = $GLOBALS['db']->query($sql)->fetch_all(MYSQLI_ASSOC);
        foreach ($data as $i => &$char) {
            if (isset($world)) {
                $char['rank'] = isset($vocation) ? ($char['rank_vocation'] ?? null)
                    : ($char['rank_global'] ?? null);
            } else {
                $char['rank'] = $i + 1;
            }
        }
        return $data;
    }

    public function get_pvp($world)
    {
        return $GLOBALS['db']->query('SELECT scores_characters.id, `scores_characters`.`nickname`,
                        `scores_characters`.`vocation`,
                        `scores_highscores`.`level`,
                        `scores_pvp`.`rank`,
                        `scores_pvp`.`quota`
                        FROM scores_pvp
                        JOIN scores_characters ON scores_characters.id = scores_pvp.characterID
                        LEFT JOIN `scores_highscores` ON scores_highscores.characterID = scores_pvp.characterID
                        WHERE `scores_characters`.`world` = \'' . $world . '\'
                        AND `scores_highscores`.`date` = \'' . $this->date() . '\'
                        AND scores_highscores.hour = ' . $this->dateg() . '
                        ORDER BY `rank` ASC'
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function get_char_data($char_id)
    {
        return $GLOBALS['db']->query('SELECT scores_player_max_min.max_gain_daily,
                        scores_player_max_min.min_gain_daily,
                        scores_player_max_min.max_gain_date_daily,
                        scores_player_max_min.max_gain_level_daily,
                        scores_player_max_min.min_gain_date_daily,
                        scores_player_max_min.min_gain_level_daily,
                        scores_player_max_min.max_gain_hourly,
                        scores_player_max_min.max_gain_date_hourly,
                        scores_player_max_min.max_gain_level_hourly,
                        scores_player_max_min.max_gain_hour,
                        scores_player_max_min.min_gain_hourly,
                        scores_player_max_min.min_gain_date_hourly,
                        scores_player_max_min.min_gain_level_hourly,
                        scores_player_max_min.min_gain_hour,
                        scores_characters.nickname,
                        scores_characters.world,
                        scores_characters.vocation,
                        scores_pvp.rank AS pvp_rank,
                        scores_highscores.exp,
                        scores_highscores.level,
                        scores_highscores.rank_global AS rank_world,
                        scores_highscores.rank_vocation AS rank_world_vocation,
                        TIMESTAMPDIFF(HOUR, CONCAT(scores_highscores.date, \' \', LPAD(scores_highscores.hour, 2, 0), \':05:00\'), NOW()) AS lastupdate,
                        scores_achievements.points AS achievements_points,
                        scores_achievements.rank_global AS achievements_rank_global,
                        scores_achievements.rank_vocation AS achievements_rank_vocation,
                        scores_characters_guilds.guild,
                        (SELECT COUNT(*) FROM scores_highscores as r1 WHERE r1.date = scores_highscores.date AND r1.hour = scores_highscores.hour AND (r1.exp > scores_highscores.exp OR (r1.exp = scores_highscores.exp AND r1.rank_global > scores_highscores.rank_global))) + 1 AS rank_global,
                        (SELECT COUNT(*) FROM scores_highscores as r2 INNER JOIN scores_characters AS c2 ON r2.characterID = c2.id WHERE c2.vocation = scores_characters.vocation AND r2.date = scores_highscores.date AND r2.hour = scores_highscores.hour AND (r2.exp > scores_highscores.exp OR (r2.exp = scores_highscores.exp AND r2.rank_vocation > scores_highscores.rank_vocation))) + 1 AS rank_global_vocation
                        FROM scores_characters
                        LEFT JOIN scores_highscores
                        ON scores_highscores.characterID = scores_characters.id
                        LEFT JOIN scores_player_max_min
                        ON scores_player_max_min.characterID = scores_characters.id
                        LEFT JOIN scores_pvp
                        ON scores_pvp.characterID = scores_characters.id
                        LEFT JOIN scores_achievements
                        ON scores_achievements.characterID = scores_characters.id
                        LEFT JOIN scores_characters_guilds
                        ON scores_characters_guilds.characterID = scores_characters.id
                        WHERE scores_characters.id = ' . $char_id
            . ' AND scores_highscores.date = '
            . '(SELECT MAX(date) FROM scores_highscores'
            . ' WHERE characterID = scores_characters.id)'
            . ' AND scores_highscores.hour = (SELECT MAX(hour)'
            . ' FROM scores_highscores AS h2'
            . ' WHERE h2.characterID = scores_characters.id'
            . ' AND h2.date = scores_highscores.date)')->fetch_assoc();
    }

    public function get_char_exp_history($char_id)
    {
        $sql = $GLOBALS['db']->query('SELECT exp, level, exp_gain_daily, exp_gain_hourly,'
            . ' rank_global, rank_vocation, date, hour'
            . ' FROM scores_highscores WHERE characterID = ' . $char_id
            . ' ORDER BY date DESC, hour ASC');
        $arr = [];
        while ($row = $sql->fetch_assoc()) {
            $arr[$row['date']][$row['hour']] = $row;
        }
        return $arr;
    }

    private function get_char_exp_gain_per_period($char_exp_history, $start_day_offset = -7, $end_day_offset = 0)
    {
        $exp_start = $char_exp_history[self::date($start_day_offset)][0]['exp'] ?? null;
        $exp_end = $char_exp_history[self::date($end_day_offset)][0]['exp'] ?? null;
        if ($exp_start === null || $exp_end === null) {
            return null;
        }
        return $exp_end - $exp_start;
    }

    public function get_char_performance($char_exp_history)
    {
        $arr = [];
        foreach ([7, 30, 90, 180] as $period) {
            $arr[$period]['gain'] = $this->get_char_exp_gain_per_period($char_exp_history, $period * -1, 0);
            $arr[$period]['performance'] = $this->get_char_exp_gain_per_period($char_exp_history, $period * -1 * 2, $period * -1);
            if ($arr[$period]['performance'] == 0) {
                $arr[$period]['performance'] = null;
            }
            if ($arr[$period]['performance'] !== null) {
                $arr[$period]['performance'] = ($arr[$period]['gain'] / $arr[$period]['performance'] - 1) * 100;
            }
        }
        return $arr;
    }

    public function get_exp_chart_data($exp_history)
    {
        $arr = [];
        foreach ($exp_history as $date => $row) {
            if ($date >= $this->date(-17) && isset($row[23]['exp_gain_daily'])) {
                $arr[] = [explode('-', $date), intval($row[23]['exp_gain_daily'])];
            }
        }
        return json_encode(array_reverse($arr));
    }

    public function get_worlds($world = null)
    {
        $sql = 'SELECT `scores_worlds`.`world`,'
            . ' `scores_worlds`.`gain`, `scores_worlds_max_min`.`maxGain`,'
            . ' `scores_worlds_max_min`.`minGain`,'
            . ' `scores_worlds_max_min`.`maxGainDate`,'
            . ' `scores_worlds_max_min`.`minGainDate`, '
            . '(SELECT COUNT(*) FROM `scores_characters` AS t1'
            . ' WHERE t1.`world` = `scores_worlds`.`world`) as `characters`'
            . ' FROM `scores_worlds`, `scores_worlds_max_min`'
            . ' WHERE `scores_worlds`.`date` = \'' . $this->date(-1) . '\''
            . ' AND `scores_worlds`.`world` = `scores_worlds_max_min`.`world`';
        if ($world !== null) {
            $sql .= ' AND `scores_worlds`.`world` = ' . $world;
        }
        $sql .= ' ORDER BY scores_worlds.gain DESC';
        $sql = $GLOBALS['db']->query($sql);
        return $world === null ? $sql->fetch_all(MYSQLI_ASSOC) : $sql->fetch_assoc();
    }

    public function get_achievements($world = null, $vocation = null)
    {
        $sql = 'SELECT `scores_achievements`.`points`, `scores_characters`.`nickname`'
            . ', scores_characters.id, `scores_achievements`.`rank_vocation`'
            . ', `scores_achievements`.`rank_global`, `scores_characters`.`world`'
            . ', `scores_characters`.`vocation` FROM `scores_achievements`'
            . ', `scores_characters` WHERE `scores_achievements`.`characterID` = `scores_characters`.`id`';
        if (isset($vocation)) {
            $sql .= ' AND `scores_characters`.`vocation` = \'' . $vocation . '\'';
        }
        if (isset($world)) {
            $sql .= ' AND `scores_characters`.`world` = \'' . $world . '\'';
        }
        $sql .= ' ORDER BY `scores_achievements`.`points` DESC'
            . ', `scores_achievements`.`rank_global` ASC'
            . ', `scores_achievements`.`rank_vocation` ASC';
        $achis = $GLOBALS['db']->query($sql)->fetch_all(MYSQLI_ASSOC);
        foreach ($achis as $i => &$achi) {
            $achi['rank'] = $i + 1;
        }
        return $achis;
    }

    public function get_guild_exp_history($guild_name, $world)
    {
        $sql = $GLOBALS['db']->query('SELECT exp, exp_gain_daily, exp_gain_hourly, rank, date, hour FROM `scores_guilds`'
            . ' WHERE `name` = ' . $GLOBALS['db']->quote($guild_name) . ' AND `world` = \''
            . $world . '\' ORDER BY `date` DESC, hour ASC');
        $arr = [];
        while ($row = $sql->fetch_assoc()) {
            $arr[$row['date']][$row['hour']] = $row;
        }
        return $arr;
    }

    public function get_guild_info($guild_name, $world)
    {
        return $GLOBALS['db']->query('SELECT name, world, exp, rank'
            . ' FROM scores_guilds WHERE name = '
            . $GLOBALS['db']->quote($guild_name) . ' AND world='
            . $world . ' AND date = ' . $GLOBALS['db']->quote($this->date())
            . ' AND hour = 0')->fetch_assoc();
    }

    public function get_guild_members($guild_name, $world)
    {
        return $GLOBALS['db']->query('SELECT scores_characters.id,'
            . ' scores_characters.nickname, scores_characters.vocation,'
            . ' scores_highscores.level, scores_highscores.rank_global,'
            . ' scores_highscores.rank_vocation'
            . ' FROM scores_characters, scores_characters_guilds, scores_highscores'
            . ' WHERE scores_characters_guilds.guild = ' . $GLOBALS['db']->quote($guild_name)
            . ' AND scores_characters.id = scores_characters_guilds.characterID'
            . ' AND scores_highscores.characterID = scores_characters.id'
            . ' AND scores_highscores.date = \'' . $this->date() . '\''
            . ' AND scores_highscores.hour = ' . $this->dateg()
            . ' AND scores_characters.world = ' . $world
            . ' ORDER BY level DESC')->fetch_all(MYSQLI_ASSOC);
    }

    public function get_guilds($world = null)
    {
        $sql = 'SELECT `name`, `rank`, `exp`, `world`'
            . ' FROM `scores_guilds` WHERE `date` = \'' . $this->date() . '\' AND hour = 0';
        if ($world !== null) {
            $sql .= ' AND `world` = \'' . $world . '\'';
        }
        $sql .= ' ORDER BY `exp` DESC, `rank` ASC';
        $guilds = $GLOBALS['db']->query($sql)->fetch_all(MYSQLI_ASSOC);
        if ($world === null) {
            // assigning ranks
            foreach ($guilds as $i => &$guild) {
                $guild['rank'] = $i + 1;
            }
        }
        return $guilds;
    }

    public function get_hunters_guilds($world = null, $period = null)
    {
        list($ymd, $g) = $this->parse_hunters_period($period);
        $sql = 'SELECT g1.name, g1.rank, g1.exp, g2.exp_gain_daily AS exp_gain, g1.world '
            . 'FROM scores_guilds AS g1 JOIN scores_guilds AS g2 ON g1.date = g2.date AND g1.name = g2.name AND g1.world = g2.world'
            . ' WHERE g1.date = ' . $GLOBALS['db']->quote($ymd) . ' AND g1.hour = 0 AND g2.hour = 23';
        /*
         * $sql = 'SELECT `name`, `rank`, `exp`, ' . ($g === null ? 'exp_gain_daily'
          : 'exp_gain_hourly') . ' AS  exp_gain, `world` FROM `scores_guilds` WHERE `date` = '
          . $GLOBALS['db']->quote($ymd) . ' AND hour = ' . $GLOBALS['db']->quote($g
          ?? ($ymd == $this->date() ? 0 : 23));
         * 
         */
        if (isset($world)) {
            $sql .= ' AND g1.`world` = \'' . $world . '\'';
        }
        $sql .= ' AND g2.' . ($g === null ? 'exp_gain_daily' : 'exp_gain_hourly') . ' > \'0\' ORDER BY g2.' . ($g
            === null ? 'exp_gain_daily' : 'exp_gain_hourly') . ' DESC';
        $guilds = $GLOBALS['db']->query($sql)->fetch_all(MYSQLI_ASSOC);
        foreach ($guilds as $i => &$guild) {
            $guild['rank'] = $i + 1;
        }
        return $guilds;
    }

    // @todo add overall period when namechanges info is available in XML file
    public function get_hunters($world = null, $vocation = null, $period = null,
                                $sort = 'gain')
    {
        list($ymd, $g) = $this->parse_hunters_period($period);

        $sql = 'SELECT scores_characters.id,'
            . ' scores_characters.nickname,'
            . ' scores_characters.world,'
            . ' scores_characters.vocation,'
            . ' scores_highscores.exp,'
            . ' scores_highscores.' . ($g === null ? 'exp_gain_daily' : 'exp_gain_hourly')
            . ' AS exp_gain, scores_highscores.level'
            . ' FROM scores_characters'
            . ' INNER JOIN scores_highscores ON scores_characters.id = scores_highscores.characterID'
            . ' WHERE date =' . $GLOBALS['db']->quote($ymd) . ' AND hour = ' . $GLOBALS['db']->quote($g
                ?? ($ymd == $this->date() ? $this->dateg() - 1 : 23));
        if ($vocation !== null) {
            $sql .= ' AND scores_characters.vocation = ' . $GLOBALS['db']->quote($vocation);
        }
        if ($world !== null) {
            $sql .= ' AND scores_characters.world = ' . $GLOBALS['db']->quote($world);
        }

        switch ($sort) {
            case 'level':
                $sql .= ' ORDER BY exp DESC';
                break;
            case 'loss':
                $sql .= ' AND ' . ($g === null ? 'exp_gain_daily' : 'exp_gain_hourly') . ' <  0';
                $sql .= ' ORDER BY ' . ($g === null ? 'exp_gain_daily' : 'exp_gain_hourly') . ' ASC';
                break;
            case 'gain':
            default:
                $sql .= ' AND ' . ($g === null ? 'exp_gain_daily' : 'exp_gain_hourly') . ' >  0';
                $sql .= ' ORDER BY ' . ($g === null ? 'exp_gain_daily' : 'exp_gain_hourly') . ' DESC';
        }

        $sql = $GLOBALS['db']->query($sql)->fetch_all(MYSQLI_ASSOC);
        foreach ($sql as $i => &$row) {
            $row['rank'] = $i + 1;
        }
        return $sql;
    }

    public function search($nickname, $world = null)
    {
        return $GLOBALS['db']->query('SELECT id, nickname, world, vocation
                FROM `scores_characters`
                WHERE `nickname` LIKE \'%' . $GLOBALS['db']->real_escape_string($nickname) . '%\'
                ' . (!isset($world) ? '' : 'AND `world` = \'' . $world . '\'') . '
                ORDER BY `nickname`' . (isset($world) ? '' : ', `world`'))->fetch_all(MYSQLI_ASSOC);
    }

    private function get_lastupdate_offset($lastupdate_Ymd, $lastupdate_G)
    {
        $interval = (new DateTime('now'))->diff(DateTime::createFromFormat('Y-m-d-G',
            $lastupdate_Ymd . '-' . $lastupdate_G), true);
        return $interval->format('%a') * 24 + date('G') - $lastupdate_G;
    }

    public function get_hunters_periods($guilds = false)
    {
        $date = DateTime::createFromFormat('Y-m-d-G-i',
            $this->date() . '-' . $this->dateg() . '-05');
        $date_now = clone $date;
        $dates = [];
        if (!$guilds) {
            $date->modify('-1 hour');
            $date_1h = clone $date;
            $dates[$date_1h->format('Y-m-d-G')] = $date_1h->format('d.m') . ' ' . $date_1h->format('H:i')
                . ' – ' . $date_now->format('H:i');
            if ($date_now->format('d') != $date_1h->format('d')) {
                $dates[$date_1h->format('Y-m-d-G')] .= ' ' . $date_now->format('d.m');
            }
            $date->modify('-1 hour');
            $date_2h = clone $date;
            $dates[$date_2h->format('Y-m-d-G')] = $date_2h->format('d.m') . ' ' . $date_2h->format('H:i') . ' – '
                . $date_1h->format('H:i');
            if ($date_1h->format('d') != $date_2h->format('d')) {
                $dates[$date_2h->format('Y-m-d-G')] .= ' ' . $date_1h->format('d.m');
            }
            if ($date_now->format('G') != 0) {
                $date_now->modify('-' . $date_now->format('G') . ' hour');
            }
            $dates[$date_now->format('Y-m-d')] = $date_now->format('d.m');
        }
        for ($i = 0; $i < 6; ++$i) {
            $date_now->modify('-1 day');
            $dates[$date_now->format('Y-m-d')] = $date_now->format('d.m');
        }
        return $dates;
    }

    public function get_period()
    {
        return $this->period;
    }

    private function parse_hunters_period($period = null)
    {
        if ($period === null) {
            $ymd = $this->date();
            $g = null;
        } else {
            $period = explode('-', $period);
            if (count($period) < 3) {
                return $this->parse_hunters_period();
            }
            $ymd = implode('-', array_slice($period, 0, 3));
            $g = $period[3] ?? null;
        }
        if (!($date = DateTime::createFromFormat('Y-m-d', $ymd)) || $date->format('Y-m-d')
            != $ymd) {
            return $this->parse_hunters_period();
        }
        $this->period = $ymd;
        if ($g !== null) {
            $this->period .= '-' . $g;
        }
        return [$ymd, $g];
    }

}
