<?php

/**
 * Tibia-ME.net core functions.
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright Copyright (c) 2008 Tibia-ME.net
 */
function autoload($class)
{
    if (strpos($class, 'PHPExcel') === 0) {
        $class = __DIR__ . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR .
            str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
    } elseif (strpos($class, 'Dropbox\\') === 0 || strpos($class, 'Facebook\\') === 0) {
        // relying on 3rd party loader
        return;
    } else {
        if (!in_array($class,
            ['TibiameComParser', 'TochkiSuParser', 'ExchangerRuParser',
                'TibiameHexatComParser'])) {
            $class = strtolower($class);
        }
        //if (preg_match('@\\\\([\w]+)$@', $class, $matches)) {
        //    $class = $matches[1];
        //}
        $class = __DIR__ . '/classes/' . $class . '.class.php';
    }
    if ((file_exists($class) === false) || (is_readable($class) === false)) {
        log_error('Can\'t load class ' . $class);
        return false;
    }
    require $class;
}

function Bot()
{
    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }
    switch ($_SERVER['HTTP_USER_AGENT']) {
        case 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)':
        case 'Googlebot/2.1 (+http://www.google.com/bot.html)':
        case 'Googlebot/2.1 (+http://www.googlebot.com/bot.html)':
            return 'Googlebot';
        case 'Googlebot-News':
            return 'Googlebot-News';
        case 'Googlebot-Image/1.0':
            return 'Googlebot-Image';
        case 'Googlebot-Video/1.0':
            return 'Googlebot-Video';
        case 'SAMSUNG-SGH-E250/1.0 Profile/MIDP-2.0 Configuration/CLDC-1.1 UP.Browser/6.2.3.3.c.1.101 (GUI) MMP/2.0 (compatible; Googlebot-Mobile/2.1; +http://www.google.com/bot.html)':
        case 'DoCoMo/2.0 N905i(c100;TB;W24H16) (compatible; Googlebot-Mobile/2.1; +http://www.google.com/bot.html)':
        case 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_1 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8B117 Safari/6531.22.7 (compatible; Googlebot-Mobile/2.1; +http://www.google.com/bot.html)':
            return 'Googlebot-Mobile';
        case 'Mediapartners-Goolge':
            return 'Mediapartners-Google';
        case 'AdsBot-Google (+http://www.google.com/adsbot.html)':
            return 'AdsBot-Google';
        case 'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)':
        case 'Mozilla/5.0 (compatible; Yahoo! Slurp China; http://misc.yahoo.com.cn/help.html':
            return 'Yahoo! Slurp';
        case 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)':
            return 'Bingbot';
        case 'Mediapartners-Google':
            return 'Google AdSense';
        case 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)':
            return 'Yandex';
        case 'Mozilla/5.0 (compatible; YandexImages/3.0; +http://yandex.com/bots)':
            return 'Yandex.Image';
        case 'Mozilla/5.0 (compatible; YandexVideo/3.0; +http://yandex.com/bots)':
            return 'Yandex.Video';
        case 'Mozilla/5.0 (compatible; YandexMedia/3.0; +http://yandex.com/bots)':
            return 'YandexMedia';
        case 'Mozilla/5.0 (compatible; YandexBlogs/0.99; robot; +http://yandex.com/bots)':
            return 'YandexBlogs';
        case 'Mozilla/5.0 (compatible; YandexFavicons/1.0; +http://yandex.com/bots)':
            return 'YandexFavicons';
        case 'Mozilla/5.0 (compatible; YandexWebmaster/2.0; +http://yandex.com/bots)':
            return 'Yandex.Webmaster';
        case 'Mozilla/5.0 (compatible; YandexPagechecker/1.0; +http://yandex.com/bots)':
            return 'YandexPagechecker';
        case 'Mozilla/5.0 (compatible; YandexImageResizer/2.0; +http://yandex.com/bots)':
            return 'YandexImageResizer';
        case 'Mozilla/5.0 (compatible; YandexDirect/3.0; +http://yandex.com/bots)':
            return 'YandexDirect/3.0';
        case 'Mozilla/5.0 (compatible; YandexDirect/2.0; Dyatel; +http://yandex.com/bots)':
            return 'YandexDirect/2.0';
        case 'Mozilla/5.0 (compatible; YandexMetrika/2.0; +http://yandex.com/bots)':
            return 'Yandex.Metrica';
        case 'Mozilla/5.0 (compatible; YandexNews/3.0; +http://yandex.com/bots)':
            return 'Yandex.News';
        case 'Mozilla/5.0 (compatible; YandexCatalog/3.0; +http://yandex.com/bots)':
            return 'Yandex.Catalog';
        case 'Mozilla/5.0 (compatible; YandexAntivirus/2.0; +http://yandex.com/bots)':
            return 'YandexAntivirus';
        case 'Mozilla/5.0 (compatible; YandexZakladki/3.0; +http://yandex.com/bots)':
            return 'Yandex.Bookmarks';
        default:
            return false;
    }
    return false;
}

