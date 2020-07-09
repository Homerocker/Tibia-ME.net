<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2013, Tibia-ME.net
 * @version 2.4
 */
class TochkiSuParser {

    /**
     * @deprecated since 2.4.1
     */
    public function sync() {

        $dom = new DOMDocument;
        $cats = array(
            'weapons',
            'helmets',
            'armours',
            'legs',
            'amulets',
            'rings',
            'shields',
            'monsters'
        );
        foreach ($cats as $cat) {
            switch ($cat) {
                case 'weapons':
                    $sources = array(
                        'http://tochki.su/tibiame/weapons-warrior/pc-en',
                        'http://tochki.su/tibiame/weapons-wizard/pc-en'
                    );
                    break;
                default:
                    $sources = array('http://tochki.su/tibiame/' . $cat . '/pc-en');
            }
            foreach ($sources as $source) {
                $dom->loadHTMLFile($source);
                $dom->preserveWhiteSpace = false;
                $tables = $dom->getElementsByTagName('table');
                for ($t = 0; $t < $tables->length; ++$t) {
                    $rows = $tables->item($t)->getElementsByTagName('tr');
                    for ($r = 0; $r < $rows->length; ++$r) {
                        $cols = $rows->item($r)->getElementsByTagName('td');
                        switch ($cat) {
                            case 'weapons':
                                $item = array(
                                    'damage' => array(),
                                    'mana' => null
                                );
                                break;
                            case 'helmets':
                            case 'armours':
                            case 'legs':
                            case 'shields':
                                $item = array(
                                    'slot' => $this->get_slot_by_type($cat),
                                    'vocation' => $t ? 'warrior' : 'wizard'
                                );
                                break;
                            case 'amulets':
                            case 'rings':
                                $item = array(
                                    'slot' => $this->get_slot_by_type($cat),
                                    'vocation' => null
                                );
                                break;
                            case 'monsters':
                                $item = array(
                                    'attack' => array(
                                        'energy' => 0,
                                        'fire' => 0,
                                        'hit' => 0,
                                        'soul' => 0,
                                        'ice' => 0
                                    ),
                                    'sens' => array(
                                        'energy' => 0,
                                        'fire' => 0,
                                        'hit' => 0,
                                        'soul' => 0,
                                        'ice' => 0
                                    ),
                                    'skill' => null,
                                    'spell' => array(
                                        'name' => null,
                                        'energy' => 0,
                                        'fire' => 0,
                                        'hit' => 0,
                                        'soul' => 0,
                                        'ice' => 0
                                    )
                                );
                                break;
                        }
                        for ($c = 0; $c < $cols->length; ++$c) {
                            switch ($c) {
                                case 0:
                                    $item['icon'] = $cols->item($c)->getElementsByTagName('img')->item(0)->getAttribute('src');
                                    break;
                                case 1:
                                    $item['name'] = $cols->item($c)->nodeValue;
                                    switch ($cat) {
                                        case 'monsters':
                                            $this->parse_monster($cols->item($c)->getElementsByTagName('a')
                                                            ->item(0)->getAttribute('href'), $item);
                                            break;
                                    }
                                    break;
                                case 2:
                                    switch ($cat) {
                                        case 'monsters':
                                            $item['exp'] = intval($cols->item($c)->nodeValue);
                                            break;
                                        default:
                                            $item['level'] = intval($cols->item($c)->nodeValue);
                                    }
                                    break;
                                case 3:
                                    switch ($cat) {
                                        case 'monsters':
                                            // dropped items here, skipping for now
                                            break;
                                        case 'weapons':
                                        case 'helmets':
                                        case 'armours':
                                        case 'legs':
                                        case 'shields':
                                        case 'amulets':
                                        case 'rings':
                                            $imgs = $cols->item($c)->getElementsByTagName('img');
                                            $values = array_values(array_filter(explode(' ', trim($cols->item($c)->nodeValue))));
                                            for ($i = 0; $i < $imgs->length; ++$i) {
                                                $img_damage_type = $this->img_damage_type($imgs->item($i)->getAttribute('src'));
                                                if ($cat == 'weapons') {
                                                    list($damage_min, $damage_max)
                                                            = (strpos($values[$i], '-')
                                                            !== false) ? explode('-', $values[$i])
                                                                : array(null, $values[$i]);
                                                    $item['damage'][$img_damage_type . '_min']
                                                            = $damage_min;
                                                    $item['damage'][$img_damage_type . '_max']
                                                            = $damage_max;
                                                } else {
                                                    $item['defense'][$img_damage_type]
                                                            = intval($values[$i]);
                                                }
                                            }
                                            break;
                                    }
                                    break;
                                case 4:
                                    switch ($cat) {
                                        case 'monsters':
                                            $item['gold'] = intval($cols->item($c)->nodeValue);
                                            break;
                                        default:
                                            $item['mana'] = intval($cols->item($c)->nodeValue);
                                    }
                                    break;
                            }
                        }
                        switch ($cat) {
                            case 'weapons':
                                $this->add_weapon($item['name'], $item['icon'], $item['level'], $item['damage'], $item['mana']);
                                break;
                            case 'helmets':
                            case 'armours':
                            case 'legs':
                            case 'shields':
                            case 'amulets':
                            case 'rings':
                                $this->add_armour($item['name'], $item['icon'], $item['level'], $item['defense'], $item['slot'], $item['vocation']);
                                break;
                            case 'monsters':
                                $this->add_monster($item);
                                break;
                        }
                    }
                }
            }
        }
    }

