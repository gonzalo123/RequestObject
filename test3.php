<?php
include "RequestObject.php";

class Request extends RequestObject
{
    /** @cast string */
    public $param1;
    /** @cast integer */
    public $param2;

    protected function validate_param1(&$value)
    {
        $value = strrev($value);
    }
    
    protected function validate_param2($value)
    {
        if ($value == 1) {
            return false;
        }
    }
}
try {
    $request = new Request();

    echo "param1: <br/>";
    var_dump($request->param1);
    echo "<br/>";

    echo "param2: <br/>";
    var_dump($request->param2);
    echo "<br/>";
} catch (RequestObjectException $e) {
    echo $e->getMessage();
    echo "<br/>";
    var_dump($e->getValidationErrors());
}

/*
test3.php?param2=2&param1=hi
param1: string(2) "ih"
param2: int(2)

test3.php?param1=hola&param2=1
Validation error
array(1) { [1]=> array(1) { ["value"]=> NULL } }
*/
