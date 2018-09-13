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
require_once "parser.php";
require_once "engine.php";
require_once "data.php";

require_once "dbpw.php";

function die_more_elegantly( $text ) {
        echo "$text";
        echo '<a href="aleph.php"><br><button>zurück</button></a><br><br>';   
        require_once "vsteno_template_bottom.php";
        die();
}

function connect_or_die() {
        // Create connection
        $conn = Connect2DB();
        // Check connection
        if ($conn->connect_error) {
            die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
        }
        return $conn;
}

function prepare_and_execute_query( $conn ) {
    global $safe_word_id;
    $safe_word_id = htmlspecialchars($_GET['word_id']);
    $sql = "SELECT * FROM purgatorium WHERE word_id='$safe_word_id'";
    $result = $conn->query($sql);
    return $result;
}

function escape_data() {
        global $row, $safe_word, $safe_std, $safe_prt, $safe_cmp, $safe_result, $safe_user_id, $safe_comment;
        $safe_word = htmlspecialchars($row['word']);
        $safe_std = htmlspecialchars($row['std']);
        $safe_prt = htmlspecialchars($row['prt']);
        $safe_cmp = htmlspecialchars($row['composed']);
        $safe_result = htmlspecialchars($row['result']);
        $safe_user_id = htmlspecialchars($row['user_id']);
        $safe_comment = htmlspecialchars($row['comment']);    
}

function prepare_output_strings_and_variables() {
        global $safe_result, $restxt, $safe_cmp, $safe_std, $safe_prt, $composed_out, $safe_std_out, $safe_prt_out, $safe_word, $elysium_base_word, $elysium_composed_word;
        switch ($safe_result) {
                case 'c' : $restxt = "richtig"; break;
                case 'w' : $restxt = "falsch"; break;
                case 'u' : $restxt = "undefiniert"; break;
                default : $restxt = "-"; break;
        }
        $composed_out = (mb_strlen($safe_cmp)>0) ? "$safe_cmp" : "-";
        $safe_std_out = (mb_strlen($safe_std)>0) ? "$safe_std" : "-";
        $safe_prt_out = (mb_strlen($safe_prt)>0) ? "$safe_prt" : "-";
        // prepare other variables
        $elysium_base_word = (mb_strlen($safe_cmp)>0) ? $safe_cmp : $safe_word;
        $elysium_composed_word = preg_replace( "/\|/", "\\", $safe_cmp); 
}

function show_general_info() {
        global $safe_word, $safe_word_id, $safe_user_id, $safe_result, $restxt, $safe_composed, $safe_std, $safe_prt, $composed_out, $safe_std_out, $safe_prt_out;
        echo "<h2>Info</h2>";
        echo "<table><tr><td>Wort:<br>Autor/in:<br>Resultat:<br>Mehrere:</td>
        <td>$safe_word ($safe_word_id)<br>$safe_user_id<br>$restxt<br>$composed_out</td></tr></table>";
        if (mb_strlen($safe_comment)>0) {
                echo "<p><b>Kommentar:</b><br>$safe_comment</p>";
        }
}

