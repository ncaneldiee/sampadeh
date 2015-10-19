<?php
namespace Sampadeh;

class Arr
{

    use Macro;

    public static function forget(&$array, $key)
    {
        $temp = &$array;

        foreach ((array) $key as $path) {
            $segment = explode('.', $path);

            while (count($segment) > 1) {
                $part = array_shift($segment);

                if (self::has($array, $part)) {
                    $array = &$array[$part];
                }
            }

            unset($array[array_shift($segment)]);

            $array = &$temp;
        }
    }

    public static function get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    public static function has($array, $key)
    {
        if (empty($array) || is_null($key)) {
            return false;
        }

        if (array_key_exists($key, $array)) {
            return true;
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }

    public static function object($array)
    {
        return is_array($array) ? (object) array_map([
            __CLASS__,
            __METHOD__
        ], $array) : $array;
    }

    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $data = explode('.', $key);

        while (count($data) > 1) {
            $key = array_shift($data);

            if (! is_array($array) || ! array_key_exists($key, $array)) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($data)] = $value;

        return $array;
    }
}
