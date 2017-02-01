<?php
include_once('LinearSystem.php');
include_once('RationalNumber.php');
include_once('math.php');
include_once('tests.php');
include_once('expr.php');
include_once('lang.php');


// use bc package for numbers to deal with large values!


error_reporting(E_ALL);
ini_set('display_errors', 1);

$strA = "";
$strb = "";
if(isset($_GET['A']))$strA = $_GET['A'];
if(isset($_GET['b']))$strb = $_GET['b'];


parseAB($strA, $strb);

// set language if necessary
if(!isset($_GET['lang']))setLang('en');
else setLang($_GET['lang']);

$inputcontainserrors = false;

$formsend = false;
// has the page been exercised via a form action?
if (isset($_POST['submit'])) {
    $formsend = true;
}

function parseAB($strA, $strb) {
    global $n;

    //asserts...
    if(strlen($strA) < 2)return false;
    if(strlen($strb) < 2)return false;

    //A
    if($strA{0} != '[')return false;
    if($strA{strlen($strA)-1} != ']')return false;
    //b
    if($strb{0} != '[')return false;
    if($strb{strlen($strb)-1} != ']')return false;


    // first check out n
    $pos = 1;
    $maxsep = 0; // max number of , separators
    $sepcounter = 0; // separator counter
    $maxopar = 0; // max number of opening brackets
    $maxcpar = 0; // max number of opening brackets

    //A
    while($pos < strlen($strA) - 1) {
        if($strA{$pos} == '[') {
            $maxsep = max($maxsep, $sepcounter);
            $sepcounter = 0;
            $maxopar++;
        }
        if($strA{$pos} == ']'){
            $maxsep = max($maxsep, $sepcounter);
            $sepcounter = 0;
            $maxcpar++;
        }

        if($strA{$pos} == ',')$sepcounter++;
        $pos++;
    }

    //b
    $pos = 1;
    $sepcounter = 0;
    while($pos < strlen($strb) - 1) {
        if($strb{$pos} == ',')$sepcounter++;
        $pos++;
    }
    $maxsep = max($maxsep, $sepcounter);

    // now determine n
    $n = min(max(min($maxcpar, $maxopar), $maxsep + 1), 9); // no more than 9 allowed!

    // now set first all post elements to 0
    for($i = 1; $i <= $n; $i++)
        for($j = 1; $j <= $n; $j++) {
            $str = 'a_' . $i . $j;
            $_POST[$str] = new RationalNumber(0);
            $str = 'b_'.$j;
            $_POST[$str] = new RationalNumber(0);

        }

    // now parse in A
    $pos = 1;
    $i = 1;
    $j = 1;
    $curstr = "";
    while($pos < strlen($strA)) {
        if($strA{$pos} == ']') {
            $_POST['a_'.$i.$j] = $curstr;
            $i++;
        }
        else if($strA{$pos} == '[') {
            $curstr = "";
            $j = 1;
        }
        else if($strA{$pos} == ',') {

            $_POST['a_'.$i.$j] = $curstr;
            $curstr = "";
            $j++;
        }
        else {
            $curstr .= $strA{$pos};
        }
    $pos++;
    }

    // parse b
    $pos = 1;
    $i = 1;
    $curstr = "";
    while($pos < strlen($strb)) {
        if($strb{$pos} == ']') {
            $_POST['b_'.$i] = $curstr;
        }
        else if($strb{$pos} == ',') {

            $_POST['b_'.$i] = $curstr;
            $curstr = "";
            $i++;
        }
        else {
            $curstr .= $strb{$pos};
        }
        $pos++;
    }

    return true;
}

function getNumberOf($name)
{
    if (isset($_POST[$name]))
        return $_POST[$name];
    else {
        $num = rand(0, 9);
        $_POST[$name] = $num;
        return $num;
    }
}

// encodes rational as x or x / y
function rationalToStr($r)
{
    $r->reduce();
    if ($r->denominator == 1)
        return strval($r->numerator);
    else return strval($r->numerator) . "/" . strval($r->denominator);
}

// returns an error string, if conversion was not successful
function strToRational($str, &$r)
{

    if (0 == strcmp($str, "")) {
        $r = new RationalNumber(0);
        return "";
    }

    try {
        $r = evalRational($str);
    } catch (Exception $e) {
        return $e->getMessage();
    }

    return "";
}

