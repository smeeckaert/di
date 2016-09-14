<?php

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);

class Car
{
    use \FW\DI\DI;

    public $window = Window::class;

    public function __construct($window)
    {
    }

    public function setWindow($window)
    {
        $this->window = $window;
    }
}

class Window
{
    use \FW\DI\DI;

    public $name;

    public function __construct($name)
    {

    }
}

$car = Car::buildSoftImmutable()->with(Window::build()->with('win1', 'name'));
$newWindow = Window::build()->with('win4', 'name');
var_dump($car->window->name);

try {
    $car->window = Window::build()->with('win2', 'name'); // Will throw an error
} catch (Exception $e) {
    var_dump($e->getMessage());
}


try {
    $car->setWindow($newWindow); // Will work in soft mode
} catch (Exception $e) {
    var_dump($e->getMessage());
}

var_dump($car->window->name); // Will be win 4