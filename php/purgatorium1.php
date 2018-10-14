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
require_once "parser.php";
require_once "engine.php";
require_once "data.php";
require_once "dbpw.php";

$options = "";

function die_more_elegantly( $text ) {
        echo "$text";
        echo '<a href="aleph.php"><br><button>zurück</button></a><br><br>';   
        require_once "vsteno_template_bottom.php";
        die();
}
/*
function connect_or_die() {
       // Create connection
        echo "hier";
        $conn = Connect2DB();
        // Check connection
        if ($conn->connect_error) {
            die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
        }
        return $conn;
}
*/

function prepare_and_execute_query( $conn ) {
    global $safe_word_id, $conn;
    $purgatorium = GetDBName( "purgatorium" );
    $safe_word_id = $conn->real_escape_string($_GET['word_id']);
    $sql = "SELECT * FROM $purgatorium WHERE word_id='$safe_word_id'";
    $result = $conn->query($sql);
    return $result;
}

function escape_data() { // disable escaping for the moment
        global $row, $safe_word, $safe_std, $safe_prt, $safe_cmp, $safe_result, $safe_user_id, $safe_comment, $conn;
        $safe_word = $row['word'];
        $safe_std = $row['std'];
        $safe_prt = $row['prt'];
        $safe_cmp = $row['composed'];
        $safe_result = $row['result'];
        $safe_user_id = $row['user_id'];
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
        echo "<h1>Info</h1>";
        echo "<table><tr><td>Wort:<br>Autor/in:<br>Resultat:<br>Mehrere:</td>
        <td>$safe_word ($safe_word_id)<br>$safe_user_id<br>$restxt<br>$composed_out</td></tr></table>";
        if (mb_strlen($safe_comment)>0) {
                echo "<p><b>Kommentar:</b><br>$safe_comment</p>";
        }
}

