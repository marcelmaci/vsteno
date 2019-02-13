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

require_once "options.php"; 
require_once "dbpw.php";
require_once "data.php";
require_once "session.php"; // add this temporarily for debugging

/*
require_once "engine.php";
require_once "constants.php";
require_once "session.php";
*/
$act_word = "";
$word_at_beginning_of_function = "";
$actual_function = "";


function replace_all( $pattern, $replacement, $string ) {
    do {
        $old_string = $string;
        $string = extended_preg_replace( $pattern, $replacement, $string );
    } while ($old_string !== $string );
    return $string;
}
/*
function GenericParser( $table, $word ) {
    global $original_word, $result_after_last_rule, $global_debug_string, $global_number_of_rules_applied;
    //echo "GenericParser(): word: $word table: $table ";
    $output = $word;
    foreach ( $table as $pattern => $replacement ) {
        //echo "pattern: $pattern replacement: $replacement output: $output<br>";
        $type = gettype($table[$pattern]);
        //$type = gettype($table[$replacement]);
        
        //echo "type: $type<br>";
        if ($type === "array") {
            //echo "replacement == array:<br>";
            //echo "apply rule: $pattern => " . $table[$pattern][0] . " with exception: " . $table[$pattern][1] . "<br>";
            $extra_replacement = $table[$pattern][0];
            $output = preg_replace( "/$pattern/", $extra_replacement, $output );
            //echo "word: $word output: $output replaced: $replaced FROM: rule: $pattern => $replacement <br>";
            if ($output !== $word) {   // rule has been applied => test, if there are exceptions
                //echo "Rule applied: word: $word output: $output FROM: rule: $pattern => $extra_replacement <br>";
                $length = count($table[$pattern]);
                //echo "length(array): $length<br>";
                $there_is_a_match = false;
                for ($i=1; $i<$length; $i++) {
                    $extra_pattern = $table[$pattern][$i];
                    //$original_word = "Pflicht"; // must be the original word without any modifications! => take it from constants before rewrite as OOP
                    if (mb_strlen($extra_pattern)>0)$result = preg_match( "/$extra_pattern/", $original_word );
                    if ($result == 1) {  // exception matches
                        $there_is_a_match = true;
                        $matching_pattern = $extra_pattern;
                        //echo "Match with: $extra_pattern in Original: $original_word<br>";
                    }
                }
                if ($there_is_a_match) {
                    //echo "Don't apply rule!<br>";
                    $output = $result_after_last_rule; // $word; // don't apply rule (i.e. set $output back to $word) => Wrong! set it to result after last applied rule
                    $global_debug_string .= "<tr><td>[X] $output</td><td>RULE($rules_pointer): " . htmlspecialchars($pattern) . " => { " . htmlspecialchars($rules["$actual_model"][$rules_pointer][1]) . ", ... }<br>NOT APPLIED: $matching_pattern MATCHES IN $original_word</td></tr>";
                } else {
                    $global_number_of_rules_applied++;
                    $global_debug_string .= "<tr><td>[$global_number_of_rules_applied] WORD: $output </td><td>FROM: rule: " . htmlspecialchars($pattern) . " => " . htmlspecialchars($replacement) . "</td></tr>";
                }
            }
        } else {
            $preceeding_result = $output;
            $temp = $output;
            $output = preg_replace( "/$pattern/", $replacement, $output );
            //echo "\nStandardProcedureForRule: pattern: $pattern => replacement: $replacement<br>word: $word result: $output preceeding: $preceeding_result last: $result_after_last_rule<br>";
            
            if ($output !== $preceeding_result) {           // maybe wrong: should be $result_after_last_rule?!
                $result_after_last_rule = $output;
                $global_number_of_rules_applied++;
                $global_debug_string .= "<tr><td>[$global_number_of_rules_applied] $output</td><td>RULE($rules_pointer): " . htmlspecialchars($pattern) . " => " . htmlspecialchars($replacement) . "</td></tr>"; 
                
                //echo "GDS: $global_debug_string<br>";
                //echo "Match: word: $word output: $output FROM: rule: $pattern => $replacement <br>";
            }
        }
    }
    
     return $output;
}
*/
///////////////////////////////////////////// parser functions ////////////////////////////////////////////////

