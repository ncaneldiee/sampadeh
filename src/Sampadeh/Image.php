<?php
namespace Sampadeh;

class Image
{

    public $height;

    public $meta;

    public $quality = 70;

    public $width;

    protected $file;

    protected $image;

    public function __construct($file = null)
    {
        if (File::available($file)) {
            $this->load($file);
        }

        return $this;
    }

    public function __destruct()
    {
        if (get_resource_type($this->image) === 'gd') {
            imagedestroy($this->image);
        }
    }

    public function create($width, $height = null, $color = null)
    {
        $height = $height ?: $width;

        $this->width = $width;
        $this->height = $height;
        $this->image = imagecreatetruecolor($width, $height);

        if (imagesx($this->image) > imagesy($this->image)) {
            $orientation = 'landscape';
        } elseif (imagesx($this->image) < imagesy($this->image)) {
            $orientation = 'portrait';
        } else {
            $orientation = 'square';
        }

        $this->meta = array(
            'width' => $width,
            'height' => $height,
            'orientation' => $orientation,
            'format' => 'png',
            'mime' => 'image/png'
        );

        if ($color) {
            $rgba = $this->imagecolornormalize($color);
            $color = imagecolorallocatealpha($this->image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);
            imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $color);
        }

        return $this;
    }

    public function crop($x1, $y1, $x2, $y2)
    {
        if ($x2 < $x1) {
            list($x1, $x2) = [
                $x2,
                $x1
            ];
        }

        if ($y2 < $y1) {
            list($y1, $y2) = [
                $y2,
                $y1
            ];
        }

        $width = $x2 - $x1;
        $height = $y2 - $y1;

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagecopyresampled($image, $this->image, 0, 0, $x1, $y1, $width, $height, $width, $height);

        $this->width = $width;
        $this->height = $height;
        $this->image = $image;

        return $this;
    }

    public function embed($format = null, $quality = null)
    {
        switch (mb_strtolower($format)) {
            case 'gif':
                $mime = 'image/gif';

                break;
            case 'jpeg':
            case 'jpg':
                imageinterlace($this->image, true);
                $mime = 'image/jpeg';

                break;
            case 'png':
                $mime = 'image/png';

                break;
            default:
                $mime = Mime::get($this->file);

                break;
        }

        $quality = $quality ?: $this->quality;

        ob_start();

        switch ($mime) {
            case 'image/gif':
                imagegif($this->image);

                break;
            case 'image/jpeg':
                imagejpeg($this->image, null, round($quality));

                break;
            case 'image/png':
                imagepng($this->image, null, round(9 * $quality / 100));

                break;
        }

        $data = ob_get_contents();

        ob_end_clean();

        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    public function fit($maxwidth, $maxheight)
    {
        if ($this->width <= $maxwidth && $this->height <= $maxheight) {
            return $this;
        }

        $ratio = $this->height / $this->width;

        if ($this->width > $maxwidth) {
            $width = $maxwidth;
            $height = $width * $ratio;
        } else {
            $width = $this->width;
            $height = $this->height;
        }

        if ($height > $maxheight) {
            $height = $maxheight;
            $width = $height / $ratio;
        }

        return $this->resize($width, $height);
    }

    public function flip($direction)
    {
        $image = imagecreatetruecolor($this->width, $this->height);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        switch (mb_strtolower($direction)) {
            case 'y':
                for ($y = 0; $y < $this->height; $y ++) {
                    imagecopy($image, $this->image, 0, $y, 0, $this->height - $y - 1, $this->width, 1);
                }

                break;
            default:
                for ($x = 0; $x < $this->width; $x ++) {
                    imagecopy($image, $this->image, $x, 0, $this->width - $x - 1, 0, 1, $this->height);
                }

                break;
        }

        $this->image = $image;

        return $this;
    }

    public function load($file)
    {
        $this->file = $file;

        return $this->imagemetadata();
    }

    public function output($format = null, $quality = null)
    {
        switch (mb_strtolower($format)) {
            case 'gif':
                $mime = 'image/gif';

                break;
            case 'jpeg':
            case 'jpg':
                imageinterlace($this->image, true);
                $mime = 'image/jpeg';

                break;
            case 'png':
                $mime = 'image/png';

                break;
            default:
                $mime = Mime::get($this->file);

                break;
        }

        $quality = $quality ?: $this->quality;

        header('Content-Type: ' . $mime);

        switch ($mime) {
            case 'image/gif':
                imagegif($this->image);

                break;
            case 'image/jpeg':
                imagejpeg($this->image, null, round($quality));

                break;
            case 'image/png':
                imagepng($this->image, null, round(9 * $quality / 100));

                break;
        }
    }

    public function resize($width, $height)
    {
        $image = imagecreatetruecolor($width, $height);

        if ($this->meta['format'] === 'gif') {
            $imagecolortransparent = imagecolortransparent($this->image);
            $imagecolorstotal = imagecolorstotal($this->image);

            if ($imagecolortransparent >= 0 && $imagecolortransparent < $imagecolorstotal) {
                $imagecolorsforindex = imagecolorsforindex($this->image, $imagecolortransparent);
                $imagecolorallocate = imagecolorallocate($image, $imagecolorsforindex['red'], $imagecolorsforindex['green'], $imagecolorsforindex['blue']);
                imagefill($image, 0, 0, $imagecolorallocate);
                imagecolortransparent($image, $imagecolorallocate);
            }
        } else {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        imagecopyresampled($image, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        $this->width = $width;
        $this->height = $height;
        $this->image = $image;

        return $this;
    }

    public function rotate($angle, $color = '#000000')
    {
        $rgba = $this->imagecolornormalize($color);
        $color = imagecolorallocatealpha($this->image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
        $image = imagerotate($this->image, - ($this->imageensuresize($angle, - 360, 360)), $color);
        imagesavealpha($image, true);
        imagealphablending($image, true);

        $this->width = imagesx($image);
        $this->height = imagesy($image);
        $this->image = $image;

        return $this;
    }

    public function save($file = null, $quality = null, $format = null)
    {
        $file = $file ?: $this->file;
        $quality = $quality ?: $this->quality;
        $format = $format ? $format : File::extension($file) ?: $this->meta['format'];

        switch (mb_strtolower($format)) {
            case 'gif':
                $result = imagegif($this->image, $file);

                break;
            case 'jpg':
            case 'jpeg':
                imageinterlace($this->image, true);
                $result = imagejpeg($this->image, $file, round($quality));

                break;
            case 'png':
                $result = imagepng($this->image, $file, round(9 * $quality / 100));

                break;
        }

        return $this;
    }

    public function text($text, $font, $size = 14, $color = '#000000', $position = 'center', $x = 0, $y = 0)
    {
        $angle = 0;

        $rgba = $this->imagecolornormalize($color);
        $color = imagecolorallocatealpha($this->image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);

        $box = imagettfbbox($size, $angle, $font, $text);
        $width = abs($box[6] - $box[2]);
        $height = abs($box[7] - $box[1]);

        switch (mb_strtolower($position)) {
            case 'top left':
                $x = 0 + $x;
                $y = 0 + $y + $height;
                break;
            case 'top right':
                $x = $this->width - $width + $x;
                $y = 0 + $y + $height;
                break;
            case 'top':
                $x = ($this->width / 2) - ($width / 2) + $x;
                $y = 0 + $y + $height;
                break;
            case 'bottom left':
                $x = 0 + $x;
                $y = $this->height - $height + $y + $height;
                break;
            case 'bottom right':
                $x = $this->width - $width + $x;
                $y = $this->height - $height + $y + $height;
                break;
            case 'bottom':
                $x = ($this->width / 2) - ($width / 2) + $x;
                $y = $this->height - $height + $y + $height;
                break;
            case 'left':
                $x = 0 + $x;
                $y = ($this->height / 2) - (($height / 2) - $height) + $y;
                break;
            case 'right':
                $x = $this->width - $width + $x;
                $y = ($this->height / 2) - (($height / 2) - $height) + $y;
                break;
            case 'center':
            default:
                $x = ($this->width / 2) - ($width / 2) + $x;
                $y = ($this->height / 2) - (($height / 2) - $height) + $y;
                break;
        }

        imagesavealpha($this->image, true);
        imagealphablending($this->image, true);
        imagettftext($this->image, $size, $angle, $x, $y, $color, $font, $text);

        return $this;
    }

    public function thumbnail($width, $height = null)
    {
        $height = $height ?: $width;

        $currentratio = $this->height / $this->width;
        $newratio = $height / $width;

        if ($newratio > $currentratio) {
            $this->resize($height / $currentratio, $height);
        } else {
            $this->resize($width, $width * $currentratio);
        }

        $left = floor(($this->width / 2) - ($width / 2));
        $top = floor(($this->height / 2) - ($height / 2));

        return $this->crop($left, $top, $width + $left, $height + $top);
    }

    public function watermark($watermark, $position = 'center', $opacity = .7, $x = 0, $y = 0)
    {
        $watermark = new Image($watermark);

        $opacity = $opacity * 100;

        switch (mb_strtolower($position)) {
            case 'top left':
                $x = 0 + $x;
                $y = 0 + $y;
                break;
            case 'top right':
                $x = $this->width - $watermark->width + $x;
                $y = 0 + $y;
                break;
            case 'top':
                $x = ($this->width / 2) - ($watermark->width / 2) + $x;
                $y = 0 + $y;
                break;
            case 'bottom left':
                $x = 0 + $x;
                $y = $this->height - $watermark->height + $y;
                break;
            case 'bottom right':
                $x = $this->width - $watermark->width + $x;
                $y = $this->height - $watermark->height + $y;
                break;
            case 'bottom':
                $x = ($this->width / 2) - ($watermark->width / 2) + $x;
                $y = $this->height - $watermark->height + $y;
                break;
            case 'left':
                $x = 0 + $x;
                $y = ($this->height / 2) - ($watermark->height / 2) + $y;
                break;
            case 'right':
                $x = $this->width - $watermark->width + $x;
                $y = ($this->height / 2) - ($watermark->height / 2) + $y;
                break;
            case 'center':
            default:
                $x = ($this->width / 2) - ($watermark->width / 2) + $x;
                $y = ($this->height / 2) - ($watermark->height / 2) + $y;
                break;
        }

        $this->imagecopymerge($this->image, $watermark->image, $x, $y, 0, 0, $watermark->width, $watermark->height, $opacity);

        return $this;
    }

    protected function imagecolornormalize($color)
    {
        if (is_string($color)) {
            $color = trim($color, '#');

            if (mb_strlen($color) == 6) {
                list($r, $g, $b) = [
                    $color[0] . $color[1],
                    $color[2] . $color[3],
                    $color[4] . $color[5]
                ];
            } elseif (mb_strlen($color) == 3) {
                list($r, $g, $b) = [
                    $color[0] . $color[0],
                    $color[1] . $color[1],
                    $color[2] . $color[2]
                ];
            } else {
                return false;
            }

            return [
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b),
                'a' => 0
            ];
        } elseif (is_array($color) && (count($color) == 3 || count($color) == 4)) {
            if (isset($color['r'], $color['g'], $color['b'])) {
                return [
                    'r' => $this->imageensuresize($color['r'], 0, 255),
                    'g' => $this->imageensuresize($color['g'], 0, 255),
                    'b' => $this->imageensuresize($color['b'], 0, 255),
                    'a' => $this->imageensuresize(isset($color['a']) ? $color['a'] : 0, 0, 127)
                ];
            } elseif (isset($color[0], $color[1], $color[2])) {
                return [
                    'r' => $this->imageensuresize($color[0], 0, 255),
                    'g' => $this->imageensuresize($color[1], 0, 255),
                    'b' => $this->imageensuresize($color[2], 0, 255),
                    'a' => $this->imageensuresize(isset($color[3]) ? $color[3] : 0, 0, 127)
                ];
            }
        }

        return false;
    }

    protected function imagecopymerge($destinationimage, $sourceimage, $destinationx, $destinationy, $sourcex, $sourcey, $sourcewidth, $sourceheight, $pct)
    {
        $pct /= 100;

        $w = imagesx($sourceimage);
        $h = imagesy($sourceimage);

        imagealphablending($sourceimage, false);

        $minalpha = 127;

        for ($x = 0; $x < $w; $x ++) {
            for ($y = 0; $y < $h; $y ++) {
                $alpha = (imagecolorat($sourceimage, $x, $y) >> 24) & 0xFF;
                if ($alpha < $minalpha) {
                    $minalpha = $alpha;
                }
            }
        }

        for ($x = 0; $x < $w; $x ++) {
            for ($y = 0; $y < $h; $y ++) {
                $colorxy = imagecolorat($sourceimage, $x, $y);
                $alpha = ($colorxy >> 24) & 0xFF;

                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
                } else {
                    $alpha += 127 * $pct;
                }

                $alphacolorxy = imagecolorallocatealpha($sourceimage, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);

                if (! imagesetpixel($sourceimage, $x, $y, $alphacolorxy)) {
                    return;
                }
            }
        }

        imagesavealpha($destinationimage, true);
        imagealphablending($destinationimage, true);
        imagesavealpha($sourceimage, true);
        imagealphablending($sourceimage, true);
        imagecopy($destinationimage, $sourceimage, $destinationx, $destinationy, $sourcex, $sourcey, $sourcewidth, $sourceheight);
    }

    protected function imageensuresize($value, $min, $max)
    {
        if ($value < $min) {
            return $min;
        }

        if ($value > $max) {
            return $max;
        }

        return $value;
    }

    protected function imagemetadata()
    {
        list($this->width, $this->height, $attribute) = getimagesize($this->file);

        switch (image_type_to_mime_type($attribute)) {
            case 'image/gif':
                $this->image = imagecreatefromgif($this->file);

                break;
            case 'image/jpeg':
                $this->image = imagecreatefromjpeg($this->file);

                break;
            case 'image/png':
                $this->image = imagecreatefrompng($this->file);

                break;
        }

        if (imagesx($this->image) > imagesy($this->image)) {
            $orientation = 'landscape';
        } elseif (imagesx($this->image) < imagesy($this->image)) {
            $orientation = 'portrait';
        } else {
            $orientation = 'square';
        }

        $this->meta = [
            'width' => $this->width,
            'height' => $this->height,
            'orientation' => $orientation,
            'format' => preg_replace('/^image\//', '', image_type_to_mime_type($attribute)),
            'mime' => image_type_to_mime_type($attribute)
        ];

        imagesavealpha($this->image, true);
        imagealphablending($this->image, true);

        return $this;
    }
}
