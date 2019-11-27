<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2010, Tibia-ME.net
 */
class HighscoresUpdater
{

    private $date, $max_world = 0, $XML, $zip, $DST_shift = false;

    public function __construct()
    {
        set_time_limit(3600 - intval(date('i')) * 60);
        $date = new DateTime();
        $this->date['now'] = [
            'Y-m-d' => $date->format('Y-m-d'),
            'G' => $date->format('G')
        ];
        $date->modify('-1 hour');
        $this->date['1h'] = [
            'Y-m-d' => $date->format('Y-m-d'),
            'G' => $date->format('G')
        ];
        if ($this->date['now']['G'] == 0) {
            $date->modify('-23 hours');
        } elseif ($this->date['now']['G'] > 1) {
            $date->modify('-' . ($this->date['now']['G'] - 1) . ' hours');
        }
        $this->date['1d'] = [
            'Y-m-d' => $date->format('Y-m-d'),
            'G' => $date->format('G')
        ];

        $transitions = Date::get_timezone_transition($this->date['now']['Y-m-d']);
        if ($transitions !== false && $transitions['offset'] < 0) {
            if ($transitions['timestamp'] == strtotime(date('Y-m-d\TH:00:00O'))) {
                exit('DST shift, skipping update.');
            }
            $this->DST_shift = true;
        }

        if (Scores::date() == $this->date['now']['Y-m-d'] && Scores::dateg() == $this->date['now']['G']) {
            echo Scores::date() . '==' . $this->date['now']['Y-m-d'] . '<br/>';
            echo Scores::dateg() . '==' . $this->date['now']['G'] . '<br/>';
            exit('No update required.');
        }

        if (date('i') < 10 || date('i') > 50) {
            exit('Wrong update time.');
        }

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/../highscoresupdater.lock')) {
            exit('Update already in progress.');
        }

        touch($_SERVER['DOCUMENT_ROOT'] . '/../highscoresupdater.lock');

