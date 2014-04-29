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
class Token {
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

function isOperator($str) {
    $pos = 0;
    if(strlen($str) > 1)return false;
    return $str{$pos} == '+' || $str{$pos} == '-' || $str{$pos} == '*' || $str{$pos} == '/' || $str{$pos} == '(' || $str{$pos} == ')';
}
function regmatch($str, $pattern) {
    $matches = array();
    if(preg_match($pattern, $str, $matches))
    return strlen($matches[0]) == strlen($str);
    else return false;
}

function tokenize($str) {
    //regmatch($curstr, "#[-+]?([0-9]*\.[0-9]+|[0-9]+)#")

    $token_list = array();
    // go through string and match longest reg expr
    $pos = 0;
    $length = 1;
    $match = null;
    $lasttokenisnumber = false;
    while($pos <= strlen($str)) {
        for($j = 1; $j <= strlen($str) - $pos; $j++) {
            $curstr = substr($str, $pos, $j);
            if(isOperator($curstr)) {
                $match = $curstr;
                $length = $j;
            }
            if(!$lasttokenisnumber && regmatch($curstr, "#[-+]?([0-9]*\.[0-9]+|[0-9]+)#")) {
                $match = $curstr;
                $length = $j;
            }
        }

        $pos += $length;
        if($match) {
            // no match, save token
            echo "adding token: ".$match;
            array_push($token_list, $match);
            $length = 1;

            // is token a number?
            if(isOperator($match))$lasttokenisnumber = false;
            else $lasttokenisnumber = true;

            $match = null;
        }
    }

    // add last token...
    if($match) {
        echo "adding token: ".$match;
        array_push($token_list, $match);
    }


    // todo correct list of tokens, i.e. special case 2(3+4)

    // print out tokens
    echo "<ul>";
    for($i = 0; $i < count($token_list); $i++) {
        echo "<li>".$token_list[$i]."</li>";
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
    tokenize("2.09+-3.847*6.095657*-7/8+2/(4+7)");
    //tokenize("2+3*6*7/8+2*(4+7)");
?>
</body>
</html>