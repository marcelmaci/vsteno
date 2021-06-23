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
require_once "linguistics.php";

/*
require_once "engine.php";
require_once "constants.php";
require_once "session.php";
*/
$act_word = "";
$word_at_beginning_of_function = "";
$actual_function = "";

function prepare_optimized_cache_array($text) {
    global $cached_results;
    $text_array = explode(" ", $text);
    foreach ($text_array as $word) {
        //echo "word: $word<br>";
        if (preg_match("/<.*?>/", $word) === 1) {} // don't cache html-tags
        else {
            if (isset($cached_results[$word])) $cached_results[$word]++;     // count number of ocurrencies
            else $cached_results[$word] = 0;
        }
    }
    //echo "prepared temp: <br>";
    //var_dump($cached_results);
    //echo "<br>end<br>";
    foreach ($cached_results as $word => $value) {
        //echo "word = $word<br>";
        if ($value === 0) unset($cached_results[$word]);  // don't create entry: only 1 occurrence (no worth caching)
        else {
            //echo "prepare >$word< for caching<br>";
            $cached_results[$word] = false; // prepare for caching: set entry to false (will be replaced by parsing result)
        }
    }
    //echo "prepared cached_results: <br>";
    //var_dump($cached_results);
    //echo "<br>end<br>";
}

function replace_all( $pattern, $replacement, $string ) {
/*    do { // extended_preg_replace repeats rule (= applies it 2x in order to avoid non application if contexts of condition overlap
        $old_string = $string; // so it is probably not necessary (and even "pernicious" (risk of infinite loop)) to include a while-loop here => watch this carefully (if errors ocurr in calculation, this might be the cause)
*/
        $string = extended_preg_replace( $pattern, $replacement, $string );
        
//    } while ($old_string !== $string );
    //echo "replace_all: string = $string";
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
        global $global_warnings_string, $avoid_empty_result;
        //if ($pattern === "/(\\)/") {
          //      echo "PATTERN: " . htmlspecialchars($pattern) . " REPLACEMENT: " . htmlspecialchars($replacement) . " STRING: " . htmlspecialchars($string) . "<br>";
        //}
        switch ($replacement) {
                // tried to replace $word[1] by mb_substr($word, 0, 1) - didn't work! (why?!)
                // characters with umlaut (ä,ö,ü) are not handled correctly, neither by strtoupper() nor strtolower() ! (BUG)
                // apparently both functions strtolower() and strtoupper work if used like so:
                // "([a-z]|ä|ö|ü)" => "strtoupper()";
                // "([a-z]|Ä|Ö|Ü)" => "strtolower()";
                // BUT: "(.*?)" => "strtoupper()"; DOESN'T WORK!
                // I was just curious: "(^.*?$)" => "strtoupper()"; WORKS! => REGEX, multi-byte string and UTF-8 combined with callback are a complete mystery to me ... :):):)
                case "strtolower()" : $result = preg_replace_callback( $pattern, function ($word) { return mb_strtolower($word[1], "UTF-8"); }, $string); 
                                      break;
                case "strtoupper()" : $result = preg_replace_callback( $pattern, function ($word) { return mb_strtoupper($word[1], "UTF-8"); }, $string); 
                                      break;
                default :  // found a really tricky aspect in regex: "overlapping contexts" ... found out that when two matches overlap,
                           // only one (!) is replaced. Example: rule = "([kr])([rp])" => "$1X$2", only "kXr" is generated ...
                           // 1st solution was to create a loop here and apply preg_replace until the result doesn't change any more ...
                           // But this leads to infinite loops with certain rules ... For example a => "[a]" will be applied eternally 
                           // (due to "autofeeding").
                           // The current workaround is to apply the rule twice (this might be enough to overcome short overlappings).
                           // Infinite loops are not possible if rule is repeated only once but the above rule a => [a] still produces
                           // unpleasant results (namely [[a]] instead of [a]).
                           // Workaround here: reformulate the rule as (?<!\[)a => [a]
                           // Maybe it's possible to solve this using lookahead expressions for the contexts that overlap (haven't 
                           // tried that for the moment, because the rules VSTENO uses for individual spacing are terribly complicated
                           // and it'd drive me crayzy to rewrite that regex_helper.php which creates those rules (they are so complicated
                           // I don't event want to write them by hand ... :-)
                           // CAVEAT: OBSERVE VERY WELL IF THIS "APPLY-TWICE" WORKAROUND HAS ANY UNPLEASANT SIDE EFFECTS!!!
                            $result1 = preg_replace( $pattern, $replacement, $string);
                            if ($result !== $string) $result = preg_replace( $pattern, $replacement, $result1);
                        break;
        }   
        //echo "extended_preg_replace: result = $result<br>";
        if (($result1 === "") && ($string !== "")) {
            $global_warnings_string .= "REGEX RETURNS EMPTY STRING: $pattern => $replacement<br>";
            if ($avoid_empty_result) $result="EMPTY STRING"; //$string;
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
            case "=:std" : //echo "=:std: #$result_after_last_rule# act_word = $act_word act_function: $actual_function rules_pointer=$rules_pointer<br>"; 
                            //$std_form = $result_after_last_rule; // this is wrong!!!! word "testword" contains no bundled [st]!!!
                            $std_form = $act_word; 
                            
                            break;
            case "=:prt" : /*echo "=:prt: #$result_after_last_rule#<br>";*/ $prt_form = $result_after_last_rule; break;
            //case "@@dic" :  // obsolete: replaced by stages
      /*          list($temp_std, $temp_prt) = Lookuper($act_word);
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
                } else {
                    //echo "word not found in dictionary!<br>";
                    //echo "LING PARAMETERS: " . $_SESSION['hyphenate_yesno'] . "-" . $_SESSION['composed_words_yesno'] . "-" . $_SESSION['composed_words_separate'] . "-" . $_SESSION['composed_words_glue'] . "-" .
                    // the problem here is that if LING finds composed words, they are not separated as an array (since this is done
                    // before in metaparser ... In other words: we deal here only with part of words and the character \ is treated
                    // as a character (not as a separator to generate an array)
                    // try to fix that:
                    // (1) by calling analize_word_linguistically in the metaparser giving it the argument "no hyphens" => if the word
                    //     has to be separated it will get separated from the beginning.
                    // (2) call analyze_word_linguistically once more here, but this time giving it the parameter "no composed words"
                    //     (meaning: it adds only hypens)
                    // Unfortunately this messes up completely with the dictionary => no idea how to solve that problem for the moment
                    // In addition: that didn't work ...
                    // If analyze_word_linguistically is called in metaparser both | and \ will produce separate words in the array.
                    // Consequence: words like "Andenken" will get tokenized correctly but then spacer rule cannot be applied (because
                    // "an" and "denken" are two separate parts. For \ this is not a problem (there will be space between the two parts
                    // anyway) but for | there's no possibility to decide how much space there must be added ...
                    // The momentary solution is to call analyze_words_linguistically in the metaparser (to get \) and then filter
                    // out | again ... 
                    // As a consequence, analyze_words_linguistically must be called again here including the composed word analysis.
                    // This is terrible for performance ... and messes up with | (now available as character in the string) and \ (not
                    // available as char since treated only as separator on a higher level).
                    // no idea how to fix this whole mess ... :-)
                    // language is complicated - really! :-)
                    // ok, found a solution:
                    // the whole parsing process is separated into "stages":
                    // - stage1: rules apply to the whole text
                    // - stage2: dictionary (things like the "helvetizer" must be done in stage1 now: in order to be safe for dictionary
                    //   call, no rules will be applied to words - except stage1!)
                    // - stage3: rules that apply to composed words (each word individually: for example Test\wort|schatz will be splitted
                    //   up into 3 parts and rules of stage3 will be applied entirely to any of them)
                    // - stage4: after stage3, the parser merges the parts together again, using | and \ als "glue" (so the information
                    //   of analyze_word_individually is preserved and can be used by stage4 or deleted; both tokens now appear as tokens
                    //   (chars) inside the string)
                    // how to mark begin and end of the stages (done in import_model.php):
                    // for the moment, only the beginning of the sections stage3 and stage4 can be marked by using "=:stage3" and
                    // "=:stage4" as a second or third argument in the #EndSubSection-statement. E.g: #EndSubSection(bundler,=:stage4) 
                    // marks the end of stage3 and the beginning of stage4 (next rule == stage4). This formalism is really stupid and
                    // imperfect, but it's the fastest way to implement the concept for now ... :-) (change it later to something more
                    // logic and "beautiful" ...)
                    // The limits for the stages
                    // - stage1: 0 - @@dic
                    // - stage2: no rules (call to Lookuper)
                    // - stage3: $rules_pointer_start_stage3 - $rules_pointer_start_stage4 (new global variables)
                    // - stage4: $rules_pointer_start_stage4 - count($rules[$actual_model])
                    // the good news: analyze_word_lingistically has to be called only once (directly in metaparser and not here any more,
                    // the call can be complete (with hyphens and composed words))
                    // UNCLEAR: up until now, =:std and =: were inside "composed words parsing" (= actual stage3)
                    // QUESTION: what happens if stage4 is define before =:std and =:prt?
                    // TEST: the parser seems to work, but no std and prt-information appears in debugging
                    // => if this is the only side effect, it would be easy to fix, BUT: let's observe this a little bit if it really is
                    // the only implication of an earlier stage4.
                    // earlier stage4 is needed because certain consonant-combinations occur even with word boundaries:
                    // Wohn|raum, Gast|recht, Erd|reich => [NR], [STR], [DR] must be bundled! (=> bundler)
                    // an earlier stage4 would benefit the performance (fewer cases have to be tested because words are not split up)
                    // Another modification:
                    // Linguistical analysis separates too many words, e.g.: sie|ben, wie|der, wer|den ...
                    // With the above concept, these errors could be corrected, but only in stage4 (where the word were already considerably transformed)
                    // Therefore, it would be better to have a stage immediately after LING and before stage3 that would allow to apply rules to
                    // whole words (like in stage4). In order not to be forced to renumerate the stages 3 and 4, we add 1 stage at the beginning
                    // (starting with 0):
                    // stage0: apply to whole text (preprocessing); 0 - @@dic (for the moment)
                    // stage1: no rules (call lookuper, if it fails: call LING)
                    // stage2: apply to whole word; $rules_pointer_start_stage2 - $rules_pointer_stage3
                    // other stages as above
                    // to do:
                    // variables $rules_pointer_start_stageX should be rewritten as a global array (e.g. $stages[$x])
                    // stage markers should not begin with =: (reserve that for variables that go to dictionary!)
                    // stage markers could be: #stageX 
                    // as STD and PRT a LIN variable should be created: LIN = result after linguistical analyzer
                    // there should be an entry LIN in the dictionary (elysium): user has the possibility to define
                    // each variable individually: only LIN, LIN + STD, LIN + STD + PRT (don't know if PRT really makes sense?!) 
                    // LIN can contain exceptions with respect to the linguistical analysis (something that is done for now via
                    // the new "corrector" function in stage2)
                    // NOTE: This "fully staged parser" concept is - in my opinion - the ultimate solution for all problems!
                    // It works great (even if the code, for the moment, is a whole mess ...)
                    
                    /*
                    $temp_word = $act_word;
                    $act_word = analyze_word_linguistically($act_word, $_SESSION['hyphenate_yesno'], $_SESSION['composed_words_yesno'], $_SESSION['composed_words_separate'], $_SESSION['composed_words_glue']);    
                    if ($temp_word !== $act_word) {
                        $parameters = "";
                        if ($_SESSION['hyphenate_yesno']) $parameters .= "syllables ";
                        if ($_SESSION['composed_words_yesno']) $parameters .= "words ";
                        if (mb_strlen($parameters) > 0) {
                            $parameters .= " / separate: " . $_SESSION['composed_words_separate'] . " glue: " . $_SESSION['composed_words_glue'];
                        }
                        if (mb_strlen($parameters) > 0) $parameters = "($parameters)";
                        
                        $global_debug_string .= "LING: $temp_word => $act_word $parameters<br>";
                        //var_dump($act_word);
                    }
                    */
//                }          */

                //break;
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
                    case "#>" : //if ($word_at_beginning_of_function !== $act_word) {                     // branch if not equal
                                    //$rules_pointer = $functions_table[$actual_model][$argument][0];
                                    
                                    $global_debug_string .= "<tr><td></td><td>--- " . mb_strtoupper($argument) . " ---</td><td>" . strtoupper($actual_function) . "</td></tr>";
                                    //$actual_function = $argument;
                                //}
                                break;
                }
        }
    }
}

