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
//                     @@   connect to dictionary (e.g. @@dic) => obsolete! replaced by stages
//                          or connect to or get source (e.g. @@wrd, @@tag) => obsolete! replaced by stages
//                     +    transform variable to uppercase (e.g. +std)
//                     -    transform variable to lowercase (e.g. -std)
//                     //   comment
//                     /*   begin comment
//                     */   end comment
//
// 3-Letter-Keywords:  std  standard shorthand form
//                     prt  print shorthand form
//                     (lng  linguistical form => new (hardcoded =:LNG) afert linguistical analyzer)
//                     act  actual form => ??? (unused until now)
//                     dic  dictionary => obsolete! replaced by stages
//                     tag  complete text with tags => obsolete! replaced by stages
//                     txt  text without tags => obsolete! replaced by stages
//                     wrd (default) => obsolete! replaced by stages
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
$analyzer_subsection = "";
$session_subsection = "";
$base_subsection = "";
$combiner_subsection = "";
$shifter_subsection = "";

// linguistical data (as parsed data)
$base = array();
$combiner = array();
$shifter = array();
$font = array();            // former $steno_tokens_master
$rules = array(); $rules_options = array();           // use only 1 variable for all rules and parts
$analyzer = array(); $analyzer_options = array();
$rules_pointer = 0; 
$insertion_key = "";        // key that identifies inserted models (several models can be loaded and used in new parser) 
                            // data will be addressed by: $rules[$key][data] (example for rules: $key identifies array of data)
$actual_key = "";
$functions_table = array();  // values for linguistical functions (start, end)
$actual_function = "";
$start_word_parser = 0;     // contains rules pointer to first rule that has to be applied to a word only (before: global parser that has to be applied to whole text)

//require_once "vsteno_fullpage_template_top.php";
require_once "constants.php";
require_once "dbpw.php";
require_once "options.php";  // for whitelist (session variables)
//require_once "regex_helper_functions.php";
//require_once "data.php";

//////////////////////////////////////// load from database ///////////////////////////////////////////////
// special LoadModel function database
function LoadModelToShareFromDatabase($name) {
    // same as LoadModelFromDatabase but without setting global variables
    global $conn, $insertion_key, $font, $combiner, $shifter, $rules, $functions_table;
    $conn = connect_or_die();
    $safe_name = $conn->real_escape_string($name);
    $sql = "SELECT * FROM models WHERE name='$safe_name'";
    //echo "QUERY: $sql<br>";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc(); 
        $output = $row['header'] . "\n" . $row['font'] . "\n" . $row['rules'];
        //$insertion_key = $name;
        //$font[] = $insertion_key;       // add insertion key to $font
        //$combiner[] = $insertion_key;   // idem for $combiner (maybe kind of an overkill: combiner will only be used 1x to create complete font table, but to avoid confusion in case of loading different models create different combiner and shifter variables ...)
       // $shifter[] = $insertion_key;    // idem for $shifter
        //$rules[] = $insertion_key;      // idem for $rules
        //$functions_table[] = $insertion_key; // semper idem
        //echo "output = $output<br>";
        return $output;
    } else {
        die_more_elegantly("<p>Kein Eintrag in models.</p>");
        return null;
    }
}
// special LoadModel function file
function LoadModelToShareFromFile($name) {
    // same as LoadModelFromDatabase but without setting global variables
    global $conn, $insertion_key, $font, $combiner, $shifter, $rules, $functions_table;
    $complete_filename = "../ling/$name.txt";
    //echo "$complete_filename<br>";
    $myfile = fopen("$complete_filename", "r"); // font must be in ling directory
    if ($myfile === false) {
        die("Unable to open file!");
        return null;
    }
    $output = fread($myfile,filesize("$complete_filename"));
    //echo "fread: $output<br>";
    fclose($myfile);
    return $output;
}

