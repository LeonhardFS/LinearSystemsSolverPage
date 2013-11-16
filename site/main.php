<?php
include_once('LinearSystem.php');
include_once('RationalNumber.php');
include_once('math.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Ax=b</title>
<style type="text/css">
@import url("style.css");
</style>

<!-- configuring MathJax to allow inline TexSyntax !-->
<script type="text/x-mathjax-config">
MathJax.Hub.Config({
  extensions: ["tex2jax.js","fp.js"],
  jax: ["input/TeX","output/HTML-CSS"],
  "HTML-CSS": {
    styles: {".MathJax_Preview": {visibility: "hidden"}}
  },
  "SVG": {
    styles: {".MathJax_Preview": {visibility: "hidden"}}
  },
  tex2jax: {inlineMath: [["$","$"],["\\(","\\)"]]},
  TeX: {extensions: ["xypic.js", "AMSmath.js","AMSsymbols.js"]}
});
</script>    
    
<!-- local copy !-->
<script type="text/javascript" src="./MathJax/MathJax.js?config=TeX-AMS-MML_HTMLorMML">
</script>

</head>

<body>
<h1>Gleichungssystem $Ax=b$</h1>
<h2>Schritt 1: Gleichung aufstellen</h2>
Blabla2
<p><?php
/*$r1 = new RationalNumber();
$r2 = new RationalNumber(3, 5);
$r3 = new RationalNumber(20, 1);
$r4 = new RationalNumber(20, 4);
echo "<p>$".$r1->totex()."$</p>";
echo "<p>$".$r2->totex()."$</p>";
echo "<p>$".$r3->totex()."$</p>";
echo "<p>$".$r4->totex()."$</p>";
$r4->reduce();
echo "<p>$".$r4->totex()."$</p>";
$tr4 = clone $r4;
$r4->minus($r2);
echo "<p>$".$tr4->totex()." - ".$r2->totex()." = ".$r4->totex();
$r4->reduce();
echo " = ".$r4->totex()."$</p>";

echo "<p>".gcd(120, 60)."</p>";
echo "<p>$".rplus(new RationalNumber(30, 4), new RationalNumber(2,4))->totex()."$</p>";
*/
?></p>

<table width="100%" border="0">
<?php
// solve Gauss here
$n = 3;

// matrix test
$A = randMatrix($n);
$x = randVector($n);
$b = transformVector($A, $x, $n);

// Attention !!! Test Zero matrices!
// test purposes
//$A[0][0] = new RationalNumber(0);
$A[0][1] = new RationalNumber(0);
$A[0][2] = new RationalNumber(0);
/*
// second column zero
$A[1][0] = new RationalNumber(0);
$A[1][1] = new RationalNumber(0);
$A[1][2] = new RationalNumber(0);

// third column zero
$A[2][0] = new RationalNumber(0);
$A[2][1] = new RationalNumber(0);
$A[2][2] = new RationalNumber(0);
*/

$LS = new LinearSystem($n);
$LS->A = $A;
$LS->b = $b;
$LS->initGauss();
?>
<p>Lösungsvektor $\vec{x} = <?php echo texVector($x, 3); ?>$</p>
<?php


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
echo "$".texExtMatrix($LS->A,$x, 3)."$";

echo "</div>";
	
	echo "</td>
    <td width=\"33%\">";
	
	echo "Ausgangsmatrix";
	
	echo "</td></tr>";

while($LS->finished() <> true){
	
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

echo "$".texExtMatrix($LS->A,$x, 3)."$";

echo "</div>";
	
	echo "</td>
    <td width=\"33%\">";
	
	echo $str;
	
	echo "</td></tr>";
	
	
	// secruing
	if($i > 10)break;
}

  ?>
</table>
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
<p>&nbsp;</p>

</body>
</html>