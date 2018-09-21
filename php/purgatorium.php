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
        echo '<a href="purgatorium.php"><br><button>zurück</button></a><br><br>';   
        require_once "vsteno_template_bottom.php";
        die();
}

function prepare_aleph() {
        $_SESSION['original_text_format'] = "normal";       // must be in normal mode for work with aleph (otherwise a part of the parsing process won't be executed!)
        if ($_SESSION['output_format'] === "debug") $_SESSION['output_format'] = "inline";              // can mess up database tables if "debug" is selected (set it to inline to be safe)
}

if (($_SESSION['user_logged_in']) && ($_SESSION['user_privilege'])) {

    prepare_aleph();
    
    echo "
    <h1>Purgatorium</h1>
    <p>Hier wird entschieden, welche Vorschläge aus dem Purgatorium definitiv ins
    Wörterbuch (Elysium) aufgenommen werden und welche unwiderbringlich ins Nirvana befördert werden ...</p><p>Wählen Sie einen der untenstehenden Einträge aus, um ihn zu bearbeiten.</p>
    <h1>Einträge</h1>";

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
       
        echo "<p>Anzahl: " . $result->num_rows . "<br>Wörter: ";
        $row = $result->fetch_assoc(); 
        
        while ($row != null) {
                echo "<a href='purgatorium1.php?word_id=" . $row['word_id'] . "'>" . $row['word'] . "</a> ";
                $row = $result->fetch_assoc();
            
        }
        echo "</p>";
        
        /*
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
        */
    
    } else {
        die_more_elegantly("<p>Kein Eintrag in Purgatorium.</p>");
    }
    echo '<br><a href="purgatorium.php"><br><button>zurück</button></a><br><br>';   
   
    require_once "vsteno_template_bottom.php";
    $conn->close();

} else {
    echo "<p>Sie benötigen Superuser-Rechte und müssen eingeloggt sein, um Aleph zu benutzen.</p>";
    echo '<a href="purgatorium.php"><br><button>zurück</button></a><br><br>';   
}    
?>