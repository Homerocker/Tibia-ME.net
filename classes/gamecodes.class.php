<?php

class GameCodes extends TibiameComParser {

    private $errors = array();
    public $codes, $type, $amount, $nickname, $world, $mode, $multiplier;

    const MODE_ACTIVATE_CONFIRMATION = 1, MODE_ACTIVATE_CONFIRMED = 2;

    public function __construct() {
        if (isset($_GET['nickname']) && isset($_GET['world']) && isset($_GET['code_type'])
                && isset($_GET['multiplier']) && empty($this->errors) && Perms::get(Perms::GAMECODES_ACTIVATE)) {
            $this->mode = self::MODE_ACTIVATE_CONFIRMATION;
        } elseif (isset($_POST['nickname']) && isset($_POST['world']) && isset($_POST['code_type'])
                && isset($_POST['multiplier']) && empty($this->errors) && Perms::get(Perms::GAMECODES_ACTIVATE)) {
            $this->mode = self::MODE_ACTIVATE_CONFIRMED;
        }
    }

    public function add($codes, $type, $amount) {
        list($this->codes, $this->type, $this->amount) = func_get_args();
        if ($type !== 'platinum' && $type !== 'premium') {
            $this->errors[] = _('Invalid game codes type.');
        }
        if (!ctype_digit((string) $amount) || ($type === 'platinum'
                && !in_array($amount, [100, 210, 700, 2500])) || ($type === 'premium'
                && !in_array($amount, [30, 120]))) {
            $this->errors[] = _('Invalid amount.');
        }
        $this->codes = $codes;
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
        $GLOBALS['db']->query('INSERT INTO gamecodes (code, type, amount, added_mod_id) VALUES (\'' . implode('\', \'' . $type . '\', ' . $amount . ', ' . $_SESSION['user_id'] . '), (\'',
                        $codes) . '\', \'' . $type . '\', ' . $amount . ', ' . $_SESSION['user_id'] . ')');
        Document::reload_msg(sprintf(ngettext('%d game code added.',
                                '%d game codes added.', count($codes)),
                        count($codes)));
    }

    /**
     * 
     * @return array all available game codes
     */
    public function get_codes() {
        return $GLOBALS['db']->query('SELECT code, type, amount, added_mod_id, failed'
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
            if (isset($overview[$code['type']][$code['amount']])) {
                ++$overview[$code['type']][$code['amount']];
            } else {
                $overview[$code['type']][$code['amount']] = 1;
            }
        }
        foreach ($overview as $type => $amounts) {
            ksort($amounts);
            $overview[$type] = $amounts;
        }
        return $overview;
    }

    public function activate($code_type, $nickname, $world, $multiplier,
            $confirmed = false) {
        $this->nickname = $nickname;
        $this->world = $world;
        if (strpos($code_type, ':') !== false) {
            list($this->type, $this->amount) = explode(':', $code_type);
        }
        $this->multiplier = $multiplier;
        if (!Auth::CheckNickname($nickname)) {
            $this->errors[] = _('Invalid nickname.');
        }
        if (!Auth::check_world($world)) {
            $this->errors[] = _('Invalid world.');
        }
        if (($this->type !== 'platinum' && $this->type !== 'premium') || !ctype_digit($this->amount)) {
            $this->errors[] = _('Invalid game code type.');
        } else {
            $codes = $this->get_overview();
            if (!isset($codes[$this->type][$this->amount])) {
                $this->errors[] = _('Game code is not available.');
            }
            if (!ctype_digit((string) $multiplier) || $multiplier < 1) {
                $this->errors[] = _('Invalid multiplier.');
            } elseif ($codes[$this->type][$this->amount] < $this->multiplier) {
                $this->errors[] = _('No enough game codes available.');
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
        $sql = $GLOBALS['db']->query('SELECT code FROM gamecodes WHERE type = \'' . $this->type . '\' AND amount = ' . $this->amount . ' AND nickname IS NULL LIMIT ' . $multiplier);
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
    
    public static function get_platinum_sum($overview = null) {
        if ($overview === null) {
            $overview = (new self)->get_overview();
        }
        if (!isset($overview['platinum'])) {
            return 0;
        }
        $sum = 0;
        foreach ($overview['platinum'] as $amount => $count) {
            $sum+=$amount*$count;
        }
        return $sum;
    }

}
