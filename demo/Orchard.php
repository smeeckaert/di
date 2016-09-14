<?php

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);

class Orchard
{
    use \FW\DI\DI;

    /**
     * Common protected parameters
     **/
    protected $apple;
    protected $pear;
    protected $orange;

    /**
     * DI contructor (see below)
     **/
    public function __construct($apple)
    {
    }
}

try {
    var_dump(Orchard::build()->with(1, 'orange')); // Will generate an error since apple is a required property
} catch (Exception $e) {
    var_dump($e->getMessage());
}
var_dump(Orchard::build()->with(1, 'orange')->with(1, 'apple'));