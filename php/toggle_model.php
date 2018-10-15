<?php

require_once "vsteno_template_top.php";
require_once "dbpw.php";
require_once "session.php";

$model = $_SESSION['model_standard_or_custom'];
$_SESSION['model_standard_or_custom'] = ($model === 'standard') ? 'custom' : 'standard';
$model = $_SESSION['model_standard_or_custom'];
$model_purgatorium = GetDBName( "purgatorium" );
$model_elysium = GetDBName( "elysium" );
$model_olympus = GetDBName( "olympus" );


echo "<h1>Model</h1>
    <p>Das aktive Model wurde geändert auf: <b><i>$model</i></b> (" . $_SESSION['actual_model'] . ":$model_purgatorium/$model_elysium/$model_olympus).<br>";
echo '<a href="input.php"><br><button>zurück</button></a></p>';   
require_once "vsteno_template_bottom.php";

?>
