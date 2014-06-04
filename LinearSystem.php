<?php
// inc rationalnumber
include_once('./RationalNumber.php');
include_once('lang.php');

// function to multiply vector by matrix
function transformVector($A, $x, $n)
{
    assert($n >= 1);

    $res = array();
    for ($i = 0; $i < $n; $i++) {

        $t = new RationalNumber(0, 1);
        for ($j = 0; $j < $n; $j++) {
            $t = rplus($t, rtimes($A[$i][$j], $x[$j]));
        }
        $res[$i] = clone $t;
    }

    return $res;
}

// creates a random n by n matrix
function randMatrix($n)
{
    $res = array(array());
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $res[$i][$j] = new RationalNumber(rand() % 10, 1);
        }
    }
    return $res;
}

// creates a random n vector
function randVector($n)
{
    $res = array();
    for ($j = 0; $j < $n; $j++) {
        $res[$j] = new RationalNumber(rand() % 10, 1);
    }
    return $res;
}

// helper to create tex code for vector
function texVector($v, $n)
{
    $str = "";
    $str .= "\\left( \\begin{array}{r}\n";
    for ($i = 0; $i < $n; $i++) {
        $str .= $v[$i];
        if ($i <> $n - 1) $str .= "\\\\";
    }
    $str .= "\\end{array} \\right)";
    return $str;
}

// create latex code for matrix
function texMatrix($A, $n)
{
    $str = "";
    $str .= "\\left( \\begin{array}{";
    for ($i = 0; $i < $n; $i++) $str .= "c";
    $str .= "}\n";

    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $str .= $A[$j][$i];
            if ($j <> $n - 1) $str .= " & ";
            else if ($i <> $n - 1) $str .= " \\\\\n";
        }
    }

    $str .= "\\end{array} \\right)";
    return $str;
}

// create latex code for extended matrix
function texExtMatrix($A, $b, $n)
{
    $str = "";
    $str .= "\\left( \\begin{array}{";
    for ($i = 0; $i < $n; $i++) $str .= "c";
    $str .= "|c}\n";

    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $str .= $A[$j][$i];
            $str .= " & ";
            if ($j == $n - 1) {
                $str .= $b[$i];
                if ($i <> $n - 1) $str .= " \\\\\n";
            }
        }
    }

    $str .= "\\end{array} \\right)";
    return $str;
}

// class for holding a linear system of form Ax=b
class LinearSystem
{
    public $A;
    public $b;

    private $step;
    private $c;
    private $r;
    private $finished;
    private $n; // A is n by n matrix, b n-vector
    public $R; // result matrix
    private $lCurIndex; // current Index for free parameter at R Matrix
    private $varNames; // string array, to name variables which are introduced if rk A < n!
    private $maxVarNames; // number of var names
    private $existsSolution; // empty set?

    // constants for modes
    private $mode;
    const MODE_FIRST = 1;
    const MODE_SECOND = 2;

    function __construct($n)
    {
        $this->n = $n;

        $this->A = array(array());
        $this->R = array(array());
        $this->b = array();


        // matrix arrays are addressed in the following way
        // A[col][row]
        // ie for A = 1 2 3
        //            4 5 6
        //            7 8 9
        // A[2][1] = 6 ! (note indices start with 0!)

        // used to indicate progress of algorithm
        $this->step = 0;
        $this->c = 0;
        $this->r = 1;
        $this->lCurIndex = 1;
        $this->finished = false;

        // init var names
        $this->varNames[0] = "t";
        $this->varNames[1] = "s";
        $this->varNames[2] = "r";
        $this->varNames[3] = "u";
        $this->varNames[4] = "v";
        $this->varNames[5] = "w";
        $this->maxVarNames = 6;

        // set mode to first
        $this->mode = LinearSystem::MODE_FIRST;
    }


    function initGauss()
    {
        // set b to R
        for ($i = 0; $i < $this->n; $i++)
            for ($j = 0; $j < $this->n; $j++) {
                if ($i == 0) $this->R[$i][$j] = clone $this->b[$j]; // R's first column equals b
                else $this->R[$i][$j] = new RationalNumber(0); // set other cols to zero
            }

        // R has dimension n+1 cols and n columns
        for ($j = 0; $j < $this->n; $j++) {
            $this->R[$this->n][$j] = new RationalNumber(0);
        }

        // set existsSolution to true(default mode)
        $this->existsSolution = true;
    }

