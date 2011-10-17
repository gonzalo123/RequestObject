<?php
include "RequestObject.php";

class Request extends RequestObject
{
    /** @cast arrayInteger */
    public $param1;
}

$request = new Request();

echo "param1: <p/>";
var_dump($request->param1);
/*
test5.php?param1[]=1&param1[]=2
param1: 11212

test1.php?param1=hola
param1: hola
 */
