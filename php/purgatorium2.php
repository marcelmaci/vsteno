<?php

require_once "vsteno_template_top.php";
require_once "session.php";
require_once "dbpw.php";

function die_more_elegantly( $text ) {
        echo "$text";
        echo '<a href="aleph.php"><br><button>zurück</button></a><br><br>';   
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

function GetWordID( $word ) {
    global $conn;
    // check if word-entry exists already
    $sql = "SELECT * FROM elysium WHERE word='$word'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "eintrag gefunden<br>";
        $row = $result->fetch_assoc();
        echo "id = " . $row['word_id'] . "<br>";
        return $row['word_id'];
    } else {
        return 0; // no id (entry)
   }
}

if (($_SESSION['user_logged_in']) && ($_SESSION['user_privilege'])) {

    echo "<h1>Eintrag speichern</h1>";
    
    // Create connection
    $conn = Connect2DB();

    // Check connection
    if ($conn->connect_error) {
        die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
    }

    // prepare data
    $safe_word = $conn->real_escape_string(StripOutWordSeparators($_POST['single_original']));
    //echo "Strippedout: $safe_word<br>";
    $safe_submitted_by = 0; // htmlspecialchars($_POST['single_original']);
    $safe_reviewed_by = $conn->real_escape_string($_SESSION['user_id']);
    $safe_single_bas = ($_POST['single_chkcmp'] == "1") ? $conn->real_escape_string($_POST['single_txtcmp']) : "";
    $safe_single_std = ($_POST['single_chktxt'] == "1") ? $conn->real_escape_string($_POST['single_txtstd']) : "";
    $safe_single_prt = ($_POST['single_chkprt'] == "1") ? $conn->real_escape_string($_POST['single_txtprt']) : "";
    $safe_separated_bas = ($_POST['composed_chkcmp'] == "1") ? $conn->real_escape_string($_POST['composed_txtcmp']) : "";
    $safe_separated_std = ($_POST['composed_chkcmp'] == "1") ? $conn->real_escape_string($_POST['composed_txtstd']) : "";
    $safe_separated_prt = ($_POST['composed_chkcmp'] == "1") ? $conn->real_escape_string($_POST['composed_txtprt']) : "";
    if (isset($_POST['recommended_form'])) {
        $temp = ($_POST['recommended_form'] === "single") ? "1" : "2";
        $safe_recommended_form = $conn->real_escape_string($temp);
        $safe_number_forms = $conn->real_escape_string("2");
    } else {
        $safe_recommended_form = $conn->real_escape_string("1");
        $safe_number_forms = $conn->real_escape_string("1");
    }
    echo "number: $safe_number_forms<br>";
    if ((mb_strlen($safe_separated_bas)==0) && (mb_strlen($safe_separated_std)==0) && (mb_strlen($safe_separated_prt)==0))  // empty strings in purgatorium can be used to mark word as single (if it has erroneously been marked as composed in training) 
        $safe_number_forms = $conn->real_escape_string("1");
 echo "number: $safe_number_forms<br>";
   
    $word_id = GetWordID( $safe_word );
    echo "id = $word_id<br>";
    if ($word_id != 0) {
        // update existing entry
        $sql = "UPDATE elysium 
        SET word='$safe_word', number_forms='$safe_number_forms', recommended_form='$safe_recommended_form', submitted_by='$safe_submitted_by', reviewed_by='$safe_reviewed_by', 
        single_bas='$safe_single_bas', single_std='$safe_single_std', single_prt='$safe_single_prt', separated_bas='$safe_separated_bas',
        separated_std='$safe_separated_std', separated_prt='$safe_separated_prt'
        WHERE word_id='$word_id';";
    } else {
    // create new entry
        $sql = "INSERT INTO elysium (word, number_forms, recommended_form, submitted_by, reviewed_by, single_bas, single_std, single_prt, separated_bas, separated_std, separated_prt)
        VALUES ( '$safe_word', '$safe_number_forms', '$safe_recommended_form', '$safe_submitted_by', '$safe_reviewed_by', '$safe_single_bas', '$safe_single_std', '$safe_single_prt', '$safe_separated_bas',
        '$safe_separated_std', '$safe_separated_prt')";
    }
    $result = $conn->query($sql);
    
    echo "<p>Der Eintrag <b>$safe_word</b> wurde in Elysium geschrieben.</p>";
    echo "Query: $sql<br>";

    echo '<br><a href="purgatorium.php"><br><button>zurück</button></a><br><br>';   
   
    require_once "vsteno_template_bottom.php";
    $conn->close();

} else {
    echo "<p>Sie benötigen Superuser-Rechte und müssen eingeloggt sein, um Aleph zu benutzen.</p>";
    echo '<a href="purgatorium.php"><br><button>zurück</button></a><br><br>';   
}    
?>
?>