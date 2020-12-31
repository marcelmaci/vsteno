<?php

/* VSTENO - Vector Steno Tool with Enhanced Notational Options
 * (c) 2018-2021 - Marcel Maci (m.maci@gmx.ch)
 
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
 
// The file linguistics contains tools for linguistical analysis

// global variables
$is_noun = false;
$acronym = 99999;
$value_separate = 3;
$value_glue = 2;

// phpSyllable: include and prepare
require_once("../phpSyllable-master" . '/classes/autoloader.php');


// initialize hunspell and phpSyllables

global $syllable;
global $hunspell_dictionary;

function InitializeHunspellAndPHPSyllable() {
    global $syllable;
    global $hunspell_dictionary; 
    //echo "Initialize linguistics: hyphenator: " . $_SESSION['language_hyphenator'] . " hunspell: " . $_SESSION['language_hunspell'] . " espeak: " . $_SESSION['language_espeak'] . "<br>";
    $phpSyllable_dictionary = $_SESSION['language_hyphenator']; // "de";                             //"de_CH";
    $syllable = new Syllable($phpSyllable_dictionary);          // 'en-us'
    $syllable->setHyphen(new Syllable_Hyphen_Dash());           // get all syllables, with a dash
    // hunspell: dictionary
    $hunspell_dictionary = $_SESSION['language_hunspell']; //"de_CH"; //"de_DE"; //"de_CH";
}
if (!isset($_SESSION['fortune_cookie'])) InitializeHunspellAndPHPSyllable(); // use this for first initialization (cookie) and make sure phpSyllable doesn't get initialized 2x ...


// functions


////////////////////////////////////// all pspell functions grouped together => probably obsolete //////////////////////////////
// pspell: dictionary - probably obsolete and hardcoded to "de" 
// leave it for the moment (in case a system doesn't offer hunspell this could be reactivated - even if pspell is much less
// performant than hunspell, pspell is better than nothing!
// reason to leave it: there is a switch($speller) statement in analyze_composed_words_and_hyphenate() function

$pspell_dictionary = "de";
//$pspell_link = pspell_new("$pspell_dictionary", "", "", "utf-8");

function PSPELLcapitalizedStringList2composedWordsArray($string) {
    //global $pspell_link; // produces unpredictable results if link is declared only once and reused as global?!?
    global $pspell_dictionary;
    $pspell_link = pspell_new("$pspell_dictionary", "", "", "utf-8"); // this seems to work, but us probably slower ... ?!?

    $composed_words = array();
    $word_list_array = explode(" ", $string);
    //var_dump($word_list_array);
    for ($i=0; $i<count($word_list_array); $i++) {
        $test_in_dictionary = $word_list_array[$i];
        for ($j=$i; $j<count($word_list_array); $j++) {
            $hit = false;
            if ($j!=$i) $test_in_dictionary .= decapitalize($word_list_array[$j]);
            if (mb_strlen($test_in_dictionary)>2) {
                //echo "<br>test: i/j: $i/$j: $test_in_dictionary<br>";
                // check for Fugen-s!
                //echo "pspell($test_in_dictionary): >" . pspell_check($pspell_link, $test_in_dictionary) . "<<br>"; 
                if (pspell_check($pspell_link, $test_in_dictionary) != false) {
                    //echo "Word: $test_in_dictionary found in dictionary!<br>";
                    $hit = true;
                    $composed_words[] = $test_in_dictionary;
                    $i = $j;
                    $j = count($word_list_array);
                    break;
                }
            }
        }
        if (!$hit) {
            $combine_with_preceeding = $composed_words[count($composed_words)-1] . decapitalize($word_list_array[$i]);
            //echo "additional check: combine with preceeding = $combine_with_preceeding<br>";
            if ($i<count($word_list_array)-1) {
                if (pspell_check($pspell_link, $combine_with_preceeding) != false) {
                    //echo "<br><br>i=$i<br><br>";
                    $composed_words[count($composed_words)-1] .= decapitalize($word_list_array[$i]);
                } else $composed_words[] = $word_list_array[$i];
            } 
        }
    }
    return $composed_words;
}

/////////////////////////////////// end pspell functions ///////////////////////////////////////////////////////////////////////

// functions
function capitalize($word) {
    //$word[0] = mb_strtoupper($word[0], "UTF-8");
    $first = mb_substr($word, 0, 1);
    $rest = mb_substr($word, 1);
    $word = mb_strtoupper($first, "UTF-8") . $rest; // always those twisted solutions to solve the annoying UTF-8 problem ... (slow, but I don't see any other possibility for the moment ...)
    return $word;
}
function array2capitalizedStringList($array) {
    $word_list = "";
    for ($i=0; $i<count($array); $i++) {
        $result = capitalize($array[$i]);
        $word_list .= $result . " ";
    }
    return $word_list;
}
function decapitalize($word) {
    //$word[0] = mb_strtolower($word[0], "UTF-8"); // wrong for umlauts
    $word = mb_strtolower($word, "UTF-8"); // slower but with utf-8
    return $word;
}
function hyphenate($word) {
    global $syllable;
    //echo "word: $word<br>";
    return preg_replace("/-([a-zA-Z])-/", "$1-", $syllable->hyphenateText($word)); // quick fix: add orphanated chars to preceeding (phpSyllable produces such erroneous outputs ... !?)
}
function word2array($word) {
    return explode("-", $word);
}
function composedWordsArray2hyphenatedString($array) {
    $output = "";
    for ($i=0; $i<count($array); $i++) {
        if ($i!=0) $output .= decapitalize($array[$i]);
        else $output .= $array[$i];
        if ($i<count($array)-1) $output .= "\\";
    }
    return $output;
}
function capitalizedStringList2composedWordsArray($string) {
    global $hunspell_dictionary;
    $composed_words = array();
    $word_list_array = explode(" ", $string);
    //var_dump($word_list_array);
    $length = count($word_list_array);
    for ($i=0; $i<$length; $i++) {
        $test_in_dictionary = $word_list_array[$i];
        for ($j=$i; $j<$length; $j++) {
            $hit = false;
            if ($j!=$i) $test_in_dictionary .= decapitalize($word_list_array[$j]);
            if (mb_strlen($test_in_dictionary)>2) {
                //echo "<br>test: i/j: $i/$j: $test_in_dictionary<br>";
                exec("echo \"$test_in_dictionary\" | hunspell -d $hunspell_dictionary -a -m -s", $o); // assign output to $o (= array)
                //echo "result hunspell: "; // . $o[1] . "<br>";
                // check for Fugen-s!
                if (($o[count($o)-2][0] === "*") || (($o[count($o)-2][0] === "&") && (mb_strpos($o[count($o)-2], "$test_in_dictionary-") > 0))) {
                    //echo "Word: $test_in_dictionary found in dictionary!<br>";
                    $hit = true;
                    $composed_words[] = $test_in_dictionary;
                    $i = $j;
                    $j = $length; //count($word_list_array);
                    break;
                }
            }
        }
        if (!$hit) {
            $combine_with_preceeding = $composed_words[count($composed_words)-1] . decapitalize($word_list_array[$i]);
            //echo "additional check: combine with preceeding = $combine_with_preceeding<br>";
            exec("echo \"$combine_with_preceeding\" | hunspell -d $hunspell_dictionary -a -m -s", $o, $v); // assign output to $o (= array)
            //var_dump($o);
            if ($i<count($word_list_array)-1) {
                if (($o[count($o)-2][0] === "*") || ($o[4][0] === "*") || (($o[count($o)-2][0] === "&") && (mb_strpos($o[count($o)-2], "$combine_with_preceeding-") > 0))) {
                    //echo "<br><br>i=$i<br><br>";
                    //echo "combine with preceeding ...<br>";
                    $composed_words[count($composed_words)-1] .= decapitalize($word_list_array[$i]);
                } else {
                    //echo "consider it a separate word!<br>";
                    $composed_words[] = $word_list_array[$i];
                }
            } 
        }
    }
    return $composed_words;
}

function analyze_composed_words_and_hyphenate($word, $speller) {
    $word = hyphenate($word);
    $word_array = word2array($word);
    $word_list = array2capitalizedStringList($word_array);
    switch ($speller) {
        case "hunspell" : $composed_words = capitalizedStringList2composedWordsArray($word_list); break; // better!
        case "pspell" : $composed_words = PSPELLcapitalizedStringList2composedWordsArray($word_list); break; // just in case hunspell isn't available
    }
    $final_result = composedWordsArray2hyphenatedString($composed_words);
    $final_result_hyphenated = hyphenate($final_result);
    return $final_result_hyphenated;
}

// the above function analyze_composed_words_and_hyphenate works quite well, but it is terribly slow
// this is due to many and slow shell calls ... the goal therefore is to make it faster by grouping
// the words that have to be tested and to call hunspell only once per word.
// this can be achieved creating a list of all possible syllable combinations.
// For examples: Schreib-tisch-tä-ter = 4 Syllables (ABCD)
// Combinations: 
// 1 syllable: A B C D = 4
// 2 syllables: AB BC CD = 3
// 3 syllables: ABC BCD CDE = 2
// 4 syllables: ABCD = 1 (this one, if correctly spelled, should always return * from hunspell)
// total: (n+1) * (n/2) = 10 combinations 
// Since parts of words sometimes can only be recognized by hunspell when they go with an dash in 
// the end, this Variant must also be tested (example: Versicherungsvertreter => Versicherungs
// only returns * if tested as Versicherungs-)
// This doubles the possibilities: total = (n+1) * n, so:
// 1 syllable => 2 possibilities
// 2 syllables => 6 possibilities
// 3 syllables => 12 possibilities
// 4 syllables => 20 possibilities
// 5 syllables => 30 possibilities
// 6 syllables => 42 possibilities
// 7 syllables => 56 possibilities
// The first step therefore is to call hunspell with all these possibilities, for example:
// echo "Schreib Tisch Tä Ter Schreibtisch Tischtä Täter Schreibtischtä Tischtäter Schreibtischtäter
// Schreib- Tisch- Tä- Ter- Schreibtisch- Tischtä- Täter- Schreibtischtä- Tischtäter- Schreibtischtäter-" | hunspell -d de_CD -a
// In a second step the algorithm must check, what combinations where recognized as correct
// (= possible) and which combination of these combinations is the most adequate.
// The tricky part: Not all combinations recognized by hunspell will from a word inside
// the composed word! For example: Was-ser-schloss => "Was" will be recognized as a correct
// german word; nonetheless it isn't a base word composing Wasserschloss since the second
// part "ser" would stay "orphanized". Therefore the algorithm must find the best combinations
// (without orphanized parts).

//$array = array(); // almost no performace gain if $array is declared as global variable!
function count_uppercase($string) {
    global $acronym;
    $stripped = preg_replace("/[A-ZÄÖÜ]/u", "", $string); // umlaut untested! => Umlaut needs the u-modifier in REGEX!
    //echo "string: >$string<<br>stripped: >$stripped<<br>";
    $length1 = mb_strlen($string);
    $length2 = mb_strlen($stripped);
    //echo "length1: $length1<br>length2: $length2<br>";
    $difference = $length1 - $length2;
    //echo "difference: $difference<br>";
    if ($length2 === 0) return $acronym;
    else return $difference;
}

function TryPhoneticTranscriptionFromList($word) {
    //echo "<br>>$word<";
    $result = null;
    foreach ($_SESSION['phonetics_transcription_array'] as $key => $transcription) {
        //echo "check: $key<br>";
        if (preg_match("/^$key$/", $word)) {
            //echo "<br>match with $key";
            $result = preg_replace("/$key/", $transcription, $word); // use regex also for transcription (like in l'aspect: l' can be tested with variable)
            break;
        }
    }
    return $result;
}

function GetPhoneticTranscription($word) {
    //global $last_written_form;
     //$last_written_form = $word;
    //echo "word to transcribe: $word<br>";
    //if (mb_substr($word, 0, 1) !== "#") {  // do not transcribe words starting with # (can be used to mark words that have to be written literaly)
// if word is single char, only transcribe it if session-variable is set
if ((mb_strlen($word) > 1) || ($_SESSION['phonetics_single_char_yesno'])) {
  // strangely enough the first letter of $word is lower case ... no idea why this is so ... !?
  // so, test if word starts with two uppercase chars via [A-Z]{2,} is impossible
  // as a workaround (... yes, more and more workarounds in this program ...)
  // simply test the second character: if it is uppercase, assume the word is an 
  // acronym (and if corresponding option is set, don't transcribe it
  //echo "SESSION[phonetics_acronyms_yesno] = [" . $_SESSION['phonetics_acronyms_yesno'] . "]<br>";
  //$test = preg_match("/.[A-Z]/", $word);
  //echo "preg_match(/.[A-Z]/) = [$test]<br>";
  if (($_SESSION['phonetics_acronyms_yesno'] === false) && (preg_match("/.[A-Z]/", $word) === 1)) {  
        //echo "NO TRANSCRIPTION<br>";
        if ($_SESSION['phonetics_acronyms_lowercase_yesno']) $output = mb_strtolower($word);
        else $output = $word; // don't transcribe acronyms (leave it up to the model to convert upper to lower case if needed!)
  } else {
    //if (($_SESSION['phonetics_acronyms_yesno']) && (preg_match("/.[A-Z]/", $word) !== 1)) {  
    $check = mb_strpos($word, "#");
    if ($check === false) {  // do not transcribe words containing # (at any position)
        //echo "<br>transcribe: $word<br>";
        $decapitalized = mb_strtolower($word);
        $list_result = TryPhoneticTranscriptionFromList($decapitalized); // $_SESSION['phonetics_transcription_array'][$decapitalized];
        //echo "list_result: $list_result<br>";
        // if word exists in list, take result from list
        if ($list_result !== null) $output = $list_result;
        else {
            // otherwise call espeak for transcription
            $language = $_SESSION['language_espeak'];
            $alphabet_option = ($_SESSION['phonetic_alphabet'] === "espeak") ? "-x" : "--ipa"; 
            // transcriptions in english are wrong if they contain any -#+, so filter them out (don't know for other languages)
            $stripped_word = preg_replace("/[-#+]/", "", $word);
            //echo "original word: $word stripped: $stripped_word<br>";
            $shell_command = "espeak -q -v $language $alphabet_option \"$stripped_word\"";
            //echo "$shell_command";
        
            exec("$shell_command",$o);
            //var_dump($o);
            $output = trim($o[0]); // trim is necessary because espeak adds additional spaces
            //echo "trimmed output: >$output<<br>";
        }
    } else {
        //echo "don't transcribe: $word<br>";
         // extend rule to not transcribe words to any position of #
         // shortened words with vowels at beginning might have preceeding phonems
         // example (french): n#avais 
         // separating n and #avais is not an option since n will get transcribed "en"
         // in n#avais, n can be treated as phonem and the abbreviation #avais can be applied normally
        $output = $word;
    }
 } 
} else {
  // word is single character => don't transcribe
  $output = $word;
}
    //echo "result: $output<br>";
    //$last_written_form = $word;
    
    return $output;
}

// make analyze_word_linguistically() a native cpp-function
// in order to achieve that (and full backwards compatibility) make php-function analyze_word_linguistically a wrapper-function
// which calls either the classic php analyze_word_linguistically() function or the (new) native cpp function
// original code is agnostic to how the request will be processed: it just calls the php function as before
// of course, the wrapper slows down php execution (this is an inevitable downside ...)
// Houston, we have a problem: PHP refuses to execute the wrapper function if no extension is present ... (darned, I hoped that 
// the interpreter would be more tolerant ... :)
// solution: use require_once to include either
// (1) wrapper function (if native extension is present)
// (2) classic function (if only classic php code is available)
// advantage(1): almost no slowdown for php-code (since no wrapper included in this case)
// advantage(2): code fails only if native extensions are not present (and this can always be adjusted by setting global $native_extensions 
// variable in constants.php to false.

// new solution: conditional include
if ($_SESSION['native_extensions']) require_once "linguistics_native.php";
else require_once "linguistics_classic.php";

/*
function analyze_word_linguistically($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block) {
    /*switch ($_SESSION['native_extensions']) {
        case false : return analyze_word_linguistically_classic($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block);
                    break;
        case true : return analyze_word_linguistically_classic($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block);
                    break;
    }*/
