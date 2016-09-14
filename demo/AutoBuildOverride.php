<?php

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);

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
    $overridenPost = Model::build()->with(DBConnection::build()->with('127.0.0.1', 'host'), 'connection')->auto();
    var_dump($overridenPost->connection->host);
} catch (Exception $e) {
    var_dump($e->getMessage());
}