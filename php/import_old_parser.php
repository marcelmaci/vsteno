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
// system (tokens & rules) will be called "model"
//
// Structure for model file:
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
$font_section = "";
$rules_section = "";

// subsections (as text)
$base_subsection = "";
$combiner_subsection = "";
$shifter_subsection = "";

// linguistical data (as parsed data)
$base = array();
$combiner = array();
$shifter = array();
$font = array();            // former $steno_tokens_master
$rules = array();           // use only 1 variable for all rules and parts
$rules_pointer = 0; 
$insertion_key = "";        // key that identifies inserted models (several models can be loaded and used in new parser) 
                            // data will be addressed by: $rules[$key][data] (example for rules: $key identifies array of data)
$actual_key = "";
$functions_table = array();  // values for linguistical functions (start, end)

require_once "vsteno_fullpage_template_top.php";
require_once "dbpw.php";
require_once "data.php";

//////////////////////////////////////// load from database ///////////////////////////////////////////////
function connect_or_die() {
        // Create connection
        $conn = Connect2DB();
        // Check connection
        if ($conn->connect_error) {
            die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
        }
        return $conn;
}

function LoadModelFromDatabase($name) {
    global $conn, $insertion_key, $font, $combiner, $shifter, $rules, $functions_table;
    $conn = connect_or_die();
    $safe_name = $conn->real_escape_string($name);
    $sql = "SELECT * FROM models WHERE name='$safe_name'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc(); 
        $output = $row['header'] . $row['font'] . $row['rules'];
        $insertion_key = $name;
        $font[] = $insertion_key;       // add insertion key to $font
        $combiner[] = $insertion_key;   // idem for $combiner (maybe kind of an overkill: combiner will only be used 1x to create complete font table, but to avoid confusion in case of loading different models create different combiner and shifter variables ...)
        $shifter[] = $insertion_key;    // idem for $shifter
        $rules[] = $insertion_key;      // idem for $rules
        $functions_table[] = $insertion_key; // semper idem
        return $output;
    } else {
        die_more_elegantly("<p>Kein Eintrag in models.</p>");
    }
}

//////////////////////////////////////// import functions /////////////////////////////////////////////////
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
    global $shrinking_base_subsection, $insertion_key, $font, $actual_key;
    $result = preg_match( "/^[ ]*?\"(.*?)\"[ ]*?=>[ ]*?{(.*)/", $shrinking_base_subsection, $matches);
    if ($result == 1) {
        $key = $matches[1]; // corresponds to $1
        $shrinking_base_subsection = $matches[2]; // corresponds to $2
    }
    //echo "Key: $key => ";
    $actual_key = $key;
    $font["$insertion_key"][] = $key;      // add token key to $font (actual model: $font["$insertion_key"])
}

function ImportBase() { 
    global $base_subsection, $imported_base, $shrinking_base_subsection, $font, $insertion_key, $actual_key;
    $shrinking_base_subsection = $base_subsection;
    while ($shrinking_base_subsection !== "") {
        GetNextTokenDefinitionKeyAndShrink();
        do {
            list($element, $last) = GetNextTokenDefinitionElementAndShrink();
            //if ($element !== null) if ($last !== true) echo "$element,"; else echo "$element";
            $font["$insertion_key"]["$actual_key"][] = $element;    // add definition data to actual token (symbolically: font["model"]["token"])
        } while ($last !== true);
        //echo "}<br><br>";
    }
}

///////////////////////////////////////////////// import combiner ////////////////////////////////////////
function GetNextCombinerDefinitionKeyAndShrink() {
    global $shrinking_combiner_subsection, $combiner, $insertion_key, $actual_key;
    $result = preg_match( "/^[ ]*?\"(.*?)\"[ ]*?=>[ ]*?({.*)/", $shrinking_combiner_subsection, $matches);
    if ($result == 1) {
        $key = $matches[1]; // corresponds to $1
        $shrinking_combiner_subsection = $matches[2]; // corresponds to $2
    }
    //echo "Key: $key => ";
    $actual_key = $key;
    $combiner["$insertion_key"][] = $key; 
}

// "D" => { "@R", /*delta*/ 0, 0 }
function GetNextCombinerDefinitionAndShrink() {
    global $shrinking_combiner_subsection, $combiner, $insertion_key, $actual_key;
    $result = preg_match( "/^[ ]*?{[ ]*?\"(.*?)\"[ ]*?,[ ]*?(.*?)[ ]*?,[ ]*?(.*?)[ ]*?}(.*)/", $shrinking_combiner_subsection, $matches);
    if ($result == 1) {
        $shrinking_combiner_subsection = $matches[4];
        //echo "REST: " . $matches[4] . "<br>";
        $combiner["$insertion_key"]["$actual_key"][] = $matches[1]; 
        $combiner["$insertion_key"]["$actual_key"][] = $matches[2];
        $combiner["$insertion_key"]["$actual_key"][] = $matches[3];
        return array( $matches[1], $matches[2], $matches[3] );
    } else return null;
}

