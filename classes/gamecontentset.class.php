<?php

class GameContentSet {

    public $head, $shield, $legs, $amulet, $torso, $ring;

    /**
     * items for comparison
     * to save resources and make calculations faster we will only compare "best" items for specified defense level and stats
     * @var array $filtered_items
     */
    public $filtered_items = array(
        'head' => array(),
        'shield' => array(),
        'legs' => array(),
        'amulet' => array(),
        'torso' => array(),
        'ring' => array()
    );

    /**
     * list of ignored items
     * @var array $ignored_items
     */
    public $ignored_items = [];

    /**
     * used to store set history to recalculate set stats when one of its items is ignored
     * @var array $set_history
     */
    public $set_history = [];
    public $selected_stats = ['hit'];
    public $vocation = 'warrior';
    public $def_level;
    public $incl_upgraded = false;
    public $evenly = false;

    public function __construct($set = null) {
        if ($set !== null) {
            $properties = array_map(function ($property) {
                return $property->getName();
            },
                    (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC));

            foreach ($set as $key => $value) {
                if (in_array($key, $properties)) {
                    if (in_array($key,
                                    array('head', 'shield', 'legs', 'amulet', 'torso',
                                'ring'))) {
                        $this->$key = new GameContentItem($value);
                        if ($this->$key->id === null) {
                            $this->$key = null;
                        }
                    } else {
                        $this->$key = $key === 'ignored_items' ? array_map(function ($item) {
                                    return new GameContentItem($item);
                                }, $value) : $value;
                    }
                }
            }
        }
    }

    /**
     * Sanitizes and sets calculator parameters.
     * @param string $vocation
     * @param int|null $def_level
     * @param array $selected_stats
     * @param boolean $incl_upgraded
     * @param boolean $evenly
     */
    public function set_params($vocation, $def_level, $selected_stats,
            $incl_upgraded = true, $evenly = false) {
        $this->vocation = in_array($vocation, ['warrior', 'wizard']) ? $vocation
                    : 'warrior';
        $this->def_level = ctype_digit((string) $def_level) ? (int) $def_level : $GLOBALS['db']->query('SELECT MAX(level) FROM game_content_armours')->fetch_row()[0];
        $this->selected_stats = array_intersect((array) $selected_stats,
                ['hit', 'fire', 'ice', 'energy', 'soul']);
        if (empty($this->selected_stats)) {
            $this->selected_stats = ['hit'];
        }
        $this->incl_upgraded = (bool) $incl_upgraded;
        $this->evenly = (bool) $evenly;
    }

    private function get_items() {
        if (empty($this->selected_stats)) {
            $this->selected_stats = ['hit'];
        }
        // @todo add vocation-level index (???)
        $query = 'SELECT * FROM game_content_armours WHERE (vocation = \'' . $this->vocation . '\' OR vocation IS NULL)';
        if ($this->def_level) {
            $query .= ' AND level <= ' . $this->def_level;
        }
        if (!$this->incl_upgraded) {
            $query .= ' AND upgraded = \'0\'';
        }
        foreach ($this->ignored_items as $item) {
            $query .= ' AND id != ' . $item->id;
        }
        $query .= ' AND (' . implode(' > 0 OR ', $this->selected_stats) . ' > 0)';
        $query = $GLOBALS['db']->query($query);
        // creating valid array
        $items = (new ReflectionClass($this))->getDefaultProperties()['filtered_items'];
        while ($item = $query->fetch_assoc()) {
            if ($item['icon'] != null) {
                $item['icon'] = $item['icon'];
            }
            $items[$item['slot']][] = $item;
        }
        return $items;
    }