function get_single_word_data_fields() {
    global $safe_word, $safe_std, $safe_prt, $safe_result, $safe_cmp, $elysium_base_word, $prt_form, $std_form, $separated_std_form, $separated_prt_form;
    $std_form_upper = mb_strtoupper( $safe_std );
    $restxt = ($safe_result === 'u') ? "(undefiniert)" : "";
    // check by default checkboxes of information that has been entered by the user
    $chkcmp_yn = "checked"; // check this one always
    $chkstd_yn = (mb_strlen($std_form_upper)>0) ? "checked" : "";
    $chkprt_yn = (mb_strlen($safe_prt)>0) ? "checked" : "";
    // if one of the fields is empty => fill it with calculated values (without checking checkbox)
    $nil = SingleWord2SVG( $elysium_base_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
    //echo "std_form_upper: $std_form_upper sep_std_form: $separated_std_form<br>";
    $std_form_upper = (mb_strlen($std_form_upper) > 0) ? $std_form_upper : mb_strtoupper($separated_std_form);
    $safe_prt = (mb_strlen($safe_prt) > 0) ? $safe_prt : mb_strtoupper($separated_prt_form); 
    $submitted_by = $_GET['submit_id'];
    //echo "submit_id = $submitted_by<br>"; 
    $output = "";
    $output .= "<input type='hidden' name='single_original' value='$safe_word'>
                <input type='hidden' name='submitted_by' value='$submitted_by'>
                <input type='hidden' name='word_id' value='" . $_GET['word_id'] . "'>
                <input type='checkbox' name='single_chkcmp' value='1' $chkcmp_yn> BAS: 
                <input type='text' name='single_txtcmp'  size='30' value='$elysium_base_word'>
                <br>
                <input type='checkbox' name='single_chkstd' value='1' $chkstd_yn> STD: 
                <input type='text' name='single_txtstd'  size='30' value='$std_form_upper'>
                <br>
                <input type='checkbox' name='single_chkprt' value='1' $chkprt_yn> PRT: 
                <input type='text' name='single_txtprt'  size='30' value='$safe_prt'>
                ";
    return $output;
}

function get_decision_checkboxes_and_text( $text ) {
        switch ($_GET['dest']) {
            case "olympus" : $destination = "Olympus"; break;
            case "elysium" : $destination = "Elysium"; break;
            default : $destination = "none"; break;
        }
        $output = "";
        $output .= "<input type='checkbox' name='$text" . "decision_write_to_database' value='1' checked> ➟$destination
        <input type='hidden' name='dest' value='" . mb_strtolower($destination) . "'>
        <input type='checkbox' name='$text" . "decision_nirvana' value='1' checked> ➟Nirvana
        ";
        return $output;
}

function GetSinglePropositionSVG() {
        global $safe_prt, $safe_std, $safe_bas, $elysium_base_word;
        if (mb_strlen($safe_prt)>0) {
            //echo "start from prt-form: $safe_prt";
            $tl = MetaForm2TokenList( $safe_prt );
            //echo var_dump($tl);
            $svg = TokenList2SVG( $tl, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
        } elseif (mb_strlen($safe_std)>0) {
            // prepare engine
            $temp = $_SESSION["original_text_format"];
            $_SESSION["original_text_format"] = "std";
            // calculate prt
            $safe_prt = MetaParser($safe_std);
            // calculate svg from prt
            $tl = MetaForm2TokenList( $safe_prt );
            //echo var_dump($tl);
            $svg = TokenList2SVG( $tl, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
            // restore engine
            $_SESSION["original_text_format"] = $temp;
        } else $svg = SingleWord2SVG( $elysium_base_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
        return $svg;
}

function prepare_and_show_single_table() {
        global $safe_word, $safe_cmp, $elysium_base_word, $processing_in_parser;
        echo "<h2>(1) Single</h2>";
        $original_word_svg = SingleWord2SVG( $safe_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
        //$proposition_single_svg = SingleWord2SVG( $elysium_base_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);   
        $proposition_single_svg = GetSinglePropositionSVG();
        $single_word_data_fields = get_single_word_data_fields();
        //$decision_checkboxes_and_text = get_decision_checkboxes_and_text( "" ); // we only need that 1x actually ... fix it later: for the moment show it ohnly in single table
        echo "<table>
            <tr>
                <td>Aktuell ($processing_in_parser)<br>$original_word_svg</td>
                <td>Vorschlag<br>$proposition_single_svg</td>
                <td>Daten<br>$single_word_data_fields</td>
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
                <input type='checkbox' name='composed_chkcmp' value='1' $chkcmp_yn> BAS: 
                <input type='text' name='composed_txtcmp'  size='30' value='$elysium_composed_word'>
                <br>
                <input type='checkbox' name='composed_chkstd' value='1'> STD: 
                <input type='text' name='composed_txtstd'  size='30' value='$std_form_upper'>
                <br>
                <input type='checkbox' name='composed_chkprt' value='1'> PRT: 
                <input type='text' name='composed_txtprt'  size='30' value='$safe_prt'>
                ";
    return $output;
}

function offer_elysium_options() {
    $decision_checkboxes_and_text = get_decision_checkboxes_and_text( "" );
    $output = "Aktion: $decision_checkboxes_and_text<br>";
    $output .= "Modus: ";
    $output .= "<input type='radio' name='elysium_write_method' value='update' checked> Update "; 
    $output .= "<input type='radio' name='elysium_write_method' value='replace'> Replace"; 
    return $output;    
}

function offer_preference_options() {
    $output = "<br>Vorzug: <input type='radio' name='recommended_form' value='single' checked> Single"; 
    $output .= "<input type='radio' name='recommended_form' value='separated'> Separated ";
    return $output;
}

function prepare_and_show_composed_table() {
        global $safe_word, $safe_cmp, $elysium_composed_word, $processing_in_parser, $options;
        echo "<h2>(2) Separated</h2>";
        $original_word_svg = SingleWord2SVG( $safe_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
        $proposition_composed_svg = SingleWord2SVG( $elysium_composed_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);   
        $composed_word_data_fields = get_composed_word_data_fields();
        $decision_checkboxes_and_text = ""; //get_decision_checkboxes_and_text( "composed1" );
        echo "<table>
            <tr>
                <td>Aktuell ($processing_in_parser)<br>$original_word_svg</td>
                <td>Vorschlag<br>$proposition_composed_svg</td>
                <td>Daten<br>$composed_word_data_fields</td>
                <td>$decision_checkboxes_and_text</td>
            </tr>
        </table>";  
        // $options .= offer_preference_options();
}

function show_title_and_form() {
        global $safe_cmp, $processing_in_parser, $options;
        echo "<h1>Änderungen</h1>";
        echo "<form action='purgatorium2.php' method='post'>";
        prepare_and_show_single_table();
        if (mb_strlen($safe_cmp)>0) prepare_and_show_composed_table();
        //if ($processing_in_parser === "D")
        $options .= "<br>" . offer_elysium_options() . offer_preference_options() ;
        echo "<table><tr><td>$options</td><td><br><br><br><input type='submit' name='action' value='ausführen'></td></tr></table>";
        echo "</form>";
}
/*
show_entry_in_elysium_if_it_exists() {
    global $safe_word, $conn;
    $sql = "SELECT * FROM elysium WHERE word='$safe_word'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "<h2>Existing</h2>";
        $existing_base = conn->real_escape_string(row['base'])
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
}
*/

// main
// just create combined/shifted-tokens once per call of calculate.php (performace)
//CreateCombinedTokens();
//CreateShiftedTokens();
        
if (($_SESSION['user_logged_in']) && ($_SESSION['user_privilege'])) {
    
    echo "<h1>Purgatorium</h1>";
    echo "<p>Entscheiden Sie, was mit dem folgenden Eintrag aus dem Purgatorium geschehen soll.</p>";
    $conn = connect_or_die();
    $result = prepare_and_execute_query( $conn );

    if ($result->num_rows > 0) {
        // test
        //CreateCombinedTokens();
        //CreateShiftedTokens();
        
        $row = $result->fetch_assoc(); 
        escape_data();
        prepare_output_strings_and_variables();
        show_general_info();
        show_title_and_form();
        //show_entry_in_elysium_if_it_exists();
    
    } else {
        //die_more_elegantly("<p>Kein Eintrag in Purgatorium.</p>");
    }
    echo '<a href="purgatorium.php"><br><button>zurück</button></a><br><br>';   
   
    require_once "vsteno_template_bottom.php";
    $conn->close();

} else {
    echo "<p>Sie benötigen Superuser-Rechte und müssen eingeloggt sein, um Aleph zu benutzen.</p>";
    echo '<a href="purgatorium.php"><br><button>zurück</button></a><br><br>';   
}    
?>