function ImportCombiner() {
    global $combiner_subsection, $imported_combiner, $shrinking_combiner_subsection;
    $shrinking_combiner_subsection = $combiner_subsection;
    while ($shrinking_combiner_subsection !== "") {
        GetNextCombinerDefinitionKeyAndShrink();
        list($second, $delta_x, $delta_y) = GetNextCombinerDefinitionAndShrink();
        //echo "{ $second, $delta_x, $delta_y }<br>";
        // echo "REST: $shrinking_combiner_subsection<br>";
    }
}

////////////////////////////////////////////////////// import shifter ///////////////////////////////////////////////////////////////////////////
function GetNextShifterDefinitionKeyAndShrink() {
    global $shrinking_shifter_subsection, $shifter, $insertion_key, $actual_key;
    $result = preg_match( "/^[ ]*?\"(.*?)\"[ ]*?=>[ ]*?({.*)/", $shrinking_shifter_subsection, $matches);
    if ($result == 1) {
        $key = $matches[1]; // corresponds to $1
        $shrinking_shifter_subsection = $matches[2]; // corresponds to $2
    }
    //echo "Key: $key => ";
    $actual_key = $key;
    $shifter["$insertion_key"][] = $key;
}

function GetNextShifterDefinitionAndShrink() {
    global $shrinking_shifter_subsection, $shifter, $insertion_key, $actual_key;
    $result = preg_match( "/^[ ]*?{[ ]*?\"(.*?)\"[ ]*?,[ ]*?(.*?)[ ]*?,[ ]*?(.*?)[ ]*?,[ ]*?(.*?)[ ]*?,[ ]*?(.*?)[ ]*?}(.*)/", $shrinking_shifter_subsection, $matches);
    if ($result == 1) {
        $shrinking_shifter_subsection = $matches[6];
        //echo "Gesamt-Match: " . $matches[0] . "<br>";
        $shifter["$insertion_key"]["$actual_key"][] = $matches[1];     
        $shifter["$insertion_key"]["$actual_key"][] = $matches[2];
        $shifter["$insertion_key"]["$actual_key"][] = $matches[3];
        $shifter["$insertion_key"]["$actual_key"][] = $matches[4];
        $shifter["$insertion_key"]["$actual_key"][] = $matches[5];
        return array( $matches[1], $matches[2], $matches[3], $matches[4], $matches[5] );
    } else return null;
}

