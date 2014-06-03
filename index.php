<?php
include_once('LinearSystem.php');
include_once('RationalNumber.php');
include_once('math.php');
include_once('tests.php');
include_once('expr.php');


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

    if(0 == strcmp($str, "")){
        $r = new RationalNumber(0);
        return "";
    }

    try {
        $r = evalRational($str);
    }
    catch (Exception $e) {
        return $e->getMessage();
    }

    return "";
}

// sets all Post fields to some nice numbers (such that solution is nice ;) )
// sets global n!
function generateNumbers() {

    global $n;

    $n = 3; // set here number for default count, 3x3 is common => use it!
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

    <meta name="author" content="L.Spiegelberg">
    <meta name="publisher" content="L. Spiegelberg">
    <meta name="copyright" content="L. Spiegelberg">
    <meta name="description" content="Ein Onlinelöser für lineare Gleichungssysteme basierend auf dem Gaußalgorithmus, der neben eindeutigen oder unendliche viele Lösungen in der Ausgabe unterstützt.">
    <meta name="keywords" content="lineares, Gleichungssystem, Ax=b, Matrix, Vektor, Gauss, Gaussalgorithmus, Zeilenstufenform, Lineare, Algebra, ">
    <meta name="page-topic" content="Bildung">
    <meta http-equiv="content-language" content="de">
    <meta name="robots" content="index, follow">


    <title>Gaußlöser für lineare Gleichungssysteme Ax=b</title>
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


    <!-- all jQuery code for this doc goes here !-->
    <script>
        $(document).ready(function(){

            var maxRows = 9;

            // do here the magic
            $("input[name*='addDim']").click(function() {

                var matRows = $('#inMatA tr');
                if(matRows.length < maxRows)
                {
                    var str = "";
                    for(var i = 1; i <= matRows.length; i++) {
                        $("#inMatA tr:nth-child(" + i + ") td:last").after("<td><input type=\"text\" name=\"a_"+i+(matRows.length+1)+"\" value=\"0\"></td>");
                        str += "<td><input type=\"text\" name=\"a_"+(matRows.length+1)+i+"\" value=\"0\"></td>";
                    }
                    // one more for the nxn element
                    str += "<td><input type=\"text\" name=\"a_"+(matRows.length+1)+(matRows.length+1)+"\" value=\"1\"></td>";
                    $("#inMatA tr:last").after("<tr>"+str+"</tr>");

                    // x vector
                    $("#inVecX tr:last").after("<tr><td><div style=\"font-size: 125%;width: 40px;height: 20px;text-align: center;\">$x_{" + (matRows.length + 1) + "}$</div></td></tr>");

                    // b vector
                    $("#inVecB tr:last").after("<tr><td><input type=\"text\" name=\"b_"+(matRows.length + 1)+"\" value=\"0\"></td></tr>");


                    // typeset table
                    MathJax.Hub.Queue(["Typeset",MathJax.Hub]);

                    // hide if 8
                    if(matRows.length === maxRows - 1) {
                        $(this).hide();
                    }
                    // show if > 0
                    if(matRows.length > 0) $("input[name*='remDim']").show();
                }
            });

            $("input[name*='remDim']").click(function() {

                var matRows = $('#inMatA tr');
                    var str = "";
                    for(var i = 1; i <= matRows.length; i++) {
                        $("#inMatA tr:nth-child(" + i + ") td:last").remove();
                        str += "<td>row</td>";
                    }
                    // one more for the nxn element
                    str += "<td>row</td>";
                    $("#inMatA tr:last").remove();

                    // x vector
                    $("#inVecX tr:last").remove();
                    // b vector
                    $("#inVecB tr:last").remove();


                    // typeset table
                    MathJax.Hub.Queue(["Typeset",MathJax.Hub]);

                    // hide if 1
                    if(matRows.length === 2) {
                        $(this).hide();
                    }
                    // show
                    if(matRows.length < maxRows + 1) $("input[name*='addDim']").show();

            });
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
<p>Um das lineare Gleichungssystem $Ax=b$ mit $A \in \mathbb{R}^{n \times n}$, $x,b \in \mathbb{R}^n$ zu lösen, gib einfach die Zahlen oder einen mathematischen Term (z.B. 2^2 + 3/7), der keine Variablen beinhaltet, in die enstprechenden Felder ein. </p>

<p></p>
<form name="les" method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
<table style="margin-left: auto;margin-right: auto">
    <tr>
        <td>
                <table class="matrixDecor" id="inMatA">
                    <?php
                        // generate matrix content
                        for($i = 0; $i < $n; $i++) {
                            echo "<tr>";
                                for($j = 0; $j < $n; $j++) {
                                    echo "<td><input type=\"text\" name=\"a_".($i+1).($j+1)."\" value=\"".getNumberOf("a_".($i+1).($j+1))."\"></td>";
                                }
                            echo "</tr>";
                        }
                    ?>
                </table>

        </td>
        <td>$\cdot$</td>
        <td>
            <table style="height: auto" class="matrixDecor" id="inVecX">
               <?php
                    for($i = 0; $i < $n; $i++) {
                        echo "<tr><td><div style=\"font-size: 125%;width: 40px;height: 20px;text-align: center;\">\$x_".($i+1)."\$</div></td></tr>";
                    }
                ?>
            </table>
        </td>
        <td>=</td>
        <td>
            <table class="matrixDecor" id="inVecB">
                <?php
                for($i = 0; $i < $n; $i++) {
                    $bstr = "b_".($i+1);
                    echo "<tr>";
                    echo "<td><input type=\"text\" name=\"".$bstr."\" value=\"".getNumberOf($bstr)."\"></td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align: center">
            <input type="submit" value="Lösen" class="buttonDecor" name="submit" style="margin-right: 40px">
            <input type="button" name="addDim" value="+" class="roundButton">
            <input type="button" name="remDim" value="-" class="roundButton">
        </td>
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

    // we always have secured two steps
    $strMatExCur = $LS->getFormattedTexCode();
    $strDescCur = $LS->gaussStep();
    $strMatExLast = $strMatExCur;
    $strDescLast = $strDescCur;

    $i = 0; // counter for display & security use
    $fin = $LS->finished();
    while ($LS->finished() <> true) {

        $i++;
        // step
        $strMatExLast = $strMatExCur;
        $strDescLast = $strDescCur;
        $strMatExCur = $LS->getFormattedTexCode();
        $strDescCur = $LS->gaussStep();

        echo "<tr><td>";
        echo "<span style=\"width:20px\" class=\"roundDecor\">".$i."</span>";
        echo "</td><td>";
        echo "<div style=\"font-size: 150%;width: 100%;text-align: center\">";
        echo "$" .$strMatExLast. "$";
        echo "</div>";
        echo "</td><td width=\"33%\">";
        echo "<span class=\"roundRectDecor\">".$strDescLast."</span>";
        echo "</td></tr>";

        // securing
        if ($i > 100) break;
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

<div class="solution">
    Lösung: <?php echo "$" . "L = " . $LS->getAffineSpaceTexString() . "$";?>
</div>
</section>

<footer>(c) 2014 by L.Spiegelberg | spiegelb (at) in (dot) tum (dot) de
</footer>
</body>
</html>