        set_maintenance(1, 'm', null, 1);
        $GLOBALS['db']->truncate('scores_characters_guilds',
            'scores_achievements', 'scores_pvp');
    }

    public function fetch()
    {
        $this->zip = $this->date['now']['G'] == 0 ? $_SERVER['DOCUMENT_ROOT'] . '/../scores_' . $this->date['now']['Y-m-d'] . '.zip' : tempnam(sys_get_temp_dir(), 'scores');
        for ($i = 0; $i < 10; ++$i) {
            if (copy('https://www.tibiame.com/download/scores.zip?ts=' . $_SERVER['REQUEST_TIME'], $this->zip)) {
                return true;
            }
            sleep(30);
        }
        set_maintenance(false, null, null, 1);
        exit('Could not retrieve remote file.');
    }

    public function setXML()
    {
        $zip = new ZipArchive;
        $openzip = $zip->open($this->zip);
        if ($openzip !== true) {
            set_maintenance(false, null, null, 1);
            log_error($openzip);
            $zip->close();
            exit('Could not open scores.zip');
        }
        $this->XML = $zip->getFromName('scores.xml');
        if ($this->XML === false) {
            $zip->close();
            set_maintenance(false, null, null, 1);
            exit('Could not extract scores.xml');
        }
        $zip->close();
    }

    public function cleanup()
    {
        $GLOBALS['db']->query('DELETE FROM `scores_highscores` WHERE `date` <= \'' . Scores::date(-Scores::KEEP_DATA_DAYS) . '\'');
        $GLOBALS['db']->query('DELETE FROM `scores_guilds` WHERE `date` <= \'' . Scores::date(-Scores::KEEP_DATA_DAYS) . '\'');
        $GLOBALS['db']->query('DELETE FROM `scores_worlds` WHERE `date` <= \'' . Scores::date(-Scores::KEEP_DATA_DAYS) . '\'');

        Filesystem::emptydir($_SERVER['DOCUMENT_ROOT'] . CACHE_DIR . '/scores',
            true, true);
        if ($this->date['now']['G'] == 0 && file_exists($file = $_SERVER['DOCUMENT_ROOT'] . '/../scores_' . Date::modify($this->date['now']['Y-m-d'], -3) . '.zip')) {
            unlink($file);
        }

        if ($this->date['now']['G'] == 0) {
            /*
             * @todo guilds hourly exp history
              $GLOBALS['db']->query('DELETE FROM `scores_guilds`'
              . ' WHERE hour != 23 and hour != 0 AND date < DATE_SUB(\''
              . $this->date['now']['Y-m-d'] . '\', INTERVAL '
              . Scores::KEEP_HOURLY_DATA_DAYS . ' DAY)');
             * 
             */
            $GLOBALS['db']->query('DELETE FROM `scores_highscores`'
                . ' WHERE hour != 23 and hour != 0 AND date < DATE_SUB(\''
                . $this->date['now']['Y-m-d'] . '\', INTERVAL '
                . Scores::KEEP_HOURLY_DATA_DAYS . ' DAY)');
        }

        $GLOBALS['db']->query('DELETE FROM `scores_characters`
            WHERE id NOT IN (
                SELECT characterID
                FROM `scores_highscores`
            )');

        // @todo this doesn't belong here
        $GLOBALS['db']->query('DELETE FROM `guests_activity`'
            . ' WHERE `time` < \'' . ($_SERVER['REQUEST_TIME'] - max(min((int)ini_get('session.cookie_lifetime'), (int)ini_get('session.gc_maxlifetime')), 300)) . '\'');
        set_maintenance(false, null, null, 1);
    }

    public function parse()
    {
        $dom = new DOMDocument;

        if (!$dom->loadXML($this->XML)) {
            set_maintenance(false, null, null, 1);
            exit('Could not load XML content.');
        }

        $sql_scores_characters_select = $GLOBALS['db']->prepare('SELECT `id`, `vocation`'
            . ' FROM `scores_characters`'
            . ' WHERE `nickname` = ?'
            . ' AND `world` = ?', 'si');
        $sql_scores_characters_insert = $GLOBALS['db']->prepare('INSERT INTO `scores_characters`'
            . ' (`nickname`, `world`, `vocation`)'
            . ' VALUES (?, ?, ?)', 'sis');
        $sql_scores_characters_update = $GLOBALS['db']->prepare('UPDATE `scores_characters`'
            . ' SET `vocation` = ? WHERE `id` = ?', 'si');
        $sql_scores_highscores_exp = $GLOBALS['db']->prepare('SELECT exp, level FROM scores_highscores WHERE characterID = ? AND date = ? AND hour = ?', 'isi');
        $sql_scores_highscores_insert = $GLOBALS['db']->prepare('INSERT INTO `scores_highscores`'
            . ' (`characterID`, `level`, `exp`, `rank_global`,'
            . ' `rank_vocation`, `date`, `hour`)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?)', 'iiiiisi');
        $sql_scores_highscores_gains_insert = $GLOBALS['db']->prepare('INSERT INTO scores_highscores (characterID, exp_gain_daily, exp_gain_hourly, date, hour) VALUES (?, ?, ?, ?, ?)', 'iiisi');
        $sql_scores_highscores_gains_update = $GLOBALS['db']->prepare('UPDATE scores_highscores'
            . ' SET exp_gain_daily = ?, exp_gain_hourly = ? WHERE characterID = ?'
            . ' AND date = ? AND hour = ?', 'iiisi');
        if (!$this->DST_shift) {
            $sql_scores_player_max_min_select = $GLOBALS['db']->prepare('SELECT max_gain_daily, min_gain_daily'
                . ', max_gain_hourly, min_gain_hourly'
                . ' FROM scores_player_max_min WHERE characterID = ?', 'i');
            $sql_scores_player_max_min_insert = $GLOBALS['db']->prepare('INSERT INTO scores_player_max_min ('
                . 'characterID, '
                . 'max_gain_daily, '
                . 'max_gain_level_daily, '
                . 'max_gain_date_daily, '
                . 'min_gain_daily, '
                . 'min_gain_level_daily, '
                . 'min_gain_date_daily, '
                . 'max_gain_hourly, '
                . 'max_gain_level_hourly, '
                . 'max_gain_date_hourly, '
                . 'max_gain_hour, min_gain_hourly, '
                . 'min_gain_level_hourly, '
                . 'min_gain_date_hourly, '
                . 'min_gain_hour'
                . ') VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 'iiisiisiisiiisi');
            $sql_scores_player_max_daily_update = $GLOBALS['db']->prepare('UPDATE scores_player_max_min SET max_gain_daily = ?'
                . ', max_gain_level_daily = ?, max_gain_date_daily = ? WHERE characterID = ?', 'iisi');
            $sql_scores_player_min_daily_update = $GLOBALS['db']->prepare('UPDATE scores_player_max_min SET min_gain_daily = ?'
                . ', min_gain_level_daily = ?, min_gain_date_daily = ? WHERE characterID = ?', 'iisi');
            $sql_scores_player_max_hourly_update = $GLOBALS['db']->prepare('UPDATE scores_player_max_min SET max_gain_hourly = ?'
                . ', max_gain_level_hourly = ?, max_gain_date_hourly = ?, max_gain_hour = ? WHERE characterID = ?', 'iisii');
            $sql_scores_player_min_hourly_update = $GLOBALS['db']->prepare('UPDATE scores_player_max_min SET min_gain_hourly = ?'
                . ', min_gain_level_hourly = ?, min_gain_date_hourly = ?, min_gain_hour = ? WHERE characterID = ?', 'iisii');
        }
        $sql_scores_achievements_insert = $GLOBALS['db']->prepare('INSERT INTO `scores_achievements`'
            . ' (`characterID`, `points`, `rank_global`, `rank_vocation`)'
            . ' VALUES (?, ?, ?, ?)', 'iiii');
        $sql_scores_pvp_insert = $GLOBALS['db']->prepare('INSERT INTO `scores_pvp`'
            . ' (`characterID`, `rank`, `quota`)'
            . ' VALUES (?, ?, ?)', 'iii');
        $sql_scores_characters_guilds_insert = $GLOBALS['db']->prepare('INSERT INTO `scores_characters_guilds`'
            . ' (`characterID`, `guild`)  VALUES (?, ?)', 'is');
        if ($this->date['now']['G'] == 0) {
            // @todo enable for guilds hourly gains
            $sql_scores_guilds_insert = $GLOBALS['db']->prepare('INSERT INTO `scores_guilds` (`name`,'
                . ' `world`, `exp`, `rank`, `date`, hour)'
                . ' VALUES (?, ?, ?, ?, ?, ?)', 'siiisi');
            $sql_scores_guilds_exp = $GLOBALS['db']->prepare('select `exp` from `scores_guilds`'
                . ' where `name` = ? and `world` = ?'
                . ' and `date` = ? AND hour = ?', 'sisi');
            $sql_scores_guilds_gains_insert = $GLOBALS['db']->prepare('INSERT INTO scores_guilds'
                . ' (name, world, exp_gain_daily, exp_gain_hourly, date, hour)'
                . ' VALUES (?, ?, ?, ?, ?, ?)', 'siiisi');
            $sql_scores_guilds_gains_update = $GLOBALS['db']->prepare('UPDATE scores_guilds'
                . ' SET exp_gain_daily = ?, exp_gain_hourly = ?'
                . ' WHERE name = ? AND world = ? AND date = ? AND hour = ?', 'iisisi');
        }

        $worlds = $dom->getElementsByTagName('scores')->item(0)->getElementsByTagName('world');

        foreach ($worlds as $world) {
            $characters = $world->getElementsByTagName('characters')->item(0)->getElementsByTagName('character');

            $world_id = $world->getAttribute('id');
            $this->max_world = max($this->max_world, $world_id);

            foreach ($characters as $character) {
                $char = array(
                    'nickname' => $character->getAttribute('name')
                );
                $stats = $character->getElementsByTagName('stats')->item(0);
                foreach (array('level', 'vocation', 'exppoints',
                             'achievement', 'guild', 'quota') as $index) {
                    $char[$index] = $stats->getElementsByTagName($index);
                    $char[$index] = $char[$index]->length ? $char[$index]->item(0)->nodeValue
                        : null;
                }

                if (isset($char['exppoints'], $char['level']) && $char['level'] >= 226) {
                    // workaround for exppoints never exceeding 2147M
                    $expbylevel = Scores::get_ep_by_level($char['level']);
                    while ($char['exppoints'] < $expbylevel) {
                        $char['exppoints'] += 2147483647;
                    }
                }

                $ranks = $character->getElementsByTagName('ranks')->item(0)->getElementsByTagName('rank');

                foreach ($ranks as $rank) {
                    switch ($rank->getAttribute('type')) {
                        case 'highscore_global':
                            $char['rank_global'] = $rank->nodeValue;
                            break;
                        case 'highscore_warrior':
                        case 'highscore_wizard':
                            $char['rank_vocation'] = $rank->nodeValue;
                            break;
                        case 'achievement_global':
                            $char['rank_achievement_global'] = $rank->nodeValue;
                            break;
                        case 'achievement_warrior':
                        case 'achievement_wizard':
                            $char['rank_achievement_vocation'] = $rank->nodeValue;
                            break;
                        case 'pvp':
                            $char['rank_pvp'] = $rank->nodeValue;
                            break;
                    }
                }

                // updating character's info
                $sql = $sql_scores_characters_select->execute($char['nickname'], $world_id)->fetch_assoc();
                if (!$sql) {
                    // adding character to database if it does not exist
                    $sql_scores_characters_insert->execute($char['nickname'], $world_id, $char['vocation']);
                    $char['id'] = $sql_scores_characters_insert->insert_id;
                } else {
                    // updating character's vocation if it does not match the one in database
                    $char['id'] = $sql['id'];
                    if ($char['vocation'] != $sql['vocation']) {
                        $sql_scores_characters_update->execute($char['vocation'], $char['id']);
                    }
                }

                // updating `scores_highscores`
                // fetching level and exp for previous hour
                list($char['exp_gain_hourly'], $char['gain_level_hourly']) = $sql_scores_highscores_exp->execute($char['id'], $this->date['1h']['Y-m-d'], $this->date['1h']['G'])->fetch_row();
                // calculating hourly gain
                if ($char['exp_gain_hourly'] !== null) {
                    $char['exp_gain_hourly'] = $char['exppoints'] - $char['exp_gain_hourly'];
                    if ($char['exp_gain_hourly'] < 0) {
                        $char['gain_level_hourly'] = $char['level'];
                    }
                }

                if ($this->date['1d'] == $this->date['1h']) {
                    // hourly and daily gains are same (1h passed in current day (it's 1 AM now))
                    $char['exp_gain_daily'] = $char['exp_gain_hourly'];
                    $char['gain_level_daily'] = $char['gain_level_hourly'];
                } else {
                    // fetching level and exp for previous day (or start of current day)
                    list($char['exp_gain_daily'], $char['gain_level_daily']) = $sql_scores_highscores_exp->execute($char['id'], $this->date['1d']['Y-m-d'], $this->date['1d']['G'])->fetch_row();
                    // calculating daily gain
                    if ($char['exp_gain_daily'] !== null) {
                        $char['exp_gain_daily'] = $char['exppoints'] - $char['exp_gain_daily'];
                        if ($char['exp_gain_daily'] < 0) {
                            $char['gain_level_daily'] = $char['level'];
                        }
                    }
                }

                if (!isset($char['rank_vocation'])) {
                    $char['rank_global'] = $char['rank_vocation'] = null;
                } elseif (!isset($char['rank_global'])) {
                    $char['rank_global'] = null;
                }

                $sql_scores_highscores_insert->execute($char['id'], $char['level'], $char['exppoints'], $char['rank_global'], $char['rank_vocation'], $this->date['now']['Y-m-d'], $this->date['now']['G']);

                if ($char['exp_gain_hourly'] !== null || $char['exp_gain_daily']
                    !== null) {
                    // checking if scores_highscores entry exists for previous hour
                    if ($char['exp_gain_hourly'] !== null) {
                        $sql_scores_highscores_gains_update->execute($char['exp_gain_daily'], $char['exp_gain_hourly'], $char['id'], $this->date['1h']['Y-m-d'], $this->date['1h']['G']);
                    } else {
                        $sql_scores_highscores_gains_insert->execute($char['id'], $char['exp_gain_daily'], $char['exp_gain_hourly'], $this->date['1h']['Y-m-d'], $this->date['1h']['G']);
                    }
                }

                // updating `scores_player_max_min`
                if (!$this->DST_shift && ($char['exp_gain_daily'] !== null || $char['exp_gain_hourly']
                        !== null)) {
                    $sql = $sql_scores_player_max_min_select->execute($char['id'])->fetch_assoc();
                    if (!$sql) {
                        // character record does not exist, setting current exp gains as max and min
                        $sql_scores_player_max_min_insert->execute($char['id'], $char['exp_gain_daily'] ?? null, $char['exp_gain_level_daily'] ?? null, $char['exp_gain_daily'] ? $this->date['1d']['Y-m-d'] : null, $char['exp_gain_daily'] ?? null, $char['exp_gain_level_daily'] ?? null, $char['exp_gain_daily'] ? $this->date['1d']['Y-m-d'] : null, $char['exp_gain_hourly'] ?? null, $char['exp_gain_level_hourly'] ?? null, $char['exp_gain_hourly'] ? $this->date['1h']['Y-m-d'] : null, $char['exp_gain_hourly'] ? $this->date['1h']['G'] : null, $char['exp_gain_hourly'] ?? null, $char['exp_gain_level_hourly'] ?? null, $char['exp_gain_hourly'] ? $this->date['1h']['Y-m-d'] : null, $char['exp_gain_hourly'] ? $this->date['1h']['G'] : null);
                    } else {
                        // updating character's max/min daily gains
                        if ($char['exp_gain_daily'] !== null) {
                            if ($char['exp_gain_daily'] > $sql['max_gain_daily'] || $sql['max_gain_daily'] === null) {
                                $sql_scores_player_max_daily_update->execute($char['exp_gain_daily'], $char['gain_level_daily'], $this->date['1d']['Y-m-d'], $char['id']);
                            }
                            if ($char['exp_gain_daily'] < $sql['min_gain_daily'] || $sql['min_gain_daily'] === null) {
                                $sql_scores_player_min_daily_update->execute($char['exp_gain_daily'], $char['gain_level_daily'], $this->date['1d']['Y-m-d'], $char['id']);
                            }
                        }
                        // updating character's max/min hourly gains
                        if ($char['exp_gain_hourly'] !== null) {
                            if ($char['exp_gain_hourly'] > $sql['max_gain_hourly'] || $sql['max_gain_hourly'] === null) {
                                $sql_scores_player_max_hourly_update->execute($char['exp_gain_hourly'], $char['gain_level_hourly'], $this->date['1h']['Y-m-d'], $this->date['1h']['G'], $char['id']);
                            }
                            if ($char['exp_gain_hourly'] < $sql['min_gain_hourly'] || $sql['min_gain_hourly'] === null) {
                                $sql_scores_player_min_hourly_update->execute($char['exp_gain_hourly'], $char['gain_level_hourly'], $this->date['1h']['Y-m-d'], $this->date['1h']['G'], $char['id']);
                            }
                        }
                    }
                }

                // updating `scores_achievements`
                $sql_scores_achievements_insert->execute($char['id'], $char['achievement'], $char['rank_achievement_global'] ?? null, $char['rank_achievement_vocation'] ?? null);

                // updating `scores_pvp`
                if (isset($char['rank_pvp'], $char['quota'])) {
                    $sql_scores_pvp_insert->execute($char['id'], $char['rank_pvp'], $char['quota']);
                }

                // updating `scores_characters_guilds`
                if (isset($char['guild'])) {
                    $sql_scores_characters_guilds_insert->execute($char['id'], $char['guild']);
                }
            }

            // @todo guilds hourly exp history
            if ($this->date['now']['G'] != 0) {
                continue;
            }

            $guilds = $world->getElementsByTagName('guilds')->item(0)->getElementsByTagName('guild');

            foreach ($guilds as $guild) {
                $g = array(
                    'name' => $guild->getAttribute('name')
                );

                foreach (array('exppoints', 'rank') as $index) {
                    $g[$index] = $guild->getElementsByTagName($index);
                    if (!$g[$index]->length) {
                        $g[$index] = null;
                        continue;
                    }
                    $g[$index] = $g[$index]->item(0);
                    if ($index == 'rank' && $g[$index]->getAttribute('type') != 'guild') {
                        $g[$index] = null;
                        continue;
                    }
                    $g[$index] = $g[$index]->nodeValue;
                }

                $g['exp_gain_daily'] = $sql_scores_guilds_exp->execute($g['name'], $world_id, $this->date['1d']['Y-m-d'], $this->date['1d']['G'])->fetch_row()[0];
                if ($g['exp_gain_daily'] !== null) {
                    $g['exp_gain_daily'] = $g['exppoints'] - $g['exp_gain_daily'];
                }

                /*
                 * @todo guilds hourly exp history
                 * calculation hourly exp gain, disabled due to xml bug
                  if ($this->date['1d'] == $this->date['1h']) {
                  $g['exp_gain_hourly'] = $g['exp_gain_daily'];
                  } else {
                  $sql = $GLOBALS['db']->query('select `exp` from `scores_guilds`'
                  . ' where `name` = ' . $GLOBALS['db']->quote($g['name'])
                  . ' and `world` = \'' . $world_id . '\''
                  . ' and `date` = \'' . $this->date['1h']['Y-m-d']
                  . '\' AND hour = ' . $this->date['1h']['G'])->fetch_row()[0];
                  if ($sql === null) {
                  $g['exp_gain_hourly'] = null;
                  } else {
                  $g['exp_gain_hourly'] = $g['exppoints'] - $sql;
                  }
                  }
                 * 
                 */
                // @todo guilds hourly exp history
                // remove when enabling hourly exp gains calculation
                $g['exp_gain_hourly'] = null;

                $sql_scores_guilds_insert->execute($g['name'], $world_id, $g['exppoints'], $g['rank'], $this->date['now']['Y-m-d'], $this->date['now']['G']);

                if ($g['exp_gain_daily'] !== null || $g['exp_gain_hourly'] !== null) {
                    if ($g['exp_gain_hourly'] === null) {
                        $sql_scores_guilds_gains_insert->execute($g['name'], $world_id, $g['exp_gain_daily'], $g['exp_gain_hourly'], $this->date['1h']['Y-m-d'], $this->date['1h']['G']);
                    } else {
                        $sql_scores_guilds_gains_update->execute($g['exp_gain_daily'], $g['exp_gain_hourly'], $g['name'], $world_id, $this->date['1h']['Y-m-d'], $this->date['1h']['G']);
                    }
                }
            }
        }
    }

    public function update_worlds()
    {
        if ($this->date['now']['G'] != 0) {
            return false;
        }
        for ($i = 1; $i <= WORLDS; ++$i) {
            $sql = $GLOBALS['db']->query('SELECT SUM(`scores_highscores`.`exp_gain_daily`)'
                . ' FROM `scores_highscores`, `scores_characters`'
                . ' WHERE `scores_highscores`.`characterID` = `scores_characters`.`id`'
                . ' AND `scores_highscores`.`date` = \'' . $this->date['1d']['Y-m-d'] . '\''
                . ' AND `scores_highscores`.`hour` = 23'
                . ' AND `scores_characters`.`world` = ' . $i)->fetch_row()[0];
            if ($sql === null) {
                // no data for this world, jump to the next one
                continue;
            }
            $GLOBALS['db']->query('INSERT INTO `scores_worlds` (`world`, `date`, `gain`)'
                . ' VALUES (' . $i . ', \'' . $this->date['1d']['Y-m-d'] . '\', \'' . $sql . '\')');

            // updating world max/min gains
            if (!$this->DST_shift) {
                $sql2 = $GLOBALS['db']->query('SELECT `minGain`, `maxGain`'
                    . ' FROM `scores_worlds_max_min` WHERE `world` = ' . $i)->fetch_row();
                if ($sql2[0] === null || $sql2[0] > $sql) {
                    $GLOBALS['db']->query('UPDATE `scores_worlds_max_min` SET `minGain` = \'' . $sql . '\','
                        . ' `minGainDate` = \'' . $this->date['1d']['Y-m-d'] . '\''
                        . ' WHERE `world` = \'' . $i . '\' LIMIT 1');
                }
                if ($sql2[1] === null || $sql2[1] < $sql) {
                    $GLOBALS['db']->query('UPDATE `scores_worlds_max_min` SET `maxGain` = \'' . $sql . '\','
                        . ' `maxGainDate` = \'' . $this->date['1d']['Y-m-d'] . '\''
                        . ' WHERE `world` = \'' . $i . '\' LIMIT 1');
                }
            }
        }

        // we do not update new gameworlds now coz there's no exp gain data for them yet
        // so we just add them to the database for future updates
        if ($this->max_world > WORLDS) {
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/../worlds.dat',
                $this->max_world, LOCK_EX);
            if ($this->max_world - WORLDS == 1) {
                $GLOBALS['db']->query('INSERT INTO `scores_worlds_max_min` (`world`) VALUES (' . $this->max_world . ')');
            } else {
                for ($j = WORLDS + 1; $j <= $this->max_world; ++$j) {
                    $GLOBALS['db']->query('INSERT INTO `scores_worlds_max_min` (`world`) VALUES (' . $j . ')');
                }
            }
        }
    }

    public function __destruct()
    {
        unlink($_SERVER['DOCUMENT_ROOT'] . '/../highscoresupdater.lock');
    }

}
