<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2013, Tibia-ME.net
 */
class Ranks {

    public $id, $name, $prefix, $color, $perms = array(), $error = array(), $mode, $rank_id;

    const MODE_DEL_GET = 1, MODE_EDIT_GET = 2, MODE_DEL_POST = 3, MODE_EDIT_POST
            = 4, MODE_ADD_POST = 5;

    public static $colors = array(
        'inherit' => '-',
        'blue' => 'blue',
        'green' => 'green',
        'orange' => 'orange',
        'red' => 'red'
    );

    public function __construct() {
        if (isset($_POST['delete']) && self::exists($_POST['delete'])) {
            $this->mode = self::MODE_DEL_POST;
            $this->rank_id = $_POST['delete'];
        } elseif (isset($_POST['edit']) && isset($_POST['name']) && isset($_POST['prefix'])
                && isset($_POST['color']) && self::exists($_POST['edit'])) {
            $this->mode = self::MODE_EDIT_POST;
            $this->rank_id = $_POST['edit'];
        } elseif (isset($_GET['delete']) && self::exists($_GET['delete'])) {
            $this->mode = self::MODE_DEL_GET;
            $this->rank_id = $_GET['delete'];
        } elseif (isset($_GET['edit']) && self::exists($_GET['edit'])) {
            $this->mode = self::MODE_EDIT_GET;
            $this->rank_id = $_GET['edit'];
        } elseif (isset($_POST['add']) && isset($_POST['name']) && isset($_POST['prefix'])
                && isset($_POST['color'])) {
            $this->mode = self::MODE_ADD_POST;
        }
    }

    /**
     * @param string $rank_name case-sensitive rank name
     * @return int rank id
     */
    public static function get_id_by_name($rank_name) {
        return $GLOBALS['db']->query('SELECT id FROM ranks WHERE name = ' . $GLOBALS['db']->quote($rank_name))->fetch_row()[0];
    }

    /**
     * @param int $id should be safe to use in MySQL query
     * @return null|array
     */
    public function get($id = null) {
        if ($this->name !== null || $id === null) {
            return array(
                'id' => $this->id,
                'name' => $this->name,
                'prefix' => $this->prefix,
                'color' => $this->color,
                'perms' => $this->perms
            );
        } else {
            $perms = array();
            $sql = $GLOBALS['db']->query('SELECT perm_id FROM ranks_perms WHERE rank_id = ' . $id);
            while ($perm_id = $sql->fetch_row()) {
                $perms[] = $perm_id[0];
            }
            return array_merge($GLOBALS['db']->query('SELECT id, name, prefix, color'
                            . ' FROM ranks WHERE id = ' . $id)->fetch_assoc(),
                    array('perms' => $perms));
        }
    }

    /**
     * Get ranks list.
     * @param int $exclude rank id to exclude, should be safe to use in MySQL query
     * @param int $restrict rank id to restrict permissions to. Only ranks with same or lower permissions will be returned.
     * @return array
     */
    public static function get_list($exclude = null, $restrict = null) {
        $sql = 'SELECT id, name, prefix, color FROM ranks';
        if ($exclude !== null) {
            $sql .= ' WHERE id != ' . $exclude;
        }
        $sql = $GLOBALS['db']->query($sql);
        if ($restrict !== null) {
            $perms_restrict = self::get_perms($restrict);
        }
        while ($rank = $sql->fetch_assoc()) {
            if ($restrict !== null) {
                $perms = self::get_perms($rank['id']);
                if (!empty(array_diff($perms, $perms_restrict))) {
                    continue;
                }
            }
            yield $rank;
        }
    }

