<?php

/**
 * Images functions.
 * Built-in support for JPEG, GIF, PNG and BMP images.
 *
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 */
class Images
{

    const ROTATE_LEFT = 90, ROTATE_RIGHT = 270;

    public static function thumbnail($path, $output_dir_doc_root_rel,
                                     $max_width = 360, $quality = 80, $max_height = 400)
    {
        $output_dir = $_SERVER['DOCUMENT_ROOT'] . $output_dir_doc_root_rel;
        if (parse_url($path, PHP_URL_HOST) !== null) {
            $output_file = $output_dir . '/' . hash('sha256', $path) . '-' . $max_width . '-' . $quality . '.jpg';
            if (file_exists($output_file)) {
                return Filesystem::get_doc_root_path($output_file);
            }
            $temp = tempnam(sys_get_temp_dir(), 'THUMB');
            if (!copy($path, $temp)) {
                unlink($temp);
                return null;
            }
            $path = $temp;
            register_shutdown_function(function ($path) {
                unlink($path);
            }, $path);
        } elseif (!file_exists($path)) {
            return null;
        } else {
            $output_file = $output_dir . '/' . pathinfo($path, PATHINFO_FILENAME) . '-' . $max_width . '-' . $quality . '.jpg';
        }
        if (file_exists($output_file)) {
            return Filesystem::get_doc_root_path($output_file);
        }
        if (!Filesystem::mkdir($output_dir)) {
            return Filesystem::get_doc_root_path($path);
        }
        $im = new Imagick($path);
        $width = $im->getimagewidth();
        if ($max_width > $width) {
            $max_width = $width;
        }
        $height = $im->getimageheight();
        if ($max_height > $height) {
            $max_height = $height;
        }

        if (exif_imagetype($path) === IMAGETYPE_GIF && self::is_animated($path)) {
            $im = $im->coalesceImages();
            $im = iterator_to_array($im)[0];
        }

        $im->setimageformat('jpeg');
        $im->thumbnailImage($max_width, $max_height, true, false);
        $im->setimagecompressionquality($quality);
        //$im->sharpenimage(0, 0);
        $im = $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        $im->writeimage($output_file);
        $im->clear();
        $im->destroy();
        return Filesystem::get_doc_root_path($output_file);
    }