    public function get_bis() {
        $this->set_history = array();
        // @todo should somehow combine different items with same stats and calculate them as one item to save resources
        $this->filter_items($this->get_items());

        foreach ($this->filtered_items['head'] as $item_head) {
            foreach ($this->filtered_items['shield'] as $item_shield) {
                foreach ($this->filtered_items['legs'] as $item_legs) {
                    foreach ($this->filtered_items['amulet'] as $item_amulet) {
                        foreach ($this->filtered_items['torso'] as $item_torso) {
                            foreach ($this->filtered_items['ring'] as $item_ring) {
                                $set_curr = array(
                                    'head' => $item_head,
                                    'shield' => $item_shield,
                                    'legs' => $item_legs,
                                    'amulet' => $item_amulet,
                                    'torso' => $item_torso,
                                    'ring' => $item_ring
                                );
                                if (!isset($set_bis)) {
                                    $set_bis = $set_curr;
                                    $stats_bis = array_fill_keys($this->selected_stats,
                                            0);
                                    foreach ($set_curr as $item) {
                                        if ($item === null) {
                                            continue;
                                        }
                                        foreach ($this->selected_stats as $stat) {
                                            $stats_bis[$stat] = $this->defense_calculate($stats_bis[$stat],
                                                    $item[$stat]);
                                        }
                                    }
                                    $stats_bis = $this->evenly ? min($stats_bis)
                                                : array_sum($stats_bis);
                                    continue;
                                }
                                $stats_curr = array_fill_keys($this->selected_stats,
                                        0);
                                foreach ($set_curr as $item) {
                                    if ($item === null) {
                                        continue;
                                    }
                                    foreach ($this->selected_stats as $stat) {
                                        $stats_curr[$stat] = $this->defense_calculate($stats_curr[$stat],
                                                $item[$stat]);
                                    }
                                }
                                $stats_curr = $this->evenly ? min($stats_curr) : array_sum($stats_curr);
                                if ($stats_curr > $stats_bis) {
                                    $set_bis = $set_curr;
                                    $stats_bis = $stats_curr;
                                }
                            }
                        }
                    }
                }
            }
        }
        $set_bis = isset($set_bis) ? array_map(function ($item) {
                    return new GameContentItem($item);
                }, $set_bis) : [];
        $set_bis['ignored_items'] = $this->ignored_items;
        $set_bis['filtered_items'] = $this->filtered_items;
        $this->__construct($set_bis);
    }

    private function set_item_stats_sum($item, $stats_pri) {
        $item['sum'] = 0;
        foreach ($stats_pri as $stat) {
            $item['sum'] += $item[$stat];
        }
        return $item;
    }

    private function filter_items($items) {
        $this->filtered_items = (new ReflectionClass($this))->getDefaultProperties()['filtered_items'];
        $stats_sec = array_diff(array('hit', 'fire', 'ice', 'energy', 'soul'),
                $this->selected_stats);
        $stats_pri_count = count($this->selected_stats);
        foreach ($items as $slot => $items_list) {
            foreach ($items_list as $item) {
                if ($this->def_level && $item['level'] > $this->def_level) {
                    // level too high
                    continue;
                }
                if (empty($this->filtered_items[$slot])) {
                    // no alternate items parsed yet
                    $item = $this->set_item_stats_sum($item,
                            $this->selected_stats);
                    $this->filtered_items[$slot][$item['id']] = $item;
                    continue;
                }
                foreach ($this->filtered_items[$slot] as $id => $filtered_item) {
                    $i = 0;
                    foreach ($this->selected_stats as $stat_pri) {
                        if ($item[$stat_pri] > $filtered_item[$stat_pri]) {
                            ++$i;
                        }
                    }
                    if ($i === $stats_pri_count) {
                        unset($this->filtered_items[$slot][$id]);
                    } elseif ($i >= 1) {
                        $k = 0;
                        foreach ($this->selected_stats as $stat_pri) {
                            if ($item[$stat_pri] >= $filtered_item[$stat_pri]) {
                                ++$k;
                            }
                        }
                        if ($k === $stats_pri_count) {
                            unset($this->filtered_items[$slot][$id]);
                            $this->filtered_items[$slot][$item['id']] = $this->set_item_stats_sum($item,
                                    $this->selected_stats);
                            continue 2;
                        }
                    } else {
                        $i = $j = 0;
                        foreach ($this->selected_stats as $stat_pri) {
                            if ($item[$stat_pri] === $filtered_item[$stat_pri]) {
                                ++$i;
                            } elseif ($item[$stat_pri] < $filtered_item[$stat_pri]) {
                                ++$j;
                            }
                        }
                        if ($i === $stats_pri_count) {
                            $item_stats_sum_sec = $filtered_item_stats_sum_sec = 0;
                            foreach ($stats_sec as $stat_sec) {
                                $item_stats_sum_sec += $item[$stat_sec];
                                $filtered_item_stats_sum_sec += $filtered_item_stats_sum_sec[$stat_sec];
                            }
                            if ($item_stats_sum_sec > $filtered_item_stats_sum_sec) {
                                unset($this->filtered_items[$slot][$id]);
                            }
                        } else {
                            continue 2;
                        }
                    }
                }
                $this->filtered_items[$slot][$item['id']] = $this->set_item_stats_sum($item,
                        $this->selected_stats);
            }
        }
        if (count($this->filtered_items[$slot]) > 1) {
            // deleting possible null values
            $this->filtered_items[$slot] = array_filter($this->filtered_items[$slot]);
        }
        $j = 16;
        while (count($this->filtered_items['head']) * count($this->filtered_items['shield'])
        * count($this->filtered_items['legs']) * count($this->filtered_items['amulet'])
        * count($this->filtered_items['torso']) * count($this->filtered_items['ring'])
        > 700000) {
            --$j;
            // @todo we could try to remove items from one of categories (slots)
            // rather than removing items from all of cats
            foreach ($this->filtered_items as $slot => $filtered_items) {
                usort($filtered_items,
                        function ($a, $b) {
                    return $b['sum'] - $a['sum'];
                });
                foreach ($filtered_items as $i => $item) {
                    if ($i > $j && $item['sum'] !== $filtered_items[$i - 1]['sum']) {
                        $this->filtered_items[$slot] = array_slice($filtered_items,
                                0, $i);
                        continue 2;
                    }
                }
                $this->filtered_items[$slot] = $filtered_items;
            }
        }
        foreach (['head', 'torso', 'legs', 'amulet', 'shield', 'ring'] as $slot) {
            if (empty($this->filtered_items[$slot])) {
                $this->filtered_items[$slot][0] = null;
            }
        }
    }

