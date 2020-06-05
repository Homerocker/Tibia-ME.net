<?php

class GameCodes extends TibiameComParser {

    private $errors = array();
    public $codes, $amount, $nickname, $world, $mode;

    const MODE_ACTIVATE_CONFIRMATION = 1, MODE_ACTIVATE_CONFIRMED = 2;

    public function __construct() {
        if (isset($_GET['nickname']) && isset($_GET['world']) && isset($_GET['amount']) && empty($this->errors) && Perms::get(Perms::GAMECODES_ACTIVATE)) {
            $this->mode = self::MODE_ACTIVATE_CONFIRMATION;
        } elseif (isset($_POST['nickname']) && isset($_POST['world']) && isset($_POST['amount'])
                && empty($this->errors) && Perms::get(Perms::GAMECODES_ACTIVATE)) {
            $this->mode = self::MODE_ACTIVATE_CONFIRMED;
        }
    }

    public function add($codes, $amount) {
        $this->codes = $codes;
        $this->amount = $amount;
        if (!ctype_digit((string) $amount) || !in_array($amount, [100, 210, 700, 2500])) {
            $this->errors[] = _('Invalid amount.');
        }
        $codes = explode("\n", $codes);
        foreach ($codes as $i => $code) {
            $codes[$i] = $code = trim($code);
            if ($code === '') {
                unset($codes[$i]);
                continue;
            } elseif (!$this->is_valid($code)) {
                $this->errors[] = _('Some of game codes are invalid.');
                break;
            }
        }
        if (empty($codes)) {
            $this->errors[] = _('No valid game codes specified.');
        }
        foreach ($codes as $i => $code) {
            $codes[$i] = $code = encrypt(strtoupper(implode('-',
                                    str_split(str_replace('-', '', $code), 5))));
            if ($GLOBALS['db']->query('SELECT COUNT(*) FROM gamecodes'
                            . ' WHERE code = \'' . $code . '\'')->fetch_row()[0]
                    != 0) {
                $this->errors[] = sprintf(_('%s is duplicate.'), decrypt($code));
            }
        }
        if (!empty($this->errors)) {
            Document::msg($this->errors);
            return;
        }
        $GLOBALS['db']->query('INSERT INTO gamecodes (code, amount, added_mod_id) VALUES (\'' . implode('\', ' . $amount . ', ' . $_SESSION['user_id'] . '), (\'',
                        $codes) . '\', ' . $amount . ', ' . $_SESSION['user_id'] . ')');
        Document::reload_msg(sprintf(ngettext('%d game code added.',
                                '%d game codes added.', count($codes)),
                        count($codes)));
    }

    /**
     * 
     * @return array all available game codes
     */
    public function get_codes() {
        return $GLOBALS['db']->query('SELECT code, amount, added_mod_id, failed'
                . ' FROM gamecodes WHERE nickname IS NULL')->fetch_all(MYSQLI_ASSOC);
    }

