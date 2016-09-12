<pre>
<?php

require 'vendor/autoload.php';
error_reporting(E_ALL);

function d($item)
{
    echo "\n" . var_dump($item) . "\n";
}

class Unused
{

}

class B
{
    use \FW\DI\DI;

    public $c = C::class;

    public function construct(C $c)
    {

    }
}

class C
{
    use \FW\DI\DI;
}

class D
{

}

class SubD extends D
{

}

class Test
{
    use \FW\DI\DI;

    protected $c = C::class;
    protected $b = B::class;
    public $a = D::class;
    public $d = SubD::class;
    public $e = "test";

    public function construct(C $c, B $b)
    {
        echo "constructed";
    }

    public function meth()
    {
        d("meth called");
    }
}


$item = B::build();
$testC = C::build();

d($testC);

try {
    $test = Test::build()
        ->with(
            B::build()
                ->with(C::build())
        )
        ->with(C::build())
        ->with(new D(), 'd')
        ->with(new Unused());

    try {
        $test->meth();
    } catch (\FW\Decorator\Exception $e) {
        d($e->getMessage());
    }
} catch (Exception $e) {
    d($e->getMessage());
}


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

    public function construct($host, $user, $password)
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

    public function construct(DBConnection $connection)
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


d($dbCon->isConnected());
d($model);


$model->id = 42;
d($model->id);
d((string)$model);
d(isset($model->title));
d($model->title);


d("#### Immutable");
// Once build it can't be changed externally
$modelImmut = Model_Post::buildImmutable()->with($dbCon);

d(isset($modelImmut->id));
try {
    $modelImmut->id = 25;
} catch (Exception $e) {
    d($e->getMessage());
}
d(isset($modelImmut->id));
$modelImmut->setId(22);
d($modelImmut->id);
d(isset($modelImmut->title));
d(isset($modelImmut->id));
d((string)$modelImmut);
