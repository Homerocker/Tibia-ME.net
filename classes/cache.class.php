<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2013, Tibia-ME.net
 */
class Cache
{

    /**
     * Recursive analogue for rmdir()
     * @param string $dir full path to directory
     * @return boolean true or false
     * @deprecated
     */
    public static function rmtree($dir)
    {
        if (empty($dir) || !is_dir($dir)) {
            return false;
        }
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            is_dir($dir . '/' . $file) ? rmtree($dir . '/' . $file) : unlink($dir . '/' . $file);
        }
        return rmdir($dir);
    }

    /**
     * @deprecated
     * @param string $path
     * @param string $parent_folder
     * @return string|boolean
     */
    private function is_sub_dir($path = NULL, $parent_folder = SITE_PATH)
    {

        //Get directory path minus last folder
        $dir = dirname($path);
        $folder = substr($path, strlen($dir));

        //Check the the base dir is valid
        $dir = realpath($dir);

        //Only allow valid filename characters
        $folder = preg_replace('/[^a-z0-9\.\-_]/i', '', $folder);

        //If this is a bad path or a bad end folder name
        if (!$dir OR !$folder OR $folder === '.') {
            return FALSE;
        }

        //Rebuild path
        $path = $dir . DS . $folder;

        //If this path is higher than the parent folder
        if (strcasecmp($path, $parent_folder) > 0) {
            return $path;
        }

        return FALSE;
    }

    /**
     * @param string $data string|int|array
     * @param string $filename cache file name without extension
     * @param string|null $subdir cache subdir
     * @param boolean $serialize true to serialize $data
     */
    public static function write($data, $filename, $subdir = null)
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . CACHE_DIR . '/';
        if ($subdir !== null) {
            $path .= $subdir . '/';
            if (!is_dir($path) && !mkdir($path, 0777, true)) {
                log_error('could\'t create cache subdir');
                return false;
            }
        }
        $path .= $filename;
        $ext = self::get_extension($filename);
        $path .= $ext;
        if (!file_put_contents($path, ($ext == 'dat' || $ext == '.dat') ? serialize($data) : $data, LOCK_EX)) {
            log_error('could not write to cache file');
            return false;
        }
    }

    /**
     * @param string $filename cache file name without extension
     * @param int $timeout seconds for cache file to expire, 0 to disable (cache file doesn't expire)
     * @param string|null $subdir cache subdir
     * @return unserialized data from cached file if it exists and is not expired, otherwise false
     * Warning: This function may return Boolean FALSE, but may also return a non-Boolean value which evaluates to FALSE.
     * Use the === operator for testing the return value of this function.
     */
    public static function read($filename, $timeout = 0, $subdir = null)
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . CACHE_DIR . '/';
        if ($subdir !== null) {
            $path .= $subdir . '/';
        }
        $path .= $filename;
        $ext = self::get_extension($filename);
        $path .= $ext;
        if (!file_exists($path) || ($timeout && $_SERVER['REQUEST_TIME'] - filemtime($path) >= $timeout)) {
            return false;
        }
        if ($ext == 'dat' || $ext == '.dat') {
            return unserialize(file_get_contents($path));
        }
        return file_get_contents($path);
    }

    public static function getFile($filename, $subdir = null)
    {
        $path = CACHE_DIR;
        if ($subdir !== null) {
            $path .= '/' . $subdir;
        }
        $path .= '/' . $filename;
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
            return false;
        }
        return $path;
    }

    private static function get_extension($filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if ($ext === null || substr($ext, -1) != '.') {
            return '.dat';
        }
        return 'dat';
    }

    public static function touch($filename, $subdir = null)
    {
        $ext = self::get_extension($filename);
        $path = $_SERVER['DOCUMENT_ROOT'] . CACHE_DIR . ($subdir === null ? '' : '/' . $subdir) . '/' . $filename . $ext;
        if (file_exists($path)) {
            return touch($path);
        }
        return false;
    }

}
