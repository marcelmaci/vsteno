<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Titel</title>
  </head>
  <body>

<?php

// phpSyllable
$dictionary = "de"; //"de_CH";
require_once("../phpSyllable-master" . '/classes/autoloader.php');
$syllable = new Syllable($dictionary);  // 'en-us'
$syllable->setHyphen(new Syllable_Hyphen_Dash());

function capitalize( $word ) {
        $word[0] = mb_strtoupper($word[0]);
        return $word;
}
function decapitalize( $word ) {
        $word[0] = mb_strtolower($word[0]);
        return $word;
}
function getHyphenatedWord($word) {
        return $syllable->hyphenateText($original_word);
}
function getComposedWordsArray($word) {
    $hyphenated = getHyphenatedWord($word);
    $wordpartsarray = explode("-");
    
    
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

//$original_word = 'Dampfschifffahrtskapitänsjackenknopfloch'; // doesn't work
$original_word = 'Wachtmeister';
$original_word = "Lebenspartner";
$original_word = "Eulenspiegel";
$original_word = "Wolkenkratzer";
$original_word = "Versicherungsvertreter";
//$original_word = "kopfgesteuert";
//$original_word = "Originalbild";
//$original_word = 'Dampfschifffahrtskapitänsjackenknopfloch'; // doesn't work
//$original_word = "Abteilungsleiterin";
//$original_word = "Blumenverkäufer"; // nope

$dictionary = "de"; //"de_CH";

// ************************************* test for syllable analysis with phpSyllable
// conclusion: very simple and useful
// phpSyllable
require_once("../phpSyllable-master" . '/classes/autoloader.php');
$syllable = new Syllable($dictionary);  // 'en-us'
$syllable->setHyphen(new Syllable_Hyphen_Dash());
$word = $syllable->hyphenateText($original_word);
echo "Silben: $original_word => $word<br>";

//$word = preg_replace("/-/", " ", $word);
$word_array = explode("-", $word);

var_dump($word_array);
$word_list = "";

for ($i=0; $i<count($word_array); $i++) {
    //echo "capitalize: " . $word_array[$i] . "<br>";
    $result = capitalize($word_array[$i]);
    //echo "result: $result<br>";
    $word_list .= $result . " ";
}
echo "Wordlist: $word_list<br>";

$composed_words = array();
$word_list_array = explode(" ", $word_list);
var_dump($word_list_array);
for ($i=0; $i<count($word_list_array); $i++) {
    $test_in_dictionary = $word_list_array[$i];
    for ($j=$i; $j<count($word_list_array); $j++) {
        $hit = false;
        if ($j!=$i) $test_in_dictionary .= decapitalize($word_list_array[$j]);
        if (mb_strlen($test_in_dictionary)>2) {
                echo "<br>test: i/j: $i/$j: $test_in_dictionary<br>";
                $dictionary = "de_CH";
                exec("echo \"$test_in_dictionary\" | hunspell -d $dictionary -a -m -s", $o); // assay output to $o (= array)
                echo "result hunspell: "; // . $o[1] . "<br>";
                var_dump($o);
                echo "<br>";
                // check for Fugen-s!
                if (($o[count($o)-2][0] === "*") || (($o[count($o)-2][0] === "&") && (mb_strpos($o[count($o)-2], "$test_in_dictionary-") > 0))) {
                    echo "Word: $test_in_dictionary found in dictionary!<br>";
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
            echo "additional check: combine with preceeding = $combine_with_preceeding<br>";
            exec("echo \"$combine_with_preceeding\" | hunspell -d $dictionary -a -m -s", $o, $v); // assign output to $o (= array)
            var_dump($o);
            if ($i<count($word_list_array)-1) {
                if (($o[count($o)-2][0] === "*") || ($o[4][0] === "*") || (($o[count($o)-2][0] === "&") && (mb_strpos($o[count($o)-2], "$combine_with_preceeding-") > 0))) {
                    echo "<br><br>i=$i<br><br>";
                    $composed_words[count($composed_words)-1] .= decapitalize($word_list_array[$i]);
                } else $composed_words[] = $word_list_array[$i];
            } 
    }
}
echo "<br>Liste zusammengesetzer Wörter: <br>";
var_dump($composed_words);



/*
// combine with spellcheck
$dictionary = "de_CH";
echo "Spellchecker: hunspell<br>";
echo "Word: $word<br>";
echo "Dictionary: $dictionary<br>";
echo exec("echo \"$word_list\" | hunspell -d $dictionary -a -m -s", $o, $v); // assay output to $o (= array)

echo "Return (array): ";
var_dump($o);
var_dump($v);
echo "<br>Return (word): " . $o[1] . "<br>";
*/


//echo htmlspecialchars(system("ls -l"));

echo "<br><br>";


// regex definitions
$first_token_type_a_single = "1a_single"; //"blmnpvwfsxy";
$first_token_type_a_multi = "1a_multi"; //"ff|mm|nn|pp|pf|sp|ant|&e|ss|un|schaft|&a|&u|&o|&i|all|hab|haft|auf|aus|des|bei|selb|wo|fort|";
$first_token_type_a_special = "0n-|"; 
$first_token_type_a_combined = "l@l|b@l|m@l|f@l|p@l|pf@l|v@l|sp@l|w@l|";
$first_token_type_a_combined .= "b@r6|sp@r6|m@r6|p@r6|pf@r6|n@r6|n@l|l@r6|";
$first_token_type_a_single = "blmnpvwfxy";
$first_token_type_a_multi = "$first_token_type_a_special" . "$first_token_type_a_combined" . "rr|ff|mm|nn|pp|pf|sp|ant|&e|ss|un|schaft|&a|&u|&o|&i|all|hab|haft|auf|aus|des|bei|selb|wo|fort|";

//(\[(ff|mm|nn|pp|pf|sp|ant|&e|ss|un|schaft|&a|&u|&o|&i|all|hab|haft|auf|aus|des|bei|selb|wo|fort|[blmnpvwfsxy])\])

$first_token_type_b_single = "1b_single"; // "gkhjcdtqz";
$first_token_type_b_special = "";
$first_token_type_b_multi = "1b_multi"; // "mpf|schm|zw|tt|nd|st|in|ng|ns|nk|ur|sch|schw|gegen|hat|da|vr|vor|inter|rück|ion|durch|ch|solch|";
$first_token_type_b_combined = "vr@l|d@r|nd@r|t@r|g@r|k@r|ch@r|nk@r|sch@r|st@r|l@l|g@l3|t@l3|ng@l3|d@l3|nd@l3|st@l3|nk@l3|";
$first_token_type_b_combined .= "k@l3|z@l3|sch@l3|f@r6|ch@l3|v@r6|w@r6|z@r|z@l3|da@r|ck@l|l@r6|tt@r|";
$first_token_type_b_single = "gkjcdstqzh";
$first_token_type_b_multi = "$first_token_type_b_special" . "$first_token_type_b_combined" . "mpf|schm|zw|tt|nd|st|in|ng|ns|nk|ur|sch|schw|gegen|hat|da|vr|ar|vor|inter|rück|ion|durch|ch|\^ch|ck|solch|";

// (\[(mpf|schm|zw|tt|nd|st|in|ng|ns|nk|ur|sch|schw|gegen|hat|da|vr|vor|inter|rück|ion|durch|ch|solch|[gkhjcdtqz])\])
//$first_token_type_b_combined = "d@r|nd@r|t@r|g@r|k@r|ch@r|nk@r|sch@r|st@r|l@l|b@l|g@l3|m@l|f@l|p@l|pf@l|v@l|sp@l|w@l|t@l3|ng@l3|d@l3|nd@l3|st@l3|nk@l3|";
//$first_token_type_b_combined .= "k@l3|z@l3|sch@l3|ch@l3|b@r6|sp@r6|f@r6|m@r6|p@r6|pf@r6|v@r6|w@r6|z@r|z@l3|da@r|n@r6|n@l|vr@l|ck@l|l@r6|tt@r|";
	
// vowel
$vowel_type_a = "(\[(i|au)\])";
$vowel_type_b = "(\[a\])?";
$all_narrow_vowels = "(\[(a|u|o|i|au|#n|#ns)\])?"; 

// second token type a:
$second_token_type_a_single = "2a_single"; // "bdcfwlxptvq";
$second_token_type_a_multi = "2a_multi"; // "pf|st|rr|nd|vr|ff|pp|tt|all|hab|haft|auf|aus|des|bei|wo|selb|da|vor|inter|ion|";
$second_token_type_a_combined = "d@r|nd@r|t@r|st@r|l@l|b@l|f@l|p@l|pf@l|v@l|w@l|t@l3|d@l3|nd@l3|st@l3|";
$second_token_type_a_combined .= "k@l3|b@r6|f@r6|p@r6|pf@r6|v@r6|w@r6|da@r|n@r6|n@l|vr@l|l@r6|tt@r|";
$second_token_type_a_single = "bdcfwlxptvqhns";
$second_token_type_a_multi = "$second_token_type_a_combined" . "pf|st|rr|nd|vr|ff|pp|tt|all|hab|haft|auf|aus|des|bei|wo|selb|da|vor|inter|ion|";

// (\[(pf|st|rr|nd|vr|ff|pp|tt|all|hab|haft|auf|aus|des|bei|wo|selb|da|vor|inter|ion|[bdcfwlxptvq])\])

$second_token_type_b_single = "2b_single"; // "jzghmykn";
$second_token_type_b_multi = "2b_multi"; //"ng|sch|nk|schm|mm|nn|ss|ch|mpf|sp|ns|zw|schw|ck|gegen|hat|vr|durch|solch|";
$second_token_type_b_combined = "g@r|k@r|ch@r|nk@r|sch@r|g@l3|m@l|sp@l|ng@l3|nk@l3|";
$second_token_type_b_combined .= "k@l3|z@l3|sch@l3|ch@l3|sp@r6|m@r6|z@r|z@l3|ck@l|";
$second_token_type_b_single = "jzgmyk";
$second_token_type_b_multi = "$second_token_type_b_combined" . "&a|&u|&i|&e|&o|-e|ng|sch|nk|schm|mm|nn|ss|ch|mpf|sp|ns|zw|schw|ck|gegen|hat|vr|durch|solch|";

// combine
$condition_aa = "(\\[($first_token_type_a_multi" . "[" . "$first_token_type_a_single" . "]" . ")\\])$all_narrow_vowels(\\[($second_token_type_a_multi" . "[" . "$second_token_type_a_single" . "]" . ")\\])";
$consequence_aa = "$1[#3]$3$5";
$rule_aa = "\"$condition_aa\" => \"$consequence_aa\";";
echo "// case: aa<br>$rule_aa<br>";

$condition_ab = "(\\[($first_token_type_a_multi" . "[" . "$first_token_type_a_single" . "]" . ")\\])$all_narrow_vowels(\\[($second_token_type_b_multi" . "[" . "$second_token_type_b_single" . "]" . ")\\])";
$consequence_ab = "$1[#0]$3$5";
$rule_ab = "\"$condition_ab\" => \"$consequence_ab\";";
echo "// case: ab<br>$rule_ab<br>";

$condition_ba = "(\\[($first_token_type_b_multi" . "[" . "$first_token_type_b_single" . "]" . ")\\])$all_narrow_vowels(\\[($second_token_type_a_multi" . "[" . "$second_token_type_a_single" . "]" . ")\\])";
$consequence_ba = "$1[#6]$3$5";
$rule_ba = "\"$condition_ba\" => \"$consequence_ba\";";
echo "// case: ba<br>$rule_ba<br>";

$condition_bb = "(\\[($first_token_type_b_multi" . "[" . "$first_token_type_b_single" . "]" . ")\\])$all_narrow_vowels(\\[($second_token_type_b_multi" . "[" . "$second_token_type_b_single" . "]" . ")\\])";
$consequence_bb = "$1[#3]$3$5";
$rule_bb = "\"$condition_bb\" => \"$consequence_bb\";";
echo "// case: bb<br>$rule_bb<br>";


// (\[(ng|sch|nk|schm|mm|nn|ss|ch|mpf|sp|ns|zw|schw|ck|gegen|hat|vr|durch|solch|[jzghmykn)\])
/*
// combine
// case: aba
$case_aba_condition = "(\\[($first_token_type_a_multi\[$first_token_type_a_single\])\\])$vowel_type_b(\\[($second_token_type_a_multi\[$second_token_type_a_single\])\\])";
$case_aba_consequence = "$1[#2]$3$4";
$case_aba_rule = "\"$case_aba_condition\" => \"$case_aba_consequence\"";
//$case_aba_condition = "$first_token_type_a_multi $first_token_type_a_single $vowel_type_b $second_token_type_a_multi $second_token_type_a_single";
//$case_aba_consequence = "cons";
//$case_aba_rule = "$case_aba_condition $case_aba_consequence";
 
// case aab, bba, bbb => aab, bb(ab)
// aab
$case_aab_condition = "(\\[($first_token_type_a_multi\[$first_token_type_a_single\])\\])$vowel_type_a(\\[($second_token_type_b_multi\[$second_token_type_b_single\])\\])";
$case_aab_consequence = "$1\[#4\]$3$5";
$case_aab_rule = "\"$case_aab_condition\" => \"$case_aab_consequence\"";
// bba
$case_bba_condition = "(\\[($first_token_type_b_multi\[$first_token_type_b_single\])\\])$vowel_type_b(\\[($second_token_type_a_multi\[$second_token_type_a_single\])\\])";
$case_bba_consequence = "$1\[#4\]$3$4";
$case_bba_rule = "\"$case_bba_condition\" => \"$case_bba_consequence\"";
// bbb
$case_bbb_condition = "(\\[($first_token_type_b_multi\[$first_token_type_b_single\])\\])$vowel_type_b(\\[($second_token_type_b_multi\[$second_token_type_b_single\])\\])";
$case_bbb_consequence = "$1\[#4\]$3$4";
$case_bbb_rule = "\"$case_bbb_condition\" => \"$case_bbb_consequence\"";

// case aaa, baa, bab => (ab)aa, bab
// aaa
$case_aaa_condition = "(\\[($first_token_type_a_multi\[$first_token_type_a_single\])\\])$vowel_type_a(\\[($second_token_type_a_multi\[$second_token_type_a_single\])\\])";
$case_aaa_consequence = "$1\[#4\]$3$5";
$case_aaa_rule = "\"$case_aaa_condition\" => \"$case_aaa_consequence\"";
// baa
$case_baa_condition = "(\\[($first_token_type_b_multi\[$first_token_type_b_single\])\\])$vowel_type_a(\\[($second_token_type_a_multi\[$second_token_type_a_single\])\\])";
$case_baa_consequence = "$1\[#4\]$3$5";
$case_baa_rule = "\"$case_baa_condition\" => \"$case_baa_consequence\"";
// bab
$case_bab_condition = "(\\[($first_token_type_b_multi\[$first_token_type_b_single\])\\])$vowel_type_a(\\[($second_token_type_b_multi\[$second_token_type_b_single\])\\])";
$case_bab_consequence = "$1\[#4]\$3$4";
$case_bab_rule = "\"$case_bab_condition\" => \"$case_bab_consequence\"";

//"(\[(tt|nd|st|in|ng|ns|nk|ur|sch|schw|zw|ff|mm|nn|pp|sp|ant|haft|[blmnpvwf])\])(\[(i|au)\])?(\[(pf|st|rr|ll|nd|ff|pp|tt|ss|[bdycfwlxptvq])\])" => "$1[#6]$3$5";
//"(\[(tt|nd|st|in|ng|ns|nk|ur|sch|schw|[cdtqblmnpvwf])\])(\[(i|au)\])(\[(ng|nk|sch|^sch|schm|mm|nn|ss|ch|mpf|sp|ns|zw|schw|ck|pf|st|rr|ll|nd|ff|pp|tt|[bdycfwlxptvqjzghmkn])\])" => "$1[#6]$3$5";
echo "$case_aba_rule<br>$case_aab_rule<br>$case_bba_rule<br>$case_bbb_rule<br>$case_aaa_rule<br>$case_baa_rule<br>$case_bab_rule<br>";

*/
?>

  </body>
</html>
