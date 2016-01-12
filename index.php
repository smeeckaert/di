<pre>
<?php

require 'vendor/autoload.php';
error_reporting(E_ALL);

function d($item)
{
    echo "\n".var_dump($item)."\n";
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

$test = Test::build()
    ->with(
        B::build()
            ->with(C::build()))
    ->with(C::build())
    ->with(new D(), 'd')
    ->with(new Unused());

try {
    $test->meth();
} catch (\FW\Decorator\Exception $e) {
    d($e->getMessage());
}
