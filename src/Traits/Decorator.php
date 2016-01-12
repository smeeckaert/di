<?php

namespace FW\Traits;

use FW\Decorator\Builder;
use FW\Decorator\Exception;

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
        $this->getReflectionInfos();
    }

    /**
     * Find the construct parameters
     */
    protected function getReflectionInfos()
    {
        try {
            $this->constructMethod = new \ReflectionMethod($this->className, "construct");
            $params                = $this->constructMethod->getParameters();
            $i                     = 0;
            foreach ($params as $param) {
                $this->requiredParams[$i++] = ['name' => $param->getName(), 'class' => $param->getClass()->getName()];
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
     * Try to build the object, if we can't return this class
     *
     * @return $this
     */
    public function make()
    {
        if (($params = $this->canBuild())) {
            $object = new $this->className();
            if ($this->constructMethod) {
                call_user_func_array(array($object, $this->constructMethod->getName()), $params);
            }

            $this->resetObject($object);

            foreach ($this->params as $key => $value) {
                $object->with($value, (is_numeric($key) ? null : $key));
            }

            return $object;
        }
        return $this;
    }

    /**
     * Reset the default object values to null if they were string
     *
     * @param DI $object
     */
    protected function resetObject(&$object)
    {
        $class      = new \ReflectionClass($this->className);
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
            $this->builder = new Builder($this->requiredParams, $this->params);

            if ($this->builder->getErrors()) {
                return false;
            }
            return $this->builder->getParams();
        }
        return true;
    }

    public function __toString()
    {
        $this->decoratorError();
    }

    public function __debugInfo()
    {
        $this->decoratorError();
    }

    public function __call($a, $b)
    {
        $this->decoratorError();
    }

    public function __invoke()
    {
        $this->decoratorError();
    }

    protected function decoratorError()
    {
        throw new Exception("Decorator " . implode(' - ', $this->builder->getErrors()));
    }
}