// after almost hours (and hours) of searching I come to the conclusion that there is no out-of-the-box-solution to do an upper/lower-case conversion in php-regex ... :-/
// the only solution would be to substitute character by character (individually)
// of course: in php you can do that - still quite elegantly - with an array
// but VSTENO-users won't have this possibility ...
// finally, i came up with the following workaround: the function extended_preg_replace() uses the preg_replace_callback()-function in php, which offers the possibility
// to call a php-function depending on whether a pattern matches or not (and sending the part that matches to that function)
// extended_preg_replace() uses this to call the function mb_strtolower() and mb_strtoupper()
//
// so, finally the user has 2 possibilities:
// (1) write a "normal" regex expression, e.g.:                         "convert this" => "convert that"
// (2) use "strtolower()" or "strtoupper()" as replacement string:      "[a-z]" => "strtoupper()"     or "[A-Z]" => "strtolower()"
//
// works like a charm ... ! ;-)
//
// nonetheless, it would have been much simpler, if php offered a regex-syntax like: "([A-Z])" => "\L$1" ...

function extended_preg_replace( $pattern, $replacement, $string) {
        switch ($replacement) {
                case "strtolower()" : $result = preg_replace_callback( $pattern, function ($word) { return mb_strtolower($word[1]); }, $string); 
                                      break;
                case "strtoupper()" : $result = preg_replace_callback( $pattern, function ($word) { return mb_strtoupper($word[1]); }, $string); 
                                      break;
                default : $result = preg_replace( $pattern, $replacement, $string);
        }
        return $result;
};

function Lookuper( $word ) {
    global $this_word_punctuation, $last_word_punctuation, $processing_in_parser, $global_debug_string, $actual_function;
    //echo "in Lookuper()";
    $conn = Connect2DB();
    // Check connection
    if ($conn->connect_error) {
        die("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
    }
    //echo "in Lookuper: $word<br>";
    // prepare data
    $safe_word = $conn->real_escape_string( $word );
    $elysium = GetDBName( "elysium" );
    $sql = "SELECT * FROM $elysium WHERE word='$safe_word'";
    //echo "Elysium: query = $sql<br>";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        //echo "Wort: " . $row['word'] . " in Datenbank gefunden. Rückgabe: " . $row['single_prt'] . "<br>";
        $global_debug_string .= "<tr><td></td><td>ELYSIUM ($elysium): MATCH FOUND</td><td>" . mb_strtoupper($actual_function) . "</td></tr>";
            
        $processing_in_parser = "D";
        return GetOptimalStdPrtForm( $row );   // return both: std and prt
        
    } elseif ($last_word_punctuation) {
        $safe_word = mb_strtolower( $safe_word );   // if word is at beginning of text or after a punctuation, seek also for lower case wordwrap
        //echo "$safe_word => check lowercase PUNCTUATION:  #$last_word_punctuation#$this_word_punctuation# (lookuper)<br>";
        $sql = "SELECT * FROM $elysium WHERE word='$safe_word'";
        //echo "Elysium: query = $sql<br>";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            //echo "Wort: " . $row['word'] . " in Datenbank gefunden. Rückgabe: " . $row['single_prt'] . "<br>";
            $global_debug_string .= "<tr><td></td><td>ELYSIUM ($elysium): MATCH FOUND</td><td>" . mb_strtoupper($actual_function) . "</td></tr>";
            $processing_in_parser = "D";
            return GetOptimalStdPrtForm( $row );   // return both: std and prt
       }
    } else return null;    
}

