<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Titel</title>
  </head>
  <body>
<h1>RULE-GENERATOR</h2>
<p>This programm creates spacer rules based on tokens of the actual model. Copy them to the rules file.</p>

<?php

// functions
function GetRegexOrString($array) {
    $temp1 = "";
    foreach ($array as $element)
         $temp1 .= ($element === end($array)) ? "$element" : "$element|";
    return $temp1;
}

// permutations
$permutations = array();

// define data
require_once "regex_helper_import.php"; // $token_groups array
$token_variants = array(); // nice side-effect: not necessary since token_combiner copies offset 23 of token header (and hence the group!)

// combination = preceeding + following token that can then be combined with different vowels (see rules_list)
$group_combinations = array(
    "C1" => array( "L1", "R1" ),
    "C2" => array( "L1", "R2" ),
    "C3" => array( "L1", "R3" ),
    "C4" => array( "L2", "R1" ),
    "C5" => array( "L2", "R2" ),
    "C6" => array( "L2", "R3" ),
    "C7" => array( "L3", "R1" ),
    "C8" => array( "L3", "R2" ),
    "C9" => array( "L3", "R3" ),
    "C10" => array( "L4", "R1" ),
    "C11" => array( "L4", "R2" ),
    "C12" => array( "L4", "R3" )
);

// like token groups but for vowels
$vowel_groups = array(
    "V1" => array( "A", "O", "U" ),
    "V2" => array( "I", "AU" )
);

// rules: combination + vowel + distance (string) + mandatory/optional
// for each combination (2 tokens out of groups) a vowel group can be given a specific distance
$rules_list = array( 
    "R1" => array( "C1", "V1", "D1", "?" ), // "?" means optional (used as regex token)
    "R2" => array( "C1", "V2", "D2", "" ),   // "" means mandatory
    "R3" => array( "C2", "V1", "D3", "?" ),
    "R4" => array( "C2", "V2", "D4", "" ),
    "R5" => array( "C3", "V1", "D5", "?" ),
    "R6" => array( "C3", "V2", "D6", "" ),
    "R7" => array( "C4", "V1", "D7", "?" ),
    "R8" => array( "C4", "V2", "D7", "" ),
    "R9" => array( "C5", "V1", "D8", "?" ),
    "R10" => array( "C5", "V2", "D9", "" ),
    "R11" => array( "C6", "V1", "D10", "?" ),
    "R12" => array( "C6", "V2", "D11", "" ),
    "R13" => array( "C7", "V1", "D12", "?" ),
    "R14" => array( "C7", "V2", "D14", "" ),
    "R15" => array( "C8", "V1", "D15", "?" ),
    "R16" => array( "C8", "V2", "D16", "" ),
    "R17" => array( "C9", "V1", "D17", "?" ),
    "R18" => array( "C9", "V2", "D18", "" ),
    "R19" => array( "C10", "V1", "D19", "?" ),
    "R20" => array( "C10", "V2", "D20", "" ),
    "R21" => array( "C11", "V1", "D21", "?" ),
    "R22" => array( "C11", "V2", "D22", "" ),
    "R23" => array( "C12", "V1", "D23", "?" ),
    "R24" => array( "C12", "V2", "D24", "" ),
     
);

/*
// abstract examples and description

// define data
// groups of consonants that share same (or similar) as to distance needed between tokens
// example: b, p, pp as a preceeding consonant (all have a straight line to the left and have a round bottom)
$token_groups = array(
    "G1" => array( "a1", "a2", "a3"),
    "G2" => array( "b1", "b2" ),
    "G3" => array( "c1" )
);

// allows to check for combined tokens (check is optional, i.e. combined token "identifier" may or may not be present)
// example: @[lr].? checks for combinations like b@r, b@l, p@r, p@l etc.
$token_variants = array(
    "G1" => array( "X", "Y" ),
    "G2" => array( "Y" )
);

// combination = preceeding + following token that can then be combined with different vowels (see rules_list)
$group_combinations = array(
    "C1" => array( "G1", "G2" ),
    "C2" => array( "G1", "G3" ),
    "C3" => array( "G2", "G3" )
);

// like token groups but for vowels
$vowel_groups = array(
    "V1" => array( "v1", "v2", "v3" ),
    "V2" => array( "v2", "v4"),
    "V3" => array( "v5", "v6")
);

// rules: combination + vowel + distance (string) + mandatory/optional
// for each combination (2 tokens out of groups) a vowel group can be given a specific distance
$rules_list = array( 
    "R1" => array( "C1", "V2", "2", "?" ), // "?" means optional (used as regex token)
    "R2" => array( "C1", "V3", "4", "" ),   // "" means mandatory
    "R3" => array( "C3", "V1", "6", "" )
);
*/


// generate rules
$regex_rules = array();

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
    $temp1 = GetRegexOrString($vowels);
    // write full regex and insert
    $vowel_groups_string[$key] = "\[(?:$temp1)\]";
    // calculate permutations
    $permutations[$key] = count($vowels);
}

// show result
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

?>
  </body>
</html>
