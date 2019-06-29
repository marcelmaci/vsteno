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
 
 
class JSGlobalStructure {
    public $version;
    public $tokenList;  // JSTokenList;
    public $editorData; //array();
    public function JSGlobalStructure() {
       $this->tokenList = array();      // important: don't cast array to a standard object, the following is wrong: (object)array(); (the point is that by doing that, you loose the array push function to insert new key/value pairs!)
       $this->editorData = array(); 
    }
    public function addTokenListElement($token) {
       $newToken = new JSTokenList;
       $this->tokenList[$token] = $newToken;    // add new object as key/value pair by using the array push function (the array will be implicitely converted to a standard object and then stringified correctly)
    }
    public function addTokenEditorDataElement($token) {
       $newEditorDataElement = new JSTokenEditorDataElement;
       $this->editorData[$token] = $newEditorDataElement;    // add new object as key/value pair by using the array push function (the array will be implicitely converted to a standard object and then stringified correctly)
    }
    public function addNewElement($token) {
        $this->addTokenListElement($token);
        $this->addTokenEditorDataElement($token);
    }
}

class JSTokenList {
    public $tokenType;   // use this to indicate if a token is: base / shifted / combined (necessary for backwards compatibility when re-exporting data to db via js)
    public $header = array(); 
    public $tokenData = array();
    public function JSTokenList() {
        // empty constructor: probably obsolete
    }
    public function addTokenDataElement() {
        array_push($this->tokenData, new JSTokenData);
    }
}

class JSTokenData {
    public $knotType;
    public $calcType;
    public $vector1;
    public $vector2;
    public $shiftX;
    public $shiftY;
    public $tensions;
    public $thickness;
}

class JSKnotType {
    public $entry = false;
    public $exit = false;
    public $pivot1 = false;
    public $pivot2 = false;
    public $earlyExit = false;
    public $lateEntry = false;
    public $combinationPoint = false;
    public $connect = false;
    public $intermediateShadow = false;
    public function importFromSE1($d1, $d2, $dr) {
        // assume alle properties are set to false
        switch ($d1) {
            case 1 : $this->entry = true; break;
            case 2 : $this->pivot1 = true; break;
            case 4 : $this->combinationPoint = true; break;
            case 5 : $this->intermediateShadow = true; break;
            case 98 : $this->lateEntry = true; break;
        }
        switch ($d2) {
            case 1 : $this->exit = true; break;
            case 2 : $this->pivot2 = true; break;
            case 99 : $this->earlyExit = true; break;
        }
        $rev0_dr = $dr & bindec("111"); // in order to make it backwards compatible, get only lowest three bits of dr rev1!
        switch ($rev0_dr) {
            case 0 : $this->connect = true; break;
            case 5 : $this->connect = false; break;            
        }
    }
}

class JSThicknessContainer {
    public $standard;
    public $shadowed; 
    public function JSThicknessContainer() {
        $this->standard = new JSThicknessLeftRight;
        $this->shadowed = new JSThicknessLeftRight;
    }
    public function importFromSE1($thickness) {
        $this->standard->left = 0.5;    // SE1 has no standard thickness (it's assumed it is 1, so divide it by 2 for left and right vectors and use that value for standard property)
        $this->standard->right = 0.5;
        $this->shadowed->left = ($thickness > 1) ? ($thickness / 2) : 0.5;     // use thickness property of SE1 and divide it by 2 for left and right vectors
        $this->shadowed->right = ($thickness > 1) ? ($thickness / 2) : 0.5;     // thickness must at least be 1 (normal thickness), only thicknesses > 1 are converted (divided by 2)
    }
}

class JSThicknessLeftRight {
    public $left;
    public $right;
}

class JSTokenEditorDataElement {
    public $rotatingAxisList;
    public function JSTokenEditorDataElement() {
        $this->rotatingAxisList = array();
    }
}

// model selection is done in data.php now => should probably be disabled!?
global $default_model;
require_once "session.php";
/*
if ($_SESSION['user_logged_in']) {
    if ($_POST['model'] === "custom") $_SESSION['actual_model'] = GetDBUserModelName(); 
    else $_SESSION['actual_model'] = $_SESSION['selected_std_model'];
    //echo "Model = " . $_SESSION['actual_model'] . "<br>";
}
*/

// include external data and code
require_once "constants.php";
require_once "data.php";
require_once "parser.php";
require_once "engine.php";
require_once "export_old_parser_only_functions.php"; 

