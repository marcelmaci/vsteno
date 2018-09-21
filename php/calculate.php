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
 
// include external data and code
require_once "constants.php";
require_once "data.php";
require_once "parser.php";
require_once "engine.php";
require_once "session.php";
//require_once "words.php";     // revert back to procedural-only version


function InsertHTMLHeader() {
    if ($_SESSION['output_integratedyesno']) {
        require "vsteno_template_top.php";
    } else {
        require "vsteno_fullpage_template_top.php";
    }
}

function InsertHTMLFooter() {
    if ($_SESSION['output_integratedyesno']) {
        require "vsteno_template_bottom.php";
    } else {
        require "vsteno_fullpage_template_bottom.php";
    }
}

function ResetSessionGetBackPage() {
    InitializeSessionVariables();   // output is reseted to integrated, so that the following message will appear integrated
    InsertHTMLHeader();
    echo "<p>Die Optionen wurden zurückgesetzt.</p>";
    echo '<a href="input.php"><br><button>"zurück"</button></a>';
    InsertHTMLFooter();
}

function InsertTitle() {
    if (($_SESSION['title_yesno']) && ($_SESSION['output_format'] !== "debug")) {
            $size_tag = "h" . $_SESSION['title_size'];
            $size_tag_start = "<$size_tag>";
            $size_tag_end = "</$size_tag>";
            echo "$size_tag_start" . $_SESSION['title_text'] . "$size_tag_end\n";
    }
}

function InsertIntroduction() {
    // size is ignored for the moment
    if (($_SESSION['introduction_yesno']) && ($_SESSION['output_format'] !== "debug")) {
            $p_tag_start = "<p>";
            $p_tag_end = "</p>";
            echo "$p_tag_start" . $_SESSION['introduction_text'] . "$p_tag_end\n";
    }
}

function InsertReturnButton() {
    if (!$_SESSION['output_without_button_yesno']) {
        echo '<a href="' . $_SESSION['return_address'] . '"><br><button>zurück</button></a><br><br>';   
    }
}

function InsertDatabaseButton() {
    echo '<center><input type="submit" name="action" value="speichern"></center><br>';
}

function CalculateStenoPage() {
    global $global_debug_string;
    $global_debug_string = "";
    CopyFormToSessionVariables();
    InsertHTMLHeader();
    
    $text = isset($_POST['original_text']) ? $_POST['original_text'] : "";
    
    // if there is text, insert title&introduction and SVG(s)
    if (strlen($text) > 0) {
        if ($_SESSION['output_format'] === "layout") {
            // insert introduction as text in svg if necessary, use inline/html-options
            $title_to_add = "";
            if ($_SESSION['title_yesno']) {
                    $title_to_add = "<@token_type=\"svgtext\"><@svgtext_size=\"40\">";
                    $title_to_add .= $_SESSION['title_text'];
                    $title_to_add .= "<@token_type=\"shorthand\"><br>";
            }
            if ($_SESSION['introduction_yesno']) {
                    $title_to_add .= "<@token_type=\"svgtext\"><@svgtext_size=\"30\">";
                    $title_to_add .= $_SESSION['introduction_text'];
                    $title_to_add .= "<@token_type=\"shorthand\"><br>";
            }
            // add this at beginning of original text
            //echo "title_to_add: $title_to_add<br>";
            $text = $title_to_add . $text;
        } elseif ($_SESSION['output_format'] === "train") {
            echo "<h1>Training</h1>";
            if (!$_SESSION['user_logged_in']) echo "<p><b>Sie müssen eingeloggt sein, um Datenbankfunktionen nutzen zu können!</b></p>";
            else {
                switch ($_SESSION['user_privilege']) {
                    case 1 : $privilege_text = "Purgatorium"; break;
                    case 2 : $privilege_text = "Purgatorium & Elysium"; break;
                    default : $privilege_text = "(Fehler)";
                }
                echo "<p>Nutzer <b>" . $_SESSION['user_username'] . " (user_id: " . $_SESSION['user_id'] . ")</b> mit Schreibrechten für <b>$privilege_text</b>.<p>";
            }
            echo "<form action='../php/training_execute.php' method='post'>";
            NormalText2SVG( $text ); // NormalText2SVG will call CalculateTrainingSVG
            InsertDatabaseButton();
            echo "</form>";
        } else {
            InsertTitle();
            InsertIntroduction();
            NormalText2SVG( $text );
            InsertReturnButton();
        }
        //echo "\nText aus CalculateStenoPage()<br>$text<br>\n";
        //NormalText2SVG( $text ); // do not escape entered text (will be done in parser: pre/postprocessnormaltext())
       
    } else echo "<h1>Optionen</h2><p>Die neuen Optionen wurden gesetzt.</p>";
   
    
    //echo '<a href="input.php"><br><button>"Nochmals!"</button></a>';
   
    InsertHTMLFooter();
}

// main

// just create combined/shifted-tokens once per call of calculate.php (performace)
CreateCombinedTokens();
CreateShiftedTokens();
        
if ($_POST['action'] === "abschicken") {
    CalculateStenoPage();
} else {                // don't test for "zurücksetzen" (if it should be tested, careful with umlaut ...)
    ResetSessionGetBackPage();
}

?>