function LoadModelFromDatabase($name) {
    global $conn, $insertion_key, $font, $combiner, $shifter, $rules, $functions_table;
    $conn = connect_or_die();
    $safe_name = $conn->real_escape_string($name);
    $sql = "SELECT * FROM models WHERE name='$safe_name'";
    //echo "QUERY: $sql<br>";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $output = $row['header'] . "\n" . $row['font'] . "\n" . $row['rules'];
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

function WriteTextToFile($file, $text) {
    $out = fopen("$file", "w") or die("<p>Unable to open file $file.</p>");
    fwrite($out, $text);
}

//////////////////////////////////////// import functions /////////////////////////////////////////////////
function StripOutComments($text) {
    $output = preg_replace("/\/\*.*?\*\//", "", $text);     // replace /* ... */ comments by empty string
    //$output = preg_replace("/\/\/.*?\n/", "[\n\r]", $output);     // replace // ... \n comments by \n
     $output = preg_replace("/\/\/.*?(?=\n)/", "", $output);     // replace // ... \n comments empty string followed by \n (careful with that modification ...
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

/////////////////////////////////////////////////// import header //////////////////////////////////////////////////
function ResetRestrictedSessionVariables() {
        global $restricted_session_variables_list;
        // restricted session variables are variables that are not accessible via the input form
        // the only way to set them is via inline-option tags
        // they are typically used for data that is specific for the model like affix and stems
        // definitions for linguistical anlyzer and license and release information
        // make sure these variables get reseted everytime a new model is loaded
        //$_SESSION['language_hunspell'] = ""; // these can be modified via form => leave it up to the user if he/she set and/or deletes (cleans) the variable
        //$_SESSION['language_hyphenator'] = "";
        // $_SESSION['hyphenate_yesno'] = true;  // same goes for these two variables
        // $_SESSION['composed_words_yesno'] = true;
        foreach ($restricted_session_variables_list as $variable => $value) {
            $_SESSION[$variable] = $value; 
        }
        /*
        $_SESSION['prefixes_list'] = ""; 
        $_SESSION['stems_list'] = ""; 
        $_SESSION['suffixes_list'] = ""; 
        $_SESSION['block_list'] = "";
        $_SESSION['filter_list'] = "";
        $_SESSION['analysis_type'] = "none";
        $_SESSION['spacer_token_combinations'] = "";
        $_SESSION['spacer_vowel_groups'] = "";
        $_SESSION['spacer_rules_list'] = "";
        $_SESSION['license'] = "";
        $_SESSION['release_notes'] = "";
        $_SESSION['copyright_footer'] = "";
        $_SESSION['model_version'] = "";
        $_SESSION['model_date'] = "";
        */
}


// session
function ImportSession() {
    global $session_subsection, $whitelist_variables, $global_error_string, $global_warnings_string;
    
    ResetRestrictedSessionVariables();
    
    //echo "session_subsection: $session_subsection<br>";
    while ($session_subsection !== "") {
    $result = preg_match( "/[ ]*?\"(.*?)\"[ ]*?:=[ ]*?({?[ ]*?\".*?\"[ ]*?}?)[ ]*?;(.*)/", $session_subsection, $matches);
    
    //echo "<br>----------------------------------------------<br>session: $session_subsection result: $result<br>";
    
    if ($result == 1) {
        //echo "matches:<br>1: " . $matches[1] . "<br>2:" . $matches[2] . "<br>3: " . $matches[3] . "<br>";
        //echo "#" . $matches[1] . "# => #" . $matches[2] . "#<br>";
        $variable = $matches[1];
        if (preg_match("/ = /", $matches[1])) AddWarning("WARNING: = instead of := ?!<br>SECTION: " . htmlspecialchars($matches[1]) . "<br>"); 
        
        $value = trim(preg_replace("/\"(.*)\"/", "$1", $matches[2]));
        //echo "variable := value: $variable => $value<br>";
        $session_subsection = $matches[3];
        CheckAndSetSessionVariable( $variable, $value );
       /*
       if (mb_strpos($whitelist_variables, " $variable ") === FALSE) {
            //echo "Error! variable not in whitelist!<br>";
            $global_error_string .= "ERROR: you are not allowed to set variable '$variable'!";
        } else {
            //echo "assign \$_SESSION[$variable] = >$value<<br>";
            $_SESSION["$variable"] = $value; 
            //var_dump($_SESSION);
        }
        */
    } else return null;
  }
}


// analyzer
function ImportAnalyzer() {
    global $analyzer_subsection, $analyzer, $analyzer_options;
    $i = 0;
    
    while ($analyzer_subsection !== "") {
    // read rule with condition => single or multiple consequence (as second part = $matches[2])
    $result = preg_match( "/[ ]*?\"(.*?)\"[ ]*?=>[ ]*?({?[ ]*?\".*?\"[ ]*?}?)[ ]*?;(.*)/", $analyzer_subsection, $matches);
    
    //echo "analyzer: $analyzer_subsection result: $result<br>";
    
    if ($result == 1) {
        //echo "#" . $matches[1] . "# => #" . $matches[2] . "#<br>";
        $condition = $matches[1];
        // extension: separate tstopt(x) from base condition
        //echo "Analyzer-Rule: $i<br>";
        //echo "Condition: $condition<br>";
        list($condition, $bare_opt) = SeparateTstoptFromBaseCondition($condition);
        //$analyzer_options[$i][] = $bare_opt; // no insertion key ... (no distinction for model ...)
        if ($bare_opt === null) {
            $analyzer_options[$i][] = null; // element 0
            $analyzer_options[$i][] = ""; // element 1
        } else {
            $analyzer_options[$i][] = str_split($bare_opt); // element 0
            $analyzer_options[$i][] = $bare_opt; // element 1
        }
        // optimise wrt (idem)
        $new_condition = $condition;
        list($temp, $value) = SeparateWrtLngOptionFromBaseCondition($condition, "wrt");
        if ($value === null) $analyzer_options[$i][] = false;
        else {
            $new_condition = $temp;
            //echo "A: replace old condition $condition by $new_condition<br>"; 
            // replace original condition by new bare condition
            //$analyzer[$i][0] = $condition;
            $analyzer_options[$i][] = true; // push element [2]
        }
        // optimise lng (idem)
        list($temp, $value) = SeparateWrtLngOptionFromBaseCondition($condition, "lng");
        if ($value === null) $analyzer_options[$i][] = false;
        else { 
            $new_condition = $temp;
            //echo "A: replace old condition $condition by $new_condition<br>"; 
            // replace original condition by new bare condition
            //$analyzer[$i][0] = $condition;
            $analyzer_options[$i][] = true; // push element [3]
        }
        
        $consequence = $matches[2];
        //echo "condition => consequence: $condition => $consequence<br>";
        $analyzer_subsection = $matches[3];
        ///////////////////////// not sure if multiple consequences is needed - leave it for the moment //////////////////////
        // check if consequence is multiple consequence (= inside {}) 
        $result1 = preg_match( "/^{[ ]*?(\".*\")[ ]*?}$/", $consequence, $matches1); 
        //echo "rule $rules_pointer: $condition => $consequence<br>";
        switch ($result1) {
            //$nil = preg_match( "/^{(.*)}$/", $consequence, $matches1); // $nil should always be true ... ! ;-) 
            case "1" : 
                // multiple consequences
                $analyzer[$i][] = $new_condition;
                //echo "analyzer condition: $condition<br>";
                //echo "analyzer multiple: #" . htmlspecialchars($matches1[1]) . "#<br>";
                $matches1[1] = replace_all("/\"([^ ]*?),([^ ]*?)\"([ ]*?[,}])/", "\"$1#C#O#M#A#$2\"$3", $matches1[1]);
                //echo "analyzer multiple: #" . htmlspecialchars($matches1[1]) . "#<br>";
                $consequence_list = explode( ",", $matches1[1] ); // BUG: , inside "" must be escaped before explode!!! See also: ImportRule()
                foreach ($consequence_list as $element) {
                    // filter out spaces at beginning and end
                    $bare_element = preg_replace("/^[ ]*?\"(.*?)\"[ ]*?/", "$1", $element);
                    //echo "element: #$element# => bare_element: #$bare_element#<br>";
                    
                    $analyzer[$i][] = replace_all( "/#C#O#M#A#/", ",", $bare_element); // resubstitue #C#O#M#A#
                }
                //echo "rule($i) written: #" . $analyzer[$i][0] . "# => #" . $analyzer[$i][1] . "#-#" . $analyzer[$i][2] . "#<br>";
                $i++; // point to next analyzer-rule entry in array $analyzer
                break;
            default : 
                // just one consequence
                //echo "single consequence: #" . $consequence . "#<br>";
                $result2 = preg_match( "/^[ ]*?\"(.*?)\"[ ]*?/", $consequence, $matches2);
                //echo "result2: $result2 matches1(1): " . $matches2[1] . " rulespointer: $rules_pointer<br>";
                //$rules["$insertion_key"][] = $rules_pointer;
                $analyzer[] = array( $condition, $matches2[1]);        // rules[model][x][0] = condition of rule x in model
                //$rules["$insertion_key"][$rules_pointer][] = $matches2[1];      // rules[model][x][1] = consequence of rule x in model
                $i++;
                break;
        }
    } else return null;
  }
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
            //if ($actual_key === "@#/") echo "element: $element (last: $last)<br>";
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
    global $shrinking_generic_subsection, $rules, $rules_pointer, $insertion_key, $global_error_string, $rules_options;
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
        // extension: separate tstopt(x) from base condition
        //echo "Rule: $rules_pointer<br>";
        list($condition, $bare_opt) = SeparateTstoptFromBaseCondition($condition);
        //echo "Insert Option: key: $insertion_key rules_pointer: $rules_pointer option: $bare_opt<br>";
        //$rules_options["$insertion_key"][$rules_pointer][] = $bare_opt;
        if ($bare_opt === null) {
            $rules_options["$insertion_key"][$rules_pointer][] = null;      
            $rules_options["$insertion_key"][$rules_pointer][] = "";
        } else {
            $rules_options["$insertion_key"][$rules_pointer][] = str_split($bare_opt); // push as element [0]
            $rules_options["$insertion_key"][$rules_pointer][] = $bare_opt; // push as element [1]
        }
        // further optimisations
        $new_condition = $condition;
        // optimise wrt (idem)
        list($temp, $value) = SeparateWrtLngOptionFromBaseCondition($condition, "wrt");
        if ($value === null) $rules_options["$insertion_key"][$rules_pointer][] = false;
        else {
            $new_condition = $temp;
            //echo "R: replace old condition = $condition by $new_condition<br>"; 
            // replace original condition by new bare condition
            //$rules["$insertion_key"][$rules_pointer][0] = $condition;
            $rules_options["$insertion_key"][$rules_pointer][] = true; // push element [2]
        }
        // optimise lng (idem)
        list($temp, $value) = SeparateWrtLngOptionFromBaseCondition($condition, "lng");
        if ($value === null) $rules_options["$insertion_key"][$rules_pointer][] = false;
        else { 
            $new_condition = $temp;
            //echo "R: replace old condition $condition by $new_condition<br>"; 
            // replace original condition by new bare condition
            //$rules["$insertion_key"][$rules_pointer][0] = $condition;
            $rules_options["$insertion_key"][$rules_pointer][] = true; // push element [3]
        }
       
        /*
        $test = str_split($bare_opt);
        if ($bare_opt !== null) {
            echo "bare option: $bare_opt bare option splitted (array): "; var_dump($test);
            echo "rules_options: "; var_dump($rules_options["$insertion_key"][$rules_pointer][0]);
        } 
        */
        $consequence = $matches[2];
        if (preg_match("/=>/", $condition) === 1) $global_error_string .= "WARNING: \"$condition\" => \"$consequence\" (possibly malformed rule)<br>";
        if (preg_match("/=>/", $consequence) === 1) $global_error_string .= "WARNING: \"$condition\" => \"$consequence\" (possibly malformed rule)<br>";
        //if (mb_strlen($global_error_string)>0) echo $global_error_string;
        //if ($condition === "«") echo "consequence: >$consequence<<br>";  
        //echo "condition => consequence: " . htmlspecialchars($condition) . " => $consequence<br>";
        //echo "new condition: $new_condition<br>";
        
        $shrinking_generic_subsection = $matches[3];
        $result1 = preg_match( "/^{[ ]*?(\".*\")[ ]*?}$/", $consequence, $matches1); 
        //echo "rule $rules_pointer: $condition => $consequence<br>";
        switch ($result1) {
            //$nil = preg_match( "/^{(.*)}$/", $consequence, $matches1); // $nil should always be true ... ! ;-) 
            case "1" : 
                // multiple consequences
                //echo "INSERT new condition: $new_condition<br>";
                $rules["$insertion_key"][$rules_pointer][] = $new_condition;
                //echo "condition: $condition<br>";
                //echo "multiple: #" . htmlspecialchars($matches1[1]) . "#<br>";
                $matches1[1] = replace_all("/\"([^ ]*?),([^ ]*?)\"([ ]*?[,}])/", "\"$1#C#O#M#A#$2\"$3", $matches1[1]);
                //echo "replaced: " . htmlspecialchars($matches1[1]) . "<br>";
                $consequence_list = explode( ",", $matches1[1] ); // BUG!
                // using explode with , leads to a serious problem:
                // rule:
                // "tstwrt(^[Dd]is)" => { "^d,Is", "dis" }; // disarm (dis-), disappear
                // can't be processed because the multiple consequence is divided into three parts:
                // a) "^d
                // b) Is"
                // c)  "dis"
                // workaround: replace , inside "" with #C#O#M#A# before explode and re-sustitute it afterwords
                foreach ($consequence_list as $element) {
                    $bare_element = preg_replace("/^[ ]*?\"(.*?)\"[ ]*?/", "$1", $element);
                    //echo "element: #$element# => bare_element: #$bare_element#<br>";
                    //$rules["$insertion_key"][] = $rules_pointer;
                    
                    $rules["$insertion_key"][$rules_pointer][] = replace_all( "/#C#O#M#A#/", ",", $bare_element); // resubstitute #C#O#M#A#
                }
                //echo "Final insertion:<br>"; var_dump($rules["$insertion_key"][$rules_pointer]); echo "<br>";
                //echo "Options: <br>"; var_dump($rules_options["$insertion_key"][$rules_pointer]); echo "<br>";
                $rules_pointer++;
                break;
            default : 
                // just one consequence
                //echo "single consequence: #" . $consequence . "#<br>";
                $result2 = preg_match( "/^[ ]*?\"(.*)\"[ ]*?/", $consequence, $matches2);
                // search for second " must be GREEDY (otherwise, the consequence """ wont work!)
                
                //echo "result2: $result2 matches1(1): " . $matches2[1] . " rulespointer: $rules_pointer<br>";
                //$rules["$insertion_key"][] = $rules_pointer;
                // extension: tstopt(x)condition => filter out tstopt(x) from condition
/*
                echo "CHECK if rule[$rules_pointer] with 1 consequence contains tstopt(x) ...<br>";
                echo "condition: $condition<br>";
                $resultopt = preg_match( "/tstopt\((.*?)\)/", $condition, $tstopt);
                if ($resultopt === 1) {
                    echo "YES: modify condition: $condition => ";
                    //echo "Pattern: " . $tstopt[0] . "<br>";
                    $condition = preg_replace( "/" . preg_quote($tstopt[0]) . "/", "", $condition);
                    echo "$condition <br>";
                    $bare_option = $tstopt[1];
                    echo "bare_option: $bare_option<br>";
                    $rules_options["$insertion_key"][$rules_pointer][] = $bare_option;
                } else {
                    echo "NO: Rule[$rules_pointer] doesn't contain additional options (set to null)<br>";
                    $rules_options["$insertion_key"][$rules_pointer][] = null;
            }
*/
                
                // is the following line correct?!?
                //echo "123Final insertion: <br>Condition: " . htmlspecialchars($new_condition) . "<br>Consequence(s):"; var_dump($matches2[1]); echo "<br>";
                //echo "Options: <br>"; var_dump($rules_options["$insertion_key"][$rules_pointer]); echo "<br>";
                
                $rules["$insertion_key"][] = array( $new_condition, $matches2[1]);        // rules[model][x][0] = condition of rule x in model
                //$rules["$insertion_key"][$rules_pointer][] = $matches2[1];      // rules[model][x][1] = consequence of rule x in model
                $rules_pointer++;
                break;
        }
    } else return null;
  }
}

