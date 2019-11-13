<?php
function StripOutCommentsRegexHelper($text) {
    $output = preg_replace("/\/\*.*?\*\//", "", $text);     // replace /* ... */ comments by empty string
    //$output = preg_replace("/\/\/.*?\n/", "[\n\r]", $output);     // replace // ... \n comments by \n
     $output = preg_replace("/\/\/.*?(?=\n)/", "", $output);     // replace // ... \n comments empty string followed by \n (careful with that modification ...
    return $output;
}
// create token groups
$token_groups = array();

function GenerateTokenGroups( $steno_tokens_master ) {
    global $token_groups;
    foreach ($steno_tokens_master as $token => $definition) {
        $group = $definition[23];
        if (($group !== 0) && (mb_strlen($group)>0)) {
            //echo "assign $token to group $group<br>";
            $group_array = explode(":", $group); // same token can be attributed to different groups
            foreach ($group_array as $group_name) $token_groups[$group_name][] .= $token;   
        }
    }
}

function GetRegexOrString($array) {
    $output = "";
    foreach ($array as $element) {
         $escaped = preg_quote($element); // in tokens like ^CH escaping is necessary => escape it always! 
         $escaped1 = preg_replace( "/\//", "\\/", $escaped); // additional escaping: / is not escaped by preg_quote!
         //echo "<br>element: $element escaped: $escaped escaped1: $escaped1<br>";
         $output .= ($element === end($array)) ? "$escaped1" : "$escaped1|";
    }
    return $output;
}

function GetRegexOrStringAndPrint($array) {
    $output = "";
    foreach ($array as $element) {
         $escaped = preg_quote($element); // in tokens like ^CH escaping is necessary => escape it always! 
         $escaped1 = preg_replace( "/\//", "\\/", $escaped); // additional escaping: / is not escaped by preg_quote!
         //echo "<br>element: $element escaped: $escaped escaped1: $escaped1<br>";
         $output .= ($element === end($array)) ? "$escaped1" : "$escaped1|";
         echo "$element ";
    }
    return $output;
}

// permutations
$permutations = array();

// define data
//require_once "regex_helper_import.php"; // $token_groups array
$token_variants = array(); // nice side-effect: not necessary since token_combiner copies offset 23 of token header (and hence the group!)

function GenerateGroupCombinations() {
    global $group_combinations;
    $group_combinations_variable = $_SESSION['spacer_token_combinations'];
    $group_combinations = ImportGroupCombinationsFromVariable( $group_combinations_variable );
}
//echo "group_combinations: $group_combinations_variable";
//var_dump($group_combinations);

// like token groups but for vowels
function GenerateVowelGroups() {
    global $vowel_groups;
    $vowel_groups_variable = $_SESSION['spacer_vowel_groups'];
    $vowel_groups = ImportVowelGroupsFromVariable($vowel_groups_variable);
    //var_dump($vowel_groups);
}

// rules: combination + vowel + distance (string) + mandatory/optional
// for each combination (2 tokens out of groups) a vowel group can be given a specific distance
function GenerateRulesList() {
    global $rules_list;
    $rules_list_variable = $_SESSION['spacer_rules_list'];
    $rules_list = ImportRulesListFromVariable( $rules_list_variable);
}

// generate rules
//$rules_for_patching = GenerateSpacerRulesAndPrintData();
//echo "$rules_for_patching";

