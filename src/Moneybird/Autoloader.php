<?php
namespace Moneybird;

class Autoloader {

    /**
     * @param string $className
     */
    public static function autoload($className) {
        if (strpos($className, "Moneybird") === 0) {
            $fileName = str_replace("\\", "/", $className);
            $fileName = realpath(dirname(__DIR__) . "/{$fileName}.php");

            if ($fileName !== FALSE) {
                require $fileName;
            }
        }
    }

    /**
     * @return bool
     */
    public static function register() {
        return spl_autoload_register([ __CLASS__, "autoload" ]);
    }

    /**
     * @return bool
     */
    public static function unregister() {
        return spl_autoload_unregister([ __CLASS__, "autoload" ]);
    }
}

Autoloader::register();