function SeparateTstoptFromBaseCondition($condition) {
        //echo "SeparateTstoptFromBaseCondition()<br>";
        //echo "condition: $condition<br>";
        $resultopt = preg_match( "/tstopt\((.*?)\)/", $condition, $tstopt);
        if ($resultopt === 1) {
                //echo "YES: modify condition: $condition => ";
               $condition = preg_replace( "/" . preg_quote($tstopt[0]) . "/", "", $condition);
                //echo "$condition <br>";
                $bare_opt = $tstopt[1];
                //echo "bare_option: $bare_option<br>";
        } else {
                //echo "NO: Rule doesn't contain additional options (set to null)<br>";
                $bare_opt = null;
        }
        return array($condition, $bare_opt);
}

function SeparateWrtLngOptionFromBaseCondition($condition, $option) {
        // returns:
        // - bare condition
        // - true (if tstoption must be tested) / null (if it is a standard rule)
        // IMPORTANT: regex must be greedy (unlike tstopt()) - see french rule "effort"
        $resultopt = preg_match( "/tst$option\((.*)\)/", $condition, $tstopt);
        if ($resultopt === 1) {
                // new condition is argument x of tstwrt(x) / tstlng(x)
                $condition = $tstopt[1];
                //echo "new conditon: $condition<br>";
                // function returns true
                $value = true;
        } else $value = null;
        return array($condition, $value);
}

