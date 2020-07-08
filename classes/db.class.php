<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2014, Tibia-ME.net
 */
class DB extends mysqli {

    /**
     * @var float $slow_query_time seconds
     */
    private $slow_query_time = 0;
    private $stmt_affected_rows = -1;

    public function __construct() {
        if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === 'tibiame.ddns.net' || $_SERVER['SERVER_NAME'] == '192.168.100.2') {
            parent::__construct('localhost', 'root', 'root', 'smolodo_tibiame');
        } else {
            parent::__construct(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        }
        if ($this->connect_errno) {
            exit(_('Could not connect to database. Try again in few minutes.'));
        }
        if (!$this->set_charset('utf8mb4')) {
            exit(sprintf(_('Could not load character set %s.'), 'utf8mb4'));
        }

        $this->query('SET time_zone=\'' . date('P') . '\'');
        return $this;
    }

    private function log_error($query) {
        foreach (debug_backtrace() as $dbgbt) {
            if ($dbgbt['file'] !== __FILE__) {
                break;
            }
        }
        return error_log($this->error . ' (' . $this->errno . ') in ' . $dbgbt['file'] . ' on line ' . $dbgbt['line'] . ' (' . $query . ')');
    }

    public function prepare($query, $paramtypes = null) {
        $stmt = new DB_stmt($this, $query, $paramtypes);
        if ($stmt->errno) {
            foreach (debug_backtrace() as $dbgbt) {
                if ($dbgbt['file'] !== __FILE__) {
                    break;
                }
            }
            error_log($stmt->error . ' in ' . $dbgbt['file'] . ' on line ' . $dbgbt['line']);
        }
        return $stmt;
    }

    /**
     * Returns the maximum of mysqli::$affected_rows and mysqli_stmt::$affected_rows
     * @return int number of affected rows or -1 on error
     */
    public function affected_rows() {
        return max($this->affected_rows, $this->stmt_affected_rows);
    }

