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
$safe_pwhash = hash( 'sha256', $safe_password );

// check if account exists already
$sql = "SELECT * FROM users WHERE username='$safe_username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    //echo "Username: " . $row['username'] . "<br>";
    //echo "PWHash: " . $row['pwhash'] . "<br>";
    //echo "Entered: " . $safe_pwhash . "<br>";
    
    if (($safe_username === $row['username']) && ($safe_pwhash === $row['pwhash'])) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_username'] = $safe_username;
        $_SESSION['user_privilege'] = $row['privilege'];
        $_SESSION['user_id'] = $row['user_id'];

    } else {
        die_more_elegantly("Falsche Login-Daten.<br>");
    }
} else {
    die_more_elegantly("Falsche Login-Daten.<br>");
}
?>
<h1>Einloggen</h1>
<p>Sie haben Sich erfolgreich eingeloggt!</p>
<a href="input.php"><button>zurück</button></a><br><br>

<?php

require_once "vsteno_template_bottom.php";
$conn->close();
?>
