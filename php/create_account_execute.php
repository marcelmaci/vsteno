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

echo "<h1>Konto anlegen</h1><br>";
// Create connection
$conn = Connect2DB();

// Check connection
if ($conn->connect_error) {
    die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
}

// prepare data
$safe_username = htmlspecialchars($_POST['username']);
$safe_password = htmlspecialchars($_POST['password']);
$safe_email = htmlspecialchars($_POST['email']);
$safe_captcha = htmlspecialchars( mb_strtolower($_POST['captcha']));

// check length of username and password
$lg_length = mb_strlen($safe_username);
$pw_length = mb_strlen($safe_password);

if (($lg_length < 8) || ($lg_length > 10)) {
    die_more_elegantly("Benutzername muss 8-10 Zeichen lang sein.<br>");
}

if ($pw_length < 8) {
    die_more_elegantly("Passwort muss mindestens 8 Zeichen lang sein.<br>");
}

// check if captcha is correct
if ( $safe_captcha !== $_SESSION['captcha']) {
    die_more_elegantly("Falsches Captcha!<br>Gefragt: " . htmlspecialchars($_SESSION['captcha']) . "<br>Eingegeben: " . $safe_captcha . "<br>");
} else {
    echo "Captcha ist richtig.<br>";
}

// check if account exists already
$sql = "SELECT * FROM users WHERE username='$safe_username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    die_more_elegantly("Username bereits verwendet.<br>");
} else {
    echo "Username ist noch frei.<br>";
}

// insert new account in db
$account_privilege = normal_user;
$account_pwhash = hash('sha256', $_POST['password']);
$sql = "INSERT INTO users (username, email, pwhash, privilege)
VALUES ( '$safe_username', '$safe_email', '$account_pwhash', '$account_privilege')";

if ($conn->query($sql) === TRUE) {
    echo "Neues Konto angelegt.<br>";
} else {
    die_more_elegantly("Fehler: " . $sql . "<br>" . $conn->error . "<br>");
}

// get user id
$sql = "SELECT * FROM users WHERE username='$safe_username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $db_user_id = $row['user_id'];
} else {
    die_more_elegantly("Username nicht gefunden.<br>");
}

// mark user as logged in
$_SESSION['user_logged_in'] = true;
$_SESSION['user_privilege'] = normal_user;
$_SESSION['user_username'] = $safe_username;
$_SESSION['user_id'] = $db_user_id;    
    
// show account info to user
echo "<h2>Kontodaten</h2>";
echo "<p>Bitte notieren Sie sich Ihre Kontoinformation oder drucken Sie sie aus:<br><br>";
echo "Login: $safe_username<br>";
echo "Password: $safe_password<br>";
echo "E-Mail: $safe_email<br></p>";

echo "<h2>Purgatorium</h2>";
echo "<p>Willkommen im <a href='https://de.wiktionary.org/wiki/purgatorium' target='_blank'>Purgatorium</a>!</p><p>Mit Ihrem Konto können Sie nun den Training-Modus der Vollversion 
      benützen, um falsche Stenogramme zu markieren und zu korrigieren. ";
echo "Sämtliche Vorschläge werden zunächst provisorisch aufgenommen (daher der Name <i>Purgatorium</i>;-).</p><p>Vorschläge werden periodisch begutachtet und - falls berechtigt und richtig - ";
echo "ins <a href='https://de.wiktionary.org/wiki/Elysium' target='_blank'>Elysium</a> (die definitive Datenbank) aufgenommen.<p>";

echo '<a href="input.php"><button>zurück</button></a><br><br>';   
echo "<h2>Post Scriptum</h2><p>Wir stellen uns hier übrigens auf den Standpunkt, dass das Purgatorium nicht die 'Vor-Hölle', sondern der 'Vor-Himmel' ist ... ;-)</p><br>";
$conn->close();

require_once "vsteno_template_bottom.php";

?>