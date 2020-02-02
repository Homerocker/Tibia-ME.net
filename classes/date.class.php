<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2017, Tibia-ME.net
 */
class Date
{

    /**
     *
     * @return array numeric representations of month and year, month is in date("m") format with leading zero
     */
    public static function get_m($month_offset = 0, $m = null, $y = null)
    {
        if ($m == null) {
            $m = date('n');
        }
        if ($y == null) {
            $y = date('Y');
        }
        for ($i = abs($month_offset); $i > 0; --$i) {
            $m += get_sign($month_offset);
            if ($m == 0) {
                $m = 12;
                --$y;
            } elseif ($m == 13) {
                $m = 1;
                ++$y;
            }
        }
        $m = sprintf('%02d', $m);
        return [$m, $y];
    }

    /**
     * @return array lists of months numbers
     */
    public static function listmonthsfromrange(int $start, int $end)
    {
        if ($start == $end) {
            return [$start];
        }
        $months = [];
        $i = $start;
        do {
            $months[] = $i;
            $i = $i == 12 ? 1 : $i + 1;
        } while ($i != $end);
        $months[] = $end;
        return $months;
    }

    public static function modify($Ymd, $day_offset = 0)
    {
        if (!$Ymd) {
            return null;
        }
        if ($day_offset == 0) {
            return $Ymd;
        }
        $date = DateTime::createFromFormat('Y-m-d', $Ymd);
        if (get_sign($day_offset) === 1) {
            $day_offset = '+' . $day_offset;
        }
        $date->modify($day_offset . ' days');
        return $date->format('Y-m-d');
    }

    /**
     * Checks if timezone changes during the specified day (DST starts/ends).
     * @param $ymd date to check in YYYY-MM-DD format
     * @return bool|array DST change timestamp if DST changes on specified date, otherwise false
     */
    public static function get_timezone_transition($ymd)
    {
        $transitions = (new DateTimeZone(date_default_timezone_get()))->getTransitions(strtotime($ymd), strtotime(self::modify($ymd, 1)));
        return count($transitions) < 2 ? false : [
            'ts' => $transitions[1]['ts'],
            'offset' => $transitions[1]['offset'] - $transitions[0]['offset']
        ];
    }

    /**
     * @deprecated
     */
    public static function get_timezone($ymd, $hour = 0, $timezone_name = null) {
        $transitions = (new DateTimeZone($timezone_name ?? date_default_timezone_get()))->getTransitions(strtotime($ymd), strtotime(self::modify($ymd, 1)));
    }
}
