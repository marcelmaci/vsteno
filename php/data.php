<?php

/* VSTENO - Vector Steno Tool with Enhanced Notational Options
 * (c) 2018 - Marcel Maci (m.maci@gmx.ch)
 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 */
 
 
/*
14. August 2018: New rule formalism

Either:    A => B
Or:        A => array(a, b, c, d ...)
where:     a is the original B (i.e. replacement for REGEX)
           b, c, d ... are exceptions: if one of the matches, the rule won't be applied
           
           can be used for example for word "geschäft": 
           define a rule which replaces "schäft" => "{SCHAFT}"
           define exception "geschäft" (rule shouldn't be applied to that word)

           in PHP:
           "schaft$ => array( "{SCHAFT}", "Geschäft(en?)?" )

           Advantages:
           (1) exceptions can be indicated together with rules (more logical and better to understand)
           (2) possible to use REGEX also for exceptions (big plus)
           
           Performance-whise this should also be beneficial since exceptions are only tested if first part of rule matches
           (in the old version, every exception was tested on every word).
*/
 
// 25. September 2018: read data from database

require_once "session.php";

if ($_SESSION['user_logged_in']) {
    //echo "<p>POST(model): " . $_POST['model'] . "</p>";  // why is $_POST['model'] === "" in mini.php??? => ok, in input.php it's a post variable, in mini.php it's not ... should be taken from the session variable!!!  $_SESSION['model_standard_or_custom']
    // fix that: $_SESSION['model_standard_or_custom'] 
    // now the name is correct, but nothing works ...

    if (isset($_POST['model'])) {    // if POST-variable is set via maxi.php, this selection has priority (i.e. disregard SESSION-variable)
        switch ($_POST['model']) {
            case "custom" : $_SESSION['actual_model'] = getDBUserModelName(); break;
            default : //$_SESSION['actual_model'] = $default_model; 
                $_SESSION['actual_model'] = $_POST['std_model_name'];
                break;
        } 
    } else {    
        switch ($_SESSION['model_standard_or_custom']) {
            case "custom" : $_SESSION['actual_model'] = GetDBUserModelName(); break;
            default : $_SESSION['actual_model'] = $_SESSION['selected_std_model']; break;
        }
    }

//echo "Model = " . $_SESSION['actual_model'] . "<br>";
}

require_once "import_model.php";
require_once "engine.php";
require_once "parser.php";

global $font, $combiner, $shifter;
global $rules, $functions_table;
global $insertion_key;
global $global_error_string;
$global_error_string = "";
//require_once "old_font.php";

// main

/*
require_once "vsteno_fullpage_template_top.php";
*/
/*
switch ($_SESSION['model_standard_or_custom']) {
        case "standard" : $model_name = $_SESSION['actual_model']; break; 
        case "custom" : $model_name = "XM" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT); break;
}
*/


// DO NOT ECHO DEBUG INFORMATION HERE => THATS BEFORE HTML HEAD!!!!!!!!!!
//echo "<p>Model to load: " . $_SESSION['actual_model'] . "</p>";
// old version!!!!!
 $text_to_parse = LoadModelFromDatabase($_SESSION['actual_model']);
//$text_to_parse = LoadModelFromDatabase($model_name);
//echo "<p>$text_to_parse</p>";


//echo "text: $text_to_parse<br><br>";
/*

echo "Importiert: <textarea id='Model_as_text' name='Model_as_text' rows='30' cols='230'>" . htmlspecialchars($text_to_parse) . "</textarea><br>";
*/
$test = ImportModelFromText($text_to_parse);
$actual_model = $_SESSION['actual_model'];


//echo "Imported:  actual_model = $actual_model font[actual_model][Z][4] = " . $font[$actual_model]["Z"][4] . "<br>";


