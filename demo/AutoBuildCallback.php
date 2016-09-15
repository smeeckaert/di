<?php

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);

class DBConnection
{
    use \FW\DI\DI;

    protected $host;
    protected $user;
    protected $password;

    public function __construct($host)
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

\FW\DI\AutoBuild::register(DBConnection::class, function () {
    return ['host' => 'localhost'];
});

try {
    $post = Model_Post::build()->auto();
    var_dump($post->getHost());
} catch (Exception $e) {
    var_dump($e->getMessage());
}