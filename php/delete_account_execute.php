<?php

require_once "vsteno_template_top.php";
require_once "session.php";
require_once "dbpw.php";

function die_more_elegantly( $text ) {
    echo "$text";
    echo '<a href="create_account.php"><br><button>zurück</button></a><br><br>';   
    require_once "vsteno_template_bottom.php";
    die();
}

// Create connection
$conn = Connect2DB();

// Check connection
if ($conn->connect_error) {
    die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
}

// prepare data
$safe_username = htmlspecialchars($_POST['username']);
$safe_password = htmlspecialchars($_POST['password']);

// check if account exists already
$sql = "SELECT * FROM users WHERE username='$safe_username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    //echo "Username: " . $row['username'] . "<br>";
    //echo "PWHash: " . $row['pwhash'] . "<br>";
    //echo "Entered: " . $safe_pwhash . "<br>";
    $salt = $row['salt'];
    $safe_pwhash = hash( 'sha256', $safe_password . $salt );
    
    if (($safe_username === $row['username']) && ($safe_pwhash === $row['pwhash'])) {
        $userid_to_delete = $row['user_id'];
        //echo "userid: " . $row['user_id'] . "-" . $userid_to_delete .  "<br>";
         // sql to delete a record
        $sql = "DELETE FROM users WHERE user_id='$userid_to_delete'";
        //echo "QUERY: $sql<br>";
        
        if ($conn->query($sql) === TRUE) {
            echo "<h1>Konto löschen</h1>Das Konto wurde gelöscht.<br>Sie wurden ausgeloggt.<br>";
            $_SESSION['user_logged_in'] = false;
            $_SESSION['user_username'] = "";
            $_SESSION['user_privilege'] = 0;
            $_SESSION['model_standard_or_custom'] = "standard";
            echo "Model wurde auf standard gesetzt.<br>";
        } else {
            die_more_elegantly("Fehler: " . $conn->error);
        }
    
        // delete custom model
        $custom_model = "XM" . str_pad($userid_to_delete, 7, '0', STR_PAD_LEFT);
        $sql = "DELETE FROM models WHERE name='$custom_model'";
        //echo "QUERY: $sql<br>";
        
        if ($conn->query($sql) === TRUE) {
            echo "Ihr Custom-Model ($custom_model) wurde gelöscht.<br>";
        } else {
            die_more_elegantly("Fehler: " . $conn->error);
        }
    
        // delete purgatorium
        $purgatorium = "XP" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT);
        $sql = "DROP TABLE $purgatorium";
        $result = $conn->query($sql);
        if ($result == TRUE) {
            echo "Purgatorium ($purgatorium) gelöscht.<br>";
        } else {
            die_more_elegantly("Fehler beim Löschen von Purgatorium ($purgatorium).<br>");
        }
    
        // delete elysium
        $elysium = "XE" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT);
        $sql = "DROP TABLE $elysium";
        $result = $conn->query($sql);
        if ($result == TRUE) {
            echo "Elysium ($elysium) gelöscht.<br>";
        } else {
            die_more_elegantly("Fehler beim Löschen von Elysium ($elysium).<br>");
        }
        
         // delete olympus
        $olympus = "XO" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT);
        $sql = "DROP TABLE $olympus";
        $result = $conn->query($sql);
        if ($result == TRUE) {
            echo "Olympus ($olympus) gelöscht.<br>";
        } else {
            die_more_elegantly("Fehler beim Löschen von Elysium ($olympus).<br>");
        }
    
    } else {
        die_more_elegantly("Falsche Login-Daten.<br>");
    }
} else {
    die_more_elegantly("Falsche Login-Daten.<br>");
}

?>
<br>
<a href="input.php"><button>zurück</button></a><br><br>

<?php

require_once "vsteno_template_bottom.php";

?>
