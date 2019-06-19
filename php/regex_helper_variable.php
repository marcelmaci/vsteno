<?php require "vsteno_fullpage_template_top.php"; ?>

<h1>RULE-GENERATOR</h1>
<p>This programm creates spacer rules based on tokens of the actual model. Copy them to the rules file.</p>

<?php
require_once "data.php";
require_once "regex_helper_functions.php";

GenerateSpacerRulesAndPrintData();

echo '<a href="input.php"><br><button>zurück</button></a>';

?>
<?php require "vsteno_fullpage_template_bottom.php"; ?>