function ExecuteBeginParameters() {
    global $rules, $rules_pointer;
    global $std_form, $prt_form, $separated_std_form, $separated_prt_form, $result_after_last_rule;
    global $original_word, $act_word, $word_at_beginning_of_function, $global_debug_string;
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
             //case "#>stage0" : break;
             //case "#>stage1" : break;
             //case "#>stage2" : break;
             case "#>stage3" :  $global_debug_string .= "<tr><td></td><td>--- STAGE3 ---</td><td>" . strtoupper($actual_function) . "</td></tr>";
                                break;
             case "#>stage4" :  $global_debug_string .= "<tr><td></td><td>--- STAGE4 ---</td><td>" . strtoupper($actual_function) . "</td></tr>";
                                break;
            //case "=:prt" : /*echo "=:prt: #$result_after_last_rule#<br>";*/ $prt_form = $result_after_last_rule; break;
        }
    }
}

function WrapStringAfterNCharacters($string, $n) {
    $output = ""; $position = 0;
    while (mb_strlen($string) > $n) {
        $output .= mb_substr($string, $position, $n) . "\n";
        $string = mb_substr($string, $n+1);
    }
    $output .= $string;
    return $output;
}

function CropStringAfterNCharacters($string, $n) {
    if ($n > mb_strlen($string)) return $string;
    else return mb_substr($string, 0, $n) . "[...]";
}

function CheckAndApplyHybridRule($condition1, $condition2, $consequence, $written, $phonetic) {
    // I know it's not good to (ab)use global variables instead of returning it via function ...
    // But I would have to adapt all function callers ... and I am honestly too lazy to do that now ...
    global $condition1_check, $condition2_check;
    $condition1_check = "-";
    //echo "Apply hybrid rule: $condition1 => { >$condition2<, >$consequence< } on test_form: >$written< and phonetic: >$phonetic<<br>";
    $result = null;
    if (preg_match("/$condition1/", $written)) {
        $condition1_check = "+"; // "\u{2713}"; // ok, if you don't like unicode, let's use good old + for matching conditions ... 
        $result = preg_replace("/$condition2/", "$consequence", $phonetic); 
        $condition2_check = ($result !== $phonetic) ? "+" : "-"; // check mark
    }
    //echo "Result: >$result<<br>";
    return $result;
}

function CheckTstopt(/* $option*/ $char_array) {
   // returns true if option matches, false if not (or null if option == null)
   
   // optimised faster version => even more optimised (using no variables but direct returns)
   // this function is called as often as ExecuteRule(), i.e. very often (in test example 144000 times)
   // optimisations have therefore a big impact on overall performance (10%)
   // CAVEAT: str_split() doesn't seem to be multibyte safe (shouldn't be a problem with options that are in the [0-9] single char range)
 //  if ($option === null) return null;
    if ($char_array === null) return null;
//   $char_array = str_split($option); // mb_str_split() doesn't work?!
   foreach($char_array as $char)
        if ($_SESSION["model_option$char" . "_yesno"]) 
            return true; // even more radical to break out of this loop .. ;-)
   return false;

/* intermediate version
//$option_result = false;
   // string-split optimisation instead of strlen() call and iteration via indexed loop and substr() calls
   $char_array = str_split($option); // mb_str_split() doesn't work?!
   foreach($char_array as $char) //{
        //$check_name = "model_option$char" . "_yesno";
        //echo "checkname: $check_name session: [" . $_SESSION["$check_name"] . "]<br>";
        if ($_SESSION["model_option$char" . "_yesno"]) 
        //{ 
            return true; // even more radical to break out of this loop .. ;-)
            //$option_result = true;
            //break; // optimise foreach loop: break out of loop as soon as first option matches (logical or)
        //}
   //} 
   //echo "return: $option_result<br>";
   return false;
   //return $option_result;
*/
/*
   // this is the original slower version (around 10% slower compared to overall performance)
   if ($option === null) return null;
   $len = mb_strlen($option);
   $option_result = false;
   for ($i=0; $i<=$len; $i++) {
        $check_name = "model_option" . mb_substr($option, $i, 1) . "_yesno";
        //echo "checkname: $check_name session: [" . $_SESSION["$check_name"] . "]<br>";
        if ($_SESSION["$check_name"]) $option_result = true;
   } 
   //echo "return: $option_result<br>";
   return $option_result;
*/
}

function OptionString($options_array) {
    return join("", $options_array);
}

