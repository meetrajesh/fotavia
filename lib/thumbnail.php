<?php

class thumbnail {

    private $quality;
    private $sharpen;

    public function __construct($quality=85, $sharpen=true) {
        $this->quality = $quality;
        $this->sharpen = $sharpen;
    }

    // generate a thumbnail proportional to the src image
    public function gen($src_file, $dest_file, $larger_dimension) {

        // $outputX is the size of the larger dimension of the output thumbnail
        // the smaller dimension size is calculated automatically using the aspect ratio

        list($width, $height) = $this->get_dimensions($src_file);
        # echo $width . ' ' . $height . "\n";

        // the image is horizontal
        if ($width > $height) {

            $outputX = $larger_dimension;
            $outputY = $this->get_other_dimension($width, $height, $larger_dimension);

        // the image is vertical
        } elseif ($width < $height) {

            $outputX = $this->get_other_dimension($width, $height, $larger_dimension);
            $outputY = $larger_dimension;

        // the image is square
        } else {

            $outputX = $larger_dimension;
            $outputY = $larger_dimension;

        }

        # echo $outputX . ' ' . $outputY . "\n";

        // grab as much as possible of the src_file
        $portionX = $width;
        $portionY = $height;
        
        // start from the top-left corner of the src_file
        $deltaX = 0;
        $deltaY = 0;

        return $this->gen_thumb($src_file, $dest_file, $deltaX, $deltaY, $outputX, $outputY, $portionX, $portionY);

    }

    // generate a thumbnail of equal dimensions
    // try to get as much of the "center" of the pic as possible
    public function gen_equal($src_file, $dest_file, $outputX) {

        list($width, $height) = $this->get_dimensions($src_file);

        // the image is vertical
        if ($width < $height) {

            $deltaX   = 0;
            $deltaY   = ($height - $width) / 2;
            $portionX = $width;
            $portionY = $width;

        // the image is horizontal
        } elseif ($width > $height) {

            $deltaX   = ($width - $height) / 2;
            $deltaY   = 0;
            $portionX = $height;
            $portionY = $height;
        
        // the image is square
        } else {

            $deltaX   = 0;
            $deltaY   = 0;
            $portionX = $width;
            $portionY = $height;

        }

        return $this->gen_thumb($src_file, $dest_file, $deltaX, $deltaY, $outputX, $outputX, $portionX, $portionY);

    }

    // use the GD library to create the thumbnail
    private function gen_thumb($src_file, $dest_file, $deltaX, $deltaY, $outputX, $outputY, $portionX, $portionY) {

        list($source) = photo_utils::get_image_handle($src_file);
        $target = imagecreatetruecolor($outputX, $outputY);

        imagecopyresampled($target, $source, 0, 0, $deltaX, $deltaY, $outputX, $outputY, $portionX, $portionY);

        // if sharpening was enabled during construction, sharpen the thumb
        // otherwise sharpen only if both width and height are both <= 400
        if ($this->sharpen || max($outputX, $outputY) <= 400) {
            $this->sharpen($target, 80, 0.5, 3);
        }

        // output the jpeg to the destination file
        $ret_val = imagejpeg($target, $dest_file, $this->quality);

        imagedestroy($source);
        imagedestroy($target);

        return $ret_val;

    }

    private function get_dimensions($src_file) {

        // get the image dimensions
        $dimensions = getimagesize($src_file);
        return array($dimensions[0], $dimensions[1]);

    }
    
    private function get_other_dimension($src_width, $src_height, $larger_dimension) {

        // the src image is horizontal
        if ($src_width > $src_height) {
            return ceil($larger_dimension * ($src_height / $src_width));
        // the src image is vertical
        } elseif ($src_width < $src_height) {
            return ceil($larger_dimension * ($src_width / $src_height));
        // the src image is square
        } else {
            // the output thumbnail's dimension are equal
            return $larger_dimension;
        }

    }

    /*
    Unsharp mask code from http://vikjavev.no/computing/ump.php?id=254

    Unsharp masking is a traditional darkroom technique that has proven very suitable for 
    digital imaging. The principle of unsharp masking is to create a blurred copy of the image
    and compare it to the underlying original. The difference in colour values
    between the two images is greatest for the pixels near sharp edges. When this 
    difference is subtracted from the original image, the edges will be
    accentuated. 
    
    The Amount parameter simply says how much of the effect you want. 100 is 'normal'.
    Radius is the radius of the blurring circle of the mask. 'Threshold' is the least
    difference in colour values that is allowed between the original and the mask. In practice
    this means that low-contrast areas of the picture are left unrendered whereas edges
    are treated normally. This is good for pictures of e.g. skin or blue skies.
    
    Any suggenstions for improvement of the algorithm, expecially regarding the speed
    and the roundoff errors in the Gaussian blur process, are welcome.
    */

