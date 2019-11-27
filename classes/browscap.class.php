<?php

class Browscap {

    private static function get_version() {
        if (($version = Cache::read('browscap_version')) || !file_exists($_SERVER['DOCUMENT_ROOT'] . '/../browscap.ini')) {
            return $version;
        }
        $ini = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../browscap.ini');
        $version = parse_ini_string(substr($ini, 0, strpos($ini, 'DefaultProperties')))['Version'];
        Cache::write($version, 'browscap_version');
        return $version;
    }

    private static function get_latest_version() {
        return (($version = Cache::read('browscap_version', 36000)) !== false) ? $version : curl_get_contents('http://browscap.org/version-number');
    }

    /**
     * Updates browscap INI if new version is available.
     */
    private static function update() {
        if (!($version = self::get_latest_version())) {
            if ($version === false && !Cache::touch('browscap_version')) {
                self::get_version();
            }
            return false;
        }
        if (self::get_version() == $version && file_exists($_SERVER['DOCUMENT_ROOT'] . '/../browscap.ini')) {
            return true;
        }
        if (($ini_data = curl_get_contents('http://browscap.org/stream?q=Lite_PHP_BrowsCapINI')) && file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/../browscap.ini', $ini_data, LOCK_EX)) {
            Cache::write($version, 'browscap_version');
            return true;
        }
        return false;
    }

    /**
     * Makes sure browscap is updated and returns PHP's default get_browser()
     */
    public static function get_browser() {
        self::update();
        return get_browser();
    }

}