// ExecuteRule replaces GenericParser from old parser
function ExecuteRule() {

    global $original_word, $result_after_last_rule, $global_debug_string, $global_number_of_rules_applied;
    global $rules, $rules_pointer, $actual_function, $rules_options;
    global $act_word, $original_word, $result_after_last_rule, $last_written_form, $parallel_lng_form, $condition1_check, $condition2_check;
    
    $actual_model = $_SESSION['actual_model'];
    $condition = $rules["$actual_model"][$rules_pointer][0];
    
    switch ($condition) {
        case "BeginFunction()" : ExecuteBeginParameters(); $output = $act_word; break;
        case "EndFunction()" : ExecuteEndParameters(); $output = $act_word; break;
        default : // normal rule
                // ***********************************************************************************************************************
                // extension: before testing anything (= multiple evolutions, hybrid or normal rule) check if rule contains options:
                //
                // if so: 
                // check first, if required option is selected:
                //      if so: execute rules as before
                //      if not: do not execute the rule
                // if not: execute rule as before
                //
                // tstopt(123) means: if one of the options is selected (logical or: 1 or 2 or 3)
                // ************************************************************************************************************************
                
                // set output and terminate early if options don't match
                $output = $act_word;
                if (CheckTstopt($rules_options["$actual_model"][$rules_pointer][0]) === false) break; 
               
                
                //$option_result = CheckTstopt($rules_options["$actual_model"][$rules_pointer][0]);
                //$option_test = $rules_options["$actual_model"][$rules_pointer][0] !== null;
                //$option_string = $rules_options["$actual_model"][$rules_pointer][1]; //OptionString($rules_options["$actual_model"][$rules_pointer][0]);
                //$option_show = $option_test;
                
                //echo "option_test: [$option_test] option_result: [$option_result] option_string: [$option_string]<br>";
                
                // these additional variables are not needed (or only for debugging?!)
                // eliminate them for performance reasons
                // $rule_condition = $condition; 
                // $simple_string = $condition;

                // otherwhise apply rule

                // find out which type of rule (single or multiple consequences)
                $length = count($rules["$actual_model"][$rules_pointer]);
                if ($length == 2) {
                    // normal rule: 1 condition => 1 consequence
                    $preceeding_result = $output;
                    //$pattern = $rules["$actual_model"][$rules_pointer][0];
                    $replacement = $rules["$actual_model"][$rules_pointer][1];
                    //$output = extended_preg_replace( "/$pattern/", $replacement, $output );
                    $output = extended_preg_replace( "/$condition/", $replacement, $output );
                    //echo "\nStandardProcedureForRule: pattern: #$pattern# => replacement: #$replacement#<br>word: $preceeding_result result: $output last: $result_after_last_rule<br>";
                    //echo "$pattern => $replacement // $preceeding_result => $output<br>";
                    if ($output !== $preceeding_result) {           // maybe wrong: should be $result_after_last_rule?!
                        $result_after_last_rule = $output;
                        $global_number_of_rules_applied++;
                        $_SESSION['rules_count'][$rules_pointer]++;
                        if ($_SESSION['output_format'] === "debug") {
                $option_result = CheckTstopt($rules_options["$actual_model"][$rules_pointer][0]);
                $option_test = $rules_options["$actual_model"][$rules_pointer][0] !== null;
                $option_string = $rules_options["$actual_model"][$rules_pointer][1]; //OptionString($rules_options["$actual_model"][$rules_pointer][0]);
                $option_show = $option_test;
                
                            //$wrapped_pattern = WrapStringAfterNCharacters($pattern, 30);
                            $wrapped_pattern = CropStringAfterNCharacters($condition, 20);
                            $option_debug_string = "";
                            if ($option_show && $option_result) $option_debug_string = "OPT: $option_string: ✓<br>"; // $condition replaces former $simple_string (also below)
                            //elseif (mb_strlen($simple_string)>0) $option_debug_string = "OPT: $simple_string: no<br>";
                            // optimize mb_strlen() call (slow)
                            elseif ($option_show && $option_string !== '') $option_debug_string = "OPT: $option_string: no<br>";
                            $global_debug_string .= "<tr><td><b>[$global_number_of_rules_applied]</b> $output </td><td><b>[R$rules_pointer]</b> $option_debug_string" . htmlspecialchars($wrapped_pattern) . " <b>⇨</b> " . htmlspecialchars($replacement) . "</td><td>" . strtoupper($actual_function) . "</td></tr>"; 
                        }
                    }
                //echo "GDS: $global_debug_string<br>";
                //echo "Match: word: $word output: $output FROM: rule: $pattern => $replacement <br>";
            
                } else {
                    // rule has several consequences
                    // special case: if phonetic transcription is selected condition can be tested on the written form of the word (instead of transcription)
                    // in that case, the following formalism is valid:
                    // "condition1" => { "condition2", "consequence" };
                    // with condition1 = tstwrt(condition)        applied to written form
                    //      condition2 = normal condition         applied to phonetic form
                    //      consequence = normal consequence      applied to phonetic form
                    // this will be called a "hybrid" rule (since it applies half to written, half to phonetic form)

                    // test if phonetic transcription is selected and if condition has to be tested on written form

                    //$match_wrt = preg_match("/^tstwrt\(/", $rules["$actual_model"][$rules_pointer][0]); // adapt for tstopt()
                    //$match_lng = preg_match("/^tstlng\(/", $rules["$actual_model"][$rules_pointer][0]); // adapt for tstopt() 
                    
                    //$match_wrt = preg_match("/^tstwrt\(/", $condition); // adapt for tstopt()
                    //$match_lng = preg_match("/^tstlng\(/", $condition); // adapt for tstopt()
                    // optimised
                    $match_wrt = $rules_options[$actual_model][$rules_pointer][2]; 
                    $match_lng = $rules_options[$actual_model][$rules_pointer][3]; 
                    
                    if (($_SESSION['phonetics_yesno']) && (($match_wrt) || ($match_lng))) {
                    
                        //echo "Rule($rules_pointer): has multiple consequences<br>";
                    
                        //echo "... phonetics is set and it's a hybrid rule<br>";
                        //echo "condition is: " . htmlspecialchars($condition) . "<br>";
                        
                        //$match_wrt = preg_match("/^tstwrt\(/", $condition); // adapt for tstopt()
                        //$match_lng = preg_match("/^tstlng\(/", $condition); // adapt for tstopt()
                        //echo "COMPARE<br>";
                        //echo "match_wrt: [$match_wrt] match_lng: [$match_lng]<br>";
                        //echo "rules_options: [" . $rules_options[$actual_model][$rules_pointer][2] . "] - [" . $rules_options[$actual_model][$rules_pointer][3] . "]<br>";
                        
                        // optimised
                        //$match_lng = $rules_options[$actual_model][$rules_pointer][2];
                        //$match_wrt = $rules_options[$actual_model][$rules_pointer][3];
                        /*
                        if ($match_wrt) {
                            echo "WRT-Rule($rules_pointer): " . $rules[$actual_model][$rules_pointer][0] . " => " . $rules[$actual_model][$rules_pointer][1] . "<br>"; 
                            //echo "Rules_options($rules_pointer)(1) = [" . $rules_options[$actual_model][$rules_pointer][0] . "]<br>";
                            echo "Rules_options($rules_pointer):"; var_dump($rules_options[$actual_model][$rules_pointer]); echo "<br>";
                    }
                    */
                        // chose wrt or lng form for hybrid rule (offering two variants for comparison)
                        // quantifier must be greedy for condition1 in order to go to the last ) !!!
                        
                        // invert order for performance (match_lng is more frequent case)
                        // do match_wrt only when needed
                        /*
                        if ($match_wrt) {
                            //echo "match_wrt: " . $rules["$actual_model"][$rules_pointer][0] . "<br>";
                            //$hybrid_condition1 = preg_replace("/(?:tstopt\([0-9]+\))?tstwrt\((.*)\)/", "$1", $rules["$actual_model"][$rules_pointer][0]);
                            // probably tstopt() can be left out
                            $hybrid_condition1 = preg_replace("/tstwrt\((.*)\)/", "$1", $rules["$actual_model"][$rules_pointer][0]);
                            $test_form = $last_written_form;
                            $hybrid_type = "H-WRT";
                        } else
                        */
                        if ($match_lng) {
                            //echo "match_lng: " . $rules["$actual_model"][$rules_pointer][0] . "<br>";
                            //$hybrid_condition1 = preg_replace("/(?:tstopt\([0-9]+\))?tstlng\((.*)\)/", "$1", $rules["$actual_model"][$rules_pointer][0]);
                            // probably tstopt() can be left out
                            //$hybrid_condition1 = preg_replace("/tstlng\((.*)\)/", "$1", $rules["$actual_model"][$rules_pointer][0]);
                            $hybrid_condition1 = $condition; //preg_replace("/tstlng\((.*)\)/", "$1", $condition);
                            $test_form = $parallel_lng_form;
                            $hybrid_type = "H-LNG";
                        } elseif ( /*preg_match("/^tstwrt\(/", $condition)*/ $match_wrt) {
                            //echo "match_wrt: " . $rules["$actual_model"][$rules_pointer][0] . "<br>";
                            //$hybrid_condition1 = preg_replace("/(?:tstopt\([0-9]+\))?tstwrt\((.*)\)/", "$1", $rules["$actual_model"][$rules_pointer][0]);
                            // probably tstopt() can be left out
                            //$hybrid_condition1 = preg_replace("/tstwrt\((.*)\)/", "$1", $rules["$actual_model"][$rules_pointer][0]);
                            $hybrid_condition1 = $condition; //preg_replace("/tstwrt\((.*)\)/", "$1", $condition);
                            $test_form = $last_written_form;
                            $hybrid_type = "H-WRT";
                        }
                    
                        //echo "hybrid_condition1: $hybrid_condition1<br>";
                        $hybrid_condition2 = $rules["$actual_model"][$rules_pointer][1];
                        $hybrid_consequence = $rules["$actual_model"][$rules_pointer][2];
                        $result = CheckAndApplyHybridRule($hybrid_condition1, $hybrid_condition2, $hybrid_consequence, $test_form, $act_word);
                        if ($result !== null) {
                            // result is valid
                            $output = $result;
                            $global_number_of_rules_applied++;
                            $_SESSION['rules_count'][$rules_pointer]++;
                            // set variables for debugging
                            //$pattern = "Hybrid[1] " . $hybrid_condition1 . " [2] " . $hybrid_condition2;
                            //$replacement = $hybrid_consequence;
                            
                            if ($_SESSION['output_format'] === "debug") {
                               $option_result = CheckTstopt($rules_options["$actual_model"][$rules_pointer][0]);
                $option_test = $rules_options["$actual_model"][$rules_pointer][0] !== null;
                $option_string = $rules_options["$actual_model"][$rules_pointer][1]; //OptionString($rules_options["$actual_model"][$rules_pointer][0]);
                $option_show = $option_test;
                            $option_debug_string = "";
                            if ($option_show && $option_result) $option_debug_string = "OPT: $option_string: ✓<br>"; // $simple_condition
                            //elseif (mb_strlen($simple_string)>0) $option_debug_string = "OPT: $simple_string: no<br>";
                            // optimize mb_strlen call (slow)
                            elseif ($option_show && $option_string !== '') $option_debug_string = "OPT: $option_string: no<br>";
                     
                               $global_debug_string .= "<tr><td><b>[$global_number_of_rules_applied]</b> $output </td><td><b>[R$rules_pointer]</b> $option_debug_string" . "$hybrid_type: $test_form<br>[1$condition1_check]: " . htmlspecialchars($hybrid_condition1) . "<br>[2$condition2_check]: " . htmlspecialchars($hybrid_condition2) . " <b>⇨</b> " . htmlspecialchars($hybrid_consequence) . "</td><td>" . strtoupper($actual_function) . "</td></tr>";
                            }
                        }
                    } else {
                        // apply "normal" rule as usual
                        //if ($rules_pointer == 43) echo "rule(43): " . $rules["$actual_model"][$rules_pointer][0] . " => " . $rules["$actual_model"][$rules_pointer][1] . "<br>";
                        //$pattern = $rules["$actual_model"][$rules_pointer][0];
                        // not necessary any more!?!
                        //$pattern = preg_replace("/^(tstopt\([0-9]+\))?(.*)$/", "$2", $rules["$actual_model"][$rules_pointer][0]); // adapt for options
                        $pattern = $rules["$actual_model"][$rules_pointer][0]; // adapt for options
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
                       
                                //if (mb_strlen($extra_pattern)>0) $result = preg_match( "/$extra_pattern/", $original_word );
                                // optimize mb_strlen (slow)
                                if ($extra_pattern !== "") $result = preg_match( "/$extra_pattern/", $original_word );
                                if ($result == 1) {  // exception matches
                                    $there_is_a_match = true;
                                    $matching_pattern = $extra_pattern;
                                    //echo "Match with: $extra_pattern in Original: $original_word result_after_last_rule: $result_after_last_rule<br>";
                                }
                            }
                
                            if ($there_is_a_match) {
                                //echo "Don't apply rule!<br>";
                                //$output = $result_after_last_rule; // $word; // don't apply rule (i.e. set $output back to $word) => Wrong! set it to result after last applied rule
                                // why must it be set to result_after_last_rule ... ??? this is wrong with "höhere" ... set it back to $word and keep an eye on that ...
                                $output = $word;
                                //echo "result after last rule: $result_after_last_rule word: $word<br>";
                                if ($_SESSION['output_format'] === "debug") {
                                    $option_result = CheckTstopt($rules_options["$actual_model"][$rules_pointer][0]);
                $option_test = $rules_options["$actual_model"][$rules_pointer][0] !== null;
                $option_string = $rules_options["$actual_model"][$rules_pointer][1]; //OptionString($rules_options["$actual_model"][$rules_pointer][0]);
                $option_show = $option_test;
                                        $option_debug_string = "";
                                        if ($option_show && $option_result) $option_debug_string = "OPT: $option_string: ✓<br>";
                                        //elseif (mb_strlen($simple_string)>0) $option_debug_string = "OPT: $simple_string: no<br>";
                                        // optimize mb_strlen (slow)
                                        elseif ($option_show && $option_string !== "") $option_debug_string = "OPT: $option_string: no<br>";
                                
                                        $global_debug_string .= "<tr><td><b>[X]</b> $output</td><td><b>[R$rules_pointer]</b> $option_debug_string" . htmlspecialchars($pattern) . " <b>⇨</b> { " . htmlspecialchars($rules["$actual_model"][$rules_pointer][1]) . ", ... }<br>NOT APPLIED: $matching_pattern (EXCEPTION)</td><td>" . strtoupper($actual_function) . "</td></tr>";
                                }
                            } else {
                                $global_number_of_rules_applied++;
                                $_SESSION['rules_count'][$rules_pointer]++;
                                $option_debug_string = "";
                                if ($_SESSION['output_format'] === "debug") {
                                    $option_result = CheckTstopt($rules_options["$actual_model"][$rules_pointer][0]);
                $option_test = $rules_options["$actual_model"][$rules_pointer][0] !== null;
                $option_string = $rules_options["$actual_model"][$rules_pointer][1]; //OptionString($rules_options["$actual_model"][$rules_pointer][0]);
                $option_show = $option_test;
                                if ($option_show && $option_result) $option_debug_string = "OPT: $option_string: ✓<br>";
                                //elseif (mb_strlen($simple_string)>0) $option_debug_string = "OPT: $simple_string: no<br>";
                                // optimize mb_strlen (slow)
                                elseif ($option_show && $option_string !== "") $option_debug_string = "OPT: $option_string: no<br>";
                                
                                    $global_debug_string .= "<tr><td><b>[$global_number_of_rules_applied]</b> $output </td><td><b>[R$rules_pointer]</b> $option_debug_string" . htmlspecialchars($pattern) . " <b>⇨</b> { " . htmlspecialchars($replacement) . ", ... }</td><td>" . strtoupper($actual_function) . "</td></tr>";
                                }
                            }
                        }
                    }
                }
    }
    //if ($output === "") echo "output = null / rule = $rules_pointer<br>";
    //echo "$output<br>";
    if ($output === "") $global_warnings_string .= "R[$rules_pointer]: RETURNS EMPTY STRING (\"" . $rules[$rules_pointer][0] . "\" => \"" . $rules[$rules_pointer][1] . ")<br>"; 
    $act_word = $output;
    //if ($result === "") {echo "return output: $output"; return $output;}
    //else { echo "return result $result"; return $result; }
   // return $output;
    //return $word;
}

