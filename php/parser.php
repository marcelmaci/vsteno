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

function replace_all( $pattern, $replacement, $string ) {
    do {
        $old_string = $string;
        $string = preg_replace( $pattern, $replacement, $string );
    } while ($old_string !== $string );
    return $string;
}
    
///////////////////////////////////////////// parser functions ////////////////////////////////////////////////

// general philosophy for parser:
// (1) divide et impera! Divide parsing task into different subtasks corresponding to linguisticly logical steps
// (e.g. mark affixes, correct different representation of same phonetics etc.)
// (2) KISS (keep it stupid, simple): one function = one (simple and basic) task!

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

// decapitalizer
function Decapitalizer( $word ) {
    $output = mb_strtolower($word);
    return $output;
}

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

// lookuper (checks if word is in dictionary)
function Lookuper( $word ) {
    global $dictionary_table;
    $original_result =  $dictionary_table[ $word ];
    if (mb_strlen( $original_result ) > 0) return $original_result;
    else {
        $lower_result = $dictionary_table[ mb_strtolower($word)];
        if (mb_strlen( $lower_result ) > 0) return $lower_result; // empty string is returned automatically if no entry is found // good idea to convert to lower case ... ?!?
    }
}



// metaparser: combines all the above parsers
function ParserChain( $text ) {
        // test if word is in dictionary: if yes => return immediately and avoid parserchain completely (= word will be transcritten directly by steno-engine
        $result = Lookuper( $text );
        if ( mb_strlen($result) > 0 ) return $result;
        // if there is no entry in the dictionary: try trickster first (befory applying parserchain)
        // if trickster returns a result, then avoid decapitalizer (trickster needs capital letter to distinguish between certain words, avoiding decapitalizing
        // gives the trickster the possibility to mark certain parts of the words as capitals (so they won't get treated by certain rules of the parser chain))
        $result = Trickster( $text ); // echo "text: $text / trickster: $result<br>";
        if ( mb_strlen($result) > 0 ) return Substituter( Transcriptor( Bundler( Normalizer( Shortener( Filter( $result )))))); // don't apply decapitalizer
        else return Substituter( Transcriptor( Bundler( Normalizer( Shortener( Decapitalizer( Filter(( $text )))))))); // apply normal parserchain on original word
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
        return array( $pretokens[0], $word_array[0], $posttokens[0] );
}

function MetaParser( $text ) {
        global $punctuation;
        $text = preg_replace( '/\s{2,}/', ' ', ltrim( rtrim( $text )));         // eliminate all superfluous spaces
        //echo "text: #$text#<br>";
        list( $pre, $word, $post ) = GetPreAndPostTags( $text );
        //echo "Metaparser(): Word: $word<br>";
        $temp_word = Globalizer( $word );
        //echo "Metaparser(): Globalized: $word<br>";
        list( $pretokens, $word, $posttokens ) = GetPreAndPostTokens( $temp_word );
        if ($temp_word === $posttokens) $pretokens = "";  // if the whole word consists of pre/posttokens, both variables are set => keep only $posttokens (i.e. delete pretokens)
        //echo "word: #$word#<br>";
        //echo "Pretokens: $pretokens <br>";
        //echo "Posttokens: $posttokens <br>";
        
        switch ($_SESSION['token_type']) {
            case "shorthand": 
                $separated_word_parts_array = explode( "\\", Helvetizer($word) );
                //var_dump($separated_word_parts_array);echo"<br";
                $output = ""; 
                foreach ($separated_word_parts_array as $word_part ) {
                    //echo "Metaparser(): Wordpart: $word_part<br>";
                    $subword_array = explode( "|", $word_part ); // problem with this method is, that certain shortings (e.g. -en) will be applied at the end of a subword, while the shouldn't ... Workaround: add | at the end (that will be eliminated later shortly before transformation into token_list) ... (?!) seems to work for the moment, but keep an eye on that! Sideeffect: shortenings at the end won't be applied (this was intended at the beginning...) => rules must be rewritten with $ and | to mark end of words and subwords
                    //var_dump($subword_array);echo"<br>"; 
                    foreach ($subword_array as $subword) { 
                        if ($subword !== end($subword_array)) $subword .= "|";
                        // echo "Metaparser(): subword: $subword<br>";
                        $output .= ParserChain( $subword ); 
                        //if ( $subword !== end($subword_array)) { /*echo "adding |<br>";*/ $output .= "|";}  // shouldn't be hardcoded?!
                        //echo "Metaparser() inner-foreach: output: $output<br>";
                    }
                    if ( $word_part !== end($separated_word_parts_array)) { /*echo "adding \\<br>";*/ $output .= "\\";}  // shouldn't be hardcoded?!
                //echo "Metaparser() outer-foreach: output: $output<br>";
                }
                //if (mb_strlen($actual_punctuation) > 0) $output .= "[$actual_punctuation]";
                //echo "Metaparser(): output: $output<br>";
                if (mb_strlen($pretokens) > 0) $output = "$pretokens\\" . "$output";
                if (mb_strlen($posttokens) > 0) $output .= "\\$posttokens";
                //$output = "$pretokens\\" . "$output" . "\\$posttokens";
                //echo "Metaparser(): output: $output<br>";
                return array( $pre, $output, $post );//break; // donnow if break is necessary?!
            case "handwriting":
                $output = $word;
                $output = preg_replace( "/(?<![<>])([ABCDEFGHIJKLMNOPQRSTUVWXYZ]){1,1}/", "[#$1+]", $output ); // upper case
                $output = preg_replace( "/(?<![<>])([abcdefghijklmnopqrstuvwxyz]){1,1}/", "[#$1-]", $output ); // lower case
                $output = mb_strtoupper( $output );
                return array( $pre, $output, $post ); break; // break necessary?!
/*
            case "htmlcode":
                $_SESSION['token_type'] = "shorthand";
                //return( $pre, $word, $post); 
                break; // break necessary? 
*/
        }
}


////////////////////////////////////////////// end of parser functions ///////////////////////////////////


?>