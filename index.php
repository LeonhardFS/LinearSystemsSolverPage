<?php
include_once('LinearSystem.php');
include_once('RationalNumber.php');
include_once('math.php');
include_once('tests.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);


$formsend = false;
// has the page been exercised via a form action?
if(isset($_POST['submit'])) {
    $formsend = true;
}

function getNumberOf($name) {
    if(isset($_POST[$name]))
        return $_POST[$name];
    else {
        $num = rand(0, 9);
        $_POST[$name] = $num;
        return $num;
    }
}

// encodes rational as x or x / y
function rationalToStr($r) {
    $r->reduce();
    if($r->denominator == 1)
    return strval($r->numerator);
    else return strval($r->numerator)."/".strval($r->denominator);
}

// returns an error string, if conversion was not successful
function strToRational($str, &$r) {
    // split str by / char
    $a = explode('/', $str);

    if(count($a) > 2)return "Zur Eingabe von Brüchen ist höchstens ein '/' Zeichen erlaubt";

    // only numbers allowed
    foreach($a as &$val) {
        $val=preg_replace("/[^0-9]/","",$val);
    }

    if(count($a) == 1) {


        // simple case, only numerator
        $r = new RationalNumber(intval($a[0]));
    } else {
        $r = new RationalNumber(0);

        // division by zero?
        if(intval($a[1]) == 0) {
            return "Division durch 0 ist nicht definiert";
        }
        $r = new RationalNumber(intval($a[0]), intval($a[1]));
    }

    return "";
}

// sets all Post fields to some nice numbers (such that solution is nice ;) )
// sets global n!
function generateNumbers() {

    global $n;

    $n = 3;
    $A = randMatrix($n); // nxn matrix
    $x = randVector($n);

    $b = transformVector($A, $x, $n);

    // set post variables
    for($i = 1; $i <= $n; $i++)
        for($j = 1; $j <= $n; $j++) {
            $str = 'a_'.$i.$j;
            $_POST[$str] =  rationalToStr($A[$i - 1][$j - 1]);
        }

    for($i = 1; $i <= $n; $i++) {
        $str = 'b_'.$i;
        $_POST[$str] = rationalToStr($b[$i - 1]);
    }
}

// parses all $POST variable a_ij, b_j
// sets variables A, b, n in global scope!
function parsePostRequest() {
    global $A;
    global $b;
    global $n;

    $max = 10;
    // search for highest index set in Post => determines n
    $index = 1;
    while(isset($_POST['b_'.$index]) && $index < $max)$index++;
    $n = $index - 1;

    $errfound = false;
    $errstr = "<div class=\"errSpace\"><h4>Fehler:</h4><hr><ul>";

    for($i = 1; $i <= $n; $i++)
        for($j = 1; $j <= $n; $j++) {
            $str = 'a_'.$i.$j;
            $estr = strToRational($_POST[$str], $A[$j - 1][$i - 1]);
            if(strlen($estr) > 0)
            {
                $errstr .= "<li>\$a_{".$i.$j."}\$: ".$estr."</li>";
                $errfound = true;
            }
        }

    for($i = 1; $i <= $n; $i++) {
        $str = 'b_'.$i;
        $estr = strToRational($_POST[$str], $b[$i - 1]);
        if(strlen($estr) > 0){
            $errstr.="<li>".$estr."</li>";
            $errfound = true;
        }
    }

    $errstr .=  "</ul></div>";

    if($errfound)echo $errstr;
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
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

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


    <!-- all jQuery code for this doc goes here !-->
    <script>
        $(document).ready(function(){

            // do here the magic


        });
    </script>
</head>

<body>

<header>
    <div class="centerit">
        <p>

        <span style="font-size: 34px">Gauß-Löser</span>
        <br>
        <span style="font-size: 20px">zur Lösung eines linearen Gleichungssystems $Ax=b$<span> </p></div>
</header>

<section id="main">
<?php
// generateNumbers if necessary
if(!isset($_POST['a_11']))
    generateNumbers();

// parse here the form
parsePostRequest();

?>
<p>Um Brüche $\frac{x}{y}$ einzugeben, tippe in die Felder einfach x / y. Aktuell werden leider nur rationale Zahlen unterstützt.</p>

<p></p>
<form name="les" method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
<table style="margin-left: auto;margin-right: auto">
    <tr>
        <td>
                <table class="matrixDecor">
                    <tr>
                        <td><input type="text" name="a_11" value="<?php echo getNumberOf('a_11'); ?>"></td>
                        <td><input type="text" name="a_12" value="<?php echo getNumberOf('a_12'); ?>"></td>
                        <td><input type="text" name="a_13" value="<?php echo getNumberOf('a_13'); ?>"></td>
                    </tr>
                    <tr>
                        <td><input type="text" name="a_21" value="<?php echo getNumberOf('a_21'); ?>"></td>
                        <td><input type="text" name="a_22" value="<?php echo getNumberOf('a_22'); ?>"></td>
                        <td><input type="text" name="a_23" value="<?php echo getNumberOf('a_23'); ?>"></td>
                    </tr>
                    <tr>
                        <td><input type="text" name="a_31" value="<?php echo getNumberOf('a_31'); ?>"></td>
                        <td><input type="text" name="a_32" value="<?php echo getNumberOf('a_32'); ?>"></td>
                        <td><input type="text" name="a_33" value="<?php echo getNumberOf('a_33'); ?>"></td>
                    </tr>
                </table>

        </td>
        <td>$\cdot$</td>
        <td>
<!--                $\left( \begin{array}{c} x_1 \\ x_2 \\ x_3 \end{array} \right)$-->
            <table style="height: auto" class="matrixDecor">
                <tr><td><div style="font-size: 125%;width: 40px;height: 20px;text-align: center;">$x_1$</div></td></tr>
                <tr><td><div style="font-size: 125%;width: 40px;height: 20px;text-align: center">$x_2$</div></td></tr>
                <tr><td><div style="font-size: 125%;width: 40px;height: 20px;text-align: center">$x_3$</div></td></tr>
            </table>
        </td>
        <td>=</td>
        <td>
            <table class="matrixDecor">
                <tr>
                    <td><input type="text" name="b_1" value="<?php echo getNumberOf('b_1'); ?>"></td>
                </tr>
                <tr>
                    <td><input type="text" name="b_2" value="<?php echo getNumberOf('b_2'); ?>"></td>
                </tr>
                <tr>
                    <td><input type="text" name="b_3" value="<?php echo getNumberOf('b_3'); ?>"></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align: center"><input type="submit" value="Lösen" class="buttonDecor" name="submit"></td>
    </tr>
</table>
</form>
<p class="horzSpace"></p>
<hr style="width: 90%" class="separator">
<p class="horzSpace"></p>
<table style="min-width:300px;max-width:500px;margin-left: auto;margin-right: auto;margin-top: 20px">
    <?php
    // solve Gauss here


    // load test cases, if wished
    // loadTest1();
    // loadTest2();
    // loadTest3();
    // loadTest4();

    $LS = new LinearSystem($n);
    $LS->A = $A;
    $LS->b = $b;
    $LS->initGauss();


    $i = 0;


    // we always have secured two steps
    $strMatExCur = $LS->getFormattedTexCode();
    $strDescCur = $LS->gaussStep();
    $strMatExLast = $strMatExCur;
    $strDescLast = $strDescCur;

    $fin = $LS->finished();
    while ($LS->finished() <> true) {

        $i++;
        // step
        $strMatExLast = $strMatExCur;
        $strDescLast = $strDescCur;
        $strMatExCur = $LS->getFormattedTexCode();
        $strDescCur = $LS->gaussStep();



        echo "
  <tr>
    <td>";

        echo "<span style=\"width:20px\" class=\"roundDecor\">".$i."</span>";

        echo "</td>
    <td>";

        echo "<div style=\"font-size: 150%;width: 100%;text-align: center\">";

        echo "$" .$strMatExLast. "$";
        echo "</div>";

        echo "</td>
    <td width=\"33%\">";

        echo "<span class=\"roundRectDecor\">".$strDescLast."</span>";

        echo "</td></tr>";


        // securing
        if ($i > 15) break;
    }

    ?>

    <tr>
        <td><?php echo "<span style=\"width:20px\" class=\"roundDecor\">".($i+1)."</span>"?></td>
        <td><div style="font-size:150%;width: 100%;text-align: center">$<?php echo $strMatExCur; ?>$</div></td>
        <td><span class="roundRectDecor"><?php echo $strDescCur; ?></span></td>
    </tr>
    <?php
        // print last info if necessary
        if(strpos($strDescCur, 'Ergebnis') === false && $LS->existsSolution()) {
            echo "<tr><td><span style=\"width:20px\" class=\"roundDecor\">".($i+2)."</span></td>
    <td><div style=\"font-size:150%;width: 100%;text-align: center\">\$".$LS->getFormattedTexCode()."\$</div></td>
    <td><span class=\"roundRectDecor\">Ergebnis aus Matrix ablesen</span></td></tr>";
        }
    ?>

</table>
<hr style="width: 90%" class="separator">

<div class="solution">Lösung: <?php
    echo "$" . "L = " . $LS->getAffineSpaceTexString() . "$";?></div>

</section>

<footer>(c) 2014 by L.Spiegelberg | spiegelb (at) in (dot) tum (dot) de
</footer>
</body>
</html>