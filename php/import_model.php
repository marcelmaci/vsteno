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
//                     !>   if not equal go to that subsection
//                     =:   write actual value to that variable (e.g. =:std)
//                     @@   connect to dictionary (e.g. @@dic)
//                          or connect to or get source (e.g. @@wrd, @@tag)
//                     +    transform variable to uppercase (e.g. +std)
//                     -    transform variable to lowercase (e.g. -std)
//                     //   comment
//                     /*   begin comment
//                     */   end comment
//
// 3-Letter-Keywords:  std  standard shorthand form
//                     prt  print shorthand form
//                     act  actual form
//                     dic  dictionary
//                     tag  complete text with tags
//                     txt  text without tags
//                     wrd (default)
//
// 3-Letter-Keywords are used for variables (std, prt, act), sources (dic, tag, txt, word). 
// Variables are read/write. Sources are read-only.
//
// @@ can be placed at beginning or at the end of a function. In both cases it means
// that the dictionary will be consulted an that the execution of the rules either:
// (1) stops completely (if prt-form is found)
// (2) starts at function with "=:prt" at beginning (if only std-form is found)
//
// - in BeginFunction(): get value from source (e.g. @@dic = load result from dictionary)
// - in EndFunction(): send value to source (e.g. @@dic = send word to dictionary)
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
$actual_function = "";
$start_word_parser = 0;     // contains rules pointer to first rule that has to be applied to a word only (before: global parser that has to be applied to whole text)

//require_once "vsteno_fullpage_template_top.php";
require_once "dbpw.php";
//require_once "data.php";

//////////////////////////////////////// load from database ///////////////////////////////////////////////

function LoadModelFromDatabase($name) {
    global $conn, $insertion_key, $font, $combiner, $shifter, $rules, $functions_table;
    $conn = connect_or_die();
    $safe_name = $conn->real_escape_string($name);
    $sql = "SELECT * FROM models WHERE name='$safe_name'";
    //echo "QUERY: $sql<br>";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc(); 
        $output = $row['header'] . $row['font'] . $row['rules'];
        $insertion_key = $name;
        //$font[] = $insertion_key;       // add insertion key to $font
        //$combiner[] = $insertion_key;   // idem for $combiner (maybe kind of an overkill: combiner will only be used 1x to create complete font table, but to avoid confusion in case of loading different models create different combiner and shifter variables ...)
       // $shifter[] = $insertion_key;    // idem for $shifter
        $rules[] = $insertion_key;      // idem for $rules
        $functions_table[] = $insertion_key; // semper idem
        //echo "output = $output<br>";
        return $output;
    } else {
        die_more_elegantly("<p>Kein Eintrag in models.</p>");
        return null;
    }
}

//////////////////////////////////////// import functions /////////////////////////////////////////////////
function StripOutComments($text) {
    $output = preg_replace("/\/\*.*?\*\//", "", $text);     // replace /* ... */ comments by empty string
    $output = preg_replace("/\/\/.*?\n/", "[\n\r]", $output);     // replace // ... \n comments by \n
    return $output;
}

function StripOutTabsAndNewlines($text) {
    $output = preg_replace("/\t/", "", $text);     // replace /* ... */ comments by empty string
    $output = preg_replace("/\n/", "", $output);     // replace // ... \n comments by \n
    $output = preg_replace("/\r/", "", $output); 
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
   // $font[$insertion_key][] = $key;      // add token key to $font (actual model: $font["$insertion_key"])
}

function StripOutSpaces( $text ) {
    $output = preg_replace( "/ /", "", $text);
    return $output;
}

function StripOutQuotesAndCast( $element ) {
    $stripped = preg_replace( "/[\"\']/", "", $element );
    // automatic type cast is no 100% accurate: some elements that should be float appear as int
    // (because they have no fraction part). This should be no problem: the variable will be 
    // automatically casted to float later in the calculation.
    if ($stripped !== $element) return (string)$stripped;
    else {
            if ((int)$element == $element) return (int)$element;
            elseif ((float)$element == $element) return (float)$element;
            else return $element;
    }
}

function PurifyData( $data ) {
    return StripOutQuotesAndCast( StripOutSpaces( $data ));
}

