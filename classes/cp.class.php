<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2013, Tibia-ME.net
 */
class CP {

    public $message, $time = 0, $time_type;

    public static function auth($additional_perm = null) {
        if (!Perms::get(Perms::CP_ACCESS) || ($additional_perm !== null && !Perms::get($additional_perm))) {
            Document::reload_msg(_('Access denied.'), (pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME) === 'index' ? '/' : '.'));
        }
    }

    public function set_maintenance() {
        if (isset($_GET['disable'])) {
            set_maintenance(false);
            Document::reload_msg(_('Maintenance work notification has been disabled.'), $_SERVER['SCRIPT_NAME']);
        }
        if (isset($_POST['time'])) {
            if (isset($_POST['time_type']) && $_POST['time_type'] == 'h') {
                $time_type = 'h';
            } else {
                $time_type = 'm';
            }
            $message = empty($_POST['message']) ? null : $_POST['message'];
            set_maintenance(intval($_POST['time']), $time_type, $message);
            Document::reload_msg(_('Maintenance work notification has been updated.'), $_SERVER['SCRIPT_NAME']);
        } else {
            list($this->time, $this->time_type, $this->message) = $GLOBALS['db']->query(
                            'SELECT `time`, `time_type`, `message` FROM `maintenance`'
                            . ' WHERE `highscores` = 0 LIMIT 1')->fetch_row();
        }
    }

}
