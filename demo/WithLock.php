<?php

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);

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
