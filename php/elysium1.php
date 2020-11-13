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

require_once "vsteno_template_top.php";
require_once "session.php";
require_once "constants.php";
require_once "dbpw.php";
require_once "data.php";
require_once "parser.php";
require_once "engine.php";

function die_more_elegantly( $text ) {
        echo "$text";
        echo '<a href="elysium.php"><br><button>zurück</button></a><br><br>';   
        require_once "vsteno_template_bottom.php";
        die();
}

function prepare_aleph() {
        $_SESSION['original_text_format'] = "normal";       // must be in normal mode for work with aleph (otherwise a part of the parsing process won't be executed!)
        if ($_SESSION['output_format'] === "debug") $_SESSION['output_format'] = "inline";              // can mess up database tables if "debug" is selected (set it to inline to be safe)
}

if (($_SESSION['user_logged_in']) && ($_SESSION['user_privilege'])) {
    if (($_SESSION['user_privilege'] > 1) || (($_SESSION['user_privilege'] == 1) && ($_SESSION['model_standard_or_custom'] === "custom"))) {
    
        prepare_aleph();
        $elysium = GetDBName( "elysium" );
    
        echo "<h1>Elysium</h1><p>Hier können Sie den folgenden Eintrag aus Elysium ($elysium) bearbeiten.</p>";
      
      // Create connection
       
        $conn = Connect2DB();

        // Check connection
        if ($conn->connect_error) {
            die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
        }

        // prepare data
        //$safe_username = htmlspecialchars($_SESSION['user_username']);
        $safe_word_id = $conn->real_escape_string($_GET['word_id']);
        
        $sql = "SELECT * FROM $elysium WHERE word_id='$safe_word_id'";
        echo "<p><b>QUERY</b><br>$sql</p>";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
       
            $row = $result->fetch_assoc(); 
            $act_word = $row['word'];
            $act_number_forms = $row['number_forms'];
            $act_recommended_form = $row['recommended_form'];
            $act_submitted_by = $row['submitted_by'];
            $act_reviewed_by = $row['reviewed_by'];
            $act_single_bas = $row['single_bas'];
            $act_single_std = $row['single_std'];
            $act_single_prt = $row['single_prt'];
            $act_separate_bas = $row['separated_bas'];
            $act_separate_std = $row['separated_std'];
            $act_separate_prt = $row['separated_prt'];
            $act_insertion_date = $row['insertion_date'];
            
            // calculate shorthand svgs
            //CreateCombinedTokens();
            //CreateShiftedTokens();
            $single_tl = Metaform2TokenList( $act_single_prt );
            $single_svg = TokenList2SVG( $single_tl, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
            $separate_tl = Metaform2TokenList( $act_separate_prt );
            $separate_svg = TokenList2SVG( $separate_tl, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
            
            echo "<form action='elysium2.php' method='post'>";
            echo "<div id='debug_table'><p><table>
                    <tr><td colspan='2'><b>INFO</b></td></tr>
                    <tr><td>Wort:<br>Formen:<br>Vorzug:<br>Autor:<br>Review:<br>Datum:</td>
                        <td>
                            <input type='hidden' name='word_id' value='$safe_word_id'>
                            <input type='hidden' name='word'value='$act_word'>$act_word&nbsp;&nbsp;&nbsp;<br>
                            <input type='text' name='number_forms' size='4' value='$act_number_forms'><br>
                            <input type='text' name='recommended_form' size='4' value='$act_recommended_form'><br>
                            <input type='hidden' name='submitted_by' value='$act_submitted_by'>$act_submitted_by<br>
                            <input type='hidden' name='reviewed_by'value='" . $_SESSION['user_id'] . "'>$act_reviewed_by (➟" . $_SESSION['user_id'] . ")<br>
                            <input type='hidden' name='insertion_date'value='$act_insertion_date'>$act_insertion_date
                        </td>
                    </tr>        
                </table></p>
                <p><table>
                    <tr><td colspan='3'><b>(1) SINGLE</b></td></tr
                    <tr>
                        <td>BAS:<br>STD:<br>PRT:</td>
                        <td>
                            <input type='text' name='single_bas' size='30' value='$act_single_bas'><br>
                            <input type='text' name='single_std' size='30' value='$act_single_std'><br>
                            <input type='text' name='single_prt' size='30' value='$act_single_prt'>
                        </td>
                        <td>$single_svg</td>
                    </tr>
                    <tr><td colspan='3'><b>(2) COMPOSED</b></td></tr>
                    <tr>
                        <td>BAS:<br>STD:<br>PRT:</td>
                        <td>
                            <input type='text' name='separate_bas' size='30' value='$act_separate_bas'><br>
                            <input type='text' name='separate_std' size='30' value='$act_separate_std'><br>
                            <input type='text' name='separate_prt' size='30' value='$act_separate_prt'>
                        </td>
                        <td>$separate_svg</td>
                    </tr>
                  </table></p></div>
                  <p><input type='checkbox' name='delete_entry' value='delete_yes'> Eintrag löschen</p>
                  
                  <input type='submit' name='action' value='update'>";
            echo "</form>";
        
        } else {
            die_more_elegantly("<p>Kein Eintrag in Elysium.</p>");
        }
        //echo '<a href="elysium.php"><br><button>zurück</button></a><br><br>';   
   
        
        $conn->close();
    } else {
            echo "<h1>Elysium</h1><p>Sie arbeiten zur Zeit mit dem Modell <b>standard</b>, das Sie nicht bearbeiten können. Ändern Sie das Modell auf <b>custom</b> um mit Ihrem
            eigenen Elysium ($elysium) zu arbeiten.</p>";
            echo "<p><a href='input.php'><button>zur&uuml;ck</button></a></p>";
    }
    require_once "vsteno_template_bottom.php";
} else {
    echo "<p>Sie benötigen Superuser-Rechte und müssen eingeloggt sein, um Aleph zu benutzen.</p>";
    echo '<a href="elysium.php"><br><button>zurück</button></a><br><br>';   
}    

?>