function GenerateSpacerRules() {
    global $permutations, $token_groups, $vowel_groups, $rules_list, $token_variants, $group_combinations, $vowel_groups_string, $token_groups_string;
    $regex_rules = array();
    //var_dump($vowel_groups);
    
    // generate variants
    // token groups
    $token_groups_string = array();
    foreach ($token_groups as $key => $tokens) {
        // calculate regex or-chain
        $temp1 = GetRegexOrString($tokens);
        $temp2 = GetRegexOrString($token_variants[$key]);
        // write full regex and insert (use non capturing groups ?:)
        $token_groups_string[$key] = (mb_strlen($temp2) === 0) ? "\[(?:$temp1)\]" : "\[(?:$temp1)(?:$temp2)?\]";
        // calculate permutations
        $permutations[$key] = count($tokens) * (count($token_variants[$key])+1);
    }

    // vowels
    $vowel_groups_string = array();
    foreach ($vowel_groups as $key => $vowels) {
        $temp1 = "";
        // calculate or-string
        $temp1 = GetRegexOrString($vowel_groups[$key]);
        // write full regex and insert
        $vowel_groups_string[$key] = "\[(?:$temp1)\]";
        // calculate permutations
        $permutations[$key] = count($vowels);
    }
    //var_dump($vowel_groups_string);
    
    // show result
    //echo "<h2>TOKENS</h2>"; foreach ($token_groups_string as $key => $string) echo "$key: $string (" . $permutations[$key]. ")<br>";
    //echo "<h2>VOWELS</h2>"; foreach ($vowel_groups_string as $key => $string) echo "$key: $string (" . $permutations[$key]. ")<br>";

    // generate rules
    $total_permutations = 0;
    $rules_list_string = array();
    foreach ($rules_list as $key => $rule) {
        // get combinations
        list($combination_key, $vowel_key, $distance, $optional) = $rule;
        list($group1_key, $group2_key) = $group_combinations[$combination_key];
        $group1 = $token_groups_string[$group1_key];
        $group2 = $token_groups_string[$group2_key];
        $vowel = $vowel_groups_string[$vowel_key];
        // calculate permutations
        $new_permutations = $permutations[$group1_key] * ($permutations[$vowel_key] + (($optional === "?") ? 1 : 0)) * $permutations[$group2_key];
        $total_permutations += $new_permutations;
        // write rule and insert
        $rules_list_string[$key] = mb_strtolower("\"($group1)($vowel)$optional($group2)\" => \"$1[#$distance]$2$3\";") . " // $key|$combination_key: $group1_key#$vowel_key:$distance#$group2_key ($new_permutations)";
    }   

    // show result (copy it to VSTENO model)
    //echo "<h2>RULES</h2><p>// statistics: these rules cover approximately <b>$total_permutations</b> token combinations.</p>"; foreach ($rules_list_string as $key => $string) echo "$string<br>";
    $output = "// AUTOGENERATED statistics: these rules cover approximately $total_permutations token combinations.<br>\n"; 
    foreach ($rules_list_string as $key => $string) $output .= "$string<br>\n";

    // statistics
    //echo "<h2>STATISTICS</h2>"; 
    //echo "<p>These rules cover approximately <b>$total_permutations</b> token combinations.</p>";
    return $output;
}
    