    function solveWithGauss()
    {
        // part 1: row reduction

        // go through columns
        for ($c = 0; $c < $this->n - 1; $c++) {
            // go through rows
            for ($r = $c + 1; $r < $this->n; $r++) {
                // eliminate element
                //$pivot = clone $this->A[$c][$c];

                $coefficient = rdivide($this->A[$c][$c + 1], $this->A[$c][$c]);

                $this->A[$c][$r] = new RationalNumber(0, 1);

                // madd row
                for ($k = $c + 1; $k < $this->n; $k++) {
                    $temp = rminus($this->A[$k][$r], rtimes($this->A[$k][$c], $coefficient));
                    $this->A[$k][$r] = clone $temp;
                }

            }
        }
    }

    public function finished()
    {
        return $this->finished;
    }

    public function existsSolution()
    {
        return $this->existsSolution;
    }

    private function swapRows($a, $b)
    {
        for ($k = 0; $k < $this->n; $k++) {
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
    // row b = row b - coeff * row a
    // coeff is determined by c
    private function gaussRows($b, $a, $c)
    {
        // c is column
        $coefficient = rdivide($this->A[$c][$b], $this->A[$c][$a]);

        // reduce
        $coefficient->reduce();

        // small optimization, when coefficient is 1, reduce immediately
        if ($coefficient->numerator == $coefficient->denominator) $coefficient->reduce();

        // go through cols
        for ($k = 0; $k < $this->n + 1; $k++) {
            $this->R[$k][$b] = rminus($this->R[$k][$b], rtimes($coefficient, $this->R[$k][$a]));

            // reduce
            $this->R[$k][$b]->reduce();
        }

        for ($k = $c; $k < $this->n; $k++) {
            $this->A[$k][$b] = rminus($this->A[$k][$b], rtimes($coefficient, $this->A[$k][$a]));

            // reduce
            $this->A[$k][$b]->reduce();
        }

        // return descriptional string for output
        $str = "";
        if ($coefficient->numerator == $coefficient->denominator) // if 1, we do not have to display the obvious coefficient!
            $str .= "\\tilde{\\text{" . toRoman($b + 1) . "}} = \\text{" . toRoman($b + 1) . "} - \\text{" . toRoman($a + 1) . "}";
        else
            $str .= "\\tilde{\\text{" . toRoman($b + 1) . "}} = \\text{" . toRoman($b + 1) . "} - " . $coefficient->toTexWithBrackets() . " \\cdot \\text{" . toRoman($a + 1) . "}";

        return $str;
    }

    // the magic function!
    public function gaussStep()
    {
        $str = "";

        // fix for 1x1 matrix
        if ($this->n == 1) {
            $this->mode = LinearSystem::MODE_SECOND;
            $this->r = 0;
            $this->c = 0;
        }

        // first: differ between two modes!
        // first mode is reducing Matrix to row column form
        // second mode is substituion, precisely solving an upper triangular matrix
        if ($this->mode == LinearSystem::MODE_FIRST) {
            // zero at current upper row?
            if (requals($this->A[$this->c][$this->c], new RationalNumber(0))) {
                // swap rows
                // therefore find beginning with last row empty row
                $found = false;
                for ($k = $this->n - 1; $k > 0 && $found <> true; $k--) {
                    if (requals($this->A[$this->c][$k], new RationalNumber(0)) <> true) {
                        // swap
                        $found = true;

                        if (langDE())
                            $str .= "tausche Zeile $\\text{" . toRoman($this->c + 1) . "}$ mit Zeile $\\text{" . toRoman($k + 1)."}$";
                        else
                            $str .= "swap rows $\\text{" . toRoman($this->c + 1) . "}$ and $\\text{" . toRoman($k + 1)."}$";

                        $this->swapRows($k, $this->c);

                    }
                }

                if ($found <> true) {
                    // test linear independece!
                    if (langDE())
                        $str .= "Spalte $\\text{" . toRoman($this->c + 1) . "}$ besteht nur aus Nulleinträgen, gehe zur nächsten Spalte";
                    else
                        $str .= "There are only zeros in column $\\text{" . toRoman($this->c + 1) . "}$, go to next column";
                    $this->c++;
                    $this->r = $this->c + 1;
                }
            } else {
                // regular

                // go through rows


                // substract rows if not equal to 0
                if (requals($this->A[$this->c][$this->r], new RationalNumber(0)) == true) {
                    if (langDE())
                        $str .= " nichts zu tun, ";
                    else
                        $str .= " nothing to do, ";
                    if ($this->r + 1 >= $this->n - 1) {
                        if ($this->c + 1 == $this->n - 1)
                            if (langDE())
                                $str .= "Zeilenstufenform erreicht";
                            else
                                $str .= "reached row echelon form";
                        else
                            if (langDE())
                                $str .= "gehe zur nächsten Spalte";
                            else
                                $str .= "go to next column";
                    } else {
                        if (langDE())
                            $str .= "gehe zur nächsten Zeile";
                        else
                            $str .= "go to next row";
                    }
                } else {
                    //substract rows
                    if (langDE())
                        $str = "rechne ";
                    else
                        $str = "let ";
                    $tmp = $this->gaussRows($this->r, $this->c, $this->c);
                    $str .= "$" . $tmp . "$";
                }

                // small optimization for better readability: If numerator equals 0, reduce immediately!
                for ($i = 0; $i < $this->n; $i++) {
                    if ($this->A[$this->c][$i]->numerator == 0) $this->A[$this->c][$i]->reduce();
                }


                $this->r++;
                //already finished?
                if ($this->r >= $this->n) {
                    $this->c++;
                    $this->r = $this->c + 1;
                    //$str .= "new column c: ".$this->c." r: ".$this->r;
                }
            }

            // should mode be changed?
            if ($this->c == $this->n - 1) {
                $this->mode = LinearSystem::MODE_SECOND; //$this->finished = true;

                // we solve now system for upper triangular Matrix Ux=b, this means we count columns downwards
                $this->c = $this->n - 1;
                $this->r = $this->n - 1;
            }
        } else if ($this->mode == LinearSystem::MODE_SECOND) {

            // solve here upper triangular matrix!

            // is element A_cc 0?
            if ($this->A[$this->c][$this->c]->numerator == 0) {

                // is b also 0?
                if ($this->b[$this->c]->numerator == 0) {
                    // now introduce new variable and set entry in matrix A to 0
                    // don't forget to set in the next zero column corresponding line to 1!
                    $this->A[$this->c][$this->c] = new RationalNumber(1);

                    // set R
                    $this->R[$this->lCurIndex][$this->c] = new RationalNumber(1);

                    if ($this->lCurIndex - 1 < $this->maxVarNames)
                        if (langDE())
                            $str = "Nullzeile, führe neue Variable $" . $this->varNames[$this->lCurIndex - 1] . " \\in \\mathbb{R}$ ein";
                        else
                            $str = "zero row, introduce new variable $" . $this->varNames[$this->lCurIndex - 1] . " \\in \\mathbb{R}$";
                    else $str = "error";

                    $this->lCurIndex++;
                } else {
                    // there's a contradiction => 1 = 0
                    // => no solution
                    if (langDE())
                        $str = "Widerspruch in Zeile $\\text{" . toRoman($this->c + 1) . "}$, es gibt keine Lösung";
                    else
                        $str = "contradiction in row $\\text{" . toRoman($this->c + 1) . "}$, there is no solution";
                    $this->finished = true;
                    $this->existsSolution = false;
                }

            } else {
                // go through all elements in row with r as counter

                // last step, normalize to 1?
                if ($this->r == $this->c) {
                    if ($this->A[$this->r][$this->c]->numerator == $this->A[$this->r][$this->c]->denominator)
                        // finished?
                        if ($this->c == 0)
                            if (langDE())
                                $str = "Ergebnis aus Matrix ablesen";
                            else
                                $str = "take result from matrix";
                        else
                            if (langDE())
                                $str = "nichts zu tun, nächste Zeile";
                            else

                                $str = "nothing to do, go to next row";

                    else {
                        if (langDE())
                            $str = "kürzen, rechne $\\tilde{\\text{" . toRoman($this->c + 1) . "}} = \\text{" . toRoman($this->c + 1) . "}  : " . $this->A[$this->r][$this->c]->toTexWithBrackets() . "$";
                        else
                            $str = "reduce row by $\\tilde{\\text{" . toRoman($this->c + 1) . "}} = \\text{" . toRoman($this->c + 1) . "}  : " . $this->A[$this->r][$this->c]->toTexWithBrackets() . "$";

                        // go through R
                        for ($i = 0; $i <= $this->n - 1; $i++) { // fix for 1x1 matrix!
                            $this->R[$i][$this->c] = rdivide($this->R[$i][$this->r], $this->A[$this->c][$this->r]);

                            //reduce
                            $this->R[$i][$this->c]->reduce();
                        }
                    }
                    // set to one(no complicated calculations)
                    $this->A[$this->r][$this->c] = new RationalNumber(1);
                } else {
                    // text output, for 1 * something shorten! 0 * something has also to be dealt with!
                    // ATTENTION!
                    if ($this->A[$this->r][$this->c]->numerator == $this->A[$this->r][$this->c]->denominator) {
                        if (langDE())
                            $str = "rechne $\\tilde{\\text{" . toRoman($this->c + 1) . "}} = \\text{" . toRoman($this->c + 1) . "} - \\text{" . toRoman($this->r + 1) . "}$";
                        else
                            $str = "let $\\tilde{\\text{" . toRoman($this->c + 1) . "}} = \\text{" . toRoman($this->c + 1) . "} - \\text{" . toRoman($this->r + 1) . "}$";
                    } else {
                        if (langDE())
                            $str = "rechne $\\tilde{\\text{" . toRoman($this->c + 1) . "}} = \\text{" . toRoman($this->c + 1) . "} -" . $this->A[$this->r][$this->c]->toTexWithBrackets() . "\\cdot \\text{" . toRoman($this->r + 1) . "}$";
                        else
                            $str = "let $\\tilde{\\text{" . toRoman($this->c + 1) . "}} = \\text{" . toRoman($this->c + 1) . "} -" . $this->A[$this->r][$this->c]->toTexWithBrackets() . "\\cdot \\text{" . toRoman($this->r + 1) . "}$";
                    }
                    // go through R
                    for ($i = 0; $i < $this->n - 1; $i++) {
                        $this->R[$i][$this->c] = rminus($this->R[$i][$this->c], rtimes($this->A[$this->r][$this->c], $this->R[$i][$this->r]));
                    }
                    // set to zero(no complicated calculations)
                    $this->A[$this->r][$this->c] = new RationalNumber(0);
                }
            }


            // count down
            $this->r--; // we use this counter here now for the rows, just want to make steps easy!

            // new column?
            if ($this->r < $this->c) {
                $this->r = $this->n - 1;
                $this->c--;
            }

            // negative ?
            if ($this->c == -1) $this->finished = true;
        } else {
            $str = "unknown mode, complete failure!";
        }
        return $str;
    }

    // prints out tex code for the extended matrix
    public function getFormattedTexCode()
    {
        $str = "";
        $str .= "\\left( \\begin{array}{";
        for ($i = 0; $i < $this->n; $i++) $str .= "c";
        $str .= "|c}\n";

        for ($i = 0; $i < $this->n; $i++) {
            for ($j = 0; $j < $this->n; $j++) {
                // reduce first
                $this->A[$j][$i]->reduce();

                $str .= $this->A[$j][$i];
                $str .= " & ";
                if ($j == $this->n - 1) {

                    // print out first entry if != 0
                    if ($this->R[0][$i]->numerator <> 0) // numerator != 0
                        $str .= $this->R[0][$i];

                    // go through all vars introduced for zero rows
                    for ($k = 1; $k < $this->n + 1; $k++) {
                        // reduce first
                        $this->R[$k][$i]->reduce();

                        if ($this->R[$k][$i]->numerator <> 0) // numerator != 0
                            if ($this->R[$k][$i]->isNegative()) // negative? no + sign necessary
                                $str .= $this->R[$k][$i] . $this->varNames[$k - 1];
                            else
                                if (requals($this->R[$k][$i], new RationalNumber(1))) // if one, no coefficient needed
                                    $str .= $this->varNames[$k - 1];
                                else
                                    $str .= "+" . $this->R[$k][$i] . $this->varNames[$k - 1];
                    }

                    // check special case, iff all entries of R equal zero
                    $allzero = true;
                    for ($k = 0; $k < $this->n + 1; $k++) {
                        if (!requals($this->R[$k][$i], new RationalNumber(0))) {
                            $allzero = false;
                            break;
                        }
                    }

                    // if all entries are zero, out a zero for a cleaner look
                    if ($allzero) $str .= new RationalNumber(0);

                    if ($i <> $this->n - 1) $str .= " \\\\\n";
                }
            }
        }

        $str .= "\\end{array} \\right)";
        return $str;
    }

    // returns a latex formatted string describing the affine vector space of the solution
    public function getAffineSpaceTexString()
    {
        $str = "";

        // special case, solution is empty set
        if (!$this->existsSolution())
            return "\\emptyset";

        // go through solution array R
        for ($col = 0; $col < $this->n + 1; $col++) {

            // check if col is zero, iff not add to output
            $columnequalszero = true;
            for ($row = 0; $row < $this->n; $row++) {
                if (!requals($this->R[$col][$row], new RationalNumber(0))) $columnequalszero = false;
            }

            // only add vector to string iff != 0
            if (!$columnequalszero) {

                // add multiplicator for complex product
                if ($col > 0) {
                    // add + sign only iff str != nullstr
                    if (strlen($str) != 0) $str .= " + \\mathbb{R}";
                    else $str .= "\\mathbb{R}";
                }


                $str .= "\\left( \\begin{array}{c}\n";

                for ($row = 0; $row < $this->n; $row++) {
                    $str .= $this->R[$col][$row];

                    if ($row != $this->n) $str .= " \\\\ ";
                }

                $str .= " \\end{array} \\right)";
            }
        }

        // special case, zero vector
        if (strlen($str) == 0) $str .= "0";


        // handle special case R^n
        if ($this->lCurIndex == $this->n + 1) {
            $str .= " = \\mathbb{R}^" . $this->n;
        }

        return $str;
    }
// end class
}

?>