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

$car = Car::buildImmutable()->with(Window::build()->with('win1', 'name'));
var_dump($car->window->name);
$carMutable = Car::build()->with(Window::build()->with('win2', 'name'));
var_dump($carMutable->window->name);


$carMutable->window = Window::build()->with('win3', 'name'); // Will work
var_dump($carMutable->window->name);
$newWindow = Window::build()->with('win4', 'name');
$carMutable->setWindow($newWindow);
var_dump($carMutable->window->name);

try {
    echo "Changing public property\n";
    $car->window = Window::build(); // Will throw an error
} catch (Exception $e) {
    var_dump($e->getMessage());
}


try {
    echo "Using method\n";
    $car->setWindow($newWindow); // Will throw an error
} catch (Exception $e) {
    var_dump($e->getMessage());
}

var_dump($car->window->name); // Will still be win1