/*    // or here's the one-liner for the same (more or less ... :)
    return ($_SESSION['native_extensions']) ? analyze_word_linguistically_native($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block) : analyze_word_linguistically_classic($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block);
}
*/

// rename original php analyze_word_linguistically() to analyze_word_linguistically_classic()
// new solution: include it via a separate file: linguistics_classic.php
/*
function analyze_word_linguistically_classic($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block) {
        global $last_written_form;
        $last_written_form = $word;
        //global $parallel_lng_form; // contains analysis of written form if phonetic transcription is selected
        //echo "<br>analyze_word_linguistically: word=$word hyphenate=$hyphenate decompose=$decompose separate=$separate glue=$glue prefixes=$prefixes stems=$stems suffixes=$suffixes block=$block<br>";
        // explode strings to get rid of commas
        $prefixes_array = explode(",", $prefixes);
        $stems_array = explode(",", $stems);
        $suffixes_array = explode(",", $suffixes);
        //$block_array = explode(",", $block);
        // trim
        $prefixes_array = array_map('trim',$prefixes_array); // use callback for trim
        $stems_array = array_map('trim',$stems_array);
        $suffixes_array = array_map('trim',$suffixes_array);
        //$block_array = array_map('trim',$suffixes_array);
    
        $several_words = explode("-", $word);  // if word contains - => split it into array
        $result = "";
        //echo "prefixes: $prefixes";
        //echo "stems: $stems<br>";
        //echo "suffixes: $suffixes<br>";
        for ($i=0;$i<count($several_words);$i++) {
            $single_result = analyze_one_word_linguistically($several_words[$i], $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block);
            //echo "single result: $single_result<br>";
       
            $result .= ($i==0) ? $single_result : "=" . $single_result;     // rearrange complete word using = instead of - (since - is used for syllables)
        }
        //echo "result: $result<br>";
        if ($result === "Array") {
            if ($_SESSION['hyphenate_yesno']) $result = hyphenate($word);    // if word isn't found in dictionary, string "Array" is returned => why?! This is just a quick fix to prevent wrong results
            else $result = $word;
            if ($_SESSION['phonetics_yesno']) $result = GetPhoneticTranscription($result);
            return $result;
            //if ($_SESSION['hyphenate_yesno']) return hyphenate($word);    // if word isn't found in dictionary, string "Array" is returned => why?! This is just a quick fix to prevent wrong results
            //else return $word;
        } else {
            //echo "<br>result(BEFORE): $result<br>";
            if ($_SESSION['affixes_yesno']) $result = mark_affixes($result, $prefixes_array, $suffixes_array);
            else {
                // if affixes should not be marked, it's better to:
                // (1) mark them
                // (2) filter out markings (+, #, |)
                // Reason: parts recognized as words that actually are prefixes (and thus syllables) 
                // will appear as separate words otherwhise 
                // Example: Ein|ga-be
                // Will be processed: Ein|ga-be => Ein+ga-be => Ein-ga-be
                $result = mark_affixes($result, $prefixes_array, $suffixes_array);
                //echo "<br>result(MARKED): $result<br>";
                if ($_SESSION['filter_out_prefixes_yesno']) $result = preg_replace("/(\+)/", "-", $result);
                if ($_SESSION['filter_out_suffixes_yesno']) $result = preg_replace("/(#)/", "-", $result);
                if ($_SESSION['filter_out_words_yesno']) $result = preg_replace("/(\||\|)/", "-", $result);
            }
            //echo "<br>result(AFTER): $result<br>";
            if ($_SESSION['phonetics_yesno']) $result = GetPhoneticTranscription($result);
            //echo "<br>result(PHONETICS): $result<br>";
            return $result;
        }
}
*/

