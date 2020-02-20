<?php session_start();
// start with inverted display mode after login
// do this operation without testing password
$_SESSION['display_mode'] = "inverted";
$_SESSION['token_color'] = "white";
$_SESSION['title_color'] = "white";
$_SESSION['introduction_color'] = "white";
$_SESSION['output_page_number_color'] = "white";
$_SESSION['output_line_number_color'] = "white";

require_once "vsteno_template_top.php";
require_once "session.php";
require_once "dbpw.php";

function die_more_elegantly( $text ) {
    echo "$text";
    echo '<a href="' . $_SERVER['HTTP_REFERER'] . '"><br><button>zurück</button></a><br><br>';   
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
$safe_username = $conn->real_escape_string($_POST['username']);
$safe_password = $_POST['password'];

// check if account exists already
$sql = "SELECT * FROM users WHERE username='$safe_username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $salt = $row['salt'];
    //echo "Username: " . $row['username'] . "<br>";
    //echo "PWHash: " . $row['pwhash'] . "<br>";
    //echo "Entered: " . $safe_pwhash . "<br>";
    $safe_pwhash = $conn->real_escape_string(hash( 'sha256', $safe_password . $salt ));
    
    if (($safe_username === $row['username']) && ($safe_pwhash === $row['pwhash'])) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_username'] = $safe_username;
        $_SESSION['user_privilege'] = $row['privilege'];
        $_SESSION['user_id'] = $row['user_id'];

        // write last_activity date
        $timestamp = date('Y-m-d G:i:s');
        $sql = "INSERT INTO users (last_activity) WHERE username='$safe_username'
        VALUES ( '$timestamp')"; // query doesn't work
        $result = $conn->query($sql);   // test if error!

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
