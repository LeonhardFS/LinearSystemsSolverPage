<?php
include_once('LinearSystem.php');
include_once('RationalNumber.php');
include_once('math.php');
include_once('tests.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);
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

<p>Hier ist der tolle Gauss Löser!</p>
<span class="roundDecor">1</span>
<p></p>
<table style="margin-left: auto;margin-right: auto">
    <tr>
        <td>
            <form>
                <table class="matrixDecor">
                    <tr>
                        <td><input type="text"></td>
                        <td><input type="text"></td>
                        <td><input type="text"></td>
                    </tr>
                    <tr>
                        <td><input type="text"></td>
                        <td><input type="text"></td>
                        <td><input type="text"></td>
                    </tr>
                    <tr>
                        <td><input type="text"></td>
                        <td><input type="text"></td>
                        <td><input type="text"></td>
                    </tr>
                </table>
            </form>

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
                    <td><input type="text"></td>
                </tr>
                <tr>
                    <td><input type="text"></td>
                </tr>
                <tr>
                    <td><input type="text"></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align: center"><input type="button" value="Lösen" class="buttonDecor"></td>
    </tr>
</table>
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