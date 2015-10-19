<?php
namespace Sampadeh;

use Closure;

trait Macro
{

    protected static $macro = [];

    public static function has($name)
    {
        return array_key_exists($name, self::$macro);
    }

    public static function macro($name, $callable)
    {
        self::$macro[$name] = $callable;
    }

    public static function __callStatic($method, $parameter)
    {
        if (self::has($method)) {
            if (self::$macro[$method] instanceof Closure) {
                return call_user_func_array(Closure::bind(self::$macro[$method], null, get_called_class()), $parameter);
            } else {
                return call_user_func_array(self::$macro[$method], $parameter);
            }
        }
    }

    public function __call($method, $parameter)
    {
        return self::__callStatic($method, $parameter);
    }
}