function ExecuteEndParameters() {
    global $rules, $rules_pointer;
    global $std_form, $prt_form, $separated_std_form, $separated_prt_form, $result_after_last_rule, $act_word;
    global $functions_table, $word_at_beginning_of_function, $global_debug_string, $actual_function;
    $actual_model = $_SESSION['actual_model'];
    $length = count($rules["$actual_model"][$rules_pointer]);
    for ($i=1; $i<$length; $i++) {
        switch ($rules["$actual_model"][$rules_pointer][$i]) {
            case "=:std" : /*echo "=:std: #$result_after_last_rule# act_word = $act_word act_function: $actual_function rules_pointer=$rules_pointer<br>";*/ $std_form = $result_after_last_rule; break;
            case "=:prt" : /*echo "=:prt: #$result_after_last_rule#<br>";*/ $prt_form = $result_after_last_rule; break;
            case "@@dic" : 
                list($temp_std, $temp_prt) = Lookuper($act_word);
                //echo "result lookuper: temp_std = #$temp_std# temp_prt = #$temp_prt#<br>";
                if (($temp_std !== null) || ($temp_prt !== null)) {
                    // there was a result in the dictionary
                    $std_form = $temp_std;
                    $prt_form = $temp_prt;
                    $result_after_last_rule = $prt_form;
                //echo "act_word = $act_word (in Lookuper 1) $temp_prt $temp_std<br>";
                
                    $act_word = $prt_form;
                //echo "act_word = $act_word (in Lookuper 2)<br>";
                
            $rules_pointer = count($rules["$actual_model"]);    // set rules pointer to end (= dont execute more rules)
                    //echo "rules_pointer = $rules_pointer std_form = $std_form prt_form = $prt_form<br>";
                }
                break;
            default :
                $temp_element = $rules["$actual_model"][$rules_pointer][$i];
                //$temp_element = ">>test";
                $first_two_chars = mb_substr($temp_element, 0, 2);
                $length = mb_strlen($temp_element);
                $argument = mb_substr($temp_element, 2, $length-2);
                //echo "first_two = $first_two_chars length = $length argument = $argument<br>";
                
                switch ($first_two_chars) {
                    case ">>" : $rules_pointer = $functions_table[$actual_model][$argument][0]; 
                                $global_debug_string .= "<tr><td></td><td>BRANCH: $temp_element($rules_pointer)</td><td>" . strtoupper($actual_function) . "</td></tr>";
                                $actual_function = $argument;
                                break;  // inconditional branch
                    case "=>" : if ($word_at_beginning_of_function === $act_word) {                     // branch if equal
                                    $rules_pointer = $functions_table[$actual_model][$argument][0];
                                    $global_debug_string .= "<tr><td></td><td>BRANCH: $temp_element($rules_pointer)</td><td>" . strtoupper($actual_function) . "</td></tr>";
                                    $actual_function = $argument;
                                }
                                break;
                    case "!>" : if ($word_at_beginning_of_function !== $act_word) {                     // branch if not equal
                                    $rules_pointer = $functions_table[$actual_model][$argument][0];
                                    $global_debug_string .= "<tr><td></td><td>BRANCH: $temp_element($rules_pointer)</td><td>" . strtoupper($actual_function) . "</td></tr>";
                                    $actual_function = $argument;
                                }
                                break;
                }
        }
    }
}

function ExecuteBeginParameters() {
    global $rules, $rules_pointer;
    global $std_form, $prt_form, $separated_std_form, $separated_prt_form, $result_after_last_rule;
    global $original_word, $act_word, $word_at_beginning_of_function;
    global $safe_std, $actual_function;                   // this global variable comes from database (in purgatorium1.php)
    
    //echo "execute begin<br>";
    $result = "";
    $actual_model = $_SESSION['actual_model'];
    $length = count($rules["$actual_model"][$rules_pointer]);
    $word_at_beginning_of_function = $act_word;
    $actual_function = $rules["$actual_model"][$rules_pointer][1]; // name of function must be first argument!

    for ($i=1; $i<$length; $i++) {
        //echo "argument($i) length=$length: " . $rules["$actual_model"][$rules_pointer][$i] . "<br>";
        switch ($rules["$actual_model"][$rules_pointer][$i]) {
            case "@@wrd" : if ($_SESSION['original_text_format'] === "normal") {
                                $act_word = $original_word; 
                                $result_after_last_rule = $original_word; 
                            } else {
                                $act_word = ""; 
                                $result_after_last_rule = ""; 
                            }
                            break;
            case "@@std" :  //echo "hier sollte standard gesetzt werden (safe_std = $safe_std)<br>";
                            if ($_SESSION['original_text_format'] === "std") {
                                $act_word = $safe_std; 
                                $result_after_last_rule = $safe_std; 
                            }
                            break;
            //case "=:prt" : /*echo "=:prt: #$result_after_last_rule#<br>";*/ $prt_form = $result_after_last_rule; break;
        }
    }
}