function mark_prefixes($word, $prefixes) {
    // word: linguistically analyzed word (hyphenated and containing composed words and prefixes separated by |
    // prefixes: prefix list => goal is to mark prefixes with an + instead of | like "ge|laufen" => "ge+laufen"
    $prefix_list = explode(",", $prefixes);
    for ($i=0; $i<count($prefix_list); $i++) {
        $actual_prefix = trim($prefix_list[$i]);
        //echo "prefix: $actual_prefix word: $word<br>";
        $word = preg_replace("/(^|\+|\|)($actual_prefix)\|/i", "$1$2+", $word); // i = regex caseless modifier
        //echo "result: $word<br>";
    }
    return $word;
}

function mark_affixes($word, $prefixes, $suffixes) {
    $word = backwards_preg_replace_all($word, $prefixes, "prefix");
    $word = backwards_preg_replace_all($word, $suffixes, "suffix");
    return $word;
}

function ApplyFilter($word) {
    $filter_list = $_SESSION['filter_list'];
    $filter_array = explode(",", $filter_list);
    $filter_array = array_map('trim',$filter_array);
    
    foreach ($filter_array as $key => $element) {
        //$replacement = preg_replace("/(\||\\)?(.*?)(\||\\)?/", "$1-$3", $element);
        //$element = preg_replace("/(?<!\\)\|/", "bla", $element); // escape | if it is not escaped! (prevent infitie preg_replace-loop)
        
        $replacement = preg_replace("/(\|)/", "-", $element);
        //$element = preg_quote($element);
        //$replacement = preg_replace("/(\|)/", "-", "|des");
        
        // test the whole and horribly complicated escape sequence to be able to use regex inside filter_list
        //$test_element = "|de[rsmn]";
        //$test_element = "de[rsmn]|";
        $test_element = $element;
        //echo "test_element: $test_element<br>";
        
        $test_replacement = preg_replace("/\|/", "", $test_element);
        //echo "test_replacement: $test_replacement<br>";
        
        $test_replacement_regex = preg_quote($test_replacement);
        //echo "test_replacement_regex: $test_replacement_regex<br>";
        
        $test_condition = preg_replace("/$test_replacement_regex/", "($test_replacement)", $test_element);
        $test_condition = preg_replace("/\|/", "\|", $test_condition);
        //echo "test_condition: $test_condition<br>";
        
        $test_replacement_with_dash = preg_replace("/\|/", "-", $test_element);
        //echo "test_replacement_with_dash: $test_replacement_with_dash<br>";
        
        // impossible to insert $1 ... no idea how that has to be escaped ... ?!! use space followed by trim instead ..
        $test_replacement_with_variable = preg_replace("/$test_replacement_regex/", "\$ 1", $test_replacement_with_dash);
        $test_replacement_with_variable = preg_replace("/ /", "", $test_replacement_with_variable);
        //echo "test_replacement_with_variable: $test_replacement_with_variable<br>";
        
        $test_word = preg_replace("/$test_condition/", "$test_replacement_with_variable", $word);
        
        //echo "real: filter element: #$element# => #$replacement# in word: $word<br>";
        //echo "test: \"$test_condition\" => \"$test_replacement_with_variable\"<br>word: $word => result: $test_word<br>";
        
        $word = $test_word;
        
        // old preg_replace
        //$word = preg_replace("/$element/", "$replacement", $word);
        //echo "result: $word<br>";
    }
    
    return $word;
}

