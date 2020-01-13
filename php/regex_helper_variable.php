<?php require "vsteno_fullpage_template_top.php"; ?>

<h1>RULE-GENERATOR</h1>
<p>This programm creates spacer rules based on tokens of the actual model. Copy them to the rules file.</p>

<?php
global $include_for_regex_gen;
$include_for_regex_gen = true;

require_once "data.php";
$include_for_regex_gen = false;

require_once "regex_helper_functions.php";

GenerateSpacerRulesAndPrintData();

echo '<a href="input.php"><br><button>zurück</button></a>';

?>
<?php require "vsteno_fullpage_template_bottom.php"; ?>