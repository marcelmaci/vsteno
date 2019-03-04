<?php

require_once "vsteno_template_top.php";
require_once "dbpw.php";
require_once "session.php";
require_once "import_model.php";

$model = $_SESSION['model_standard_or_custom'];
$_SESSION['model_standard_or_custom'] = ($model === 'standard') ? 'custom' : 'standard';
$model = $_SESSION['model_standard_or_custom'];
$model_purgatorium = GetDBName( "purgatorium" );
$model_elysium = GetDBName( "elysium" );
$model_olympus = GetDBName( "olympus" );
$model_name = ($_SESSION['model_standard_or_custom'] === "custom") ? "XM" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT) : $_SESSION['actual_model'] ;

// reset all session variables to raw value
InitializeSessionVariables(); // initialize with raw values
$_SESSION['model_standard_or_custom'] = $model;
$_SESSION['actual_model'] = $model_name;
$_SESSION['rules_count'] = null; // reset rules statistics
$text_to_parse = LoadModelFromDatabase($_SESSION['actual_model']);
$output = StripOutComments($text_to_parse);
$output = StripOutTabsAndNewlines($output);
$header_section = GetSection($output, "header");
$session_subsection = GetSubSection($header_section, "session");
ImportSession(); // initialize with values specified by model
        
echo "<h1>Model</h1>
    <p>Das aktive Model wurde geändert auf: <b><i>$model</i></b><br>($model_name:$model_purgatorium/$model_elysium/$model_olympus).<br>";
echo '<a href="input.php"><br><button>zurück</button></a></p>';   
require_once "vsteno_template_bottom.php";

?>
