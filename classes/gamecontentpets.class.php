<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 */
class GameContentPets {

    public $data = array();

    public function fetch() {
        $sql = $GLOBALS['db']->query('SELECT * FROM game_content_pets ORDER BY (SELECT attack+defense FROM game_content_pets_stats WHERE name = game_content_pets.name AND level = 1), (SELECT hp FROM game_content_pets_stats WHERE name = game_content_pets.name AND level = 1), name');
        while ($assoc = $sql->fetch_assoc()) {
            $this->data[] = $assoc;
            end($this->data);
            $key = key($this->data);
            $sql_stats = $GLOBALS['db']->query('SELECT * FROM game_content_pets_stats WHERE name = '
                    . $GLOBALS['db']->quote($assoc['name']));
            while ($assoc = $sql_stats->fetch_assoc()) {
                $this->data[$key]['stats'][] = $assoc;
            }
        }
    }

}