    private function defense_calculate($base, $add) {
        return $base + ceil($add - $base * $add / 100);
    }

    public function get_stats($stat = null) {
        $stats = array(
            'hit' => 0,
            'fire' => 0,
            'ice' => 0,
            'energy' => 0,
            'soul' => 0
        );
        foreach (array('head', 'shield', 'legs', 'amulet', 'torso', 'ring') as
                    $slot) {
            if ($this->$slot === null) {
                continue;
            }
            if ($stat === null) {
                foreach (array_keys($stats) as $stat_curr) {
                    $stats[$stat_curr] = $this->defense_calculate($stats[$stat_curr],
                            $this->$slot->$stat_curr);
                }
            } else {
                $stats[$stat] = $this->defense_calculate($stats[$stat],
                        $this->$slot->$stat);
            }
        }
        return $stat === null ? $stats : $stats[$stat];
    }

    public function get_formatted_stats() {
        foreach (['head', 'shield', 'legs', 'amulet', 'torso', 'ring'] as $slot) {
            $stats[$slot] = '';
            if ($this->$slot === null) {
                continue;
            }
            foreach (DMG_ELEMENTS as $k => $stat) {
                if ($this->$slot->$stat == 0) {
                    continue;
                }
                for ($i = 0; $i < $k; $i++) {
                    if ($this->$slot->$stat === $this->$slot->{DMG_ELEMENTS[$i]}) {
                        // element already grouped
                        continue 2;
                    }
                }
                for ($i = $k; $i < count(DMG_ELEMENTS); ++$i) {
                    if ($i !== $k && $this->$slot->$stat !== $this->$slot->{DMG_ELEMENTS[$i]}) {
                        continue;
                    }
                    if ($stats[$slot] !== '' && substr($stats[$slot], -1) !== '>') {
                        $stats[$slot] .= '&nbsp;';
                    }
                    $stats[$slot] .= '<img src="/images/icons/' . DMG_ELEMENTS[$i] . '.png" alt="' . _(DMG_ELEMENTS[$i]) . '" title="' . _(DMG_ELEMENTS[$i]) . '"/>';
                }
                $stats[$slot] .= $this->$slot->$stat;
            }
        }
        return $stats;
    }

