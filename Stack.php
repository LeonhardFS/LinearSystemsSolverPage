<?php


// simple stack class for easier handling
class Stack {

    private $a;

    function __construct() {
        $this->a = array();
    }

    function push($var) {
        array_push($this->a, $var);
    }

    function pop() {
        return array_pop($this->a);
    }

    function last() {
        return $this->a[count($this->a) - 1];
    }

    function isEmpty() {
    return count($this->a) == 0;
    }
}

?>