function analyze_one_word_linguistically($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block) {
    global $parallel_lng_form;
    //echo "analyze_one_word_linguistically: hyphenate: $hyphenate decompose: $decompose separate: $separate glue: $glue<br>";
    
    // $separate: if length of composed word < $separate => use | (otherwise use \ and separate composed word)
    //            if 0: separate always
    // $glue: if length of composed word < $glue => use - (= syllable of same word), otherwise use | or \
    //        if 0: glue always (= annulate effect of linguistical analysis)
    // Examples: 
    // a) $glue = 4:                                    $glue = 0:
    //    Eu-len\spie\gel => Eu-len\spie-gel            Eu-len-spie-gel
    //    Ab\tei-lungs\lei-ter => Ab-teilungs\leiter
    // b) $separate = 4:                $separate = 0:
    //    Mut\pro-be => Mut|probe       Mut\pro-be
    //    Ha-sen\fuss => Hasen\fuss     Ha-sen\fuss
    // declare globals
    global $is_noun;    // true if first letter of word is a capital
    global $acronym, $value_separate, $value_glue, $value_hyphenate;
    // set globals
    $value_separate = $separate;
    $value_glue = $glue;
    $value_hyphenate = $hyphenate;
    //echo "suffixes (one word): $suffixes<br>";
    
    // check for acronyms and nouns
    $upper_case = count_uppercase($word);
    if ($upper_case === $acronym) return $word;         // return word without modifications if it is an acronym (= upper case only)
    elseif ($upper_case > 1) return hyphenate($word);   // probably an acronym with some lower case => hyphenate        
    else {
    
        if ($decompose) {
            //echo "decompose word<br>";
            list($word_list_as_string, $array) = create_word_list($word);
            //echo "stems: $stems<br>";
            //echo "suffixes (one word): $suffixes<br>";
   
            $array = eliminate_inexistent_words_from_array($word_list_as_string, $array, $prefixes, $stems, $suffixes, $block);
            //var_dump($array);
            $result = recursive_search(0,0, $array);
            
            //echo "inside (one word): word: $word result: $result<br>";
            if ($result === "") $result = $word; // fix bug: recursive search can return "" instead of a word if word isn't found in hunspell dictionary
        } else $result = $word; //$result = iconv(mb_detect_encoding($word, mb_detect_order(), true), "UTF-8", $word);
        //echo "$result - $word<br>";
        if ($hyphenate) $result = hyphenate($result);
        if ($upper_case === 1) {
            //echo "word is noun<br>";
            //echo "1:$result<br>";
            $result = mb_strtolower($result, "UTF-8"); // argh ... always these encoding troubles ...
            //echo "2:$result<br>";
            $result = capitalize($result);
            //echo "3:$result<br>";
           
        } else $result = ApplyFilter(mb_strtolower($result));
        $final_result = ApplyFilter($result);
        $parallel_lng_form = $final_result;
        //echo "<br>result (lng): $final_result<br>";
        return $final_result;
    }
}