// sets all Post fields to some nice numbers (such that solution is nice ;) )
// sets global n!
function generateNumbers()
{

    global $n;

    $n = 3; // set here number for default count, 3x3 is common => use it!
    $A = randMatrix($n); // nxn matrix
    $x = randVector($n);

    $b = transformVector($A, $x, $n);

    // set post variables
    for ($i = 1; $i <= $n; $i++)
        for ($j = 1; $j <= $n; $j++) {
            $str = 'a_' . $i . $j;
            $_POST[$str] = rationalToStr($A[$i - 1][$j - 1]);
        }

    for ($i = 1; $i <= $n; $i++) {
        $str = 'b_' . $i;
        $_POST[$str] = rationalToStr($b[$i - 1]);
    }
}

// parses all $POST variable a_ij, b_j
// sets variables A, b, n in global scope!
function parsePostRequest()
{
    global $A;
    global $b;
    global $n;

    // set global flag
    global $inputcontainserrors;

    $max = 10;
    // search for highest index set in Post => determines n
    $index = 1;
    while (isset($_POST['b_' . $index]) && $index < $max) $index++;
    $n = $index - 1;

    $errfound = false;
    $errstr = "<div class=\"errSpace\"><h4>".tt_s('error').":</h4><hr><ul>";

    for ($i = 1; $i <= $n; $i++)
        for ($j = 1; $j <= $n; $j++) {
            $str = 'a_' . $i . $j;
            $estr = strToRational($_POST[$str], $A[$j - 1][$i - 1]);
            if (strlen($estr) > 0) {
                $errstr .= "<li>\$a_{" . $i . $j . "}\$: " . $estr . "</li>";
                $errfound = true;
            }
        }

    for ($i = 1; $i <= $n; $i++) {
        $str = 'b_' . $i;
        $estr = strToRational($_POST[$str], $b[$i - 1]);
        if (strlen($estr) > 0) {
            $errstr .= "<li>" . $estr . "</li>";
            $errfound = true;
        }
    }

    $errstr .= "</ul></div>";

    if ($errfound) {
        echo $errstr;
        $inputcontainserrors = true;
    }
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
    <meta name="description"
          content="Ein Onlinelöser für lineare Gleichungssysteme basierend auf dem Gaußalgorithmus, der neben eindeutigen oder unendliche viele Lösungen in der Ausgabe unterstützt.">
    <meta name="keywords"
          content="lineares, Gleichungssystem, Ax=b, Matrix, Vektor, Gauss, Gaussalgorithmus, Zeilenstufenform, Lineare, Algebra, ">
    <meta name="page-topic" content="Bildung">
    <meta http-equiv="content-language" content="de">
    <meta name="robots" content="index, follow">


    <title><?php tt('bartitle'); ?></title>
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



    <?php
        // following lines configure the active state via jQuery
        if(0 == strcmp(getLang(), 'de')){

    ?>
        <script>
            function setLangState() {
                $("#langSel a.langDE").addClass("active");
            }
        </script>
    <?php
        } else if(0 == strcmp(getLang(), 'en')) {
    ?>
            <script>
                function setLangState() {
                    $("#langSel a.langEN").addClass("active");
                }
            </script>
    <?php
        // end active state config
        }
    ?>

    <!-- on submit of form
    -->
    <script>

        function getSerializedData() {
            var str = "";
            // construct "get" url from form
            var matRows = $('#inMatA tr');
            var n = matRows.length;

            var serializedMatrix = "[";
            // first serialize values of A matrix
            for(var i = 1; i <= n; i++) {
                serializedMatrix += "[";
                for(var j = 1; j <= n; j++) {
                    var a = $("input[name='a_"+ i.toString()+j.toString()+"']").val();

                    if(j < n) serializedMatrix += a.toString() + ",";
                    else serializedMatrix += a.toString();
                }
                serializedMatrix += "]";
                if(i < n)serializedMatrix += ",";
            }
            serializedMatrix += "]";

            var serializedB = "[";
            // next serialize b
            for (var j = 1; j <= n; j++) {
                var b = $("input[name='b_" + j.toString() + "']").val();

                if (j < n) serializedB += b.toString() + ",";
                else serializedB += b.toString();
            }
            serializedB += "]";

            return "&A=" + serializedMatrix + "&b=" + serializedB;
        }


        function serializeForm(form) {
            // change here
            form.action = form.action + getSerializedData();
            return true;
        }

        function serializeHref(href) {
            href.href = href.href + getSerializedData();
        }

    </script>
    <!-- all jQuery code for this doc goes here !-->
    <script>
        $(document).ready(function () {

            // use for manipulation of language
            setLangState();

            $('#langSel a').hover(function() {
                $('#langSel a').removeClass("active");
                $(this).addClass("active");
            }, function() {
                $('#langSel a').removeClass("active");
                setLangState();
            });

            // here is the matrix code

            var maxRows = 9;

            // do here the magic
            $("input[name*='addDim']").click(function () {

                var matRows = $('#inMatA tr');
                if (matRows.length < maxRows) {
                    var str = "";
                    for (var i = 1; i <= matRows.length; i++) {
                        $("#inMatA tr:nth-child(" + i + ") td:last").after("<td><input type=\"text\" name=\"a_" + i + (matRows.length + 1) + "\" value=\"0\"></td>");
                        str += "<td><input type=\"text\" name=\"a_" + (matRows.length + 1) + i + "\" value=\"0\"></td>";
                    }
                    // one more for the nxn element
                    str += "<td><input type=\"text\" name=\"a_" + (matRows.length + 1) + (matRows.length + 1) + "\" value=\"1\"></td>";
                    $("#inMatA tr:last").after("<tr>" + str + "</tr>");

                    // x vector
                    $("#inVecX tr:last").after("<tr><td><div style=\"font-size: 125%;width: 40px;height: 20px;text-align: center;\">$x_{" + (matRows.length + 1) + "}$</div></td></tr>");

                    // b vector
                    $("#inVecB tr:last").after("<tr><td><input type=\"text\" name=\"b_" + (matRows.length + 1) + "\" value=\"0\"></td></tr>");


                    // typeset table
                    MathJax.Hub.Queue(["Typeset", MathJax.Hub]);

                    // hide if 8
                    if (matRows.length === maxRows - 1) {
                        $(this).hide();
                    }
                    // show if > 0
                    if (matRows.length > 0) $("input[name*='remDim']").show();
                }
            });

            $("input[name*='remDim']").click(function () {

                var matRows = $('#inMatA tr');
                var str = "";
                for (var i = 1; i <= matRows.length; i++) {
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
                MathJax.Hub.Queue(["Typeset", MathJax.Hub]);

                // hide if 1
                if (matRows.length === 2) {
                    $(this).hide();
                }
                // show
                if (matRows.length < maxRows + 1) $("input[name*='addDim']").show();

            });
        });
    </script>
</head>

<body>

<header>
    <div class="centerit">
        <p>

            <span style="font-size: 34px"><?php tt('title'); ?></span>
            <br>
            <span style="font-size: 20px"><?php tt('subtitle'); ?><span></p>
    </div>
</header>

<nav>
    <div style="margin: 10; height: 0px; float: right; padding-right: 20px;" id="langSel">
<!--        <img src="img/United-States_set.png" style="margin: 2px;" width="32px">-->
<!--        <img src="img/Germany_set.png" width="32px" style="margin: 2px;">-->
        <a href="index.php?lang=en" class="langEN" onclick="serializeHref(this);">en</a>
        <a href="index.php?lang=de" class="langDE" onclick="serializeHref(this);">de</a>
    </div>
</nav>

<section id="main">
    <?php
    // generateNumbers if necessary
    if (!isset($_POST['a_11']))
        generateNumbers();

    // parse here the form
    parsePostRequest();

    ?>

    <p><?php tt('description'); ?></p>

    <p></p>

    <form name="les" method="post" action="<?php echo $_SERVER['PHP_SELF']."?lang=".getLang(); ?>" onsubmit="return serializeForm(this);">
        <table style="margin-left: auto;margin-right: auto">
            <tr>
                <td>
                    <table class="matrixDecor" id="inMatA">
                        <?php
                        // generate matrix content
                        for ($i = 0; $i < $n; $i++) {
                            echo "<tr>";
                            for ($j = 0; $j < $n; $j++) {
                                echo "<td><input type=\"text\" name=\"a_" . ($i + 1) . ($j + 1) . "\" value=\"" . getNumberOf("a_" . ($i + 1) . ($j + 1)) . "\"></td>";
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
                        for ($i = 0; $i < $n; $i++) {
                            echo "<tr><td><div style=\"font-size: 125%;width: 40px;height: 20px;text-align: center;\">\$x_" . ($i + 1) . "\$</div></td></tr>";
                        }
                        ?>
                    </table>
                </td>
                <td>=</td>
                <td>
                    <table class="matrixDecor" id="inVecB">
                        <?php
                        for ($i = 0; $i < $n; $i++) {
                            $bstr = "b_" . ($i + 1);
                            echo "<tr>";
                            echo "<td><input type=\"text\" name=\"" . $bstr . "\" value=\"" . getNumberOf($bstr) . "\"></td>";
                            echo "</tr>";
                        }
                        ?>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: center">
                    <input type="submit" value="<?php tt('submit'); ?>" class="buttonDecor" name="submit" style="margin-right: 40px">
                    <input type="button" name="addDim" value="+" class="roundButton">
                    <input type="button" name="remDim" value="-" class="roundButton">
                </td>
            </tr>
        </table>
    </form>
    <p class="horzSpace"></p>
    <?php
    if (!$inputcontainserrors) {
        ?>

        <hr style="width: 90%" class="separator">
    <?php
    }
    ?>
    <p class="horzSpace"></p>
    <table style="min-width:300px;max-width:500px;margin-left: auto;margin-right: auto;margin-top: 20px">
        <?php
        if (!$inputcontainserrors) {


            // solve Gauss here

            // set if n > 4 size of matrices smaller
            $fontsize = 100;
            if ($n > 4) $fontsize = 100;
            else $fontsize = 150;


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
            $strMatExCur = $LS->getFormattedTexCode($LS->getPivotRow(), $LS->getPivotCol());
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
                $strMatExCur = $LS->getFormattedTexCode($LS->getPivotRow(), $LS->getPivotCol());
                $strDescCur = $LS->gaussStep();//."pivotrow: ".$LS->getPivotRow()."pivotcol: ".$LS->getPivotCol();

                echo "<tr><td>";
                echo "<span style=\"width:20px\" class=\"roundDecor\">" . $i . "</span>";
                echo "</td><td>";
                echo "<div style=\"font-size: " . $fontsize . "%;width: 100%;text-align: center\">";
                echo "$" . $strMatExLast . "$";
                echo "</div>";
                echo "</td><td width=\"33%\">";
                echo "<span class=\"roundRectDecor\">" . $strDescLast . "</span>";
                echo "</td></tr>";

                // securing
                if ($i > 100) break;
            }
            ?>

            <tr>
                <td><?php echo "<span style=\"width:20px\" class=\"roundDecor\">" . ($i + 1) . "</span>" ?></td>
                <td>
                    <div style="font-size:<?php echo $fontsize; ?>%;width: 100%;text-align: center">
                        $<?php echo $strMatExCur; ?>$
                    </div>
                </td>
                <td><span class="roundRectDecor"><?php echo $strDescCur; ?></span></td>
            </tr>
            <?php
            // print last info if necessary
            if (strpos($strDescCur, 'Ergebnis') === false && $LS->existsSolution()) {
                echo "<tr><td><span style=\"width:20px\" class=\"roundDecor\">" . ($i + 2) . "</span></td>
    <td><div style=\"font-size:" . $fontsize . "%;width: 100%;text-align: center\">\$" . $LS->getFormattedTexCode() . "\$</div></td>
    <td><span class=\"roundRectDecor\">".tt_s('get_result')."</span></td></tr>";
            }
        }
        ?>
    </table>
    <hr style="width: 90%" class="separator">

    <div class="solution" style="font-size: <?php if ($n > 4) echo 100; else echo 125; ?>%; width: 60%;">
        <?php echo tt_s('solution').": ";
        if (!$inputcontainserrors) {

            echo "$" . "L = " . $LS->getAffineSpaceTexString() . "$";
        } else {
            tt('correct_input');
        }
        ?>
    </div>
</section>

<footer>(c) 2014 by L.Spiegelberg | spiegelb (at) in (dot) tum (dot) de
</footer>
</body>
</html>
