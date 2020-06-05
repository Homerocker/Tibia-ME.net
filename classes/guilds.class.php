<?php

class Guilds
{

    public $count = 0, $data = array();

    public function fetch()
    {
        $sql = $GLOBALS['db']->query('SELECT * FROM `guilds`');
        while ($row = mysql_fetch_assoc($sql)) {
            $this->data[] = $row;
            ++$this->count;
        }
    }

}
