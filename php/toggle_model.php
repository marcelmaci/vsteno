<?php

require_once "vsteno_template_top.php";
require_once "session.php";

$model = $_SESSION['model_standard_or_custom'];
$_SESSION['model_standard_or_custom'] = ($model === 'standard') ? 'custom' : 'standard';
$model = $_SESSION['model_standard_or_custom'];

echo "<h1>Model</h2>
    <p>Das aktive Model wurde geändert auf: <b><i>$model</i></b></p>";
echo '<a href="input.php"><br><button>zurück</button></a><br><br>';   
require_once "vsteno_template_bottom.php";

?>
