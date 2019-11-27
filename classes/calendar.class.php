<?php

class Calendar {

    private $event = [
        'id' => null,
        'title' => null,
        'description' => null,
        'start_day' => null,
        'start_month' => null,
        'start_year' => null,
        'end_day' => null,
        'end_month' => null,
        'is_confirmed' => 0
    ];
    private $errors = [];
    private $layout = [];

    public function __construct() {
        $this->event['start_day'] = date('d');
        $this->event['start_month'] = date('m');
    }

    public function set_event_id($event_id) {
        $sql = $GLOBALS['db']->query('SELECT * FROM calendar WHERE id = ' . $GLOBALS['db']->quote($event_id))->fetch_assoc();
        if (!$sql) {
            return false;
        }
        $this->event = $sql;
        return true;
    }

    public function set_event($array) {
        if (!$array) {
            return;
        }
        foreach ($array as $k => $v) {
            if (array_key_exists($k, $this->event)) {
                switch ($k) {
                    case 'id':
                        if (empty($v)) {
                            $v = null;
                        }
                        break;
                    case 'title':
                        $v = trim($v);
                        if (mb_strlen($v) > 64) {
                            $this->errors[] = _('Title is too long.');
                        }
                        break;
                    case 'description':
                        $v = trim($v);
                        break;
                    case 'start_day':
                        if (!ctype_digit((string) $v) || $v < 1 || $v > 31) {
                            $this->errors[] = _('Invalid start date.');
                        }
                        break;
                    case 'start_month':
                        if (!ctype_digit((string) $v) || $v < 1 || $v > 12) {
                            $this->errors[] = _('Invalid start date.');
                        }
                        break;
                    case 'start_year':
                        if (empty($v)) {
                            $v = null;
                            break;
                        }
                        if (!ctype_digit((string) $v) || $v < 2003 || $v > date('Y')
                                + 1) {
                            $this->errors[] = _('Invalid start date.');
                        }
                        break;
                    case 'end_day':
                        if (empty($v) && empty($array['end_month'])) {
                            $v = null;
                            break;
                        }
                        if (!ctype_digit((string) $v) || $v < 1 || $v > 31) {
                            $this->errors[] = _('Invalid end date.');
                        }
                        break;
                    case 'end_month':
                        if (empty($v) && empty($array['end_day'])) {
                            $v = null;
                            break;
                        }
                        if (!ctype_digit((string) $v) || $v < 1 || $v > 12) {
                            $this->errors[] = _('Invalid end date.');
                        }
                        break;
                    case 'is_confirmed':
                        $v = 1;
                        break;
                }
                $this->event[$k] = $v;
            }
        }
        if (empty($this->errors)) {
            if ($this->event['start_day'] > ($this->event['start_year'] == null ? 31
                                : cal_days_in_month(CAL_GREGORIAN,
                                    $this->event['start_month'],
                                    $this->event['start_year']))) {
                $this->errors[] = _('Invalid start date.');
            }
            if (isset($this->event['end_day'])) {
                if ($this->event['end_day'] > cal_days_in_month(CAL_GREGORIAN,
                                $this->event['end_month'],
                                $this->get_end_year($this->event['start_year'] ?? date('Y'), $this->event['start_month'], $this->event['end_month']))) {
                    $this->errors[] = _('Invalid end date.');
                }
                if (($this->event['start_month'] > $this->event['end_month'] && $this->event['start_month']
                        - $this->event['end_month'] < 10) || ($this->event['start_month']
                        < $this->event['end_month'] && $this->event['end_month']
                        - $this->event['start_month'] > 3)) {
                    $this->errors[] = _('Event duration is too long.');
                }
            }
        }
        return true;
    }

    public function get_event() {
        return $this->event;
    }

    public function save_event($array) {
        $this->set_event($array);
        if (!empty($errors = $this->get_errors())) {
            return $errors;
        }
        $event = $this->get_event();
        if ($event['id'] === null) {
            $GLOBALS['db']->query('INSERT INTO calendar (id, title, description, start_day, start_month, start_year, end_day, end_month, is_confirmed) VALUES (' . implode(',',
                            $GLOBALS['db']->quote($event)) . ')');
        } else {
            $GLOBALS['db']->query('UPDATE calendar SET ' . implode(',',
                            array_map(function($v, $k) {
                                return $k . '=' . $v;
                            }, $GLOBALS['db']->quote($event), array_keys($event))) . ' WHERE id = ' . $GLOBALS['db']->quote($event['id']));
        }
        return true;
    }

    private function get_start_year($start_m, $end_m) {
        if ($start_m >= date('n') - 1) {
            return date('Y');
        }
        if ($end_m >= date('n') - 1) {
            if ($start_m <= $end_m) {
                return date('Y');
            }
            return date('Y') + 1;
        }
        return date('Y') + 1;
    }

    private function get_end_year($start_y, $start_m, $end_m) {
        return ($end_m < $start_m - 1) ? $start_y + 1 : $start_y;
    }

