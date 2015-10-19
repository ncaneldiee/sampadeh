<?php
namespace Sampadeh;

class Dir
{

    public static function available($dir)
    {
        return is_dir($dir) ? true : false;
    }

    public static function copy($from, $to)
    {
        $handle = opendir($from);

        if ($handle = opendir($from)) {
            self::create($to);

            $file = readdir($handle);

            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..') {
                    $src = $from . DIRECTORY_SEPARATOR . $file;
                    $dst = $to . DIRECTORY_SEPARATOR . $file;

                    if (self::available($src)) {
                        self::copy($src, $dst);
                    } else {
                        copy($src, $dst);
                    }
                }
            }

            closedir($handle);
        }
    }

    public static function create($dir, $chmod = 0775)
    {
        return self::available($dir) ? true : mkdir($dir, $chmod, true);
    }

    public static function delete($dir)
    {
        $temp = false;

        if ($handle = opendir($dir)) {
            $temp = true;

            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..') {
                    $path = $dir . DIRECTORY_SEPARATOR . $file;

                    if (self::available($path)) {
                        $temp = self::delete($path);
                    } else {
                        $temp = unlink($path);
                    }
                }
            }

            closedir($handle);

            if ($temp) {
                $temp = rmdir($dir);
            }
        }

        return $temp;
    }

    public static function move($from, $to)
    {
        self::copy($from, $to);
        self::delete($from);

        return ! file_exists($from) && file_exists($to);
    }

    public static function name($dir)
    {
        return basename(dirname($dir));
    }

    public static function permission($dir)
    {
        clearstatcache();

        return mb_substr(sprintf('%o', fileperms($dir)), - 4);
    }

    public static function readable($dir)
    {
        return is_readable($dir) ? true : false;
    }

    public static function rename($from, $to)
    {
        return self::available($from) ? rename($from, $to) : false;
    }

    public static function size($dir)
    {
        $total = 0;

        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..') {
                    $path = $dir . DIRECTORY_SEPARATOR . $file;

                    if (self::available($path)) {
                        $total += self::size($path);
                    } else {
                        $total += filesize($path);
                    }
                }
            }
        }

        return $total;
    }

    public static function writable($dir)
    {
        return is_writable($dir) ? true : false;
    }
}
