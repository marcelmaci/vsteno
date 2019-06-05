<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Titel</title>
  </head>
  <body>

<?php

// functions
function GetRegexOrString($array) {
    $temp1 = "";
    foreach ($array as $element)
         $temp1 .= ($element === end($array)) ? "$element" : "$element|";
    return $temp1;
}


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

// rules: combination + vowel + distance (string)
// for each combination (2 tokens out of groups) a vowel group can be given a specific distance
$rules_list = array( 
    "R1" => array( "C1", "V2", "2" ),
    "R2" => array( "C1", "V3", "4" ),
    "R3" => array( "C3", "V1", "6" )
);

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
    $token_groups_string[$key] = (mb_strlen($temp2) === 0) ? "[(?:$temp1)]" : "[(?:$temp1)(?:$temp2)?]";
}

// vowels
$vowel_groups_string = array();
foreach ($vowel_groups as $key => $vowels) {
    $temp1 = "";
    // calculate or-string
    $temp1 = GetRegexOrString($vowels);
    // write full regex and insert
    $vowel_groups_string[$key] = "[$temp1]";
}

// show result
//foreach ($token_groups_string as $key => $string) echo "$key: $string<br>";
//foreach ($vowel_groups_string as $key => $string) echo "$key: $string<br>";

// generate rules
$rules_list_string = array();
foreach ($rules_list as $key => $rule) {
    // get combinations
    list($combination_key, $vowel_key, $distance) = $rule;
    list($group1_key, $group2_key) = $group_combinations[$combination_key];
    $group1 = $token_groups_string[$group1_key];
    $group2 = $token_groups_string[$group2_key];
    $vowel = $vowel_groups_string[$vowel_key];
    // write rule and insert
    $rules_list_string[$key] = "\"($group1)($vowel)($group2)\" => \"$1[#$distance]$2$3\"; // $key";
}

// show result (copy it to VSTENO model)
foreach ($rules_list_string as $key => $string) echo "$string<br>";


?>
  </body>
</html>
