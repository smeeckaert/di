<?php

namespace FW\DI;

use FW\DI\Decorator\Immutable;

trait DI
{

    protected $propertyTypes = [];
    protected $restrictedProperties = [];
    protected $frozen = false;

    /**
     * @return static
     */
    public static function build()
    {
        $decorator = new Decorator(get_called_class());
        return $decorator->make();
    }

    /**
     * @return static
     */
    public static function buildImmutable()
    {
        $decorator = new Immutable(get_called_class());
        return $decorator->make();
    }

    /**
     * @return static
     */
    public static function buildSoftImmutable()
    {
        $decorator = new Immutable\Soft(get_called_class());
        return $decorator->make();
    }


    /**
     * Add automatically a dependancy to the class.
     * If the parameter $name is specified, it will be used as the object property.
     *
     * @param      $object
     * @param null $name
     *
     * @return $this
     */
    public function with($object, $name = null)
    {
        if (is_array($object)) {
            $carry = null;
            foreach ($object as $propertyName => $value) {
                $carry = $this->setProperty($value, $propertyName);
            }
            return $carry;
        }
        return $this->setProperty($object, $name);
    }

    /**
     * Freeze the object against further modification of protected and private methods
     */
    public function freeze()
    {
        $this->frozen = true;
    }

    /**
     * Add the property to the object
     *
     * @param $object
     * @param $name
     * @throws Exception
     *
     * @return $this
     */
    protected function setProperty($object, $name)
    {
        if (empty($this->propertyTypes)) {
            // We need to recover the data types since they will get busted by the decorator
            $this->propertyTypes = $this->getPropertyTypes(get_class());
            $this->restrictedProperties = $this->getRestrictedProperties(get_class());
        }
        if ($name) {
            $this->assignProperty($name, $object);
        } else {
            $objectClass = get_class($object);
            $match = null;
            foreach ($this->propertyTypes as $name => $type) {
                if (is_a($object, $type)) {
                    if ($match) {
                        throw new \FW\DI\Exception("You must specify a name for the property of type $objectClass,
                        since at least two parameters ($match and $name) share the same class");
                    }
                    $match = $name;
                }
            }
            if (!$match) {
                // We gave an unused class in the DI, it doesn't serve any purpose but it won't break the build
                trigger_error("Object of class $objectClass given to " . get_class() . " but it seems unused.", E_USER_WARNING);
            } else {
                $this->assignProperty($match, $object);
            }
        }
        return $this;
    }

    /**
     * Return an array of hinted type for class properties
     * If they don't have a type, we ignore it
     *
     * @param $class
     * @return array
     */
    protected function getPropertyTypes($class)
    {
        $class = new \ReflectionClass($class);
        $properties = $class->getDefaultProperties();
        $propertyTypes = [];

        // This prevents any default string value, so it could be enhanced
        foreach ($properties as $propName => $property) {
            if (is_string($property) && class_exists($property)) {
                $propertyTypes[$propName] = $property;
            }
        }
        return $propertyTypes;
    }

    protected function getRestrictedProperties($class)
    {
        $class = new \ReflectionClass($class);
        $properties = $class->getProperties();
        $restrictedProperties = [];

        foreach ($properties as $propName => $property) {
            if ($property->isPrivate() || $property->isProtected()) {
                $restrictedProperties[] = $property->getName();
            }
        }
        return $restrictedProperties;
    }

    /**
     * Assing the property while checking if the type matches
     *
     * @param $name
     * @param $value
     *
     * @throws Exception
     */
    protected function assignProperty($name, $value)
    {
        if (!empty($this->propertyTypes[$name]) && $value !== null) {
            if (!is_a($value, $this->propertyTypes[$name])) {
                throw new Exception(
                    sprintf("You can't put an instance of %s in the parameter $%s of type %s",
                        get_class($value), $name, $this->propertyTypes[$name]
                    )
                );
            }
        }
        if ($this->frozen && in_array($name, $this->restrictedProperties)) {
            throw new Exception(
                sprintf("You can't change the protected or private parameter $%s of type %s",
                    $name, !empty($this->propertyTypes[$name]) ? $this->propertyTypes[$name] : "scalar"
                )
            );
        }
        $this->$name = $value;
    }

}