    public function query($query, $ping = false) {
        if ($ping && !$this->ping()) {
            $this->__construct();
        }
        if ($this->slow_query_time) {
            $duration = microtime(true);
        }
        $result = parent::query($query);
        if ($this->slow_query_time) {
            $duration = (microtime(true) - $duration);
            if ($duration > $this->slow_query_time) {
                $query = trim(preg_replace('/\s+/', ' ', $query));
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/../mysql-slow-query.log')) {
                    $file = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../mysql-slow-query.log');
                } else {
                    $file = '';
                }
                if ($file == '' || strpos($file, PHP_EOL . $query . PHP_EOL) === false) {
                    error_log($duration . 's ' . PHP_EOL . '+' . PHP_EOL . $query . PHP_EOL,
                            3,
                            $_SERVER['DOCUMENT_ROOT'] . '/../mysql-slow-query.log');
                } else {
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/../mysql-slow-query.log',
                            str_replace(PHP_EOL . $query . PHP_EOL,
                                    '+' . PHP_EOL . $query . PHP_EOL, $file),
                            LOCK_EX);
                }
                unset($file);
            }
        }
        if ($this->errno) {
            if ($this->errno == 2006 && !$ping) {
                return $this->query($query, true);
            } else {
                $this->log_error($query);
            }
        }
        return $result;
    }

    /**
     * adds quotes and casts mysqli::real_escape_sting() when necessary,
     * converts PHP NULL values to 'NULL' strings usable in MySQL queries
     * NOTE: this function only works with single-quoted queries.
     * @param string|array $val can be multidimensional
     * @return string|array formatted string or array of strings
     */
    public function quote($val) {
        if ($val === null) {
            return 'NULL';
        } elseif (is_array($val)) {
            return array_map(array($this, __FUNCTION__), $val);
        } elseif (is_int($val) && ctype_digit((string) $val)) {
            return $val;
        }
        return '\'' . $this->real_escape_string($val) . '\'';
    }

    /**
     * Simple MySQL INSERT.
     * @param string $table table name
     * @param array $fields_data fields name as keys and inserted data as values.
     * Accepts unsafe values, DO NOT use DB::quote() manually. Fields names have to be checked manually tho, if needed.
     */
    public function insert($table, $fields_data) {
        /*
          if (!$this->identifier_verify($table, array_keys($fields_data))) {
          log_error('invalid SQL identifier');
          return false;
          }
         *
         */
        return $this->query('INSERT INTO ' . $table . ' ('
                        . implode(', ', array_keys($fields_data)) . ') VALUES ('
                        . implode(', ', $this->quote($fields_data)) . ')');
    }

    /**
     * @param string $table
     * @param array|string $fields list of inserted fields
     * @param array|string $data 2-dimensional associative array with column names as keys and unsafe data as values
     */
    public function insert_multi($table, $fields, $data) {
        if (!$this->identifier_verify($table, $fields)) {
            log_error('invalid SQL identifier');
            return false;
        }
        asort($fields);
        $this->query('INSERT INTO ' . $table . ' ('
                . (is_array($fields) ? implode(', ', $fields) : $fields) . ') VALUES ('
                . implode('), (',
                        array_map(function ($dataset) {
                            ksort($dataset);
                            return implode(', ', $dataset);
                        }, $this->quote($data))
                ) . ')');
    }

    /**
     * Truncate tables. Specify each table name as a new param.
     */
    public function truncate() {
        foreach (func_get_args() as $table_name) {
            $this->multi_query('RENAME TABLE ' . $table_name . ' TO ' . $table_name . '_del;'
                    . 'CREATE TABLE ' . $table_name . ' LIKE ' . $table_name . '_del;'
                    . 'DROP TABLE ' . $table_name . '_del;');
            while ($this->more_results()) {
                $this->next_result();
            }
        }
    }

    /**
     * @param string $table table name
     * @param array $fields_data an array with fields names as keys and inserted data as values. Accepts unsafe (unquoted) data.
     * @param string|null $where_clause Where conditions starting with keyword WHERE, or null.
     */
    public function update($table, $fields_data, $where_clause = null) {
        /*
          if (!$this->identifier_verify($table, array_keys($data))) {
          log_error('invalid SQL identifier');
          return false;
          }
         * 
         */
        $fields_data = $this->quote($fields_data);
        foreach ($fields_data as $field => $value) {
            if (isset($query)) {
                $query .= ', ' . $field . ' = ' . $value;
            } else {
                $query = 'UPDATE ' . $table . ' SET ' . $field . ' = ' . $value;
            }
        }
        if ($where_clause !== null) {
            $query .= ' ' . $where_clause;
        }
        return $this->query($query);
    }

    /**
     * @deprecated
     */
    public function delete($table, $where) {
        if (!$this->identifier_verify($table, array_keys($where))) {
            log_error('invalid SQL identifier');
            return false;
        }
        $where = $this->quote($where);
        foreach ($where as $field => $value) {
            if (isset($query)) {
                $query .= ' AND ' . $field . ' = ' . $value;
            } else {
                $query = 'DELETE FROM ' . $table . ' WHERE ' . $field . ' = ' . $value;
            }
        }
        $this->query($query);
    }

    /**
     * Checks if SQL identifiers names are valid.
     * @param string|array list of strings or one-dimensional arrays
     * @return boolean true if all strings are valid SQL identifier names, otherwise false
     */
    private function identifier_verify() {
        $args = func_get_args();
        foreach ($args as $identifier) {
            if (is_array($identifier)) {
                foreach ($identifier as $id) {
                    if (!preg_match('/^[a-z_][a-z0-9_]*$/i', $id)) {
                        return false;
                    }
                }
            } elseif (!preg_match('/^[a-z_][a-z0-9_]*$/i', $identifier)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Simple SELECT.
     * @param type $table
     * @deprecated
     */
    public function select($tables_fields, $where) {
        
    }

    /**
     * Enables custom MySQL slow queries logging.
     * @param float $slow_query_time Execution time in seconds above which mysql query considered slow. If set to 0 slow query log is disabled.
     */
    public function slow_query_log($slow_query_time = 0) {
        $this->slow_query_time = $slow_query_time;
    }

}
