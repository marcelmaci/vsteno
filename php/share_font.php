<?php
/* VSTENO - Vector Steno Tool with Enhanced Notational Options
 * (c) 2018-2019 - Marcel Maci (m.maci@gmx.ch)
 
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
 
 
/* This file contains functions to share fonts between two models. */

require_once "dbpw.php";
require_once "import_model.php";

// session variables related to font
$variable_list = array("token_distance_wide", "spacer_token_combinations", "spacer_vowel_groups", "spacer_rules_list");
    

function BorrowFont( $original_model_text, $lender_model_name ) {

   $lender_model_text = LoadModelToShareFromDatabase($lender_model_name);
/*
    // control output
    ControlOutput("ORIGINAL:", $original_model_text);
    ControlOutput("LENDER:", $lender_model_text);
*/
    // preparations
    $original_model_text = StripOutUnnecessaryElements($original_model_text);
    $lender_model_text = StripOutUnnecessaryElements($lender_model_text);
    
    // control output
    //ControlOutput("STRIPORIGINAL:", $original_model_text);
/*    ControlOutput("STRIPLENDER:", $lender_model_text);
*/

    // patch original model
    // header: session-part
    // stripe out all session variables related to font
    $original_model_text = StripOutFontSessionVariables( $original_model_text );
    // get complete variable list (definitions) from lending font
    $lender_definition_list = GetLenderSessionVariableDefinitions( $lender_model_text );
    //ControlOutput("DEFINITIONS:", $lender_definition_list);
    
    // add definition list to original model
    $original_model_text = preg_replace("/(#BeginSubSection\(session\))/", "$1\n" . $lender_definition_list, $original_model_text);
    
    // font-part
    // get font definitions
    $font_section = GetFontDefinitions($lender_model_text);
    //ControlOutput("FONT:", $font_section);
    // replace font definitions in original model
    $original_model_text = preg_replace("/#BeginSection\(font\).*?#EndSection\(font\)/s", "$font_section", $original_model_text);
    
    // rule-part
    // spacer
    // get spacer and postspacer rules from lender (note: do not use autogeneration, but copy rules from lender)
    $prespacer_rules = ShareFontGetSubSection("prespacer", $lender_model_text);
    $spacer_rules = ShareFontGetSubSection("spacer", $lender_model_text);
    $postspacer_rules = ShareFontGetSubSection("postspacer", $lender_model_text);
    
    //ControlOutput("PRESPACER:", $prespacer_rules);
    //ControlOutput("SPACER:", $spacer_rules);
    //ControlOutput("POSTSPACER:", $postspacer_rules);
    
    //ControlOutput("BEFORE:", $original_model_text);
    
    // replace spacer and postspacer in original model
    $original_model_text = ShareFontReplaceSubsection("prespacer", $original_model_text, $prespacer_rules);
    $original_model_text = ShareFontReplaceSubsection("spacer", $original_model_text, $spacer_rules);
    $original_model_text = ShareFontReplaceSubsection("postspacer", $original_model_text, $postspacer_rules);
    
    //ControlOutput("MODIFIED:", $original_model_text);

    return $original_model_text;
}
 
function StripOutFontSessionVariables( $original_model_text ) {
    global $variable_list;
    foreach ($variable_list as $variable)  
        $original_model_text = preg_replace("/\"$variable\"[ ]*?:=[ ]*?\".*?\".*?;/s", "", $original_model_text);
    return $original_model_text;
}

function GetLenderSessionVariableDefinitions( $lender_model_text ) {
    global $variable_list;
    $definition_list_as_text = "";
    foreach ($variable_list as $variable)
        if (preg_match("/\"$variable\"[ \n]*?:=[ \n]*?\".*?\".*?;/s", $lender_model_text, $matches))
            $definition_list_as_text .= $matches[0] . "\n";
    return $definition_list_as_text;
}

function StripOutUnnecessaryElements($text) {
    // strip out all //
    $text = preg_replace("/\/\/.*?\n/", "\n", $text);
    // IMPORTANT: DO NOT STRIP OUT NEWLINES ETC. BECAUSE THIS CREATES OTHER PROBLEMS
    // INSTEAD USE /s MODIFIER IN REGEX TO MAKE THE DOT MATCH NEWLINE, CARRIAGE RETURN, LINE FEED ETC.
    // EXAMPLE:
    //
    // /#BeginSection\(font\).*?#EndSection\(font\)/s
    //
    // MATCHES EVERYTHING BELONGING TO FONT DEFINITIONS!
    // strip out all newlines, line feed and tabs (unless preceeded / followed by certain tokens)
    //$text = preg_replace("/(?<![;\)])[\t\n](?!#End)/", "", $text); // #End doesn't work - why?
    //$text = preg_replace("/[\r]/", "", $text); // #End doesn't work - why?
    // strip out all /* */ comments
    $text = preg_replace("/\/\*.*?\*\//s", "", $text);
    return $text;
}

function ControlOutput($title, $text) {
    $t = htmlspecialchars($text);
    echo "<b>$title</b><pre>$t</pre>";
}

function GetFontDefinitions($text) {
    if (preg_match("/#BeginSection\(font\).*?#EndSection\(font\)/s", $text, $matches))
        $font_as_text = $matches[0];
    else 
        $font_as_text = "";
    return $font_as_text;
}

function ShareFontGetSubSection($type, $model_text) {
    if (preg_match("/#BeginSubSection\($type\).*?#EndSubSection\($type\)/s", $model_text, $matches))
        $subsection = $matches[0];
    else 
        $subsection = "";
    return $subsection;   
}

function EscapeDollarCharInVariables($text) {
    // Welcome to the ESCAPING HELL ... !!!!!!!!
    // Substituting spacer subsections with preg_replace didn't work.
    // Raison: $ in replacement was interpreted as variable.
    // Escaping replacement using preg_quote didn't help neither.
    // Raison: everything (not only $) got escaped ...
    // In theory the solution was thus to escape only $ in replacement.
    // Problem: any escape attempt of type "\$" gets eliminated by PHP
    // before it even gets to REGEX ...
    // Using triple escaping \\\$ didn't help neither.
    // Only solution: use ' instead of " and user triple escaping at
    // the same time.
    // Unfortunately this conflicts with use of $ in replacement
    // as variable.
    // Only solution: Use both ' (escaped $) and " (unescaped $, 
    // variables) in replacement and concatenate the right before
    // sending them to REGEX.
    // This approach seems to work now (TOUCH WOOD!!!)
    
    //echo "ESC-BEFORE: " . htmlspecialchars($text) . "<br><br>";
    
    //$text = preg_replace("/s([0-9])/", "x$1", "s1s2"); // works
    //$text = preg_replace("/s([0-9])/", "\\x$1", "s1s2"); // works
    //$text = preg_replace("/\$([0-9])/", "\\x$1", "$1$2"); // doesn't work
    // $text = preg_replace('/\$([0-9])/', '\\x$1', "$1$2"); // works
    //$text = preg_replace("/\\\$([0-9])/", '\\\$' . "$1", "$1$2"); // works
    $text = preg_replace("/\\\$([0-9])/", '\\\$' . "$1", $text); // works
    
    //$text = preg_quote("$1$2");
    // echo "ESC-AFTER: " . htmlspecialchars($text) . "<br><br>";
    return $text;
}

function ShareFontReplaceSubsection($type, $model_text, $replacement) {
    $model_text = preg_replace("/#BeginSubSection\($type\).*?#EndSubSection\($type\)/s", EscapeDollarCharInVariables($replacement), $model_text);
    return $model_text;
}


?>