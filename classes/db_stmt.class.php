<?php

/**
 * @copyright (c) Tibia-ME.net, 2018
 */
class DB_stmt extends mysqli_stmt
{
    public $fields = [];
    public $paramtypes;
    public $row;
    public $query_string;

    public function __construct($link, $query, $paramtypes = null)
    {
        $this->query_string = $query;
        $this->paramtypes = $paramtypes;
        parent::__construct($link, $query);
    }

    public function execute(...$params)
    {
        if (count($params)) {
            if ($this->paramtypes === null) {
                $this->paramtypes = $this->get_params_types($params);
            }
            $this->bind_param($this->paramtypes, ...$params);
        }
        $result = parent::execute();
        if ($this->errno) {
            $this->log_error();
        }
        $meta = $this->result_metadata();
        if (!$meta) {
            return $result;
        }
        $this->store_result();
        $this->fields = [];
        while ($field = $meta->fetch_field()) {
            $this->fields[] = $field->name;
        }
        $params = [];
        foreach ($this->fields as $field) {
            $params[] = &$this->row[$field];
        }
        $this->bind_result(...$params);
        return $this;
    }

    public function fetch_assoc()
    {
        if (!($fetch = $this->fetch())) {
            return $fetch;
        }
        return $this->row;
    }

    public function fetch_row()
    {
        if (!($fetch = $this->fetch())) {
            return $fetch;
        }
        return array_values($this->row);
    }

    public function fetch_all($resulttype = MYSQLI_ASSOC)
    {
        $result = [];
        switch ($resulttype) {
            case MYSQLI_ASSOC:
                while ($row = $this->fetch_assoc()) {
                    $result[] = $row;
                }
                break;
            case MYSQLI_NUM:
                while ($row = $this->fetch_row()) {
                    $result[] = $row;
                }
                break;
            default:
                return false;
        }
        return $result;
    }

    private function get_params_types($params)
    {
        return implode('', array_map(function ($param) {
            switch (gettype($param)) {
                case 'integer':
                    return 'i';
                case 'double':
                    return 'd';
                default:
                    return 's';
            }
        }, $params));
    }

    private function log_error()
    {
        foreach (debug_backtrace() as $dbgbt) {
            if ($dbgbt['file'] !== __FILE__) {
                break;
            }
        }
        return error_log($this->error . ' in ' . $dbgbt['file'] . ' on line ' . $dbgbt['line'] . ' (' . $this->query_string . ')');
    }
}