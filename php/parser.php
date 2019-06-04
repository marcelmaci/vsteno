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
                            $result = preg_replace( $pattern, $replacement, $string);
                            if ($result !== $string) $result = preg_replace( $pattern, $replacement, $result);
                        break;
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
    /*
    if ($condition === "[^\|]den-") {
        echo "OBSERVE: >$condition< => >" . $rules["$actual_model"][$rules_pointer][1] . "< in $act_word<br>";
    }
    */
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
                    $_SESSION['rules_count'][$rules_pointer]++;
                    if ($_SESSION['output_format'] === "debug") {
                        //$wrapped_pattern = WrapStringAfterNCharacters($pattern, 30);
                        $wrapped_pattern = CropStringAfterNCharacters($pattern, 20);
                        $global_debug_string .= "<tr><td><b>[$global_number_of_rules_applied]</b> $output </td><td><b>[R$rules_pointer]</b> " . htmlspecialchars($wrapped_pattern) . " <b>⇨</b> " . htmlspecialchars($replacement) . "</td><td>" . strtoupper($actual_function) . "</td></tr>"; 
                    }
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
                        if ($_SESSION['output_format'] === "debug") $global_debug_string .= "<tr><td><b>[X]</b> $output</td><td><b>[R$rules_pointer]</b> " . htmlspecialchars($pattern) . " <b>⇨</b> { " . htmlspecialchars($rules["$actual_model"][$rules_pointer][1]) . ", ... }<br>NOT APPLIED: $matching_pattern (EXCEPTION)</td><td>" . strtoupper($actual_function) . "</td></tr>";
                    } else {
                        $global_number_of_rules_applied++;
                        $_SESSION['rules_count'][$rules_pointer]++;
                        if ($_SESSION['output_format'] === "debug") $global_debug_string .= "<tr><td><b>[$global_number_of_rules_applied]</b> $output </td><td><b>[R$rules_pointer]</b> " . htmlspecialchars($pattern) . " <b>⇨</b> { " . htmlspecialchars($replacement) . ", ... }</td><td>" . strtoupper($actual_function) . "</td></tr>";
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

function ParserChain( $text, $start = null, $end = null ) {
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
        if ($start !== null) {
            $rules_pointer = $start;
            $act_word = $text;
        }
        $actual_model = $_SESSION['actual_model'];
        if ($end !== null) $stop = $end;
        else $stop = count($rules[$actual_model]);
        
        
       //echo "set rules_pointer to $start_word_parser<br>";
        
        //$act_word = $text;
        
        $original_word = $text;
        $result_after_last_rule = $act_word;
        
        //$temp = isset($rules[$actual_model][$rules_pointer]);
        //echo "actual_model: $actual_model";
        //var_dump($rules);
        //$number_of_rules = count($rules[$actual_model]);
        //echo "number of rules: $number_of_rules rules_pointer: $rules_pointer<br>";
        //echo "ParserChain: Start: $rules_pointer Word: $act_word<br>";
        //echo "std_form: $std_form<br>";
        while ($rules_pointer < $stop) { // (isset($rules[$actual_model][$rules_pointer])) { // ($rules_pointer < 45) { // only apply 45 rules for test // 
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
        //echo "ParserChain: Stop: $stop Result: $act_word<br>";
        //echo "std_form: $std_form<br>";
        
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
        if (IsAnyOfAllArguments("#>stage0")) {
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
                    if ($_SESSION['output_format'] === "debug") $global_textparser_debug_string .= "<tr><td>(..)$matching_section(..)</td><td><b>R$rules_pointer</b> $esc_pattern <b>⇨</b> $esc_replacement</td><td>" . mb_strtoupper($temp_function) . "</td></tr>";
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

function PostProcessDataFromLinguisticalAnalyzer($word) {
    global $analyzer; // contains postprocess-rules
    global $global_linguistical_analyzer_debug_string;
    $number_analyzer_rules = 0;
    for ($i=0; $i<count($analyzer); $i++) {
        // uses extended_preg_replace (i.e. strtolower()/strtoupper() can be used) but no extended formalism (i.e. no multiple consequences!!! (even if multiple consequences have been stored to $analyzer by import_model.php))
        //echo "postprocess: /" . $analyzer[$i][0] . "/ => " . $analyzer[$i][1] . "($word)<br>";
        $old_word = $word;
        $word = replace_all( "/" . $analyzer[$i][0] . "/", $analyzer[$i][1], $word);
        
        /*
        if ($old_word !== $word) {
                echo "rule: " . $analyzer[$i][0] . " => " . $analyzer[$i][1] . " has been applied ($old_word => $word).<br>";
        } else {
          echo "no match: " . $analyzer[$i][0] . " => " . $analyzer[$i][1] . "<br>";
          
        }
        */
        
        if (($_SESSION['output_format'] === "debug") && ($old_word !== $word)) {
            //echo "modification: $old_word => $word (rule: $i)<br>";
            $wrapped_pattern = WrapStringAfterNCharacters($analyzer[$i][0], 30);
            $replacement = $analyzer[$i][1];
            $global_linguistical_analyzer_debug_string .= "<tr><td><b>[$number_analyzer_rules]</b> $word </td><td><b>[A$i]</b> " . htmlspecialchars($wrapped_pattern) . " <b>⇨</b> " . htmlspecialchars($replacement) . "</td><td>LNG-POST</td></tr>"; 
            $number_analyzer_rules++;
        }     
        //echo "result: $word<br>";
    }
    //echo "Word after postprocess: $word<br>";
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
    global $font, $combiner, $shifter, $rules, $functions_table;
    global $std_form, $prt_form, $processing_in_parser, $separated_std_form, $separated_prt_form, $original_word, $lin_form;
    global $punctuation, $combined_pretags, $combined_posttags, $global_debug_string;
    global $safe_std;       // this global variable comes from database (in purgatorium1.php)
    global $last_pretoken_list, $last_posttoken_list, $rules_pointer_start_stage4, $rules_pointer_start_stage3, $rules_pointer_start_stage2, $rules_pointer_start_std2prt;
    global $cached_results;
    global $global_linguistical_analyzer_debug_string;
    
    $global_linguistical_analyzer_debug_string = "";
    //echo "processing: $text => ";
    // check if word has been cached
    //echo "isset: " . isset($cached_results[$text]) . " value: " . $cached_results[$text] . " ";
    
    
    if ((isset($cached_results[$text])) && ($cached_results[$text] !== false)){
        //echo "<b>get cached: " . $cached_results[$text] . "</b><br>";
        // due to the global variables used throughout parser and engine, these must be set accordingly to get correct results ...
        switch ($_SESSION['output_format']) {
            case "meta_lng" : $lin_form = $cached_results[$text]; break;
            case "meta_std" : $std_form = $cached_results[$text]; $pretokens = ""; $posttokens = ""; $last_pretoken_list = ""; $last_posttoken_list = ""; $combined_pretags = ""; $combined_posttags = ""; 
                            break;
            case "meta_prt" : $prt_form = $cached_results[$text]; $pretokens = ""; $posttokens = ""; $last_pretoken_list = ""; $last_posttoken_list = ""; $combined_pretags = ""; $combined_posttags = ""; 
                            break;
        }
        return $cached_results[$text];
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
            $safe_std = mb_strtoupper($text, "UTF-8");
            $safe_prt = "";
    } elseif ($text_format === "prt") {
            $safe_std = ""; 
            $safe_prt = mb_strtoupper($text, "UTF-8");
    }
        
    if  ($safe_prt !== "") return $safe_prt;    // no parsing at all
    elseif ($safe_std !== "") {
        // parse from std2stage4
        $std2stage4 = ParserChain($safe_std, $rules_pointer_start_std2prt, $rules_pointer_start_stage4);
        // parse from stage4 to end (= prt)
        //echo "go to stage4";
        $actual_model = $_SESSION['actual_model'];
        $final_prt = ParserChain($std2stage4, $rules_pointer_start_stage4, count($rules[$actual_model]));
        return $final_prt;
    } else {
        // word is not in dictionary => parse from stage3 (= after dictionary) to stage4 (start) using word splitting (composed words)
        //echo "word is not in dictionary<br>";
        // first check if parsing is (partially) needed => is done above now
        /*
        $text_format = $_SESSION['original_text_format'];
        if ($text_format === "prt") return $text; // no parsing
        elseif ($text_format === "std") { 
            // partial parsing: std => prt
            $safe_std = mb_strtoupper($text, "UTF-8");
            $std_form = $safe_std; // not sure if this variable has to be set to get infos in the debugger?!
            $std2stage4 = ParserChain($safe_std, $rules_pointer_start_std2prt, $rules_pointer_start_stage4);
            // parse from stage4 to end (= prt)
            //echo "go to stage4";
            $actual_model = $_SESSION['actual_model'];
            $prt_form = ParserChain($std2stage4, $rules_pointer_start_stage4, count($rules[$actual_model]));
            return $prt;
        } else 
        */ 
        
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
                    
                    if ($_SESSION['original_text_format'] !== "lng") {
                    
         //echo  $_SESSION['hyphenate_yesno'] . "<br>" . $_SESSION['composed_words_yesno'];
         
if (($_SESSION['hyphenate_yesno']) || ($_SESSION['composed_words_yesno'])) {
                    //echo "shorthand: $text<br>";
                    $temp_word = $text;
                    $pos1 = mb_strpos($text, "\\", 0, "UTF-8");
                    $pos2 = mb_strpos($text, "|", 0, "UTF-8");
                    // if $text contains \ or | from user input, consider that user wants to separate word manually, therefore only do hyphens (no analysis for composed words!)
                    //echo "pos12: $pos1, $pos2<br>";
                    //echo "stems_list: " . $_SESSION['stems_list'] . "<br>";
                    //echo "suffixes_list: " . $_SESSION['suffixes_list'] . "<br>";
                    if (($pos1 !== false) || ($pos2 !== false)) {
                        //echo "only do hyphens<br>";
                        $test = analyze_word_linguistically($word, $_SESSION['hyphenate_yesno'], false, $_SESSION['composed_words_separate'], $_SESSION['composed_words_glue'], $_SESSION['prefixes_list'], $_SESSION['stems_list'], $_SESSION['suffixes_list']);    
                    } else $test = analyze_word_linguistically($word, $_SESSION['hyphenate_yesno'], $_SESSION['composed_words_yesno'], $_SESSION['composed_words_separate'], $_SESSION['composed_words_glue'], $_SESSION['prefixes_list'], $_SESSION['stems_list'], $_SESSION['suffixes_list']);    
                    //$test = preg_replace("/\|/", "", $test); // horrible ... filter out |, so that only \ from analizer will get separated ...
                    // write debug info
                    $parameters = "";
                    if ($_SESSION['hyphenate_yesno']) $parameters .= "syllables ";
                    if ($_SESSION['composed_words_yesno']) $parameters .= "words ";
                    if (mb_strlen($parameters) > 0) {
                        $parameters .= " / separate: " . $_SESSION['composed_words_separate'] . " glue: " . $_SESSION['composed_words_glue'];
                    }
                    if (mb_strlen($parameters) > 0) $parameters = "($parameters)";
                    $global_debug_string .= "LNG (raw): $temp_word => $test $parameters<br>"; 
                    // now "post"process LING result applying analyzer rules from header (still stage1)
                    $lin_form = PostProcessDataFromLinguisticalAnalyzer($test);
                    // set lin_form
                    //$lin_form = $test;
                    //echo "lin_form: $lin_form<br>";
                        
                    // write debug info of postprocessing: LING (post)
                    $global_debug_string .= "LNG (post): $lin_form<br>";
} else {
        $lin_form = $text;
        //echo "lin: $lin_form";
        $global_debug_string .= "LNG: $lin_form (linguistical analysis disabled)<br>";
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
                    $separated_word_parts_array = explode( "\\", /*GenericParser( $helvetizer_table, */ $word ); // helvetizer must be replaced 
                    //var_dump($separated_word_parts_array);echo"<br";
                    $output = ""; 
                    $separated_std_form = "";
                    $separated_prt_form = "";
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
                        /*
                        echo "word_part: $word_part<br>";
                        echo "separated_std_form: $separated_std_form<br>";
                        echo "separated_prt_form: $separated_prt_form<br>";
                        echo "std_form: $std_form<br>";
                        echo "prt_form: $prt_form<br>";
                        */
                        $separated_word_parts_array[$w] = $word_part;
                    /*
                        if ( $word_part !== end($separated_word_parts_array)) { 
                            $output .= "\\";  // shouldn't be hardcoded?!
                            $separated_std_form .= "\\";        // eh oui ... l'horreur continue ... ;-)
                            $separated_prt_form .= "\\";
                        }
                    */
                    }
                    //echo "<br>end result: <br>";
                    //var_dump($separated_word_parts_array);
                    $output = implode("\\", $separated_word_parts_array);
                    //if ($output === "ne-men") var_dump($separated_word_parts_array);
                    
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
                        } else $output = "$pretokens\\" . "$output";    // not sure whether this is correct (needs same correction for std and prt as above?!?)
                    }
                    if (mb_strlen($posttokens) > 0) {
                        if ((mb_substr($posttokens, 0, 1) === "}") || (mb_substr($posttokens,0,1) === "]")) { // check only first char of posttokens, since there may be . ? ! afterwards (et l'horreur sous forme de greffes aléatoires continue ...;-))
                            $output .= $posttokens; // add } and ] without \\
                            $separated_std_form .= $posttokens;     // do the same for std and prt form
                            $separated_prt_form .= $posttokens;
                   
                        } else $output .= "\\$posttokens";
                    }
                    // cache result
                    if (isset($cached_results[$text])) $cached_results[$text] = $output;
                    return $output;
  
                    break;
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
            
        //}
    }
}


////////////////////////////////////////////// end of parser functions ///////////////////////////////////


?>