function backwards_preg_match($wordpart, $array) { 
    // tests if regex pattern in array matches wordpart; type = prefix, suffix 
    // returns modified wordpart if pattern matches
    //echo "<br>wordpart: $wordpart<br>";
    //var_dump($array);
    for ($i=0; $i<count($array); $i++) {
        $pattern = mb_strtolower($array[$i]);
        $wordpart_lower = mb_strtolower($wordpart);
        // maybe I fixed a bug that wasn't there ... because SESSION-variables weren't actualized ...
        // if any strange behaviour of pre/suffixes => revert back to previous commit
        // i.e. do not use strtolower but i-flag for regex instead ...
        //if ($pattern === "be") echo "pattern: $pattern wordpart_lower: $wordpart_lower<br>";
        //echo "preg_match: #$pattern# in wordpart: $wordpart_lower<br>";
        $result = preg_match("/^$pattern$/", $wordpart_lower); 
        //echo "result $i: $result<br>";
        if ($result === 1) return true;
    }
}

function backwards_preg_replace_all($word, $array, $type) { 
    //var_dump($array);
    // tests if regex pattern in array matches wordpart; type = prefix, suffix 
    // returns modified wordpart if pattern matches
    for ($i=0; $i<count($array); $i++) {
        $pattern = $array[$i];
        //echo "pattern: $pattern<br>";
        switch ($type) {
            case "prefix" : $result = preg_replace("/(^|\||\+)($pattern)(\|)/i", "$1$2+", $word); break;
            case "suffix" : $result = preg_replace("/(\||\+)($pattern)(#|\||$)/i", "#$2$3", $word); break;
        }
        //if ($result !== $wordpart) return $result;
        $word = $result;
    }
    //echo "word: $word<br>";
    return $word;
}