    public function get_history() {
        $sql = $GLOBALS['db']->query('SELECT type, amount, nickname, world, code'
                . ', used_timestamp, modified_mod_id, failed'
                . ' FROM gamecodes WHERE nickname IS NOT NULL'
                . ' ORDER BY used_timestamp DESC');
        $data = array();
        while ($row = $sql->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function get_overview($codes = null) {
        if ($codes === null) {
            $codes = $this->get_codes();
        }
        $overview = array();
        foreach ($codes as $code) {
            $overview[$code['amount']] = ($overview[$code['amount']] ?? 0) + 1;
        }
        ksort($overview);
        return $overview;
    }

    public function activate($amount, $nickname, $world,
            $confirmed = false) {
        $this->amount = $amount;
        $this->nickname = $nickname;
        $this->world = $world;
        if (!Auth::CheckNickname($nickname)) {
            $this->errors[] = _('Invalid nickname.');
        }
        if (!Auth::check_world($world)) {
            $this->errors[] = _('Invalid world.');
        }
        // @todo check if required bundle still exists before confirming payment
        if (!ctype_digit($this->amount)) {
            $this->errors[] = _('Invalid amount.');
        } else {
            $codes = $this->get_bundle($amount);
            if ($this->get_platinum_sum($codes) != $amount) {
                $this->errors[] = _('Requested game codes are no longer available.');
            }
        }
        if (!empty($this->errors)) {
            Document::msg($this->errors);
            return false;
        }

        if (!$confirmed) {
            return true;
        }

        $GLOBALS['db']->query('LOCK TABLES gamecodes WRITE');
        $sql = $GLOBALS['db']->query('SELECT code FROM gamecodes WHERE amount = ' . $this->amount . ' AND nickname IS NULL LIMIT ' . $multiplier);
        if ($GLOBALS['db']->affected_rows != $multiplier) {
            Document::msg($this->errors[] = _('No enough game codes available.'));
            $GLOBALS['db']->query('UNLOCK TABLES');
            return false;
        }
        if ($multiplier > 1) {
            $activated_count = 0;
        }
        while ($code = $sql->fetch_row()[0]) {
            $activated = $this->gamecode_activate(decrypt($code), $nickname,
                    $world);
            if ($activated === true) {
                $GLOBALS['db']->query('UPDATE gamecodes'
                        . ' SET nickname = \'' . $nickname . '\', world = '
                        . $world . ', used_timestamp = ' . $_SERVER['REQUEST_TIME']
                        . ', modified_mod_id = ' . $_SESSION['user_id']
                        . ', failed = 0 WHERE code = \'' . $code . '\'');
                if ($multiplier > 1) {
                    ++$activated_count;
                    if ($activated_count == $multiplier) {
                        $GLOBALS['db']->query('UNLOCK TABLES');
                        Document::reload_msg(sprintf(ngettext('%d game code has been activated.', '%d game codes have been activated.',
                                                $activated_count), $activated_count));
                        return true;
                    }
                } else {
                    $GLOBALS['db']->query('UNLOCK TABLES');
                    Document::reload_msg(_('Game code has been activated.'));
                    return true;
                }
            } else {
                $GLOBALS['db']->query('UPDATE gamecodes'
                        . ' SET nickname = \'' . $nickname . '\', world = '
                        . $world . ', used_timestamp = ' . $_SERVER['REQUEST_TIME']
                        . ', modified_mod_id = ' . $_SESSION['user_id']
                        . ', failed = 1 WHERE code = \'' . $code . '\'');
                Document::msg($this->errors[] = $activated);
                if ($multiplier > 1) {
                    Document::msg(sprintf(_('%d of %d game codes have been activated.'),
                                    $activated_count, $multiplier));
                    $this->multiplier = $this->multiplier - $activated_count;
                }
                $GLOBALS['db']->query('UNLOCK TABLES');
                return false;
            }
        }
    }

    /**
     * Checks whether string is a valid game code.
     * @param string $code either plain or encrypted game code
     * @return boolean
     */
    private function is_valid($code) {
        return (preg_match('/^[\da-z]{5}\-?[\da-z]{5}\-?[\da-z]{5}\-?[\da-z]{5}$/i',
                        $code) || preg_match('/^[\da-z]{5}\-?[\da-z]{5}\-?[\da-z]{5}\-?[\da-z]{5}$/i',
                        decrypt($code)));
    }
    
    public function get_mode() {
        return empty($this->errors) ? $this->mode : 0;
    }
    
    public static function get_total($codes = null) {
        if ($codes === null) {
            $codes = (new self)->get_overview();
        }
        $total = 0;
        foreach ($codes as $amount => $n) {
            $total += $amount * $n;
        }
        return $total;
    }
    
    public function get_bundle($required_amount) {
        $codes = $this->get_overview();
        krsort($codes, SORT_NUMERIC);
        array_walk($codes, function(&$v, $k) {
            $v = ['amount' => $k, 'n' => $v];
        });
        $codes = array_values($codes);
        $result = [];
        foreach ($codes as $i => $v) {
            if ($required_amount < self::get_total($result)) {
                do {
                    $result2 = $result;
                    if (empty($result2[$codes[$i - 1]['amount']])) {
                        break;
                    }
                    --$result2[$codes[$i - 1]['amount']];
                    
                    $current_codes_n = min(ceil(($required_amount - self::get_total($result2)) / $v['amount']), $v['n']);
                    $result2[$v['amount']] = ($result2[$v['amount']] ?? 0) + $current_codes_n;
                    if (self::get_total($result2) < self::get_total($result)) {
                        $result = $result2;
                    } else {
                        break;
                    }
                } while (true);
            } else {
                $current_codes_n = min(ceil(($required_amount - self::get_total($result)) / $v['amount']), $v['n']);
                $result[$v['amount']] = $current_codes_n;
            }
            if ($required_amount == self::get_total($result)) {
                break;
            }
        }
        return $result;
    }

}
