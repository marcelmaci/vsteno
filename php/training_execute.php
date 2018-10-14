<?php 
/* VSTENO - Vector Steno Tool with Enhanced Notational Options
 * (c) 2018 - Marcel Maci (m.maci@gmx.ch)
 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 */
 
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

echo "<h1>Datenbank</h1>";

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
    
    // write wrong and correct results / corrections to database
    // IMPORTANT: if none of the of R or W is selected and no checkbox is checked, nothing will be written to the database
    $something_to_write = (($safe_result === "wrong$i") || ($safe_result === "correct$i") || ($safe_chkstd) || ($safe_chkprt) || ($safe_chkcut)); 

    if ($something_to_write) {   
    
        // prepare data
        // assign variables for selection "wrong" (F)
        if ($safe_result === "wrong$i") {
            $safe_txtstd = ($safe_chkstd) ? $safe_txtstd : "";
            $safe_txtprt = ($safe_chkprt) ? $safe_txtprt : "";
            $safe_txtcut = ($safe_chkcut) ? $safe_txtcut : "";
        } else {
            // if selection is "correct" (F) => suppose that std and prt are correct (and assign the to variables by default)
            // assign "" to $safe_txtcut if checkbox is not checked
            $safe_txtcut = ($safe_chkcut) ? $safe_txtcut : "";
        }
        
        //echo "result: $safe_result<br>";
        switch ($safe_result) {
                case "correct$i" : $safe_result = "c"; break;
                case "wrong$i" : $safe_result = "w"; break;
                default : $safe_result = "u"; break;
        }
        $safe_user_id = htmlspecialchars($_SESSION['user_id']);
        $purgatorium = GetDBName( "purgatorium" );
        $sql = "INSERT INTO $purgatorium (word, std, prt, composed, result, user_id, comment)
        VALUES ( '$safe_original', '$safe_txtstd', '$safe_txtprt', '$safe_txtcut', '$safe_result', '$safe_user_id', '$safe_comment')";
        echo "<p>QUERY: $sql</p>";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p>Wort <b>$safe_original</b> wurde in PURGATORIUM (<b>➟$purgatorium</b>) geschrieben.</p>"; // (query: $sql)<br>";
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
