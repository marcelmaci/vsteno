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

if (($_SESSION['user_logged_in']) && ($_SESSION['user_privilege'])) {

    echo "<h1>Eintrag speichern</h1>";
    
    // Create connection
    $conn = Connect2DB();

    // Check connection
    if ($conn->connect_error) {
        die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
    }

    // prepare data
    $safe_word = $conn->real_escape_string($_POST['single_original']);
    $safe_submitted_by = 0; // htmlspecialchars($_POST['single_original']);
    $safe_reviewed_by = $conn->real_escape_string($_SESSION['user_id']);
    $safe_single_bas = $conn->real_escape_string($_POST['single_txtcmp']);
    $safe_single_std = $conn->real_escape_string($_POST['single_txtstd']);
    $safe_single_prt = $conn->real_escape_string($_POST['single_txtprt']);
    $safe_separated_bas = $conn->real_escape_string($_POST['composed_txtcmp']);
    $safe_separated_std = $conn->real_escape_string($_POST['composed_txtstd']);
    $safe_separated_prt = $conn->real_escape_string($_POST['composed_txtprt']);
    if (isset($_POST['recommended_form'])) {
        $temp = ($_POST['recommended_form'] === "single") ? "1" : "2";
        $safe_recommended_form = $conn->real_escape_string($temp);
        $safe_number_forms = $conn->real_escape_string("2");
    } else {
        $safe_recommended_form = $conn->real_escape_string("1");
        $safe_number_forms = $conn->real_escape_string("1");
    }

    $sql = "INSERT INTO elysium (word, number_forms, recommended_form, submitted_by, reviewed_by, single_bas, single_std, single_prt, separated_bas, separated_std, separated_prt)
    VALUES ( '$safe_word', '$safe_number_forms', '$safe_recommended_form', '$safe_submitted_by', '$safe_reviewed_by', '$safe_single_bas', '$safe_single_std', '$safe_single_prt', '$safe_separated_bas',
    '$safe_separated_std', '$safe_separated_prt')";
    $result = $conn->query($sql);
    
    echo "<p>Der Eintrag <b>$safe_word</b> wurde in Elysium geschrieben.</p>";

    echo '<br><a href="aleph.php"><br><button>zurück</button></a><br><br>';   
   
    require_once "vsteno_template_bottom.php";
    $conn->close();

} else {
    echo "<p>Sie benötigen Superuser-Rechte und müssen eingeloggt sein, um Aleph zu benutzen.</p>";
    echo '<a href="aleph.php"><br><button>zurück</button></a><br><br>';   
}    
?>
?>