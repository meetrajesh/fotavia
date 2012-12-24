<?php

class photo_utils {

    // returns array($image_handle, $output_func, $best_quality)
    public static function get_image_handle($path) {

        list(,,$image_type) = getimagesize($path);
        switch ($image_type) {
        case IMAGETYPE_JPEG:
            return array(imagecreatefromjpeg($path), 'imagejpeg', 100);
        case IMAGETYPE_PNG:
            return array(imagecreatefrompng($path), 'imagepng', 0);
        case IMAGETYPE_GIF:
            return array(imagecreatefromgif($path), 'imagegif', null);
        default:
            return false;
        }
    }

    public static function rotate($source_path, $angle, $dest_path='') {
        if ($angle == 0) {
            return;
        }
        if (empty($dest_path)) {
            $dest_path = $source_path;
        }
        list($source, $output_func, $best_quality) = self::get_image_handle($source_path);
        $black = imagecolorallocate($source, 0x00, 0x00, 0x00);
        $source = imagerotate($source, $angle, $black);
        // $source now points to the rotated image
        $output_func($source, $dest_path, $best_quality);
    }

}

?>