function InsertHTMLHeader() {
    require "vsteno_template_top_editor.php";
}

function InsertHTMLFooter() {
    require "vsteno_template_bottom.php";
}

/*
function ResetSessionGetBackPage() {
    InitializeSessionVariables();   // output is reseted to integrated, so that the following message will appear integrated
    InsertHTMLHeader();
    echo "<p>Die Optionen wurden zurückgesetzt.</p>";
    echo '<a href="input.php"><br><button>"zurück"</button></a>';
    InsertHTMLFooter();
}
*/

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

function GetTokenType($key) {
    global $steno_tokens_type;
    if ($steno_tokens_type[$key] === "shifted") return "shifted";
    else if ($steno_tokens_type[$key] === "combined") return "combined";
    else return "base"; // default value
}


function OpenEditorPage() {
    global $global_debug_string, $steno_tokens_master, $combiner_table, $shifter_table, $steno_tokens_type;
    $global_debug_string = "";
    CopyFormToSessionVariables();
    InsertHTMLHeader();

    echo "<div id='whole_page_content'><h1>Editor SE1</h1><i><p><b>SE1-Hack:</b> Die Programmierung der SE2 benötigt sehr viel Zeit. Ein \"produktives Ende\" ist im Moment nicht in Sicht. Um trotzdem weiterarbeiten zu können,
    soll der Editor mit der SE1 kompatibel gemacht werden (Rückwärtskompatibilität). Die Verwendung des Editors für die SE1 (Import, Export und Editieren der Daten) war nicht geplant und kann
    deshalb nur durch diverse \"Hacks\" (= \"münchhausnerische\" Anbindung der Daten, die direkt, queerbeet und ohne Rücksicht auf die OOP-Philosophie der SE2 in den Editor geschrieben werden) 
    erreicht werden. Das Ganze ist aber möglich und sollte dazu führen, dass SE1 und SE2 letztlich parallel verwendet werden können (bis anhin war eher beabsichtigt, die SE1 nach der 
    Implementierung der SE2 komplett zu löschen, da die SE2 aber sehr komplex ist, macht es allenfalls Sinn, die einfachere SE1 weiterzubehalten und evtl. sogar weiterzuentwickeln).</p>
    <p><b>Bedienung:</b> Das Standard-Font der SE1 wird automatisch geladen (Zeichen aus der PullDownSelection wählen und auf Load klicken, um ein Zeichen zu laden). Anschliessend Zeichen editieren
    (Punkte, Dicke, Tensions können geändert werden). Dann Zeichen in Browser-Font speichern ('save'). Zuletzt '->DB' wählen (das Font wird als ASCII-Text dargestellt, noch einmal 'speichern'
    klicken, um es definitiv in die Datenbank zu speichern. Danach ausprobieren über 'maxi' (Daten sind als 'custom' Font gespeichert, darauf achten, dass links unten 'custom' gewählt ist.<p>
    <p><b>DISCLAIMER: THERE'S ABSOLUTELY NO GUARANTEE THAT THIS EDITOR WORKS - USE IT AT YOUR OWN RISK! (IT MAY DAMAGE YOUR FONT!)</b></p>
    <i>";
    
    // All data in: global variables $steno_tokens_master, $combiner_table, $shifter_table
    
    // so here comes the code for the export "patch"
    // fortunately, I wrote some code to export the old parser (which apparently - hopefully - will also work for this export ... :-)
    
    // shifter and combiner are easy (or at least easier than the tokens ...): they get exported as plain text
    // combiner & shifter: take the output of following functions and assign them to the JS variables combinerSection and shifterSection;

    //echo "<pre>" . GenerateCombinerSubsection() . "</pre>";
    //echo "<pre>" . GenerateShifterSubsection() . "</pre>";
    
    // combiner and shifter: there's an important decision to be made
    // either (1): the tokens should not be combined before exported to the editor (this needs modification of data.php! shifter/tokenCombiner functions must not be called!) and the 
    //             combiner/shifter tables are exported to the editor
    // or (2): the thokens are combined (no changes in data.php), but then, there's no need to save shifter/combiner table any more (so nothing must be exported)
    // (a third possibility would be a hybrid mode: combine and shift tokens and export the tables nonetheless - but this is like calling the devil and ask him if he eventually likes
    // to mess up with everything ... ;-) ... normally, even if tokens are shifted/combined 2x they should appear only 1x in the associative array, but you never know ... so better not
    // to opt for this solution ...)
    //
    // (dis)advantages:
    // (1) a) more flexibility and efficiency (modifications in one part of combined token will be applied to every combination, so it has to be edited only once), b) more complex: token and
    //     combiner/shifter tables have to be edited (manually)), c) the combined tokens sometimes are not 100% perfect (since generic parts are combined with generic tokens)
    // (2) a) possibility to get rid of combiner/shifter table, but at the price of b) less flexibility and efficiency (every token and every combination has to be edited separately => more work)
    //     and with the possibility to design every token and combinations as it should be (esthetically better tokens)
    // Haven't made my mind up yet, so for the moment export export all data ... (which looks dangerously similar to option 3 ... :-)
    
    // ok, have done some further thinking and made up my mind: export for combiner/shifter will be a combination of solutions 1 and 3:
    // - tokens will be exported in combined and shifted version. Reason: This way, ALL tokens (base tokens, combined, shifted tokens) will be available in die editor (and can be graphically
    //   visualized)
    // - token/shifter tables will be exported as comments inside #BeginSubSection/#EndSubSection tags. Reason: If really, someone wants to recreated new combined tokens, it is possible to
    //   do it manually (using text editor: delete combined/shifted tokens in base section and uncomment necessary combined/shifted definitions in the corresponding subsections)
    //
    // there's still 2 problems left:
    // 1) subsection with commented definitions has to be created (new function needed, easy to do)
    // 2) comments have to be preserved from one editing process to the other (so, wenn loading the definitions, comments should also be loaded an inserted again later ... this problem
    // is the trickier one ... )
    //
    // the shifter/combiner problem is trickier than expected: it also hit's the performance - and other side effects may occur (this is just a guess, but sometimes the SE doesn't work 
    // after editing the font - this can be due to other reasons (e.g. variables that are not correctly resetted etc.), but in any case it seems useful to make the editor FULLY backwards
    // compatible (i.e. including shifter/combiner). This means that base tokens and combined tokens must be clearly distinguishable when data is exported to the editor (this wasn't necessary
    // for the original engine: the combiner/shifter just created the tokens and inserted them into the main token table used by the engine). 
    // The problem will be solved by creating a new table ($steno_tokens_type[]) that contain information about wether the token was created by shifter/token (if not, it is a base token).
    // The tables will be created directly by the shifter/combiner during data import.
    
    
    $export_combiner = htmlspecialchars(GenerateCombinerSubsection()); //addslashes(GenerateCombinerSubsection()); // hm ... strings conatain "" so they must be escaped ... but addslashes isn't enough ... why?!
    $export_shifter = htmlspecialchars(GenerateShifterSubsection()); //addslashes(GenerateShifterSubsection());
    
    $export_variable = new JSGlobalStructure;
    $export_variable->version = "SE1";
    
    
    
    foreach ($steno_tokens_master as $key => $definition) {
    
        //$export_variable->addTokenListElement($key);
        //$export_variable->addTokenEditorDataElement($key);
        $export_variable->addNewElement($key); // add both JSTokenListElement and JSTokenEditorDataElement
    
       // if ($key === "E") var_dump($steno_tokens_master[$key]);
        
        for ($i=0; $i<24; $i++) {
            $export_variable->tokenList[$key]->header[] = $steno_tokens_master[$key][$i];
         //   if ($key === "E") echo "$i: " . $steno_tokens_master[$key][$i];
        }
        //if ($key === "E") var_dump($export_variable->tokenList[$key]->header);
        
        // create rotating axis array
        $rotatingAxisArray = array();
        for ($i=7; $i<10; $i++) {
            if ($steno_tokens_master[$key][$i] != 0) {
                    $rotatingAxisArray[] = $steno_tokens_master[$key][$i];
            }
        }
        $export_variable->editorData[$key]->rotatingAxisList = $rotatingAxisArray;
        //var_dump($rotatingAxisArray);
        //$export_variable->editorData[$key]->rotatingAxisList = array(2,6);
        
        $export_variable->tokenList[$key]->tokenData = array();
        $export_variable->tokenList[$key]->tokenType = GetTokenType($key);
            
            
        for ($i=24; $i<count($steno_tokens_master[$key]); $i+=8) {
            
            // read data
            $tempX1 = $steno_tokens_master[$key][$i+0];
            $tempY1 = $steno_tokens_master[$key][$i+1];
            // $tempT1 = $steno_tokens_master[$key][$i+2];
            if ($i==24) $tempT1 = $steno_tokens_master[$key][3];    // incoming tension in first knot = offset 3 in HEADER
            else $tempT1 = $steno_tokens_master[$key][$i-1]; // normally, this is the incoming tension in SE2 ... (except for the first knot: tension = offset 3 in HEADER!
            $tempD1 = $steno_tokens_master[$key][$i+3];
            $tempTH = $steno_tokens_master[$key][$i+4];
            $tempDR = $steno_tokens_master[$key][$i+5];
            $tempD2 = $steno_tokens_master[$key][$i+6]; 
            $tempT2 = $steno_tokens_master[$key][$i+2]; // actually, offset 2 is the outgoing tension in SE2 ...
            // convert dr field
            $rev1_dr = new ContainerDRField($tempDR);
            
            // create token data objects and write data
            $newTuplet = new JSTokenData();
            //$newTuplet->calcType = "horizontal";        // this is the default value in SE1
            $newTuplet->calcType = $rev1_dr->knottype;        // use rev1 dr
            if ($newTuplet->calcType === "horizontal") {
                $newTuplet->vector1 = $tempX1;
                $newTuplet->vector2 = $tempY1;
            } else {
                $newTuplet->vector2 = $tempX1;      // if calcType is "proportional" or "orthogonal" swap vectors!
                $newTuplet->vector1 = $tempY1;
            }
            //$newTuplet->shiftX = 0;                     // default value in SE1
            $newTuplet->shiftX = get_rotating_axis_shiftX($steno_tokens_master[$key], $tempDR);
            $newTuplet->shiftY = 0;                     // default value in SE1
            $newTuplet->tensions = array($tempT1, $tempT2, $tempT1, $tempT2, $tempT1, $tempT2);    // SE1: only offsets 2+3 (for middle path) are important, but copy them to the outer shapes as well
            
            $newThickness = new JSThicknessContainer();
            $newThickness->importFromSE1($tempTH);
            $newTuplet->thickness = $newThickness;
            
            $newKnotType = new JSKnotType();
            $newTuplet->knotType = $newKnotType;
            $newTuplet->knotType->importFromSE1($tempD1, $tempD2, $tempDR);
            
            $export_variable->tokenList[$key]->tokenData[] = $newTuplet; //new JSTokenData(); //"tuplet: $i";
     
        }
     
   }
   // var_dump($export_variable);
    require_once "editor_raw_html_code.php"; // wow, this is ugly ... :-)
    
    // patch editor page and load font automatically
    $result = json_encode($export_variable);
    $data_patch = "var actualFontSE1 = $result;"; // works
    $script = "<script>$data_patch window.onload = function() { actualFont = actualFontSE1; createPullDownSelectionFromActualFont(); }</script>";
    echo $script;
    
    // include data inside HTML page via hidden input field
    echo "\n<input type=\"hidden\" id=\"combinerHTML\" value=\"$export_combiner\">";
    echo "\n<input type=\"hidden\" id=\"shifterHTML\" value=\"$export_shifter\">";
    echo "</div>"; // end div whole_page_content
   
    //var_dump($steno_tokens_type);
    InsertHTMLFooter();
}

// main
global $global_error_string;
// just create combined/shifted-tokens once per call of calculate.php (performace)

// dont know if this is correct .................................................... should be done in data.php now .................................. !!!!!!!!!!!!!!!!!!!!!!!!
//CreateCombinedTokens();
//CreateShiftedTokens();
// do it in data.php?

if ($_SESSION['user_logged_in'] == false) {
    require_once "vsteno_template_top.php";
    echo "<h1>Fehler</h1>";
    echo "<p>Sie müssen eingeloggt sein, um den grafischen Editor zu benutzen (Daten werden aus Datenbank gelesen / in Datenbank geschrieben).</p>";
    require_once "vsteno_template_bottom.php";
} elseif (($_SESSION['model_standard_or_custom'] === 'standard') && ($_SESSION['user_privilege'] < 2)) {
    require_once "vsteno_template_top.php";
    echo "<h1>Fehler</h1>";
    echo "<p>Sie arbeiten aktuell mit dem Model <b><i>standard</i></b>. Wenn Sie Ihr eigenes Stenografie-System bearbeiten wollen, ändern sie das Modell auf <b><i>custom</i></b> und rufen Sie diese Seite erneut auf.</p>";
    echo "<p><a href='toggle_model.php'><button>ändern</button></a></p>";
    require_once "vsteno_template_bottom.php";
} else { 
    OpenEditorPage();
}

?>
