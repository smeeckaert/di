<?php

namespace FW\DI;

use FW\DI\AutoBuild\Exception;

/**
 * Class AutoBuild
 * Register default class parameters to be easily builded
 *
 * @package FW\DI
 */
class AutoBuild
{
    protected static $registeredClasses = [];

    /**
     * Register a new class with its default parameter
     * The parameters can either be an array of [name => value] properties or a callback returning this array
     *
     * @param $className
     * @param $parameters
     * @throws Exception
     */
    public static function register($className, $parameters)
    {
        /**
         * If the class is already register, we throw an exception
         * Maybe it's not the best idea, because it makes it not overridable, time will tell
         */
        if (isset(static::$registeredClasses[$className])) {
            throw new Exception("The class $className is already registered");
        }
        static::$registeredClasses[$className] = $parameters;
    }

    /**
     * Get the registered parameters for a given class
     * @param $className
     * @return mixed|null
     */
    public static function getDefaultParameters($className)
    {
        if (!isset(static::$registeredClasses[$className])) {
            return null;
        }
        $params = static::$registeredClasses[$className];
        if (is_callable($params)) {
            $params = $params();
        }
        return $params;
    }

    /**
     * Get a new instance of a registered class, returns null if the class is not registered
     *
     * @param $className
     * @return DI
     */
    public static function getInstance($className)
    {
        if (!isset(static::$registeredClasses[$className])) {
            return null;
        }
        /** @var DI $object */
        $object = $className::build()->auto();
        return $object;
    }
}