<?php

include_once('Stack.php');
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
    return $str{$pos} == '+' || $str{$pos} == '-' || $str{$pos} == '~' || $str{$pos} == '#' || $str{$pos} == '*' || $str{$pos} == '/' || $str{$pos} == '^';
}

function isOperatorOrParentheses($str)
{
    $pos = 0;
    if (strlen($str) > 1) return false;
    return $str{$pos} == '+' || $str{$pos} == '-' || $str{$pos} == '~' || $str{$pos} == '#' || $str{$pos} == '*' || $str{$pos} == '/' || $str{$pos} == '^' || $str{$pos} == '(' || $str{$pos} == ')';
}

function isNumber($str)
{
    return regmatch($str, "#[-+]?([0-9]*\.[0-9]+|[0-9]+)#");
}

function isNegativeNumber($str)
{
    return regmatch($str, "#-([0-9]*\.[0-9]+|[0-9]+)#");
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



// solve the -a^2 problem via a hack
// basically convert - if necessary to unary minus and add meaning parentheses
function solvePowProblem($token_list) {
    // simply disallow negative number before ^
    $out_list = array();

    $pos = 0;
    $powseries = false;
    $pcount = 0; // count of parentheses used
    while ($pos < count($token_list)) {

        if(isOperator($token_list[$pos]) &&
            0 != strcmp($token_list[$pos], "^") &&
            0 != strcmp($token_list[$pos], "-") &&
            0 != strcmp($token_list[$pos], "~")) {
            // add closing )
            for($i = 0; $i < $pcount; $i++) {
                array_push($out_list,")");
            }
            $pcount = 0;
            $powseries=false;
        }

        if($pos + 1 < count($token_list)) {
            if(isNegativeNumber($token_list[$pos]) && 0 == strcmp($token_list[$pos + 1], "^") && !$powseries) {
                // add an unary minus following the tokens
                $token = $token_list[$pos];
                $token = substr($token, strpos($token, "-") + 1);
                array_push($out_list, "(");
                $pcount++; // for every opening bracket, there must be also a closing one
                array_push($out_list, "~");
                array_push($out_list, $token);
                $powseries = true;
            }
        else {
            array_push($out_list, $token_list[$pos]);
        }
        }
        else array_push($out_list, $token_list[$pos]);



        $pos++;
    }

    // add closing )
    for($i = 0; $i < $pcount; $i++) {
        array_push($out_list,")");
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
    $foundmatch = false; // we need to use an extra variable as php treats "0"==null!
    $lasttokenisnumber = false;
    $lasttokenisoperator = true; // hack if expr starts with - it must be an unary one!
    while ($pos <= strlen($str)) {

        for ($j = 1; $j <= strlen($str) - $pos; $j++) {
            $curstr = substr($str, $pos, $j);
            if (isOperatorOrParentheses($curstr)) {

                // check for unary -
                // as two following operators are not allowed except for unary ones (+,-)
                // use internally special symbols for it
                // # for unary +, ~ for unary -
                $match = $curstr;
                if($lasttokenisoperator) {
                   if(0 == strcmp($curstr, '+'))$match = "#";
                   if(0 == strcmp($curstr, '-'))$match = "~";
                }

                $length = $j;
                $foundmatch = true;
            }
            if (!$lasttokenisnumber && isNumber($curstr)) {
                $match = $curstr;
                $length = $j;
                $foundmatch = true;
            }
        }

        $pos += $length;
        if ($foundmatch) {
            // no match, save token
            array_push($token_list, $match);
            $length = 1;

            // is token a number?
            if (isOperatorOrParentheses($match)) $lasttokenisnumber = false;
            else $lasttokenisnumber = true;

            if(isOperator($match))$lasttokenisoperator = true;
            else $lasttokenisoperator = false;

            $match = null;
            $foundmatch = false;
        }
    }

    // add last token...
    if ($match) {
        array_push($token_list, $match);
    }


    // insert * ops where necessary
    $token_list = insertMultOps($token_list);

    // solve -a^2 prob
    $token_list = solvePowProblem($token_list);

    return $token_list;
}

function print_list($list)
{
    // print out list items
    echo "<ul>";
    for ($i = 0; $i < count($list); $i++) {
        echo "<li>" . $list[$i] . "</li>";
    }
    echo "</ul>";
}

// is token separator sign? ==> accept , ; as separators!
function isSeparator($str)
{
    return 0 == strcmp($str, ",") || 0 == strcmp($str, ";");
}

function isLeftAssociative($op) {
    if(0 == strcmp($op, "+"))return true;
    if(0 == strcmp($op, "-"))return true;
    if(0 == strcmp($op, "*"))return true;
    if(0 == strcmp($op, "/"))return true;
    if(0 == strcmp($op, "^"))return false; // right associative
    if(0 == strcmp($op, "~"))return false; // right associative (unary -)
    if(0 == strcmp($op, "#"))return false; // right associative (unary +)
    return false;
}

function precedence($op) {
    if(0 == strcmp($op, "+"))return 1;
    if(0 == strcmp($op, "-"))return 1;
    if(0 == strcmp($op, "*"))return 2;
    if(0 == strcmp($op, "/"))return 2;
    if(0 == strcmp($op, "~"))return 3; // internal symbol for unary minus
    if(0 == strcmp($op, "#"))return 3; // internal symbol for unary plus
    if(0 == strcmp($op, "^"))return 4;
}

// Shunting Yard Algorithm invented by the one and only famous egsger dijkstra
function shunting_yard($token_list)
{

    $errorstr = "";

    $rpolish = array(); // array to store tokens in reverse polish order

    $stack = new Stack(); // the stack for operands

    $pos = 0;
    while ($pos < count($token_list)) {
        $token = $token_list[$pos];

        // php sucks, therefore fast hack here cause php treats "0" as null!
        if($token == null)$token = "0";

        if (isNumber($token)) array_push($rpolish, $token);

        //if(isFunction($token))array_push($stack, $token);

        if (isSeparator($token)) {

            // pop tokens from stack till stack is (
            $bDone = false;
            while (!$bDone) {
                if (!$stack->isEmpty()) {
                    // while stack is != (, add to queue
                    if (strcmp($stack->last(), "(") != 0) {
                        array_push($rpolish, $stack->pop());
                    } else $bDone = true;
                } else {
                    $errorstr .= "Fehler: (1) Trennzeichen (,) nicht richtig platziert oder\n (2) schließender Klammer ) geht keine öffnende Klammer ( voraus";
                    $bDone = true;
                }
            }

        }

        if (isOperator($token)) {
            $bDone = false;
            while (!$bDone) {
                if(!$stack->isEmpty()) {
                    if(isOperator($stack->last()) &&
                        ((isLeftAssociative($token) && precedence($token) <= precedence($stack->last())) ||
                            (precedence($token) < precedence($stack->last())))) {
                        array_push($rpolish, $stack->pop());
                    } else $bDone = true;
                } else $bDone = true;
            }

            $stack->push($token);

        }

        if(0 == strcmp($token, "(")) {
            $stack->push($token);
        }

        if(0 == strcmp($token, ")")) {
            $bDone = false;
            while(!$bDone) {
                if(!$stack->isEmpty()) {
                    if(strcmp($stack->last(), "(") == 0) {
                        $stack->pop();
                        $bDone = true;
                    }
                    else {
                        array_push($rpolish, $stack->pop());
                    }
                }
                else {
                    $bDone = true;
                    $errorstr .= "Fehler: (1) schließender Klammer ) geht keine öffnende Klammer ( voraus\n";
                }
            }

            //if(isFunction($stack->last()))array_push($rpolish, $stack->pop());
        }

        $pos++;

    }

    // pop stack to queue
    while(!$stack->isEmpty()) {
        if(0 == strcmp($stack->last(), "(")) {
            $errorstr = "Fehler: es gibt mehr öffnende als schließende Klammern";
            break;
        }

        array_push($rpolish, $stack->pop());
    }

    return $rpolish;
}

function isBinaryOp($token) {
    return $token{0} == '+' || $token{0} == '-' || $token{0} == '*' || $token{0} == '/' || $token{0} == '^';
}

// func to evaluate a token list in rpn
function evalRPolish($token_list) {
    $pos = 0;

    $stack = new Stack();

    while($pos < count($token_list)) {
        $token = $token_list[$pos];

        // php sucks, therefore fast hack here cause php treats "0" as null!
        if($token == null)$token = "0";

        if(isNumber($token))$stack->push($token);
        if(isOperator($token)) {
            if(isBinaryOp($token)) {
                $op2 = $stack->pop();
                $op1 = $stack->pop();
                if(0 == strcmp($token, "+"))$stack->push($op1 + $op2);
                if(0 == strcmp($token, "-"))$stack->push($op1 - $op2);
                if(0 == strcmp($token, "*"))$stack->push($op1 * $op2);
                if(0 == strcmp($token, "/"))$stack->push($op1 / $op2);
                if(0 == strcmp($token, "^"))$stack->push(pow($op1, $op2));
            } else
            {
                // must be unary as tertiary ops are not supported yet(same as funcs)
                $op = $stack->pop();
                if(0 == strcmp($token, "#"))$stack->push($op);
                if(0 == strcmp($token, "~"))$stack->push(- $op);
            }
        }

        $pos++;
    }

    return $stack->pop();
}

// func to evaluate a token list in rpn using rational numbers
function evalRPolishRational($token_list) {
    $pos = 0;

    $stack = new Stack();

    while($pos < count($token_list)) {
        $token = $token_list[$pos];

        if(isNumber($token)) {
            $r = number2Rational($token);
            $stack->push($r);
        }
        if(isOperator($token)) {
            if(isBinaryOp($token)) {
                $op2 = $stack->pop();
                $op1 = $stack->pop();
                if(0 == strcmp($token, "+"))$stack->push(rplus($op1, $op2));
                if(0 == strcmp($token, "-"))$stack->push(rminus($op1, $op2));
                if(0 == strcmp($token, "*"))$stack->push(rtimes($op1, $op2));
                if(0 == strcmp($token, "/"))$stack->push(rdivide($op1, $op2));
                if(0 == strcmp($token, "^")) {
                    $op2->reduce();
                    if($op2->denominator == 1)
                    $stack->push(rpow($op1, $op2->numerator));
                    else throw new Exception('^ operator only supported for integers');
                }
            } else
            {
                // must be unary as tertiary ops are not supported yet(same as funcs)
                $op = $stack->pop();
                if(0 == strcmp($token, "#"))$stack->push($op);
                if(0 == strcmp($token, "~"))$stack->push(rminus(new RationalNumber(0), $op));
            }
        }

        $pos++;
    }

    return $stack->pop();
}


// converts a string that matches the regex above to a rational number
function number2Rational($str) {

    $str = trim($str);

    $numerator_str = "";
    $numbersafterdot = 0;
    for($i = 0; $i < strlen($str); $i++) {
        // check for dot
        if($str{$i} == '.') {
            $numbersafterdot = strlen($str) - $i - 1;
        } else {
            $numerator_str .= $str{$i};
        }
    }

    $numerator = intval($numerator_str);
    $denominator = pow(10, $numbersafterdot);

    return new RationalNumber($numerator, $denominator);
}

// func to evaluate string to rational number
function evalRational($str) {

    $str = trim($str);

    // first replace brackets (, [, {
    $brackets = array("[", "{");
    $str = str_replace($brackets, "(", $str);
    $brackets = array("]", "}");
    $str = str_replace($brackets, ")", $str);

    // first check if only allowed signs are used
    // not the negation at the beginning of the group
    if(regmatch($str, "/[^0-9\+\-\*\/\^,; \.\(\)]+/"))throw new Exception('there are only numbers, +-*/^.,;() allowed!');

    // get tokens
    $token_list = tokenize($str);

    // convert to reverse polish notation
    $token_list = shunting_yard($token_list);

    return evalRPolishRational($token_list);
}

?>


<!-- uncomment to get test data !-->

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
<h4>Alles in einem getestet</h4>
<?php

    // test if "0" is number
    echo "is 0 a number?: ".isNumber('0');

$istr = "(3+4)2(3*5+8/6)(7)8(9)";
//$istr = "13+4*--6/2";
$istr = "2.09+-3.847*6.095657*-7/8+2/(4+7)";
//$istr ="(2+3)*2/(1+1)";
//$istr = "2/(1+1)";
$istr = "2*1/3+4^2";
    $istr = "2(3+4+5+6+7+8+9)-10^2/3";
    $istr = "-10^2/3";
$istr = "2^-2^2";
$istr = "2^-2^2 + 2 - 2^-2^2^2";
$istr = "-2^2+3";
$istr = "0";
    echo "Ergebnis: ".$istr." = ".evalRational($istr);


?>
<h4>Conversion of number str to rational</h4>
<?php
  $nstr = "-123.456";
    $r = number2Rational($nstr);
    $r->reduce();
    echo $nstr." konvertiert zu $".$r."$ == ".($r->numerator / $r->denominator);
?>
<h4>Stack test</h4>
<?php
    $stack = new Stack();

    $stack->push(3);
    $stack->push(4);
    $stack->push(5);

    while(!$stack->isEmpty())echo $stack->pop()."|";

?>
<h4>Inputstr</h4>
<?php
echo "Berechne: ".$istr;

$t_list = tokenize($istr);
//tokenize("2.09+-3.847*6.095657*-7/8+2/(4+7)");
//tokenize("2+3*6*7/8+2*(4+7)");
print_list($t_list);

$rpolish = shunting_yard($t_list);
echo "<h2>Reverse Polish Notation:</h2>";
print_list($rpolish);
$r = evalRPolishRational($rpolish);
$r->reduce();
echo "Ergebnis: $".$r."$ == ".($r->numerator / $r->denominator);

?>
</body>
</html>