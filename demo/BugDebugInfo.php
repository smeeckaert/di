<?php

class Debug
{
    public function __debugInfo()
    {
        throw new Exception("Test Crash");
    }
}

try {
    var_dump(new Debug());
} catch (Exception $e) {

}