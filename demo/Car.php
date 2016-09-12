<?php

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);

class Car
{
    use \FW\DI\DI;

    public $window = Window::class;

    public function construct(Window $window)
    {
    }
}

class Window
{
    use \FW\DI\DI;
}

$car = Car::buildImmutable()->with(Window::build());
$carMutable = Car::build()->with(Window::build());

var_dump($car->window);

try {
    $car->window = Window::build(); // Will throw an error
} catch (Exception $e) {
    var_dump($e->getMessage());
}

$carMutable->window = Window::build(); // Will work