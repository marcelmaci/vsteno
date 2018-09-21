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

// generates a text file from old parser variables and shows it in a textarea-
// field that can be edited and/or written to database
//
// the generated text file can then be imported into the new parser
//
// text file that contains all definitions necessary to define a shorthand
// system (tokens & rules) will be called "system"
//
// Structure for system file:
//
// Keywords: #BeginPART(), #EndPART() - PART can be: "Section" or "SubSection"; 
//           () contains parameters, separated by commas if more than one
//           e.g. #EndSubSection(shortener) or #EndSubSection(=>transcriptor,=std)
//
// Special characters: #    marks keyword
//                     >>   go to that subsection (inconditional branch)
//                     =>   if equal go to that subsection 
//                     !=>  if not equal go to that subsection
//                     =    write actual value to that variable
//                     +    transform to uppercase (e.g. +=std or =+ std)
//                     -    transform to lowercase (e.g. -=std or =- std)
//                     //   comment
//                     /*   begin comment
//                     */   end comment
//
// Variables:          std  standard shorthand form
//                     prt  print shorthand form
//                     act  actual form
//
// Apart from //, /*, */ (that can be used anywhere), special characters and variables 
// can only be used inside ()

// main sections
$tokens_section = "";
$rules_section = "";

// subsections
$base_subsection = "";
$combiner_subsection = "";
$shifter_subsection = "";

// linguistical data
$imported_base = array();
$imported_combiner = array();
$imported_shifter = array();

require_once "vsteno_fullpage_template_top.php";
require_once "export_old_parser.php";
require_once "data.php";

$text_to_parse = GenerateCompleteSystemAsText();

function StripOutComments($text) {
    $output = preg_replace("/\/\*.*?\*\//", "", $text);     // replace /* ... */ comments by empty string
    $output = preg_replace("/\/\/.*?\n/", "\n", $output);     // replace // ... \n comments by \n
    return $output;
}

function StripOutTabsAndNewlines($text) {
    $output = preg_replace("/\t/", "", $text);     // replace /* ... */ comments by empty string
    $output = preg_replace("/\n/", "", $output);     // replace // ... \n comments by \n
    return $output;
}

function GetSection($text, $name) {
        $result = preg_match( "/#BeginSection\($name\)(.*?)#EndSection\($name\)/", $text, $matches);
        if ($result == 1) $output = $matches[1];
        else $output = "";
        return $output;
}

function GetSubSection($text, $name) {
        $result = preg_match( "/#BeginSubSection\($name\)(.*?)#EndSubSection\($name\)/", $text, $matches);
        if ($result == 1) $output = $matches[1];
        else $output = "";
        return $output;
}

/////////////////////////////////////////////////// import token definitions ///////////////////////////////////////
function GetNextTokenDefinitionElementAndShrink() {
    global $shrinking_base_subsection;
    $result = preg_match("/^[ ]*?(.*?)([,}])(.*)/", $shrinking_base_subsection, $matches);
    if ($result == 1) {
        $element = $matches[1];
        if ($matches[2] === "}") $last = true; else $last=false;  // $2 contains either , (= there are more elements) or } (= it is the last element)
        $shrinking_base_subsection = $matches[3];
        return array($element, $last);
    } else return null;
}

function GetNextTokenDefinitionKeyAndShrink() {
    global $shrinking_base_subsection;
    $result = preg_match( "/^[ ]*?\"(.*?)\"[ ]*?=>(.*)/", $shrinking_base_subsection, $matches);
    if ($result == 1) {
        $key = $matches[1]; // corresponds to $1
        $shrinking_base_subsection = $matches[2]; // corresponds to $2
    }
    echo "Key: $key => ";
}

function ImportBase() { 
    global $base_subsection, $imported_base, $shrinking_base_subsection;
    $shrinking_base_subsection = $base_subsection;
    while ($shrinking_base_subsection !== "") {
        GetNextTokenDefinitionKeyAndShrink();
        do {
            list($element, $last) = GetNextTokenDefinitionElementAndShrink();
            if ($element !== null) if ($last !== true) echo "$element,"; else echo "$element";
        } while ($last !== true);
        echo "}<br><br>";
    }
}

///////////////////////////////////////////////// import combiner ////////////////////////////////////////
function GetNextCombinerDefinitionKeyAndShrink() {
    global $shrinking_combiner_subsection;
    $result = preg_match( "/^[ ]*?\"(.*?)\"[ ]*?=>[ ]*?({.*)/", $shrinking_combiner_subsection, $matches);
    if ($result == 1) {
        $key = $matches[1]; // corresponds to $1
        $shrinking_combiner_subsection = $matches[2]; // corresponds to $2
    }
    echo "Key: $key => ";
}

// "D" => { "@R", /*delta*/ 0, 0 }
function GetNextCombinerDefinitionAndShrink() {
    global $shrinking_combiner_subsection;
    $result = preg_match( "/^[ ]*?{[ ]*?\"(.*?)\"[ ]*?,[ ]*?(.*?)[ ]*?,[ ]*?(.*?)[ ]*?}(.*)/", $shrinking_combiner_subsection, $matches);
    if ($result == 1) {
        $shrinking_combiner_subsection = $matches[4];
        //echo "REST: " . $matches[4] . "<br>";
        return array( $matches[1], $matches[2], $matches[3] );
    } else return null;
}

