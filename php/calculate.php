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

global $default_model;

require_once "errors_and_warnings.php";
require_once "session.php";
require_once "constants.php";
require_once "data.php";
require_once "parser.php";
require_once "engine.php";
require_once "linguistics.php";
require_once "share_font.php";

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

function IsStandardModel($check) {
    $output = false;
    $models_list = $_SESSION['standard_models_list'];
    //var_dump($models_list);
    foreach($models_list as $name => $description) {
        if ($check === $name) $output = true;
        //echo "check: $check =?= $name >" . ($check === $name) . "< => ouptut: >$output<<br>";
    }
    return $output;
}

function IsCustomModel($check) {
    if ($check === GetDBUserModelName()) return true;
    else return false;
}

function CustomOrStandard($check) {
    if (IsStandardModel($check)) return "standard";
    elseif (IsCustomModel($check)) return "custom";
    else return false;  // yeah, let's build one of those php-function that can return string or boolean (and drive you crazy due to implicit type cast if you don't pay attention ... :)
}

function ResetSessionGetBackPage() {
    global $session_subsection, $global_error_string, $global_warnings_string;
    InsertHTMLHeader();
    //InitializeSessionVariables();   // output is reseted to integrated, so that the following message will appear integrated
    switch ($_POST['action']) {
        case "aktualisieren" : $posted_model = $_SESSION['actual_model']; break;
        default : $posted_model = $_POST['action']; break;
    }
    
    $model_type = CustomOrStandard($posted_model);
    //echo "posted: $posted_model type: $model_type<br>";
    if ($model_type !== false) {
        // set session variables beforehand
        $_SESSION['actual_model'] = $posted_model;
        $_SESSION['model_custom_or_standard'] = $model_type;
        if ($model_type === "standard") $_SESSION['selected_std_model'] = $posted_model;
        // now load model based on session-variables
        //echo "model: " . $_SESSION['actual_model'];
        $text_to_parse = LoadModelFromDatabase($_SESSION['actual_model']);
        // borrow font
        $borrow_font_set = preg_match("/\"font_borrow_yesno\" *?:= *?\"yes\" *?;/", $text_to_parse) === 1; 
        //if ($_POST['font_borrow_yesno'] === "yes") { // use POST (SESSION not yet set)
        if ($borrow_font_set) { 
            // echo "borrow font ...<br>";
            $temp = preg_match("/\"font_borrow_model_name\" *?:= *?\"(.*?)\" *?;/", $text_to_parse, $ret);
            $parsed_model_name = $ret[1];
            //echo "model name: [$parsed_model_name]<br>";
            //var_dump($ret);
            $borrow_text = " (mit Font " . $parsed_model_name . ")";  
            $text_to_parse = BorrowFont( $text_to_parse, htmlspecialchars($parsed_model_name));
        } else $borrow_text = ""; // echo "don't borrow font";
        
        //echo "text_to_parse: $text_to_parse<br>";
        $output = StripOutComments($text_to_parse);
        $output = StripOutTabsAndNewlines($output);
        $header_section = GetSection($output, "header");
        //echo "header: $header_subsection<br>";
        $session_subsection = GetSubSection($header_section, "session");
        //echo "session_text: $session_subsection<br>";
        ImportSession();
        echo "<h1>Aktualisieren</h1><p>Das Modell " . $_SESSION['actual_model'] . "$borrow_text wurde geladen und die Optionen aktualisiert.</p>";
        echo GetErrorAndWarningSection();
        /*
        if ((mb_strlen($global_error_string)>0) || ((mb_strlen($global_warnings_string)>0))) {
            echo "<h2>ERRORS & WARNINGS</h2>";
            echo "<p>$global_error_string</p><p>$global_warnings_string</p>";
        }
        */
    } else {
        // check if it's a valid model cause post-variable can (might) be tampered ... :)
        echo "<h1>Aktualisieren</h1><p>Fehler: Das Model '" . $_POST['action'] . "' existiert nicht.</p>";
    }
    echo '<a href="input.php"><br><button>zurück</button></a>';
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
        echo '<br><a href="' . $_SESSION['return_address'] . '"><button>zurück</button></a><br><br>';   
    }
}

function InsertDatabaseButton() {
    echo '<center><input type="submit" name="action" value="speichern"></center><br>';
}

