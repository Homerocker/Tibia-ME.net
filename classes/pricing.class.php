<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2014, Tibia-ME.net
 */
class Pricing extends ExchangerRuParser {

    const PRICES = array(
        'platinum' => array(
            100 => array(
                'price' => 0.99,
                'discount_pct' => 30
            ),
            210 => array(
                'price' => 1.99,
                'discount_pct' => 25
            ),
            700 => array(
                'price' => 4.99,
                'discount_pct' => 22
            ),
            2500 => array(
                'price' => 14.99,
                'discount_pct' => 20
            ),
            9000 => array(
                'price' => 39.99,
                'discount_pct' => 18
            )
        ),
        'premium' => array(
            7 => array(
                'price' => 1.99,
                'discount_pct' => 0
            ),
            30 => array(
                'price' => 4.99,
                'discount_pct' => 0
            ),
            60 => array(
                'price' => 7.99,
                'discount_pct' => 0
            ),
            120 => array(
                'price' => 14.99,
                'discount_pct' => 0
            )
        )
    );

    public function get_ISO_currency_code($wm_code) {
        switch ($wm_code) {
            case 'WME':
                return 'EUR';
            case 'WMZ':
                return 'USD';
            case 'WMU':
                return 'UAH';
            case 'WMR':
                return 'RUB';
        }
        return $wm_code;
    }

    public static function get_price($product, $amount, $currency) {
        if (isset(self::PRICES[$product][$amount])) {
            // get default price
            $price = self::PRICES[$product][$amount]['price'];
            if (!empty(self::PRICES[$product][$amount]['discount_pct'])) {
                $discount_modifier = 1 - self::PRICES[$product][$amount]['discount_pct'] / 100;
            }
        } else {
            // calculate price for custom amount of game currency
            $prices = self::PRICES[$product];
            krsort($prices);
            foreach ($prices as $amount1 => $prices1) {
                if ($amount >= $amount1) {
                    $price = $prices1['price'] * $amount / $amount1;
                }
                if (!empty($prices1['discount_pct'])) {
                    $discount_modifier = 1 - $prices1['discount_pct'] / 100;
                }
            }
        }

        // apply discount
        if (isset($discount_modifier)) {
            if ($product == 'platinum') {
                $gc = new GameCodes;
                if (empty($gc->get_overview()[$amount]) && $gc->get_total($gc->get_bundle($amount)) < $amount) {
                    $discount_modifier = 1;
                }
            }
            $price *= $discount_modifier;
        }

        // convert price to other currency if necessary
        if ($currency != 'WME') {
            $price = self::get_rate(($currency == 'FK' ? 'WMZ' : $currency), 'WME', $price);
        }
        return [
            'price' => round($price, 2),
            'discount_pct' => self::PRICES[$product][$amount]['discount_pct'] ?? 0
        ];
    }

}
