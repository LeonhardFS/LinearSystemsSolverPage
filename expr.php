<?php
/**
 * Created by PhpStorm.
 * User: Leonhard Franz
 * Date: 29.04.14
 * Time: 22:45
 */


include_once('LinearSystem.php');
include_once('RationalNumber.php');
include_once('math.php');
include_once('tests.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// class to hold a token
class Token
{
    public $data;
    public $type;

    const TYPE_OPERATOR = 1;
    const TYPE_NUMBER = 2;
    const TYPE_VAR = 3;
    const TYPE_UNDEFINED = 0;

    // constructor
    function __construct()
    {
        $this->type = Token::TYPE_UNDEFINED;
        $this->data = null;
    }
}

function isOperator($str)
{
    $pos = 0;
    if (strlen($str) > 1) return false;
    return $str{$pos} == '+' || $str{$pos} == '-' || $str{$pos} == '*' || $str{$pos} == '/' || $str{$pos} == '(' || $str{$pos} == ')';
}

function isNumber($str)
{
    return regmatch($str, "#[-+]?([0-9]*\.[0-9]+|[0-9]+)#");
}

function regmatch($str, $pattern)
{
    $matches = array();
    if (preg_match($pattern, $str, $matches))
        return strlen($matches[0]) == strlen($str);
    else return false;
}

// inserts * operators to solve mathematical abbreviations like 2(3+4) and (3+4)5
function insertMultOps($token_list)
{
    $out_list = array();
    array_push($out_list, $token_list[0]);

    $pos = 1;
    while ($pos < count($token_list)) {

        if (strcmp($token_list[$pos], "(") == 0) {
            // check if token before is ) or a number. If so, insert * op!
            if (strcmp($token_list[$pos - 1], ")") == 0 || isNumber($token_list[$pos - 1])) {
                array_push($out_list, "*");
            }
        }

        if (isNumber($token_list[$pos]) && strcmp($token_list[$pos - 1], ")") == 0) {
            array_push($out_list, "*");
        }
        // add token to output
        array_push($out_list, $token_list[$pos]);

        $pos++;
    }
    return $out_list;
}

// returns array of tokens in str format
function tokenize($str)
{
    $token_list = array();
    // go through string and match longest reg expr
    $pos = 0;
    $length = 1;
    $match = null;
    $lasttokenisnumber = false;
    while ($pos <= strlen($str)) {
        for ($j = 1; $j <= strlen($str) - $pos; $j++) {
            $curstr = substr($str, $pos, $j);
            if (isOperator($curstr)) {
                $match = $curstr;
                $length = $j;
            }
            if (!$lasttokenisnumber && isNumber($curstr)) {
                $match = $curstr;
                $length = $j;
            }
        }

        $pos += $length;
        if ($match) {
            // no match, save token
            array_push($token_list, $match);
            $length = 1;

            // is token a number?
            if (isOperator($match)) $lasttokenisnumber = false;
            else $lasttokenisnumber = true;

            $match = null;
        }
    }

    // add last token...
    if ($match) {
        array_push($token_list, $match);
    }


    // insert * ops where necessary
    $token_list = insertMultOps($token_list);

    return $token_list;
}

function print_list($list) {
    // print out list items
    echo "<ul>";
    for ($i = 0; $i < count($list); $i++) {
        echo "<li>" . $list[$i] . "</li>";
    }
    echo "</ul>";
}

?>

<!DOCTYPE html>
<html>
<head>
    <!-- allow unicode characters in file -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Ax=b</title>
    <style type="text/css">
        @import url("style.css");
    </style>

    <!-- include jQuery from web source!-->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

    <!-- configuring MathJax to allow inline TexSyntax !-->
    <script type="text/x-mathjax-config">
  MathJax.Hub.Config({tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}});



    </script>
    <!-- local copy !-->
    <script type="text/javascript" src="./MathJax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
    <!-- web copy !-->
    <script type="text/javascript"
            src="https://c328740.ssl.cf1.rackcdn.com/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML">
    </script>
</head>
<body>
<?php

$t_list = tokenize("(3+4)2(3*5+8/6)(7)8(9)");
//tokenize("2.09+-3.847*6.095657*-7/8+2/(4+7)");
//tokenize("2+3*6*7/8+2*(4+7)");
print_list($t_list);

?>
</body>
</html>