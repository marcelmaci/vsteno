<?php 

require "linguistics.php";

// variables
$original_word = 'Wachtmeisters';
//$original_word = "Lebenspartner";
//$original_word = "Eulenspiegel";
//$original_word = "Wolkenkratzer";
//$original_word = "Versicherungsvertreter";
//$original_word = "kopfgesteuert";
//$original_word = "Originalbild";
//$original_word = 'Dampfschifffahrtskapitänsjackenknopfloch'; // doesn't work
//$original_word = "Abteilungsleiterin";
//$original_word = "Blumenverkäufer"; // nope
//$original_word = "Wahrscheinlichkeitsrechnung"; // wrong
//$original_word = "Versicherungsgesellschaft"; // wrong
//$original_word = "Ameisenbär";
//$original_word = "Kaffeetasse";

// hunspell dictionary
$dictionary = "de_CH"; //"de_CH";

list($test_string, $test_array) = create_word_list("$original_word");
echo "$test_string<br>";
//var_dump($test_array);
$length = count($test_array[0]);
echo "normal:<br>";
for ($l=0;$l<$length; $l++) {
    echo "line $l: ";
    for ($r=0;$r<count($test_array[$l]); $r++) {
        echo $test_array[$l][$r][0] . " ";
    }
    echo "<br>";
}
echo "with dash:<br>";
for ($l=0;$l<$length; $l++) {
    echo "line $l: ";
    for ($r=0;$r<count($test_array[$l]); $r++) {
        echo $test_array[$l][$r][1] . " ";
    }
    echo "<br>";
}
$shell_command = /* escapeshellcmd( */"echo \"$test_string\" | hunspell -d de_CH -a" /* ) */;
echo "$shell_command<br>";
echo "hunspell: ";
echo exec("$shell_command",$o) . "<br>";
var_dump($o);
$offset = 1;
for ($l=0;$l<$length; $l++) {
    for ($r=0;$r<count($test_array[$l]); $r++) {
        //echo "result: " . $test_array[$l][$r][1] . ": >" . $o[$offset+1] . "<<br>";
        if (($o[$offset] === "*") || (($o[$offset+1][0] === "&") && (mb_strpos($o[$offset], $test_array[$l][$r][1]) != false))) $test_array[$l][$r][2] = "*"; // normal word (without dash)
        else $test_array[$l][$r][2] = "-";
        $offset+=2;
    }
}
/*
$offset = 1;
for ($l=0;$l<$length; $l++) {
    for ($r=0;$r<count($test_array[$l]); $r++) {
        //echo "result: " . $test_array[$l][$r][1] . ": >" . $o[$offset+1] . "<<br>";
        echo ">" . $o[$offset][0] . "<>" . $o[$offset] . "<<br>";
        echo "str_pos: >" . $test_array[$l][$r][1] . "<>" . (mb_strpos($o[$offset], $test_array[$l][$r][1]) != false) . "<<br>";
        if (($o[$offset][0] === "&") && (mb_strpos($o[$offset], $test_array[$l][$r][1]) != false)) $test_array[$l][$r][3] = "*"; // word with dash (word-)
        else $test_array[$l][$r][3] = "-";
        $offset+=2;
    }
}
*/
echo "results:<br>";
echo "normal:<br>";
for ($l=0;$l<$length; $l++) {
    echo "line $l: ";
    for ($r=0;$r<count($test_array[$l]); $r++) {
        echo $test_array[$l][$r][2] . " ";
    }
    echo "<br>";
}
echo "with dash:<br>";
for ($l=0;$l<$length; $l++) {
    echo "line $l: ";
    for ($r=0;$r<count($test_array[$l]); $r++) {
        echo $test_array[$l][$r][3] . " ";
    }
    echo "<br>";
}

$result = recursive_search(0,0, $test_array);
echo "<br>---------------------<br>recursive search: $result<br>";
var_dump($result);

/*
// single steps
$word = hyphenate($original_word);
echo "Silben: $original_word => $word<br>";

$word_array = word2array($word);
//var_dump($word_array);

$word_list = array2capitalizedStringList($word_array);
//echo "<br>Wordlist: $word_list<br>";

$composed_words = capitalizedStringList2composedWordsArray($word_list);
//echo "<br>Liste zusammengesetzer Wörter: <br>";
//var_dump($composed_words);

$final_result = composedWordsArray2hyphenatedString($composed_words);
echo "<br>Result: $final_result<br>";

$final_result_hyphenated = hyphenate($final_result);
echo "<br>Hyphens: $final_result_hyphenated<br>";
*/
/*
exec("echo \"Kaffeetasse\" | hunspell -d $dictionary -a -m -s", $o); // assign output to $o (= array)
echo system("echo \"Kaffeetasse\" | hunspell -d $dictionary -a -m -s"); // assign output to $o (= array)

echo "<br>hunspell ($dictionary) result:<br>";
var_dump($o);
*/
/*
echo "<br>hunspell dictionaries:<br>";
echo shell_exec("hunspell -D -a -h"); // assign output to $o (= array)
*/

/*
echo "<br><br>pspell as an alternative:<br>";
$composed_words1 = PSPELLcapitalizedStringList2composedWordsArray($word_list);

$final_result1 = composedWordsArray2hyphenatedString($composed_words1);
echo "<br>Result: $final_result1<br><br>";

// array test
echo "test with array: <br>";
$test = array("Kaffeetasse", "Wohnungseigentum", "Wasserschloss", "Wachtmeister", "Abteilungsleiter", "Birnbaum", "Abfalleimer", "Mondgesicht",
"Hackbraten", "Möbeldesign", "Testsuite", "Bankkonto", "Kontostand", "Ablagefläche", "Fischzucht", "Bahndamm", "Kaffeehaus", "Eichenholz",
"Schreibtisch", "Schreibtischtäter");

for ($i=0; $i<count($test); $i++) {
    $hunspell = analyze_composed_words_and_hyphenate($test[$i], "hunspell");
    $pspell = analyze_composed_words_and_hyphenate($test[$i], "pspell");
    echo "Word: " . $test[$i] . ": HUNSPELL: $hunspell <=> PSPELL: $pspell<br>"; 
}
*/
?>
