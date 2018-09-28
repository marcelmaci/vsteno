<?php

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

echo "<h1>Zeichen</h1>";
    
$model_name = "XM" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT);

if ($_POST['action'] == 'speichern') {
    $update_font = $conn->real_escape_string($_POST['font_as_text']);
    $sql = "UPDATE models
            SET font = '$update_font'
            WHERE name='$model_name';";
    $result = $conn->query($sql);

    if ($result == TRUE) {
        echo "<p>Die neuen Zeichen (Font) wurden gespeichert.</p>";    
    } else {
        //echo "Query: $sql<br>";
        die_more_elegantly("Fehler beim Speichern der Zeichen.<br>");
    }
} else {
    echo "<p>Hier können Sie die Zeichen (Font) Ihres eigenen Stenosystems editieren und speichern.</p><p><b>ACHTUNG:</b><br><i>Es wird KEINE Syntax-Prüfung vorgenommen. Falls die Definitionen
    Fehler aufweisen, werden Sie NICHT darauf hingewiesen! (Sorry guys ... technisch (noch nicht) möglich;-)</i></p>";
}

// check if account exists already
$sql = "SELECT * FROM models WHERE name='$model_name'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $font = $row['font'];
} else {
    die_more_elegantly("Keine Zeichen (Font) vorhanden.<br>");
}

echo "<form action='edit_font.php' method='post'>
        <textarea id='font_as_text' name='font_as_text' rows='45' cols='120'>" . htmlspecialchars($font) . "</textarea><br>
        <input type='submit' name='action' value='speichern'>
        </form>";

require_once "vsteno_template_bottom.php";

?>
