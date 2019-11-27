<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2014, Tibia-ME.net
 *
 * This class stores messages to be translated with gettext in .php files. These files can be then parsed with Poedit to add  the messages to its catalogue. This is useful when the messages are not accessible by Poedit (e.g. stored in database).
 */
class GettextExtraMessages {

    private $file, $filename;

    /**
     * @param string $filename file name (not path) without extension
     * @param boolean $append if set to true appends data to file instead of overwriting it
     */
    public function __construct($filename = 'default', $append = false) {
        $this->filename = __DIR__ . '/../gettextextras/' . $filename . '.php';
        $this->file = ($append && file_exists($this->filename)) ? file($this->filename) : array();
    }

    /**
     * Adds message. Message will be added even if it already exists.
     * @param string $string message to be translated
     */
    public function add($string) {
        $string = str_replace('%', '&#37;', $string);
        $this->file[] = '_(\'' . addslashes($string) . '\');' . "\n";
        return $string;
    }

    /**
     * Removes first matching message.
     * @param string $string message to be removed
     * @return boolean false if message not found, otherwise true
     */
    public function remove($string) {
        $key = array_search('_(\'' . $string . '\');' . PHP_EOL, $this->file);
        if ($key === false) {
            return false;
        }
        unset($this->file[$key]);
        return true;
    }

    /**
     * Writes changes to file.
     */
    public function __destruct() {
        if (empty($this->file)) {
            if (file_exists($this->filename)) {
                unlink($this->filename);
            }
            return;
        }
        $this->file = array_unique($this->file);
        if ($this->file[0] != '<?php' . PHP_EOL) {
            array_unshift($this->file, '<?php' . PHP_EOL);
        }
        file_put_contents($this->filename, implode('', $this->file), LOCK_EX);
    }

}