function get_single_word_data_fields() {
    global $safe_word, $safe_std, $safe_prt, $safe_result, $safe_cmp, $elysium_base_word, $prt_form, $std_form;
    $std_form_upper = mb_strtoupper( $safe_std );
    $restxt = ($safe_result === 'u') ? "(undefiniert)" : "";
    // check by default checkboxes of information that has been entered by the user
    $chkcmp_yn = "checked"; // check this one always
    $chkstd_yn = (mb_strlen($std_form_upper)>0) ? "checked" : "";
    $chkprt_yn = (mb_strlen($safe_prt)>0) ? "checked" : "";
    // if one of the fields is empty => fill it with calculated values (without checking checkbox)
    $nil = SingleWord2SVG( $elysium_base_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
    $std_form_upper = (mb_strlen($std_form_upper) > 0) ? $std_form_upper : mb_strtoupper($std_form); 
    $safe_prt = (mb_strlen($safe_prt) > 0) ? $safe_prt : mb_strtoupper($prt_form); 
    $output = "";
    $output .= "<input type='hidden' name='single_original' value='$safe_word'>
                <input type='checkbox' name='single_chkcmp' value='single_chkcmpyes' $chkcmp_yn> BAS: 
                <input type='text' name='single_txtcmp'  size='30' value='$elysium_base_word'>
                <br>
                <input type='checkbox' name='single_chkstd' value='single_chkstdyes' $chkstd_yn> STD: 
                <input type='text' name='single_txtstd'  size='30' value='$std_form_upper'>
                <br>
                <input type='checkbox' name='single_chkprt' value='single_chkprtyes' $chkprt_yn> PRT: 
                <input type='text' name='single_txtprt'  size='30' value='$safe_prt'>
                ";
    return $output;
}

function get_decision_checkboxes_and_text( $text ) {
        $output = "";
        $output .= "<input type='checkbox' name='$text" . "_decision_elysium' value='$text" . "_decision_elysium_yes' checked> Elysium<br>
        <input type='checkbox' name='$text" . "_decision_nirvana' value='$text" . "_decision_nirvana_yes' checked> Nirvana<br>
        <input type='checkbox' name='$text" . "_decision_analysis' value='$text" . "_decision_analysis_yes'> Analyse<br>";
        return $output;
}

function prepare_and_show_single_table() {
        global $safe_word, $safe_cmp, $elysium_base_word, $processing_in_parser;
        echo "<h2>(1) Single</h2>";
        $original_word_svg = SingleWord2SVG( $safe_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
        $proposition_single_svg = SingleWord2SVG( $elysium_base_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);   
        $single_word_data_fields = get_single_word_data_fields();
        $decision_checkboxes_and_text = get_decision_checkboxes_and_text( "single" );
        echo "<table>
            <tr>
                <td>Aktuell ($processing_in_parser)<br>$original_word_svg</td>
                <td>Vorschlag<br>$proposition_single_svg</td>
                <td>Daten<br>$single_word_data_fields</td>
                <td>Handlung<br>$decision_checkboxes_and_text</td>
            </tr>
        </table>";    
}

function get_composed_word_data_fields() {
    global $safe_word, $safe_std, $safe_prt, $safe_result, $safe_cmp, $elysium_base_word, $elysium_composed_word, $prt_form, $std_form, $separated_std_form, $separated_prt_form;
    $std_form_upper = mb_strtoupper( $safe_std );
    $restxt = ($safe_result === 'u') ? "(undefiniert)" : "";
    // check by default checkboxes of information that has been entered by the user
    $chkcmp_yn = "checked"; // check this one always
    // if one of the fields is empty => fill it with calculated values (without checking checkbox)
    $nil = SingleWord2SVG( $elysium_composed_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
    $std_form_upper = mb_strtoupper($separated_std_form); 
    $safe_prt = mb_strtoupper($separated_prt_form); 
    
    $output = "";
    $output .= "
                <input type='checkbox' name='composed_chkcmp' value='single_chkcmpyes' $chkcmp_yn> BAS: 
                <input type='text' name='composed_txtcmp'  size='30' value='$elysium_composed_word'>
                <br>
                <input type='checkbox' name='composed_chkstd' value='single_chkstdyes'> STD: 
                <input type='text' name='composed_txtstd'  size='30' value='$std_form_upper'>
                <br>
                <input type='checkbox' name='composed_chkprt' value='single_chkprtyes'> PRT: 
                <input type='text' name='composed_txtprt'  size='30' value='$safe_prt'>
                ";
    return $output;
}

function prepare_and_show_composed_table() {
        global $safe_word, $safe_cmp, $elysium_composed_word, $processing_in_parser;
        echo "<h2>(2) Separated</h2>";
        $original_word_svg = SingleWord2SVG( $safe_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
        $proposition_composed_svg = SingleWord2SVG( $elysium_composed_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);   
        $composed_word_data_fields = get_composed_word_data_fields();
        $decision_checkboxes_and_text = get_decision_checkboxes_and_text( "composed1" );
        echo "<table>
            <tr>
                <td>Aktuell ($processing_in_parser)<br>$original_word_svg</td>
                <td>Vorschlag<br>$proposition_composed_svg</td>
                <td>Daten<br>$composed_word_data_fields</td>
                <td>Handlung<br>$decision_checkboxes_and_text</td>
            </tr>
        </table>";    
}

function offer_elysium_options() {
        echo "<input type='checkbox' name='single_chkcmp' value='single_chkcmpyes'> Eintrag in Elysium löschen."; 
}

function show_title_and_form() {
        global $safe_cmp, $processing_in_parser;
        echo "<h1>Änderungen</h1>";
        echo "<form action='aleph_execute2.php' method='post'>";
        prepare_and_show_single_table();
        if (mb_strlen($safe_cmp)>0) prepare_and_show_composed_table();
        if ($processing_in_parser === "D") offer_elysium_options();
        echo '<input type="submit" name="action" value="speichern">';
        echo "</form>";
}

// main
// just create combined/shifted-tokens once per call of calculate.php (performace)
CreateCombinedTokens();
CreateShiftedTokens();
        
if (($_SESSION['user_logged_in']) && ($_SESSION['user_privilege'])) {
    
    echo "<h1>Elysium</h1>";
    echo "<p>Entscheiden Sie, was mit dem folgenden Eintrag aus dem Purgatorium geschehen soll.</p>";
    $conn = connect_or_die();
    $result = prepare_and_execute_query( $conn );

    if ($result->num_rows > 0) {
       
        $row = $result->fetch_assoc(); 
        escape_data();
        prepare_output_strings_and_variables();
        show_general_info();
        show_title_and_form();
    
    } else {
        die_more_elegantly("<p>Kein Eintrag in Purgatorium.</p>");
    }
    echo '<a href="aleph.php"><br><button>zurück</button></a><br><br>';   
   
    require_once "vsteno_template_bottom.php";
    $conn->close();

} else {
    echo "<p>Sie benötigen Superuser-Rechte und müssen eingeloggt sein, um Aleph zu benutzen.</p>";
    echo '<a href="aleph.php"><br><button>zurück</button></a><br><br>';   
}    
?>