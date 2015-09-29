<?php
namespace Sampadeh;

class Cookie
{

    public static function forget($name, $path = '/', $domain = null, $secure = false, $httponly = true)
    {
        return self::set($name, null, - 86400, $path, $domain, $secure, $httponly);
    }

    public static function get($name, $default = null)
    {
        return array_key_exists($name, $_COOKIE) ? $_COOKIE[$name] : $default;
    }

    public static function set($name, $value, $expire = 86400, $path = '/', $domain = null, $secure = false, $httponly = true)
    {
        $expire = $expire > 0 ? time() + $expire : 0;
        
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
}
