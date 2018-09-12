<?php

require_once "vsteno_template_top.php";
require_once "session.php";
require_once "dbpw.php";

function die_more_elegantly( $text ) {
    echo "$text";
    echo '<a href="input.php"><br><button>zurück</button></a><br><br>';   
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
$safe_username = htmlspecialchars($_SESSION['user_username']);

// check if account exists already
$sql = "SELECT * FROM users WHERE username='$safe_username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<h1>Benutzerdaten</h1>";
    echo "<p>Username: " . $row['username'] . "<br>";
    echo "User-ID: " . $row['user_id'] . "<br>";
    echo "Privilege: " . $row['privilege'] . "<br>";
    echo "E-Mail: " . $row['email'] . "<br>";
    echo "Registrierung: " . $row['reg_date'] . "<br>";
    
} else {
    die_more_elegantly("<p>Kein Eintrag für User $safe_username.</p>");
}
echo '<a href="input.php"><br><button>zurück</button></a><br><br>';   
   
require_once "vsteno_template_bottom.php";


$conn->close();
?>

