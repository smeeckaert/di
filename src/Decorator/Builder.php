<?php

namespace FW\Decorator;

use FW\DI\Decorator;

/**
 * Class Builder
 * This class is used to find the correct arguments for a class constructor
 * @package FW\Decorator
 */
class Builder
{
    protected $methodParams = [];
    protected $errors = [];

    public function __construct()
    {

    }

    /**
     * Take a set of required parameters [name,class] and a set of object and tries to make them match
     * If they don't match of if there are some errors, these errors get be retrieved with the getErrors() method.
     * @param $required
     * @param $params
     */
    public function build($required, $params)
    {
        $this->errors = [];
        $this->methodParams = [];
        $availableParams = $params;
        $this->checkParams($params);
        foreach ($required as $position => $infos) {
            /**
             * If a parameter is named, we just set it
             * The setParams method will check if the object is of correct type
             */
            if (!empty($availableParams[$infos['name']])) {
                $this->setParams($position, $infos, $availableParams[$infos['name']]);
                unset($availableParams[$infos['name']]);
            } else {
                $hasParams = false;
                /**
                 * If we have a class, we search a candidate for the injection
                 * If the parameter is scalar, it __MUST__ be named, otherwise we can't match it
                 */
                if (!empty($infos['class'])) {
                    // Searching by class if we found it
                    $searchedClass = $infos['class'];
                    foreach ($availableParams as $key => $type) {
                        if (is_a($type, $searchedClass)) {
                            if (is_numeric($key) || $key === $infos['name']) {
                                $this->setParams($position, $infos, $type);
                                $hasParams = true;
                            }
                            unset($availableParams[$key]);
                            break;
                        }
                    }
                }
                if (!$hasParams) {
                    $this->errors[] = sprintf("Can't find matching dependancy for parameter #%s $%s of type %s", $position, $infos['name'], !empty($infos['class']) ? $infos['class'] : 'scalar type');
                }
            }
        }

    }

    /**
     * Check if parameters are usable
     * Mainly if parameters are still decorators it will break the injection
     * @param $params
     */
    protected function checkParams($params)
    {
        foreach ($params as $type) {
            if (is_a($type, Decorator::class)) {
                $this->errors[] = "Parameter is not well instantiated [" . (string)$type . "]";
            }
        }
    }

    /**
     * Set into the methodParams array the correct param for the position
     * @param $position
     * @param $required
     * @param $param
     * @return bool
     */
    protected function setParams($position, $required, $param)
    {
        if (!empty($required['class']) && !is_a($param, $required['class'])) {
            $this->errors[] = sprintf("You can't put an instance of %s in the parameter #%s $%s of type %s",
                get_class($param), $position, $required['name'], $required['class']);
            return false;
        }
        $this->methodParams[$position] = $param;
    }

    /**
     * Get the sorted contructor params
     * @return array
     */
    public function getParams()
    {
        return $this->methodParams;
    }

    /**
     * Get the list of errors encountered when trying to find parameters
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}