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

// generates a text file from old parser variables and shows it in a textarea-
// field that can be edited and/or written to database
//
// the generated text file can then be imported into the new parser
//
// text file that contains all definitions necessary to define a shorthand
// system (tokens & rules) will be called "system"
//
// Structure for system file:
//
// Keywords: #BeginPART(), #EndPART() - PART can be: "Section" or "SubSection"; 
//           () contains parameters, separated by commas if more than one
//           e.g. #EndSubSection(shortener) or #EndSubSection(=>transcriptor,=std)
//
// Special characters: #    marks keyword
//                     >>   go to that subsection (inconditional branch)
//                     =>   if equal go to that subsection 
//                     !=>  if not equal go to that subsection
//                     =    write actual value to that variable
//                     +    transform to uppercase (e.g. +=std or =+ std)
//                     -    transform to lowercase (e.g. -=std or =- std)
//                     //   comment
//                     /*   begin comment
//                     */   end comment
//
// Variables:          std  standard shorthand form
//                     prt  print shorthand form
//                     act  actual form
//
// Apart from //, /*, */ (that can be used anywhere), special characters and variables 
// can only be used inside ()

require_once "vsteno_fullpage_template_top.php";
require_once "data.php";

$system_as_text_complete = "";
$system_as_text_token_section = "";
$system_as_text_rules_section = "";


/////////////////////// token section ///////////////////////////////////////////////////

function GetTokenDataSeparator( $offset ) {
    switch ($offset) {
        case 0 : return "/*header*/"; break;
        case 24 : return "/*data*/"; break;
        default : return (($offset % 8) == 0) ? "/**/" : ""; break;
    }
}

function GenerateBaseSubsection() {
    global $steno_tokens_master;            // variable containing token definitions in old parser
    $output = "\t#BeginSubSection(base)\n";
    $definition = "";
    foreach($steno_tokens_master as $key => $value) {
        $definition = "\t\t$key => { ";
        $i = 0;
        $length = count($value);
        foreach($value as $element) {
            $write = (mb_strlen($element)==0) ? "0" : $element;
            $separator = " " . GetTokenDataSeparator( $i++ );
            $comma = ($i == $length) ? "" : ",";
            $definition .= "$separator $write" . "$comma";
        }
        $definition .= " }";
        $output .= "$definition\n";
    }
    $output .= "\t#EndSubSection(base)\n";
    return $output;
}

function GenerateCombinerSubsection() {
    global $combiner_table;             // variable containing TokenCombiner-definitions in old parser
    $output = "\t#BeginSubSection(combiner)\n";
    $definition = "";
    foreach($combiner_table as $data_array) {
        $first = $data_array[0];
        $second = $data_array[1];
        $delta_x = $data_array[2];
        $delta_y = $data_array[3];
        $definition = "\t\t$first => { $second, /*delta*/ $delta_x, $delta_y }";
        $output .= "$definition\n";
    }
    $output .= "\t#EndSubSection(combiner)\n";
    return $output;
}

function GenerateShifterSubsection() {
    global $shifter_table;             // variable containing TokenShifter-definitions in old parser
    $output = "\t#BeginSubSection(shifter)\n";
    $definition = "";
    foreach($shifter_table as $data_array) {
        $original = $data_array[0];
        $new = $data_array[1];
        $shift_x = $data_array[2];
        $shift_y = $data_array[3];
        $delta_x = $data_array[4];
        $delta_y = $data_array[5];
        $definition = "\t\t$original => { $new, /*shift*/ $shift_x, $shift_y, /*delta*/ $delta_x, $delta_y }";
        $output .= "$definition\n";
    }
    $output .= "\t#EndSubSection(shifter)\n";
    return $output;
}

function GenerateTokenSubsections() {
    $output = "";
    $output .= GenerateBaseSubsection();
    $output .= GenerateCombinerSubsection();
    $output .= GenerateShifterSubsection();
    return $output;
}

function GenerateTokenSection() {
    global $steno_tokens_master, $system_as_text_token_section;            // variable containing token definitions in old parser
    $system_as_text_token_section = "#BeginSection(tokens)\n";
    $system_as_text_token_section .= GenerateTokenSubsections();
    $system_as_text_token_section .= "#EndSection(tokens)\n";
}

//////////////////////// rules section /////////////////////////////////////////////////////

function GenerateGenericRulesSubsection( $name, $table, $options ) {
    $output = "\t#BeginSubSection($name)\n";
    foreach ($table as $key => $value) {
        if (gettype($value) === "array") {
                $definition = "\t\t\"$key\" => {";
                $i=1; $length=count($value);
                foreach ($value as $element) {
                    $comma = ($i++ == $length) ? "" : ",";
                    $definition .= " \"$element\"" . "$comma";
                }
                $definition .= " }\n";
        } else $definition = "\t\t\"$key\" => \"$value\",\n";
        $output .= $definition;
    }
    $output .= "\t#EndSubSection($name" . "$options)\n";
    return $output;
}

function AddSpecialCapitalizerSections() {
    $output = "\t#BeginSubSection(capitalizer)\n\t\t\"[a-z]\" => \"strtoupper()\",\n\t#EndSubSection(capitalizer)\n";
    $output .= "\t#BeginSubSection(decapitalizer)\n\t\t\"[A-Z]\" => \"strtolower()\",\n\t#EndSubSection(decapitalizer)\n";
    return $output;
}

function GenerateRulesSubsections() {
    global $helvetizer_table, $trickster_table, $filter_table, $shortener_table, $normalizer_table, $bundler_table, $transcriptor_table, $substituter_table;
    $output = "";
    $output .= GenerateGenericRulesSubsection( "helvetizer", $helvetizer_table);
    $output .= GenerateGenericRulesSubsection( "trickster", $trickster_table, ",=>decapitalizer,!=>filter" );
    $output .= AddSpecialCapitalizerSections();
    $output .= GenerateGenericRulesSubsection( "filter", $filter_table );
    $output .= GenerateGenericRulesSubsection( "shortener", $shortener_table );
    $output .= GenerateGenericRulesSubsection( "normalizer", $normalizer_table );
    $output .= GenerateGenericRulesSubsection( "bundler", $bundler_table, ",=std" );
    $output .= GenerateGenericRulesSubsection( "transcriptor", $transcriptor_table );
    $output .= GenerateGenericRulesSubsection( "substituter", $substituter_table, ",=prt" );
    return $output;
}

function GenerateRulesSection() {
    global $system_as_text_rules_section;
    $system_as_text_rules_section = "#BeginSection(rules)\n";
    $system_as_text_rules_section .= GenerateRulesSubsections();
    $system_as_text_rules_section .= "#EndSection(rules)\n";
}
// main
GenerateTokenSection();
GenerateRulesSection();

$system_as_text_complete = $system_as_text_token_section . $system_as_text_rules_section;

echo "Generated text:<br><br><textarea id='system_as_text' name='system_as_text' rows='55' cols='230'>" . htmlspecialchars($system_as_text_complete) . "</textarea><br>";


require_once "vsteno_fullpage_template_bottom.php";
?>