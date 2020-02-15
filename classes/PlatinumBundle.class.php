<?php


class PlatinumBundle extends GameCodes
{
    private $bundle = [];
    private $amount = 0;
    public function __construct($required_amount, $codes = null, $depth = 0) {
        if($codes === null) {
            $codes = $this->get_overview();
            krsort($codes, SORT_NUMERIC);
            foreach ($codes as $k => &$v) {
                $v = ['amount' => $k, 'n' => $v];
            }
            $codes = array_values($codes);
        }
        $val1 = 0;
        for ($i = (int) min(ceil($required_amount / $codes[$depth]['amount']), $codes[$depth]['n']); $i >= 0; --$i) {
            $val2 = $codes[$depth]['amount'] * $i;
            $bundle2 = [$codes[$depth]['amount'] => $i];
            if ($val2 == $required_amount) {
                $this->bundle = $bundle2;
                $this->amount = $val2;
                break;
            }
            if (isset($codes[$depth + 1]) && $val2 < $required_amount) {
                $bundle3 = new $this($required_amount - $val2, $codes, $depth + 1);
                $bundle2 = array_replace($bundle2, $bundle3->get_bundle());
                $val2 += $bundle3->get_amount();
            }
            if (($val1 < $required_amount && $val2 > $val1) || ($val1 > $required_amount && $val2 >= $required_amount && $val2 < $val1)) {
                $this->bundle = $bundle2;
                $this->amount = $val2;
                if ($val2 == $required_amount) {
                    break;
                } else {
                    $val1 = $val2;
                }
            }
        }
    }

    public function get_amount() {
        return $this->amount;
    }

    public function get_bundle() {
        return $this->bundle;
    }

    /**
     * Converts amount of gamecodes into array of encrypted gamecodes in gamecodes bundle.
     * @return bool|array false on failure
     */
    private function get_codes() {
        $sql = $GLOBALS['db']->prepare('SELECT code FROM gamecodes WHERE amount = ? AND nickname IS NULL LIMIT ?', 'ii');
        $bundle = [];
        foreach ($this->bundle as $amount => $n) {
            $bundle[$amount] = [];
            $result = $sql->execute($amount, $n);
            while ($code = $result->fetch_row()[0]) {
                $bundle[$amount][] = $code;
            }
            if (count($bundle[$amount]) != $n) {
                return false;
            }
        }
        return $bundle;
    }

    /**
     * @param $nickname
     * @param $world
     * @return bool|int true on success, activated amount if some of gamecodes activation failed
     */
    public function activate($nickname, $world)
    {
        $GLOBALS['db']->query('LOCK TABLES gamecodes WRITE');
        if (!($bundle = $this->get_codes($this->bundle))) {
            $GLOBALS['db']->query('UNLOCK TABLES');
            return false;
        }
        $sql = $GLOBALS['db']->prepare('UPDATE gamecodes'
            . ' SET nickname = \'' . $nickname . '\', world = ' . $world . ', used_timestamp = '
            . $_SERVER['REQUEST_TIME'] . ', modified_mod_id = ' . $_SESSION['user_id']
            . ', failed = ? WHERE code = ?', 'is');
        $activated = 0;
        foreach ($bundle as $amount => $codes) {
            foreach ($codes as $code) {
                if (TibiameComParser::gamecode_activate(decrypt($code), $nickname, $world)) {
                    $sql->execute($code, 0);
                    $activated += $amount;
                } else {
                    $sql->execute($code, 1);
                }
            }
        }
        $GLOBALS['db']->query('UNLOCK TABLES');
        return ($activated == $this->amount) ? true : $activated;
    }

    public function get_price($currency) {
        return Pricing::get_price('platinum', $this->amount, $currency);
    }
}