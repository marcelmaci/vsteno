<?php
/* VSTENO - Vector Steno Tool with Enhanced Notational Options
 * (c) 2018-2019 - Marcel Maci (m.maci@gmx.ch)
 
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
 
 
/* This file contains functions to show diffs comparing fonts from various models. 
 * Only font-section is compared (i.e. no session variables, no spacer - must
 * be done manually)
 *
 * array fonts_list[] contains all fonts to be compared (first font = base font
 * to which all comparisons are made).
 *
 * this is only a helper tool which won't be officially integrated into the main
 * program (= must be called directly by typing address manually into browser bar)
 *
 */

require_once "session.php";
require_once "import_model.php";
require_once "engine.php";
require_once "parser.php";
require_once "dbpw.php";
require_once "import_model.php";

// functions
function definitions_are_equal($d1, $d2) {
    // return true or $d2 with all differing elements (equal elements are set to "-") 
    $len1 = count($d1); 
    $len2 = count($d2);
    $min = min($len1, $len2);
    $result = true;
    for ($i=0; $i<$min; $i++) {
        $c = $d1[$i] == $d2[$i];
        //echo "compare[$i]: [" . $d1[$i] . "] <=?=> [" . $d2[$i] . "]  => [" . $c . "]<br>";
        if ($c) $d2[$i] = "-";
        else $result = false;
    }
    if ($len1 != $len2) $result = false;
    $ret = ($result === true) ? true : $d2;
    return $ret;
}

function get_element_as_string($element) {
    if (is_string($element)) 
        if ($element !== "-") $element = "\"$element\"";
    return $element;
}

// fonts
$font_list = array("DESSBAS", "SPSSBAS", "FRSSBAS", "ENSSBAS");
$base_font = $font_list[0];
$font_data = array();
    
// load models and store them into array
$font = null; // somehow DESSBAS gets loaded twice (= once before that) => clear it to be sure that it's only added 1x to font
foreach ($font_list as $font_name) {
    global $font; // contains after loading
    $t = LoadModelFromDatabase($font_name);
    $e = ImportModelFromText($t);
}    

// create diff font
$font_data = $font;
$font_data["DIFF"];
foreach($font_list as $font_name) {
    foreach($font_data[$font_name] as $token => $definition) {
        //echo "FONT: $font_name TOKEN: $token => DEFINITION: $definition<br>";
        // add token if not yet present in DIFF or add it another time if definition is different
        if (isset($font_data["DIFF"][$token])) {
            //echo "$font_name: TOKEN $token is in font_data => compare definition<br>";
            //if ($font_data["DIFF"][$token] !== $definition) {
            $comparison = definitions_are_equal($font_data["DIFF"][$token], $definition);
            if ($comparison !== true) {
                //echo "$font_name: definition in diff not equal to definition in $font_name => add it<br>";
                //var_dump($comparison); echo "<br>";
                $font_data["DIFF"][$font_name . "_" . $token] = $comparison;
            } else {
                //echo "$font_name: definitions are equal => don't add it<br>";
            }
        } else {
            $length = count($definition); 
            //echo "$font_name: TOKEN $token not in font_data => add it (including definition, length $length)<br>";
            $font_data["DIFF"][$token] =  $definition;
        }
    }
}

// show final result
foreach ($font_data["DIFF"] as $token => $definition) {
    echo "\"$token\" => { ";
    $i = 0;
    $len = count($definition);
    foreach ($definition as $element) {
        $separator = "";
        if ($i == 0) $separator = "/*header*/ ";
        elseif ($i == 24) $separator = "/*data*/ ";
        elseif (($i<24) && ($i%8 == 0)) $separator = "/**/ ";
        elseif (($i>24) && ($i%8) == 0) $separator = "/*" . ($i-24)/8 . "*/ ";
        $coma = ($i<$len-1) ? "," : "";
        //echo "i: $i separator: $separator<br>";
        
        $es = get_element_as_string($element); 
        //$separator = "";
        echo $separator . $es . $coma . " ";
        $i++;
    }
    echo "}<br>";
}

?>