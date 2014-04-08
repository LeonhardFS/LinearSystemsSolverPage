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
    <!--<script type="text/javascript" src="./MathJax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
    -->
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
<h1>Gleichungssystem $Ax=b$</h1>

<h2>Schritt 1: Gleichung aufstellen</h2>

<?php
if($formsend)echo "Form abgeschickt";
?>
<p>Hier ist der tolle Gauss Löser!</p>
<span class="roundDecor">1</span>
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
<hr style="width: 90%">
<p class="horzSpace"></p>
<table style="min-width:300px;max-width:500px;margin-left: auto;margin-right: auto;margin-top: 20px">
    <?php
    // solve Gauss here
    $n = 3;

    // load test case
    loadTest1();
    //loadTest2();
    //loadTest3();

    for($i = 1; $i <= 3; $i++)
        for($j = 1; $j <= 3; $j++) {
            $str = 'a_'.$i.$j;
            $A[$j - 1][$i - 1] = new RationalNumber(intval($_POST[$str]));
        }

    for($i = 1; $i <= 3; $i++) {
        $str = 'b_'.$i;
        $b[$i - 1] = new RationalNumber(intval($_POST[$str]));
    }

    $LS = new LinearSystem($n);
    $LS->A = $A;
    $LS->b = $b;
    $LS->initGauss();


    $i = 0;


    echo "
  <tr>
    <td>";

    echo "<span style=\"width:20px\" class=\"roundDecor\">".$i."</span>";

    echo "</td>
    <td ";

    echo "<div style=\"font-size: 150%;width: 100%;text-align: center\">";

    $x = array();
    $x[0] = $LS->R[0][0];
    $x[1] = $LS->R[0][1];
    $x[2] = $LS->R[0][2];
    echo "$" . texExtMatrix($LS->A, $x, 3) . "$";

    echo "</div>";

    echo "</td>
    <td width=\"33%\">";

    echo "Ausgangsmatrix";

    echo "</td></tr>";

    while ($LS->finished() <> true) {

        $i++;

        // step
        $str = $LS->gaussStep();

        echo "
  <tr>
    <td>";

        echo "<span style=\"width:20px\" class=\"roundDecor\">".$i."</span>";

        echo "</td>
    <td>";

        echo "<div style=\"font-size: 150%;width: 100%;text-align: center\">";

        $x = array();
        $x[0] = $LS->R[0][0];
        $x[1] = $LS->R[0][1];
        $x[2] = $LS->R[0][2];

//echo "$".texExtMatrix($LS->A,$x, 3)."$";
        echo "$" . $LS->getFormattedTexCode() . "$";
        echo "</div>";

        echo "</td>
    <td width=\"33%\">";

        echo "<span class=\"roundRectDecor\">".$str."</span>";

        echo "</td></tr>";


        // securing
        if ($i > 15) break;
    }

    ?>
</table>
<hr style="width: 90%">

<div class="solution">Lösung: <?php
    echo "$" . "L = " . $LS->getAffineSpaceTexString() . "$";?></div>


</body>
</html>