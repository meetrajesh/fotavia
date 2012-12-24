<?php

// $i = year
// $j = month

$o = '';
$max_year = (int)date('Y');
$counts = photo::get_photos_per_month($user->get_id());

for ($i = $max_year; $i >= 2009; $i--) {

    $o .= "\n" . '<strong>' . $i . '</strong> &nbsp;';

    for ($j=1; $j <= 12; $j++) {

        $year  = $i;
        $month = str_pad($j, 2, '0', STR_PAD_LEFT);
        $ym = $year . $month;

        if (isset($counts[$ym]) && $counts[$ym] > 0) {
            $stamp = mktime(0,0,0,$j,1,$i);
            $href = spf('/%s/%d/%s/%s', hsc($user->get_username()), $year, $month, strtolower(date('M', $stamp)));
            $o .= spf('<a href="%s">%s</a> (%d) ' . "\n", $href, date('M', $stamp), $counts[$ym]);
        }

    }

    $o .= '<br/>';

}

echo $o;

?>