function GenerateSpacerRulesAndPrintData() {
    global $permutations, $token_groups, $vowel_groups, $rules_list, $token_variants, $group_combinations, $token_groups_string, $vowel_groups_string;
    $regex_rules = array();
    
    // BUG:  (1)                            (2)
    // "spacer_vowel_groups" :=             "spacer_vowel_groups" := "
    //         "V1:[A,E],                                       V1:[A,E],
    //         V2:[I,AU]";                                      V2:[I,AU]";
    //
    // (1) doesn't get parsed correctly (in the import already => empty session variable)
    // (2) only works after adapting ImportVowelGroupsFromVariable() - filter out [ \t\n\r] otherwise JSON_decode wont work
    // 
    // problem is (probably) the fact that in the beginning I didn't know the /s option for REGEX ... so all the old parsing functions
    // work with tricks (e.g. filtering out special chars like \r). Unfortunately this leads to problems here ...
    // Conclusion: Parsing should be debugged and all critical REGEX functions should use s-flag
    // For the moment: use syntax (2) (should work)
    
    //var_dump($vowel_groups); // this is correct!
    //echo "session(spacer_vowel_groups): " . $_SESSION['spacer_vowel_groups'] . "<br>";
    //$vowel_groups = ImportVowelGroupsFromVariable($_SESSION['spacer_vowel_groups']);
    //var_dump($vowel_groups); // this is correct!
    
    
    // sort arrays for easier reading (only when results are printed)
    ksort($token_groups); // ascending by key
    ksort($vowel_groups);
    ksort($rules_list);
    ksort($token_variants);
    ksort($group_combinations);
    
    // generate variants
    // token groups
    echo "<h2>IMPORT</h2>";
    $token_groups_string = array();
    foreach ($token_groups as $key => $tokens) {
        // calculate regex or-chain
        echo "$key: ";
        $temp1 = GetRegexOrStringAndPrint($tokens);
        $temp2 = GetRegexOrStringAndPrint($token_variants[$key]);
        echo "<br>";
        // write full regex and insert (use non capturing groups ?:)
        $token_groups_string[$key] = (mb_strlen($temp2) === 0) ? "\[(?:$temp1)\]" : "\[(?:$temp1)(?:$temp2)?\]";
        // calculate permutations
        $permutations[$key] = count($tokens) * (count($token_variants[$key])+1);
    }

    // vowels
    $vowel_groups_string = array();
    foreach ($vowel_groups as $key => $vowels) {
        $temp1 = "";
        //echo "calculate vowel group: $key<br>";
        // calculate or-string
        var_dump($vowel_groups[$key]);
        $temp1 = GetRegexOrString($vowel_groups[$key]);
        //echo "result(temp1): $temp1<br>";
        // write full regex and insert
        $vowel_groups_string[$key] = "\[(?:$temp1)\]";
        // calculate permutations
        $permutations[$key] = count($vowels);
    }

    // show result
    //var_dump($vowel_groups_string);
    echo "<h2>TOKENS</h2>"; foreach ($token_groups_string as $key => $string) echo "$key: $string (" . $permutations[$key]. ")<br>";
    echo "<h2>VOWELS</h2>"; foreach ($vowel_groups_string as $key => $string) echo "$key: $string (" . $permutations[$key]. ")<br>";

    // generate rules
    $total_permutations = 0;
    $rules_list_string = array();
    foreach ($rules_list as $key => $rule) {
        // get combinations
        list($combination_key, $vowel_key, $distance, $optional) = $rule;
        list($group1_key, $group2_key) = $group_combinations[$combination_key];
        $group1 = $token_groups_string[$group1_key];
        $group2 = $token_groups_string[$group2_key];
        $vowel = $vowel_groups_string[$vowel_key];
        // calculate permutations
        $new_permutations = $permutations[$group1_key] * ($permutations[$vowel_key] + (($optional === "?") ? 1 : 0)) * $permutations[$group2_key];
        $total_permutations += $new_permutations;
        // write rule and insert
        $rules_list_string[$key] = mb_strtolower("\"($group1)($vowel)$optional($group2)\" => \"$1[#$distance]$2$3\";") . " // $key|$combination_key: $group1_key#$vowel_key:$distance#$group2_key ($new_permutations)";
    }   

    // show result (copy it to VSTENO model)
    echo "<h2>RULES</h2><p>// statistics: these rules cover approximately <b>$total_permutations</b> token combinations.</p>"; foreach ($rules_list_string as $key => $string) echo "$string<br>";
    
    // statistics
    echo "<h2>STATISTICS</h2>"; 
    echo "<p>These rules cover approximately <b>$total_permutations</b> token combinations.</p>";
}