/**
 * Parses $_REQUEST['world'] to get valid world number.
 * @param mixed $default default value to return if no valid world number found
 * @return int|mixed either world number or default value
 */
function get_world($default = null)
{
    if (isset($_REQUEST['world']) && ctype_digit($_REQUEST['world']) && $_REQUEST['world']
        >= 1 && $_REQUEST['world'] <= WORLDS) {
        return intval($_REQUEST['world']);
    } else {
        return $default;
    }
}

function get_vocation($default = null)
{
    if (isset($_GET['vocation']) && ($_GET['vocation'] == 'warrior' || $_GET['vocation']
            == 'wizard')) {
        return $_GET['vocation'];
    } else {
        return $default;
    }
}

function get_month($default = null)
{
    if (!isset($_REQUEST['month'])) {
        return $default;
    }
    $m = intval($_REQUEST['month']);
    if ($m < 1 || $m > 12) {
        return $default;
    }
    return sprintf('%02d', $m);
}

/**
 * logs error
 * @param string $error_msg error message, should not end with dot
 * @return boolean true on success, false on failure, see error_log()
 */
function log_error($error_msg)
{
    $bt = debug_backtrace();
    foreach ($bt as $t) {
        $error_msg .= PHP_EOL . $t['file'] . '(' . $t['line'] . ')';
    }
    return error_log($error_msg);
}

/**
 * gets mime type of file
 * @param string $filename full path to file
 * @return boolean|string mime type on success, false on failure
 */
function get_mime_type($filename)
{
    return (new finfo(FILEINFO_MIME_TYPE))->file($filename);
}

/**
 * @deprecated
 * @param type $ul
 * @param type $count
 * @return string
 */
function icons_shuffle($ul = false, $count = 0)
{
    if ($ul) {
        $paths = array(
            '/images/icons'
        );
    } else {
        $paths = array(
            '/images/icons/' . $_SESSION['icons_client_type'] . '/armours',
            '/images/icons/' . $_SESSION['icons_client_type'] . '/monsters',
            #'/images/icons/' . $_SESSION['icons_client_type'] . '/weapons'
        );
    }
    $icons = array();
    foreach ($paths as $path) {
        $dir = new DirectoryIterator($_SERVER['DOCUMENT_ROOT'] . $path);
        foreach ($dir as $file) {
            if ($file->isFile()) {
                // using file size as array key to make elements unique
                $icons[$file->getSize()] = $path . '/' . $file->getFilename();
            }
        }
    }
    shuffle($icons);
    if ($count) {
        array_slice($icons, 0, $count);
    }
    return $icons;
}

/**
 * Returns redirect link.
 * @param boolean $encode toggles urlencode(), default is true
 * @param string|null $default this value will be returned if $_REQUEST['redirect'] is no set.
 * If $default is not set too, $_SERVER['REQUEST_URI'] is returned.
 * @return string redirect url
 */
function get_redirect($encode = true, $default = null)
{
    $redirect = $_REQUEST['redirect'] ?? ($default ?? $_SERVER['REQUEST_URI']);
    return $encode ? urlencode($redirect) : $redirect;
}

/**
 * @param int|boolean $minutes false to turn off maintenance message, 0 to enable maintenance without specified time
 * @param string|null $message null for default message
 * @param int $highscores set this to 1 and $message to null to display highscores update message
 */
function set_maintenance($time = 0, $time_type = 'm', $message = null,
                         $highscores = 0)
{
    $GLOBALS['db']->query('DELETE FROM `maintenance`'
        . ' WHERE `highscores` = ' . $highscores . ' LIMIT 1', true);
    if ($time === false) {
        return;
    }
    $GLOBALS['db']->query('INSERT INTO `maintenance` (`time`, `time_type`, `message`, `highscores`)'
        . ' VALUES (' . $time . ', \'' . $time_type . '\', ' . ($message ? '\'' . $GLOBALS['db']->real_escape_string($message) . '\''
            : 'NULL') . ','
        . ' ' . $highscores . ')');
}