function ParserChain( $text, $start = null, $end = null ) {
        global $rules, $functions_table, $rules_pointer;
        global $processing_in_parser, $rules_pointer_start_std2prt;
        global $original_word, $result_after_last_rule, $act_word, $start_word_parser;

        $processing_in_parser = "R"; // suppose word will been obtained by processing the rules

        // set rules pointer
        if ($start !== null) {
            $rules_pointer = $start;
            $act_word = $text;
        } elseif ($_SESSION['original_text_format'] === "std") {
            $rules_pointer = $rules_pointer_start_std2prt; // start with STD form (after bundler)
            $act_word = $text;
        } else $rules_pointer = $start_word_parser; // default

        if ($end !== null) $stop = $end;
        else $stop = $_SESSION['actual_model_number_of_rules']; // count($rules[$_SESSION['actual_model']]);
        
        $original_word = $text;
        $result_after_last_rule = $act_word;
        
        while ($rules_pointer < $stop) {
            ExecuteRule();
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
        global $rules, $actual_model, $rules_pointer, $start_word_parser, $global_textparser_debug_string, $global_debug_string, $rules_options;
        //echo "PreProcessGlobalParserFunctions():<br>"; 
        //echo "start_word_parser: $start_word_parser<br>";
        $rules_pointer = 0;
        $global_textparser_debug_string = "";
        if (IsAnyOfAllArguments("#>stage0")) {
            $temp_function = $rules["$actual_model"][$rules_pointer][1];
            while ($rules["$actual_model"][$rules_pointer][0] !== "EndFunction()") {
                
// ***************************** include tstopt() extension

/*
if (preg_match("/^tstopt\(([0-9]+)\).*$/", $rules["$actual_model"][$rules_pointer][0], $option_string) === 1) {
    $rule_condition = $rules["$actual_model"][$rules_pointer][0];
    $simple_string = $option_string[1];
    //echo "rule: $rule_condition contains an option: $simple_string<br>";
    $len = mb_strlen($simple_string);
    $option_result = false;
    for ($i=0; $i<=$len; $i++) {
        $check_name = "model_option" . mb_substr($simple_string, $i, 1) . "_yesno";
        //echo "checkname: $check_name session: [" . $_SESSION["$check_name"] . "]<br>";
        if ($_SESSION["$check_name"]) $option_result = true;
    }
    //echo "option check: [$option_result]<br>";
}
*/
//echo "Rule: $rules_pointer: " . $rules[$actual_model][$rules_pointer][0] . " => " . $rules[$actual_model][$rules_pointer][1] . "<br>";

//$option_to_test = $rules_options["$actual_model"][$rules_pointer][0];
$option_result = CheckTstopt($rules_options["$actual_model"][$rules_pointer][0]);
//echo "Rule ($rules_pointer): " . $rules["$actual_model"][$rules_pointer][0] . " => ... Option: [$option_to_test] Result: [$option_result]<br>";

$rule_condition = $rules["$actual_model"][$rules_pointer][0];
//$simple_string = $option_string[1];
   
//echo "Rule condition: $rule_condition<br>";

// set output before eventual break
$output = $act_word;
//if ($option_result === false) echo "BREAK EXPECTED!<br>";
if ($option_result === false) {
    $rules_pointer++;
    continue; // go to next rule if option doesn't match
}
//if ($option_result === false) echo "AFTER BREAK<br>";

// ********************************************************
                //$pattern = $rules["$actual_model"][$rules_pointer][0];
                // adapt for tstopt()
                // not necessary any more
                //$pattern = preg_replace("/^(tstopt\([0-9]+\))?(.*)$/", "$2", $rules["$actual_model"][$rules_pointer][0]); // adapt for options
                $pattern = $rules["$actual_model"][$rules_pointer][0]; 
                
                $replacement = $rules["$actual_model"][$rules_pointer][1]; // only simple replacements are allowed for global parser ... 
                $temp_text = $text;
                //$text = preg_replace( "/$pattern/", "$replacement", $text); // use only preg_replace (i.e. not extended_preg_replace)
                // originally, only preg_replace here (see line before)
                // test if extended_preg_replace works (otherwhise revert back)
                // extended_preg_replace is necessary in order to use strtolower() in global rules (stage0)
                //echo "preprocessing: rule: $rules_pointer text (before): $text";

                $text = extended_preg_replace( "/$pattern/", "$replacement", $text); // use only preg_replace (i.e. not extended_preg_replace)
                //echo " text (after): $text<br>";

                //echo "\"$pattern\" => \"$replacement\" // word: $temp_text => $text<br>";
                if ($temp_text !== $text) {
                    $nil = preg_match( "/$pattern/", $temp_text, $matches);
                    $matching_section = $matches[0];
                    $esc_pattern = htmlspecialchars($pattern);
                    $esc_replacement = htmlspecialchars($replacement);
                    // tstopt() extension
                    $option_debug_string = "";
                    if ($option_result) $option_debug_string = "OPT: $simple_string: ✓<br>";
                    elseif (mb_strlen($simple_string)>0) $option_debug_string = "OPT: $simple_string: no<br>";
        
                    if ($_SESSION['output_format'] === "debug") $global_textparser_debug_string .= "<tr><td>(..)$matching_section(..)</td><td><b>R$rules_pointer</b> $option_debug_string" . "$esc_pattern <b>⇨</b> $esc_replacement</td><td>" . mb_strtoupper($temp_function) . "</td></tr>";
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
        if ($_SESSION['output_format'] === "debug") {
            if (mb_strlen($global_textparser_debug_string)>0)
                echo "<br><br><b>#STAGE0:</b><br><div id='debug_table'><table><tr><td><b>STEPS</b></td><td><b>RULES</b></td><td><b>FUNCTIONS</b></td></tr><tr>$global_textparser_debug_string</table></div>";
            else
                echo "<br><br><b>#STAGE0:</b><br>no rules";
            echo "<br><br><b>#STAGES1234:</b><br>";
        }
        return $text;
}

function PostProcessDataFromLinguisticalAnalyzer($word) {
    global $analyzer, $analyzer_options; // contains postprocess-rules
    global $global_linguistical_analyzer_debug_string, $last_written_form, $parallel_lng_form, $condition1_check, $condition2_check;
    global $parallel_lng_form, $std_form, $original_word;
    //echo "PostProcessDataFromLinguisticalAnalyzer():<br>";
    
    $number_analyzer_rules = 0;
    $length = count($analyzer);
    for ($i=0; $i<$length; $i++) {
        // uses extended_preg_replace (i.e. strtolower()/strtoupper() can be used) but no extended formalism (i.e. no multiple consequences!!! (even if multiple consequences have been stored to $analyzer by import_model.php))
        //echo "postprocess: /" . $analyzer[$i][0] . "/ => " . $analyzer[$i][1] . " ($word / $last_written_form)<br>";
        //echo "A: Rule ($i): " . $analyzer[$i][0] . " => " . $analyzer[$i][1] . "<br>";

// special rule: (1 condition => several consequences &&) hybrid rule
// special case: if phonetic transcription is on condition can be tested on the written form of the word (instead of transcription)
// in that case, the following formalism is valid:
// "condition1" => { "condition2", "consequence" };
// with condition1 = tstwrt(condition)        applied to written form
//      condition2 = normal condition         applied to phonetic form
//      consequence = normal consequence      applied to phonetic form
// this will be called a "hybrid" rule (since it applies half to written, half to phonetic form)
// test if phonetical transcription is selected and if condition has to be tested on written form
// optimised (untested)
$match_wrt = $analyzer_options[$i][2]; // preg_match("/^tstwrt\(/", $analyzer[$i][0]);
$match_lng = $analyzer_options[$i][3]; // preg_match("/^tstlng\(/", $analyzer[$i][0]);
if (($_SESSION['phonetics_yesno']) && (($match_wrt) || ($match_lng))) {
    //echo "Analyzer[$i]: hybrid rule<br>";
    // quantifier must be greedy for condition1 in order to go to the last ) !!!
    // chose wrt or lng form for hybrid rule (offering to variants for comparison)
    if ($match_wrt) {
        // optimised (untested)
        $hybrid_condition1 = $analyzer[$i][0]; // preg_replace("/tstwrt\((.*)\)/", "$1", $analyzer[$i][0]);
        $test_form = $last_written_form;
        $hybrid_type = "H-WRT";
    } else if ($match_lng) {
        // optimised (untested)
        $hybrid_condition1 = $analyzer[$i][0]; // preg_replace("/tstlng\((.*)\)/", "$1", $analyzer[$i][0]);
        $test_form = $parallel_lng_form;
        $hybrid_type = "H-LNG";
    }
    //$hybrid_condition1 = preg_replace("/tstwrt\((.*)\)/", "$1", $analyzer[$i][0]);
    $hybrid_condition2 = $analyzer[$i][1];
    $hybrid_consequence = $analyzer[$i][2];
    $result = CheckAndApplyHybridRule($hybrid_condition1, $hybrid_condition2, $hybrid_consequence, $test_form, $word);
    //echo "result: $result<br>";
    
    if ($result !== null) {
        // result is valid
        $word = $result; // $word instead of $output
        //$global_number_of_rules_applied++;
        //$_SESSION['rules_count'][$rules_pointer]++;
        // set variables for debugging
        //$pattern = "Hybrid[1] " . $hybrid_condition1 . " [2] " . $hybrid_condition2;
        //$replacement = $hybrid_consequence;
         if ($_SESSION['output_format'] === "debug") $global_linguistical_analyzer_debug_string .= "<tr><td><b>[$number_analyzer_rules]</b> $word </td><td><b>[A$i]</b> $hybrid_type: $test_form<br>[1$condition1_check]: " . htmlspecialchars($hybrid_condition1) . "<br>[2$condition2_check]: " . htmlspecialchars($hybrid_condition2) . " <b>⇨</b> " . htmlspecialchars($hybrid_consequence) . "</td><td>LNG-POST</td></tr>";
        $number_analyzer_rules++;
       
    }

} else {
      // execute rest of the code as before 
       
       $old_word = $word;
        $condition = "/" . $analyzer[$i][0] . "/";
        //echo "execute rule($i): #" . $condition . "# => #" . $analyzer[$i][1] . "#<br>";
        $word = replace_all( $condition, $analyzer[$i][1], $word);
        //echo "word: $word<br>old_word: $old_word<br>";
        
        $len = count($analyzer[$i]);
        if (($word !== $old_word) && ($len>2)) {
            $not_applied_comment = "";
            //echo "multiple:<br>"; //var_dump($analyzer[$i]);
            // multiple consequences
            
            // traditional rule with multiple consequences: check if one of the multiple consequences matches
            $hit = false;
            $j = 2;
            while (($j<=$len-1) && (!$hit)) {
                if (preg_match("/" . $analyzer[$i][$j] . "/", $old_word)) $hit = true;
                //echo "check($i,$j): #" . $analyzer[$i][$j] . "# result: #$hit#<br>";
                if ($hit) { 
                    //echo "not applied<br>";
                    $not_applied_comment = "<b>NOT APPLIED (MATCH: " . $analyzer[$i][$j] . ")</b>";
                    $word = $old_word; // don't apply rule (revert back to $old_word)
                }
                $j++;
            }
            if ($_SESSION['output_format'] === "debug") {
                $wrapped_pattern = WrapStringAfterNCharacters($analyzer[$i][0], 30);
                $replacement = $analyzer[$i][1];
                $global_linguistical_analyzer_debug_string .= "<tr><td><b>[$number_analyzer_rules]</b> $word </td><td><b>[A$i]</b> " . htmlspecialchars($wrapped_pattern) . " <b>⇨</b> " . htmlspecialchars($replacement) . "<br>$not_applied_comment</td><td>LNG-POST</td></tr>"; 
                //if ($hit) $global_linguistical_analyzer_debug_string .= "<tr><td></td><td>NOT APPLIED</td><td></td></tr>"; 
                $number_analyzer_rules++;
            }     
        }
        //if ($hit) echo "rule not applied<br>";
        //else echo "rule applied<br>";
        
        if (($_SESSION['output_format'] === "debug") && ($old_word !== $word)) {
            //echo "modification: $old_word => $word (rule: $i)<br>";
            $wrapped_pattern = WrapStringAfterNCharacters($analyzer[$i][0], 30);
            $replacement = $analyzer[$i][1];
            $global_linguistical_analyzer_debug_string .= "<tr><td><b>[$number_analyzer_rules]</b> $word </td><td><b>[A$i]</b> " . htmlspecialchars($wrapped_pattern) . " <b>⇨</b> " . htmlspecialchars($replacement) . "</td><td>LNG-POST</td></tr>"; 
            $number_analyzer_rules++;
        }     
        //echo "result: $word<br>";
    }
} // end of hybrid rule postprocessing
    //echo "Word after postprocess: $word<br>";
    //$parallel_lng_form = $word;
    //echo "global_linguistical_analyzer_debut_string: $global_linguistical_analyzer_debug_string<br>";
    return $word;
}

//function ParserChainForComposed() {
    // this function is called by MetaParser in stage3 (= composed words have to be splitted and parsed individually)
    // the function only some sort of "wrapper": it calls ParserChain (with needed start and stop values)
    // all it does is split the words, call ParserChain individually (for each word) and packing them together
    // again (which means: ParserChainForComposed gets a string and returns a string - the whole splitting up
    // occurs inside of this function).
    // not necessary: do it directly inside MetaParser
//}

function MetaParser( $text ) {          // $text is a single word!
    global $font, $combiner, $shifter, $rules, $functions_table, $cached_result;
    global $std_form, $prt_form, $processing_in_parser, $separated_std_form, $separated_prt_form, $original_word, $lin_form;
    global $punctuation, $combined_pretags, $combined_posttags, $global_debug_string;
    global $safe_std;       // this global variable comes from database (in purgatorium1.php)
    global $last_pretoken_list, $last_posttoken_list, $rules_pointer_start_stage4, $rules_pointer_start_stage3, $rules_pointer_start_stage2, $rules_pointer_start_std2prt;
    global $cached_results;
    global $global_linguistical_analyzer_debug_string;
    global $parallel_lng_form, $last_written_form;
    global $caching_temporarily_disabled;
    global $only_pretokens, $only_posttokens, $punctuation;
    
    $global_linguistical_analyzer_debug_string = "";
    //echo "Metaparser(): $text / 'token_type' = " . $_SESSION['token_type'] . "<br>";
    // check if word has been cached
    //echo "isset: " . isset($cached_results[$text]) . " value: " . $cached_results[$text] . " ";
    
    // this is a nice example of how caching can go wrong - and produce errors that are very difficult to track.
    // In the sequence:
    //
    // <@token_type=shorthand>Dies ist ein Test.<@token_type=handwriting>Dies ist ein Test in Blockschrift.
    // <@token_type=shorthand>
    // 
    // Strangely, the first part in shorthand was correct, whereas in the second part the words "dies" and "ist" continued to
    // be written in shorthand. I first thougt, that the session variable token_type wasn't modified correctly (which could
    // be a problem coming from the parser), but actually this problem is due the fact, that "dies" and "ist" get cached as
    // shorthand words first and are then copy&pasted without considering that the token_type may have changed in the meantime.
    // The solution for now: limit caching strictly to shorthand words (see last AND in following if-statement)
    //
    // And here's another example (from the series "how caching can go wrong"):
    // When you calculate intermediate steps (like std and prt form), VSTENO catches the final result (replacing the intermediate
    // form by the catched final form from the second occurrence on ...)
    // Therefore implement possibility to dissable caching in this case via global variable $caching_temporarily_discabled

if (!$caching_temporarily_disabled) {
    $cached_result = false;
    if ((isset($cached_results[$text])) && ($cached_results[$text] !== false) && ($_SESSION['token_type'] === "shorthand")){
        //echo "<b>get cached: " . $cached_results[$text] . "</b><br>";
        // due to the global variables used throughout parser and engine, these must be set accordingly to get correct results ...
        switch ($_SESSION['output_format']) {
            case "meta_lng" : $lin_form = $cached_results[$text]; break;
            case "meta_std" : $std_form = $cached_results[$text]; $pretokens = ""; $posttokens = ""; $last_pretoken_list = ""; $last_posttoken_list = ""; $combined_pretags = ""; $combined_posttags = ""; 
                            break;
            case "meta_prt" : $prt_form = $cached_results[$text]; $pretokens = ""; $posttokens = ""; $last_pretoken_list = ""; $last_posttoken_list = ""; $combined_pretags = ""; $combined_posttags = ""; 
                            break;
        }
        $cached_result = true;
        //echo "cache hit: " . htmlspecialchars($cached_results[$text]) . "<br>";
        return $cached_results[$text];
    }
}    
    
    // this is a good place to lookup words!
    // after that branch to  std2prt oder stage4
    $text_format = $_SESSION['original_text_format'];
    if ($text_format === "normal") {   
            // this is a good place to lookup words!
            // after that branch to  std2prt oder stage4
            list($get_standard, $get_print) = Lookuper($text); // corresponds to stage1 (dictionary)
            //echo "dictionary (metaparser): $text std: $get_standard prt: $get_print<br>"; 
            //echo "stage4: $rules_pointer_start_stage4<br>";
            $safe_std = mb_strtoupper($get_standard, "UTF-8");
            $safe_prt = mb_strtoupper($get_print, "UTF-8");
            //echo "safe_std: $safe_std start: $rules_pointer_start_std2prt<br>";
    } elseif ($text_format === "lng") { 
            $safe_std = "";
            $safe_prt = "";
    } elseif ($text_format === "std") {
            $safe_std = ($_SESSION['token_type'] !== "handwriting") ? mb_strtoupper($text, "UTF-8") : $text;
            $safe_prt = "";
    } elseif ($text_format === "prt") {
            $safe_std = ""; 
            $safe_prt = ($_SESSION['token_type'] !== "handwriting") ? mb_strtoupper($text, "UTF-8") : $text;
            // mb_strtoupper($text, "UTF-8");
    }
//////////////////        
    if  ($safe_prt !== "") return $safe_prt;    // no parsing at all
    elseif ($safe_std !== "") {
        switch ($_SESSION['token_type']) {
            case "handwriting" : $final_prt = GetHandwriting($safe_std); break;
            default :
                // parse from std2stage4
                $std2stage4 = ParserChain($safe_std, $rules_pointer_start_std2prt, $rules_pointer_start_stage4);
                // parse from stage4 to end (= prt)
                //echo "go to stage4";
                $actual_model = $_SESSION['actual_model'];
                $final_prt = ParserChain($std2stage4, $rules_pointer_start_stage4, count($rules[$actual_model]));
            }
        return $final_prt;
    } else {
        // word is not in dictionary => parse from stage3 (= after dictionary) to stage4 (start) using word splitting (composed words)
        //echo "word is not in dictionary<br>";
        // first check if parsing is (partially) needed => is done above now
       
        
        //{ 
            // full parsing
            $text = preg_replace( '/\s{2,}/', ' ', trim( $text ));         // eliminate all superfluous spaces
            $text1 = html_entity_decode( $text );    // do it here the hardcoded way
            $text2 = GetWordSetPreAndPostTags( $text1 );
        
            //$text2 = preg_replace('/»/', '"', $text2);      // not sure if this is done in stage1 ?!
            //$text2 = preg_replace('/«/', '"', $text2);
        
            list( $pretokens, $word, $posttokens ) = GetPreAndPostTokens( $text2 );
            //echo "word: $word pretokens: $pretokens posttokens: $posttokens<br>";
            
            $last_pretoken_list = $pretokens;
            $last_posttoken_list = $posttokens;
        
            switch ($_SESSION['token_type']) {
                case "shorthand": 
//////////
 
      
                    if ($_SESSION['original_text_format'] !== "lng") {
                    
         //echo  $_SESSION['hyphenate_yesno'] . "<br>" . $_SESSION['composed_words_yesno'];
         
//if ((($_SESSION['hyphenate_yesno']) || ($_SESSION['composed_words_yesno'])) || ($_SESSION['phonetics_yesno']) && ($_SESSION['analysis_type'] === "selected")) {
if ($_SESSION['analysis_type'] === "selected") {
                    //echo "shorthand: $text<br>";
                    $temp_word = $text;
                    $pos1 = mb_strpos($text, "\\", 0, "UTF-8");
                    $pos2 = mb_strpos($text, "|", 0, "UTF-8");
                    // if $text contains \ or | from user input, consider that user wants to separate word manually, therefore only do hyphens (no analysis for composed words!)
                    //echo "pos12: $pos1, $pos2<br>";
                    //echo "stems_list: " . $_SESSION['stems_list'] . "<br>";
                    //echo "suffixes_list: " . $_SESSION['suffixes_list'] . "<br>";
                    //echo "session(block_list): " . $_SESSION['block_list'] . "<br>";
                    
                    // somewhere during Ariadne+ development captchas got broken (again ...)
                    // the bug seems to be in the following lines
                    // so to avoid it, introduce a session variable and avoid the code when calculating captchas ... 
                    // (ugly, yes ... :)
                    if ($_SESSION['captcha_processing'] === false){ 
                        if (($pos1 !== false) || ($pos2 !== false)) {
                            //echo "only do hyphens<br>";
                            $test = analyze_word_linguistically($word, $_SESSION['hyphenate_yesno'], false, $_SESSION['composed_words_separate'], $_SESSION['composed_words_glue'], $_SESSION['prefixes_list'], $_SESSION['stems_list'], $_SESSION['suffixes_list'], $_SESSION['block_list']);    
                        } else $test = analyze_word_linguistically($word, $_SESSION['hyphenate_yesno'], $_SESSION['composed_words_yesno'], $_SESSION['composed_words_separate'], $_SESSION['composed_words_glue'], $_SESSION['prefixes_list'], $_SESSION['stems_list'], $_SESSION['suffixes_list'], $_SESSION['block_list']);    
                    } else $test = $word;
                    //$test = preg_replace("/\|/", "", $test); // horrible ... filter out |, so that only \ from analizer will get separated ...
                    // write debug info
                  
                    // define parallel form
                    $parallel_form = (($_SESSION['phonetics_yesno']) && (($_SESSION['hyphenate_yesno']) || ($_SESSION['composed_words_yesno']))) ? $parallel_lng_form : $last_written_form;
                    $global_debug_string .= "LNG (par): $parallel_form<br>LNG (raw): $test<br>"; // => $test $parameters<br>"; 
                   
                    // now "post"process LING result applying analyzer rules from header (still stage1)
                    $lin_form = PostProcessDataFromLinguisticalAnalyzer($test);
                    // set lin_form
                    //$lin_form = $test;
                    //echo "lin_form: $lin_form<br>";
                    
                        
                    // write debug info of postprocessing: LING (post)
                    $global_debug_string .= "LNG (post): $lin_form<br>PRE: \"$pretokens\" - POST: \"$posttokens\"<br>";
                   
        
} else {
        //$lin_form = $text;
        $lin_form = $word;
        
        //echo "lin: $lin_form";
        $global_debug_string .= "PRE: \"$pretokens\" - POST: \"$posttokens\"<br>LNG: $lin_form (linguistical analysis disabled)<br>";

}
                   //echo "test: $test<br>";
                   
                    // calculate
                    $word = $lin_form;
                    
                    } else {
                        $lin_form = $word;
                        //echo "lin_form: $lin_form<br>";
                        //$word = $lin_form; // start directly with stage2 (= no linguistical analysis)
                    }
                    
                   
                    if ($_SESSION['output_format'] === "meta_lng") {
                        //echo "cache: $lin_form<br>"; 
                        if (isset($cached_results[$text])) $cached_results[$text] = $lin_form;
                        return $lin_form; // only execute following if formats as std and prt are needed (performance gain)
                    }
                
                
                   // first do stage2: parse entire word from stage2-stage3
                    //echo "start stage2: $rules_pointer_start_stage2 word: $word<br>";
                    $word = ParserChain( $word, $rules_pointer_start_stage2, $rules_pointer_start_stage3 );
                    //echo "result stage2: $word<br>";
                    ///////////////////////////////////////
                    $separated_word_parts_array = explode( "\\", $word ); // helvetizer must be replaced 
                    //var_dump($separated_word_parts_array);echo"<br";
                    $output = ""; 
                    $separated_std_form = "";
                    $separated_prt_form = "";
                    //echo "parallel_lng_form before stage3 separated parts: $parallel_lng_form<br>";
                    for ($w=0; $w<count($separated_word_parts_array); $w++) {
                        $word_part = $separated_word_parts_array[$w];
                        //var_dump($separated_word_parts_array);
                        $subword_array = explode( "|", $word_part ); // problem with this method is, that certain shortings (e.g. -en) will be applied at the end of a subword, while the shouldn't ... Workaround: add | at the end (that will be eliminated later shortly before transformation into token_list) ... (?!) seems to work for the moment, but keep an eye on that! Sideeffect: shortenings at the end won't be applied (this was intended at the beginning...) => rules must be rewritten with $ and | to mark end of words and subwords
                        for ($i=0; $i<count($subword_array); $i++) { 
                            $subword = $subword_array[$i];
                            //echo "test: $subword i: $i<br>";
                           // $separated_std_form = ""; // reset those global variables ... otherwhise parser will add them ... ???????
                           // $separated_prt_form = "";
                            //echo "stage3: before: $subword<br>";
                            $output = ParserChain( $subword, $rules_pointer_start_stage3, $rules_pointer_start_stage4 );
                            //echo "output (after): $output<br>";
                            //var_dump($subword_array);
                            //if ($i<count($subword_array)-1) 
                            $subword_array[$i] = $output;
                            //echo "<br>i: $i<br>";
                            //var_dump($subword_array);
                            //echo "Metaparser(): subword = $subword<br>";
                            //$output .= ParserChain( $subword, $rules_pointer_start_stage3, $rules_pointer_start_stage4 );
                            //echo "Metaparser(): output = $output<br>";
                       
                            $separated_std_form .= $std_form;
                            $separated_prt_form .= $prt_form;
                        }
                        //echo "<br>subword_array:<br>";
                        //var_dump($subword_array);
                        $word_part = implode("|", $subword_array);
                       
                        $separated_word_parts_array[$w] = $word_part;
                   
                    }
                    //echo "<br>end result: <br>";
                    //var_dump($separated_word_parts_array);
                    $output = implode("\\", $separated_word_parts_array);
                    //if ($output === "ne-men") var_dump($separated_word_parts_array);
                    //echo "output: [$output] pre: $pretokens post: $posttokens<br>";
                    // BUGFIX: when output is empty, pre- and posttokens aren't added (why?!)
                    // this is complete bullshit ... the bug comes from handwriting ... see further down => correct it there
                    // unfortunately, this might lead to bugs in handwriting again ...
                    // anyway it's not clear at this point how handwritten tokens are handled ?!
                    /*
                    if (mb_strlen($output)==0) { 
                        echo "length 0:<br>";
                        echo "output: [$output] pre: $pretokens post: $posttokens<br>";
                        $output = $pretokens . $output . $posttokens; 
                    }
                    */
                    //$global_debug_string .= "STD: " . mb_strtoupper($separated_std_form) . "<br>PRT: $separated_prt_form<br>";
                    //echo "metaparser: parserchain($rules_pointer_start_stage3, $rules_pointer_start_stage4)<br>";
                    //echo "metaparser: separated_std_form = $separated_std_form<br>";
                    //echo "metaparser: full form: $output<br>";
                   
                    // now do stage4 (full word)
                    
                    //$global_debug_string .= "begin stage4";
                    $actual_model = $_SESSION['actual_model'];
                    //echo "begin stage4: $rules_pointer_start_stage3-$rules_pointer_start_stage4-" . count($rules[$actual_model]) . "<br>";
                    
                    $output = ParserChain($output, $rules_pointer_start_stage4, count($rules[$actual_model]));
                    //echo "final: std/prt_form: $std_form / $prt_form<br>";
                    
                    // add pre/posttokens after all parsing is done
                    if (mb_strlen($pretokens) > 0) { 
                        if (($pretokens === "{") || ($pretokens === "[")) {
                            $output = $pretokens . $output; // add { and [ without \\
                            $separated_std_form = $pretokens . $separated_std_form;     // do the same for std and prt form
                            $separated_prt_form = $pretokens . $separated_prt_form;
                        } else { 
                             // special treatment for numbers in shorthand: transform to # + number + handwriting_marker inside []
                            // see BUGFIX comment above: do NOT trasnform pre-/posttokens here!!!
                            // $pre_handwriting = preg_match("/[0-9]/", $pretokens) ? "[#" . $pretokens . $_SESSION['handwriting_marker'] . "]" : $pretokens;
                            $pre_handwriting = $pretokens;
                            $output = "$pre_handwriting\\" . "$output";    // not sure whether this is correct (needs same correction for std and prt as above?!?)
                        }
                    }
                    if (mb_strlen($posttokens) > 0) {
                        if (preg_match("/" . preg_quote(mb_substr($posttokens, 0, 1)) . "/", $punctuation . $only_posttokens . $only_pretokens) === 1) {
                        //if ((mb_substr($posttokens, 0, 1) === "}") || (mb_substr($posttokens,0,1) === "]")) { // check only first char of posttokens, since there may be . ? ! afterwards (et l'horreur sous forme de greffes aléatoires continue ...;-))
                            $output .= $posttokens; // add } and ] without \\
                            $separated_std_form .= $posttokens;     // do the same for std and prt form
                            $separated_prt_form .= $posttokens;
                   
                        } else {
                            // special treatment for numbers in shorthand: transform to # + number + handwriting_marker inside []
                            // see BUGFIX comment above
                            //$post_handwriting = preg_match("/[0-9]/", $posttokens) ? "[#" . $posttokens . $_SESSION['handwriting_marker'] . "]" : $posttokens;
                            $post_handwriting = $posttokens;
                            $output .= "\\$post_handwriting";
                        }
                    }
                    // cache result
                    if (isset($cached_results[$text])) $cached_results[$text] = $output;
                    // echo "output: $output only_pretokens: $only_pretokens only_posttokens: $only_posttokens<br>";
                    return $output;
  
                    break;
                case "handwriting":
                
                    $output = GetHandwriting($word);
                    
                    /*
                    // **********************************************************************************************
                    // THE FOLLOWING LINES HAVE BEEN INTEGRATED IN FUNCTION GETHANDWRITING(WORD) AND CAN BE REPLACED 
                    // (JUST LEAVE THEM FOR THE MOMENT IN ORDER TO TEST THE FUNCTION)
                    // **********************************************************************************************
                    //echo "handwriting marker: " . $_SESSION['handwriting_marker'] . " output: $output<br>";
                    $output = $word;
                    // tokens without upper/lower case (+special cases)
                    //echo "before: $output<br>";
                    $output = preg_replace( "/(?<![<>])-{1,1}/", "[#-" . $_SESSION['handwriting_marker'] . "]", $output ); 
                    //$output = preg_replace( "/(?<![<>])([0-9]){1,1}/", "[#$1" . $_SESSION['handwriting_marker'] . "]", $output ); 
                    //echo "after: $output<br>";
                    // tokens with distinciton upper/lower case
                    $output = preg_replace( "/(?<![<>])([ABCDEFGHIJKLMNOPQRSTUVWXYZ]|Ä|Ö|Ü){1,1}/", "[#$1+" . $_SESSION['handwriting_marker'] . "]", $output ); // upper case
                    $output = preg_replace( "/(?<![<>])([abcdefghijklmnopqrstuvwxyz]|ä|ö|ü){1,1}/", "[#$1-" . $_SESSION['handwriting_marker'] . "]", $output ); // lower case
                    // prepare handwriting pretokens (hwpre)
                    // Example: ! => [#!0] (if marker is 0, so each token is inside [], preceeded by # and followed by marker)
                    $hwpre = "";
                    for ($p=0; $p<mb_strlen($pretokens); $p++) $hwpre .= "[#" . mb_substr($pretokens, $p, 1) . $_SESSION['handwriting_marker'] . "]";
                    // idem posttokens (hwpost)
                    $hwpost = "";
                    for ($p=0; $p<mb_strlen($posttokens); $p++) $hwpost .= "[#" . mb_substr($posttokens, $p, 1) . $_SESSION['handwriting_marker'] . "]";
                    
                    $output = $hwpre . mb_strtoupper( $output ) . $hwpost;
                    //echo "final result (handwriting): $output<br>(pretokens: [$pretokens] / posttokens: [$posttokens]) <br>(hwpre: $hwpre / hwpost: $hwpost)<br>";
                    //echo $_SESSION['output_format'];
                    */
                    /*if ($_SESSION['output_format'] === "debug")*/ $global_debug_string .= "Handwriting: $output<br><br>";
                    /*
                    /*elseif ($_SESSION['output_format'] === "meta_lng") {
                        echo "create std form for handwriting: $word<br>";
                        $output = $word;
                    }
                    */
                    
                    switch ($_SESSION['output_format']) {
                        // case "inline" : break; // default case: return $output
                        case "debug" : $global_debug_string .= "Handwriting: $output<br><br>"; break; // default case + debug info
                        case "meta_lng" : $output = $original_word; break; // for meta forms: don't process handwritten text parts
                        case "meta_std" : $std_form = $original_word; break;
                        case "meta_prt" : $prt_form = $original_word; break;
                        //case "layouted" : break; // default case (same as inline)
                    }
                
                    return $output;
            }


}

}

function GetHandwriting($word) {
    global $global_debug_string;
    //echo "handwriting marker: " . $_SESSION['handwriting_marker'] . " output: $output<br>";
    $output = $word;
    // tokens without upper/lower case (+special cases)
    //echo "before: $output<br>";
    $output = preg_replace( "/(?<![<>])-{1,1}/", "[#-" . $_SESSION['handwriting_marker'] . "]", $output ); 
    //$output = preg_replace( "/(?<![<>])([0-9]){1,1}/", "[#$1" . $_SESSION['handwriting_marker'] . "]", $output ); 
    //echo "after: $output<br>";
    // tokens with distinciton upper/lower case
    $output = preg_replace( "/(?<![<>])([ABCDEFGHIJKLMNOPQRSTUVWXYZ]|Ä|Ö|Ü|É|È|Ê|Ë|À|Á|Â|Í|Ì|Î|Ï|Ó|Ò|Ô|Ú|Ù|Û|Ç){1,1}/", "[#$1+" . $_SESSION['handwriting_marker'] . "]", $output ); // upper case
    $output = preg_replace( "/(?<![<>])([abcdefghijklmnopqrstuvwxyz]|ä|ö|ü|é|è|ê|ë|á|à|â|í|ì|î|ï|ó|ò|ô|ú|ù|û|ç){1,1}/", "[#$1-" . $_SESSION['handwriting_marker'] . "]", $output ); // lower case
    $marker = $_SESSION['handwriting_marker'];
    $output = preg_replace( "/(?<![<>])(\!|\?|\'|\"|\.|\,|\;|\:|\(|\)){1,1}/", "[#$1+$marker]", $output ); // lower case
    
    // prepare handwriting pretokens (hwpre)
    // Example: ! => [#!0] (if marker is 0, so each token is inside [], preceeded by # and followed by marker)
    $hwpre = "";
    for ($p=0; $p<mb_strlen($pretokens); $p++) $hwpre .= "[#" . mb_substr($pretokens, $p, 1) . $_SESSION['handwriting_marker'] . "]";
    // idem posttokens (hwpost)
    $hwpost = "";
    for ($p=0; $p<mb_strlen($posttokens); $p++) $hwpost .= "[#" . mb_substr($posttokens, $p, 1) . $_SESSION['handwriting_marker'] . "]";
                    
    $output = $hwpre . mb_strtoupper( $output ) . $hwpost;
    //echo "final result (handwriting): $output<br>(pretokens: [$pretokens] / posttokens: [$posttokens]) <br>(hwpre: $hwpre / hwpost: $hwpost)<br>";
    //echo $_SESSION['output_format'];
                    
    if ($_SESSION['output_format'] === "debug") {
        // following line doesn't work!?!
        $global_debug_string .= "Handwriting: $output<br><br>";
        // use this as a replacement
        // not possible ... only manual workaround
        //if ($_SESSION['token_type'] === "handwriting") echo "Handwriting: $output";
    }

    //echo "output: $output<br>";
    return $output;
}
////////////////////////////////////////////// end of parser functions ///////////////////////////////////


?>