<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class GameContent
{

    public $data = array(), $level_min, $level_max, $name, $order = 'asc',
        $slot, $sort, $target, $type, $vocation;
    private $count = 0, $DrawingCollection, $gettextextras, $xls;
    private $sync_files = array(
        'monsters' => array(
            'filename' => 'community_allmonsters_AU2017.xlsx',
            'sheet_index' => 0,
            'start_row' => 2
        ),
        'armours' => array(
            'filename' => 'allitems_community_AU2017.xls',
            'sheet_index' => 0,
            'start_row' => 3
        ),
        'weapons' => array(
            'filename' => 'allitems_community_AU2017.xls',
            'sheet_index' => 1,
            'start_row' => 3
        ),
        'spells' => array(
            'filename' => 'community_spell_listing_AU2017.xls',
            'sheet_index' => 0,
            'start_row' => 0
        ),
        'skills_warrior' => array(
            'filename' => 'community_skills_listing_AU2017.xls',
            'sheet_index' => 1,
            'start_row' => 2
        ),
        'skills_wizard' => array(
            'filename' => 'community_skills_listing_AU2017.xls',
            'sheet_index' => 0,
            'start_row' => 2
        ),
        'pets' => array(
            'filename' => 'community_pets_AU2017.xlsx',
            'sheet_index' => 0,
            'start_row' => 3
        ),
        'food' => array(
            'filename' => 'community_potions&food_AU2017.xlsx',
            'sheet_index' => 0,
            'start_row' => 0
        )
    );
    private $sync_cell_types = array(
        'monsters' => array(
            1 => array(
                'name' => 'name',
                'filter' => 'parse_name'
            ),
            2 => array(
                'name' => 'hp',
                'filter' => 'intval'
            ),
            3 => array(
                'name' => 'exp',
                'filter' => 'intval'
            ),
            4 => array(
                'name' => 'gold',
                'filter' => 'intval'
            ),
            5 => array(
                'name' => 'attack_hit',
                'filter' => 'parse_attack'
            ),
            6 => array(
                'name' => 'attack_fire',
                'filter' => 'parse_attack'
            ),
            7 => array(
                'name' => 'attack_ice',
                'filter' => 'parse_attack'
            ),
            8 => array(
                'name' => 'attack_energy',
                'filter' => 'parse_attack'
            ),
            9 => array(
                'name' => 'attack_soul',
                'filter' => 'parse_attack'
            ),
            10 => array(
                'name' => 'sens_hit',
                'filter' => 'parse_sens'
            ),
            11 => array(
                'name' => 'sens_fire',
                'filter' => 'parse_sens'
            ),
            12 => array(
                'name' => 'sens_ice',
                'filter' => 'parse_sens'
            ),
            13 => array(
                'name' => 'sens_energy',
                'filter' => 'parse_sens'
            ),
            14 => array(
                'name' => 'sens_soul',
                'filter' => 'parse_sens'
            ),
            15 => array(
                'name' => 'walkspeed',
                'filter' => 'parse_walkspeed'
            ),
            16 => array(
                'name' => 'islands',
                'filter' => 'parse_island'
            ),
            17 => array(
                'name' => 'islands',
                'filter' => 'parse_island'
            ),
            18 => array(
                'name' => 'islands',
                'filter' => 'parse_island'
            ),
            19 => array(
                'name' => 'islands',
                'filter' => 'parse_island'
            ),
            20 => array(
                'name' => 'islands',
                'filter' => 'parse_island'
            ),
            21 => array(
                'name' => 'loot',
                'filter' => 'parse_name'
            ),
            22 => array(
                'name' => 'loot',
                'filter' => 'parse_name'
            ),
            23 => array(
                'name' => 'loot',
                'filter' => 'parse_name'
            ),
            24 => array(
                'name' => 'loot',
                'filter' => 'parse_name'
            ),
            25 => array(
                'name' => 'loot',
                'filter' => 'parse_name'
            ),
            26 => array(
                'name' => 'loot',
                'filter' => 'parse_name'
            ),
            27 => array(
                'name' => 'loot',
                'filter' => 'parse_name'
            ),
            28 => array(
                'name' => 'loot',
                'filter' => 'parse_name'
            ),
            29 => array(
                'name' => 'loot',
                'filter' => 'parse_name'
            ),
            30 => array(
                'name' => 'icon',
                'filter' => 'parse_icon'
            )
        ),
        'armours' => array(
            1 => array(
                'name' => array('name', 'upgraded'),
                'filter' => 'parse_armour_name'
            ),
            2 => array(
                'name' => 'slot',
                'filter' => 'parse_slot'
            ),
            3 => array(
                'name' => 'level',
                'filter' => 'intval'
            ),
            4 => array(
                'name' => 'hit',
                'filter' => 'intval'
            ),
            5 => array(
                'name' => 'fire',
                'filter' => 'intval'
            ),
            6 => array(
                'name' => 'ice',
                'filter' => 'intval'
            ),
            7 => array(
                'name' => 'energy',
                'filter' => 'intval'
            ),
            8 => array(
                'name' => 'soul',
                'filter' => 'intval'
            ),
            11 => 1,
            12 => 2,
            13 => 3,
            14 => 4,
            15 => 5,
            16 => 6,
            17 => 7,
            18 => 8
        ),
        'weapons' => array(
            1 => array(
                'name' => 'name',
                'filter' => 'parse_name'
            ),
            3 => array(
                'name' => 'level',
                'filter' => 'intval'
            ),
            4 => array(
                'name' => array('hit_min', 'hit_max'),
                'filter' => 'parse_weapon_damage'
            ),
            5 => array(
                'name' => array('fire_min', 'fire_max'),
                'filter' => 'parse_weapon_damage'
            ),
            6 => array(
                'name' => array('ice_min', 'ice_max'),
                'filter' => 'parse_weapon_damage'
            ),
            7 => array(
                'name' => array('energy_min', 'energy_max'),
                'filter' => 'parse_weapon_damage'
            ),
            8 => array(
                'name' => array('soul_min', 'soul_max'),
                'filter' => 'parse_weapon_damage'
            ),
            11 => 1,
            13 => 3,
            14 => array(
                'name' => 'mana',
                'filter' => 'intval'
            ),
            15 => 4,
            16 => 5,
            17 => 6,
            18 => 7,
            19 => 8
        ),
        'skills_warrior' => array(
            1 => array(
                'name' => 'char_level',
                'filter' => 'intval'
            ),
            2 => array(
                'name' => 'name',
                'filter' => 'parse_skill_name'
            ),
            3 => array(
                'name' => 'requirements',
                'filter' => 'parse_skill_requirements'
            ),
            4 => array(
                'name' => 'skill_level',
                'filter' => 'intval'
            ),
            5 => array(
                'name' => 'loads',
                'filter' => 'parse_skill_loads'
            ),
            6 => array(
                'name' => 'duration',
                'filter' => 'parse_skill_duration'
            ),
            7 => array(
                'name' => 'cooldown',
                'filter' => 'intval'
            ),
            8 => array(
                'name' => 'arena',
                'filter' => 'parse_skill_arena'
            ),
            10 => array(
                'name' => 'description',
                'filter' => 'parse_description'
            )
        ),
        'skills_wizard' => 'skills_warrior',
        'pets' => array(
            1 => array(
                'name' => 'name',
                'filter' => 'trim'
            ),
            2 => array(
                'name' => 'type',
                'filter' => 'parse_pet_type'
            ),
            3 => array(
                'name' => array('dmg_hit', 'dmg_fire', 'dmg_ice', 'dmg_energy', 'dmg_soul'),
                'filter' => 'parse_pet_dmg'
            ),
            4 => array(
                'name' => array('prot_hit', 'prot_fire', 'prot_ice', 'prot_energy',
                    'prot_soul'),
                'filter' => 'parse_pet_dmg'
            ),
            5 => array(
                'name' => array('weak_hit', 'weak_fire', 'weak_ice', 'weak_energy',
                    'weak_soul'),
                'filter' => 'parse_pet_dmg'
            ),
            6 => array(
                'name' => 'level',
                'filter' => 'intval'
            ),
            7 => array(
                'name' => 'ep',
                'filter' => 'intval'
            ),
            8 => array(
                'name' => 'hp',
                'filter' => 'intval'
            ),
            9 => array(
                'name' => 'attack',
                'filter' => 'intval'
            ),
            10 => array(
                'name' => 'defense',
                'filter' => 'intval'
            ),
            11 => array(
                'name' => 'owner_level',
                'filter' => 'intval'
            ),
            13 => 1,
            14 => 2,
            15 => 3,
            16 => 4,
            17 => 5,
            18 => 6,
            19 => 7,
            20 => 8,
            21 => 9,
            22 => 10,
            23 => 11,
            26 => 1,
            27 => 2,
            28 => 3,
            29 => 4,
            30 => 5,
            31 => 6,
            32 => 7,
            33 => 8,
            34 => 9,
            35 => 10,
            36 => 11,
            39 => 1,
            40 => 2,
            41 => 3,
            42 => 4,
            43 => 5,
            44 => 6,
            45 => 7,
            46 => 8,
            47 => 9,
            48 => 10,
            49 => 11
        ),
        'food' => [],
        'spells' => []
    );

    private function add_weapon($data)
    {
        $data = array_map(array($GLOBALS['db'], 'quote'), $data);
        $GLOBALS['db']->query('INSERT INTO `game_content_weapons` (`name`,
                    `vocation`,
                    `level`,
                    `mana`,
                    `energy_min`,
                    `energy_max`,
                    `fire_min`,
                    `fire_max`,
                    `hit_min`,
                    `hit_max`,
                    `soul_min`,
                    `soul_max`,
                    `ice_min`,
                    `ice_max`,
                    `icon`,
                    `is_upgraded`) VALUES (
                    ' . $data['name'] . ',
                    ' . $data['vocation'] . ',
                    ' . $data['level'] . ',
                    ' . $data['mana'] . ',
                    ' . $data['energy_min'] . ',
                    ' . $data['energy_max'] . ',
                    ' . $data['fire_min'] . ',
                    ' . $data['fire_max'] . ',
                    ' . $data['hit_min'] . ',
                    ' . $data['hit_max'] . ',
                    ' . $data['soul_min'] . ',
                    ' . $data['soul_max'] . ',
                    ' . $data['ice_min'] . ',
                    ' . $data['ice_max'] . ',
                    ' . $data['icon'] . ',
                    ' . $data['is_upgraded'] . '
                )');
    }

    private function add_spell($data)
    {
        $data = array_map(array($GLOBALS['db'], 'quote'), $data);
        $GLOBALS['db']->query('INSERT INTO `game_content_spells` (`name`, `vocation`, `level`,'
            . ' `type`, `modifies`, `description`, `target`, `amount`, `heal`, `dmg`, `mana`, `duration`,'
            . ' `cooldown`, `icon`) VALUES (' . $data['name'] . ', ' . $data['vocation']
            . ', ' . $data['level'] . ', ' . $data['type'] . ', ' . $data['modifies']
            . ', ' . $data['description'] . ', ' . $data['target'] . ', ' . $data['amount']
            . ', ' . $data['heal'] . ', ' . $data['dmg'] . ', ' . $data['mana'] . ', '
            . $data['duration'] . ', ' . $data['cooldown'] . ', ' . $data['icon'] . ')');
    }

    private function add_food($data)
    {
        if (!isset($data['hp'])) {
            $data['hp'] = 0;
        }
        if (!isset($data['mp'])) {
            $data['mp'] = 0;
        }
        if (!isset($data['duration'])) {
            $data['duration'] = null;
        }
        if (!isset($data['description'])) {
            $data['description'] = null;
        }
        $data = array_map(array($GLOBALS['db'], 'quote'), $data);
        $GLOBALS['db']->query('INSERT INTO game_content_food'
            . ' (name, hp, mp, vocation, duration, description, icon) VALUES'
            . ' (' . $data['name'] . ', ' . $data['hp'] . ', '
            . $data['mp'] . ', ' . $data['vocation'] . ', '
            . $data['duration'] . ', ' . $data['description'] . ', ' . $data['icon'] . ')');
    }

    private function add_armour($data)
    {
        if (($data['slot'] != 'amulet' && $data['slot'] != 'ring') || $GLOBALS['db']->query('SELECT COUNT(*) FROM `game_content_armours`'
                . ' WHERE `name` = ' . $GLOBALS['db']->quote($data['name']))
                ->fetch_row() == array(0 => '0')) {
            $data = array_map(array($GLOBALS['db'], 'quote'), $data);
            $GLOBALS['db']->query('INSERT INTO `game_content_armours` (`name`,
                    `level`,
                    `vocation`,
                    `slot`,
                    `energy`,
                    `fire`,
                    `hit`,
                    `soul`,
                    `ice`,
                    `icon`,
                    `upgraded`) VALUES (
                    ' . $data['name'] . ',
                    ' . $data['level'] . ',
                    ' . $data['vocation'] . ',
                    ' . $data['slot'] . ',
                    ' . $data['energy'] . ',
                    ' . $data['fire'] . ',
                    ' . $data['hit'] . ',
                    ' . $data['soul'] . ',
                    ' . $data['ice'] . ',
                    ' . $data['icon'] . ',
                    ' . $data['upgraded'] . '
                )');
        }
    }

    private function add_skill($data)
    {
        $data = array_map(array($GLOBALS['db'], 'quote'), $data);
        $GLOBALS['db']->query('INSERT INTO `game_content_skills` (`name`, `vocation`, `skill_level`,'
            . ' `char_level`, `requirements`, `loads`, `duration`, `cooldown`,'
            . ' `arena`, `description`) VALUES (' . $data['name'] . ', ' . $data['vocation'] . ', '
            . $data['skill_level'] . ', ' . $data['char_level'] . ', '
            . $data['requirements'] . ', ' . $data['loads'] . ', '
            . $data['duration'] . ', ' . $data['cooldown'] . ', ' . $data['arena']
            . ', ' . $data['description'] . ')');
    }

    public function set_vocation($vocation)
    {
        if ($vocation == 'warrior' || $vocation == 'wizard') {
            $this->vocation = $vocation;
        }
    }

    public function set_slot($slot)
    {
        if (in_array($slot,
                array('head', 'torso', 'legs', 'shield', 'amulet', 'ring'))
            || $slot === null) {
            $this->slot = $slot;
        } else {
            log_error('invalid slot \'' . $slot . '\'');
        }
    }

    /**
     * for armours and weapons
     */
    public function set_type($type)
    {
        foreach ($type as $element) {
            if (in_array($element, array('energy', 'fire', 'hit', 'soul', 'ice'))) {
                $this->type[] = $element;
            }
        }
    }

    /**
     * for spells, obviously
     */
    public function set_spell_type($type)
    {
        if (in_array($type,
            array('heal', 'buff', 'debuff', 'hit', 'fire', 'ice', 'energy',
                'soul', 'weapon'))) {
            $this->type = $type;
        }
    }

    /**
     * for skills ;)
     * @deprecated
     */
    public function set_skill_type($type)
    {
        if ($type == 'offensive' || $type == 'defensive') {
            $this->type = $type;
        }
    }

    public function fetch($cat)
    {
        // search criterias
        foreach (array('level_min', 'level_max', 'slot', 'vocation', 'type',
                     'name', 'target') as $name) {
            if ($this->$name === null) {
                continue;
            }
            if (isset($sql)) {
                $sql .= ' AND ';
            } else {
                $sql = ' WHERE ';
            }
            switch ($name) {
                case 'vocation':
                    if ($cat === 'weapons') {
                        if ($this->$name === 'warrior') {
                            $sql .= 'mana IS NULL';
                        } else {
                            $sql .= 'mana IS NOT NULL';
                        }
                    } else {
                        $sql .= '(vocation = \'' . $this->$name . '\' OR vocation IS NULL)';
                    }
                    break;
                case 'slot':
                    $sql .= 'slot = \'' . $this->$name . '\'';
                    break;
                case 'level_min':
                    $sql .= 'level >= ' . $this->$name;
                    break;
                case 'level_max':
                    $sql .= 'level <= ' . $this->$name;
                    break;
                case 'type':
                    if ($cat == 'spells') {
                        $sql .= 'FIND_IN_SET(\'' . $this->$name . '\',`type`)>0';
                        break;
                    } elseif ($cat == 'skills') {
                        $sql .= 'type = \'' . $this->$name . '\'';
                        break;
                    }
                    $count = count($this->$name);
                    if ($count > 1) {
                        $sql .= ' (';
                    }
                    foreach ($this->$name as $i => $element) {
                        if ($i != 0) {
                            $sql .= ' OR ';
                        }
                        if ($cat == 'armours') {
                            $sql .= $element . ' > 0';
                        } elseif ($cat == 'weapons') {
                            $sql .= '(' . $element . '_min IS NOT NULL OR ' . $element . '_max IS NOT NULL)';
                        }
                    }
                    if ($count > 1) {
                        $sql .= ')';
                    }
                    break;
                case 'name':
                    $sql .= 'name LIKE \'%' . $GLOBALS['db']->real_escape_string($this->name) . '%\'';
                    break;
                case 'target':
                    $sql .= 'target = \'' . $this->$name . '\'';
                    break;
            }
        }

        if ($this->sort === null) {
            switch ($cat) {
                case 'monsters':
                    $sort = 'exp ' . $this->order;
                    break;
                case 'skills':
                    $sort = 'char_level ' . $this->order . ', `name` '
                        . $this->order . ', `skill_level` ' . $this->order;
                    break;
                case 'food':
                    break;
                default:
                    $sort = 'level ' . $this->order;
            }
        } elseif ($this->sort !== 'level' && $cat === 'weapons') {
            $sort = $this->sort . '_max ' . $this->order . ', ' . $this->sort . '_min ' . $this->order;
        } else {
            $sort = $this->sort . ' ' . $this->order;
        }

        $sort = isset($sort) ? $sort . ', name' : 'name';
        if ($cat == 'weapons' || $cat == 'armours') {
            $sort .= ', ';
            if ($cat == 'weapons') {
                $sort .= 'is_upgraded';
            }
            if ($cat == 'armours') {
                $sort .= 'upgraded';
            }
        }

        $sql = $GLOBALS['db']->query('SELECT * FROM game_content_' . $cat . (isset($sql)
                ? $sql : '')
            . (isset($sort) ? ' ORDER BY ' . $sort . ', name' : ' ORDER BY name'));
        while ($array = $sql->fetch_assoc()) {
            $this->data[$this->count] = $array;
            if ($cat == 'spells') {
                $this->data[$this->count]['type'] = explode(',',
                    $this->data[$this->count]['type']);
            }
            if ($cat == 'monsters') {
                $this->data[$this->count]['loot'] = array();
                $loot = $GLOBALS['db']->query('SELECT `item_name`, (SELECT COUNT(*)'
                    . ' FROM `game_content_weapons` WHERE `name` = `item_name`) as `weapon`,'
                    . ' (SELECT COUNT(*) FROM `game_content_armours` WHERE `name` = `item_name`) as `armour`'
                    . ' FROM `game_content_loot` WHERE `monster_id` = '
                    . $GLOBALS['db']->quote($array['id']));
                while ($item = $loot->fetch_assoc()) {
                    $this->data[$this->count]['loot'][] = $item;
                }
                $this->data[$this->count]['islands'] = array();
                $islands = $GLOBALS['db']->query('SELECT `island`'
                    . ' FROM `game_content_monsters_islands` WHERE `monster_id` = '
                    . $GLOBALS['db']->quote($array['id']));
                while ($island = $islands->fetch_assoc()) {
                    $this->data[$this->count]['islands'][] = $island['island'];
                }
            } elseif ($cat == 'weapons' || $cat == 'armours') {
                $this->data[$this->count]['source'] = array();
                $source = $GLOBALS['db']->query('SELECT `game_content_monsters`.`name`'
                    . ' FROM `game_content_monsters`, `game_content_loot`'
                    . ' WHERE `game_content_loot`.`item_name` = '
                    . $GLOBALS['db']->quote($array['name'])
                    . ' AND `game_content_loot`.`monster_id` = `game_content_monsters`.`id`');
                while ($monster = $source->fetch_row()) {
                    $this->data[$this->count]['source'][] = $monster[0];
                }
            } elseif ($cat === 'spells') {
                if ($this->data[$this->count]['heal'] !== null) {
                    $heal_avg = explode('-', $this->data[$this->count]['heal']);
                    $heal_avg = array_sum($heal_avg) / count($heal_avg);
                    $this->data[$this->count]['hps'] = round($heal_avg / $this->data[$this->count]['cooldown'],
                        2);
                    //$this->data[$this->count]['hpmp'] = round($amount_avg / $this->data[$this->count]['mana'], 2);
                }
                // @todo: add DMG/s values
            }
            ++$this->count;
        }
    }

    public function set_name($name)
    {
        $this->name = $name;
    }

    public function set_sort($sort)
    {
        if (in_array($sort,
            array('level', 'energy', 'fire', 'hit', 'soul', 'ice'))) {
            $this->sort = $sort;
        }
    }

    public function set_order($order)
    {
        if ($order == 'asc' || $order == 'desc') {
            $this->order = $order;
        }
    }

    public function set_level($level_min, $level_max)
    {
        $level_min = ctype_digit($level_min) ? intval($level_min) : null;
        $level_max = ctype_digit($level_max) ? intval($level_max) : null;
        if ($level_max !== null && $level_min !== null && $level_max <= $level_min) {
            $level_max = $level_min;
        }
        $this->level_min = $level_min;
        $this->level_max = $level_max;
    }

    public function set_target($target)
    {
        if (in_array($target, array('self', 'guild', 'AoE', 'single', 'closest'))) {
            $this->target = $target;
        }
    }

    private function add_monster($data)
    {
        $data = array_map(array($GLOBALS['db'], 'quote'), $data);
        $GLOBALS['db']->query('INSERT INTO `game_content_monsters` (`name`, `icon`, `exp`,'
            . ' `attack_energy`, `attack_fire`, `attack_hit`, `attack_soul`, `attack_ice`,'
            . ' `sens_energy`, `sens_fire`, `sens_hit`, `sens_soul`,'
            . ' `sens_ice`, `hp`, `gold`, `walkspeed`, `skill`, `spell`, `spell_energy`,'
            . ' `spell_fire`, `spell_hit`, `spell_soul`, `spell_ice`) VALUES ('
            . $data['name'] . ', ' . $data['icon'] . ', ' . $data['exp'] . ', '
            . $data['attack_energy'] . ', ' . $data['attack_fire'] . ', ' . $data['attack_hit'] . ', '
            . $data['attack_soul'] . ', ' . $data['attack_ice'] . ', ' . $data['sens_energy'] . ', '
            . $data['sens_fire'] . ', ' . $data['sens_hit'] . ', ' . $data['sens_soul'] . ', '
            . $data['sens_ice'] . ', ' . $data['hp'] . ', ' . $data['gold'] . ', '
            . $data['walkspeed'] . ', ' . $data['skill'] . ', ' . $data['spell'] . ', '
            . $data['spell_energy'] . ', ' . $data['spell_fire'] . ', ' . $data['spell_hit'] . ', '
            . $data['spell_soul'] . ', ' . $data['spell_ice'] . ')');
        if ($GLOBALS['db']->errno) {
            return;
        }
        $monster_id = $GLOBALS['db']->insert_id;
        foreach ($data['loot'] as $loot) {
            if ($loot === 'NULL') {
                continue;
            }
            $GLOBALS['db']->query('INSERT INTO `game_content_loot` (`monster_id`, `item_name`)'
                . ' VALUES (' . $monster_id . ', ' . $loot . ')');
        }
        foreach ($data['islands'] as $island) {
            if ($island === 'NULL') {
                continue;
            }
            $GLOBALS['db']->query('INSERT INTO `game_content_monsters_islands` (`monster_id`, `island`)'
                . ' VALUES (' . $monster_id . ', ' . $island . ')');
        }
    }

    private function add_pet($data)
    {
        $GLOBALS['db']->insert('game_content_pets',
            array(
                'name' => $data['name'],
                'type' => $data['type'],
                'dmg_hit' => $data['dmg_hit'],
                'dmg_fire' => $data['dmg_fire'],
                'dmg_ice' => $data['dmg_ice'],
                'dmg_energy' => $data['dmg_energy'],
                'dmg_soul' => $data['dmg_soul'],
                'prot_hit' => $data['prot_hit'],
                'prot_fire' => $data['prot_fire'],
                'prot_ice' => $data['prot_ice'],
                'prot_energy' => $data['prot_energy'],
                'prot_soul' => $data['prot_soul'],
                'weak_hit' => $data['weak_hit'],
                'weak_fire' => $data['weak_fire'],
                'weak_ice' => $data['weak_ice'],
                'weak_energy' => $data['weak_energy'],
                'weak_soul' => $data['weak_soul'],
                'icon' => $data['icon']
            ));
        $GLOBALS['db']->insert_multi('game_content_pets_stats',
            array(
                'name',
                'level',
                'ep',
                'hp',
                'attack',
                'defense',
                'owner_level'
            ),
            array_map(function ($array) use ($data) {
                $array['name'] = $data['name'];
                return $array;
            }, $data['stats']));
    }

    public function sync($cat)
    {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/../sync_' . $cat . '.lock')) {
            return false;
        }
        touch($_SERVER['DOCUMENT_ROOT'] . '/../sync_' . $cat . '.lock');
        set_time_limit(600);
        switch ($cat) {
            case 'skills_warrior':
                $GLOBALS['db']->query('DELETE FROM `game_content_skills` WHERE `vocation` = \'warrior\'');
                break;
            case 'skills_wizard':
                $GLOBALS['db']->query('DELETE FROM `game_content_skills` WHERE `vocation` = \'wizard\'');
                break;
            default:
                // all other cats
                $GLOBALS['db']->query('TRUNCATE TABLE `game_content_' . $cat . '`');
        }
        // truncate additional tables
        if ($cat === 'monsters') {
            $GLOBALS['db']->query('TRUNCATE TABLE `game_content_loot`');
            $GLOBALS['db']->query('TRUNCATE TABLE game_content_monsters_islands');
            Filesystem::emptydir($_SERVER['DOCUMENT_ROOT'] . '/images/icons/classic/monsters');
        } elseif ($cat == 'pets') {
            $GLOBALS['db']->query('TRUNCATE TABLE `game_content_pets_stats`');
        }
        $this->xls = IOFactory::load(__DIR__ . '/../gamecontent_xls/'
            . $this->sync_files[$cat]['filename']);
        $this->xls->setActiveSheetIndex($this->sync_files[$cat]['sheet_index']);
        $rows = $this->xls->getActiveSheet()->getHighestRow();
        $cols = Coordinate::columnIndexFromString($this->xls->getActiveSheet()->getHighestColumn());
        for ($row = $this->sync_files[$cat]['start_row']; $row <= $rows; ++$row) {
            if ($cat == 'spells' || $cat == 'food') {
                $cell = $this->xls->getActiveSheet()->getCellByColumnAndRow(1,
                    $row);
                if ($cell->getStyle()->getFont()->getBold()) {
                    // reading subcat and skipping rows with bold text
                    foreach ($this->xls->getActiveSheet()->getMergeCells() as $cells) {
                        if ($cell->isInRange($cells)) {
                            list($start, $end) = explode(':', $cells);
                            if (Coordinate::columnIndexFromString(Coordinate::coordinateFromString($end)[0]) - Coordinate::columnIndexFromString(Coordinate::coordinateFromString($start)[0]) + 1 != 1) {
                                if ($cat == 'spells') {
                                    // skipping subcat
                                    continue;
                                } elseif ($cat == 'food') {
                                    $value = $cell->getValue();
                                    if (stripos($value, 'warrior') !== false) {
                                        $vocation = 'warrior';
                                    } elseif (stripos($value, 'wizard') !== false) {
                                        $vocation = 'wizard';
                                    } else {
                                        $vocation = null;
                                    }
                                }
                            }
                            break;
                        }
                    }
                    if ($cat != 'spells' && $cat != 'food') {
                        continue;
                    }
                }
            }
            $i = 0;
            if ($cat !== 'pets' || $row === $this->sync_files[$cat]['start_row']
                || !isset($pets_stats_loop[$i])) {
                $data[$i] = array();
                if ($cat === 'monsters') {
                    $data[$i]['loot'] = $data[$i]['islands'] = array();
                } elseif ($cat === 'pets') {
                    $data[$i]['stats'] = array();
                    $pets_stats_loop[$i] = 1;
                }
            }
            for ($col = 1; $col <= $cols; ++$col) {
                $cell = $this->xls->getActiveSheet()->getCellByColumnAndRow($col,
                    $row);
                if ($cell->getStyle()->getFont()->getBold()) {
                    if ($cat == 'spells') {
                        $this->sync_cell_types[$cat][$col] = $this->spell_getFilterByColTitle($cell->getValue());
                    } elseif ($cat == 'food') {
                        $this->sync_cell_types[$cat][$col] = $this->food_getFilterByColTitle($cell->getValue());
                    }
                    continue;
                }
                $sync_cell_type = is_array($this->sync_cell_types[$cat]) ? $this->sync_cell_types[$cat]
                    : $this->sync_cell_types[$this->sync_cell_types[$cat]];
                if ($cat == 'weapons') {
                    if ($col == 1) {
                        $data[$i]['vocation'] = 'warrior';
                    } elseif ($col == array_search(1,
                            $this->sync_cell_types['weapons'])) {
                        $data[$i]['vocation'] = 'wizard';
                    }
                }
                if (!isset($sync_cell_type[$col])) {
                    if (($cat == 'weapons' || $cat == 'armours') && $col == 10) {
                        // 10th column separate warrior and wizard weapons/armours
                        $data[++$i] = array();
                    } elseif ($cat === 'pets') {
                        ++$i;
                        if (!isset($pets_stats_loop[$i])) {
                            $data[$i] = array();
                            $pets_stats_loop[$i] = 1;
                        }
                        while (!isset($sync_cell_type[$col + 1]) && $col < $cols) {
                            ++$col;
                        }
                    }
                    // skipping unused col
                    continue;
                }

                $sync_cell_type = is_array($sync_cell_type[$col]) ? $sync_cell_type[$col]
                    : $sync_cell_type[$sync_cell_type[$col]];
                if ($cat === 'pets') {
                    if ($pets_stats_loop[$i] !== 1 && $sync_cell_type['name'] !== 'level'
                        && $sync_cell_type['name'] !== 'ep' && $sync_cell_type['name']
                        !== 'hp' && $sync_cell_type['name'] !== 'attack' && $sync_cell_type['name']
                        !== 'defense' && $sync_cell_type['name'] !== 'owner_level') {
                        // only parsing stats
                        continue;
                    }
                    if ($sync_cell_type['name'] == 'name') {
                        if (empty($cell->getValue())) {
                            for ($k = 1; $k <= max(array_keys($this->sync_cell_types['pets'])); ++$k) {
                                if (!isset($this->sync_cell_types['pets'][$k]) || $this->sync_cell_types['pets'][$k]
                                    === $this->sync_cell_types['pets'][1]) {
                                    break;
                                }
                            }
                            $col += ($k - 1);
                            continue;
                        }

                        $pets_stats_count[$i] = 1;
                        foreach ($this->xls->getActiveSheet()->getMergeCells() as $cells) {
                            if ($cell->isInRange($cells)) {
                                list($start, $end) = explode(':', $cells);
                                $pets_stats_count[$i] = Coordinate::coordinateFromString($end)[1] - Coordinate::coordinateFromString($start)[1] + 1;
                                break;
                            }
                        }
                    }
                }
                if (in_array($sync_cell_type['filter'],
                        ['parse_description', 'parse_food_duration']) && $this->gettextextras
                    === null) {
                    $this->gettextextras = new GettextExtraMessages('gamecontent_' . $cat);
                }
                if ($sync_cell_type['filter'] === 'parse_icon') {
                    $data[$i][$sync_cell_type['name']] = $this->parse_icon($cat,
                        $row, $col);
                } elseif (is_array($sync_cell_type['name'])) {
                    // $this->parse_cell() should return an array
                    $temp = $this->parse_cell($cell, $sync_cell_type['filter']);
                    foreach ($sync_cell_type['name'] as $temp_i => $temp_name) {
                        $data[$i][$temp_name] = $temp[$temp_i];
                    }
                } elseif ($sync_cell_type['name'] === 'loot' || $sync_cell_type['name']
                    === 'islands') {
                    $data[$i][$sync_cell_type['name']][] = $this
                        ->parse_cell($cell, $sync_cell_type['filter']);
                } elseif ($cat === 'pets' && ($sync_cell_type['name'] === 'level'
                        || $sync_cell_type['name'] === 'ep' || $sync_cell_type['name']
                        === 'hp' || $sync_cell_type['name'] === 'attack' || $sync_cell_type['name']
                        === 'defense' || $sync_cell_type['name'] === 'owner_level')) {
                    if ($sync_cell_type['name'] == 'owner_level' && empty($cell->getValue())) {
                        // if owner level is not specified, using owner level from previous record (previous pet level)
                        if ($pets_stats_loop[$i] == 1) {
                            $data[$i]['stats'][$pets_stats_loop[$i]][$sync_cell_type['name']]
                                = 1;
                        } else {
                            $data[$i]['stats'][$pets_stats_loop[$i]][$sync_cell_type['name']]
                                = $data[$i]['stats'][$pets_stats_loop[$i] - 1][$sync_cell_type['name']];
                        }
                    } else {
                        $data[$i]['stats'][$pets_stats_loop[$i]][$sync_cell_type['name']]
                            = $this
                            ->parse_cell($cell, $sync_cell_type['filter']);
                    }
                } else {
                    $data[$i][$sync_cell_type['name']] = $this
                        ->parse_cell($cell, $sync_cell_type['filter']);
                }
                // kinda workaround?
                // is_upgraded is not "registered" at the top of the script
                if ($cat === 'weapons' && $sync_cell_type['name'] === 'name') {
                    $data[$i]['is_upgraded'] = (strpos($cell->getValue(), '(+)')
                        === false) ? 0 : 1;
                } elseif ($cat === 'armours' && $sync_cell_type['name'] === 'slot') {
                    if ($data[$i][$sync_cell_type['name']] === 'ring' || $data[$i][$sync_cell_type['name']]
                        === 'amulet') {
                        $data[$i]['vocation'] = null;
                    } elseif ($col == 2) {
                        $data[$i]['vocation'] = 'warrior';
                    } elseif ($col == array_search(2,
                            $this->sync_cell_types['armours'])) {
                        $data[$i]['vocation'] = 'wizard';
                    }
                } elseif ($cat == 'food') {
                    $data[$i]['vocation'] = $vocation;
                }
            }
            for ($j = 0; $j <= $i; ++$j) {
                if (empty($data[$j]['name'])) {
                    continue;
                }
                if ($cat == 'pets' && $pets_stats_loop[$j] < $pets_stats_count[$j]) {
                    ++$pets_stats_loop[$j];
                    continue;
                }
                if ($cat == 'monsters') {
                    list($data[$j]['skill'], $data[$j]['spell'], $data[$j]['spell_hit'],
                        $data[$j]['spell_fire'], $data[$j]['spell_ice'], $data[$j]['spell_energy'],
                        $data[$j]['spell_soul']) = $this->get_monster_extra_data($data[$j]['name']);
                    $data[$j]['loot'] = array_unique($data[$j]['loot']);
                } elseif ($cat == 'spells') {
                    if (isset($data[$j]['amount'])) {
                        // formatting amount
                        if ($data[$j]['modifies'] == 'attack' || $data[$j]['modifies']
                            == 'damage') {
                            $data[$j]['amount'] = $this->convert_spell_amount_modifier2pct($data[$j]['amount']);
                        } else {
                            $data[$j]['amount'] .= '%';
                        }
                    } else {
                        $data[$j]['amount'] = null;
                    }
                    if (isset($data[$j]['heal_min']) && isset($data[$j]['heal_max'])) {
                        // formatting heal
                        if ($data[$j]['heal_min'] == $data[$j]['heal_max']) {
                            $data[$j]['heal'] = $data[$j]['heal_min'];
                        } else {
                            $data[$j]['heal'] = $data[$j]['heal_min'] . '-' . $data[$j]['heal_max'];
                        }
                        unset($data[$j]['heal_min'], $data[$j]['heal_max']);
                    } else {
                        $data[$j]['heal'] = null;
                    }
                    if (isset($data[$j]['dmg_min']) && isset($data[$j]['dmg_max'])) {
                        // formatting dmg
                        if ($data[$j]['dmg_min'] == $data[$j]['dmg_max']) {
                            $data[$j]['dmg'] = $data[$j]['dmg_min'];
                        } else {
                            $data[$j]['dmg'] = [$data[$j]['dmg_min'], $data[$j]['dmg_max']];
                            sort($data[$j]['dmg']);
                            $data[$j]['dmg'] = implode('-', $data[$j]['dmg']);
                        }
                        if (in_array('weapon', explode(',', $data[$j]['type']))) {
                            $data[$j]['dmg'] .= '%';
                        }
                        unset($data[$j]['dmg_min'], $data[$j]['dmg_max']);
                    } else {
                        $data[$j]['dmg'] = null;
                    }
                    if (!isset($data[$j]['duration'])) {
                        $data[$j]['duration'] = null;
                    }
                } elseif ($cat == 'skills_warrior' || $cat == 'skills_wizard') {
                    $data[$j]['vocation'] = $this->sync_files[$cat]['sheet_index']
                    === 0 ? 'wizard' : 'warrior';
                } elseif ($cat == 'weapons' && !isset($data[$j]['mana'])) {
                    $data[$j]['mana'] = null;
                }
                if ($cat !== 'monsters') {
                    if ($cat == 'food') {
                        $data[$j]['icon'] = TochkiSuParser::get_food_icon($data[$j]['name']);
                    } elseif ($cat == 'armours') {
                        $stats = [];
                        foreach (DMG_ELEMENTS as $element) {
                            $stats[$element] = $data[$j][$element];
                        }
                        $data[$j]['icon'] = TibiameHexatComParser::get_icon($data[$j]['name'],
                            $cat, $data[$j]['vocation'],
                            $data[$j]['slot'], $stats);
                    } elseif (empty($data[$j]['icon'])) {
                        $data[$j]['icon'] = TibiameHexatComParser::get_icon($data[$j]['name'],
                            $cat, $data[$j]['vocation'] ?? null);
                    }
                }
                if (!empty($data[$j]['icon'])) {
                    $data[$j]['icon'] = Filesystem::get_doc_root_path($data[$j]['icon']);
                }

                switch ($cat) {
                    case 'monsters':
                        if (!isset($data[$j]['walkspeed'])) {
                            echo $data[$j]['name'], '<br/>';
                        }
                        $this->add_monster($data[$j]);
                        break;
                    case 'weapons':
                        $this->add_weapon($data[$j]);
                        break;
                    case 'armours':
                        $this->add_armour($data[$j]);
                        break;
                    case 'spells':
                        $this->add_spell($data[$j]);
                        break;
                    case 'skills_warrior':
                    case 'skills_wizard':
                        $this->add_skill($data[$j]);
                        break;
                    case 'pets':
                        $this->add_pet($data[$j]);
                        unset($pets_stats_loop[$j], $pets_stats_count[$j]);
                        break;
                    case 'food':
                        $this->add_food($data[$j]);
                        break;
                }
            }
        }
        unlink($_SERVER['DOCUMENT_ROOT'] . '/../sync_' . $cat . '.lock');
        return true;
    }

    private function parse_cell($cell, $filter)
    {
        $value = $cell->getValue();
        if ($filter == 'intval') {
            if ($cell->getStyle()->getNumberFormat()->getFormatCode() == NumberFormat::FORMAT_PERCENTAGE) {
                $value *= 100;
            }
        } else {
            $value = trim($value);
            if ($filter === 'trim') {
                return $value;
            }
            // list of functions that might return 0 rather than null
            if (empty($value) && !in_array($filter,
                    array('parse_attack', 'parse_sens', 'parse_walkspeed',
                        'parse_skill_arena', 'parse_food_value'))) {
                return null;
            }
            if ($filter === 'ucfirst') {
                $value = strtolower($value);
            }
        }

        if (strpos($filter, 'parse_') === 0) {
            if ($filter == 'parse_spell_amount') {
                return $this->$filter($cell);
            }
            return $this->$filter($value);
        }
        return $filter($value);
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_attack($value)
    {
        return ($value ? 1 : 0);
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_sens($value)
    {
        switch ($value) {
            case '+':
                return 1;
            case '++':
                return 2;
            default:
                return 0;
        }
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_weapon_damage($value)
    {
        $value = explode('-', $value);
        if (!isset($value[1])) {
            $value[1] = null;
        }
        return $value;
    }

    private function parse_food_value($value)
    {
        return $value == 'full' ? '100%' : $value;
    }

    /**
     * gets additional monster info from obsolete xls
     * @param string $monster_name
     * @return array an array with 8 elements (spell, skill, spell_hit, spell_fire, spell_ice, spell_energy, spell_soul)
     */
    private function get_monster_extra_data($monster_name)
    {
        $xls = IOFactory::createReader(IOFactory::identify(__DIR__ . '/../gamecontent_xls/Monsters.xls'));
        $xls->setReadDataOnly(true);
        $xls = $xls->load(__DIR__ . '/../gamecontent_xls/Monsters.xls');
        $xls->setActiveSheetIndex(0);
        $monster_name = strtolower($monster_name);
        $rows = $xls->getActiveSheet()->getHighestRow();
        for ($row = 2; $row <= $rows; ++$row) {
            if (strtolower(trim($xls->getActiveSheet()->getCellByColumnAndRow(1,
                    $row)->getValue())) == $monster_name) {
                $spell = trim($xls->getActiveSheet()->getCellByColumnAndRow(14,
                    $row)->getValue());
                if (empty($spell)) {
                    $spell = null;
                }
                $skill = trim($xls->getActiveSheet()->getCellByColumnAndRow(15,
                    $row)->getValue());
                if (empty($skill)) {
                    $skill = null;
                }
                list($spell_hit, $spell_fire, $spell_ice, $spell_energy, $spell_soul)
                    = TochkiSuParser::get_monster_spell_elements($monster_name);
                return array($skill, $spell, $spell_hit, $spell_fire, $spell_ice,
                    $spell_energy, $spell_soul);
            }
        }
        return array(null, null, 0, 0, 0, 0, 0);
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_slot($slot_name)
    {
        $slot_name = strtolower($slot_name);
        return ($slot_name == 'helmet') ? 'head' : (($slot_name == 'armour') ? 'torso'
            : $slot_name);
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_name($name)
    {
        $name = trim(str_replace('(+)', '', $name));
        if (empty($name)) {
            return null;
        }
        return ucfirst(strtolower($name));
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_vocation($vocation)
    {
        $vocation = strtolower($vocation);
        switch ($vocation) {
            case 'wizard':
            case 'warrior':
                return $vocation;
            case 'all':
                return null;
            default:
                log_error('couldn\'t parse vocation \'' . $vocation . '\'');
        }
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_spell_type(string $type1): string
    {
        $type1 = array_map('trim', explode('&', strtolower($type1)));
        $type2 = [];
        foreach ($type1 as $type) {
            if (strpos($type, 'weapon') !== false) {
                $type2[] = 'weapon';
            } elseif ($type == 'lightning') {
                $type2[] = 'energy';
            } elseif ($type == 'damage') {
                $type2[] = 'weapon';
            } elseif (in_array($type,
                array('heal', 'buff', 'debuff', 'hit', 'fire', 'ice',
                    'soul'))) {
                $type2[] = $type;
            } else {
                log_error('unexpected spell type \'' . $type . '\'');
            }
        }
        return implode(',', $type2);
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_spell_modifies($modifies)
    {
        if ($modifies == '-') {
            return null;
        }
        $modifies = strtolower($modifies);
        if (!in_array($modifies,
            array('attack', 'damage', 'visibility', 'hp', 'vulnerability'))) {
            log_error('unexpected $modifies value \'' . $modifies . '\'');
        }
        return $modifies;
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_spell_target($target)
    {
        switch ($target) {
            case 'Single':
                return 'self';
            case 'Guild (in range)':
                return 'guild';
            case 'Monsters in area':
            case 'Multiple monsters':
                return 'AoE';
            case 'Single monster':
                return 'single';
            default:
                log_error('unknown spell target \'' . $target . '\'');
        }
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_spell_amount($cell)
    {
        $amount = $cell->getValue();
        if (empty($amount) || $amount == '-') {
            return null;
        }
        if ($cell->getStyle()->getNumberFormat()->getFormatCode() == NumberFormat::FORMAT_PERCENTAGE) {
            $amount *= 100;
        }
        return str_replace([' to ', '%'], ['-', ''], $amount);
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_spell_duration($duration)
    {
        return str_replace(' to ', '-', $duration);
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_walkspeed($walkspeed)
    {
        switch ($walkspeed) {
            case '---':
                return -3;
            case '--':
                return -2;
            case '-':
                return -1;
            case 0:
                return 0;
            case '+':
                return 1;
            case '++':
                return 2;
            case '+++':
                return 3;
            default:
                log_error('could not parse walkspeed \'' . $walkspeed . '\'');
        }
    }

    private function parse_skill_name($name)
    {
        return ucwords(strtolower($name));
    }

    private function parse_skill_requirements($req)
    {
        if ($req === 'None') {
            return null;
        }
        return serialize(array_map('trim', explode(';', $req)));
    }

    private function parse_skill_loads($loads)
    {
        $loads = intval($loads);
        return $loads === 0 ? null : $loads;
    }

    private function parse_description($desc)
    {
        $desc = ucfirst($desc);
        if (!in_array(substr($desc, -1), ['.', '!'])) {
            $desc .= '.';
        }
        return $this->gettextextras->add($desc);
    }

    private function parse_skill_arena($arena)
    {
        return isset($arena[0]) ? 0 : 1;
    }

    private function parse_food_duration($string)
    {
        return $this->gettextextras->add($string);
    }

    private function parse_skill_duration($duration)
    {
        $duration = intval($duration);
        return $duration === 0 ? null : $duration;
    }

    private function convert_spell_amount_modifier2pct($modifier_string)
    {
        if (strpos($modifier_string, '-') === false) {
            return abs($modifier_string - 100) . '%';
        }
        $modifier_array = explode('-', $modifier_string);
        $pct_array = array();
        foreach ($modifier_array as $modifier) {
            $pct_array[] = abs($modifier - 100);
        }
        sort($pct_array);
        return $pct_array[0] . '-' . $pct_array[1] . '%';
    }

    /**
     * used in GameContent::parse_cell()
     */
    private function parse_island($island)
    {
        $search = str_replace('st.nivalis', 'st. nivalis', strtolower($island));
        $search = array_search($search, array_map('strtolower', ISLANDS));
        if ($search !== false) {
            return ucfirst(ISLANDS[$search]);
        }
        log_error('could not parse island name \'' . $island . '\'');
        return null;
    }

    private function parse_armour_name($name)
    {
        $pattern = '\( *(\+) *(hit|fire|ice|light|soul|ep)?\)';
        if (preg_match("/$pattern/", $name, $matches)) {
            $upgraded = isset($matches[2]) ? ($matches[2] === 'light' ? 'energy'
                : $matches[2]) : '1';
            $name = trim(preg_replace("/$pattern(.*)/", '\3', $name));
        } else {
            $upgraded = '0';
        }
        return array(ucfirst(strtolower($name)), $upgraded);
    }

    private function parse_pet_type($type)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'avg':
            case 'dmg':
            case 'prot':
                return $type;
            default:
                log_error('count not parse pet type \'' . $type . '\'');
        }
    }

    private function parse_pet_dmg($dmg)
    {
        $return = array(
            'hit' => 0,
            'fire' => 0,
            'ice' => 0,
            'energy' => 0,
            'soul' => 0
        );
        $dmg = preg_split('/[ ,\/]+/', strtolower($dmg), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($dmg as $element) {
            if ($element === 'lightning') {
                $element = 'energy';
            }
            if (!isset($return[$element])) {
                log_error('could not parse pet damage type \'' . $element . '\'');
                continue;
            }
            $return[$element] = 1;
        }
        return array_values($return);
    }

    /**
     * Get an array with list of items.
     * @return array list of items sorted alphabetically.
     */
    public static function get_armors_list($vocation = null)
    {
        $sql = 'SELECT id, name, upgraded, slot, vocation FROM game_content_armours';
        if ($vocation !== null) {
            $sql .= ' WHERE vocation = \'' . $vocation . '\' OR vocation IS NULL';
        }
        $sql .= ' ORDER BY name ASC';
        $sql = $GLOBALS['db']->query($sql);
        $items = [];
        while ($assoc = $sql->fetch_assoc()) {
            if ($vocation === null) {
                $items[$assoc['vocation'] === null ? 'null' : $assoc['vocation']][$assoc['slot']][$assoc['id']]
                    = array(
                    'name' => $assoc['name'],
                    'upgraded' => $assoc['upgraded']
                );
            } else {
                $items[$assoc['slot']][$assoc['id']] = array(
                    'name' => $assoc['name'],
                    'upgraded' => $assoc['upgraded']
                );
            }
        }
        return $vocation === null ? array_map(function ($vocation_set) {
            return array_map(function ($items_set) {
                return array_map(function ($item) {
                    return self::format_upgrade($item['upgraded']) . htmlspecialchars($item['name']);
                }, $items_set);
            }, $vocation_set);
        }, $items) : array_map(function ($items_set) {
            return array_map(function ($item) {
                return self::format_upgrade($item['upgraded']) . htmlspecialchars($item['name']);
            }, $items_set);
        }, $items);
    }

    public static function format_upgrade($upgrade)
    {
        switch ($upgrade) {
            case 'hit':
            case 'fire':
            case 'ice':
            case 'energy':
            case 'soul':
                return '(+ ' . $upgrade . ')&nbsp;';
            case 1:
                return '(+)&nbsp;';
            default:
                return null;
        }
    }

    private function parse_icon($cat, $row, $col)
    {
        if (!isset($this->DrawingCollection)) {
            $this->DrawingCollection = $this->xls->getActiveSheet()->getDrawingCollection();
        }
        $coord = Coordinate::stringFromColumnIndex($col) . $row;
        $icons = [];
        foreach ($this->DrawingCollection as $drawing) {
            if ($drawing->getCoordinates() !== $coord) {
                continue;
            }
            $icons[] = is_a($drawing, 'MemoryDrawing') ? $drawing->getImageResource()
                : Images::imagecreate($drawing->getPath());
            if (count($icons) == 2) {
                if (imagesx($icons[0]) * imagesy($icons[0]) < imagesx($icons[1]) * imagesy($icons[1])) {
                    $icons = array_reverse($icons);
                }
                break;
            }
        }
        if (empty($icons)) {
            return null;
        }
        // workaround for weird blue Fouldrake background
        for ($x = 0; $x < imagesx($icons[0]); ++$x) {
            for ($y = 0; $y < imagesy($icons[0]); ++$y) {
                if (imagecolorat($icons[0], $x, $y) === 2135575225) {
                    imagesetpixel($icons[0], $x, $y,
                        imagecolorallocate($icons[0], 255, 255, 255));
                }
            }
        }
        imagecolortransparent($icons[0],
            imagecolorallocate($icons[0], 255, 255, 255));
        if (isset($icons[1])) {
            imagecopymerge($icons[0], $icons[1],
                imagesx($icons[0]) - imagesx($icons[1]), 0, 0, 0,
                imagesx($icons[1]), imagesy($icons[1]), 100);
        }
        $filename = uniqid() . '.png';
        if (!imagepng($icons[0],
            $_SERVER['DOCUMENT_ROOT'] . '/images/icons/classic/' . $cat . DIRECTORY_SEPARATOR . $filename,
            9)) {
            log_error('could not save icon to ' . $_SERVER['DOCUMENT_ROOT'] . '/images/icons/classic/' . $cat . DIRECTORY_SEPARATOR . $filename);
            return null;
        }
        foreach ($icons as $im) {
            imagedestroy($im);
        }
        unset($icons);
        return '/images/icons/classic/' . $cat . DIRECTORY_SEPARATOR . $filename;
    }

    private function spell_getFilterByColTitle($colTitle)
    {
        switch ($colTitle) {
            case 'Spellname':
                return ['name' => 'name', 'filter' => 'parse_name'];
            case 'Class':
                return ['name' => 'vocation', 'filter' => 'parse_vocation'];
            case 'Difficulty':
                return ['name' => 'level', 'filter' => 'intval'];
            case 'Type':
                return ['name' => 'type', 'filter' => 'parse_spell_type'];
            case 'Modifies':
                return ['name' => 'modifies', 'filter' => 'parse_spell_modifies'];
            case 'Range':
                return ['name' => 'target', 'filter' => 'parse_spell_target'];
            case 'Amount':
                return ['name' => 'amount', 'filter' => 'parse_spell_amount'];
            case 'Duration':
                return ['name' => 'duration', 'filter' => 'intval'];
            case 'Mana':
                return ['name' => 'mana', 'filter' => 'intval'];
            case 'Cooldown':
                return ['name' => 'cooldown', 'filter' => 'intval'];
            case 'Effect':
            case '':
                return ['name' => 'description', 'filter' => 'parse_description'];
            case 'Cost':
            case 'Teacher':
                return null;
            case 'Heal (min)':
                return ['name' => 'heal_min', 'filter' => 'intval'];
            case 'Heal (max)':
                return ['name' => 'heal_max', 'filter' => 'intval'];
            case 'Duration':
                return ['name' => 'duration', 'filter' => 'parse_spell_duration'];
            case 'Dmg (min)':
                return ['name' => 'dmg_min', 'filter' => 'intval'];
            case 'Dmg (max)':
                return ['name' => 'dmg_max', 'filter' => 'intval'];
            default:
                log_error('unknown spells column title: \'' . $colTitle . '\'');
                return null;
        }
    }

    private function food_getFilterByColTitle($colTitle)
    {
        if (stripos($colTitle, 'name') !== false) {
            return ['name' => 'name', 'filter' => 'trim'];
        } elseif (stripos($colTitle, 'hit') !== false) {
            return ['name' => 'hp', 'filter' => 'parse_food_value'];
        } elseif (stripos($colTitle, 'mana') !== false) {
            return ['name' => 'mp', 'filter' => 'parse_food_value'];
        } elseif (stripos($colTitle, 'duration') !== false) {
            return ['name' => 'duration', 'filter' => 'parse_food_duration'];
        } elseif (stripos($colTitle, 'effect') !== false) {
            return ['name' => 'description', 'filter' => 'parse_description'];
        }
    }

    /*
     * Removes text in brackets, replaces all non-alphabetic characters except "_", trims spaces and returns lowercase string.
     */
    public static function sanitize_filename($item_name)
    {
        return strtolower(str_replace(' ', '_',
            trim(preg_replace(['/\(([^()]|(?R))*\)/', '/[^a-z_]/i'],
                '', $item_name))));
    }

    public static function get_icon_path($icons_dir_rel_path, $client_type = null)
    {
        if ($client_type == null) {
            $client_type = $_SESSION['icons_client_type'];
        }
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . ICONS_DIR . '/' . $client_type . $icons_dir_rel_path)) {
            return ICONS_DIR . '/' . $client_type . $icons_dir_rel_path;
        }
        if ($client_type != 'classic') {
            return get_icon($icons_dir_rel_path, 'classic');
        }
        return '/images/item_no_icon.png';
    }

}
