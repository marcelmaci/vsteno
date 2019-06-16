<?php require "vsteno_fullpage_template_top.php"; ?>

<h1>RULE-GENERATOR</h2>
<p>This programm creates spacer rules based on tokens of the actual model. Copy them to the rules file.</p>

<?php
require_once "regex_helper_functions.php";

GenerateSpacerRulesAndPrintData();

?>
<?php require "vsteno_fullpage_template_bottom.php"; ?>