    private static function img_damage_type($url) {
        return pathinfo($url, PATHINFO_FILENAME);
    }

    /**
     * @deprecated since 2.4.1
     */
    private function get_slot_by_type($type) {
        return str_replace(array(
            'helmets',
            'armours',
            'legs',
            'shields',
            'amulets',
            'rings'
                ), array(
            'head',
            'chest',
            'legs',
            'shield',
            'amulet',
            'ring'
                ), $type);
    }

    /**
     * @deprecated since 2.4.1
     */
    private function parse_monster($url, &$item) {
        $dom = new DOMDocument;
        $dom->loadHTMLFile($url);
        $dom->preserveWhiteSpace = false;
        $tables = $dom->getElementsByTagName('table')->item(0)->getElementsByTagName('table');
        $table1 = $tables->item(1)->getElementsByTagName('tr');
        $item['hp'] = $table1->item(0)->getElementsByTagName('td')->item(1)->nodeValue;
        $attack = $table1->item(1)->getElementsByTagName('td')->item(1)->getElementsByTagName('img');
        for ($i = 0; $i < $attack->length; ++$i) {
            ++$item['attack'][$this->img_damage_type($attack->item($i)->getAttribute('src'))];
        }
        $sens = $table1->item(2)->getElementsByTagName('td')->item(1)->getElementsByTagName('img');
        for ($i = 0; $i < $sens->length; ++$i) {
            ++$item['sens'][$this->img_damage_type($sens->item($i)->getAttribute('src'))];
        }
        $trs = $dom->getElementsByTagName('table')->item(0)->getElementsByTagName('table')
                        ->item(2)->getElementsByTagName('tr');
        for ($i = 0; $i < $trs->length; ++$i) {
            $tds = $trs->item($i)->getElementsByTagName('td');
            switch ($tds->item(0)->nodeValue) {
                case 'Speed':
                    $item['speed'] = strtolower($tds->item(1)->nodeValue);
                    if (!in_array($item['speed'], array('very slow', 'slow', 'normal', 'fast', 'very fast'))) {
                        $item['speed'] = null;
                    }
                    break;
                case 'Spell':
                    $td = $tds->item(1);
                    $item['spell']['name'] = empty($td->nodeValue) ? null : trim($td->nodeValue);
                    $imgs = $td->getElementsByTagName('img');
                    for ($j = 0; $j < $imgs->length; ++$j) {
                        $item['spell'][$this->img_damage_type($imgs->item($j)->getAttribute('src'))]
                                = 1;
                    }
                    break;
                case 'Skill':
                    $item['skill'] = $tds->item(1)->nodeValue;
                    break;
            }
        }
    }

    /**
     * gets mana usage by weapon name
     * @param string $weapon_name
     * @return null|int amount of mana on success, otherwise null
     * @deprecated since 2.5.4
     */
    public static function get_weapon_mana($weapon_name) {
        $url = 'http://tochki.su/tibiame/e/' . self::format_name($weapon_name)[0] . '/pc-en';
        if (!remote_file_exists($url)) {
            return null;
        }
        $dom = new DOMDocument;
        $dom->loadHTMLFile($url);
        $dom->preserveWhiteSpace = false;
        $mana = $dom->getElementsByTagName('table');
        if (!$mana->length) {
            return null;
        }
        $mana = $mana->item(0)->getElementsByTagName('tr')->item(1)->getElementsByTagName('td')->item(0)->getElementsByTagName('img');
        $length = $mana->length;
        for ($i = 0; $i <= $length; ++$i) {
            if ($mana->item($i)->getAttribute('title') == 'Mana') {
                // using preg_replace() coz trim() didn't work (some weird spaces)
                return (int) preg_replace('/[^0-9]/', '', $mana->item($i)->nextSibling->nodeValue);
            }
        }
        return null;
    }