    private function sharpen($img, $amount, $radius, $threshold) { 

        ////////////////////////////////////////////////////////////////////////////////////////////////  
        ////  
        ////                  Unsharp Mask for PHP - version 2.1.1  
        ////  
        ////    Unsharp mask algorithm by Torstein Hønsi 2003-07.  
        ////             thoensi_at_netcom_dot_no.  
        ////               Please leave this notice.  
        ////  
        ///////////////////////////////////////////////////////////////////////////////////////////////  

        // $img is an image that is already created within php using
        // imgcreatetruecolor. No url! $img must be a truecolor image.

        // Attempt to calibrate the parameters to Photoshop:
        if ($amount > 500) {
            $amount = 500;
        }
        $amount = $amount * 0.016;
        if ($radius > 50) {
            $radius = 50;
        }
        $radius = $radius * 2;
        if ($threshold > 255) {
            $threshold = 255;
        }

        // only integers make sense. 
        $radius = abs(round($radius));
        if ($radius == 0) {
            imagedestroy($img);
            return $img;
        } 
        $w = imagesx($img);
        $h = imagesy($img);
        $imgCanvas = imagecreatetruecolor($w, $h);
        $imgBlur = imagecreatetruecolor($w, $h);

        // Gaussian blur matrix: /////////////////////////
        //                         
        //    1    2    1         
        //    2    4    2         
        //    1    2    1         
        //                         
        ////////////////////////////////////////////////// 

        if (function_exists('imageconvolution')) { // PHP >= 5.1

            $matrix = array(array(1,2,1),
                            array(2,4,2),
                            array(1,2,1));
            imagecopy($imgBlur, $img, 0, 0, 0, 0, $w, $h);
            imageconvolution($imgBlur, $matrix, 16, 0);

        } else {

            // move copies of the image around one pixel at the time and merge them with weight
            // according to the matrix. The same matrix is simply repeated for higher radii.

            for($i=0; $i < $radius; $i++) {
                imagecopy($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h); // left
                imagecopymerge($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50); // right
                imagecopymerge($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50); // center
                imagecopy($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);
                imagecopymerge($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up
                imagecopymerge($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down
            }

        }

        if ($threshold > 0) {
            // Calculate the difference between the blurred pixels and the original
            // and set the pixels
            for ($x = 0; $x < $w-1; $x++) { // each row
                for ($y = 0; $y < $h; $y++) { // each pixel
                     
                    $rgbOrig = ImageColorAt($img, $x, $y);
                    $rOrig = (($rgbOrig >> 16) & 0xFF);
                    $gOrig = (($rgbOrig >> 8) & 0xFF);
                    $bOrig = ($rgbOrig & 0xFF);

                    $rgbBlur = ImageColorAt($imgBlur, $x, $y);

                    $rBlur = (($rgbBlur >> 16) & 0xFF);
                    $gBlur = (($rgbBlur >> 8) & 0xFF);
                    $bBlur = ($rgbBlur & 0xFF);
                 
                    // When the masked pixels differ less from the original
                    // than the threshold specifies, they are set to their original value.
                    $rNew = (abs($rOrig - $rBlur) >= $threshold)
                        ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))
                        : $rOrig;
                    $gNew = (abs($gOrig - $gBlur) >= $threshold)
                        ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))
                        : $gOrig;
                    $bNew = (abs($bOrig - $bBlur) >= $threshold)
                        ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))
                        : $bOrig;

                    if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
                        $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
                        ImageSetPixel($img, $x, $y, $pixCol);
                    }
                }
            }
        } else {
            for ($x = 0; $x < $w; $x++) { // each row
                for ($y = 0; $y < $h; $y++) { // each pixel
                    $rgbOrig = ImageColorAt($img, $x, $y);
                    $rOrig = (($rgbOrig >> 16) & 0xFF);
                    $gOrig = (($rgbOrig >> 8) & 0xFF);
                    $bOrig = ($rgbOrig & 0xFF);
                 
                    $rgbBlur = ImageColorAt($imgBlur, $x, $y);
                 
                    $rBlur = (($rgbBlur >> 16) & 0xFF);
                    $gBlur = (($rgbBlur >> 8) & 0xFF);
                    $bBlur = ($rgbBlur & 0xFF);
                 
                    $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
                    if ($rNew > 255) {
                        $rNew = 255;
                    } elseif ($rNew < 0) {
                        $rNew = 0;
                    }
                    $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;
                    if ($gNew > 255) {
                        $gNew = 255;
                    } elseif ($gNew < 0) {
                        $gNew = 0;
                    }
                    $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;
                    if ($bNew > 255) {
                        $bNew = 255;
                    } elseif ($bNew < 0) {
                        $bNew = 0;
                    }
                    $rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew;
                    ImageSetPixel($img, $x, $y, $rgbNew);
                }
            }
        }
        imagedestroy($imgCanvas);
        imagedestroy($imgBlur);
     
        return $img;

    }

}

?>
