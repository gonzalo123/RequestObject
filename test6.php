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
extract($request->getFilteredParameters());

echo "param1: <br/>";
var_dump($param1);
echo "<br/>";

echo "param2: <br/>";
var_dump($param2);
echo "<br/>";