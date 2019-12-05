<?php require "vsteno_template_top.php"; ?>
<h1>MDUMP</h1>
<form action='dump_models.php' method='post'>

<?php
require_once "constants.php";
require_once "import_model.php";

$path = "../ling/";

if ($_SESSION['user_logged_in'] && ($_SESSION['user_privilege'] == 2)) {
    if (isset($_POST['action']) && ($_POST['action'] === "dump")) {
        // dump standard models
        foreach ($standard_models_list as $model => $description) {
            $text = LoadModelToShareFromDatabase($model);
            WriteTextToFile("../ling/$model.dmp", $text);
        }
        // dump custom model
        $model = getDBUserModelName();
        $text = LoadModelToShareFromDatabase($model);
        WriteTextToFile("$path" . "$model.dmp", $text);
        echo "<p>Zielpfad: $path<br>Dateiname: MODELLNAME.dmp</p>";
        echo "<p>Die Modelle wurden auf dem Server gespeichert.</p>";
        
        echo '<a href="input.php"><br><button>zurück</button></a><br><br>';
    } else {
        echo "<p>Die folgenden Modelle auf Disk (Server) dumpen:</p>";

        echo "<p>STANDARD: ";
        foreach ($standard_models_list as $model => $description) echo "[$model] ";
        echo "<br>CUSTOM: [" . getDBUserModelName() . "]";
        echo "</p>";
        echo "<p>Die Modelle werden mit der Erweiterung .dmp gespeichert (z.B. $default_model.dmp)</p>";
        echo "<input type='hidden' id='action' name='action' value='dump'>";
        echo "<input type='submit' value='Bestätigen'>";
    }
} else {
    echo "<p>Privilege Error: " . $_SESSION['user_privilege'] . "</p>";
    echo "<p>Sie benötigen Super-User-Rechte, um Modelle auf dem Server dumpen zu können.</p>";
    echo '<a href="input.php"><br><button>zurück</button></a><br><br>';   
}


?>
</form>
<?php require "vsteno_template_bottom.php"; ?>
