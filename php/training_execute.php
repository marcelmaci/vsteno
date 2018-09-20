<?php 
require "vsteno_template_top.php";
require_once "dbpw.php";

function die_more_elegantly( $text ) {
    echo "$text";
    echo '<a href="input.php"><br><button>zurück</button></a><br><br>';   
    require_once "vsteno_template_bottom.php";
    die();
}

function StripOutWordSeparators( $word ) {
        $stripped = "";
        for ($i = 0; $i < mb_strlen($word); $i++) {
            $character = mb_substr($word, $i, 1);
            if (($character !== "|") && ($character !== "\\")) $stripped .= $character;
        }
        return $stripped;
}

echo "<h1>Datenbank</h1><br>";

if ((!$_SESSION['user_logged_in']) || ($_SESSION['user_privilege']) < 1) {
   die_more_elegantly("<p>Sie müssen über einen User- oder Superuser-Account verfügen und eingeloggt sein, um Schreibrechte für die Datenbank zu haben.</p>");
}

// Create connection
$conn = Connect2DB();

// Check connection
if ($conn->connect_error) {
    die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
}

$i = 0;
while (isset($_POST["txtstd$i"])) {

    /*
    // check if account exists already
    $sql = "SELECT * FROM users WHERE username='$safe_username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        die_more_elegantly("Username bereits verwendet.<br>");
    } else {
        echo "Username ist noch frei.<br>";
    }
    */


    // insert word $i from form to db
    // read & escape data
    $safe_original = $conn->real_escape_string(StripOutWordSeparators($_POST["original$i"]));
    if (isset($_POST["lowercase$i"]) && ($_POST["lowercase$i"]) == "1") $safe_original = mb_strtolower($safe_original);
    $safe_txtstd = $conn->real_escape_string($_POST["txtstd$i"]);
    $safe_chkstd = $_POST["chkstd$i"];
    $safe_txtprt = $conn->real_escape_string($_POST["txtprt$i"]);
    $safe_chkprt = $_POST["chkprt$i"];
    $safe_txtcut = $conn->real_escape_string($_POST["txtcut$i"]);
    $safe_chkcut = $_POST["chkcut$i"];
    $safe_result = $conn->real_escape_string($_POST["result$i"]);
    $safe_comment = $conn->real_escape_string($_POST["comment$i"]);
    
    $something_to_write = (($safe_result === "wrong$i") || ($safe_chkstd) || ($safe_chkprt) || ($safe_chkcut)); // write only wrong results / corrections to database

    if ($something_to_write) {   
    
        // prepare data
        $safe_txtstd = ($safe_chkstd) ? $safe_txtstd : "";
        $safe_txtprt = ($safe_chkprt) ? $safe_txtprt : "";
        $safe_txtcut = ($safe_chkcut) ? $safe_txtcut : "";
        //echo "result: $safe_result<br>";
        switch ($safe_result) {
                case "correct$i" : $safe_result = "c"; break;
                case "wrong$i" : $safe_result = "w"; break;
                default : $safe_result = "u"; break;
        }
        $safe_user_id = htmlspecialchars($_SESSION['user_id']);
        $sql = "INSERT INTO purgatorium (word, std, prt, composed, result, user_id, comment)
        VALUES ( '$safe_original', '$safe_txtstd', '$safe_txtprt', '$safe_txtcut', '$safe_result', '$safe_user_id', '$safe_comment')";
        //echo "query: $sql";
        
        if ($conn->query($sql) === TRUE) {
            echo "Word $safe_original written to PURGATORIUM<br>"; // (query: $sql)<br>";
        } else {
            die_more_elegantly("Fehler: " . $sql . "<br>" . $conn->error . "<br>");
        }

    }
    $i++;
}
$conn->close();
echo '<a href="input.php"><button>zurück</button></a><br><br>';  
require "vsteno_template_bottom.php";
?>
