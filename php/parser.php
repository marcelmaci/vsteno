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

function replace_all( $pattern, $replacement, $string ) {
    do {
        $old_string = $string;
        $string = preg_replace( $pattern, $replacement, $string );
    } while ($old_string !== $string );
    return $string;
}

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
                    $global_debug_string .= "NOT APPLIED: rule: " . htmlspecialchars($pattern) . " => " . htmlspecialchars($table[$pattern][0]) . " REASON: pattern: $matching_pattern matches in $original_word<br>";
                } else {
                    $global_number_of_rules_applied++;
                    $global_debug_string .= "[$global_number_of_rules_applied] WORD: $output FROM: rule: " . htmlspecialchars($pattern) . " => " . htmlspecialchars($replacement) . "<br>";
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
                $global_debug_string .= "[$global_number_of_rules_applied] WORD: $output FROM: rule: " . htmlspecialchars($pattern) . " => " . htmlspecialchars($replacement) . "<br>"; 
                
                //echo "GDS: $global_debug_string<br>";
                //echo "Match: word: $word output: $output FROM: rule: $pattern => $replacement <br>";
            }
        }
    }
    
     return $output;
}

///////////////////////////////////////////// parser functions ////////////////////////////////////////////////

// general philosophy for parser:
// (1) divide et impera! Divide parsing task into different subtasks corresponding to linguisticly logical steps
// (e.g. mark affixes, correct different representation of same phonetics etc.)
// (2) KISS (keep it stupid, simple): one function = one (simple and basic) task!
/*
// globalizer (= full text scanner, applied before any other operation)
function Globalizer( $word ) {
    global $globalizer_table;
    $output = $word;
    foreach ( $globalizer_table as $pattern => $replacement ) {
        $output = preg_replace( "/$pattern/", $replacement, $output );
    }
    return $output;
}

// helvetizer
function Helvetizer( $word ) {
    global $helvetizer_table;
    $output = $word;
    foreach ( $helvetizer_table as $pattern => $replacement ) {
        $output = preg_replace( "/$pattern/", $replacement, $output );
    }
    return $output;
}
*/

// decapitalizer: can't be replaced with GenericParser! (?)
// Can be replaced: REGEX: ([A-Z]) => \L$1 IN PHP: ???
// Idem for strtoupper: REGEX: ([a-z]) => \U$1 IN PHP: ???
// must be refined for special characters (äöü etc.)
function Decapitalizer( $word ) {
    $output = mb_strtolower($word);
    return $output;
}

/*
function Substituter( $word ) {
    global $substituter_table;
    $output = $word;
    foreach ( $substituter_table as $pattern => $replacement ) {
       $output = preg_replace( "/$pattern/", $replacement, $output );
    }
    return $output;
}

// normalizer
function Normalizer( $word ) {
    global $normalizer_table;
    $output = $word;
    foreach ( $normalizer_table as $pattern => $replacement ) {
        $output = preg_replace( "/$pattern/", $replacement, $output );
    }
    return $output;
}

// bundler
function Bundler( $word ) {
    global $bundler_table;
    $output = $word;
    foreach ( $bundler_table as $pattern => $replacement ) {
        $output = preg_replace( "/$pattern/", $replacement, $output );
    }
    return $output;
}

// shortener
function Shortener( $word ) {
    global $shortener_table;
    $output = $word;
    foreach ( $shortener_table as $pattern => $replacement ) {
        $output = preg_replace( "/$pattern/", $replacement, $output );
    }
    return $output;
}
*/

// trickster: functions like dictionary, but word goes to parserchain afterwards
function Trickster( $word ) {
    global $trickster_table;
    $output = $word;
    foreach ( $trickster_table as $pattern => $replacement ) {
        $output = preg_replace( "/$pattern/", $replacement, $output ); //echo "trickster-pattern: $pattern word: $word output: $output<br>";
    }
    if (strcmp($word, $output) == 0) return ""; // if no trickster rule was applied return "" to tell metaparser to apply normal parserchain
    else return $output; // if there was a match in trickster return it in order to tell metaparser to apply reduced parserchain (i.e. without decapitalizer)
}

/*
// filter
function Filter( $word ) {
    global $filter_table;
    $output = $word;
   // $word = str_replace('»', "", $string);
   // $word = str_replace('«', "", $string);
    foreach ( $filter_table as $pattern => $replacement ) {
        $output = preg_replace( "/$pattern/", $replacement, $output );
    }
    return $output;
}

// transcriptor
function Transcriptor( $word ) {
    global $transcriptor_table;
    $output = $word;
    foreach ( $transcriptor_table as $pattern => $replacement ) {
        $output = preg_replace( "/$pattern/", $replacement, $output );
    }
    return $output;
}
*/