function eliminate_inexistent_words_from_array($string, $array, $prefixes, $stems, $suffixes, $block) {
    $language_code = $_SESSION['language_hunspell'];
    $shell_command = /* escapeshellcmd( */"echo \"$string\" | hunspell -i utf-8 -d $language_code -a" /* ) */;
    //echo "shell: $shell_command<br>";
    // explode strings to get rid of commas
    $prefixes_array = explode(",", $prefixes);
    $stems_array = explode(",", $stems);
    $suffixes_array = explode(",", $suffixes);
    $block_array = explode(",", $block);
    // trim
    $prefixes_array = array_map('trim',$prefixes_array); // use callback for trim
    $stems_array = array_map('trim',$stems_array);
    $suffixes_array = array_map('trim',$suffixes_array);
    $block_array = array_map('trim',$block_array);
    // implode to add spaces for string comparison
    $prefixes = " " . implode(" ", $prefixes_array) . " ";
    $stems = " " . implode(" ", $stems_array) . " ";
    $suffixes = " " . implode(" ", $suffixes_array) . " ";
    //echo "<br>suffixes(eliminate): $suffixes<br>";
    //var_dump($prefixes_array);
    $block_string = implode(" ", $block_array);
    //echo "block: $block<br>";
    //echo "block_string: $block_string<br>";
    //echo "block_array: <br>";
    //var_dump($block_array);
    //echo "<br>";
    
    //echo "$shell_command<br>";
    //echo "hunspell: ";
    exec("$shell_command",$o);
    //echo "results:<br>"; var_dump($o);
    $length = count($array[0]);
    $offset = 1;
    for ($l=0;$l<$length; $l++) {
        for ($r=0;$r<count($array[$l]); $r++) {
            //echo "<br>result: " . $array[$l][$r][0] . ": >" . $o[$offset] . "<<br>";
            //echo "prefix test: $prefixes: " . mb_strpos(mb_strtolower($prefixes), mb_strtolower($array[$l][$r][0])) . "<br>";
            //echo "stem test: $stems: " . mb_strpos(mb_strtolower($stems), mb_strtolower($array[$l][$r][0])) . "<br>";
            $testw = $array[$l][$r][0] . "-";
            //echo "test: $testw result: " . mb_strpos($o[$offset], $testw) . "<br>";
            
            if (($o[$offset] === "*") || (($o[$offset][0] === "&") && (mb_strpos($o[$offset], $testw) !== false))) {
                
                //echo "match * found: " . $array[$l][$r][0] . "<br>";
                
            } elseif (backwards_preg_match($array[$l][$r][0], $prefixes_array)) {
                 //echo "match found as prefix<br>";
                 //var_dump($prefixes_array);
            } elseif (backwards_preg_match($array[$l][$r][0], $stems_array)) {
                //echo "match found as stem<br>";
            } elseif (backwards_preg_match($array[$l][$r][0], $suffixes_array)) {
                //echo "match found as suffix<br>";
                    //echo "suffix-pattern: " . $array[$l][$r][0] . " result: " . backwards_preg_match($array[$l][$r][0], $suffixes_array);
            } else {
                // no match => delete string in array (use same data field for performance reason)
                //echo "no match: " . $array[$l][$r][0] . "<br>";
                $array[$l][$r][0] = ""; // "" means: no match!
            }
            // explications blocklist (= variable $_SESSION['block_list']):
            //
            // format: "a, b, c, d, ..." (list of elements, like prefix_list, suffix_list etc.)
            //
            // signification: 
            // - elements in blocklist are not considered as words
            // - means: they are eliminated from $array and are not sent to hunspell for testing
            //
            // examples:
            //
            // la-chen-des: 
            // - list: "la chen des lachen des la chendes lachendes"
            // - reduced to: "la chen lachen la chender lachendes"
            // - raw-LNG: la-chen-des (if "des" is not in blocklist,
            // the result is "la-chen|des")
            //
            // des-we-gen:
            // - list: "des we gen deswe gen des wegen deswegen"
            // - reduced to: "we gen deswe gen wegen deswegen"
            // - raw-LNG: des-we-gen (if "des is not in blocklist, 
            // the result is "des|wegen")
            //
            // NOTE: 
            // Even if the blocklist allows you to filter out unwanted
            // suffixes (false positives) in the above examples it 
            // completely destroys the possibility to detect pre-
            // fixes:
            // - des-we-gen: since "des" and "wegen" are not tested 
            // individually, even if you add "des" to the prefix list
            // it won't be recognized by the analyzer (since "des" is
            // no "independent" part of the word)
            //
            // If you want both advantages (i.e. recognition of
            // prefixes and no false positives at the end of the word)
            // you should use the filter_list instead:
            //
            // how-to:
            // - don't add "des" to blocklist (=> des will be processed
            // individually and can be recognized as prefixes; but
            // this also produces false positives like la-chen|des)
            // - add "des" to prefixes_list (the analyzer will mark
            // des|we-gen as des+we-gen)
            // - add "|des" to filter_list (la-chen|des will be re-
            // written as la-chen-des)
            //
            // As of writing these lines, the implementation of
            // filter_list is only partial (no full regex can be 
            // used, but only | either at the beginning or at
            // the end of the element that has to be filtered) 
            // and will probably be adapted/extended in the future.
            
            // test blocklist
            //echo "<br>test blocklist<br>";
            //echo "array:<br>";
            //print_r($array);
            //echo "<br>block_array:<br>";
            //print_r($block_array);
            //echo "test($l,$r): #" . $array[$l][$r][0] . "#<br>";
            if (backwards_preg_match($array[$l][$r][0], $block_array)) $array[$l][$r][0] = "";
            //echo "result($l,$r): #" . $array[$l][$r][0] . "#<br>";
            $offset+=1;
        }
    }
    //echo "<br><br>array:<br>";
    //var_dump($array);
    return $array;
}