    /**
     * creates image resource from file
     * @param string $filename full path to image
     * @return boolean|resource image resource on success, otherwise false
     */
    public static function imagecreate($filename)
    {
        switch (exif_imagetype($filename)) {
            case IMAGETYPE_BMP:
                return imagecreatefrombmp($filename);
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filename);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filename);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filename);
        }
        return false;
    }

    /**
     *
     * @deprecated
     */
    public function imhashtest($path)
    {
        $thumb = imagecreatetruecolor(10, 10);
        $source = self::imagecreate($path);
        imagecopyresized($thumb, $source, 0, 0, 0, 0, 10, 10, imagesx($source),
            imagesy($source));
        for ($i = 0; $i <= 9; ++$i) {
            for ($j = 0; $j <= 9; ++$j) {
                $rgb = imagecolorat($thumb, $i, $j);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $n = 50;
                $r = round($r / $n, 0) * $n;
                $g = round($g / $n, 0) * $n;
                $b = round($b / $n, 0) * $n;
                echo "($r, $g, $b)";
                //imagesetpixel($thumb, $i, $j, $color);
            }
            echo "<br/>";
        }
        //header("Content-type: image/png");
        //imagepng($thumb);
    }

    public static function rotate($src, $angle, $dest = null)
    {
        if (exif_imagetype($src) === IMAGETYPE_GIF && self::is_animated($src)) {
            return false;
        }
        if ($dest === null) {
            $dest = $src;
        } else {
            if (!Filesystem::mkdir(pathinfo($dest, PATHINFO_DIRNAME))) {
                return false;
            }
            if (is_dir($dest)) {
                $dest .= '/' . pathinfo($src, PATHINFO_BASENAME);
            }
        }
        if (!($im = self::imagecreate($src))) {
            return false;
        }
        $im = imagerotate($im, $angle, imagecolorallocate($im, 0, 0, 0));
        switch (exif_imagetype($src)) {
            case IMAGETYPE_JPEG:
                imagejpeg($im, $dest, self::get_quality($src));
                break;
            case IMAGETYPE_PNG:
            case IMAGETYPE_BMP:
            case IMAGETYPE_GIF:
                imagepng($im, $dest, 9);
                break;
            default:
                return false;
        }
        return $dest;
    }

    /**
     * Converts hex colors, like #FFFFFF or #FFF, to RGB.
     * @param string $color
     * @return array RGB
     */
    public static function hex2rgb($color)
    {
        if (!preg_match('/^#?([a-f0-9]{3}){1,2}$/i', $color)) {
            $color = [
                    'black' => array('red' => 0x00, 'green' => 0x00, 'blue' => 0x00),
                    'maroon' => array('red' => 0x80, 'green' => 0x00, 'blue' => 0x00),
                    'green' => array('red' => 0x00, 'green' => 0x80, 'blue' => 0x00),
                    'olive' => array('red' => 0x80, 'green' => 0x80, 'blue' => 0x00),
                    'navy' => array('red' => 0x00, 'green' => 0x00, 'blue' => 0x80),
                    'purple' => array('red' => 0x80, 'green' => 0x00, 'blue' => 0x80),
                    'teal' => array('red' => 0x00, 'green' => 0x80, 'blue' => 0x80),
                    'gray' => array('red' => 0x80, 'green' => 0x80, 'blue' => 0x80),
                    'silver' => array('red' => 0xC0, 'green' => 0xC0, 'blue' => 0xC0),
                    'red' => array('red' => 0xFF, 'green' => 0x00, 'blue' => 0x00),
                    'lime' => array('red' => 0x00, 'green' => 0xFF, 'blue' => 0x00),
                    'yellow' => array('red' => 0xFF, 'green' => 0xFF, 'blue' => 0x00),
                    'blue' => array('red' => 0x00, 'green' => 0x00, 'blue' => 0xFF),
                    'fuchsia' => array('red' => 0xFF, 'green' => 0x00, 'blue' => 0xFF),
                    'aqua' => array('red' => 0x00, 'green' => 0xFF, 'blue' => 0xFF),
                    'white' => array('red' => 0xFF, 'green' => 0xFF, 'blue' => 0xFF),
//  Additional  colors  as  they  are  used  by  Netscape  and  IE 
                    'aliceblue' => array('red' => 0xF0, 'green' => 0xF8, 'blue' => 0xFF),
                    'antiquewhite' => array('red' => 0xFA, 'green' => 0xEB, 'blue' => 0xD7),
                    'aquamarine' => array('red' => 0x7F, 'green' => 0xFF, 'blue' => 0xD4),
                    'azure' => array('red' => 0xF0, 'green' => 0xFF, 'blue' => 0xFF),
                    'beige' => array('red' => 0xF5, 'green' => 0xF5, 'blue' => 0xDC),
                    'blueviolet' => array('red' => 0x8A, 'green' => 0x2B, 'blue' => 0xE2),
                    'brown' => array('red' => 0xA5, 'green' => 0x2A, 'blue' => 0x2A),
                    'burlywood' => array('red' => 0xDE, 'green' => 0xB8, 'blue' => 0x87),
                    'cadetblue' => array('red' => 0x5F, 'green' => 0x9E, 'blue' => 0xA0),
                    'chartreuse' => array('red' => 0x7F, 'green' => 0xFF, 'blue' => 0x00),
                    'chocolate' => array('red' => 0xD2, 'green' => 0x69, 'blue' => 0x1E),
                    'coral' => array('red' => 0xFF, 'green' => 0x7F, 'blue' => 0x50),
                    'cornflowerblue' => array('red' => 0x64, 'green' => 0x95, 'blue' => 0xED),
                    'cornsilk' => array('red' => 0xFF, 'green' => 0xF8, 'blue' => 0xDC),
                    'crimson' => array('red' => 0xDC, 'green' => 0x14, 'blue' => 0x3C),
                    'darkblue' => array('red' => 0x00, 'green' => 0x00, 'blue' => 0x8B),
                    'darkcyan' => array('red' => 0x00, 'green' => 0x8B, 'blue' => 0x8B),
                    'darkgoldenrod' => array('red' => 0xB8, 'green' => 0x86, 'blue' => 0x0B),
                    'darkgray' => array('red' => 0xA9, 'green' => 0xA9, 'blue' => 0xA9),
                    'darkgreen' => array('red' => 0x00, 'green' => 0x64, 'blue' => 0x00),
                    'darkkhaki' => array('red' => 0xBD, 'green' => 0xB7, 'blue' => 0x6B),
                    'darkmagenta' => array('red' => 0x8B, 'green' => 0x00, 'blue' => 0x8B),
                    'darkolivegreen' => array('red' => 0x55, 'green' => 0x6B, 'blue' => 0x2F),
                    'darkorange' => array('red' => 0xFF, 'green' => 0x8C, 'blue' => 0x00),
                    'darkorchid' => array('red' => 0x99, 'green' => 0x32, 'blue' => 0xCC),
                    'darkred' => array('red' => 0x8B, 'green' => 0x00, 'blue' => 0x00),
                    'darksalmon' => array('red' => 0xE9, 'green' => 0x96, 'blue' => 0x7A),
                    'darkseagreen' => array('red' => 0x8F, 'green' => 0xBC, 'blue' => 0x8F),
                    'darkslateblue' => array('red' => 0x48, 'green' => 0x3D, 'blue' => 0x8B),
                    'darkslategray' => array('red' => 0x2F, 'green' => 0x4F, 'blue' => 0x4F),
                    'darkturquoise' => array('red' => 0x00, 'green' => 0xCE, 'blue' => 0xD1),
                    'darkviolet' => array('red' => 0x94, 'green' => 0x00, 'blue' => 0xD3),
                    'deeppink' => array('red' => 0xFF, 'green' => 0x14, 'blue' => 0x93),
                    'deepskyblue' => array('red' => 0x00, 'green' => 0xBF, 'blue' => 0xFF),
                    'dimgray' => array('red' => 0x69, 'green' => 0x69, 'blue' => 0x69),
                    'dodgerblue' => array('red' => 0x1E, 'green' => 0x90, 'blue' => 0xFF),
                    'firebrick' => array('red' => 0xB2, 'green' => 0x22, 'blue' => 0x22),
                    'floralwhite' => array('red' => 0xFF, 'green' => 0xFA, 'blue' => 0xF0),
                    'forestgreen' => array('red' => 0x22, 'green' => 0x8B, 'blue' => 0x22),
                    'gainsboro' => array('red' => 0xDC, 'green' => 0xDC, 'blue' => 0xDC),
                    'ghostwhite' => array('red' => 0xF8, 'green' => 0xF8, 'blue' => 0xFF),
                    'gold' => array('red' => 0xFF, 'green' => 0xD7, 'blue' => 0x00),
                    'goldenrod' => array('red' => 0xDA, 'green' => 0xA5, 'blue' => 0x20),
                    'greenyellow' => array('red' => 0xAD, 'green' => 0xFF, 'blue' => 0x2F),
                    'honeydew' => array('red' => 0xF0, 'green' => 0xFF, 'blue' => 0xF0),
                    'hotpink' => array('red' => 0xFF, 'green' => 0x69, 'blue' => 0xB4),
                    'indianred' => array('red' => 0xCD, 'green' => 0x5C, 'blue' => 0x5C),
                    'indigo' => array('red' => 0x4B, 'green' => 0x00, 'blue' => 0x82),
                    'ivory' => array('red' => 0xFF, 'green' => 0xFF, 'blue' => 0xF0),
                    'khaki' => array('red' => 0xF0, 'green' => 0xE6, 'blue' => 0x8C),
                    'lavender' => array('red' => 0xE6, 'green' => 0xE6, 'blue' => 0xFA),
                    'lavenderblush' => array('red' => 0xFF, 'green' => 0xF0, 'blue' => 0xF5),
                    'lawngreen' => array('red' => 0x7C, 'green' => 0xFC, 'blue' => 0x00),
                    'lemonchiffon' => array('red' => 0xFF, 'green' => 0xFA, 'blue' => 0xCD),
                    'lightblue' => array('red' => 0xAD, 'green' => 0xD8, 'blue' => 0xE6),
                    'lightcoral' => array('red' => 0xF0, 'green' => 0x80, 'blue' => 0x80),
                    'lightcyan' => array('red' => 0xE0, 'green' => 0xFF, 'blue' => 0xFF),
                    'lightgoldenrodyellow' => array('red' => 0xFA, 'green' => 0xFA, 'blue' => 0xD2),
                    'lightgreen' => array('red' => 0x90, 'green' => 0xEE, 'blue' => 0x90),
                    'lightgrey' => array('red' => 0xD3, 'green' => 0xD3, 'blue' => 0xD3),
                    'lightpink' => array('red' => 0xFF, 'green' => 0xB6, 'blue' => 0xC1),
                    'lightsalmon' => array('red' => 0xFF, 'green' => 0xA0, 'blue' => 0x7A),
                    'lightseagreen' => array('red' => 0x20, 'green' => 0xB2, 'blue' => 0xAA),
                    'lightskyblue' => array('red' => 0x87, 'green' => 0xCE, 'blue' => 0xFA),
                    'lightslategray' => array('red' => 0x77, 'green' => 0x88, 'blue' => 0x99),
                    'lightsteelblue' => array('red' => 0xB0, 'green' => 0xC4, 'blue' => 0xDE),
                    'lightyellow' => array('red' => 0xFF, 'green' => 0xFF, 'blue' => 0xE0),
                    'limegreen' => array('red' => 0x32, 'green' => 0xCD, 'blue' => 0x32),
                    'linen' => array('red' => 0xFA, 'green' => 0xF0, 'blue' => 0xE6),
                    'mediumaquamarine' => array('red' => 0x66, 'green' => 0xCD, 'blue' => 0xAA),
                    'mediumblue' => array('red' => 0x00, 'green' => 0x00, 'blue' => 0xCD),
                    'mediumorchid' => array('red' => 0xBA, 'green' => 0x55, 'blue' => 0xD3),
                    'mediumpurple' => array('red' => 0x93, 'green' => 0x70, 'blue' => 0xD0),
                    'mediumseagreen' => array('red' => 0x3C, 'green' => 0xB3, 'blue' => 0x71),
                    'mediumslateblue' => array('red' => 0x7B, 'green' => 0x68, 'blue' => 0xEE),
                    'mediumspringgreen' => array('red' => 0x00, 'green' => 0xFA, 'blue' => 0x9A),
                    'mediumturquoise' => array('red' => 0x48, 'green' => 0xD1, 'blue' => 0xCC),
                    'mediumvioletred' => array('red' => 0xC7, 'green' => 0x15, 'blue' => 0x85),
                    'midnightblue' => array('red' => 0x19, 'green' => 0x19, 'blue' => 0x70),
                    'mintcream' => array('red' => 0xF5, 'green' => 0xFF, 'blue' => 0xFA),
                    'mistyrose' => array('red' => 0xFF, 'green' => 0xE4, 'blue' => 0xE1),
                    'moccasin' => array('red' => 0xFF, 'green' => 0xE4, 'blue' => 0xB5),
                    'navajowhite' => array('red' => 0xFF, 'green' => 0xDE, 'blue' => 0xAD),
                    'oldlace' => array('red' => 0xFD, 'green' => 0xF5, 'blue' => 0xE6),
                    'olivedrab' => array('red' => 0x6B, 'green' => 0x8E, 'blue' => 0x23),
                    'orange' => array('red' => 0xFF, 'green' => 0xA5, 'blue' => 0x00),
                    'orangered' => array('red' => 0xFF, 'green' => 0x45, 'blue' => 0x00),
                    'orchid' => array('red' => 0xDA, 'green' => 0x70, 'blue' => 0xD6),
                    'palegoldenrod' => array('red' => 0xEE, 'green' => 0xE8, 'blue' => 0xAA),
                    'palegreen' => array('red' => 0x98, 'green' => 0xFB, 'blue' => 0x98),
                    'paleturquoise' => array('red' => 0xAF, 'green' => 0xEE, 'blue' => 0xEE),
                    'palevioletred' => array('red' => 0xDB, 'green' => 0x70, 'blue' => 0x93),
                    'papayawhip' => array('red' => 0xFF, 'green' => 0xEF, 'blue' => 0xD5),
                    'peachpuff' => array('red' => 0xFF, 'green' => 0xDA, 'blue' => 0xB9),
                    'peru' => array('red' => 0xCD, 'green' => 0x85, 'blue' => 0x3F),
                    'pink' => array('red' => 0xFF, 'green' => 0xC0, 'blue' => 0xCB),
                    'plum' => array('red' => 0xDD, 'green' => 0xA0, 'blue' => 0xDD),
                    'powderblue' => array('red' => 0xB0, 'green' => 0xE0, 'blue' => 0xE6),
                    'rosybrown' => array('red' => 0xBC, 'green' => 0x8F, 'blue' => 0x8F),
                    'royalblue' => array('red' => 0x41, 'green' => 0x69, 'blue' => 0xE1),
                    'saddlebrown' => array('red' => 0x8B, 'green' => 0x45, 'blue' => 0x13),
                    'salmon' => array('red' => 0xFA, 'green' => 0x80, 'blue' => 0x72),
                    'sandybrown' => array('red' => 0xF4, 'green' => 0xA4, 'blue' => 0x60),
                    'seagreen' => array('red' => 0x2E, 'green' => 0x8B, 'blue' => 0x57),
                    'seashell' => array('red' => 0xFF, 'green' => 0xF5, 'blue' => 0xEE),
                    'sienna' => array('red' => 0xA0, 'green' => 0x52, 'blue' => 0x2D),
                    'skyblue' => array('red' => 0x87, 'green' => 0xCE, 'blue' => 0xEB),
                    'slateblue' => array('red' => 0x6A, 'green' => 0x5A, 'blue' => 0xCD),
                    'slategray' => array('red' => 0x70, 'green' => 0x80, 'blue' => 0x90),
                    'snow' => array('red' => 0xFF, 'green' => 0xFA, 'blue' => 0xFA),
                    'springgreen' => array('red' => 0x00, 'green' => 0xFF, 'blue' => 0x7F),
                    'steelblue' => array('red' => 0x46, 'green' => 0x82, 'blue' => 0xB4),
                    'tan' => array('red' => 0xD2, 'green' => 0xB4, 'blue' => 0x8C),
                    'thistle' => array('red' => 0xD8, 'green' => 0xBF, 'blue' => 0xD8),
                    'tomato' => array('red' => 0xFF, 'green' => 0x63, 'blue' => 0x47),
                    'turquoise' => array('red' => 0x40, 'green' => 0xE0, 'blue' => 0xD0),
                    'violet' => array('red' => 0xEE, 'green' => 0x82, 'blue' => 0xEE),
                    'wheat' => array('red' => 0xF5, 'green' => 0xDE, 'blue' => 0xB3),
                    'whitesmoke' => array('red' => 0xF5, 'green' => 0xF5, 'blue' => 0xF5),
                    'yellowgreen' => array('red' => 0x9A, 'green' => 0xCD, 'blue' => 0x32)
                ][$color] ?? false;
            if ($color === false) {
                log_error('Invalid color identifier');
                return false;
            }
            return array_values($color);
        }
        $color = ltrim($color, '#');
        $color = str_split($color, strlen($color) / 3);
        if (strlen($color[0]) === 1) {
            $color = array_map(function ($rgb) {
                return $rgb . $rgb;
            }, $color);
        }
        return array_map('hexdec', $color);
    }

    public static function get_quality($path)
    {
        return (new Imagick($path))->getImageCompressionQuality();
    }

    public static function compress($path, $lossless = false)
    {
        $imagetype = exif_imagetype($path);
        if ($lossless) {
            switch ($imagetype) {
                case IMAGETYPE_JPEG:
                    return $path;
                case IMAGETYPE_GIF:
                    if (self::is_animated($path)) {
                        return $path;
                    }
                case IMAGETYPE_BMP:
                case IMAGETYPE_PNG:
                    $temp = tempnam(sys_get_temp_dir(), 'CMP');
                    self::image2png($path, $temp);
                    if (filesize($temp) < filesize($path)) {
                        $pathinfo = pathinfo($path);
                        rename($temp,
                            $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.png');
                        if ($imagetype != IMAGETYPE_PNG) {
                            unlink($path);
                        }
                        return $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.png';
                    }
                    unlink($temp);
                    return $path;
            }
        } else {
            switch ($imagetype) {
                case IMAGETYPE_JPEG:
                    if ((int)UPLOADS_JPEG_MAX_QUALITY >= 100) {
                        return $path;
                    }
                    $temp = tempnam(sys_get_temp_dir(), 'CMP');
                    self::image2jpeg($path, $temp, UPLOADS_JPEG_MAX_QUALITY);
                    if (filesize($temp) <= filesize($path)) {
                        rename($temp, $path);
                    } else {
                        unlink($temp);
                    }
                    return $path;
                case IMAGETYPE_GIF:
                    if (self::is_animated($path)) {
                        return $path;
                    }
                case IMAGETYPE_BMP:
                case IMAGETYPE_PNG:
                    if (self::hastransparency($path)) {
                        return self::compress($path, true);
                    }
                    $pathinfo = pathinfo($path);
                    $temp = tempnam(sys_get_temp_dir(), 'CMP');
                    self::image2jpeg($path, $temp, UPLOADS_JPEG_MAX_QUALITY);
                    if (filesize($temp) < filesize($path)) {
                        $jpeg_path = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.jpg';
                        rename($temp, $jpeg_path);
                        unlink($path);
                        return $jpeg_path;
                    }
                    unlink($temp);
                    return $path;
            }
        }
        return $path;
    }

    public static function is_animated($path): bool
    {
        return ((new Imagick($path))->getnumberimages() > 1);
    }

    public static function hastransparency($path): bool
    {
        if ((new Imagick($path))->getimagealphachannel() == 0) {
            return false;
        }
        $im = self::imagecreate($path);
        $width = imagesx($im); // Get the width of the image
        $height = imagesy($im); // Get the height of the image
        // We run the image pixel by pixel and as soon as we find a transparent pixel we stop and return true.
        $transp = imagecolortransparent($im);
        for ($i = 0; $i < $width; ++$i) {
            for ($j = 0; $j < $height; ++$j) {
                if ((imagecolorat($im, $i, $j) & 0x7F000000) >> 24 || ($transp != -1
                        && $transp == imagecolorat($im, $i, $j))) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function image2jpeg($path, $output,
                                      int $quality = UPLOADS_JPEG_MAX_QUALITY)
    {
        $im = new Imagick($path);
        $im->setimageformat('jpeg');
        self::stripimage($im);
        $im->setimagecompression(Imagick::COMPRESSION_JPEG);
        $im->setimagecompressionquality($quality);
        $im->writeimage($output);
        $im->clear();
        $im->destroy();
        return true;
    }

    public static function stripimage(&$im)
    {
        $profiles = $im->getImageProfiles('icc', true);
        $im->stripImage();
        if (!empty($profiles)) {
            $im->profileImage('icc', $profiles['icc']);
        }
    }

    /**
     * Converts any* image to png, compressing it.
     * @param string $path
     * @param string $output full path to new image
     * @return boolean
     */
    public static function image2png($path, $output)
    {
        if (!Filesystem::mkdir(pathinfo($output, PATHINFO_DIRNAME))) {
            return $path;
        }
        $im = new Imagick($path);
        $im->setimageformat('png');
        self::stripimage($im);
        $im->writeimage($output);
        $im->clear();
        $im->destroy();
        return true;
    }

    public static function get_image_ext($path)
    {
        switch (exif_imagetype($path)) {
            case IMAGETYPE_JPEG:
                return 'jpg';
            case IMAGETYPE_PNG:
                return 'png';
            case IMAGETYPE_BMP:
                return 'bmp';
            case IMAGETYPE_GIF:
                return 'gif';
        }
        return false;
    }

}
