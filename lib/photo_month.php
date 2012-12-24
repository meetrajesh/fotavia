<?php

class photo_month {

    public $year;
    public $month;
    public $stamp;
    public $cal;

    private $username;
    private $user_id;

    public function __construct(user $user, $year, $month) {
        $this->username = $user->get_username();
        $this->user_id = $user->get_id();
        $this->year = (int)$year;
        $this->month = (int)$month;

        if (!checkdate($this->month, 1, $this->year)) {
            error('Invalid year+month combination provided.');
        }

        $this->populate_fields();
    }

    private function populate_fields() {
        $this->stamp = mktime(0,0,0, $this->month, 1, $this->year);
        $this->cal = new calendar_month($this->year, $this->month, 0);
        $this->cal->build($this->get_tagged_days());
    }

    private function get_tagged_days() {

        // create an array of calendar_day objects to represented tagged days
        // (days that have photos associated with them)

        $days = array();

        $sql = 'SELECT photo_id, UNIX_TIMESTAMP(user_date) AS stamp FROM photos WHERE owner_id=%d AND DATE_FORMAT(user_date, "%%Y%%c") = "%s" AND status != %d';
        $result = db::query($sql, $this->user_id, $this->year . $this->month, photo::REJECTED);
        
        while ($row = $result->fetch_assoc()) {
            $tag = $row['photo_id'];
            $days[] = new calendar_day(date('Y', $row['stamp']), date('m', $row['stamp']), date('d', $row['stamp']), $tag);
        }

        return $days;
    }

    public function has_next() {
        $last_date = spf('%02d-%02d-31', $this->year, $this->month);
        return (bool)db::result_query('SELECT MAX(user_date) > "%s" FROM photos WHERE owner_id=%d', $last_date, $this->user_id);
    }

    public function has_prev() {
        $first_date = spf('%02d-%02d-01', $this->year, $this->month);
        return (bool)db::result_query('SELECT MIN(user_date) < "%s" FROM photos WHERE owner_id=%d', $first_date, $this->user_id);
    }

    // assumes has_next() has been called
   public function next_year_month() {
        $month = str_pad($this->cal->nextMonth(), 2, 0, STR_PAD_LEFT);
        $year = ($month == 1) ? $this->cal->nextYear() : $this->cal->thisYear();
        return array($year, $month);
    }

    public function url(calendar_month $month) {
        $y = $month->thisYear();
        $m = str_pad($month->thisMonth(), 2, '0', STR_PAD_LEFT);
        return spf('/%s/%d/%s/%s', hsc($this->username), $y, $m, strtolower(date('M', $month->getStamp())));
    }

}

?>
