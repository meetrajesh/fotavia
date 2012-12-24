<?php

class calendar_month {

    private $y;
    private $m;
    private $stamp;

    // all the days needed to fill up this {7 x [4,5,6]} month
    private $days;

    const first_day_of_week = 0; // sunday

    public function __construct($y, $m) {
        if (!checkdate($m, 1, $y)) {
            throw new exception('invalid month specified');
        }
        $this->y = $y;
        $this->m = $m;
        $this->stamp = mktime(0,0,0, $m, 1, $y);
    }

    public function build($tagged_dates) {
        usort($tagged_dates, array('calendar_day', 'sorter'));
        $num_days_in_month = date('t', $this->stamp);
        for ($i=1; $i <= $num_days_in_month; $i++) {
            $tag = null;
            if (!empty($tagged_dates) && $this->y == $tagged_dates[0]->thisYear() && $this->m == $tagged_dates[0]->thisMonth() && $i == $tagged_dates[0]->thisDay()) {
                $tag = $tagged_dates[0]->getTag();
                array_shift($tagged_dates);
            }
            $this->days[] = new calendar_day($this->y, $this->m, $i, $tag);
        }
        // prefix the month with days from the previous month
        $prev_day = $this->days[0];
        while (!$prev_day->isFirst()) {
            $prev_day = $this->days[0]->prevDay();
            array_unshift($this->days, $prev_day);
        }
        // append days from the next month
        $next_day = array_last($this->days);
        while (!$next_day->isLast()) {
            $next_day = array_last($this->days)->nextDay();
            $this->days[] = $next_day;
        }
    }

    public function fetch() {
        $ret = each($this->days);
        return $ret ? $ret['value'] : false;
    }

    public function prevMonth() {
        return $this->m == 1 ? new calendar_month($this->y - 1, 12) : new calendar_month($this->y, $this->m - 1);
    }

    public function nextMonth() {
        return $this->m == 12 ? new calendar_month($this->y + 1, 1) : new calendar_month($this->y, $this->m + 1);
    }

    public function thisYear() {
        return $this->y;
    }

    public function thisMonth() {
        return $this->m;
    }

    public function getStamp() {
        return $this->stamp;
    }

}

?>