// ExecuteRule replaces GenericParser from old parser
function ExecuteRule( /*$word*/ ) {

    global $original_word, $result_after_last_rule, $global_debug_string, $global_number_of_rules_applied;
    global $rules, $rules_pointer, $actual_function;
    global $act_word, $original_word, $result_after_last_rule;
    //echo "is word set?: $word";
    //$output = $word;
    $actual_model = $_SESSION['actual_model'];
    $condition = $rules["$actual_model"][$rules_pointer][0];
    //echo "ExecuteRule(): condition=#$condition#<br>";
    switch ($condition) {
        case "BeginFunction()" : ExecuteBeginParameters(); $output = $act_word; break;
        case "EndFunction()" : ExecuteEndParameters(); $output = $act_word; break;
        default : // normal condition
            //echo "in default: act_word = $act_word<br>";
            $output = $act_word;
            $length = count($rules["$actual_model"][$rules_pointer]);
            if ($length == 2) {
                // normal rule: 1 condition => 1 consequence
                $preceeding_result = $output;
                $temp = $output;
                $pattern = $rules["$actual_model"][$rules_pointer][0];
                $replacement = $rules["$actual_model"][$rules_pointer][1];
                $output = extended_preg_replace( "/$pattern/", $replacement, $output );
                //echo "\nStandardProcedureForRule: pattern: #$pattern# => replacement: #$replacement#<br>word: $preceeding_result result: $output last: $result_after_last_rule<br>";
            
                if ($output !== $preceeding_result) {           // maybe wrong: should be $result_after_last_rule?!
                    $result_after_last_rule = $output;
                    $global_number_of_rules_applied++;
                    $global_debug_string .= "<tr><td><b>[$global_number_of_rules_applied]</b> $output </td><td><b>[R$rules_pointer]</b> " . htmlspecialchars($pattern) . " <b>⇨</b> " . htmlspecialchars($replacement) . "</td><td>" . strtoupper($actual_function) . "</td></tr>"; 
                }
                //echo "GDS: $global_debug_string<br>";
                //echo "Match: word: $word output: $output FROM: rule: $pattern => $replacement <br>";
            
            } else {
                // special rule: 1 condition => several consequences
                //if ($rules_pointer == 43) echo "rule(43): " . $rules["$actual_model"][$rules_pointer][0] . " => " . $rules["$actual_model"][$rules_pointer][1] . "<br>";
                $pattern = $rules["$actual_model"][$rules_pointer][0];
                $replacement = $rules["$actual_model"][$rules_pointer][1];
                //$extra_replacement = $rules["$actual_model"][$rules_pointer][1];
                $word = $act_word;
                $output = extended_preg_replace( "/$pattern/", $replacement, $output );
                //echo "word: $word output: $output FROM: rule: $pattern => $replacement <br>";
                if ($output !== $word) {   // rule has been applied => test, if there are exceptions
                    //echo "Rule applied: word: $word output: $output FROM: rule: $pattern => $extra_replacement <br>";
                    $length = count($rules["$actual_model"][$rules_pointer]); // number of elements as consequence + 1 (condition is counted)
                    $there_is_a_match = false;
                    for ($i=2; $i<$length; $i++) {  // element 2 = first exception
                        $extra_pattern = $rules["$actual_model"][$rules_pointer][$i];
                        //$original_word = "Pflicht"; // must be the original word without any modifications! => take it from constants before rewrite as OOP
                        //echo "TEST: pattern: $extra_pattern in Original: $original_word<br>";
                       
                        if (mb_strlen($extra_pattern)>0) $result = preg_match( "/$extra_pattern/", $original_word );
                        if ($result == 1) {  // exception matches
                            $there_is_a_match = true;
                            $matching_pattern = $extra_pattern;
                            //echo "Match with: $extra_pattern in Original: $original_word result_after_last_rule: $result_after_last_rule<br>";
                        }
                    }
                    if ($there_is_a_match) {
                        //echo "Don't apply rule!<br>";
                        $output = $result_after_last_rule; // $word; // don't apply rule (i.e. set $output back to $word) => Wrong! set it to result after last applied rule
                        $global_debug_string .= "<tr><td><b>[X]</b> $output</td><td><b>[R$rules_pointer]</b> " . htmlspecialchars($pattern) . " <b>⇨</b> { " . htmlspecialchars($rules["$actual_model"][$rules_pointer][1]) . ", ... }<br>NOT APPLIED: $matching_pattern (EXCEPTION)</td><td>" . strtoupper($actual_function) . "</td></tr>";
                    } else {
                        $global_number_of_rules_applied++;
                        $global_debug_string .= "<tr><td><b>[$global_number_of_rules_applied]</b> $output </td><td><b>[R$rules_pointer]</b> " . htmlspecialchars($pattern) . " <b>⇨</b> { " . htmlspecialchars($replacement) . ", ... }</td><td>" . strtoupper($actual_function) . "</td></tr>";
                    }
                }
            
            }
    }
    //if ($output === "") echo "output = null / rule = $rules_pointer<br>";
    $act_word = $output;
    //if ($result === "") {echo "return output: $output"; return $output;}
    //else { echo "return result $result"; return $result; }
   // return $output;
    //return $word;

}

