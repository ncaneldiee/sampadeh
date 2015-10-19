<?php
namespace Sampadeh;

class File
{

    public static function available($file)
    {
        return is_file($file) ? true : false;
    }

    public static function copy($from, $to)
    {
        return copy($from, $to);
    }

    public static function create($file)
    {
        if (self::available($file)) {
            return true;
        } else {
            if ($handle = fopen($file, 'w')) {
                fclose($handle);
            }
        }
    }

    public static function delete($file)
    {
        if (self::available($file)) {
            if (is_array($file)) {
                foreach ($file as $value) {
                    unlink($value);
                }
            } else {
                return unlink($file);
            }
        }
    }

    public static function display($file)
    {
        if (self::available($file)) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $info = self::info($file);

            header('Pragma: public');
            header('Expires: 0');
            header('Last-Modified: ' . date('D, d M Y H:i:s', $info['time']['change']) . ' GMT');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            header('Content-Disposition: inline; filename="' . $info['name'] . '"');
            header('Content-Length: ' . self::size($file));
            header('Content-Transfer-Encoding: binary');
            header('Content-type: ' . $info['mime']);

            readfile($info['path']);

            exit();
        }
    }

    public static function download($file, array $secure = [], $speed = 0)
    {
        if (array_key_exists('path', $secure)) {
            $file = (mb_substr($secure['path'], - 1) == DIRECTORY_SEPARATOR ? $secure['path'] : $secure['path']) . DIRECTORY_SEPARATOR . $file;
        }

        if (self::available($file)) {
            $info = self::info($file);
            $name = array_key_exists('name', $secure) ? $secure['name'] : $info['name'];

            if (array_key_exists('referer', $secure)) {
                if (! array_key_exists('HTTP_REFERER', $_SERVER) || mb_strpos(mb_strtoupper($_SERVER['HTTP_REFERER']), mb_strtoupper($secure['referer'])) === false) {
                    exit();
                }
            }

            header('Pragma: public');
            header('Expires: 0');
            header('Last-Modified: ' . date('D, d M Y H:i:s', $info['time']['change']) . ' GMT');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename="' . $name . '"');
            header('Content-Length: ' . self::size($file));
            header('Content-Transfer-Encoding: binary');
            header('Content-type: ' . $info['mime']);
            ob_clean();
            flush();

            if ($speed === 0) {
                readfile($file);
            } else {
                while (! feof($file = fopen($file, 'r'))) {
                    print(fread($file, round($speed * 1024)));
                    flush();
                    sleep();
                }

                fclose($file);
            }

            exit();
        }
    }

    public static function extension($file)
    {
        return mb_strtolower(mb_substr(mb_strrchr($file, '.'), 1));
    }

    public static function info($file)
    {
        $temp = [];

        $temp['name'] = self::name($file);
        $temp['path'] = realpath($file);
        $temp['size'] = self::size($file);
        $temp['mime'] = Mime::get($file);
        $temp['extension'] = self::extension($file);
        $temp['permission'] = self::permission($file);
        $temp['time']['change'] = filemtime($file);
        $temp['time']['access'] = fileatime($file);

        return $temp;
    }

    public static function move($from, $to)
    {
        self::copy($from, $to);
        self::delete($from);

        return ! file_exists($from) && file_exists($to);
    }

    public static function name($file)
    {
        return basename($file);
    }

    public static function permission($file)
    {
        clearstatcache();

        return mb_substr(sprintf('%o', fileperms($file)), - 4);
    }

    public static function read($file)
    {
        $handle = fopen($file, 'r');
        $data = fread($handle, filesize($file));
        fclose($handle);

        return $data;
    }

    public static function readable($file)
    {
        return is_readable($file) ? true : false;
    }

    public static function rename($from, $to)
    {
        return self::available($from) ? rename($from, $to) : false;
    }

    public static function size($file)
    {
        return filesize($file);
    }

    public static function upload($file, $dir)
    {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_uploaded_file($file['tmp_name']) && move_uploaded_file($file['tmp_name'], $path)) {
                return true;
            }
        }

        return false;
    }

    public static function writable($file)
    {
        return is_writable($file) ? true : false;
    }

    public static function write($file, $data)
    {
        if ($handle = fopen($file, 'w')) {
            fwrite($handle, $data);
            fclose($handle);

            return true;
        }

        return false;
    }
}
