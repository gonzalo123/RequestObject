<?php
include "RequestObject.php";

class Request extends RequestObject
{
    /** @cast string */
    public $param1;
    /** @cast integer */
    public $param2;
}

$request = new Request(false); // disables perform validation on contructor
                               // it means it will not raise any validation exception
$request->appendValidateTo('param2', function($value) {
        if ($value == 1) {
            return false;
        }

        $value = '1';
    });

try {
    $request->validateAll();

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
test4.php?param1=hola&param2=2
param1: string(4) "hola"
param2: int(2)

test4.php?param1=hola&param2=1
Validation error
array(1) { [1]=> array(1) { ["value"]=> NULL } }
 */
