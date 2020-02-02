<?php

/**
 * VKAPI class for vk.com social network
 *
 * @package server API methods
 * @link http://vk.com/developers.php
 * @autor Oleg Illarionov
 * @version 1.0-Tibia-ME.net
 */
class vkapi
{

    var $api_secret;

    var $app_id;

    var $api_url;

    private $n = 0;

    function __construct($app_id, $api_secret, $api_url = 'api.vk.com/api.php')
    {
        $this->app_id = $app_id;
        $this->api_secret = $api_secret;
        if (!strstr($api_url, 'http://'))
            $api_url = 'http://' . $api_url;
        $this->api_url = $api_url;
    }

    function api($method, $params = false)
    {
        if (!$params)
            $params = array();
        $params['api_id'] = $this->app_id;
        $params['v'] = '3.0';
        $params['method'] = $method;
        $params['timestamp'] = $_SERVER['REQUEST_TIME'];
        $params['format'] = 'json';
        $params['random'] = rand(0, 10000);
        ksort($params);
        $sig = '';
        foreach ($params as $k => $v) {
            $sig .= $k . '=' . $v;
        }
        $sig .= $this->api_secret;
        $params['sig'] = md5($sig);
        $query = $this->api_url . '?' . $this->params($params);
        do {
            $res = json_decode(curl_get_contents($query), true);
        } while ((($res === null || $res === false || (isset($res['error']) && ($res['error']['error_code'] == 1 || $res['error']['error_code'] == 6))) && $this->backoff()) || $this->n = 0);
        if (isset($res['error'])) {
            log_error('VK API error: ' . $res['error']['error_msg']);
        }
        return $res;
    }

    function params($params)
    {
        $pice = array();
        foreach ($params as $k => $v) {
            $pice[] = $k . '=' . urlencode($v);
        }
        return implode('&', $pice);
    }

    private function backoff()
    {
        if ($this->n === 5) {
            return false;
        }
        usleep((2 ^ $this->n++) * 1000000 + mt_rand(0, 1000) * 1000);
        return true;
    }

}