function ImportCombiner() {
    global $combiner_subsection, $imported_combiner, $shrinking_combiner_subsection;
    $shrinking_combiner_subsection = $combiner_subsection;
    while ($shrinking_combiner_subsection !== "") {
        GetNextCombinerDefinitionKeyAndShrink();
        list($second, $delta_x, $delta_y) = GetNextCombinerDefinitionAndShrink();
        echo "{ $second, $delta_x, $delta_y }<br>";
        // echo "REST: $shrinking_combiner_subsection<br>";
    }
}

////////////////////////////////////////////////////// import shifter ///////////////////////////////////////////////////////////////////////////
function GetNextShifterDefinitionKeyAndShrink() {
    global $shrinking_shifter_subsection;
    $result = preg_match( "/^[ ]*?\"(.*?)\"[ ]*?=>[ ]*?({.*)/", $shrinking_shifter_subsection, $matches);
    if ($result == 1) {
        $key = $matches[1]; // corresponds to $1
        $shrinking_shifter_subsection = $matches[2]; // corresponds to $2
    }
    echo "Key: $key => ";
}

function GetNextShifterDefinitionAndShrink() {
    global $shrinking_shifter_subsection;
    $result = preg_match( "/^[ ]*?{[ ]*?\"(.*?)\"[ ]*?,[ ]*?(.*?)[ ]*?,[ ]*?(.*?)[ ]*?,[ ]*?(.*?)[ ]*?,[ ]*?(.*?)[ ]*?}(.*)/", $shrinking_shifter_subsection, $matches);
    if ($result == 1) {
        $shrinking_shifter_subsection = $matches[6];
        //echo "Gesamt-Match: " . $matches[0] . "<br>";
        return array( $matches[1], $matches[2], $matches[3], $matches[4], $matches[5] );
    } else return null;
}

function ImportShifter() {
    global $shifter_subsection, $imported_shifter, $shrinking_shifter_subsection;
    $shrinking_shifter_subsection = $shifter_subsection;
    while ($shrinking_shifter_subsection !== "") {
        GetNextShifterDefinitionKeyAndShrink();
        list($newname, $shift_x, $shift_y, $delta_x, $delta_y) = GetNextShifterDefinitionAndShrink();
        echo "{ $newname, $shift_x, $shift_y, $delta_x, $delta_y }<br>";
        //echo "REST: $shrinking_shifter_subsection<br>";
    }
}

//////////////////////////////////////////// import rules ////////////////////////////////////////////////////////////////////
function GetNextRulesSubSection() {
    global $shrinking_rules_section;
    $result = preg_match( "/^[ ]*?#BeginSubSection\((.*?)\)(.*?)#EndSubSection\((.*?)\)(.*)/", $shrinking_rules_section, $matches );
    if ($result == 1) {
        $shrinking_rules_section = $matches[4];
        return array( $matches[1], $matches[2], $matches[3] );
    } else return null;
}

/*
function GetNextRuleAndShrink() {
    global $shrinking_generic_subsection;
    $result = preg_match( "/^[ ]*?/", $shrinking_generic_subsection, $matches );
    if ($result == 1) {
        $shrinking_rules_section = $matches[4];
        return array( $matches[1], $matches[2], $matches[3] );
    } else return null;
}

function ImportRulesFromSubSection() {
    global $shrinking_generic_subsection;
    $rule = GetNextRuleAndShrink();
}
*/
function ImportRules() {
    global $rules_section, $shrinking_rules_section, $shrinking_generic_subsection;
    $shrinking_rules_section = $rules_section;
    echo "rulessection: $shrinking_rules_section<br>";
    list( $parameters1, $shrinking_generic_subsection, $parameters2) = GetNextRulesSubSection();
    echo "SubSectionParams1: $parameters1<br>";
    echo "SubSectionParams2: $parameters2<br>";
    echo "SubSectionContent: $shrinking_generic_subsection<br>";
    
}

//////////////////////////////////////////// import whole system /////////////////////////////////////////////////////////////
function ImportSystemFromText($text) {
    global $tokens_section, $rules_section, $base_subsection, $combiner_subsection, $shifter_subsection;
    // strip out unnecessary stuff
    $output = StripOutComments($text);
    $output = StripOutTabsAndNewlines($output);
    // get main sections
    $tokens_section = GetSection($output, "tokens");
    $rules_section = GetSection($output, "rules");
    // get subsections
    $base_subsection = GetSubSection($tokens_section, "base");
    $combiner_subsection = GetSubSection($tokens_section, "combiner");
    $shifter_subsection = GetSubSection($tokens_section, "shifter");
    // parse data
    ImportBase();   // data is written to global variable $imported_base (which corresponds to $steno_tokens_master)
    ImportCombiner(); // idem to $imported_combiner
    ImportShifter(); // idem to $imported_shifter
    ImportRules();  // idem to $imported + name of the original table (shortener, bundler, transcriptor etc.)
    
    return $output;
}

// main
echo "Ohne Kommentare: <textarea id='system_as_text' name='system_as_text' rows='30' cols='230'>" . htmlspecialchars($text_to_parse) . "</textarea><br>";
$test = ImportSystemFromText($text_to_parse);

//echo /*"Tokens: $tokens_section<br><br><br>*/"Base: $base_subsection<br><br>Combiner: $combiner_subsection<br><br>Shifter: $shifter_subsection<br><br>";


require_once "vsteno_fullpage_template_bottom.php";
?>