// lookuper (checks if word is in dictionary)
/* old version
function Lookuper( $word ) {
    global $dictionary_table;
    $original_result =  $dictionary_table[ $word ];
    if (mb_strlen( $original_result ) > 0) return $original_result;
    else {
        $lower_result = $dictionary_table[ mb_strtolower($word)];
        if (mb_strlen( $lower_result ) > 0) return $lower_result; // empty string is returned automatically if no entry is found // good idea to convert to lower case ... ?!?
    }
}
*/

function Lookuper( $word ) {
    $conn = Connect2DB();
    // Check connection
    if ($conn->connect_error) {
        die("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
    }
    // prepare data
    $safe_word = $conn->real_escape_string( $word );
    $sql = "SELECT * FROM elysium WHERE word='$safe_word'";
    //echo "Elysium: query = $sql<br>";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        //echo "Wort: " . $row['word'] . " in Datenbank gefunden. Rückgabe: " . $row['single_prt'] . "<br>";
        // BUG: when word is found in dictionary, no combined/shifted-tokens are displayed => WHY???
        return $row['single_prt'];
    } else return "";    
}

// metaparser: combines all the above parsers
function ParserChain( $text ) {
        global $globalizer_table, /*$trickster_table, $dictionary_table,*/ $filter_table, $shortener_table, $normalizer_table, 
            $bundler_table, $transcriptor_table, $substituter_table, $std_form, $prt_form, $processing_in_parser;
        // test if word is in dictionary: if yes => return immediately and avoid parserchain completely (= word will be transcritten directly by steno-engine
        $processing_in_parser = "R"; // suppose word will been obtained by processing the rules
        $result = Lookuper( $text ); // can't be replaced with GenericParser => will be database-function
        
        if ( mb_strlen($result) > 0 ) {
            $processing_in_parser = "D";  // mark word as taken from dictionary (will be replaced with database functions later)
            $prt_form = $result;
            return $result;
        }
        // if there is no entry in the dictionary: try trickster first (befory applying parserchain)
        // if trickster returns a result, then avoid decapitalizer (trickster needs capital letter to distinguish between certain words, avoiding decapitalizing
        // gives the trickster the possibility to mark certain parts of the words as capitals (so they won't get treated by certain rules of the parser chain))
        $result = Trickster( $text ); // can't be replaced with GenericParser! (?)
        /* old version
        if ( mb_strlen($result) > 0 ) return Substituter( Transcriptor( Bundler( Normalizer( Shortener( Filter( $result )))))); // don't apply decapitalizer
        else return Substituter( Transcriptor( Bundler( Normalizer( Shortener( Decapitalizer( Filter(( $text )))))))); // apply normal parserchain on original word
        */
        
        if ( mb_strlen($result) > 0 ) {
            
            $std_form = GenericParser( $bundler_table, GenericParser( $normalizer_table, GenericParser( $shortener_table, GenericParser( $filter_table, $result))));
            $prt_form = GenericParser( $substituter_table, GenericParser( $transcriptor_table, $std_form ));
            $result = $prt_form;
            return $result;
            // return Substituter( Transcriptor( Bundler( Normalizer( Shortener( Filter( $result )))))); // don't apply decapitalizer
        
        } else {
            
            $std_form = GenericParser( $bundler_table, GenericParser( $normalizer_table, GenericParser( $shortener_table, Decapitalizer( GenericParser( $filter_table, $text)))));
            $prt_form = GenericParser( $substituter_table, GenericParser( $transcriptor_table, $std_form ));
            $result = $prt_form;
            return $result;
            
            //return Substituter( Transcriptor( Bundler( Normalizer( Shortener( Decapitalizer( Filter(( $text )))))))); // apply normal parserchain on original word
        }
}

function GetPreAndPostTokens( $text ) {
        // Separates pre- und posttokens, $text must be middle part of "word", i.e. original word without pre- and posttags (must be separated
        // by GetPreAndPostTags() first
        // Returns: array($pretokens, $pureword, $posttokens) 
        global $pretokenlist, $posttokenlist;
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
        return array( $ret_pre, $ret_word, $ret_post );
}

