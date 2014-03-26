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

<!-- input object !-->

<div id="inputObject">

    <input type="text" class="code" id="customFieldValue" name="customFieldValue[]" value="0" />

</div>


<table border="0">
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
    <td width=\"3%\">";

    echo $i;

    echo "</td>
    <td width=\"64%\">";

    echo "<div style=\"font-size: 200%;\">";

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
    <td width=\"3%\">";

        echo $i;

        echo "</td>
    <td width=\"64%\">";

        echo "<div style=\"font-size: 200%;\">";

        $x = array();
        $x[0] = $LS->R[0][0];
        $x[1] = $LS->R[0][1];
        $x[2] = $LS->R[0][2];

//echo "$".texExtMatrix($LS->A,$x, 3)."$";
        echo "$" . $LS->getFormattedTexCode() . "$";
        echo "</div>";

        echo "</td>
    <td width=\"33%\">";

        echo $str;

        echo "</td></tr>";


        // securing
        if ($i > 15) break;
    }

    ?>
</table>
<p>Loesung: <?php
    echo "$" . "L = " . $LS->getAffineSpaceTexString() . "$";?></p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

</body>
</html>