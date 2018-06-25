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
 
///////////////////////////////////////////// parser functions ////////////////////////////////////////////////

// general philosophy for parser:
// (1) divide et impera! Divide parsing task into different subtasks corresponding to linguisticly logical steps
// (e.g. mark affixes, correct different representation of same phonetics etc.)
// (2) KISS (keep it stupid, simple): one function = one (simple and basic) task!

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
        $output = preg_replace( "/$pattern/", $replacement, $output );
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
    return $dictionary_table[ mb_strtolower($word) ]; // not really good to convert everything to lower case ...
}



// metaparser: combines all the above parsers
function ParserChain( $text ) {
        // test if word is in dictionary: if yes => return immediately and avoid parserchain completely (= word will be transcritten directly by steno-engine
        $result = Lookuper( $text );
        if ( mb_strlen($result) > 0 ) return $result;
        // if there is no entry in the dictionary: try trickster first (befory applying parserchain)
        // if trickster returns a result, then avoid decapitalizer (trickster needs capital letter to distinguish between certain words, avoiding decapitalizing
        // gives the trickster the possibility to mark certain parts of the words as capitals (so they won't get treated by certain rules of the parser chain))
        $result = Trickster( $text );
        if ( mb_strlen($result) > 0 ) return Substituter( Transcriptor( Bundler( Normalizer( Shortener( Filter( $result )))))); // don't apply decapitalizer
        else return Substituter( Transcriptor( Bundler( Normalizer( Shortener( Decapitalizer( Filter(( $text )))))))); // apply normal parserchain on original word
}

function MetaParser( $text ) {
        global $punctuation;
        $text = preg_replace( '/\s{2,}/', ' ', ltrim( rtrim( $text )));         // eliminate all superfluous spaces
        $actual_punctuation = "";
        if (preg_match( "/[$punctuation]/", $text) == 1) {
            $text_length = mb_strlen( $text );
            $actual_punctuation = mb_substr( $text, $text_length-1, 1);
            $text = mb_substr($text, 0, $text_length-1);
        }
        $subword_array = explode( "|", Helvetizer($text) );
        $output = ""; 
        foreach ($subword_array as $subword ) {
                $output .= ParserChain( $subword ); 
                if ( $subword !== end($subword_array)) $output .= "|";  // shouldn't be hardcoded
        }
        if (mb_strlen($actual_punctuation) > 0) $output .= "[$actual_punctuation]";
        return $output;
}


////////////////////////////////////////////// end of parser functions ///////////////////////////////////


?>