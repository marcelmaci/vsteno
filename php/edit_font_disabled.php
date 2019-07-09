<?php require_once "vsteno_template_top.php"; ?>
<h1>Deaktiviert</h1>
<p>Wegen Inkompatibilitäten zwischen VPAINT und der SE1 rev0 ist die Speicherfunktion sicherheitshalber deaktivert.</p>
<p>(Sie kann aktiviert werden, indem in js2db_code.js in der Funktion writeDataToDB() der Wert von Form Action von edit_font_disabled.php 
auf edit_font.php geändert wird (danach mit ../js/buildjs.sh JS-Code generieren).</p>
<a href="input.php"><br><button>zurück</button></a>
<?php require_once "vsteno_template_bottom.php"; ?>