    public function delete_event($event_id) {
        $GLOBALS['db']->query('DELETE FROM calendar WHERE id = ' . $GLOBALS['db']->quote($event_id));
        return $GLOBALS['db']->affected_rows > 0;
    }

    public function get_errors() {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function get_events() {
        if (empty($this->layout)) {
            return false;
        }
        $calendar_range = $layout = array_values(array_filter($this->layout));
        $start_m = (int) explode('-', $layout[0])[1];
        $end_m = (int) explode('-', $layout[count($layout) - 1])[1];
        $events = [];
        $calendar_range = [$calendar_range[0], $calendar_range[count($calendar_range)
        - 1]];
        $sql = $GLOBALS['db']->query('SELECT * FROM calendar WHERE ('
                . 'start_month IN (' . implode(',',
                        Date::listmonthsfromrange($start_m, $end_m)) . ')'
                . ' OR end_month IN (' . implode(',',
                        Date::listmonthsfromrange($start_m, $end_m)) . ')'
                . ' OR (start_month < ' . $start_m . ' AND end_month > ' . $end_m . ')'
                . ') ORDER BY start_year, start_month, start_day');
        while ($row = $sql->fetch_assoc()) {
            if ($row['start_day'] != null) {
                $row['start_day'] = sprintf('%02d', $row['start_day']);
            }
            if ($row['start_month'] != null) {
                $row['start_month'] = sprintf('%02d', $row['start_month']);
            }
            if ($row['end_day'] != null) {
                $row['end_day'] = sprintf('%02d', $row['end_day']);
                $row['end_month'] = sprintf('%02d', $row['end_month']);
            }
            if ($row['start_year'] == null) {
                $row['start_year'] = $this->get_start_year($row['start_month'],
                        $row['end_month']);
            }
            $row['start_date'] = $row['start_year'] . '-' . $row['start_month'] . '-' . $row['start_day'];
            if ($row['start_date'] > $calendar_range[1]) {
                // event starts after calendar date range
                continue;
            }
            $row['end_date'] = $row['end_month'] == null ? null : $this->get_end_year($row['start_year'], $row['start_month'], $row['end_month']) . '-' . $row['end_month'] . '-' . $row['end_day'];
            if ($row['end_date'] !== null && $row['end_date'] < $calendar_range[0]) {
                // event ends before calendar date range
                continue;
            }
            if ($row['end_date'] === null && $row['start_date'] < $calendar_range[0]) {
                // one-day event starts before calendar date range
                continue;
            }
            $events[] = $row;
        }
        return $events;
    }

    public function get_layout($m) {
        $y = $this->get_year($m);
        // day of the week of the first day in current month
        $dow_first = date('N', strtotime($y . '-' . $m . '-01'));
        if ($dow_first != 1) {
            if ($m == Date::get_m(-1)[0]) {
                for ($i = 1; $i < $dow_first; ++$i) {
                    $this->layout[] = null;
                }
            } else {
                // previous month and year numbers
                list($m_prev, $y_prev) = Date::get_m(-1, $m, $y);
                // number of days in previous month
                $dim_prev = cal_days_in_month(CAL_GREGORIAN, $m_prev, $y_prev);
                for ($i = $dim_prev - $dow_first + 2; $i <= $dim_prev; ++$i) {
                    $this->layout[] = $y_prev . '-' . $m_prev . '-' . sprintf('%02d',
                                    $i);
                }
            }
        }
        // number of days in current month
        $dim = cal_days_in_month(CAL_GREGORIAN, $m, $y);
        for ($i = 1; $i <= $dim; ++$i) {
            $this->layout[] = $y . '-' . $m . '-' . sprintf('%02d', $i);
        }
        // day of the week of the last day in current month
        $dow_last = date('N', strtotime($y . '-' . $m . '-' . $dim));
        if ($dow_last != 7) {
            if ($m == Date::get_m(10)) {
                for ($i = 7; $i > $dow_last; --$i) {
                    $this->layout[] = null;
                }
            } else {
                list($m_next, $y_next) = Date::get_m(1, $m, $y);
                // how many days of the next month should be displayed
                $m_next_end = 7 - $dow_last;
                for ($i = 1; $i <= $m_next_end; ++$i) {
                    $this->layout[] = $y_next . '-' . $m_next . '-' . sprintf('%02d',
                                    $i);
                }
            }
        }
        return $this->layout;
    }

    public static function get_month_name($month_index) {
        switch ($month_index) {
            case 1:
                return _('January');
            case 2:
                return _('February');
            case 3:
                return _('March');
            case 4:
                return _('April');
            case 5:
                return _('May');
            case 6:
                return _('June');
            case 7:
                return _('July');
            case 8:
                return _('August');
            case 9:
                return _('September');
            case 10:
                return _('October');
            case 11:
                return _('November');
            case 12:
                return _('December');
            default:
                return false;
        }
    }

    public static function get_year($m) {
        if ($m >= date('n') - 1) {
            return date('Y');
        }
        if (date('n') == 1) {
            if ($m == 12) {
                return date('Y') - 1;
            }
            return date('Y');
        }
        return date('Y') + 1;
    }

}
