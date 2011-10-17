<?php
include "RequestObject.php";

class Request extends RequestObject
{
    /** @cast string */
    public $param1;
    /**
     * @cast string
     * @default default value
     */
    public $param2;
}

$request = new Request();

echo "param1: <br/>";
var_dump($request->param1);
echo "<br/>";

echo "param2: <br/>";
var_dump($request->param2);
echo "<br/>";

/*
test2.php?param1=hi&param2=1

param1: string(2) "hi"
param2: string(1) "1"

test2.php?param1=1&param2=hi

param1: string(1) "1"
param2: string(2) "hi"

test2.php?param1=1
param1: string(1) "1"
param2: string(13) "default value"
 */