function ParserChain( $text ) {
        global $font, $combiner, $shifter, $rules, $functions_table, $rules_pointer;
        global $std_form, $prt_form, $processing_in_parser, $separated_std_form, $separated_prt_form, $rules_pointer_start_std2prt;
        global $original_word, $result_after_last_rule, $act_word, $start_word_parser;
        // test if word is in dictionary: if yes => return immediately and avoid parserchain completely (= word will be transcritten directly by steno-engine
        
        $processing_in_parser = "R"; // suppose word will been obtained by processing the rules
        //list($res_std, $res_prt) = Lookuper( $text ); // database-function
      /*  
        if ((mb_strlen($res_std) > 0) || ((mb_strlen($res_prt)>0))) {
            $processing_in_parser = "D";  // mark word as taken from dictionary
            $std_form = $res_std;
            $prt_form = $res_prt;
            $separated_std_form = ""; // must be "", otherwise result will be "doubled" (i.e. 2x std, 2x prt) => why?!
            $separated_prt_form = "";
            return $res_prt;
        }
        */
        
        // set rules pointer
        $rules_pointer = $start_word_parser; // default
        if ($_SESSION['original_text_format'] === "std") {
            $rules_pointer = $rules_pointer_start_std2prt; // start with STD form (after bundler)
            $act_word = $text;
        }
        
       //echo "set rules_pointer to $start_word_parser<br>";
        $actual_model = $_SESSION['actual_model'];
        //$act_word = $text;
        
        $original_word = $text;
        $result_after_last_rule = $act_word;
        
        //$temp = isset($rules[$actual_model][$rules_pointer]);
        //echo "actual_model: $actual_model";
        //var_dump($rules);
        $number_of_rules = count($rules[$actual_model]);
        //echo "number of rules: $number_of_rules rules_pointer: $rules_pointer<br>";
        while ($rules_pointer < $number_of_rules) { // (isset($rules[$actual_model][$rules_pointer])) { // ($rules_pointer < 45) { // only apply 45 rules for test // 
            //echo "before executerule: $rules_pointer<br>";
            //$act_word = ExecuteRule( $act_word );
            //echo "rule($rules_pointer) actword = $act_word<br>";
            //echo "ParserChain: act_word = $act_word (before)<br>";
            ExecuteRule();
            //echo "ParserChain: act_word = $act_word (after executerule())<br>";
            
            //echo "rule($rules_pointer) actword = $act_word<br>";
            //echo "after execute";
            $rules_pointer++;
        }
        return $act_word;
}

function GetPreAndPostTokens( $text ) {
        // Separates pre- und posttokens, $text must be middle part of "word", i.e. original word without pre- and posttags (must be separated
        // by GetPreAndPostTags() first
        // Returns: array($pretokens, $pureword, $posttokens) 
        global $pretokenlist, $posttokenlist, $last_word_punctuation, $this_word_punctuation, $upper_case_punctuation;
        //$text_decoded = htmlspecialchars_decode( $text );
        preg_match( "/^[$pretokenlist]*/", $text, $pretokens);
        preg_match( "/[$posttokenlist]*$/", $text, $posttokens);
        $pre_regex = preg_quote( $pretokens[0] );                                        // found patterns must be escaped before being
        $post_regex = preg_quote( $posttokens[0] );    
        preg_match( "/(?<=$pre_regex).+(?=$post_regex)/", $text, $word_array );
        if ($pre_regex === preg_quote($text)) {
            $ret_pre = $text;
            $ret_word = "";
            $ret_post = "";
        } elseif ($post_regex === preg_quote($text)) {
            $ret_pre = $text;
            $ret_word = "";
            $ret_post = "";
        } else {
            $ret_pre = $pretokens[0];
            $ret_word = $word_array[0];
            $ret_post = $posttokens[0];
        }
        //return array( $pretokens[0], $word_array[0], $posttokens[0] );
        //echo "pretokens: $ret_pre word: $ret_word post_tokens: $ret_post<br>";
        $last_char = mb_substr($ret_post, mb_strlen($ret_post)-1, 1);
        if (mb_strpos($upper_case_punctuation, $last_char) !== false) {
            $last_word_punctuation = $this_word_punctuation;
            $this_word_punctuation = true;
            //echo "$text last: $last_char  SET PUNCTUATION:  #$last_word_punctuation#$this_word_punctuation#<br>";
            
        } else {
            $last_word_punctuation = $this_word_punctuation;
            $this_word_punctuation = false;
            //echo "$text last: $last_char  PUNCTUATION:  #$last_word_punctuation#$this_word_punctuation#<br>";
        }
        return array( $ret_pre, $ret_word, $ret_post );
}

