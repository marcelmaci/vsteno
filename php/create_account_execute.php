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

function RandomString( $length ) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    for ($i = 0; $i < $length; $i++) {
        $randstring .= $characters[rand(0, strlen($characters))];
    }
    return $randstring;
}

echo "<h1>Konto anlegen</h1><br>";
// Create connection
$conn = Connect2DB();

// Check connection
if ($conn->connect_error) {
    die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
}

// prepare data
$safe_username = $conn->real_escape_string($_POST['username']);
$safe_password = $_POST['password'];  // don't escape pw here, because numbers will be escaped
$safe_email = $conn->real_escape_string($_POST['email']);
$safe_captcha = mb_strtolower($_POST['captcha']);
$safe_realname = $conn->real_escape_string($_POST['realname']);
$safe_infos = ($_POST['info'] === 'infono') ? 0 : 1;

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
$salt = RandomString(8);
$account_pwhash = $conn->real_escape_string(hash('sha256', $_POST['password'] . $salt));
$sql = "INSERT INTO users (username, email, realname, newsletter, salt, pwhash, privilege, visibility_model, visibility_database )
VALUES ( '$safe_username', '$safe_email', '$safe_realname', '$safe_infos', '$salt', '$account_pwhash', '$account_privilege', '0', '0')";

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

// create custom databases
// sql to create purgatorium table
    $purgatorium = "XP" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT);
    $sql = "CREATE TABLE IF NOT EXISTS $purgatorium (
    word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    std VARCHAR(50),
    prt VARCHAR(50),
    composed VARCHAR(50),
    result CHAR(1),
    user_id INT(6),
    comment VARCHAR(250),
    insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Purgatorium ($purgatorium) angelegt<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }

    // sql to create elysium table
    // add entry for trickster (= REGEX entry that checks for declined or conjugated forms like: Zelt => Zelte, Zelten, Zeltes, Zelts etc.
    $elysium = "XE" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT);
    $sql = "CREATE TABLE IF NOT EXISTS $elysium (
    word_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(30) NOT NULL,
    number_forms INT(1),
    recommended_form INT(1),
    submitted_by INT(6),
    reviewed_by INT(6),
    single_bas VARCHAR(50),
    single_std VARCHAR(50),
    single_prt VARCHAR(50),
    separated_bas VARCHAR(50),
    separated_std VARCHAR(50),
    separated_prt VARCHAR(50),
    insertion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Elysium ($elysium) angelegt.<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }

// copy standard model or create empty model
if ($_POST['model'] === 'standard') {
    $user_id = $_SESSION['user_id'];
    $model_name = "XM" . str_pad($user_id, 7, '0', STR_PAD_LEFT);
   
   // read data
    $sql = "SELECT * FROM models WHERE name='99999_default'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $header = $conn->real_escape_string($row['header']);
        $font = $conn->real_escape_string($row['font']);
        $rules = $conn->real_escape_string($row['rules']);
        $sql = "INSERT INTO models(user_id, name, header, font, rules)
                VALUES  ( '$user_id', '$model_name', '$header', '$font', '$rules');";
        $result = $conn->query($sql);
        if ($result === TRUE) {
            echo "Standard-Model angelegt.<br>";
        } else {
            die_more_elegantly("Fehler: " . $sql . "<br>" . $conn->error . "<br>");
        }
    } else {
        die_more_elegantly("Standard-Model nicht gefunden.<br>");
    }
} else {
    $user_id = $_SESSION['user_userid'];
    $sql = "INSERT INTO models(user_id, name, header, font, rules)
            VALUES  ( '$user_id', '', '', '');";
    $conn->query($sql);
    if ($conn->query($sql) === TRUE) {
            echo "Leeres Model angelegt.<br>";
        } else {
            die_more_elegantly("Fehler: " . $sql . "<br>" . $conn->error . "<br>");
        }
}

// show account info to user
echo "<h2>Kontodaten</h2>";
echo "<p>Bitte notieren Sie sich Ihre Kontoinformation oder drucken Sie sie aus:<br><br>";
echo "Login: $safe_username<br>";
echo "Password: $safe_password<br>";
echo "E-Mail: $safe_email<br></p>";
/*
echo "<h2>Datenbanken</h2>";
echo "<p>Es wurden die Datenbanken $purgatorium (Purgatorium) und $elysium (Elysium) eröffnet</p>.";
*/
/*
echo "<h1>Zugriffsrechte</h1>";
echo "<p>Sie haben folgende Zugriffsrechte:</p>";
echo "<h2>Standard-System</h2>";
echo "<p><b>Purgatorium:</b> Lese- und Schreibrecht<br><b>Elysium:</b> Leserecht</p>";
echo "<h2>Eigenes System</h2>";
echo "<p><b>Purgatorium:</b> Lese- und Schreibrecht<br><b>Elysium:</b> Lese-/Schreibrecht</p>";
*/

echo "<h2>Purgatorium</h2>";
echo "<p>Sie können nun den Trainingsmodus benützen, um falsche Stenogramme zu markieren. Sämtliche Vorschläge werden zunächst provisorisch im 
    <a href='https://de.wiktionary.org/wiki/purgatorium' target='_blank'>Purgatorium</a> (nomen est omen) aufgenommen. Die Vorschläge werden periodisch begutachtet und - falls berechtigt und 
    richtig - ins <a href='https://de.wiktionary.org/wiki/Elysium' target='_blank'>Elysium</a> (das definitive Wörterbuch) aufgenommen.<p>";
echo "<p>Für Ihr eigenes Stenografie-System (falls Sie eines definieren) entscheiden Sie selber, welche Vorschläge (die von Ihnen oder anderen Nutzer/innen stammen) Sie in Elysium
    aufnehmen.</p>";
echo '<a href="input.php"><button>zurück</button></a><br><br>';   
echo "<h2>Post Scriptum</h2><p>Wir stellen uns hier übrigens auf den Standpunkt, dass das Purgatorium nicht die 'Vor-Hölle', sondern der 'Vor-Himmel' ist ... ;-)</p><br>";
$conn->close();

require_once "vsteno_template_bottom.php";

?>