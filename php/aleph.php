<?php

// aleph is the place where you can see the whole universe ... ;-)
// and yes it is a reference to Jorge Luis Borges ... ;-)

// aleph allows a superuser to decide whether proposed changements to the dictionary go 
// from purgatury (purgatorium) to elysium (= good proposition that is definitely included
// or to nirvana (= it will definitely be deleted from purgatorium and will become digital
// dust ... ;-)

// aleph works in batch mode: it takes the first entry in purgatorium and asks what to 
// do with it.

require_once "vsteno_template_top.php";
require_once "session.php";
require_once "dbpw.php";

function die_more_elegantly( $text ) {
        echo "$text";
        echo '<a href="input.php"><br><button>zurück</button></a><br><br>';   
        require_once "vsteno_template_bottom.php";
        die();
}

if (($_SESSION['user_logged_in']) && ($_SESSION['user_privilege'])) {

    echo "
    <h1>Aleph</h1>
    <p>Hier wird entschieden, welche Vorschläge aus dem Purgatorium definitiv ins
    Wörterbuch (Elysium) aufgenommen werden und welche unwiderbringlich ins Nirvana befördert werden ...</p>
    <h2>Purgatorium</h2>";

    // Create connection
    $conn = Connect2DB();

    // Check connection
    if ($conn->connect_error) {
        die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
    }

    // prepare data
    //$safe_username = htmlspecialchars($_SESSION['user_username']);

    // check if account exists already
    $sql = "SELECT * FROM purgatorium";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
       
        echo "Einträge: " . $result->num_rows . "<br><br>";
        $row = $result->fetch_assoc(); 
        echo "<table>";
        echo "<tr><td><u>Eintrag</u></td><td><u>Vorschlag</u></td><td><u>Korrektur</u></td></tr>";
        
        echo "<tr><td>Wort:<br>STD:<br>PRT:<br>CMP:<br></td>";
        $temp_word = $row['word'];
        $temp_std = $row['std'];
        $temp_prt = $row['prt'];
        $temp_cmp = $row['composed'];
        
        echo "<td>$temp_word<br>$temp_std<br>$temp_prt<br>$temp_cmp</td>";
        echo "<td>felder für korrektur</td></tr>";
      
        echo "</table>";
    
    } else {
        die_more_elegantly("<p>Kein Eintrag in Purgatorium.</p>");
    }
    echo '<a href="input.php"><br><button>zurück</button></a><br><br>';   
   
    require_once "vsteno_template_bottom.php";
    $conn->close();

} else {
    echo "<p>Sie benötigen Superuser-Rechte und müssen eingeloggt sein, um Aleph zu benutzen.</p>";
    echo '<a href="input.php"><br><button>zurück</button></a><br><br>';   
}    
?>