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
        $elysium = GetElysiumDBName();
    
        echo "<h1>Elysium</h1>";
      
      // Create connection
       
        $conn = Connect2DB();

        // Check connection
        if ($conn->connect_error) {
            die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
        }
        
        if ($_POST['delete_entry'] !== "delete_yes") {  // update entry
            // prepare data
            $org_word_id = $_POST['word_id'];
            $org_word = $_POST['word'];
            $org_number_forms = $_POST['number_forms'];
            $org_recommended_form = $_POST['recommended_form'];
            $org_submitted_by = $_POST['submitted_by'];
            $org_reviewed_by = $_POST['reviewed_by'];
            $org_single_bas = $_POST['single_bas'];
            $org_single_std = $_POST['single_std'];
            $org_single_prt = $_POST['single_prt'];
            $org_separate_bas = $_POST['separate_bas'];
            $org_separate_std = $_POST['separate_std'];
            $org_separate_prt = $_POST['separate_prt'];
            $org_insertion_date = date( 'Y-m-d H:i:s' ); // 2018-10-09 17:47:58 // user insertion_date as last_modified // $_POST['insertion_date']; // leave the same date for the moment
         
            $word_id = $conn->real_escape_string($_POST['word_id']);
            $word = $conn->real_escape_string($_POST['word']);
            $number_forms = $conn->real_escape_string($_POST['number_forms']);
            $recommended_form = $conn->real_escape_string($_POST['recommended_form']);
            $submitted_by = $conn->real_escape_string($_POST['submitted_by']);
            $reviewed_by = $conn->real_escape_string($_POST['reviewed_by']);
            $single_bas = $conn->real_escape_string($_POST['single_bas']);
            $single_std = $conn->real_escape_string($_POST['single_std']);
            $single_prt = $conn->real_escape_string($_POST['single_prt']);
            $separate_bas = $conn->real_escape_string($_POST['separate_bas']);
            $separate_std = $conn->real_escape_string($_POST['separate_std']);
            $separate_prt = $conn->real_escape_string($_POST['separate_prt']);
            $insertion_date = $conn->real_escape_string($org_insertion_date); // $_POST['insertion_date']); // leave the same date for the moment
            
        
            $sql = "UPDATE $elysium
                    SET word='$word', 
                        number_forms='$number_forms', 
                        recommended_form='$recommended_form', 
                        submitted_by='$submitted_by', 
                        reviewed_by='$reviewed_by', 
                        single_bas='$single_bas', 
                        single_std='$single_std', 
                        single_prt='$single_prt', 
                        separated_bas='$separate_bas', 
                        separated_std='$separate_std', 
                        separated_prt='$separate_prt' 
                    WHERE word_id='$word_id';";
        
        } else {    // delete entry
            $word_id = $conn->real_escape_string($_POST['word_id']);
            $sql = "DELETE FROM $elysium WHERE word_id='$word_id'";
        }
        
        $result = $conn->query($sql);
        
        if ($result === TRUE) {
            if ($_POST['delete_entry'] === "delete_yes") { echo "<p><b>QUERY</b><br>$sql</p>"; die_more_elegantly("<p>Der Eintrag in Elysium ($elysium) wurde gelöscht.</p>"); }
            else echo "<p>Die Daten wurden in Elysium ($elysium) geschrieben.</p>";
            echo "<p><b>QUERY</b><br>$sql</p>";
        } else {
            die_more_elegantly("<p>Fehler beim Schreiben des Eintrages in Elysium ($elysium).</p>");
        }
        //echo '<a href="elysium.php"><br><button>zurück</button></a><br><br>';   
        // calculate shorthand svgs
        //CreateCombinedTokens();
        //CreateShiftedTokens();
        $single_tl = Metaform2TokenList( $single_prt );
        $single_svg = TokenList2SVG( $single_tl, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
        $separate_tl = Metaform2TokenList( $separate_prt );
        $separate_svg = TokenList2SVG( $separate_tl, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
            
        echo "<h2>Eintrag</h2>
              <div id='debug_table'><p><table>
                    <tr><td colspan='2'><b>INFO</b></td></tr>
                    <tr><td>Wort:<br>Formen:<br>Vorzug:<br>Autor:<br>Review:<br>Datum:</td>
                        <td>
                            <i>$org_word</i>&nbsp;&nbsp;&nbsp;<br>
                            <i>$org_number_forms</i><br>
                            <i>$org_recommended_form</i><br>
                            <i>$org_submitted_by</i><br>
                            <i>$reviewed_by</i><br>
                            <i>$org_insertion_date</i>
                        
                        </td>
                    </tr>        
                </table></p>
                <p><table>
                    <tr><td colspan='3'><b>(1) SINGLE</b></td></tr
                    <tr>
                        <td>BAS:<br>STD:<br>PRT:</td>
                        <td>
                            <i>$org_single_bas</i><br>
                            <i>$org_single_std</i><br>
                            <i>$org_single_prt<i>
                        </td>
                        <td>$single_svg</td>
                    </tr>
                    <tr><td colspan='3'><b>(2) COMPOSED</b></td></tr>
                    <tr>
                        <td>BAS:<br>STD:<br>PRT:</td>
                        <td>
                            <i>$org_separate_bas</i><br>
                            <i>$org_separate_std</i><br>
                            <i>$org_separate_prt</i>
                        </td>
                        <td>$separate_svg</td>
                    </tr>
                  </table></p></div>";
                  
            
        echo "<p><a href='elysium1.php?word_id=$word_id'><button>ändern</button></a> <a href='elysium.php'><button>->Elysium</button></a></p>";
        
        $conn->close();
    } else {
            echo "<h1>Elysium</h1><p>Sie arbeiten zur Zeit mit dem Modell <b>standard</b>, das Sie nicht bearbeiten können. Ändern Sie das Modell auf <b>custom</b> um mit Ihrem
            eigenen Elysium ($elysium) zu arbeiten.</p>";
            echo "<p><a href='toggle_model.php'><button>&auml;ndern</button></a></p>";
    }
    
    //require_once "vsteno_template_bottom.php";
} else {
    echo "<p>Sie benötigen Superuser-Rechte und müssen eingeloggt sein, um Aleph zu benutzen.</p>";
    echo '<a href="elysium.php"><br><button>zurück</button></a><br><br>';   
}    

require_once "vsteno_template_bottom.php";

?>