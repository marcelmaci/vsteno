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
    public $tokenList;  // JSTokenList;
    public $editorData; //array();
    public function JSGlobalStructure() {
       $this->tokenList = array();      // important: don't cast array to a standard object, the following is wrong: (object)array(); (the point is that by doing that, you loose the array push function to insert new key/value pairs!)
       // $this->addTokenListElement();
    }
    public function addTokenListElement($token) {
       $newToken = new JSTokenList;
       $this->tokenList[$token] = $newToken;    // add new object as key/value pair by using the array push function (the array will be implicitely converted to a standard object and then stringified correctly)
    }
}

class JSTokenList {
    public $header = array(); 
    public $tokenData = array();
    public function JSTokenList() {
        //$this->addTokenDataElement();
      //  $this->addTokenDataElement();
       // $this->addTokenDataElement();
        
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
    public $intermediteShadow = false;
}

global $default_model;
require_once "session.php";
if ($_SESSION['user_logged_in']) {
    if ($_POST['model'] === "custom") $_SESSION['actual_model'] = "XM" . str_pad($_SESSION['user_id'], 7, '0', STR_PAD_LEFT); 
    else $_SESSION['actual_model'] = $default_model;
    //echo "Model = " . $_SESSION['actual_model'] . "<br>";
}

// include external data and code
require_once "constants.php";
require_once "data.php";
require_once "parser.php";
require_once "engine.php";
require_once "export_old_parser_only_functions.php"; 

//require_once "words.php";     // revert back to procedural-only version


function InsertHTMLHeader() {
   //if ($_SESSION['output_integratedyesno']) {
        require "vsteno_template_top.php";
    //} else {
        //require "vsteno_fullpage_template_top.php";
    //}
}

function InsertHTMLFooter() {
   // if ($_SESSION['output_integratedyesno']) {
        require "vsteno_template_bottom.php";
    //} else {
      //  require "vsteno_fullpage_template_bottom.php";
    //}
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

/*
function GenerateCombinerSubsectionJS() {
    global $combiner_table;             // variable containing TokenCombiner-definitions in old parser
    $output = "\t#BeginSubSection(combiner)\n";
    $definition = "";
    echo "hi there";
    foreach($combiner_table as $data_array) {
    /*
        $first = AddQuotes($data_array[0]);
        $second = AddQuotes($data_array[1]);
        $delta_x = $data_array[2];
        $delta_y = $data_array[3];
        $definition = "\t\t$first => { $second, $delta_x, $delta_y }";
        $output .= "$definition\n";
        //
    }
    $output .= "\t#EndSubSection(combiner)\n";
    return $output;
}
*/

function OpenEditorPage() {
    global $global_debug_string, $steno_tokens_master, $combiner_table, $shifter_table;
    $global_debug_string = "";
    CopyFormToSessionVariables();
    InsertHTMLHeader();

    echo "<h1>Export that stuff ... ;-)</h1>";
    
    // searching for my data ... where the heck did I store all that stuff ... ;-)
    // it's been a long time since I worked on this program for the last time ... ;-)
    // ok, found: global variables $steno_tokens_master, $combiner_table, $shifter_table
    
    // so here comes the code for the export "patch"
    // fortunately, I wrote some code to export the old parser (which apparently - hopefully - will also work for this export ... :-)
    
    // shifter and combiner are easy (or at least easier than the tokens ...): they get exported as plain text
    // combiner & shifter: take the output of following functions and assign them to the JS variables combinerSection and shifterSection;

    //echo "<pre>" . GenerateCombinerSubsection() . "</pre>";
    //echo "<pre>" . GenerateShifterSubsection() . "</pre>";
    
   // $export_variable = (object)array( 
     //                           "tokenList" => (object)array(
                          /*                  "header" => array(), 
                                            "tokenData" => (object)array(
                                            
                                            
                                                        "knotType" => array(),
                                                        "calcType" => "horizontal",
                                                        "vector1" => 1,
                                                        "vector2" => 2,
                                                        "shiftX" => 3,
                                                        "shiftY" => 4,
                                                        "tensions" => array(),
                                                        "thickness" => array(),
                                                
                                            ) */
       //                         ), 
         //                       "editorData" => (object)array()
           //             );

      $export_variable = new JSGlobalStructure;
  //    $export_variable->addTokenListElement("A");
  //    $export_variable->addTokenListElement("B");
  //    $export_variable->addTokenListElement("C");
      
  
    
    
    foreach ($steno_tokens_master as $key => $definition) {
    
        
        $export_variable->addTokenListElement($key);
       // $export_variable->tokenList[$key]->header = array(4,5,6,7);
       
        for ($i=0; $i<24; $i++) {
            $export_variable->tokenList[$key]->header[] = $steno_tokens_master[$key][$i];
        }
    
        
        $export_variable->tokenList[$key]->tokenData = array();
            
   //     $index = 0;
        for ($i=24; $i<count($steno_tokens_master[$key]); $i+=8) {
            
            $newTuplet = new JSTokenData();
            $newTuplet->calcType = "horizontal";
            $newTuplet->vector1 = 1;
            $newTuplet->vector2 = 1;
            $newTuplet->shiftX = 1;
            $newTuplet->shiftY = 1;
            $newTuplet->tensions = array( 0.5, 0.5, 0.5, 0.5, 0.5, 0.5);
            $newTuplet->thickness = array();
            
            $newKnotType = new JSKnotType();
            $newTuplet->knotType = $newKnotType;
            
            $export_variable->tokenList[$key]->tokenData[] = $newTuplet; //new JSTokenData(); //"tuplet: $i";
            
            /*
            $export_variable["tokenList"]["key"]["tokenData"][$index] = (object)array();
            $export_variable["tokenList"]["key"]["tokenData"][$index]["knotType"] = (object)array();
            $export_variable["tokenList"]["key"]["tokenData"][$index]["calcType"] = "horizontal";
            $export_variable["tokenList"]["key"]["tokenData"][$index]["vector1"] = 1;
            $export_variable["tokenList"]["key"]["tokenData"][$index]["vector2"] = 2;
            $export_variable["tokenList"]["key"]["tokenData"][$index]["shiftX"] = 3;
            $export_variable["tokenList"]["key"]["tokenData"][$index]["shiftY"] = 4;
            $export_variable["tokenList"]["key"]["tokenData"][$index]["tensions"] = array( 0.5, 0.5, 0.5, 0.5, 0.5, 0.5);
            $export_variable["tokenList"]["key"]["tokenData"][$index]["thickness"] = array();
            */
     //       $index++:
        }
   
   }
   // var_dump($export_variable);
    
    $result = json_encode($export_variable);
    echo "<p>php json:</p><pre>$result</pre>";
    $complete = "var test = $result;"; // works
    $script = "<script>$complete console.log(test);</script>";
    echo $script;

/*   
   // some test for export
   // now, the part that's more difficult is the export of the token data ...
    // maybe start with one entry to see if it works ...
    
    //var_dump($steno_tokens_master["T"]);  // use token "T" ("an easy one" to start with ...;-)
    
    // start with header
    $header = "";
    for ($i=0; $i<24; $i++) {
        // generate list of elements
        $temp = AddQuotes($steno_tokens_master["A"][$i]);
        if ($temp == null) $temp = "\"\"";
        $header .= $temp;
        if ($i < 23) $header .= ", ";
    }
    // generate JS notation
    $header = "\"header\" : [ $header ]";
    
    // generate entire object
    // manual solution
    //$complete = "var test = { \"tokenList\" : { \"A\" : { $header, \"tokenData\" : { [] } } }, \"editorData\" : { \"A\" : \"rotatingAxisList\" : [] } };";
    //$complete = "var test = { $header };"; // works
    //$script = "<script>$complete console.log(test);</script>";
    
    // try to do the same as above an export it with JSON => ok, seems to work! Use this, i.e. export data from $steno_tokens_master to some intermediata variable and use json_encode
 */   
 /*   $php_object = (object)array( "header" => array( 0, 1, 2, 3, 4, 5, 7, "one", "two", "three" )); // there are no literal objects in php, but it is possible to cast an array to a standard object (and it gets encode correctly in JSON)
    $result = json_encode($php_object);
    
    echo "<pre>$complete</pre>";
   
   echo "<p>php json:</p><pre>$result</pre>";
*//*    echo $script;
    
    //for ($i=24; $i<count($steno_tokens_master); $i++) {
    //}
*/

    InsertHTMLFooter();
}

// main
global $global_error_string;
// just create combined/shifted-tokens once per call of calculate.php (performace)

// dont know if this is correct .................................................... should be done in data.php now .................................. !!!!!!!!!!!!!!!!!!!!!!!!
//CreateCombinedTokens();
//CreateShiftedTokens();
// do it in data.php?

OpenEditorPage();

/*
if ($_POST['action'] === "abschicken") {
    $global_error_string = "";
    CalculateStenoPage();
} else {                // don't test for "zurücksetzen" (if it should be tested, careful with umlaut ...)
    ResetSessionGetBackPage();
}
*/


?>