function IsAnyOfAllArguments( $argument ) {
    global $rules, $actual_model, $rules_pointer;
    $length = count($rules["$actual_model"][$rules_pointer]);
    $output = false;
    for ($i=0; $i<$length; $i++) {
        if ($rules["$actual_model"][$rules_pointer][$i] === $argument) $output = TRUE;
    }
    return $output;
}

function PreProcessGlobalParserFunctions( $text ) {
        global $rules, $actual_model, $rules_pointer, $start_word_parser, $global_textparser_debug_string;
        $rules_pointer = 0;
        $global_textparser_debug_string = "";
        if (IsAnyOfAllArguments("@@txt")) {
            $temp_function = $rules["$actual_model"][$rules_pointer][1];
            while ($rules["$actual_model"][$rules_pointer][0] !== "EndFunction()") {
                $pattern = $rules["$actual_model"][$rules_pointer][0];
                $replacement = $rules["$actual_model"][$rules_pointer][1]; // only simple replacements are allowed for global parser ... 
                $temp_text = $text;
                $text = preg_replace( "/$pattern/", "$replacement", $text); // use only preg_replace (i.e. not extended_preg_replace)
                if ($temp_text !== $text) {
                    $nil = preg_match( "/$pattern/", $temp_text, $matches);
                    $matching_section = $matches[0];
                    $esc_pattern = htmlspecialchars($pattern);
                    $esc_replacement = htmlspecialchars($replacement);
                    $global_textparser_debug_string .= "<tr><td>(..)$matching_section(..)</td><td><b>R$rules_pointer</b> $esc_pattern <b>⇨</b> $esc_replacement</td><td>" . mb_strtoupper($temp_function) . "</td></tr>";
                }
                $rules_pointer++;
            //$text = preg_replace("/ es ist /", " [XEX] ", $text);
            //echo "TExt = $text<br>";
            //echo "first rule: " . $rules["$actual_model"][0][2] . "<br>";
            }
            // we are at the end of the global parser
            // to simplify: don't execute end of function arguments (just ignore them)
            // set $start_word_parser to rules_pointer++;
            $rules_pointer++;
            $start_word_parser = $rules_pointer;
        } else $start_word_parser = 0;
        //echo "start_word_parser = $start_word_parser<br>";
        return $text;
}

