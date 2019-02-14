<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Titel</title>
  </head>
  <body>

<?php

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
$first_token_type_b_combined = "d@r|nd@r|t@r|g@r|k@r|ch@r|nk@r|sch@r|st@r|l@l|g@l3|t@l3|ng@l3|d@l3|nd@l3|st@l3|nk@l3|";
$first_token_type_b_combined .= "k@l3|z@l3|sch@l3|f@r6|ch@l3|v@r6|w@r6|z@r|z@l3|da@r|ck@l|l@r6|tt@r|";
$first_token_type_b_single = "gkjcdstqzh";
$first_token_type_b_multi = "$first_token_type_b_special" . "$first_token_type_b_combined" . "mpf|schm|zw|tt|nd|st|in|ng|ns|nk|ur|sch|schw|gegen|hat|da|vr|ar|vor|inter|rück|ion|durch|ch|solch|";

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
$second_token_type_b_multi = "$second_token_type_b_combined" . "ng|sch|nk|schm|mm|nn|ss|ch|mpf|sp|ns|zw|schw|ck|gegen|hat|vr|durch|solch|";

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