function create_word_list($word) {
    //global $array; // don't treat $word_list_as_array as function value but as global variable for performance reason
    $hyphenated = hyphenate($word);
    //echo "$hyphenated<br>";
    $hyphenated = decapitalize($hyphenated);
    //echo "$hyphenated<br>";
    
    $hyphenated_array = explode("-", $hyphenated);
    $word_list_as_string = "";
    $array = array();
    $syllables_count = count($hyphenated_array);
    for ($l=0; $l<$syllables_count; $l++) { // l = line of combinations
        for ($r=0; $r<$syllables_count-$l; $r++) {  // r = row of combinations
            $single = "";
            for ($n=0; $n<$l+1; $n++) {     // n = length of combination
                $single .= $hyphenated_array[$r+$n];
            }
            $single = capitalize($single);
            //$single_plus_dash = "$single-";
            //$word_list_as_string .= "$single $single_plus_dash ";
            $word_list_as_string .= "$single ";
            $array[$l][$r][0] = $single;
            //$word_list_as_array[$l][$r][1] = $single_plus_dash; // don't create dash-list for better performance
        }
    }
    //echo "wordlist: $word_list_as_string<br>";
    return array($word_list_as_string, $array);
    //return $word_list_as_string; // return only string for performance reason => almost no gain: revert back to function parameters
}

