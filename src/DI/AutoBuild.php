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
        foreach (static::$registeredClasses[$className] as $name => $value) {
            $object = $object->with($value, is_numeric($name) ? null : $name);
        }
        return $object;
    }
}