// connect old variables
// note: this is the easy (or should i say "quick and dirty";-) method to reuse old parser functions with new data
// it works as long as you reassign the new data to the old variables whenever (each time!) the model changes!
// there's still a bug: exported array has 170 elements, imported one 161 => why?!?
global $steno_tokens_master, $combiner_table, $shifter_table, $steno_tokens_type; // $steno_tokens_type: table to mark tokens created by shifter/combiner
//echo "actual_model: $actual_model<br>";
$steno_tokens_master = $font[$actual_model];
$combiner_table = $combiner[$actual_model];
$shifter_table = $shifter[$actual_model];

CreateCombinedTokens();
CreateShiftedTokens();
          

//var_dump($steno_tokens_master);
//echo "--------------------------------------------------------------";
//var_dump($steno_tokens_master);
//var_dump($font);
//var_dump($shifter_table1);
//echo "<br><br><br>";
//var_dump($shifter_table);

/*
//echo var_dump($font);
$entry = "WAS";
$element1 = $font["$insertion_key"]["$entry"][0];
$element2 = $font["$insertion_key"]["$entry"][1];
$element3 = $font["$insertion_key"]["$entry"][2];
$element4 = $font["$insertion_key"]["$entry"][3];
$element5 = $font["$insertion_key"]["$entry"][4];
$element6 = $font["$insertion_key"]["$entry"][5];

echo "Base $entry: { $element1, $element2, $element3, $element4, $element5, $element6, ... }<br>";

$entry = "VR";
$element1 = $combiner["$insertion_key"]["$entry"][0];
$element2 = $combiner["$insertion_key"]["$entry"][1];
$element3 = $combiner["$insertion_key"]["$entry"][2];

echo "Combiner $entry: { $element1, $element2, $element3 }<br>";

$entry = "NG";
$element1 = $shifter["$insertion_key"]["$entry"][0];
$element2 = $shifter["$insertion_key"]["$entry"][1];
$element3 = $shifter["$insertion_key"]["$entry"][2];
$element4 = $shifter["$insertion_key"]["$entry"][3];
$element5 = $shifter["$insertion_key"]["$entry"][4];

echo "Shifter $entry: { $element1, $element2, $element3, $element4, $element5 }<br>";


//echo var_dump($rules);
*/
/*
$i = 0;
foreach ($rules["$insertion_key"] as $single_rule) {
    $element1 = htmlspecialchars($single_rule[0]);
    $element2 = htmlspecialchars($single_rule[1]);
    switch ($element1) {
        case "BeginFunction()" : 
            echo "Rules $i: BeginFunction(";
            $length = count($single_rule);
            for ($n=1; $n<$length-1; $n++) {
                echo $single_rule[$n] . ",";
            }
            echo $single_rule[$length-1] . ")<br>";
            break;
        case "EndFunction()" : 
            echo "Rules $i: EndFunction(";
            $length = count($single_rule);
            for ($n=1; $n<$length-1; $n++) {
                echo $single_rule[$n] . ",";
            }
            echo $single_rule[$length-1] . ")<br>";
            break;
        default :
            if (isset($single_rule[2])) {
                $element3 = htmlspecialchars($single_rule[2]);
                echo "Rules $i: #$element1# => #$element2#, #$element3#<br>";
            } elseif (!isset($single_rule[1])) {
                echo "Rules $i: =====> $element1<br>";
            } else echo "Rules $i: #$element1# => #$element2#<br>";
    }
    $i++;
}

echo "<br><br>FUNCTIONS:<br><br>";
foreach ($functions_table["$insertion_key"] as $function => $values) {
    $start = $values[0];
    $end = $values[1];
    $bre = $values[2];
    $brne = $values[3];
    $store = $values[4];
    $trans = $values[5];
    
    echo "$function($start,$end,$bre,$brne,$store,$trans)<br>";
}
echo "<br><br>";

//$element1 = htmlspecialchars($rules["$insertion_key"][$rule_number][0]);
//$element2 = htmlspecialchars($rules["$insertion_key"][$rule_number][1]);

// test parser
$test_word = "beßer";
//$test_word = "jedem";
$test_word = "Beizeit";
$test_word = "Spendenaufruf";

$result = MetaParser( $test_word );
echo "test_word: #$test_word# result: #$result#<br><br>";

//require_once "vsteno_fullpage_template_bottom.php";
*/

?>