function MetaParser( $text ) {          // $text is a single word!
    global $font, $combiner, $shifter, $rules, $functions_table;
    global $std_form, $prt_form, $processing_in_parser, $separated_std_form, $separated_prt_form, $original_word;
    global $punctuation, $combined_pretags, $combined_posttags, $global_debug_string;
    global $safe_std;       // this global variable comes from database (in purgatorium1.php)
    global $last_pretoken_list, $last_posttoken_list;
    
    //echo "Textformat: " . $_SESSION['original_text_format'] . "<br>";
     $text_format = $_SESSION['original_text_format'];
     //$text_format = 'original';
    //$original_word = $text;
    if ($text_format === "prt") return $text; // no parsing
    elseif ($text_format === "std") { // partial parsing: std => prt
       // if text format is "standard" (std), then the variable $safe_std has to be set to std_form
       // $safe_std is either (1) set via database in purgatorium1.php1
       // or (2) - if the calculation is initiated from the maxi-form - 
       // it is the variable text (so in the latter case, set $safe_std = $text;
       //echo "calculate STD => PRT<br>";
       //$safe_std = (mb_strlen($safe_std) > 0) ? $safe_std : mb_strtoupper($text);
       $safe_std = mb_strtoupper($text);
       
       //echo "prt muss von std (#$safe_std#) berechnet werden<br>";
       //echo "safe_std: $safe_std<br>";
       //$prt_form = ParserChain( $temp_std ); 
       $prt_form = ParserChain( $safe_std ); 
       //$safe_std = "";//???????????
       //echo "prt: $prt_form<br>";
       
       return $prt_form;
    } else { // full parsing
       
        $text = preg_replace( '/\s{2,}/', ' ', ltrim( rtrim( $text )));         // eliminate all superfluous spaces
        //$text1 = GenericParser( $globalizer_table, $text ); // must be replaced!?
        $text1 = html_entity_decode( $text );    // do it here the hardcoded way
        $text2 = GetWordSetPreAndPostTags( $text1 );
        // $original_word = $text2;
        //echo "text: $text text1: $text1 text2: $text2<br>";
        list( $pretokens, $word, $posttokens ) = GetPreAndPostTokens( $text2 );
        // write pre/posttokens to global varables in order to use them from global parser
        $last_pretoken_list = $pretokens;
        $last_posttoken_list = $posttokens;
        
        switch ($_SESSION['token_type']) {
            case "shorthand": 
                $separated_word_parts_array = explode( "\\", /*GenericParser( $helvetizer_table, */ $word ); // helvetizer must be replaced 
                //var_dump($separated_word_parts_array);echo"<br";
                $output = ""; 
                $separated_std_form = "";
                $separated_prt_form = "";
                foreach ($separated_word_parts_array as $word_part ) {
                    $subword_array = explode( "|", $word_part ); // problem with this method is, that certain shortings (e.g. -en) will be applied at the end of a subword, while the shouldn't ... Workaround: add | at the end (that will be eliminated later shortly before transformation into token_list) ... (?!) seems to work for the moment, but keep an eye on that! Sideeffect: shortenings at the end won't be applied (this was intended at the beginning...) => rules must be rewritten with $ and | to mark end of words and subwords
                    foreach ($subword_array as $subword) { 
                        if ($subword !== end($subword_array)) $subword .= "|";
                        //echo "Metaparser(): subword = $subword<br>";
                        $output .= ParserChain( $subword );
                        //echo "Metaparser(): output = $output<br>";
                       
                        $separated_std_form .= $std_form;
                        $separated_prt_form .= $prt_form;
                    }
                    if ( $word_part !== end($separated_word_parts_array)) { 
                        $output .= "\\";  // shouldn't be hardcoded?!
                        $separated_std_form .= "\\";        // eh oui ... l'horreur continue ... ;-)
                        $separated_prt_form .= "\\";
                    }
                }
                if (mb_strlen($pretokens) > 0) { 
                    if (($pretokens === "{") || ($pretokens === "[")) {
                        $output = $pretokens . $output; // add { and [ without \\
                        $separated_std_form = $pretokens . $separated_std_form;     // do the same for std and prt form
                        $separated_prt_form = $pretokens . $separated_prt_form;
                    } else $output = "$pretokens\\" . "$output";    // not sure whether this is correct (needs same correction for std and prt as above?!?)
                }
                if (mb_strlen($posttokens) > 0) {
                    if ((mb_substr($posttokens, 0, 1) === "}") || (mb_substr($posttokens,0,1) === "]")) { // check only first char of posttokens, since there may be . ? ! afterwards (et l'horreur sous forme de greffes aléatoires continue ...;-))
                        $output .= $posttokens; // add } and ] without \\
                        $separated_std_form .= $posttokens;     // do the same for std and prt form
                        $separated_prt_form .= $posttokens;
                   
                    } else $output .= "\\$posttokens";
                }
                //$global_debug_string .= "STD: " . mb_strtoupper($separated_std_form) . "<br>PRT: $separated_prt_form<br>";
                //echo "metaparser: separated_std_form = $separated_std_form<br>";
                return $output;
            case "handwriting":
                $output = $word;
                $output = preg_replace( "/(?<![<>])([ABCDEFGHIJKLMNOPQRSTUVWXYZ]){1,1}/", "[#$1+]", $output ); // upper case
                $output = preg_replace( "/(?<![<>])([abcdefghijklmnopqrstuvwxyz]){1,1}/", "[#$1-]", $output ); // lower case
                $output = mb_strtoupper( $output );
                return $output;
/*
            case "htmlcode":
                $_SESSION['token_type'] = "shorthand";
                //return( $pre, $word, $post); 
                break; // break necessary? 
*/
        }
    }
    
}


////////////////////////////////////////////// end of parser functions ///////////////////////////////////


?>