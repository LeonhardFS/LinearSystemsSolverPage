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

</head>

<body>
<h1>Gleichungssystem $Ax=b$</h1>

<h2>Schritt 1: Gleichung aufstellen</h2>

<p>Hier ist der tolle Gauss Löser!</p>


<table border="0">
    <?php
    // solve Gauss here
    $n = 3;

    // matrix test
    $A = randMatrix($n);
    $x = randVector($n);
    $b = transformVector($A, $x, $n);

    // Attention !!! Test Zero matrices!
    // test purposes

    // Test matrix: 1 2 3  *  1  =  4
    //              0 1 2  *  0  =  2
    //              0 1 1  *  1  =  1
    $A[0][0] = new RationalNumber(1); // first column
    $A[0][1] = new RationalNumber(0);
    $A[0][2] = new RationalNumber(0);

    // second column zero
    $A[1][0] = new RationalNumber(2); // second column
    $A[1][1] = new RationalNumber(1);
    $A[1][2] = new RationalNumber(1);

    // third column zero
    $A[2][0] = new RationalNumber(3); // third column
    $A[2][1] = new RationalNumber(2);
    $A[2][2] = new RationalNumber(1);

    // b
    $b[0] = new RationalNumber(4);
    $b[1] = new RationalNumber(2);
    $b[2] = new RationalNumber(1);

    // x
    $x[0] = 1;
    $x[1] = 0;
    $x[2] = 1;

    // 2nd test case, solution is an affine vector space
    // Test matrix: 1 2 3 * (t)    = 4
    //              0 1 2 * (2-2t) = 2
    //              0 1 2 * (t)    = 2

    // <-> solution: 0        1
    //               2 + R * -2
    //               0        1
    $A[0][0] = new RationalNumber(1); // first column
    $A[0][1] = new RationalNumber(0);
    $A[0][2] = new RationalNumber(0);

    // second column zero
    $A[1][0] = new RationalNumber(2); // second column
    $A[1][1] = new RationalNumber(1);
    $A[1][2] = new RationalNumber(1);

    // third column zero
    $A[2][0] = new RationalNumber(3); // third column
    $A[2][1] = new RationalNumber(2);
    $A[2][2] = new RationalNumber(2);

    // b
    $b[0] = new RationalNumber(4);
    $b[1] = new RationalNumber(2);
    $b[2] = new RationalNumber(2);


    // Test Case 1
    loadTest1();
    loadTest2();
    loadTest3();

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