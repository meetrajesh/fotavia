<?php

require '../../config.php';

$num = isset($_GET['num']) && ctype_digit($_GET['num']) ? $_GET['num'] : 1;
$pid = isset($_GET['pid']) && ctype_digit($_GET['pid']) ? $_GET['pid'] : 0;

if (!photo::exists($pid)) {
    echo json_encode(array());
    return;
}

$photo = photo::get($pid);

$prev = array();
$next = array();

$prev_photo = $photo;
$next_photo = $photo;

// load up previous photos
for ($i = 0; $i < $num; $i++){
    if ($prev_photo->has_prev()) {
        $prev_photo = $prev_photo->prev();
        $prev[] = BASE_URL . $prev_photo->url(user::default_thumb_size());
    } else {
        break;
    }
}

// load up next photos
for ($i = 0; $i < $num; $i++) {
    if ($next_photo->has_next()) {
        $next_photo = $next_photo->next();
        $next[] = BASE_URL . $next_photo->url(user::default_thumb_size());
    } else {
        break;
    }
}

$photo_list = array('prev' => $prev, 'next' => $next);
echo json_encode($photo_list);

?>