function AddMarkings($text) {
    // Don't know why I thought markings are a good idea ...
    // Makes the whole thing even more complicated ...
    // I'm probably going to leave it here (= only color functional)
    // and/or even take it out later ...
    $old_color = $_SESSION['token_color'];
    if ($_SESSION['color_nounsyesno']) $noun_color = $_SESSION['color_nouns'];
    if ($_SESSION['color_beginningsyesno']) $beginning_color = $_SESSION['color_beginnings'];
    $replace_beginning = "$1<@token_color='$beginning_color'>$3<@token_color='$old_color'>";
    // if ($_SESSION['style_beginnings'] !== "") {}
    if ($_SESSION['color_beginningsyesno']) $step1 = preg_replace("/(\.|\?|\!)( *?)((?:[A-Z]).*?(?:\.|\?|\!| |$))/", $replace_beginning, $text);
    else $step1 = $text;
    //echo "$step1<br><br>";
    // if ($_SESSION['style_nouns'] !== "") {}
    if ($_SESSION['color_beginningsyesno']) $replace_noun = "<@token_color='$noun_color'>$2<@token_color='$old_color'>";
    else $replace_noun = "$1<@token_color='$noun_color'>$2<@token_color='$old_color'>";
    if ($_SESSION['color_nounsyesno']) $step2 = preg_replace("/(\.|\?|\!)?((?: +?)(?:[A-Z]).*?(?:\.|\?|\!| |$))/", $replace_noun, $step1);
    else $step2 = $step1;
    //echo "$step2<br><br>";
    
    return $step2;
}

function CalculateStenoPage() {
    global $global_debug_string, $global_error_string, $backport_revision1;
    $global_debug_string = "";
    CopyFormToSessionVariables();
    InitializeHunspellAndPHPSyllable(); // now that session variables have been set, initialize language for linguistics.php
    InsertHTMLHeader();
    //LoadModel();
    
    $text = isset($_POST['original_text']) ? $_POST['original_text'] : "";
    $text = AddMarkings($text);
   
    // if there is text, insert title&introduction and SVG(s)
    if (strlen($text) > 0) {
        // if text is ascii format insert html-tags by converting \n to <br>
        if ($_SESSION['original_text_ascii_yesno']) { // don't insert breaks if text already contains html-tags
            if (!preg_match("/<br>|<p>|<\/p>/", "$text")) {
                $text = preg_replace( "/\n/", "<br>", $text);
                //echo "after: " . htmlspecialchars($text) . "<br>";
            }
        }
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
            echo NormalText2SVG( $text ); 
               
            InsertReturnButton();
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
            echo NormalText2SVG( $text ); // NormalText2SVG will call CalculateTrainingSVG
            InsertDatabaseButton();
            echo "</form>";
        } else {
            InsertTitle();
            InsertIntroduction();
            if ($_SESSION['output_format'] === "debug") {
                //echo "model_standard_or_custom: " . $_SESSION['model_standard_or_custom'] . "<br>";

                $model_name = $_SESSION['actual_model'];
                $hunspell_yesno = ($_SESSION['composed_words_yesno']) ? "yes" : "no";
                $hyphens_yesno = ($_SESSION['hyphenate_yesno']) ? "yes" : "no";
                $phonetics_yesno = ($_SESSION['phonetics_yesno']) ? "yes" : "no";
                $font_output = ($_POST['font_borrow_yesno'] === "yes") ? $_POST['font_borrow_model_name'] : $model_name; 
                echo "<h2>DEBUGGING</h2><br><b>PARAMETERS</b><br>MODEL: $model_name<br>FONT: $font_output<br>";
                echo "ENGINE (SE): rev" . $_SESSION['model_se_revision'] . " (" . (($backport_revision1 === true) ? "true" : "false") . ")<br>"; 
                echo "HUNSPELL: " . $_SESSION['language_hunspell'] . " ($hunspell_yesno)<br>";
                echo "HYPHENATOR: " . $_SESSION['language_hyphenator'] . " ($hyphens_yesno)<br>";
                echo "PHONETICS: " . $_SESSION['language_espeak'] . " ($phonetics_yesno)<br>";
            }
            echo NormalText2SVG( $text );
            if ($_SESSION['output_format'] === "debug") {
                if ((mb_strlen($global_error_string)>0) || (mb_strlen($global_warnings_string)>0)) {
                    echo  "<h2>RUNTIME</h2>";
                    echo "<h2>ERRORS</h2>";
                    if (mb_strlen($global_error_string)>0) echo "<p>$global_error_string</p>";
                    else echo "<p>(empty)</p>";
                    echo "<h2>WARNINGS</h2>";
                    if (mb_strlen($global_warnings_string)>0) echo "<p>$global_warnings_string</p>";
                    else echo "<p>(empty)</p>";
                } else echo "<h2>NO KNOWN* RUNTIME ERRORS.</h2><p style='font-size:10'>* ... there's a tremendously high chance for unknown unknowns ... ;-)</p>";
            } 
            InsertReturnButton();
        }
        //echo "\nText aus CalculateStenoPage()<br>$text<br>\n";
        
    } else echo "<h1>Optionen</h2><p>Die neuen Optionen wurden gesetzt.</p>";
    
    InsertHTMLFooter();
}

// main
if ($_POST['action'] === "abschicken") {
    
    // reset rules statistics    
    $_SESSION['rules_count'] = null;
    $_SESSION['rules_count'] = array();
    for ($i=0; $i<count($rules[$actual_model]); $i++) $_SESSION['rules_count'][$i] = 0;
    
    CalculateStenoPage();
} else {                // don't test for "zurücksetzen" (if it should be tested, careful with umlaut ...)
    ResetSessionGetBackPage();
}

?>
