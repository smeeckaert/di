<?php

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);

/****
 * DB Example
 *
 */
class DBConnection
{
    use \FW\DI\DI;

    protected $host;
    protected $user;
    protected $password;

    public function __construct($host, $user, $password)
    {
    }

    public function isConnected()
    {
        return true;
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
}

class Model_Post extends Model
{
    use \FW\DI\DI;

    public $id;
    public $title;
    public $content;
    protected $table = 'post';

    public function __toString()
    {
        return "Model_Post ({$this->table}) {$this->id}";
    }

    public function setId($i)
    {
        $this->id = $i;
    }
}


$dbCon = DBConnection::build()->with(['host' => 'localhost', 'user' => 'root', 'password' => 'pwd']);

$model = Model_Post::build()->with($dbCon);


var_dump($dbCon->isConnected()); // true
var_dump($model);
$model->id = 42;
var_dump($model->id); // 42
var_dump((string)$model); // Model_Post (post) 42
var_dump(isset($model->title)); // false
var_dump($model->title); // null


var_dump("#### Immutable");
// Once build it can't be changed externally
$modelImmut = Model_Post::buildImmutable()->with($dbCon);

var_dump(isset($modelImmut->id)); // false
try {
    $modelImmut->id = 25;
} catch (Exception $e) {
    var_dump($e->getMessage()); // Exception thrown
}
var_dump(isset($modelImmut->id)); // false
$modelImmut->setId(22);
var_dump($modelImmut->id); // 22
var_dump(isset($modelImmut->title)); // false
var_dump(isset($modelImmut->id)); // true
var_dump((string)$modelImmut); // Model_Post (post) 22