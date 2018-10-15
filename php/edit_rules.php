<?php

require_once "constants.php";
require_once "vsteno_template_top.php";
require_once "session.php";
require_once "dbpw.php";

function die_more_elegantly( $text ) {
    echo "$text";
    echo '<a href="edit_rules.php"><br><button>zurück</button></a><br><br>';   
    require_once "vsteno_template_bottom.php";
    die();
}

// Create connection
$conn = Connect2DB();

// Check connection
if ($conn->connect_error) {
    die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
}

echo "<h1>Regeln</h1>";

//echo "model: " . $_SESSION['actual_model'] . " std/custom: #" . $_SESSION['model_standard_or_custom'] . "# model_name: $model_name<br>";
    
if (($_SESSION['model_standard_or_custom'] === 'standard') && ($_SESSION['user_privilege'] < 2)) {
    echo "<p>Sie arbeiten aktuell mit dem Model <b><i>standard</i></b>. Wenn Sie Ihr eigenes Stenografie-System bearbeiten wollen, ändern sie das Model auf <b><i>custom</i></b> und rufen Sie diese Seite erneut auf.</p>";
    echo "<p><a href='toggle_model.php'><button>ändern</button></a></p>";
} else {
    
    switch ($_SESSION['model_standard_or_custom']) {
        case 'standard' : $model_name = $_SESSION['actual_model']; break; 
        case 'custom' : $model_name = "XM" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT); break;
    }
    //echo "model_name: $model_name<br>";
    
    if ($_POST['action'] == 'speichern') {
        $update_rules = $conn->real_escape_string($_POST['rules_as_text']);
        $sql = "UPDATE models
            SET rules = '$update_rules'
            WHERE name='$model_name';";
        $result = $conn->query($sql);

        if ($result == TRUE) {
            echo "<p>Die neuen Regeln wurden gespeichert.</p>";    
        } else {
            //echo "Query: $sql<br>";
            die_more_elegantly("Fehler beim Speichern der Regeln.<br>");
        }
    } else {
        echo "<p>Hier können Sie die Regeln Ihres eigenen Stenosystems editieren und speichern.</p><p><b>ACHTUNG:</b><br><i>Es wird KEINE Syntax-Prüfung vorgenommen. Falls die Regeln
        Fehler aufweisen, werden Sie NICHT darauf hingewiesen! (Sorry guys ... technisch (noch nicht) möglich;-)</i></p>";
    }

    // check if account exists already
    $sql = "SELECT * FROM models WHERE name='$model_name'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $rules = $row['rules'];
    } else {
        echo "<p>QUERY: $sql</p>";
        die_more_elegantly("Keine Regeln vorhanden.<br>");
    }

    // use javascript for textarea in order to prevent predefined function of tab to change focus (use it for indentation instead)
    echo "<form action='edit_rules.php' method='post'>
        <textarea id='rules_as_text' name='rules_as_text' rows='35' cols='120' spellcheck='false' 
        onkeydown=\"if(event.keyCode===9){var v=this.value,s=this.selectionStart,e=this.selectionEnd;this.value=v.substring(0, s)+'\t'+v.substring(e);this.selectionStart=this.selectionEnd=s+1;return false;}\"
        >" . htmlspecialchars($rules) . "</textarea><br>
        <input type='submit' name='action' value='speichern'>
        </form>";
}

require_once "vsteno_template_bottom.php";

?>
