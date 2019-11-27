<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 */
class Filesystem {

    function dirsize($dir) {
        if (!file_exists($dir)) {
            return false;
        }
        $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir));
        $dirsize = 0;
        foreach ($iterator as $file) {
            $dirsize += $file->getSize();
        }
        return $dirsize;
    }

    public static function format_size($bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2);
            $bytes = sprintf(ngettext('%s GB', '%s GB', $bytes), $bytes);
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2);
            $bytes = sprintf(ngettext('%s MB', '%s MB', $bytes), $bytes);
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2);
            $bytes = sprintf(ngettext('%s KB', '%s KB', $bytes), $bytes);
        } else {
            $bytes = sprintf(ngettext('%s byte', '%s bytes', $bytes), $bytes);
        }
        return $bytes;
    }

    /**
     * Get disk usage. All values are in bytes.
     * @return array array(
     *  'used_cache' => ...,
     *  'beget_total' => ...,
     *  'beget_used' => ...
     * )
     */
    public static function get_disk_usage() {
        $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT'] . CACHE_DIR,
                RecursiveDirectoryIterator::SKIP_DOTS));
        $used_cache = 0;
        foreach ($iterator as $file) {
            $used_cache += $file->getSize();
        }
        $beget = json_decode(curl_get_contents('https://api.beget.ru/api/user/getAccountInfo?login=kfb3752u&passwd=KEvAdwkx&output_format=json'),
                        true)['answer']['result'];
        return array(
            'used_cache' => $used_cache,
            'beget_total' => $beget['plan_quota'] * 1024 * 1024,
            'beget_used' => $beget['user_quota'] * 1024 * 1024
        );
    }

    public static function usage_bar($space_used, $space_total) {
        $ratio = ceil($space_used / $space_total * 100);
        if ($ratio > 100) {
            $ratio = 100;
        } elseif ($ratio < 0) {
            $ratio = 0;
        }
        if ($ratio == 0) {
            return '/images/bar.png';
        }
        if (($im = Cache::getFile('bar_' . $ratio . '.png')) !== false) {
            return $im;
        }
        $im = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . '/images/bar.png');
        imagefilledrectangle($im, 1, 1, ceil(48 * ($ratio / 100)), 8,
                imagecolorallocate($im, 0, 255, 0));
        ob_start();
        imagepng($im);
        Cache::write(ob_get_contents(), 'bar_' . $ratio . '.png');
        ob_end_clean();
        return Cache::getFile('bar_' . $ratio . '.png');
    }

    /**
     * Empty directory. Skips .htaccess.
     * @param string $dir path to dir
     * @param boolean $recursive
     * @param boolean $rmdir if set to true will also remove .htaccess and root dir
     */
    public static function emptydir($dir, $recursive = false, $rmdir = false) {
        if (!file_exists($dir)) {
            return;
        }
        $dir = $recursive ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,
                RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST) : new DirectoryIterator($dir);
        foreach ($dir as $iterator) {
            if ((!$recursive && $iterator->isDot()) || (!$rmdir && $iterator->getFilename()
                    === '.htaccess')) {
                continue;
            }
            if ($iterator->isDir()) {
                rmdir($iterator->getPathname());
            } else {
                unlink($iterator->getPathname());
            }
        }
    }

    public static function get_doc_root_path($path) {
        $realpath = realpath($path);
        if (!$realpath) {
            return $path;
        }
        return substr(realpath($realpath), strlen($_SERVER['DOCUMENT_ROOT']));
    }

    public static function mkdir($path) {
        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            log_error('could not create folder "' . $path . '"');
            return false;
        }
        return true;
    }

}
