<?php

class calendar_day {

    private $y;
    private $m;
    private $d;
    private $stamp;
    private $weekday;
    private $tag;

    public function __construct($y, $m, $d, $tag=null) {
        if (!checkdate($m, $d, $y)) {
            throw new exception('invalid date specified');
        }
        $this->y = $y;
        $this->m = $m;
        $this->d = $d;
        $this->stamp = mktime(0,0,0, $m, $d, $y);
        $this->weekday = date('w', $this->stamp); // 0=sunday, 1=monday, ..., 6=saturday
        $this->tag = $tag;
    }

    public function thisDay() {
        return $this->d;
    }

    public function thisMonth() {
        return $this->m;
    }

    public function thisYear() {
        return $this->y;
    }

    public function getStamp() {
        return $this->stamp;
    }
    
    public function isTagged() {
        return !is_null($this->tag);
    }

    public function getTag() {
        return $this->tag;
    }

    public function isFirst() {
        return $this->weekday == calendar_month::first_day_of_week;
    }

    public function isLast() {
        $last_day = calendar_month::first_day_of_week == 0 ? 6 : calendar_month::first_day_of_week - 1;
        return $this->weekday == $last_day;
    }

    public function prevDay() {
        list($y, $m, $d) = explode('-', date('y-m-d', $this->stamp - 86400));
        return new calendar_day($y, $m, $d);
    }

    public function nextDay() {
        list($y, $m, $d) = explode('-', date('y-m-d', $this->stamp + 86400));
        return new calendar_day($y, $m, $d);
    }

    public static function sorter($a, $b) {
        return strcmp($a->getStamp(), $b->getStamp());
    }
}

?>
