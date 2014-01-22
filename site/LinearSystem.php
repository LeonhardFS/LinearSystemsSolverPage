<?php
// inc rationalnumber
include_once('./RationalNumber.php');

// function to multiply vector by matrix
function transformVector($A, $x, $n) {
	assert($n >= 1);
	
	$res = array();
	for($i = 0; $i < $n; $i++) {
		
		$t = new RationalNumber(0, 1);
		for($j = 0; $j < $n; $j++) {
			$t = rplus($t, rtimes($A[$i][$j], $x[$j])); 
		}
		$res[$i] = clone $t;
	}
	
	return $res;
}

// creates a random n by n matrix
function randMatrix($n) {
	$res = array(array());
	for($i = 0; $i < $n; $i++) {
		for($j = 0; $j < $n; $j++) { 
			$res[$i][$j] = new RationalNumber(rand() % 10, 1);
		}
	}
	return $res;
}

// creates a random n vector
function randVector($n) {
	$res = array();
	for($j = 0; $j < $n; $j++) { 
		$res[$j] = new RationalNumber(rand() % 10, 1);
	}
	return $res;
}

// helper to create tex code for vector
function texVector($v, $n) {
	$str = "";
	$str .="\\left( \\begin{array}{c}\n";
	for($i = 0; $i < $n; $i++) {
		$str .= $v[$i];
		if($i <> $n - 1)$str .= "\\\\";
	}
	$str .= "\\end{array} \\right)";
	return $str;
}

// create latex code for matrix
function texMatrix($A, $n) {
	$str = "";
	$str .="\\left( \\begin{array}{";
	for($i = 0; $i < $n; $i++)$str .="c";
	$str .= "}\n";
	
	for($i = 0; $i < $n; $i++) {
		for($j = 0; $j < $n; $j++) {
			$str .= $A[$j][$i];
			if($j <> $n - 1)$str .= " & ";
			else if($i <> $n - 1)$str .= " \\\\\n";
		}
	}
	
	$str .= "\\end{array} \\right)";
	return $str;
}

 // create latex code for extended matrix
function texExtMatrix($A, $b, $n) {
	$str = "";
	$str .="\\left( \\begin{array}{";
	for($i = 0; $i < $n; $i++)$str .="c";
	$str .= "|c}\n";
	
	for($i = 0; $i < $n; $i++) {
		for($j = 0; $j < $n; $j++) {
			$str .= $A[$j][$i];
			$str .= " & ";
			if($j == $n - 1) {
				$str .= $b[$i];
				if($i <> $n - 1)$str .= " \\\\\n";
			}
		}
	}
	
	$str .= "\\end{array} \\right)";
	return $str;
}

// class for holding a linear system of form Ax=b
class LinearSystem {
	public $A;
	public $b;

	private $step;
	private $c;
	private $r;
	private $finished;
	private $n; // A is n by n matrix, b n-vector
	public $R; // result matrix
	private $lCurIndex; // current Index for free parameter at R Matrix
	
	// constants for modes
	private $mode;
	const MODE_FIRST = 1;
	const MODE_SECOND = 2;
	
	function __construct($n) {
		$this->n = $n;
		
		$this->A = array(array());
		$this->R = array(array());
		$this->b = array();
		
		// used to indicate progress of algorithm
		$this->step = 0;
		$this->c = 0;
		$this->r = 1;
		$this->lCurIndex = 1;
		$this->finished = false;
		
		// set mode to first
		$this->mode = LinearSystem::MODE_FIRST;
	}
	
	
	function initGauss() {
		// set b to R
		for($i = 0; $i < $this->n; $i++)
			for($j = 0; $j < $this->n; $j++) {
				if($i == 0)$this->R[$i][$j] = clone $this->b[$i];
				else $this->R[$i][$j] = new RationalNumber(0);
			}
	}
	