function ImportRulesListFromVariable( $string ) {
    // $test_string = "R1: [C1, V1, D1, ?], R2: [C1, V2, D2,], R3:[C1,V2,D3, ]";
    $test_string = $string;
    $test_string = StripOutCommentsRegexHelper($test_string); // strip out comments
    $test_string1 = preg_replace("/[ \t\n\r]/", "", $test_string); // strip out spaces
    $test_string2 = preg_replace("/(.*?):\[(.*?),(.*?),(.*?),(.*?)\](,|$)/", "\"$1\":[\"$2\",\"$3\",\"$4\",\"$5\"]$6", $test_string1);
    $test_string3 = "{" . $test_string2 . "}";
    //echo "<h2>TEST (rules list)</h2>";
    //echo "<p>$test_string</p>";
    //echo "<p>$test_string1</p>";
    //echo "<p>$test_string2</p>";
    //echo "<p>$test_string3</p>";
    $test_string_php = array();
    $test_string_php = json_decode($test_string3, true); // parameter true = decode to an associative array (instead of std_object)
    //$test_string_boomerang = json_encode($test_string_php);
    //echo "<p>$test_string_boomerang (should be equal to preceeding)</p>";
    //var_dump($test_string_php);
    //echo "<br><br>";
    //var_dump($rules_list);
    return $test_string_php;
}

function ImportGroupCombinationsFromVariable( $string ) {
    $test_string = $string;
    $test_string = StripOutCommentsRegexHelper($test_string); // strip out comments
    $test_string1 = preg_replace("/[ \t\n\r]/", "", $test_string); // strip out spaces
    $test_string2 = preg_replace("/(.*?):\[(.*?),(.*?)](,|$)/", "\"$1\":[\"$2\",\"$3\"]$4", $test_string1);
    $test_string3 = "{" . $test_string2 . "}";
    //echo "<h2>TEST (group combinations)</h2>";
    //echo "<p>$test_string</p>";
    //echo "<p>$test_string1</p>";
    //echo "<p>$test_string2</p>";
    //echo "<p>$test_string3</p>";
    $test_string_php = array();
    $test_string_php = json_decode($test_string3, true); // parameter true = decode to an associative array (instead of std_object)
    //$test_string_boomerang = json_encode($test_string_php);
    //echo "<p>$test_string_boomerang (should be equal to preceeding)</p>";
    //var_dump($test_string_php);
    //echo "<br><br>";
    //var_dump($rules_list);
    return $test_string_php;
}

function ImportVowelGroupsFromVariable($string ) {
    $test_string = $string;
    $test_string = StripOutCommentsRegexHelper($test_string); // strip out comments
    $test_string1 = preg_replace("/[ \t\n\r]/", "", $test_string); // strip out spaces
    // several steps needed, because the number of vowels is undefined
    //echo "<h2>TEST (vowel groups)</h2>";
    $test_string2 = preg_replace("/(^|,)([^,]*?):/", "$1\"$2\":", $test_string1); // add "
    //echo "<p>1:$test_string2</p>";
    $test_string2 = preg_replace("/(\[)([a-zA-Z0-9]*?),/", "$1\"$2\",", $test_string2); // add "
    //echo "<p>2:$test_string2</p>";
    $test_string2 = preg_replace("/(?<=,)([a-zA-Z0-9]*?)(?=\])/", "\"$1\"", $test_string2); // add "
    //echo "<p>3:$test_string2</p>";
    $test_string3 = preg_replace("/(?<=,)([a-zA-Z0-9]*?)(?=,)/", "\"$1\"", $test_string2); // add "
    //echo "<p>4:$test_string3</p>";
    /////////////////////////////////
    $test_string3 = "{" . $test_string3 . "}";
    //echo "<p>5:$test_string3</p>";
    $test_string_php = array();
    $test_string_php = json_decode($test_string3, true); // parameter true = decode to an associative array (instead of std_object)
    $test_string_boomerang = json_encode($test_string_php);
    //echo "<p>$test_string_boomerang (should be equal to preceeding)</p>";
    //var_dump($test_string_php);
    //echo "<br><br>";
    //var_dump($rules_list);
    return $test_string_php;
}

?>