function ImportShifter() {
    global $shifter_subsection, $imported_shifter, $shrinking_shifter_subsection;
    $shrinking_shifter_subsection = $shifter_subsection;
    while ($shrinking_shifter_subsection !== "") {
        GetNextShifterDefinitionKeyAndShrink();
        list($newname, $shift_x, $shift_y, $delta_x, $delta_y) = GetNextShifterDefinitionAndShrink();
        //echo "{ $newname, $shift_x, $shift_y, $delta_x, $delta_y }<br>";
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

function ImportRulesFromGenericSubSection() {
    global $shrinking_generic_subsection, $rules, $rules_pointer, $insertion_key;
    //$result = preg_match( "/^[ ]*?\"(.*?)\"[ ]*?=>(.*?)[,;](.*)/", $shrinking_generic_subsection, $matches);
  //$result = preg_match( "/\"(.)\"[ ]*?=>[ ]*?(.*?)[,;](.*)/", $shrinking_generic_subsection, $matches);
  while ($shrinking_generic_section !== "") {
    $result = preg_match( "/[ ]*?\"(.*?)\"[ ]*?=>[ ]*(.*?)[,;](.*)/", $shrinking_generic_subsection, $matches); // use greedy [ ]* after => so that spaces that follow get cut out
    
    //echo "shrinking: $shrinking_generic_subsection result: $result<br>";
    
    if ($result == 1) {
        //echo "#" . $matches[1] . "# => #" . $matches[2] . "#<br>";
        $condition = $matches[1];
        $consequence = $matches[2];
        //echo "consequence: $consequence<br>";
        $shrinking_generic_subsection = $matches[3];
        $result1 = preg_match( "/^{[ ]*?\"(.*)\"[ ]*?}$/", $consequence, $matches1); // use greedy [ ]* after => so that spaces that follow get cut out
        switch ($result1) {
            case "1" : 
                // multiple consequences
                //echo "multiple: #" . $matches1[1] . "#<br>";
                $consequence_list = explode( ",", $matches1[1] );
                foreach ($consequence_list as $element) {
                    $bare_element = preg_replace("/[ ].*?\"(.*?)\"[ ]*?/", "$1", $element);
                    //echo "bare_element: $bare_element<br>";
                    $rules["$insertion_key"][] = $rules_pointer;
                
                    $rules["$insertion_key"][$rules_pointer][] = $bare_element;
                }
                $rules_pointer++;
                break;
            default : 
                // just one consequence
                //echo "single consequence: #" . $consequence . "#<br>";
                $result2 = preg_match( "/^[ ]*?\"(.*?)\"[ ]*?/", $consequence, $matches2);
                //echo "result2: $result2 matches1(1): " . $matches2[1] . " rulespointer: $rules_pointer<br>";
                //$rules["$insertion_key"][] = $rules_pointer;
                $rules["$insertion_key"][] = array( $condition, $matches2[1]);        // rules[model][x][0] = condition of rule x in model
                //$rules["$insertion_key"][$rules_pointer][] = $matches2[1];      // rules[model][x][1] = consequence of rule x in model
                $rules_pointer++;
                break;
        }
    } else return null;
  }
}
/*
function SetValuesBeginFunction( $parameters ) {
    global $functions_table, $rules_pointer, $insertion_key;
    $param_list = explode( ",", $parameters);
    $key = $param_list[0];
    $functions_table["$insertion_key"][] = $key;
    $function_table["$insertion_key"]["$key"][0] = $rules_pointer;  // rule at which function starts
    $function_table["$insertion_key"]["$key"][1] = 99999;           // rule at which function ends
}

function SetValuesEndFunction( $parameters ) {
    
}
*/

function ImportRules() {
    global $rules_section, $shrinking_rules_section, $shrinking_generic_subsection, $rules, $rules_pointer;
    $shrinking_rules_section = $rules_section;
    $rules_pointer = 0;
    while ($shrinking_rules_section !== "") {
        //echo "rulessection: $shrinking_rules_section<br>";
        list( $parameters1, $shrinking_generic_subsection, $parameters2) = GetNextRulesSubSection();
        echo "SubSectionParams1: $parameters1<br>";
        echo "SubSectionParams2: $parameters2<br>";
        //echo "SubSectionContent: #$shrinking_generic_subsection#<br>";
        //SetValuesBeginFunction( $parameters1 );
        ImportRulesFromGenericSubSection();
        //SetValuesEndFunction( $parameters2 );
    }
    /*
    list( $parameters1, $shrinking_generic_subsection, $parameters2) = GetNextRulesSubSection();
    echo "SubSectionParams1: $parameters1<br>";
    echo "SubSectionParams2: $parameters2<br>";
    echo "SubSectionContent: #$shrinking_generic_subsection#<br>";
    //SetValuesBeginFunction( $parameters1 );
    ImportRulesFromGenericSubSection();
    //SetValuesEndFunction( $parameters2 );
*/

}

//////////////////////////////////////////// import whole Model /////////////////////////////////////////////////////////////
function ImportModelFromText($text) {
    global $font_section, $rules_section, $base_subsection, $combiner_subsection, $shifter_subsection;
    // strip out unnecessary stuff
    $output = StripOutComments($text);
    $output = StripOutTabsAndNewlines($output);
    // get main sections
    $font_section = GetSection($output, "font");
    $rules_section = GetSection($output, "rules");
    // get subsections
    $base_subsection = GetSubSection($font_section, "base");
    $combiner_subsection = GetSubSection($font_section, "combiner");
    $shifter_subsection = GetSubSection($font_section, "shifter");
    // parse data
    ImportBase();   // data is written to global variable $imported_base (which corresponds to $steno_tokens_master)
    ImportCombiner(); // idem to $imported_combiner
    ImportShifter(); // idem to $imported_shifter
    ImportRules();  // idem to $imported + name of the original table (shortener, bundler, transcriptor etc.)
    
    return $output;
}

// main

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
    echo "Rules $i: \"$element1\" => \"$element2\"<br>";
    $i++;
}

//$element1 = htmlspecialchars($rules["$insertion_key"][$rule_number][0]);
//$element2 = htmlspecialchars($rules["$insertion_key"][$rule_number][1]);

echo "Rules 0: \"$element1\" => \"$element2\"<br>";

//echo var_dump($combiner);

//echo /*"Tokens: $font_section<br><br><br>*/"Base: $base_subsection<br><br>Combiner: $combiner_subsection<br><br>Shifter: $shifter_subsection<br><br>";


require_once "vsteno_fullpage_template_bottom.php";
?>