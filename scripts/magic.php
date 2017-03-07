<?php
class CallableClass
{
    function __invoke() {
        echo 'this is a object';
    }
}
$obj = new CallableClass;

var_dump(is_callable($obj));
