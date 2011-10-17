<?php
include "RequestObject.php";

class Request extends RequestObject
{
    public $param1;
}

$request = new Request();

echo "param1: " . $request->param1 . '<p/>';
/*
test1.php?param1=11212
param1: 11212

test1.php?param1=hola
param1: hola
 */
