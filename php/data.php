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
 
 
/*
14. August 2018: New rule formalism

Either:    A => B
Or:        A => array(a, b, c, d ...)
where:     a is the original B (i.e. replacement for REGEX)
           b, c, d ... are exceptions: if one of the matches, the rule won't be applied
           
           can be used for example for word "geschäft": 
           define a rule which replaces "schäft" => "{SCHAFT}"
           define exception "geschäft" (rule shouldn't be applied to that word)

           in PHP:
           "schaft$ => array( "{SCHAFT}", "Geschäft(en?)?" )

           Advantages:
           (1) exceptions can be indicated together with rules (more logical and better to understand)
           (2) possible to use REGEX also for exceptions (big plus)
           
           Performance-whise this should also be beneficial since exceptions are only tested if first part of rule matches
           (in the old version, every exception was tested on every word).
*/
 
// 25. September 2018: read data from database

require_once "session.php";
//echo "Model = " . $_SESSION['actual_model'] . "<br>";
require_once "import_model.php";
require_once "engine.php";
require_once "parser.php";
require_once "share_font.php";
//require_once "interpolate.php";

//require_once "regex_helper_functions.php";

global $font, $combiner, $shifter;
global $rules, $functions_table, $rules_options;
global $insertion_key;
global $global_error_string;
$global_error_string = "";

// main

// ok, when I implemented data.php (which evolved from php-file with data as variables included to
// and include that loaded the same variables from db via a parser) I was too lazy to implement a proper
// "import_from_database"-function ... As a result, data.php relied on session-variables to load the actual
// model. This worked fine as long as only one model was used (and the model never change). Now, with two
// models (german and spanish), this "autoloading via include" makes it impossible to switch between the
// models since whenever you include data.php the models gets loaded before you can change it. Which means:
// you cannot start the calculation (via input form) and then change the model according to what you selected
// in the input form. The fastest and easiest (= with as little work as possible) way around that for the moment
// is to "intercept" the post-variable in data.php in order to change the session-variables beforehand
// (pretending the model has already been selected before the form is evaluated).
// Of course: This is not good programming style at all ... (looks more like BASIC with spaghetti-code-gotos here
// and there ... :)

// adjust session variable so that correct model gets loaded
// session-variable is also used to set correct rules pointers for regex-parser-functions!!!
if (!(isset($_SESSION['actual_model']))) $_SESSION['actual_model'] = $default_model;
$model_to_load = $_SESSION['actual_model'];

// DO NOT ECHO DEBUG INFORMATION HERE => THATS BEFORE HTML HEAD!!!!!!!!!!
$text_to_parse = LoadModelFromDatabase($model_to_load);
//echo "text: $text_to_parse<br><br>";
// barrow a font from another model

///////////////////// prepare patching to use one and the same font for all models //////////
// idea is to "borrow a font from another model ///////////////////////////////////////////
// use the following line to test (or comment out to use traditional functionality ///////
if ($_POST['font_borrow_yesno'] === "yes") {  // use POST (SESSION not yet set)
    $text_to_parse = BorrowFont( $text_to_parse, htmlspecialchars($_POST['font_borrow_model_name']));
    //echo "model_name: " . $_POST['font_borrow_model_name'];
} else if ($include_for_cookie) {
    //echo "include for cookie<br>";
    // hardcoded include for first run to generate fortune cookie
    $_SESSION['font_borrow_yesno'] = true;
    $_SESSION['font_borrow_model_name'] = "GESSBAS";
    $_SESSION['font_importable_yesno'] = true;
    $_SESSION['font_exportable_yesno'] = false;
    $_SESSION['font_load_from_file_yesno'] = true;
    //echo "model_name: " . $_SESSION['font_borrow_model_name'];
    //echo "BEFORE: $text_to_parse";
    $text_to_parse = BorrowFont( $text_to_parse, htmlspecialchars($_SESSION['font_borrow_model_name']));
    //echo "AFTER: $text_to_parse";
    $include_for_cookie = false;
} else if ($include_for_regex_gen) {
    //echo "include for regex-gen<br>";
    $temp = preg_match("/\"font_borrow_model_name\" *?:= *?\"(.*?)\" *?;/", $text_to_parse, $m);
    $_SESSION['font_borrow_model_name'] = $m[1];
    //echo "font_borrow_model_name: " . $_SESSION['font_borrow_model_name'] . "<br>";
    //echo "BEFORE: $text_to_parse";
    $text_to_parse = BorrowFont( $text_to_parse, htmlspecialchars($_SESSION['font_borrow_model_name']));
    //echo "AFTER: $text_to_parse";
    
} else {
    // since foreign font loading is implemented as a check box it's not sure that font specific
    // variables are actual when calculation is launched.
    // therefore (re)load font-session variables even if no foreign font is loaded.
    // (in a certain way, the orginal model must "borrow" parts of itself - session variables -
    // to be sure that it uses the correct parameters)
    // this is only necessary for session variables (everything else, like pre/post/spacer
    // will be loaded directly by ImportModel())
    $font_session_variables = ShareFontGetSubsection( "session", $text_to_parse );
    //echo $font_session_variables;
    // get complete variable list (definitions) from lending font
    $lender_definition_list = GetLenderSessionVariableDefinitions( $font_session_variables );
    //echo "lender definition list: $lender_definition_list<br>";
    actualize_font_session_variables($lender_definition_list);
}
    
/////////////////// end of patching //////////////////////////////////////////////////////////

$test = ImportModelFromText($text_to_parse);
$actual_model = $_SESSION['actual_model'];
// create new session variable with number of rules for global use (this is faster than repeated count() calls)
$_SESSION['actual_model_number_of_rules'] = count($rules[$actual_model]);

$_SESSION['last_updated_model'] = $actual_model;

//InterpolateFont($font[$_SESSION['actual_model']]);

?>