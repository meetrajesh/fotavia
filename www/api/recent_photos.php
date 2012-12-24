<?php

require '../../config.php';

$num = isset($_GET['num']) && ctype_digit($_GET['num']) ? $_GET['num'] : 1;
$offset = isset($_GET['offset']) && ctype_digit($_GET['offset']) ? $_GET['offset'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'mine';

$user = user::active();

// Fetch $num photo with one additional photo to determine if there are more
// after the set
switch($type) {
case 'friend':
    $photo_set = photo::latest_from($user->get_leaders(), $num + 1, $offset);
    break;
case 'mine':
    $photo_set = photo::latest_from($user->get_id(), $num + 1, $offset);
    break;
case 'other':
    $photo_set = photo::latest_from_others($num + 1, $offset);
    break;
}

// Check to see if there are more photos after the last photo in the set,
// If there are, pop the test photo off of array
($has_next = (sizeof($photo_set) > $num)) ? array_pop($photo_set) : null;

$photos = array();

foreach($photo_set as $i => $photo_id) {
    $p = photo::get($photo_id);
    $photo_info = array('page_url' => $p->get_page_url(), 'img_url' => $p->url('square'), 'alt' => $p->get_short_title(), 'title' => $p->get_tooltip(false));
    $photos[] = $photo_info;
}

$photo_list = array('has_prev' => $offset > 0, 'has_next' => $has_next, 'photos' => $photos);

echo json_encode($photo_list);

?>
