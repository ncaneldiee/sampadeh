<?php
namespace Sampadeh;

class Log
{

    public static $path;

    private static $extension = '.log';

    public static function write($message)
    {
        return file_put_contents(self::$path . DIRECTORY_SEPARATOR . date('Ymd') . self::$extension, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
    }
}