	function solveWithGauss() {
		// part 1: row reduction
		
		// go through columns
		for($c = 0; $c < $this->n - 1; $c++) {
			// go through rows
			for($r = $c + 1; $r < $this->n; $r++) {
				// eliminate element
				//$pivot = clone $this->A[$c][$c];
				
				$coefficient = rdivide($this->A[$c][$c + 1], $this->A[$c][$c]);
				
				$this->A[$c][$r] = new RationalNumber(0, 1);
				
				// madd row
				for($k = $c + 1; $k < $this->n; $k++) {
					$temp = rminus($this->A[$k][$r], rtimes($this->A[$k][$c], $coefficient));
					$this->A[$k][$r] = clone $temp;
				}
				
			}
		}
	}
	
	public function finished() {
		return $this->finished;
	}
	
	private function swapRows($a, $b) {
		for($k = 0; $k < $this->n; $k++) {
			$t = clone $this->A[$k][$a];
			$this->A[$k][$a] = clone $this->A[$k][$b];
			$this->A[$k][$b] = clone $t;
			
			// same with R
			$t = clone $this->R[$k][$a];
			$this->R[$k][$a] = clone $this->R[$k][$b];
			$this->R[$k][$b] = clone $t;
		}
	}
	
	// row b holds result
	private function gaussRows($a, $b, $c) {
		// c is column
		$coefficient = rdivide($this->A[$c][$b], $this->A[$c][$a]);
		
		// small optimization, when coefficient is 1, reduce immediately
		if($coefficient->numerator == $coefficient->denominator)$coefficient->reduce();
		
		for($k = 0; $k < $this->n; $k++) {
			$this->R[$k][$b] = rminus($this->R[$k][$b], rtimes($coefficient, $this->R[$k][$a]));
		}
		
		for($k = $c; $k < $this->n; $k++) {
			$this->A[$k][$b] = rminus($this->A[$k][$b], rtimes($coefficient, $this->A[$k][$a]));
		}
		
		// return descriptional string for output
		$str = "";
		if($coefficient->numerator == $coefficient->denominator) // if 1, we do not have to display the obvious coefficient!
			$str .= "\\text{".toRoman($b + 1)."} = \\text{".toRoman($b + 1)."} - \\text{".toRoman($a)."}";
		else
			$str .= "\\text{".toRoman($b + 1)."} = ".$coefficient." \\cdot \\text{".toRoman($b + 1)."} - \\text{".toRoman($a)."}";
			
		return $str;
	}
	
