# DI

DI is an experiment on dependancy injection in PHP.

The aim of the experiment is to allow the simplest DI in PHP classes making it easily implementable in existing classes
as well as working with legacy classes.

There are no God Container and it is based on convention.

It works in PHP 7.0 and hopefully in PHP 5.6.

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
    public function construct($host, $user, $password)
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

## Constructor and required properties

If you want to make properties mandatory you have to create a `construct` method taking parameters which names must match those of the mandatory properties.

```php
<?php
// Orchard.php
class Orchard
{
    use \FW\DI\DI;

    protected $apple;
    protected $pear;
    protected $orange;

    public function construct($apple)
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

You should **not** override the __construct() of the trait, if you want to do so you can do something like :

```php
<?php
class MySuperClass {

    use \FW\DI\DI {
        \FW\DI\DI::__construct as private __diConstruct;
    }

    public function __construct($a, $b, $c = 0)
    {
        $this->__diConstruct();
    }
}

```

## Parameter type hint

You can add some type hint in your parameters to prevent wrong objects to be injected.

As of now you have to :
* Add the class of the object as a default value
* If the object is required, add it as a type hint of the construct method. (note: I need to get rid of that because it causes signature problems when extending, and it duplicates the information anyway)

```php
<?php
// TypeHint.php
class DBConnection
{
    use \FW\DI\DI;

    protected $host;
    protected $user;
    protected $password;

    public function construct($host)
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

    public function construct(DBExtend $connection)
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


## Immutability

You can build an immutable object by calling `buildImmutable` instead of `build`.

An immutable object's properties can't be altered by outside calls.

**TODO**: Monitor any changes in any properties even in methods

```php
<?php
// Car.php
class Car
{
    use \FW\DI\DI;

    public $window = Window::class;

    public function construct(Window $window)
    {
    }
}

class Window {
}

$car = Car::buildImmutable()->with(Window::build());

var_dump($car->window);

try {
    $car->window = Window::build(); // Will throw an error
} catch(Exception $e) {
    echo $e->getMessage();
}
```

## Using with existing classes

## Known issues

There is a bug when trying to debug a non built object that will cause a fatal error, at least in PHP 7.0. (check demo/BugDebugInfo.php).