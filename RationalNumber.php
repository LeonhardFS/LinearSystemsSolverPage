<?php

include_once('math.php');

class RationalNumber {
	public $numerator;
	public $denominator;

	// constructor
	function __construct() {
		$a = func_get_args(); 
        $i = func_num_args(); 
        if (method_exists($this,$f='__construct'.$i)) { 
            call_user_func_array(array($this,$f),$a); 
        } 
		
		
	}
	
	function __construct0() {
		$this->numerator = 0;
		$this->denominator = 1;
	}
	
	// value constructor 1
	function __construct1($numerator) {
		$this->numerator = $numerator;
		$this->denominator = 1;
	}
	
	// value constructor 2
	function __construct2($numerator, $denominator) {
		$this->numerator = $numerator;
		assert($denominator <> 0);
		$this->denominator = $denominator;
	}
	
	// reduce rational number by gcd
	function reduce() {
		$gcd = gcd($this->numerator, $this->denominator);
		
		// reduce
		$this->numerator /= $gcd;
		$this->denominator /= $gcd;
		
		return $gcd == 1;
	}
	
	// operator +
	public function plus($other) {
		assert(is_a($other, 'RationalNumber'));
		$this->numerator = $this->numerator * $other->denominator + $other->numerator * $this->denominator;
		$this->denominator = $this->denominator * $other->denominator;
	}
	
	// operator -
	public function minus($other) {
		assert(is_a($other, 'RationalNumber'));
		$this->numerator = $this->numerator * $other->denominator - $other->numerator * $this->denominator;
		$this->denominator = $this->denominator * $other->denominator;
	}
	
	// operator *
	public function times($other) {
		if(is_a($other, 'RationalNumber')) {
			$this->numerator *= $other->numerator;
			$this->denominator *= $other->denominator;
		} else {
			$this->numerator *= $other;
		}
	}
	
	// operator /
	public function divide($other) {
		 if(is_a($other, 'RationalNumber')) {
			$this->numerator *= $other->denominator;
			$this->denominator *= $other->numerator;
		} else {
			$this->denominator *= $other;
		}
	}
	
	// convert to frac tex string
	public function totex() {
		$res = "";
		if($this->denominator <> 1) {
			$res.="\\frac{";
			$res.=$this->numerator;
			$res.="}{";
			$res.=$this->denominator;
			$res.="}";
		} else {
			$res.=$this->numerator;
		}
		
		return $res;
	}
	
	// magic method to string
	public function __toString() {
		return $this->totex();
	}

    // is rational number negative?
    public function isNegative() {
        // negative <=> (numerator > 0 && denominator < 0) || (numerator < 0 && denominator > 0)
        return ($this->numerator > 0 && $this->denominator <0) || ($this->numerator < 0 && $this->denominator > 0);
    }
}

// direct operator functions
// operator +
function rplus($a, $b) {
	assert(is_a($a, 'RationalNumber'));
	assert(is_a($b, 'RationalNumber'));
	$res = new RationalNumber($a->numerator * $b->denominator + $b->numerator * $a->denominator, $a->denominator * $b->denominator);
	return $res;
}

// operator -
function rminus($a, $b) {
	assert(is_a($a, 'RationalNumber'));
	assert(is_a($b, 'RationalNumber'));
	$res = new RationalNumber($a->numerator * $b->denominator - $b->numerator * $a->denominator, $a->denominator * $b->denominator);
	return $res;
}

// operator *
function rtimes($a, $b) {
	assert(is_a($a, 'RationalNumber'));
	if(is_a($b, 'RationalNumber')) {
		$res = new RationalNumber($a->numerator * $b->numerator, $a->denominator * $b->denominator);
		return $res;
	} else {
		$res = new RationalNumber($a->numerator * $b, $a->denominator);
		return $res;
	}
}

// operator /
function rdivide($a, $b) {
	assert(is_a($a, 'RationalNumber'));
	 if(is_a($b, 'RationalNumber')) {
		$res = new RationalNumber($a->numerator * $b->denominator, $a->denominator * $b->numerator);
		return $res;
	} else {
		$res = new RationalNumber($a->numerator, $a->denominator * $b);
		return $res;
	}
}

function requals($a, $b) {
	assert(is_a($a, 'RationalNumber'));
	assert(is_a($b, 'RationalNumber'));
	
	$ta = clone $a;
	$tb = clone $b;
	
	$ta->reduce();
	$tb->reduce();
	
	if($ta->numerator <> $tb->numerator)return false;
	if($b->denominator <> $tb->denominator)return false;
	return true;
}

?>