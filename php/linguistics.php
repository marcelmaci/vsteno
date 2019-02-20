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
 
// The file linguistics contains tools for linguistical analysis

// phpSyllable: include and prepare
require_once("../phpSyllable-master" . '/classes/autoloader.php');
$phpSyllable_dictionary = "de";                             //"de_CH";
$syllable = new Syllable($phpSyllable_dictionary);          // 'en-us'
$syllable->setHyphen(new Syllable_Hyphen_Dash());           // get all syllables, with a dash

// hunspell: dictionary
$hunspell_dictionary = "de_CH"; //"de_DE"; //"de_CH";

// pspell: dictionary
$pspell_dictionary = "de";
//$pspell_link = pspell_new("$pspell_dictionary", "", "", "utf-8");

// functions
function capitalize($word) {
    $word[0] = mb_strtoupper($word[0]);
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
    $word[0] = mb_strtolower($word[0]);
    return $word;
}
function hyphenate($word) {
    global $syllable;
    return $syllable->hyphenateText($word);
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
                //echo "result hunspell: "; // . $o[1] . "<br>";
                //var_dump($o);
                //echo "<br>";
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
function capitalizedStringList2composedWordsArray($string) {
    global $hunspell_dictionary;
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
                exec("echo \"$test_in_dictionary\" | hunspell -d $hunspell_dictionary -a -m -s", $o); // assign output to $o (= array)
                //echo "result hunspell: "; // . $o[1] . "<br>";
                //var_dump($o);
                //echo "<br>";
                // check for Fugen-s!
                if (($o[count($o)-2][0] === "*") || (($o[count($o)-2][0] === "&") && (mb_strpos($o[count($o)-2], "$test_in_dictionary-") > 0))) {
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

function create_word_list($word) {
    $hyphenated = hyphenate($word);
    echo "$hyphenated<br>";
    $hyphenated = decapitalize($hyphenated);
    echo "$hyphenated<br>";
    $hyphenated_array = explode("-", $hyphenated);
    $word_list_as_string = "";
    $word_list_as_array = array();
    $syllables_count = count($hyphenated_array);
    for ($l=0; $l<$syllables_count; $l++) { // l = line of combinations
        for ($r=0; $r<$syllables_count-$l; $r++) {  // r = row of combinations
            $single = "";
            for ($n=0; $n<$l+1; $n++) {     // n = length of combination
                $single .= $hyphenated_array[$r+$n];
            }
            $single = capitalize($single);
            $single_plus_dash = "$single-";
            $word_list_as_string .= "$single $single_plus_dash ";
            $word_list_as_array[$l][$r][0] = $single;
            $word_list_as_array[$l][$r][1] = $single_plus_dash;
        }
    }
    return array($word_list_as_string, $word_list_as_array);
}

/********************************** some tests with hunspell spellchecker executed via shell
//echo shell_exec(escapeshellarg("echo \"Testwort\" \| hunspell -d de_CH -a"));
//echo shell_exec(escapeshellarg("hunspell -d de_CH -f \"testwoerter.txt\""));
//echo "Schiff=fahrts=mu=se=um" | hunspell -d de_CH -a
//echo escapeshellarg('echo "test" | hunspell -d de_CH -a');
echo $safe_mode;

//echo htmlspecialchars(shell_exec("echo 'Schifffahrt' > hello.txt"));
//echo htmlspecialchars(shell_exec("cat hello.txt"));

//echo "<br>";
//echo htmlspecialchars(system(escapeshellcmd("hunspell -d de_CH -a -f hello.txt")));

//echo exec("hunspell -d de_CH -a -f hello.txt", $o); // assay output to $o (= array)
$word = "Voruntersuchung Zweitwort Drittwort"; //"Schifffahrt";
$dictionary = "de_DE"; //"de_CH";

// check for existing dictionaries
echo system("hunspell -D -a", $o); // assay output to $o (= array)
var_dump($o);
echo "<br>";
*/

/*
*/


/******************************************* another spellchecker test with pspell */
/*
$pspell_link = pspell_new("de");

if (pspell_check($pspell_link, "Schiff-fahrt")) {
    echo "This is a valid spelling";
} else {
    echo "Sorry, wrong spelling";
}
*/

?>