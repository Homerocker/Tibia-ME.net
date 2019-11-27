<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2014, Tibia-ME.net
 */
class Pricing extends ExchangerRuParser
{

    // fixed fee amount in WMR
    const fee_fixed = 40;
    // fee %/100
    const fee = 0.06;

    public static $pricing = array(
        'premium' => array(
            30 => array(
                'currency' => 'WME',
                'price' => 4.99,
                'no_fee' => false,
                'discount_pct' => 0
            ),
            120 => array(
                'currency' => 'WME',
                'price' => 14.99,
                'no_fee' => false,
                'discount_pct' => 0
            )
        ),
        'platinum' => array(
            100 => array(
                'currency' => 'WME',
                'price' => 0.99,
                'no_fee' => true,
                'discount_pct' => 30
            ),
            210 => array(
                'currency' => 'WME',
                'price' => 1.99,
                'no_fee' => true,
                'discount_pct' => 25
            ),
            700 => array(
                'currency' => 'WME',
                'price' => 4.99,
                'no_fee' => true,
                'discount_pct' => 22
            ),
            2500 => array(
                'currency' => 'WME',
                'price' => 14.99,
                'no_fee' => true,
                'discount_pct' => 20
            ),
            9000 => array(
                'currency' => 'WME',
                'price' => 39.99,
                'no_fee' => true,
                'discount_pct' => 18
            )
        )
    );

    /**
     *
     * @param string $product 'premium' or 'platinum'
     * @return array amount => currency => price
     */
    public function get_prices($product)
    {
        $prices = array();
        $gc_overview = (new GameCodes)->get_overview();
        foreach (self::$pricing[$product] as $amount => $pricing) {
            if (($pricing['no_fee'] || $pricing['discount_pct']) && !isset($gc_overview[$product][$amount]) && ($product != 'platinum' || GameCodes::get_platinum_sum($gc_overview) < $amount)) {
                // if gamecodes for specified product and amount are not available, enable fee and disable discount
                // if platinum amount is 100 or 210 - skip it as we cannot buy it from official website
                if ($product == 'platinum' && !in_array($amount, [700, 2500, 9000])) {
                    continue;
                }
                $pricing['no_fee'] = false;
                $pricing['discount_pct'] = 0;
            }
            $prices[$amount] = array();
            foreach (array('WMR', 'WMU', 'WMZ', 'WME') as $currency) {
                if ($currency !== $pricing['currency']) {
                    $prices[$amount][$currency] = $this->get_rate($currency, $pricing['currency'], $pricing['price']);
                } else {
                    $prices[$amount][$currency] = $pricing['price'];
                }
                if (!$pricing['no_fee']) {
                    $prices[$amount][$currency] = $this->apply_fees($prices[$amount][$currency], $currency);
                }
                if ($pricing['discount_pct']) {
                    $prices[$amount][$currency] -= $prices[$amount][$currency] * $pricing['discount_pct'] / 100;
                }
                $prices[$amount][$currency] = round($prices[$amount][$currency], 2);
                if ($pricing['discount_pct']) {
                    $prices[$amount][$currency] .= ' <span class="small">(-' . $pricing['discount_pct'] . '%)</span>';
                }
            }
        }
        foreach ($prices as $product_amount => $pricing) {
            foreach ($pricing as $currency => $amount) {
                $new_currency = $this->currency_code_convert($currency);
                unset($prices[$product_amount][$currency]);
                $prices[$product_amount][$new_currency] = $amount;
            }
        }
        return $prices;
    }

    private function apply_fees($price, $currency)
    {
        return $price / (100 - 0.8) * 100 + $price * self::fee + (($currency == 'WMR') ? self::fee_fixed : $this->get_rate($currency, 'WMR', self::fee_fixed));
    }

    private function currency_code_convert($wm_code)
    {
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

}
