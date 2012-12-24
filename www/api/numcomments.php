<?php

include '../../config.php';

if (!empty($_GET['pid']) && is_id($_GET['pid']) && photo::exists($_GET['pid'])) {
    $num_comments = photo::get($_GET['pid'])->num_comments();
} else {
    $num_comments = 0;
}

$im = imagecreatetruecolor(98, 17);
$white = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
$blue = imagecolorallocate($im, 0x42, 0x42, 0x42);

// set white as transparent
$background = imagecolortransparent($im, $white);
// set the background
imagefill($im, 0, 0, $background);

// write the text
$font = realpath(gdir('../../img/cour.ttf'));
$comment_str = spf(ngettext('%d comment', '%d comments', $num_comments), $num_comments);
imagefttext($im, 10, 0, 0, 12, $blue, $font, $comment_str);

header('Content-type: image/jpeg');
imagejpeg($im);
imagedestroy($im);

?>
