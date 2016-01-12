<?php

namespace FW\Decorator;

class Builder
{
    protected $methodParams = [];
    protected $errors = [];

    public function __construct($required, $params)
    {
        $this->errors       = [];
        $this->methodParams = [];
        $availableParams    = $params;
        foreach ($required as $position => $infos) {

            if (!empty($availableParams[$infos['name']])) {
                $this->setParams($position, $infos, $availableParams[$infos['name']]);
                unset($availableParams[$infos['name']]);
            } else {
                // Searching by class if we found it
                $searchedClass = $infos['class'];
                $hasParams     = false;
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
                if (!$hasParams) {
                    $this->errors[] = "Can't find matching dependancy for parameter $position";
                }
            }
        }

    }

    protected function setParams($position, $required, $param)
    {
        if (!is_a($param, $required['class'])) {
            $this->errors[] = "Wrong type for param $position";
            return false;
        }
        $this->methodParams[$position] = $param;
    }

    public function getParams()
    {
        return $this->methodParams;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}