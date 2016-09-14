<?php

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);

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