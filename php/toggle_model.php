<?php

require_once "vsteno_template_top.php";
require_once "dbpw.php";
require_once "session.php";
require_once "import_model.php";

$model = $_SESSION['model_standard_or_custom'];
$_SESSION['model_standard_or_custom'] = ($model === 'standard') ? 'custom' : 'standard';
$model = $_SESSION['model_standard_or_custom'];
$model_purgatorium = GetDBName( "purgatorium" );
$model_elysium = GetDBName( "elysium" );
$model_olympus = GetDBName( "olympus" );
$model_name = ($_SESSION['model_standard_or_custom'] === "custom") ? getDBUserModelName() : $_SESSION['selected_std_model'] ;

// reset all session variables to raw value
$old_text = $_SESSION['original_text_content']; // backup old text
$selected_std_model = $_SESSION['selected_std_model'];
InitializeSessionVariables(); // initialize with raw values
$_SESSION['original_text_content'] = $old_text; // restore old text
$_SESSION['selected_std_model'] = $selected_std_model;
$_SESSION['model_standard_or_custom'] = $model;
$_SESSION['actual_model'] = $model_name;
$_SESSION['rules_count'] = null; // reset rules statistics
$text_to_parse = LoadModelFromDatabase($_SESSION['actual_model']);
$output = StripOutComments($text_to_parse);
$output = StripOutTabsAndNewlines($output);
$header_section = GetSection($output, "header");
$session_subsection = GetSubSection($header_section, "session");
ImportSession(); // initialize with values specified by model
        
echo "<h1>Model</h1>
<p>Das aktive Model wurde geändert auf: <b><i>$model</i></b><br>($model_name:$model_purgatorium/$model_elysium/$model_olympus).<br>";
// additionally, give the user the possibility to select another model
//echo $_SESSION['actual_model'] . "----" . $_SESSION['model_standard_or_custom'];

$cu_checked = ($_SESSION['model_standard_or_custom'] === "custom") ? "checked" : "";
$de_checked = ($_SESSION['actual_model'] === "DESSBAS") ? "checked" : "";
$sp_checked = ($_SESSION['actual_model'] === "SPSSBAS") ? "checked" : "";
// echo "<br>$cu_checked-$de_checked-$sp_checked";
 
echo "<form action=\"../php/select_model.php\" method=\"post\">";
echo "<input type=\"radio\" name=\"model_name\" value=\"" . GetDBUserModelName() . "\" " . $cu_checked . ">Custom: von User/in editierbares Modell (" . GetDBUserModelName() . ")<br>";
echo "<input type=\"radio\" name=\"model_name\" value=\"DESSBAS\" " . $de_checked . ">Deutsch: Stolze-Schrey Grundschrift (DESSBAS)<br>";
echo "<input type=\"radio\" name=\"model_name\" value=\"SPSSBAS\" " . $sp_checked . ">Spanisch: Stolze-Schrey Grundschrift (SPSSBAS)";
echo "<br><input type=\"submit\" name=\"action\" value=\"wählen\"></form>";
echo '<a href="input.php"><br><button>zurück</button></a></p>';
/*    
    switch ($_SESSION['model_standard_or_custom']) {
        case "standard" : 
                    $de_checked = (($_SESSION['actual_model'] === "DESSBAS") || ($_SESSION['actual_model'] !== "SPSSBAS")) ? "checked" : "";
                    $sp_checked = ($_SESSION['actual_model'] === "SPSSBAS") ? "checked" : "";
//echo $_SESSION['selected_std_model'];
echo "<form action=\"../php/select_std_model.php\" method=\"post\">";
echo "<input type=\"radio\" name=\"std_model_name\" value=\"DESSBAS\" " . $de_checked . ">Deutsch: Stolze-Schrey Grundschrift (DESSBAS)<br>";
echo "<input type=\"radio\" name=\"std_model_name\" value=\"SPSSBAS\" " . $sp_checked . ">Spanisch: Stolze-Schrey Grundschrift (SPSSBAS)";
echo "<br><input type=\"submit\" name=\"action\" value=\"wählen\"></form>";
            echo '<a href="input.php"><br><button>zurück</button></a></p>';
            
                break;
        default : echo '<a href="input.php"><br><button>zurück</button></a></p>'; break;  
    }

*/
require_once "vsteno_template_bottom.php";

?>