function recursive_search($line, $row, $array) {
    global $value_glue, $value_separate;
    //var_dump($array);
    //global $array;
    //echo "call ($line/$row): " . $array[$line][$row][0] . " (" . $array[$line][$row][2] . ")<br>";
    //if (($line < 0) || ($row < 0) || ($line > count($array)) || ($row > count($array[$line]))) return "";
    if ($array[$line][$row][0] != "") {
        //echo "that's a good start: word exists!<br>";
        if ($row === count($array[$line])-1) {
            //echo "reached end of line => return >" . $array[$line][$row][0] . "<<br>";
            //$hit = true;
            return $array[$line][$row][0];
        } else {
            $temp_row = $line+$row+1;
            $temp_line = 0; //count($array) - $temp_row-1; // could this do horizontal as well?!
            //if (($line-1-$row>=0) && ($row+$line<count($array[$line-1-$row]))) {
            if (($temp_line>=0) && ($temp_row<count($array[$temp_line]))) {
                //echo "=> try up<br>";
                //$up = recursive_search($line-1, $row+$line, $array);
                $up = recursive_search($temp_line, $temp_row, $array);
            } else $up = "";
            if (mb_strlen($up)>0) {
                //echo "found up: $up and return my own: " . $array[$line][$row][0] . "<br>";
                $length_candidate = mb_strlen($array[$line][$row][0]);
                $pos1 = mb_strpos($up, "|"); // can return boolean or int!!!
                $pos2 = mb_strpos($up, "\\");
                if (($pos1 === false) && ($pos2 === false)) {
                    $pos3 = mb_strlen($up); // if no | and \ => take strlen of already existing word
                    $pos1 = 99999;
                    $pos2 = 99999;
                } else $pos3 = 99999;
                if ($pos1 != 99999) $relevant = $pos1;
                if ($pos2 != 99999) $relevant = $pos2;
                if ($pos3 != 99999) $relevant = $pos3;
                if (($length_candidate > $value_glue) && ($relevant > $value_glue)) {
                   
                    //echo "candidate: " . $array[$line][$row][0] . " up: $up pos123relevant: >$pos1-$pos2-$pos3-$relevant<<br>";
                    
                    if (($length_candidate > $value_separate) || ($relevant > $value_separate)) return $array[$line][$row][0] . "\\" . $up;
                    else return $array[$line][$row][0] . "|" . $up;
                } else return $array[$line][$row][0] . "|" . $up;
            } else /* {
                /*if ($row+$line+1<count($array[$line])) {
                    echo "=> try horizontal<br>";
                    $horizontal = recursive_search($line, $row+$line+1, $array);
                } else $horizontal = "";
                if (mb_strlen($horizontal)>0) {
                    echo "found horizontal: $horizontal and return my own: " . $array[$line][$row][0] . "<br>";
                    return $array[$line][$row][0] . "\\" .$horizontal;
                } else */ {
                    if (($line+1<count($array)) && ($row<count($array[$line+1]))) {
                        //echo "=> try down (count(array)=" . count($array) . "/count(array(line))=" . count($array[$line]) . ")<br>";
                        $down = recursive_search($line+1, $row, $array);
                    } else $down = "";
                    if (mb_strlen($down)>0) {
                        //echo "found down: $down (don't return own " . $array[$line][$row][0] . "<br>";
                        return /*$array[$line][$row][0] . "\\" . */ $down;
                    } else return ""; // no luck - even the main word isn't recognized by hunspell ...
               //}
            }
        }
    } else {
        if (($line+1<count($array)) && ($row<count($array[$line+1]))) {
            //echo "no luck => traverse down<br>"; 
            if ($line+1<count($array)) return recursive_search($line+1, $row, $array);
        } else {
            //echo "no luck => end traversing (go back)<br>";
            return "";
        }
    }
}

?>