function MetaParser( $text ) {          // $text is a single word!
global $globalizer_table, /*$trickster_table, $dictionary_table,*/ $filter_table, $shortener_table, $normalizer_table, 
$bundler_table, $transcriptor_table, $substituter_table, $std_form, $prt_form, $processing_in_parser, $separated_std_form, $separated_prt_form;
       
        global $punctuation, $combined_pretags, $combined_posttags, $globalizer_table, $helvetizer_table;
//////// metaparser should distinguish between normal text and metaform (that doesn't need - or only partial - parsing)! !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
if ($_SESSION['original_text_format'] === "prt") return $text; // no parsing
elseif ($_SESSION['original_text_format'] === "std") { // partial parsing: std => prt
       $std_form = $text;
       $prt_form = GenericParser( $substituter_table, GenericParser( $transcriptor_table, $std_form )); 

       return $prt_form;
} else {
        $text = preg_replace( '/\s{2,}/', ' ', ltrim( rtrim( $text )));         // eliminate all superfluous spaces
        //echo "GenericParser: text before: $text<br>";
        $text1 = GenericParser( $globalizer_table, $text ); // Globalizer( $word );
        //echo "GenericParser: text after: $text1<br>";
        
        //echo "EntityDecode: text before: $text1<br>";
        $text1 = html_entity_decode( $text1 );    // do it here the hardcoded way
        //echo "EntityDecode: text after: $text1<br>";
        
        //echo "text: #$text#<br>";
        $text2 = GetWordSetPreAndPostTags( $text1 );
        //echo "Metaparser(): Word: $word<br>";
        //$text2 = GenericParser( $globalizer_table, $text1 ); // Globalizer( $word );
        
        //echo "\nText aus Metaparser() nach Globalizer: $text1 <br>nach Getwordsetpreandposttags: $text2<br>\n";
        list( $pretokens, $word, $posttokens ) = GetPreAndPostTokens( $text2 );
        //echo "Metaparser: pretokens: $pretokens posttokens: $posttokens<br>";
        
        switch ($_SESSION['token_type']) {
            case "shorthand": 
                $separated_word_parts_array = explode( "\\", GenericParser( $helvetizer_table, $word ));  // Helvetizer($word) );
                //var_dump($separated_word_parts_array);echo"<br";
                $output = ""; 
                $separated_std_form = "";
                $separated_prt_form = "";
                foreach ($separated_word_parts_array as $word_part ) {
                    //echo "Metaparser(): Wordpart: $word_part<br>";
                    $subword_array = explode( "|", $word_part ); // problem with this method is, that certain shortings (e.g. -en) will be applied at the end of a subword, while the shouldn't ... Workaround: add | at the end (that will be eliminated later shortly before transformation into token_list) ... (?!) seems to work for the moment, but keep an eye on that! Sideeffect: shortenings at the end won't be applied (this was intended at the beginning...) => rules must be rewritten with $ and | to mark end of words and subwords
                    //var_dump($subword_array);echo"<br>"; 
                    foreach ($subword_array as $subword) { 
                        if ($subword !== end($subword_array)) $subword .= "|";
                        // echo "Metaparser(): subword: $subword<br>";
                        $output .= ParserChain( $subword );
                        //echo "BEFORE: std: $std_form prt: $prt_form sep_std: $separated_std_form sep_prt: $separated_prt_form<br>";
                        $separated_std_form .= $std_form;
                        $separated_prt_form .= $prt_form;
                        //echo "AFTER: std: $std_form prt: $prt_form sep_std: $separated_std_form sep_prt: $separated_prt_form<br>";
                       
                        //echo "subword: $subword output: $output<br>";
                        //if ( $subword !== end($subword_array)) { /*echo "adding |<br>";*/ $output .= "|";}  // shouldn't be hardcoded?!
                        //echo "Metaparser() inner-foreach: output: $output<br>";
                    }
                    if ( $word_part !== end($separated_word_parts_array)) { 
                        $output .= "\\";  // shouldn't be hardcoded?!
                        $separated_std_form .= "\\";        // eh oui ... l'horreur continue ... ;-)
                        $separated_prt_form .= "\\";
                    }
                //echo "Metaparser() outer-foreach: output: $output<br>";
                }
                //if (mb_strlen($actual_punctuation) > 0) $output .= "[$actual_punctuation]";
                //echo "Metaparser(): output: $output<br>";
                if (mb_strlen($pretokens) > 0) $output = "$pretokens\\" . "$output";
                if (mb_strlen($posttokens) > 0) $output .= "\\$posttokens";
                //$output = "$pretokens\\" . "$output" . "\\$posttokens";
                //echo "Metaparser(): output: $output<br>";
                // return array( $pre, $output, $post );//break; // donnow if break is necessary?!
                //echo "output: $output<br>";
                 //echo "BEFORE RETURN: std: $std_form prt: $prt_form sep_std: $separated_std_form sep_prt: $separated_prt_form<br>";
                       
                return $output;
            case "handwriting":
                $output = $word;
                $output = preg_replace( "/(?<![<>])([ABCDEFGHIJKLMNOPQRSTUVWXYZ]){1,1}/", "[#$1+]", $output ); // upper case
                $output = preg_replace( "/(?<![<>])([abcdefghijklmnopqrstuvwxyz]){1,1}/", "[#$1-]", $output ); // lower case
                $output = mb_strtoupper( $output );
                // return array( $pre, $output, $post ); break; // break necessary?!
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