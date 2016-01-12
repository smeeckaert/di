<pre>
<?php

require 'vendor/autoload.php';


function d($item)
{
    echo "\n".var_dump($item)."\n";
}

class Unused
{

}

class B
{
    use \FW\Traits\DI;

    public $c = C::class;

    public function construct(C $c)
    {

    }
}

class C
{
    use \FW\Traits\DI;
}

class D
{

}

class SubD extends D
{

}

class Test
{
    use \FW\Traits\DI;

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
//d($item->c);
//die();
$testC = C::build();

d($testC);

$test = Test::build()->with(B::build()->with(C::build()))->with(C::build())->with(new D(), 'a')->with(new Unused());

try {
    $test->meth();
} catch (\FW\Decorator\Exception $e) {
    d($e->getMessage());
}

//d($test);