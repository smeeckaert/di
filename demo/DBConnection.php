<?php

require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);

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

// As an array of named parameters
$dbCon = DBConnection::build()->with(['host' => 'localhost', 'user' => 'root', 'password' => 'pwd']);

// OR a list of chained parameters

$dbCon = DBConnection::build()->with('localhost', 'host')->with('root', 'user')->with('pwd', 'password');

var_dump($dbCon);