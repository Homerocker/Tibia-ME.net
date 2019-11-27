<?php

/**
 * @package search
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 * @version 2.3
 */
class Search {

    /**
     * @var int count of serch results 
     */
    public $count = 0;

    /**
     * @var array search results 
     */
    public $results = array();

    /**
     * @var string target field name 
     */
    private $field_name;

    /**
     * @var array search words to highlight 
     */
    private $search_words;

    /**
     * Executes mysql query and fetches search results.
     * @param string $mysql_query mysql query fetching search results
     */
    public function search ($mysql_query) {
        $sql = $GLOBALS['db']->query($mysql_query);
        while ($row = $sql->fetch_assoc()) {
            $this->results[] = $row;
            $this->results[$this->count][$this->field_name]
                    = strip_tags(Forum::MessageHandler($this->results[$this->count][$this->field_name]));
            if (strlen($this->results[$this->count][$this->field_name]) > 256) {
                $this->results[$this->count][$this->field_name]
                        = substr($this->results[$this->count][$this->field_name], 0, 256);
            }
            foreach ($this->search_words as $word) {
                $this->results[$this->count][$this->field_name]
                        = str_ireplace($word, '<span class="search_result_highlight">' . $word . '</span>', $this->results[$this->count][$this->field_name]);
            }
            ++$this->count;
        }
    }

    /**
     * Generates part of mysql query containing search request
     * @param string $field_name search target field
     * @param string $query search query
     * @return string part of mysql query starting with space
     */
    public function parse_query ($field_name, $query) {
        $query = trim($query);
        $query = explode(' ', $query);
        $sql = '';
        foreach ($query as $i => $word) {
            if ($i == 0) {
                $sql .= ' `' . $field_name . '` LIKE \'%' . $word . '%\'';
            } else {
                $sql .= ' AND `' . $field_name . '` LIKE \'%' . $word . '%\'';
            }
        }
        $this->field_name = $field_name;
        $this->search_words = $query;
        return $sql;
    }

}