function WriteParamListToRulesArray( $type, $param_list ) {
    global $rules, $insertion_key, $rules_pointer, $rules_pointer_start_std2prt, $rules_pointer_start_stage2, $rules_pointer_start_stage4, $rules_pointer_start_stage3;
    $rules["$insertion_key"][$rules_pointer][] = $type;
    //echo "type: $type<br>"; // Begin/EndFunction()
    foreach( $param_list as $parameter ) {
        if ($parameter === "=:std") {
            //echo "End of WRD=>STD detected: rule number: $rules_pointer<br>";
            //echo "rule($rules_pointer): " . $rules["$insertion_key"][$rules_pointer][0];
            //echo "set rules_pointer_start_std2prt";
            $rules_pointer_start_std2prt = $rules_pointer + 1;  // set it to begin of following function
        } elseif ($parameter === "#>stage4") {
            //echo "set stage4: " . ($rules_pointer+1) . "<br>";
            if ($type === "EndFunction()") $rules_pointer_start_stage4 = $rules_pointer + 1;  // same as for std 
            else $rules_pointer_start_stage4 = $rules_pointer;
        } elseif ($parameter === "#>stage3") {
            //echo "set stage3: " . ($rules_pointer+1) . "<br>";
            if ($type === "EndFunction()") $rules_pointer_start_stage3 = $rules_pointer + 1;  // same as for std 
            else $rules_pointer_start_stage3 = $rules_pointer;
        } elseif ($parameter === "#>stage2") {
            //echo "set stage2: " . ($rules_pointer+1) . "<br>";
            //if (isset($rules_pointer_start_stage2)) { echo "warning: stage2 set 2x<br>"; $secure = $rules_pointer_start_stage2; }
            if ($type === "EndFunction()") $rules_pointer_start_stage2 = $rules_pointer + 1;  // same as for std 
            else $rules_pointer_start_stage2 = $rules_pointer;
            //echo "stage2: $rules_pointer_start_stage2 $secure<br>";
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
    $number_subsections = 0;
    //echo "rules_section: " . htmlspecialchars($rules_section) . "<br>";
    while /*($rules_pointer < 5) { */ ($shrinking_rules_section !== "") {
        //echo "rulessection: $shrinking_rules_section<br>";
        $number_subsections++;
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
    $_SESSION['statistics_subsections'] = $number_subsections;

}

//////////////////////////////////////////// import whole Model /////////////////////////////////////////////////////////////
function ImportModelFromText($text) {
    global $font_section, $rules_section, $base_subsection, $combiner_subsection, $shifter_subsection, $analyzer_subsection, $session_subsection, $analyzer;
    global $rules_pointer_start_stage2, $rules_pointer_start_stage3, $rules_pointer_start_stage4, $rules;
        // strip out unnecessary stuff
    $output = StripOutComments($text);
    $output = StripOutTabsAndNewlines($output);
    // get main sections
    $header_section = GetSection($output, "header");
    $font_section = GetSection($output, "font");
    $rules_section = GetSection($output, "rules");
    
    // get subsections
    // header
    $analyzer_subsection = GetSubSection($header_section, "analyzer");
    $session_subsection = GetSubSection($header_section, "session");
    //echo "analyzer: $analyzer_subsection<br>"; 
    //echo "session: $session_subsection<br>";
    // font
    $base_subsection = GetSubSection($font_section, "base");
    $combiner_subsection = GetSubSection($font_section, "combiner");
    $shifter_subsection = GetSubSection($font_section, "shifter");
    // parse data
    //ImportSession(); // do that only when loading the page for the 1st time or when reset button is clicked (not whenever a calculation is made: user must have the possibility to override session variables!)
    ImportAnalyzer();
    
    $_SESSION['statistics_analyzer_rules'] = count($analyzer); 
    
    //var_dump($analyzer);
    // genious: even slower if tokens are not loaded ... :) :) :)
    //if (($_SESSION['output_format'] === "meta_lng") || ($_SESSION['output_format'] === "meta_std") || ($_SESSION['output_format'] === "meta_prt")) {
    //    echo "don't load tokens (for performance reasons)<br>";
    //} else {
        ImportBase();   // data is written to global variable $imported_base (which corresponds to $steno_tokens_master)
        ImportCombiner(); // idem to $imported_combiner
        ImportShifter(); // idem to $imported_shifter


// connect old variables
// note: this is the easy (or should i say "quick and dirty";-) method to reuse old parser functions with new data
// it works as long as you reassign the new data to the old variables whenever (each time!) the model changes!
// there's still a bug: exported array has 170 elements, imported one 161 => why?!?
global $font, $combiner, $shifter;
global $steno_tokens_master, $combiner_table, $shifter_table, $steno_tokens_type; // $steno_tokens_type: table to mark tokens created by shifter/combiner
$actual_model = $_SESSION['actual_model'];

//echo "actual_model: $actual_model<br>";
$steno_tokens_master = $font[$actual_model];
$combiner_table = $combiner[$actual_model];
$shifter_table = $shifter[$actual_model];

 global $token_groups, $group_combinations, $vowel_groups, $token_variants, $rules_list;
  
$number_base = count($steno_tokens_master);
CreateCombinedTokens();
$number_combined = count($steno_tokens_master) - $number_base; 
CreateShiftedTokens();
$number_shifted = count($steno_tokens_master) - $number_base - $number_combined;

StripOutUnusedTuplets();

 //var_dump($steno_tokens_master);
 GenerateTokenGroups($steno_tokens_master);
 GenerateGroupCombinations();
 GenerateVowelGroups();
 GenerateRulesList();
 //$group_combinations_variable = $_SESSION['spacer_token_combinations'];
 //$group_combinations = ImportGroupCombinationsFromVariable( $group_combinations_variable );
$_SESSION['statistics_tokens'] = count($steno_tokens_master);
$_SESSION['statistics_base'] = $number_base;
$_SESSION['statistics_combined'] = $number_combined;
$_SESSION['statistics_shifted'] = $number_shifted;

  //var_dump($_SESSION['spacer_vowel_groups']);
  //var_dump($vowel_groups);
  
    // patch spacer in rules section if autoinsert is selected
    // since ImportModelFromText() can be called from different sources (form or other, i.e. with POST-variable set or not) first make sure to update
    // session-variable if POST-variable is set (= data.php is called from a form)
    if (isset($_POST['spacer_autoinsert'])) $_SESSION['spacer_autoinsert'] = ($_POST['spacer_autoinsert'] === "yes") ? true : false;
    else $_SESSION['spacer_autoinsert'] = false;
    /*
    echo $_SESSION['spacer_autoinsert'] . $_POST['spacer_autoinsert'];
    $_SESSION['spacer_autoinsert'] = false;
    */  
 
    // afterward check session variable
    if ($_SESSION['spacer_autoinsert']) {
        //echo "<p>AUTOGENERATE</p>";
        $spacer_rules = /*utf8_encode(*/GenerateSpacerRules()/*)*/;
        $spacer_rules = StripOutTabsAndNewLines(StripOutComments($spacer_rules));
        //echo "<p>$spacer_rules</p>";
        // discovered that in php-regex .*? doesn't include line breaks \n
        // which means that .*? can't find spacer rules that span over several lines
        // workaround: (?:.|[\n\t\r])*? inludes \n, \t, \s (or whatever is needed)
        // inside the "allowed" characters
        //$rules_section_test = preg_replace("/(#BeginSubSection\(spacer)(.*?\))((?:.|[\n\t\r])*?)(#EndSubSection\(spacer)/", "$1$2$spacer_rules$4", $rules_section);   
        
        //preg_match("/#BeginSubSection\(spacer)(.*?\))((?:.|[\n\t\r])*?)(#EndSubSection\(spacer)/", $rules_section, $matches);
        preg_match("/^(.*?)(#BeginSubSection\(spacer.*?\))(?:.*?)(#EndSubSection\(spacer.*?\))(.*?)$/", $rules_section, $matches);
      
        //var_dump($matches);
        $firstpart = $matches[1];
        $functionstart = $matches[2];
        $functionend = $matches[3];
        $lastpart = $matches[4];
        
        $rules_section = $firstpart . $functionstart . $spacer_rules . $functionend . $lastpart;
        //echo "<br>rules_section(test):<br><br><pre>" . htmlspecialchars($rules_section_test) . "</pre>";
        //echo "spacer_rules: " . htmlspecialchars($spacer_rules) . "<br>";
        //echo "<br>rules_section(original):<br><br><pre>" . htmlspecialchars($rules_section) . "</pre><br>";
    }
 
    ImportRules();  // idem to $imported + name of the original table (shortener, bundler, transcriptor etc.)

    $_SESSION['statistics_rules'] = count($rules[$actual_model]);

    //}
     // if stage3 and stage4 are not set => set them for compatibility
    $actual_model = $_SESSION['actual_model'];
    if ($rules_pointer_start_stage3 === null) $rules_pointer_start_stage3 = 0; // this is wrong: should point to "after global" (= stage1) ... but which variable is that?!?
    if ($rules_pointer_start_stage2 === null) $rules_pointer_start_stage2 = 0; // idem
    if ($rules_pointer_start_stage4 === null) $rules_pointer_start_stage4 = count($rules[$actual_model]); // set stage4 to end of rules (== stage4 inexistant!)
 
    return $output;
}

require_once "regex_helper_functions.php";

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