<?php

namespace FW\DI;

use FW\Decorator\Builder;

class Decorator
{
    use DI;

    protected $reflexion;
    protected $className;
    protected $params = [];
    protected $requiredParams = [];
    protected $builder;
    protected $constructMethod = null;

    public function __construct($className)
    {
        $this->className = $className;
        $this->builder = new Builder();
        $this->propertyTypes = $this->getPropertyTypes($className);
        $this->getReflectionInfos();
    }


    /**
     * Try to build the object, if we can't return this class
     *
     * @return $this
     */
    public function make()
    {
        if (($params = $this->canBuild())) {
            $reflectionClass = new \ReflectionClass($this->className);
            $object = $reflectionClass->newInstanceArgs(is_array($params) ? $params : []);
            /**
             * Reset the object by removing default values used for typehinting
             * Then add our dependancies
             */
            $this->resetObject($object);
            foreach ($this->params as $key => $value) {
                $object->with($value, (is_numeric($key) ? null : $key));
            }
            $object->freeze();
            return $object;
        }
        return $this;
    }

    /**
     * Here we are overriding any access made to the decorator before the object is built
     * This will display a list of errors if the build failed
     */

    public function __toString()
    {
        try {
            $this->decoratorError();
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return "";
    }

    // This will cause a fatal error
    public function __debugInfo()
    {
        $this->decoratorError();
        return [];
    }

    public function __call($a, $b)
    {
        $this->decoratorError();
    }

    public function __get($a)
    {
        $this->decoratorError();
    }

    public function __set($a, $b)
    {
        $this->decoratorError();
    }

    public function __isset($a)
    {
        $this->decoratorError();
    }

    public function __unset($a)
    {
        $this->decoratorError();
    }

    public function __invoke($a)
    {
        $this->decoratorError();
    }

    /**
     * Find the construct parameters
     */
    protected function getReflectionInfos()
    {
        try {
            $this->constructMethod = new \ReflectionMethod($this->className, "__construct");
            $params = $this->constructMethod->getParameters();
            $i = 0;
            foreach ($params as $param) {
                $paramsInfos = ['name' => $param->getName()];
                // Either the class is typehinted
                if (!empty($param->getClass())) {
                    $paramsInfos['class'] = $param->getClass()->getName();
                } elseif (!empty($this->propertyTypes[$param->getName()])) {
                    // Or we take it from the argument name
                    $paramsInfos['class'] = $this->propertyTypes[$param->getName()];
                }
                $this->requiredParams[$i++] = $paramsInfos;
            }
        } catch (\ReflectionException $e) {
            // There is no construct method but we don't really care
        }
    }

    /**
     * Add the property to the parameters and tries to build the object
     *
     * @param      $object
     * @param null $name
     *
     * @return Decorator
     */
    protected function setProperty($object, $name = null)
    {
        if ($name === null) {
            $this->params[] = $object;
        } else {
            $this->params[$name] = $object;
        }
        return $this->make();
    }


    /**
     * Reset the default object values to null if they were string
     *
     * @param DI $object
     */
    protected function resetObject(&$object)
    {
        $class = new \ReflectionClass($this->className);
        $properties = $class->getDefaultProperties();

        foreach ($properties as $propName => $property) {
            if (is_string($property) && class_exists($property)) {
                $object->with(null, $propName);
            }
        }
    }

    /**
     * Return true if the class doesn't require parameters
     * Return an array of parameters if all parameters can be found
     * Return false if we can't construct the object
     *
     * @return array|bool
     */
    protected function canBuild()
    {
        if (!empty($this->requiredParams)) {
            $this->builder->build($this->requiredParams, $this->params);

            if ($this->builder->getErrors()) {
                return false;
            }
            return $this->builder->getParams();
        }
        return true;
    }


    /**
     * Throw a build exception for every error found in the building process
     * @throws Exception
     */
    protected function decoratorError()
    {
        $errors = $this->builder->getErrors();
        if (empty($errors)) {
            $this->builder->build($this->requiredParams, $this->params);
        }
        var_dump($this->requiredParams);
        var_dump($this->params);
        throw new Exception(sprintf("Error when building %s - %s", $this->className, implode(' - ', $this->builder->getErrors())));
    }
}