    public function raise_stat($stat_to_raise) {
        $stat_priority = $this->selected_stats;
        if (($key = array_search($stat_to_raise, $stat_priority)) !== false) {
            unset($stat_priority[$key]);
        }
        $stat_to_raise_val = $this->get_stats($stat_to_raise);
        foreach ($this->filtered_items['head'] as $item_head) {
            foreach ($this->filtered_items['shield'] as $item_shield) {
                foreach ($this->filtered_items['legs'] as $item_legs) {
                    foreach ($this->filtered_items['amulet'] as $item_amulet) {
                        foreach ($this->filtered_items['torso'] as $item_torso) {
                            foreach ($this->filtered_items['ring'] as $item_ring) {
                                $set_curr = new GameContentSet(array(
                                    'head' => $item_head,
                                    'shield' => $item_shield,
                                    'legs' => $item_legs,
                                    'amulet' => $item_amulet,
                                    'torso' => $item_torso,
                                    'ring' => $item_ring
                                ));
                                $stat_to_raise_curr_val = $set_curr->get_stats($stat_to_raise);

                                if ($stat_to_raise_curr_val <= $stat_to_raise_val) {
                                    unset($set_curr);
                                    continue;
                                }

                                $stats_sum_curr = $set_curr->get_stats_sum($stat_priority);

                                if (!isset($set) || $stats_sum_curr > $stats_sum_lowered
                                        || ($stats_sum_curr === $stats_sum_lowered
                                        && $stat_to_raise_curr_val > $stat_to_raise_raised_val)) {
                                    $set = $set_curr;
                                    $stats_sum_lowered = $stats_sum_curr;
                                    $stat_to_raise_raised_val = $stat_to_raise_curr_val;
                                }
                            }
                        }
                    }
                }
            }
        }
        if (isset($set)) {
            if (count($this->set_history) > 3) {
                array_shift($this->set_history);
            }
            $this->set_history[] = array(
                'head' => $this->head,
                'shield' => $this->shield,
                'legs' => $this->legs,
                'amulet' => $this->amulet,
                'torso' => $this->torso,
                'ring' => $this->ring,
                'raise' => $stat_to_raise
            );
            $this->__construct(array_merge((array) $this,
                            array(
                'head' => $set->head,
                'shield' => $set->shield,
                'legs' => $set->legs,
                'amulet' => $set->amulet,
                'torso' => $set->torso,
                'ring' => $set->ring
            )));
        }
    }

    public function set_item($item_id) {
        $item_id = array_map(function ($id) {
            return (int) $id;
        }, (array) $item_id);
        $sql = $GLOBALS['db']->query('SELECT * FROM game_content_armours WHERE id IN (' . implode(',',
                        $item_id) . ')');
        while ($row = $sql->fetch_assoc()) {
            $this->{$row['slot']} = new GameContentItem($row);
        }
        $this->selected_stats = [];
    }

    private function get_stats_sum($stats_priority) {
        $stats = $this->get_stats();
        $sum = 0;
        foreach ($stats_priority as $stat) {
            $sum += $stats[$stat];
        }
        return $sum;
    }

    public function ignore_item($item_id) {
        $slot = $this->contains($item_id);
        if (!$slot) {
            return;
        }
        $this->ignored_items[$item_id] = $this->$slot;
        $this->filter_items($this->get_items());
        while (!empty($this->set_history) && $this->contains($item_id)) {
            $last_set = array_pop($this->set_history);
            $this->__construct($last_set);
        }
        if (!$this->contains($item_id)) {
            $this->raise_stat($last_set['raise']);
        } else {
            $this->get_bis();
        }
    }

    public function unignore_item($item_id) {
        unset($this->ignored_items[$item_id]);
        $this->filter_items($this->get_items());
        foreach (['head', 'torso', 'legs', 'amulet', 'shield', 'ring'] as $slot) {
            if (array_key_exists($item_id, $this->filtered_items[$slot])) {
                if ($this->$slot === null) {
                    $this->$slot = new GameContentItem($this->filtered_items[$slot][$item_id]);
                } else {
                    $stats = $new_stats = 0;
                    foreach ($this->selected_stats as $stat) {
                        $stats += $this->$slot->$stat;
                        $new_stats += $this->filtered_items[$slot][$item_id][$stat];
                    }
                    if ($new_stats > $stats) {
                        $this->$slot = new GameContentItem($this->filtered_items[$slot][$item_id]);
                    }
                }
            }
        }
    }

    private function contains($item_id, $slot = null) {
        if ($slot !== null) {
            return ($this->$slot !== null && $this->$slot->id === $item_id);
        }
        foreach (array('head', 'shield', 'legs', 'amulet', 'torso', 'ring') as
                    $slot) {
            if ($this->$slot !== null && $this->$slot->id === $item_id) {
                return $slot;
            }
        }
        return false;
    }

}