    /**
     * @deprecated
     * fetches icon by name
     * @param string $name
     * @param boolean $is_spell try searching for spell icon first
     * @return null|string icon url on success, otherwise null
     */
    public static function get_icon($name, $cat, $vocation = null) {
        $local_path = GameContent::sanitize_filename($name);
        switch ($cat) {
            case 'skills_warrior':
            case 'skills_wizard':
                $local_path = 'skills/' . $local_path;
                break;
            default:
                $local_path = $cat . '/' . $local_path;
        }
        $local_path = $_SERVER['DOCUMENT_ROOT'] . '/images/icons/classic/' . $local_path;
        $name_formatted = self::format_name($name, $cat == 'spells', $vocation);
        $dom = new DOMDocumentX;
        foreach ($name_formatted as $name) {
            $dom->loadHTMLFile('http://tochki.su/tibiame/e/' . $name);
            $icon = $dom->getElementsByTagName('table');
            if ($icon->length) {
                break;
            }
        }
        if (!$icon->length) {
            return ($cat == 'spells' && isset($vocation)) ? self::get_icon($name, $cat)
                        : null;
        }
        $icon = $icon->item(0)->getElementsByTagName('img')->item(0);
        if ($icon->getAttribute('class') != 'corn') {
            return null;
        }
        return $icon->getAttribute('src');
    }

    /**
     * formats name to be used in url
     * @param unit name
     * @param boolean $is_spell format name as spell if there's duplicate
     * @return array
     */
    private static function format_name($name, $is_spell = false, $vocation = null) {
        if (strpos($name, ' ') !== false) {
            $explode = explode(' ', $name);
            $name = null;
            foreach ($explode as $key => $value) {
                if ($value === '(+)' || $value == '(mtx)') {
                    continue;
                }
                if ($key != 0) {
                    $value = ucfirst($value);
                }
                if ($name === null) {
                    $name = $value;
                } else {
                    $name .= '_' . $value;
                }
            }
        }
        //$name = preg_replace('/[^a-zA-Z0-9_\']/', '', $name);
        if ($is_spell) {
            $name .= ' (Spell)';
        }
        $name = [$name];
        if (strpos($name[0], '\'') !== false) {
            $name[] = str_replace('\'', '', $name[0]);
            $name[] = str_replace('\'s', '', $name[0]);
        }
        if (strpos($name[0], '-') !== false) {
            $name[] = implode('-', array_map('ucfirst', explode('-', $name[0])));
        }
        if (strpos($name[0], '_') !== false) {
            $name[] = ucfirst(strtolower(str_replace('_', '', $name[0])));
        }
        if ($vocation !== null) {
            foreach ($name as $value) {
                $name[] = $value . '_(' . ucfirst($vocation) . ')';
            }
        }
        return array_filter(array_unique($name));
    }

    public static function get_monster_spell_elements($monster_name) {
        $elements = array(0, 0, 0, 0, 0);
        $formatted_name = self::format_name($monster_name);
        $dom = new DOMDocumentX;
        foreach ($formatted_name as $name) {
            $dom->loadHTMLFile('http://tochki.su/tibiame/e/' . $name . '/pc-en');
            $trs = $dom->getElementsByTagName('table');
            if ($trs->length) {
                break;
            }
        }
        if (!$trs->length) {
            return $elements;
        }
        $trs = $trs->item(3)->getElementsByTagName('tr');
        $length = $trs->length;
        for ($i = 0; $i < $length; ++$i) {
            $tds = $trs->item($i)->getElementsByTagName('td');
            if ($tds->item(0)->nodeValue == 'Spell') {
                $imgs = $tds->item(1)->getElementsByTagName('img');
                $length = $imgs->length;
                for ($i = 0; $i < $length; ++$i) {
                    $img = $imgs->item($i);
                    $elements_indexes = array(
                        'hit' => 0,
                        'fire' => 1,
                        'ice' => 2,
                        'energy' => 3,
                        'soul' => 4
                    );
                    $elements[$elements_indexes[self::img_damage_type($img->getAttribute('src'))]]
                            = 1;
                }
                return $elements;
            }
        }
        return $elements;
    }
    
    public static function get_food_icon($name) {
        $name = GameContent::sanitize_filename($name);
        $local_path = '/images/icons/classic/food/' . $name . '.png';
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $local_path)) {
            return $local_path;
        }
        $remote_path = 'http://tochki.su/tibiame/img/game/' . $name . '.png';
        if (!@exif_imagetype($remote_path)) {
            return null;
        }
        if (!Filesystem::mkdir(pathinfo($_SERVER['DOCUMENT_ROOT'] . $local_path,
                                PATHINFO_DIRNAME))) {
            return $remote_path;
        }
        if (copy($remote_path, $_SERVER['DOCUMENT_ROOT'] . $local_path)) {
            return Images::compress($_SERVER['DOCUMENT_ROOT'] . $local_path, true);
        }
        log_error('could not copy ' . $remote_path . ' to ' . $_SERVER['DOCUMENT_ROOT'] . $local_path);
        return null;
    }

}
