<?php
include_once('expr.php');
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