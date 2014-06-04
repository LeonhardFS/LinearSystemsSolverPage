<?php
    $text = null;

    function getLang() {
        global $lang;
        if(isset($lang))return $lang;
        else return 'en'; // english as default
    }

    function setLang($language) {
        global $lang;
        global $text;
        $lang = $language;
        $text = array();
        if(0 == strcmp(getLang(), 'de')) {
            $text['bartitle'] = 'Gaußlöser für lineare Gleichungssysteme Ax = b';
            $text['title'] = 'Gauß-Löser';
            $text['subtitle'] = 'zur Lösung eines linearen Gleichungssystems $Ax = b$';
            $text['submit'] = 'Lösen';
            $text['description'] = 'Um das lineare Gleichungssystem $Ax=b$ mit $A \in \mathbb{R}^{n \times n}$, $x,b \in \mathbb{R}^n$ zu lösen, gib
        einfach die Zahlen oder einen mathematischen Term (z.B. 2^2 + 3/7), der keine Variablen beinhaltet, in die
        enstprechenden Felder ein. ';
            $text['solution'] = 'Lösung';
            $text['correct_input'] = 'Bitte korrigiere oben erst deine Eingaben.';
            $text['error'] = 'Fehler';
            $text['get_result'] = 'Ergebnis aus Matrix ablesen';
        }
        else {
            $text['bartitle'] = 'Gaussian solver for linear equation systems Ax = b';
            $text['title'] = 'Gaussian Solver';
            $text['subtitle'] = 'to solve a linear equation system $Ax = b$';
            $text['submit'] = 'solve';
            $text['description'] = 'In order to solve a linear equation system $Ax = b$ with $A \in \mathbb{R}^{n \times n}$, $x,b \in \mathbb{R}^n$,
             insert numbers or mathematical expressions (i.e. 2^2 + 3/7) without any variables into the corresponding fields.';
            $text['solution'] = 'solution';
            $text['correct_input'] = 'please correct your input';
            $text['error'] = 'error';
            $text['get_result'] = 'take result from matrix';
        }
    }

    function langDE() {
        return 0 == strcmp(getLang(), 'de');
    }
    function langEN() {
        return 0 == strcmp(getLang(), 'en');
    }

    // echos the entry of the text array
    function tt($key) {
        global $text;
        echo $text[$key];
    }

    // return string entry
    function tt_s($key) {
        global $text;
        return $text[$key];
    }


?>