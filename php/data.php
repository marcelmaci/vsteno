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

require_once "import_model.php";
require_once "parser.php";

global $font, $combiner, $shifter;
global $rules, $functions_table;
global $insertion_key;


// main
// main
require_once "vsteno_fullpage_template_top.php";

$text_to_parse = LoadModelFromDatabase("99999_default");


echo "Importiert: <textarea id='Model_as_text' name='Model_as_text' rows='30' cols='230'>" . htmlspecialchars($text_to_parse) . "</textarea><br>";
$test = ImportModelFromText($text_to_parse);

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
$i = 0;
foreach ($rules["$insertion_key"] as $single_rule) {
    $element1 = htmlspecialchars($single_rule[0]);
    $element2 = htmlspecialchars($single_rule[1]);
    if (isset($single_rule[2])) {
        $element3 = htmlspecialchars($single_rule[2]);
        echo "Rules $i: #$element1# => #$element2#, #$element3#<br>";
    } elseif (!isset($single_rule[1])) {
        echo "Rules $i: =====> $element1<br>";
    } else echo "Rules $i: #$element1# => #$element2#<br>";
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

$result = MetaParser( $test_word );
echo "test_word: #$test_word# result: #$result#<br><br>";

require_once "vsteno_fullpage_template_bottom.php";

?>