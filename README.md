# DI

DI is an experiment on dependancy injection in PHP.

The aim of the experiment is to allow the simplest DI in PHP classes making it easily implementable in existing classes
as well as working with legacy classes.

There are no God Container and it is based on convention.

It works in PHP 7.0 and hopefully in PHP 5.6. It will work well with any IDE understanding PHPDoc.

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

## __construct and required properties

If you want to make properties mandatory you have to create a `__construct` method taking parameters which names __must__ match those of the mandatory properties.

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

All you have to do is to add the class of the object as a default value of the object property.

_(it's the only way to do it due to the lack of property type-hinting in PHP as of 7.0)_


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

The `with` keyword will detect automatically the type of instanciated object given to him and will try to place them in the correct properties.
However if two or more properties share 

## AutoBuild

It can be tedious to repeat the same default arguments over and over.

To change that, there is an AutoBuild tool.

It works in two steps :
* First, you register you class and arguments with the `\FW\DI\AutoBuild::register` method.
* Then you call the `auto` method of a building object.

```php
<?php
// We register the DBConnection class in the AutoBuild
\FW\DI\AutoBuild::register(DBConnection::class, ['host' => 'localhost']);
// The Model_Post uses the AutoBuild to inject the dependancy
$post = Model_Post::build()->auto();
```


The AutoBuilding is cascading, meaning if one of your class dependancy is already registered,
you don't have to add it in the class parameters. 

```php
<?php

// You can do that way, but we are adding an instanciated object into the AutoBuild mechanism
\FW\DI\AutoBuild::register(DBConnection::class, ['host' => 'localhost', 'dependancy' => Dependancy::build()]);

// Or you can register the dependancy first

\FW\DI\AutoBuild::register(Dependancy::class, []);
// And omit it in subsequent registrations
\FW\DI\AutoBuild::register(DBConnection::class, ['host' => 'localhost']);
```

Here is an example of usage of the AutoBuild

```php
<?php
// AutoBuild.php
class Dependancy
{
    use \FW\DI\DI;
}

class DBConnection
{
    use \FW\DI\DI;

    protected $host;
    protected $user;
    protected $password;
    protected $dependancy = Dependancy::class;

    public function __construct($host, $dependancy)
    {
    }

    public function getHost()
    {
        return $this->host;
    }
}

class Model
{
    use \FW\DI\DI;

    protected $connection = DBConnection::class;
    protected $table;

    public function __construct($connection)
    {
    }

    public function getHost()
    {
        return $this->connection->getHost();
    }

}

class Model_Post extends Model
{
    protected $table = 'table';
}

\FW\DI\AutoBuild::register(Dependancy::class, []);
\FW\DI\AutoBuild::register(DBConnection::class, ['host' => 'localhost']); // $dependancy will be autoloaded since it's registered already

try {
    $post = Model_Post::build()->auto();
    var_dump($post->getHost());
} catch (Exception $e) {
    var_dump($e->getMessage());
}
```

You can only register **ONE** set of default parameters for a class in the AutoBuild.

You can override AutoBuild default parameters by using `with` before the `auto` method;

```php
<?php
// AutoBuildOverride.php
class Dependancy
{
    use \FW\DI\DI;

    public $name;

    public function __construct($name)
    {
    }
}

class DBConnection
{
    use \FW\DI\DI;

    public $host;

    public function __construct($host)
    {
    }

}

class Model
{
    use \FW\DI\DI;

    public $connection = DBConnection::class;
    public $dependancy = Dependancy::class;

    public function __construct($connection, $dependancy)
    {
    }

}


\FW\DI\AutoBuild::register(Dependancy::class, ['name' => 'AutoName']);
\FW\DI\AutoBuild::register(DBConnection::class, ['host' => 'localhost']); // $dependancy will be autoloaded since it's registered already

try {
    $post = Model::build()->auto();
    var_dump($post->connection->host);
    // We override the connection's parameter while still automatically building the Dependancy
    $overridenPost = Model::build()->with(DBConnection::build()->with('127.0.0.1', 'host'), 'connection')->auto();
    var_dump($overridenPost->connection->host);
} catch (Exception $e) {
    var_dump($e->getMessage());
}
```

Note: Every arguments given to the AutoBuilder is static, thus it will never be clean by the GC.
It's good to some things (like string, int, filenames and such) but avoid puting instanciated objects in it.

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


## How it works

The DI::build method will return a Decorator instance, which inherits the DI trait.

The Decorator act like the object but prevent accessing methods or property.

Every time a property is changed in the Decorator by the `with` call, it will call the Decorator\Builder
 to see if all the object mandatory parameters are found.

If every mandatory parameter is found, it will instanciate the new object, clean the default values used by DI and return it.

When using `buildImmutable` or `buildSoftImmutable`, the Decorator will never return the new object, 
instead it will keep the object instance protected and forward appropriate calls to the object.

## License

This project is released under the MIT license.