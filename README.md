# DI

DI is an experiment on dependancy injection in PHP.

The aim of the experiment is to allow the simplest DI in PHP classes making it easily implementable in existing classes
as well as working with legacy classes.

There are no God Container and it is based on convention.

It works in PHP 7.0 and hopefully in PHP 5.6.

All codes examples in this doc can be found in the demo folder.

## Overview

The trait `\FW\DI\DI` is all you need to declare in a class to benefit from the DI.

```php
// DBConnection.php
<?php
class DBConnection
{
    use \FW\DI\DI;

    /**
    * Common protected parameters
    **/
    protected $host;
    protected $user;
    protected $password;

    /**
    * DI contructor (see below)
    **/
    public function __construct($host, $user, $password)
    {
    }

    public function isConnected()
    {
        return true;
    }
}
```

Now you can build the object with the `build` static method an the `with` method which works in two ways:

```php
<?php

// As an array of named parameters
$dbCon = DBConnection::build()->with(['host' => 'localhost', 'user' => 'root', 'password' => 'pwd']);

// OR a list of chained parameters (first the value, then the name)

$dbCon = DBConnection::build()->with('localhost', 'host')->with('root', 'user')->with('pwd', 'password');

var_dump($dbCon);
```

Once the object is built, you can't change any protected or private property via the `with` method.

```php
<?php
// WithLock.php

class DBConnection
{
    use \FW\DI\DI;

    protected $host;

    public function __construct($host)
    {
    }

    public function hello()
    {
        return 'hello';
    }
}


$model = DBConnection::build()->with('localhost', 'host');
echo $model->hello() . "\n";

try {
    $model->with('test', 'host'); // This will fail
    echo $model->hello() . "\n";
} catch (Exception $e) {
    var_dump($e->getMessage());
}

```

## __constructor and required properties

If you want to make properties mandatory you have to create a `__construct` method taking parameters which names must match those of the mandatory properties.

```php
<?php
// Orchard.php
class Orchard
{
    use \FW\DI\DI;

    protected $apple;
    protected $pear;
    protected $orange;

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

```


## Parameter type hint

You can add some type hint in your parameters to prevent wrong objects to be injected.

As of now you have to :
* Add the class of the object as a default value of the object property. (it's the only way to do it due to the lack of property type-hinting in PHP as of 7.0)
* If the object is required, add it in the __construct method.

```php
<?php
// TypeHint.php
class DBConnection
{
    use \FW\DI\DI;

    protected $host;
    protected $user;
    protected $password;

    public function __construct($host)
    {
    }
}


class DBExtend extends DBConnection
{
}

class Model
{
    use \FW\DI\DI;

    protected $connection = DBExtend::class;
    protected $table;

    public function __construct($connection)
    {
    }

    public function hello()
    {
        return 'hello';
    }
}


$model = Model::build()->with(DBExtend::build()->with('localhost', 'host')); // it will automatically match the $connection parameter
echo $model->hello() . "\n";

try {
    $model = Model::build()->with(DBConnection::build()->with('localhost', 'host')); // This will fail
    echo $model->hello() . "\n";
} catch (Exception $e) {
    var_dump($e->getMessage());
}
```

To make a property mandatory you can either type-hint it or name it exactly as the object property

```
<?php
class Model
{
    use \FW\DI\DI;

    protected $connection = DBExtend::class;
    protected $table;

    // Either

    public function __construct($connection)
    {
    }
    
    // OR
    
    public function __construct(DBExtend $myRenamedArgument)
    {
    }

    // But NOT
    
    public function __construct($myRenamedArgument)
    {
    }
}
```

## Autobuild

Todo

## Immutability

You can build an immutable object by calling `buildImmutable` instead of `build`.

An immutable object's properties can't be altered by outside calls or inside calls.

```php
<?php
// Car.php
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
```

Alternatively you can use the `buildSoftImmutable` method. It works the same way but only prevent outside change of the object.

```php
<?php
// SoftImmutable.php
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
```

## Known issues

There is a bug when trying to debug a non built object that will cause a fatal error, at least in PHP 7.0. (check demo/BugDebugInfo.php).It was fixed by http://git.php.net/?p=php-src.git;a=commit;h=2d8ab51576695630a7471ff829cc5ea10becdc0f

As of now, because of the lack of type hinting on class properties, you can't set a default value for a property to the name of an actual class.

