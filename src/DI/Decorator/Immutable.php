<?php

namespace FW\DI\Decorator;

use FW\DI\Decorator;

class Immutable extends Decorator
{
    protected $instance;

    /**
     * Try to build the object, if we can't return this class
     *
     * @return $this
     */
    public function make()
    {
        $this->instance = parent::make();
        return $this;
    }

    /**
     * Return true if the class is loaded
     * @return bool
     */
    protected function isLoaded()
    {
        return get_class($this->instance) == $this->className;
    }

    /**
     * If the object is loaded, call the callback, otherwise we call the parent method
     * @param $callback
     * @param $method
     * @param array $params
     * @return mixed
     */
    protected function forward($callback, $method, $params = [])
    {
        if ($this->isLoaded()) {
            return $callback();
        } else {
            return parent::$method($params);
        }
    }

    public function __toString()
    {
        return $this->forward(function () {
            return (string)$this->instance;
        }, '__toString');
    }

    public function __debugInfo()
    {
        return $this->forward(function () {
            return $this->instance;
        }, '__debugInfo');
    }

    public function __call($a, $b)
    {
        return $this->forward(function () use ($a, $b) {
            return call_user_func_array(array($this->instance, $a), $b);
        }, '__call', [$a, $b]);
    }

    public function __get($a)
    {
        return $this->forward(function () use ($a) {
            return $this->instance->{$a};
        }, '__get', [$a]);
    }

    public function __set($a, $b)
    {
        throw new Decorator\Immutable\Exception("The object is immutable");
    }

    public function __isset($a)
    {
        return $this->forward(function () use ($a) {
            return isset($this->instance->{$a});
        }, '__isset', [$a]);
    }

    public function __unset($a)
    {
        return $this->forward(function () use ($a) {
            unset($this->instance->{$a});
        }, '__unset', [$a]);
    }

    public function __invoke($a)
    {
        return $this->forward(function () use ($a) {
            return $this->instance($a);
        }, '__invoke', [$a]);
    }
}