function ImportBase() { 
    global $base_subsection, $imported_base, $shrinking_base_subsection, $font, $insertion_key, $actual_key;
    $shrinking_base_subsection = $base_subsection;
    while ($shrinking_base_subsection !== "") {
        GetNextTokenDefinitionKeyAndShrink();
        do {
            list($element, $last) = GetNextTokenDefinitionElementAndShrink();
            //if ($element !== null) if ($last !== true) echo "$element,"; else echo "$element";
            $font[$insertion_key][$actual_key][] = StripOutQuotesAndCast( StripOutSpaces($element));    // add definition data to actual token (symbolically: font["model"]["token"])
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
    //$combiner[$insertion_key][$actual_key][] = $key; 
}

// "D" => { "@R", /*delta*/ 0, 0 }
function GetNextCombinerDefinitionAndShrink() {
    global $shrinking_combiner_subsection, $combiner, $insertion_key, $actual_key;
    $result = preg_match( "/^[ ]*?{[ ]*?\"(.*?)\"[ ]*?,[ ]*?(.*?)[ ]*?,[ ]*?(.*?)[ ]*?}(.*)/", $shrinking_combiner_subsection, $matches);
    if ($result == 1) {
        $shrinking_combiner_subsection = $matches[4];
        //echo "REST: " . $matches[4] . "<br>";
        $combiner[$insertion_key][] = array( $actual_key, $matches[1], PurifyData($matches[2]), PurifyData($matches[3]) ); 
        
        //$combiner[$insertion_key][$actual_key][] = $matches[1]; 
        //$combiner[$insertion_key][$actual_key][] = $matches[2];
        //$combiner[$insertion_key][$actual_key][] = $matches[3];
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
    //$shifter["$insertion_key"][] = $key;
}

function GetNextShifterDefinitionAndShrink() {
    global $shrinking_shifter_subsection, $shifter, $insertion_key, $actual_key;
    $result = preg_match( "/^[ ]*?{[ ]*?\"(.*?)\"[ ]*?,[ ]*?(.*?)[ ]*?,[ ]*?(.*?)[ ]*?,[ ]*?(.*?)[ ]*?,[ ]*?(.*?)[ ]*?}(.*)/", $shrinking_shifter_subsection, $matches);
    if ($result == 1) {
        $shrinking_shifter_subsection = $matches[6];
        //echo "Gesamt-Match: " . $matches[0] . "<br>";
        $shifter[$insertion_key][] = array ( $actual_key, $matches[1], PurifyData($matches[2]), PurifyData($matches[3]), PurifyData($matches[4]), PurifyData($matches[5]) );     
        
        //$shifter[$insertion_key][] = $actual_key;     
        //$shifter[$insertion_key][] = $matches[1];     
        //$shifter[$insertion_key][] = $matches[2];
        //$shifter[$insertion_key][] = $matches[3];
        //$shifter[$insertion_key][] = $matches[4];
        //$shifter[$insertion_key][] = $matches[5];
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
    global $shrinking_rules_section, $global_rules_pointer;
    //echo "TEST: $shrinking_rules_section<br>";
    // there's a bug in the following regex: no subsections found if model is modified ... no idea ... 
    $result = preg_match( "/^[ ]*?#BeginSubSection\((.*?)\)(.*?)#EndSubSection\((.*?)\)(.*)/", $shrinking_rules_section, $matches );
    //echo "result: #$result#<br>";
    if ($result == 1) {
        $shrinking_rules_section = $matches[4];
        //if ($rules_pointer < 20) echo "matches[2] = " . $matches[2] . "<br>";
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
    //$result = preg_match( "/[ ]*?\"(.*?)\"[ ]*?=>[ ]*(.*?[\"}]);(.*)/", $shrinking_generic_subsection, $matches); // use greedy [ ]* after => so that spaces that follow get cut out
    //$result = preg_match( "/[ ]*?\"(.*?)\"[ ]*?=>(([ ]*?{.*?}[ ]*?;)|([ ]*?\".*?\"[ ]*?;))/", $shrinking_generic_subsection, $matches); // use greedy [ ]* after => so that spaces that follow get cut out
    $result = preg_match( "/[ ]*?\"(.*?)\"[ ]*?=>[ ]*?({?[ ]*?\".*?\"[ ]*?}?)[ ]*?;(.*)/", $shrinking_generic_subsection, $matches);
    
    //echo "shrinking: $shrinking_generic_subsection result: $result<br>";
    
    if ($result == 1) {
        //echo "#" . $matches[1] . "# => #" . $matches[2] . "#<br>";
        $condition = $matches[1];
        $consequence = $matches[2];
        //echo "condition => consequence: $condition => $consequence<br>";
        $shrinking_generic_subsection = $matches[3];
        $result1 = preg_match( "/^{[ ]*?(\".*\")[ ]*?}$/", $consequence, $matches1); 
        //echo "rule $rules_pointer: $condition => $consequence<br>";
        switch ($result1) {
            //$nil = preg_match( "/^{(.*)}$/", $consequence, $matches1); // $nil should always be true ... ! ;-) 
            case "1" : 
                // multiple consequences
                $rules["$insertion_key"][$rules_pointer][] = $condition;
                //echo "multiple: #" . $matches1[1] . "#<br>";
                $consequence_list = explode( ",", $matches1[1] );
                foreach ($consequence_list as $element) {
                    $bare_element = preg_replace("/^[ ]*?\"(.*?)\"[ ]*?/", "$1", $element);
                    //echo "element: #$element# => bare_element: #$bare_element#<br>";
                    //$rules["$insertion_key"][] = $rules_pointer;
                
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

function WriteParamListToRulesArray( $type, $param_list ) {
    global $rules, $insertion_key, $rules_pointer, $rules_pointer_start_std2prt;
    $rules["$insertion_key"][$rules_pointer][] = $type;
    foreach( $param_list as $parameter ) {
        if ($parameter === "=:std") {
            //echo "End of WRD=>STD detected: rule number: $rules_pointer<br>";
            //echo "rule($rules_pointer): " . $rules["$insertion_key"][$rules_pointer][0];
            //echo "set rules_pointer_start_std2prt";
            $rules_pointer_start_std2prt = $rules_pointer + 1;  // set it to begin of following function
        }
    
        $rules["$insertion_key"][$rules_pointer][] = $parameter;
    }
    $rules_pointer++;
}

function WriteEntryPointToFunctionTable ($param_list) {
    global $functions_table, $rules_pointer;
    $key = $param_list[0];
    /*
    echo "param_list: $param_list rules_pointer: $rules_pointer<br>";
    var_dump($param_list);
    echo "functions_table: ";
    var_dump($functions_table);
    */
    foreach( $param_list as $parameter ) {
        switch ($parameter) {
            case "=:std" : $functions_table["$insertion_key"]["$key"][] = "=:std"; break;
        }
    }
}

function SetValuesBeginFunction( $parameters ) {
    global $functions_table, $rules_pointer, $insertion_key, $actual_function;
    $param_list = explode( ",", $parameters);
    // write values to functions table
    $key = $param_list[0];
    $actual_function = $key;
    $functions_table["$insertion_key"][] = $key;
    $functions_table["$insertion_key"]["$key"][] = $rules_pointer;   // rule at which function starts (number)
    // write instructions that have to be executed at beginning of function to rules[]
    WriteParamListToRulesArray( "BeginFunction()", $param_list );
    WriteEntryPointToFunctionTable( $param_list );
    //echo "Function=$actual_function Start: $rules_pointer<br>";
}

function SetValuesEndFunction( $parameters ) {
    global $functions_table, $rules_pointer, $insertion_key, $actual_function, $rules;
    $param_list = explode( ",", $parameters);
    $key = $param_list[0];    
    WriteParamListToRulesArray( "EndFunction()", $param_list );
    $functions_table["$insertion_key"]["$key"][] = $rules_pointer - 1;
    
    /*
    foreach ($param_list as $element) {
        
        $result = preg_match("/^[ ]*?>>(.*?)[ ]*?$/", $element, $matches);
        if ($result == 1) { $branch_if_equal = $matches[1]; $branch_if_not_equal = $matches[1]; }    
        $result = preg_match("/^[ ]*?=>(.*?)[ ]*?$/", $element, $matches);
        if ($result == 1) $branch_if_equal = $matches[1];     
        $result = preg_match("/^[ ]*?!>(.*?)[ ]*?$/", $element, $matches);
        if ($result == 1) $branch_if_not_equal = $matches[1];
        $result = preg_match("/^[ ]*=([^>].*?)[ ]*?$/", $element, $matches);
        if ($result == 1) $save_to = $matches[1];
        $result = preg_match("/^[ ]*+(.*?)[ ]*?$/", $element, $matches);
        if ($result == 1) $transform = "upper";
        $result = preg_match("/^[ ]*-(.*?)[ ]*?$/", $element, $matches);
        if ($result == 1) $transform = "lower";
        //$result = preg_match("/^[ ]*([^>^=^-^+^!]*?)[ ]*?$/", $element, $matches);
        //if ($result == 1) $transform = "lower";
        
        $function_start = $functions_table["$insertion_key"]["$key"][0];
        $function_end = $rules_pointer;
        echo "ParamList: $actual_function($function_start,$function_end,$branch_if_equal,$branch_if_not_equal,$save_to,$transform)<br>";
        $functions_table["$insertion_key"]["$key"][] = $function_end;
        $functions_table["$insertion_key"]["$key"][] = $branch_if_equal;
        $functions_table["$insertion_key"]["$key"][] = $branch_if_not_equal;
        $functions_table["$insertion_key"]["$key"][] = $save_to;
        $functions_table["$insertion_key"]["$key"][] = $transform;
        
    }
    */
}

function ImportRules() {
    global $rules_section, $shrinking_rules_section, $shrinking_generic_subsection, $rules, $rules_pointer;
    $shrinking_rules_section = $rules_section;
    $rules_pointer = 0;
    //echo "rules_section: " . htmlspecialchars($rules_section) . "<br>";
    while /*($rules_pointer < 5) { */ ($shrinking_rules_section !== "") {
        //echo "rulessection: $shrinking_rules_section<br>";
        list( $parameters1, $shrinking_generic_subsection, $parameters2) = GetNextRulesSubSection();
        //echo "SubSectionParams1: $parameters1<br>";
        //echo "SubSectionParams2: $parameters2<br>";
        // if ($rules_pointer < 5) echo "SubSectionContent: #$shrinking_generic_subsection#<br>";
        SetValuesBeginFunction( $parameters1 );
        ImportRulesFromGenericSubSection();
        SetValuesEndFunction( $parameters2 );
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


/*
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

//$element1 = htmlspecialchars($rules["$insertion_key"][$rule_number][0]);
//$element2 = htmlspecialchars($rules["$insertion_key"][$rule_number][1]);

//echo var_dump($combiner);


require_once "vsteno_fullpage_template_bottom.php";
*/
?>