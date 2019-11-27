<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2014, Tibia-ME.net
 */
class ExchangerRuParser {

    private $exchtypes = array(
        'WMZWMR' => 1,
        'WMRWMZ' => 2,
        'WMZWME' => 3,
        'WMEWMZ' => 4,
        'WMEWMR' => 5,
        'WMRWME' => 6,
        'WMZWMU' => 7,
        'WMUWMZ' => 8,
        'WMRWMU' => 9,
        'WMUWMR' => 10,
        'WMUWME' => 11,
        'WMEWMU' => 12,
        'WMBWMZ' => 17,
        'WMZWMB' => 18,
        'WMBWME' => 19,
        'WMEWMB' => 20,
        'WMRWMB' => 23,
        'WMBWMR' => 24,
        'WMZWMG' => 25,
        'WMGWMZ' => 26,
        'WMEWMG' => 27,
        'WMGWME' => 28,
        'WMRWMG' => 29,
        'WMGWMR' => 30,
        'WMUWMG' => 31,
        'WMGWMU' => 32,
        'WMZWMX' => 33,
        'WMXWMZ' => 34,
        'WMEWMX' => 35,
        'WMXWME' => 36,
        'WMRWMX' => 37,
        'WMXWMR' => 38,
        'WMUWMX' => 39,
        'WMXWMU' => 40
    );

    public function get_rate($currency_in, $currency_out, $amount_out) {
        if ($currency_in === $currency_out) {
            return $amount_out;
        }
        if (($dom = Cache::read($currency_out . $currency_in, 43200, 'ExchangerRu')) === false) {
            $dom = new DOMDocument;
            $dom->loadXML(curl_get_contents('https://wm.exchanger.ru/asp/XMLWMList.asp?exchtype='
                            . $this->exchtypes[$currency_out . $currency_in]));
            $dom = $dom->getElementsByTagName('WMExchnagerQuerys')->item(0);
            if (!$dom) {
                return null;
            }
            if ($dom->getAttribute('amountin') !== $currency_out || $dom->getAttribute('amountout') !== $currency_in) {
                log_error('$exchtypes[' . $currency_out . $currency_in . '] seems to contain invalid exchtype id');
                return null;
            }
            $dom = str_replace(',', '.', $dom->getElementsByTagName('query')->item(0)->getAttribute('outinrate'));
            Cache::write($dom, $currency_out . $currency_in, 'ExchangerRu');
        }
        return $amount_out * $dom / (100 - 0.8) * 100;
    }

}
