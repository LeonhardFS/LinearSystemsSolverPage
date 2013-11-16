<?php

// function to calculate gcd with euclid's algorithm
function gcd($m, $n) {
	if($n == 0) {
		return $m;
	}
	else {
		return gcd($n, $m % $n);
	}
}

// function to calculate lcm using gcd
function lcm($m, $n) {
	return $m * $n / gcd($m, $n);
}

// convert decimal to roman system, returns str
function toRoman($value) {
	if (($value < 1) || ($value >= 5000)) { return "error"; }
      $res = "";

      while($value >= 1000) {$value -= 1000; $res.= "M"; }
      if ($value >= 900) {$value -= 900; $res.= "CM"; }

      while($value >= 500) {$value -= 500; $res.= "D"; }
      if ($value >= 400) {$value -= 400; $res.= "CD"; }

      while($value >= 100) {$value -= 100; $res.= "C"; }
      if ($value >= 90) {$value -= 90; $res.= "XC"; }

      while($value >= 50) {$value -= 50; $res.= "L"; }
      if ($value >= 40) {$value -= 40; $res.= "XL"; }

      while($value >= 10) {$value -= 10; $res.= "X"; }
      if ($value >= 9) {$value -= 9; $res.= "IX"; }

      while($value >= 5) {$value -= 5; $res.= "V"; }
      if ($value >= 4) {$value -= 4; $res.= "IV"; }

      while ($value >= 1) { $value -= 1; $res.= "I"; }

      return $res;
}

?>