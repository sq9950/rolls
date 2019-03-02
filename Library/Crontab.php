<?php
/**
 * @node_name
 * Desc: 功能描述
 * Created by PhpStorm.
 * User: <xuyanlong@yundun.com>
 * Date: 2018/11/8 11:53
 */
namespace Library;
class Crontab
{
    private $_crontab = '';
    private $_sched = [];
    private $_current = null;
    private $_nextTime = null;
    static private $_cfg = [
        "second" => ["min" => 0, "max" => 59],
        "minute" => ["min" => 0, "max" => 59],
        "hour"   => ["min" => 0, "max" => 23],
        "day"    => ["min" => 1, "max" => 31],
        "month"  => ["min" => 1, "max" => 12],
        "week"   => ["min" => 0, "max" => 6 ],
    ];

    static public function instance($crontab, $current) {
        return new self($crontab, $current);
    }

    public function __construct($crontab, $current = null) {
        $this->_crontab = $crontab;
        $this->_sched = $this->_formatCrontab($crontab);
        $current = $current ? strtotime($current) : time();
        $this->_current = $current;
    }

    private function _formatCrontab($crontab) {
        $result = explode(' ', $crontab);
        return [
            'second' => $result[0],
            'minute' => $result[1],
            'hour'   => $result[2],
            'day'    => $result[3],
            'month'  => $result[4],
            'week'   => $result[5],
        ];
    }

    static public function valid($crontab) {
        // *         */      6        1,2           4-5
        $regOne = '((?:\*)|(?:\*/\d{1,2})|(?:\d{1,2})|(?:\d{1,2},\d{1,2}?)|(?:\d{1,2}-\d{1,2}))';
        $reg = "`^{$regOne}\s{$regOne}\s{$regOne}\s{$regOne}\s{$regOne}\s{$regOne}$`";
        if(!preg_match($reg, $crontab)) return 0;
        preg_match_all($reg, $crontab, $matches);
        $sched = [
            'second' => $matches[1][0],
            'minute' => $matches[2][0],
            'hour'   => $matches[3][0],
            'day'    => $matches[4][0],
            'month'  => $matches[5][0],
            'week'   => $matches[6][0],
        ];
        foreach(self::$_cfg as $key => $row) {
            if($sched[$key] == "*") continue;
            if(stripos($sched[$key], ',')) {        //4,5,6
                $values = explode(",", $sched[$key]);
                foreach($values as $value) if($value < $row["min"] || $value > $row["max"]) return 0;
            }
            if(stripos($sched[$key], '-')) {        //4-6
                list($start, $end) = explode("-", $sched[$key]);
                if($start > $end) return 0;
                if($start < $row["min"] || $end > $row["max"]) return 0;
            }
            if(stripos($sched[$key], '/')) {        //*/2
                $beishu = substr($sched[$key], 2);
                if(!$beishu) return 0;
            }
        }
        return 1;
    }

    public function setCurrent($current) {
        $this->_current = strtotime($current);
        return $this;
    }

    public function getContab() {
        return $this->_crontab;
    }

    public function nextTime() {
        return $this->_calcNextTime();
    }

