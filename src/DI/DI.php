<?php

namespace FW\DI;

use FW\DI\Exception;

trait DI
{

    protected $propertyTypes = [];

    /**
     * @return static
     */
    public static function build()
    {
        $decorator = new Decorator(get_called_class());
        return $decorator->make();
    }

    public function __construct()
    {
        // We need to recover the data types since they will get busted by the decorator

        $class      = new \ReflectionClass(get_class());
        $properties = $class->getDefaultProperties();

        // This prevents any default string value, so it could be enhanced
        foreach ($properties as $propName => $property) {
            if (is_string($property) && class_exists($property)) {
                $this->propertyTypes[$propName] = $property;
            }
        }
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
        return $this->setProperty($object, $name);
    }

    /**
     * Add the prooperty to the object
     *
     * @param $object
     * @param $name
     *
     * @return $this
     */
    protected function setProperty($object, $name)
    {
        if ($name) {
            $this->assignProperty($name, $object);
        } else {
            $objectClass = get_class($object);
            $match       = null;
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
                trigger_error("Object of class $objectClass given to ".get_class()." but it seems unused.", E_USER_WARNING);
            } else {
                $this->assignProperty($match, $object);
            }
            // Find the correct field to put that, otherwise throw a big error
        }
        return $this;
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
        $this->$name = $value;
    }

}