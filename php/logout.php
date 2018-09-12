<?php
require_once "vsteno_template_top.php";
require_once "session.php";

$_SESSION['user_logged_in'] = false;
$_SESSION['user_username'] = "";
$_SESSION['user_privilege'] = 0;
?>
<h1>Ausloggen</h1>
<p>Sie haben sich ausgeloggt.</p>
<a href="input.php"><button>zum Programm</button></a><br><br>   

<?php
require_once "vsteno_template_bottom.php";

?>