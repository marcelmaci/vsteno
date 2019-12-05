<?php require "vsteno_template_top.php"; ?>
<h1>MLOAD</h1>
<form action='load_models.php' method='post'>

<?php
require_once "constants.php";
require_once "import_model.php";
require_once "dbpw.php";


function LoadModelAndCopyToDB($model) {
    // Create connection
    $conn = Connect2DB();

    // Check connection
    if ($conn->connect_error) {
        die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
    }

    // get entire model
    $text = LoadModelToShareFromFile("$model");
    // get separate parts
    $result = preg_match("/(#BeginSection\(header\).*?#EndSection\(header\)).*?(#BeginSection\(font\).*?#EndSection\(font\)).*?(#BeginSection\(rules\).*?#EndSection\(rules\))/s", $text, $matches);
    if ($result === 1) {
        //var_dump($matches);
        $part = array("header", "font", "rules");
        //var_dump($part); echo "<br>";
        //$header = $matches[1];
        //$font = $matches[2];
        //$rules  = $matches[3];
        //echo "----------------------- HEADER<br>$header<br>------------------FONT<br>$font<br>---------------------RULES<br>$rules<br>";
        $i = 1;
        foreach ($part as $section) {
            //echo "section: $section<br>";
            $update_text = $conn->real_escape_string($matches[$i]);
            //$update_text = $matches[$i];
            $sql = "UPDATE models
                SET $section = '$update_text'
                WHERE name='$model';";
            //echo "QUERY: $sql<br>";
            $result = $conn->query($sql);

            if ($result == TRUE) {
                echo "<br>Modell [$model] section '$section' wurde gespeichert.";    
            } else {
                //echo "Query: $sql<br>";
                die_more_elegantly("Fehler beim Speichern von $section.<br>");
            }
            $i++;
        }
        echo "<br>";
   } else {
        echo "<p>An error ocurred in preg_match() - malformed model?</p>";
    }
}

function WriteModelSectionToDB($model, $section) {
    global $conn;
    
}

if ($_SESSION['user_logged_in'] && ($_SESSION['user_privilege'] == 2)) {
    if (isset($_POST['action']) && ($_POST['action'] === "load")) {
        //var_dump($_POST);
        // load standard models
        foreach ($standard_models_list as $model => $description) {
            if ($_POST["$model"] === "yes") LoadModelAndCopyToDB($model);
            else echo "<br>Nicht gespeichert: $model";
            
        }
        // load custom model
        $user_model = getDBUserModelName();
        if ($_POST["$user_model"] === "yes") LoadModelAndCopyToDB($user_model);
        else echo "DON'T LOAD: $user_model<br>";
        echo '<a href="input.php"><br><button>zurück</button></a><br><br>';
    } else {
        echo "<p>Markieren Sie die Modelle, die Sie von den Dateien (Server) in die Datenbank laden wollen:</p>";

        echo "<p>STANDARD:<br>";
        foreach ($standard_models_list as $model => $description) {
            echo "<input type='checkbox' id='$model' name='$model' value='yes'> [$model]<br>";
        }
        $user_model = getDBUserModelName();
        echo "<br>CUSTOM:<br><input type='checkbox' name='$user_model' value='yes'>[$user_model]";
        echo "</p>";
    
        echo "<input type='hidden' id='action' name='action' value='load'>";
        echo "<input type='submit' value='Bestätigen'>";
    }
} else {
    echo "<p>Privilege Error: " . $_SESSION['user_privilege'] . "</p>";
    echo "<p>Sie benötigen Super-User-Rechte, um Modelle in die Server-Datenbank zu laden.</p>";
    echo '<a href="input.php"><br><button>zurück</button></a><br><br>';   
}


?>
</form>
<?php require "vsteno_template_bottom.php"; ?>