/**
 * @return string|null maintenance work message or null
 */
function get_maintenance_message()
{
    $sql = $GLOBALS['db']->query('SELECT `time`, `time_type`, `message`, `highscores`'
        . ' FROM `maintenance` LIMIT 1')->fetch_assoc();
    if (!$sql) {
        return null;
    }
    $message = isset($sql['message']) ? $sql['message'] : ($sql['highscores'] ? _('Updating highscores.')
        : _('Maintenance work.'));
    $message .= ' ' . ($sql['time'] ? ($sql['time_type'] == 'h' ? sprintf(ngettext('Come back in %d hour.',
            'Come back in %d hours.', $sql['time']),
            $sql['time']) : sprintf(ngettext('Come back in %d minute.',
            'Come back in %d minutes.', $sql['time']),
            $sql['time'])) : _('Come back later.'));
    return $message;
}

/**
 * file_exists() for remote files, relies on HTTP response
 * @param string $url file URL
 * @return boolean true or false
 */
function remote_file_exists($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($retcode == 200) ? true : false;
}

function curl_get_contents($url, $max_attempts = 3)
{
    if (ini_get('max_execution_time') - time() + $_SERVER['REQUEST_TIME'] < $max_attempts * 5 + 30) {
        ini_set('max_execution_time', $max_attempts * 10 + 30);
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 2);
    curl_setopt($ch, CURLOPT_NOBODY, FALSE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    $header_precheck_url = parse_url($url);
    curl_setopt($ch, CURLOPT_URL,
        (isset($header_precheck_url['scheme']) ? $header_precheck_url['scheme'] . '://'
            : '//') . $header_precheck_url['host'] . $header_precheck_url['path']);
    curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 0) {
        log_error($url . ' is unavailable');
        return false;
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    for ($i = 1; $i <= $max_attempts; ++$i) {
        $data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        switch ($http_code) {
            case 200:
            case 302:
                break 2;
            case 403:
            case 500:
            case 502:
            case 503:
                if ($i == $max_attempts) {
                    log_error('HTTP Code ' . $http_code . ' after ' . $i . ' attempts');
                }
                break;
            case 404:
                log_error('HTTP Code ' . $http_code);
                break 2;
            default:
                log_error('HTTP Code ' . $http_code . ' - don\'t know what to do');
        }
        usleep(min(pow(2, $i) * 500000 + mt_rand(100, 100000), 10000000));
    }
    $data = substr($data, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
    curl_close($ch);
    return in_array($http_code, [403, 404, 500, 502, 503]) ? false : $data;
}

/**
 * Returns an encrypted string
 */
function encrypt($pure_string)
{
    return openssl_encrypt($pure_string, "AES256", '^~.W_]7j:7WN(o', 0, pow(10, openssl_cipher_iv_length("AES256"))/10);
}

/**
 * Returns decrypted original string
 */
function decrypt($encrypted_string)
{
    return openssl_decrypt($encrypted_string, "AES256", '^~.W_]7j:7WN(o', 0, pow(10, openssl_cipher_iv_length("AES256"))/10);
}

function utf8_encode_($string)
{
    return iconv(mb_detect_encoding($string, mb_detect_order(), true), 'UTF-8',
        $string);
}

function _unserialize($string)
{
    $data = @unserialize($string);
    return ($string === 'b:0;' || $data !== false) ? $data : $string;
}

function _get($name, $default = null)
{
    return isset($_GET[$name]) ? _unserialize($_GET[$name]) : $default;
}

function _post($name, $default = null)
{
    return isset($_POST[$name]) ? _unserialize($_POST[$name]) : $default;
}

function _request($name, $default = null)
{
    return isset($_REQUEST[$name]) ? _unserialize($_REQUEST[$name]) : $default;
}

function _session($name, $default = null)
{
    return isset($_SESSION[$name]) ? _unserialize($_SESSION[$name]) : $default;
}

function _cookie($name, $default = null)
{
    return isset($_COOKIE[$name]) ? _unserialize($_COOKIE[$name]) : $default;
}

function get_sign($number)
{
    if (!is_numeric($number)) {
        return false;
    }
    if ($number > 0) {
        return 1;
    }
    if ($number == 0) {
        return $number;
    }
    if ($number < 0) {
        return -1;
    }
}

function is_int_string($num)
{
    if ($num === null || $num === '') {
        return false;
    }
    $string = (string)$num;
    if ($string != $num) {
        return false;
    }
    if ($string[0] === '-') {
        $string = substr($string, 1);
    }
    return ctype_digit($string);
}
