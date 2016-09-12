<?php

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);

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


$model = Model::build()->with(DBExtend::build()->with('localhost', 'host'));
echo $model->hello() . "\n";

try {
    $model = Model::build()->with(DBConnection::build()->with('localhost', 'host')); // This will fail
    echo $model->hello() . "\n";
} catch (Exception $e) {
    var_dump($e->getMessage());
}