	public function gaussStep() {
		$str = "";
		
		// first: differ between two modes!
		// first mode is reducing Matrix to row column form
		// second mode is substituion, precisely solving an upper triangular matrix
	if($this->mode == LinearSystem::MODE_FIRST) {
		// zero at current upper row?
		if(requals($this->A[$this->c][$this->c], new RationalNumber(0))) {
			// swap rows 
			// therefore find beginning with last row empty row
			$found = false;
			for($k = $this->n - 1; $k > 0 && $found <> true; $k--) {
				if(requals($this->A[$this->c][$k], new RationalNumber(0)) <> true) {
					// swap
					$found = true;
					
					$str .= "tausche Reihe ".toRoman($this->c + 1)." mit Reihe ".toRoman($k + 1);
					
					$this->swapRows($k, $this->c);
					
				}
			}
			
			if($found <> true) {
				// test linear independece!
				$str .= "Spalte $\\text{".toRoman($this->c + 1)."}$ besteht nur aus Nulleinträgen, gehe zur nächsten Spalte";
				$this->c++;
				$this->r = $this->c + 1;
			}
		}
		else {
			// regular
			
			// go through rows
			
	
				// substract rows if not equal to 0
				if(requals($this->A[$this->c][$this->r], new RationalNumber(0)) == true) {
					$str .= " nichts zu tun, ";
					if($this->r + 1 >= $this->n - 1) {
						if($this->c + 1 == $this->n - 1)
						$str .= "Zeilenstufenform erreicht";
						else
						$str .= "gehe zur nächsten Spalte";
					}
					else {
						$str .= "gehe zur nächsten Zeile";
					}
				}
				else {
					//substract rows
					$str = "rechne ";//.toRoman($this->r + 1)." = ".toRoman($this->c + 1)." - ".toRoman($this->r + 1);
					$tmp = $this->gaussRows($this->c, $this->r, $this->c);
					$str.="$".$tmp."$";
				}
				
			// small optimization for better readability: If numerator if 0, reduce immediately!
			for($i = 0; $i < $this->n; $i++) {
				if($this->A[$this->c][$i]->numerator == 0)$this->A[$this->c][$i]->reduce();
			}
				
			
			$this->r++;
			//already finished?
			if($this->r >= $this->n - 1) {
				$this->c++;
				$this->r = $this->c + 1;
				//$str .= "new column c: ".$this->c." r: ".$this->r;
			}

		}
		
		// should mode be changed?
		if($this->c == $this->n - 1) {
			$this->mode = LinearSystem::MODE_SECOND;//$this->finished = true;
			
			// we solve now system for upper triangular Matrix Ux=b, this means we count columns downwards
			$this->c = $this->n - 1;
			$this->r = $this->n - 1;
		}
	}
	else if($this->mode == LinearSystem::MODE_SECOND) {
		
		// solve here upper triangular matrix!
		
		// is element A_cc 0?
		if($this->A[$this->c][$this->c]->numerator == 0) {
			$str="error not yet implemented";
		}
		else {
			/*if($this->c == $this->n - 1) {
				$str = "rechne $\\text{".toRoman($this->c + 1). "} = \\text{".toRoman($this->c + 1)."} :".$this->A[$this->c][$this->c]."$";
				for($i=0; $i < $this->n; $i++) {
					$this->R[$i][$this->c] = rdivide($this->b[$this->c], $this->A[$this->c][$this->c]);
					$this->A[$this->c][$this->c] = new RationalNumber(1);
				}
			}
			else {*/
				// go through all elements in row with r as counter
			
				// last step, normalize to 1?
				if($this->r == $this->c) {
					if($this->A[$this->r][$this->c]->numerator == $this->A[$this->r][$this->c]->denominator)
					$str = "nichts zu tun, nächste Spalte";
					else
					$str = "rechne $\\text{".toRoman($this->c + 1). "} = \\text{".toRoman($this->c + 1)."}  : ".$this->A[$this->r][$this->c]."$";
					
					// go through R
					for($i = 0; $i < $this->n - 1; $i++) {
						$this->R[$i][$this->c] = rdivide($this->R[$i][$this->r], $this->A[$this->c][$this->r]);
					}
					// set to one(no complicated calculations)
					$this->A[$this->r][$this->c] = new RationalNumber(1);
				}/*
				else {
					// text output, for 1 * something shorten! 0 * something has also to be dealt with!
					// ATTENTION!
					if($this->A[$this->r][$this->c]->numerator == $this->A[$this->r][$this->c]->denominator)
					$str = "rechne $\\text{".toRoman($this->c + 1). "} = \\text{".toRoman($this->c + 1)."} - \\text{".toRoman($this->r + 1)."}$";
					else
					$str = "rechne $\\text{".toRoman($this->c + 1). "} = \\text{".toRoman($this->c + 1)."} -".$this->A[$this->r][$this->c]."\\cdot \\text{".toRoman($this->r + 1)."}$";
					
					// go through R
					for($i = 0; $i < $this->n - 1; $i++) {
						$this->R[$i][$this->c] = rminus($this->R[$i][$this->c], rtimes($this->A[$this->c][$this->r], $this->R[$i][$this->r]));
					}
					// set to zero(no complicated calculations)
					$this->A[$this->r][$this->c] = new RationalNumber(0);
				}
			//}*/
		}
		
		
		// count down
		$this->r--; // we use this counter here now for the rows, just want to make steps easy!
		
		// new column?
		if($this->r < $this->c) {
			$this->r = $this->n - 1;
			$this->c--;
		}
		
		//echo "Well done!";
		
		// negative ?
		if($this->c == -1)$this->finished = true;
	}
	else {
		$str = "unknown mode, complete failure!";
	}
		return $str;
	}
	
}

?>