    private function _calcNextTime() {
        $currentYear   = date('Y', $this->_current);
        $currentMonth  = date('m', $this->_current);
        $currentWeek   = date('w', $this->_current);
        $currentDay    = date('d', $this->_current);
        $currentHour   = date('G', $this->_current);
        $currentMinute = intval(date('i', $this->_current));
        $currentSecond = intval(date('i', $this->_current));
        $nextYear      = date('Y', $this->_current);

        //月
        $nextMonth = $this->_calcNextMonth($this->_sched['month'], $this->_current);
        if($nextMonth < $currentMonth && $nextYear == $currentYear) $nextYear = $currentYear + 1;
        //周
        $nextWeek = $this->_calcNextWeek($this->_sched['week'], $this->_current);
        //日
        $nextDay = $this->_calcNextDay($this->_sched['day'], $this->_current);
        if($nextDay < $currentDay && $nextMonth == $currentMonth) $nextMonth = $nextMonth + 1;
        //时
        $nextHour = $this->_calcNextHour($this->_sched['hour'], $this->_current);
        if($nextHour < $currentHour && $nextDay == $currentDay) $nextDay = $nextDay + 1;
        //分
        $nextMinute = $this->_calcNextMinute($this->_sched['minute'], $this->_current);
        if($nextMinute < $currentMinute && $nextHour == $currentHour) $nextHour = $nextHour + 1;
        //秒
        $nextSecond = $this->_calcNextSecond($this->_sched['second'], $this->_current);
        if($nextSecond < $currentSecond && $nextMinute == $currentMinute) $nextMinute = $nextMinute + 1;

        $diffDay = 0;
        if($nextWeek != $currentWeek) {
            if($nextWeek > $currentWeek) $diffDay = $nextWeek - $currentWeek;
            if($nextWeek < $currentWeek) $diffDay = 7 - $currentWeek + $nextWeek;
        }
        $nextTime = strtotime("{$nextYear}-{$nextMonth}-{$nextDay} {$nextHour}:{$nextMinute}:{$nextSecond}") + $diffDay * 86400;
        return date('Y-m-d H:i:s', $nextTime);
    }

    private function _filterMin($rows, $minValue) {
        $maxRows = [];
        foreach($rows as $value) if($value > $minValue) $maxRows[] = $value;
        return $maxRows;
    }

    private function _calcNextMonth($schedMonth, $now) {
        $rows = $this->_calcUse($schedMonth, 1, 12);
        if(!$rows) return date('m', $now);
        $current = date('m', $now);
        $used = $this->_filterMin($rows, $current);
        return $used ? $used[0] : $rows[0];
    }

    private function _calcNextWeek($schedWeek, $now) {
        $rows = $this->_calcUse($schedWeek, 0, 6);
        if(!$rows) return date('w', $now);
        $current = date('w', $now);
        $used = $this->_filterMin($rows, $current);
        return $used ? $used[0] : $rows[0];
    }

    private function _calcNextDay($schedDay, $now) {
        $rows = $this->_calcUse($schedDay, 1, date('t', $now));
        if(!$rows) return date('d', $now);
        $current = date('d', $now);
        $used = $this->_filterMin($rows, $current);
        return $used ? $used[0] : $rows[0];
    }

    private function _calcNextHour($schedHour, $now) {
        $rows = $this->_calcUse($schedHour, 0, 23);
        if(!$rows) return date('G', $now);
        $current = date('G', $now);
        $used = $this->_filterMin($rows, $current);
        return $used ? $used[0] : $rows[0];
    }

    private function _calcNextMinute($schedMinute, $now) {
        $rows = $this->_calcUse($schedMinute, 0, 59);
        if(!$rows) return intval(date('i', $now));
        $current = intval(date('i', $now));
        $used = $this->_filterMin($rows, $current);
        return $used ? $used[0] : $rows[0];
    }

    private function _calcNextSecond($schedSecond, $now) {
        $rows = $this->_calcUse($schedSecond, 0, 59);
        if(!$rows) return intval(date('s', $now));
        $current = intval(date('s', $now));
        $used = $this->_filterMin($rows, $current);
        return $used ? $used[0] : $rows[0];
    }

    private function _calcUse($schedValue, $start, $end) {
        $useValues = [];
        if(is_numeric($schedValue)) {                //4
            $useValues = [$schedValue];
        } elseif(stripos($schedValue, ',')) {        //4,5,6
            $useValues = explode(',', $schedValue);
        } elseif(stripos($schedValue, '-')) {        //4-6
            list($min, $max) = explode('-', $schedValue);
            for($i = $min; $i <= $max; $i++) $useValues[] = $i;
        } elseif(stripos($schedValue, '/')) {        //*/2
            $beishu = substr($schedValue, 2);
            for($i = $start; $i <= $end; $i++) if($i % $beishu == 0) $useValues[] = $i;
        } else {                                     //*
        }
        return $useValues;
    }
}
