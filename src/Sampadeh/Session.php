<?php
namespace Sampadeh;

class Session
{

    public static function expire($time = 30)
    {
        $activity = self::has('activity') ? self::get('activity') : false;
        
        if ($activity !== false && time() - $activity > $time * 60) {
            return true;
        }
        
        self::set('activity', time());
        
        return false;
    }

    public static function forget($key)
    {
        return Arr::forget($_SESSION, $key);
    }

    public static function get($key, $default = null)
    {
        return Arr::get($_SESSION, $key, $default);
    }

    public static function has($key)
    {
        return Arr::has($_SESSION, $key);
    }

    public static function set($key, $value)
    {
        return Arr::set($_SESSION, $key, $value);
    }

    public static function start($name)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.use_cookies', 1);
            ini_set('session.use_only_cookies', 1);
            
            session_name($name);
            
            session_set_cookie_params(0, ini_get('session.cookie_path'), ini_get('session.cookie_domain'), isset($_SERVER['HTTPS']), true);
            
            if (session_start()) {
                return mt_rand(0, 4) === 0 ? session_regenerate_id(true) : true;
            }
        }
        
        return false;
    }

    public static function stop($name)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        
        $_SESSION = [];
        
        setcookie($name, '', time() - 86400, ini_get('session.cookie_path'), ini_get('session.cookie_domain'), isset($_SERVER['HTTPS']), true);
        
        return session_destroy();
    }
}