    public function save() {
        $args = func_get_args();
        list($this->id, $this->name, $this->prefix, $this->color, $this->perms) = array_map(function ($arg) {
            if (is_string($arg)) {
                $arg = trim($arg);
            }
            return $arg === '' ? null : $arg;
        }, $args);
        if ($this->name === null) {
            $this->error[] = _('Rank name can\'t be empty.');
        } elseif (isset($this->name[20])) {
            $this->error[] = _('Rank name is too long.');
        } elseif (($this->id === null || $this->name !== $GLOBALS['db']->query('SELECT name FROM ranks WHERE id = ' . $this->id)->fetch_row()[0]) && $GLOBALS['db']->query('SELECT COUNT(*) FROM ranks WHERE name = '
                        . $GLOBALS['db']->quote($this->name))->fetch_row()[0] > 0) {
            $this->error[] = _('This name is already in use.');
        }
        if (!empty($this->prefix) && !preg_match('/[a-z]/i', $this->prefix)) {
            $this->error[] = _('Prefix contains invalid characters.');
        }
        if (isset($this->prefix[4])) {
            $this->error[] = _('Prefix is too long.');
        }
        if (!empty($this->color) && !array_key_exists($this->color,
                        self::$colors)) {
            $this->error[] = _('Invalid color.');
        }
        if (!empty(array_diff($this->perms,
                                (new ReflectionClass('Perms'))->getConstants()))) {
            $this->error[] = _('Invalid permissions.');
        }
        if (!empty($this->error)) {
            return false;
        }
        if ($this->id === null) {
            $GLOBALS['db']->query('INSERT INTO ranks (name, prefix, color)'
                    . ' VALUES (' . $GLOBALS['db']->quote($this->name) . ', '
                    . $GLOBALS['db']->quote($this->prefix) . ', '
                    . $GLOBALS['db']->quote($this->color) . ')');
            if (!empty($this->perms)) {
                $this->id = $GLOBALS['db']->insert_id;
            }
            (new GettextExtraMessages('ranks', true))->add($this->name);
        } else {
            $GLOBALS['db']->query('UPDATE ranks SET name = '
                    . $GLOBALS['db']->quote($this->name) . ', prefix = '
                    . $GLOBALS['db']->quote($this->prefix) . ', color = '
                    . $GLOBALS['db']->quote($this->color) . ' WHERE id = ' . $this->id);
            $GLOBALS['db']->query('DELETE FROM ranks_perms WHERE rank_id = ' . $this->id);
        }
        if (!empty($this->perms)) {
            $GLOBALS['db']->query('INSERT INTO ranks_perms (rank_id, perm_id)'
                    . ' VALUES (' . $this->id . ', ' . implode('), (' . $this->id
                            . ', ', $this->perms) . ')');
        }
        return true;
    }

    /**
     * 
     * @param int $rank_id should be safe to use in MySQL query
     */
    public function count_users($rank_id) {
        return $GLOBALS['db']->query('SELECT COUNT(*) FROM user_profile WHERE rank = ' . $rank_id)->fetch_row()[0];
    }

    public function delete($id, $assign = 1) {
        if ($assign != $id && Ranks::exists($assign)) {
            $GLOBALS['db']->query('UPDATE user_profile SET rank = ' . $assign . ' WHERE rank = ' . $id);
        }
        (new GettextExtraMessages('ranks'))->remove($GLOBALS['db']->query('SELECT name FROM ranks WHERE id = ' . $id)->fetch_row()[0]);
        $GLOBALS['db']->query('DELETE FROM ranks WHERE id = ' . $id);
        $GLOBALS['db']->query('DELETE FROM ranks_perms WHERE rank_id = ' . $id);
        return true;
    }

    public static function exists($id) {
        if (!ctype_digit((string) $id)) {
            return false;
        }
        return $GLOBALS['db']->query('SELECT COUNT(*) FROM ranks WHERE id = ' . $id)->fetch_row()[0];
    }

    /**
     * @return array
     */
    public static function get_perms($rank_id) {
        $sql = $GLOBALS['db']->query('SELECT perm_id FROM ranks_perms WHERE rank_id = ' . $rank_id);
        $perms = array();
        while ($row = $sql->fetch_row()) {
            $perms[] = $row[0];
        }
        return $perms;
    }

}
