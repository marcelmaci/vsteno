<?php

require_once "vsteno_template_top.php";
require_once "session.php";

$model = $_SESSION['model_standard_or_custom'];
$_SESSION['model_standard_or_custom'] = ($model === 'standard') ? 'custom' : 'standard';
$model = $_SESSION['model_standard_or_custom'];

echo "<h1>Model</h1>
    <p>Das aktive Model wurde geändert auf: <b><i>$model</i></b>.<br>";
echo '<a href="input.php"><br><button>zurück</button></a></p>';   
require_once "vsteno_template_bottom.php";

?>
