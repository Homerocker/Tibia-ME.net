<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 */
class GameContentItem {

    public $id, $name, $icon, $hit = 0, $fire = 0, $ice = 0, $energy = 0, $soul = 0;

    public function __construct($item = null) {
        if ($item !== null) {
            $item = (array) $item;
            foreach (array_keys(get_object_vars($this)) as $k) {
                if (isset($item[$k])) {
                    $this->$k = $item[$k];
                }
            }
        }
    }

    public function set_armour_id($id) {
        $this->__construct($GLOBALS['db']->query('SELECT * FROM game_contet_armours'
                        . ' WHERE id = ' . $id)->fetch_assoc());
    }

}
