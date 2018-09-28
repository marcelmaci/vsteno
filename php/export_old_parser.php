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
// system (tokens & rules) will be called "model"
//
// Structure for model file: see import_model.php

require_once "vsteno_fullpage_template_top.php";
require_once "data_bak.php";
require_once "dbpw.php";

$model_as_text_complete = "";
$model_as_text_token_section = "";
$model_as_text_rules_section = "";


/////////////////////// token section ///////////////////////////////////////////////////

function AddQuotes($data) {
    if (gettype($data) == "string") return "\"$data\"";
    else return $data;
}

function GetTokenDataSeparator( $offset ) {
    switch ($offset) {
        case 0 : return "/*h*/"; break;                     // strip out more comment to get below 65522 chars mark ...
        case 24 : return "/*d*/"; break;                    // idem (what the hell is this bug ... is it a regex limitation?!?)
        default : return (($offset % 8) == 0) ? "/**/" : ""; break;
    }
}

function GenerateBaseSubsection() {
    global $steno_tokens_master;            // variable containing token definitions in old parser
    $output = "\t#BeginSubSection(base)\n";
    $definition = "";
    foreach($steno_tokens_master as $key => $value) {
        $quotes_key = AddQuotes($key);
        $definition = "\t\t$quotes_key => { ";
        $i = 0;
        $length = count($value);
        foreach($value as $element) {
            $quotes_element = AddQuotes($element);
            $write = (mb_strlen($quotes_element)==0) ? "0" : $quotes_element;
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
        $first = AddQuotes($data_array[0]);
        $second = AddQuotes($data_array[1]);
        $delta_x = $data_array[2];
        $delta_y = $data_array[3];
        $definition = "\t\t$first => { $second, $delta_x, $delta_y }";
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
        $original = AddQuotes($data_array[0]);
        $new = AddQuotes($data_array[1]);
        $shift_x = $data_array[2];
        $shift_y = $data_array[3];
        $delta_x = $data_array[4];
        $delta_y = $data_array[5];
        $definition = "\t\t$original => { $new, $shift_x, $shift_y, $delta_x, $delta_y }"; // string gets truncated after 65522 chars ... no idea why ... 
        $output .= "$definition\n";                                                                            // temporary solution: strip out comments in order to get below that limit ...
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
    global $steno_tokens_master;            // variable containing token definitions in old parser
    $output = "#BeginSection(font)\n";
    $output .= GenerateTokenSubsections();
    $output .= "#EndSection(font)\n";
    return $output;
}

//////////////////////// rules section /////////////////////////////////////////////////////

function GenerateGenericRulesSubsection( $name, $table, $options_begin, $options_end ) {
    $output = "\t#BeginSubSection($name" . "$options_begin)\n";
    foreach ($table as $key => $value) {
        $quotes_key = AddQuotes($key);
        $quotes_value = AddQuotes($value);
        if (gettype($value) === "array") {
                $definition = "\t\t$quotes_key => {";
                $i=1; $length=count($value);
                foreach ($value as $element) {
                    $quotes_element = AddQuotes($element);
                    $comma = ($i++ == $length) ? "" : ",";
                    $definition .= " $quotes_element" . "$comma";
                }
                $definition .= " };\n";
        } else $definition = "\t\t$quotes_key => $quotes_value;\n";
        $output .= $definition;
    }
    $output .= "\t#EndSubSection($name" . "$options_end)\n";
    return $output;
}

function AddSpecialCapitalizerSections() {
    //$output = "\t#BeginSubSection(capitalizer)\n\t\t\"[a-z]\" => \"strtoupper()\";\n\t#EndSubSection(capitalizer) // dies ist ein Kommentar\n";
    $output = "";
    $output .= "\t#BeginSubSection(decapitalizer)\n\t\t\"([A-Z])\" => \"strtolower()\";\n\t#EndSubSection(decapitalizer)\n";
    return $output;
}

function GenerateRulesSubsections() {
    global $helvetizer_table, $trickster_table, $filter_table, $shortener_table, $normalizer_table, $bundler_table, $transcriptor_table, $substituter_table;
    $output = "";
    $output .= GenerateGenericRulesSubsection( "helvetizer", $helvetizer_table, ",@@wrd", ",@@dic");
    $output .= GenerateGenericRulesSubsection( "trickster", $trickster_table, "", ",=>decapitalizer,!>filter" );
    $output .= AddSpecialCapitalizerSections();
    $output .= GenerateGenericRulesSubsection( "filter", $filter_table );
    $output .= GenerateGenericRulesSubsection( "shortener", $shortener_table );
    $output .= GenerateGenericRulesSubsection( "normalizer", $normalizer_table );
    $output .= GenerateGenericRulesSubsection( "bundler", $bundler_table, "", ",=:std" );
    $output .= GenerateGenericRulesSubsection( "transcriptor", $transcriptor_table );
    $output .= GenerateGenericRulesSubsection( "substituter", $substituter_table, "", ",=:prt" );
    return $output;
}

function GenerateRulesSection() {
    $output = "#BeginSection(rules)\n";
    $output .= GenerateRulesSubsections();
    $output .= "#EndSection(rules)\n";
    return $output;
}

function GenerateCompleteModelAsText() {
    global $model_as_text_token_section, $model_as_text_rules_section;
    $model_as_text_token_section = GenerateTokenSection();
    $model_as_text_rules_section = GenerateRulesSection();
    return $model_as_text_token_section . $model_as_text_rules_section;
}

function die_more_elegantly( $text ) {
    echo "$text";
    echo '<a href="create_account.php"><br><button>zurück</button></a><br><br>';   
    require_once "vsteno_template_bottom.php";
    die();
}

function WriteDataToDatabase() {
    global $model_as_text_complete, $model_as_text_token_section, $model_as_text_rules_section, $conn;
    
    echo "<h1>Exportieren</h1><br>";
    // Create connection
    $conn = Connect2DB();

    // Check connection
    if ($conn->connect_error) {
        die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
    }

    // prepare data
    $user_id = "99999";
    $name = $conn->real_escape_string("$user_id" . "_default");
    $header = "#BeginSection(header)\n\t/* this file was automatically generated as export from old parser */\n#EndSection(header)\n";
    $font = $conn->real_escape_string($model_as_text_token_section);
    $rules = $conn->real_escape_string($model_as_text_rules_section);
    
    $sql = "INSERT INTO models (user_id, name, header, font, rules)
    VALUES ( '$user_id', '$name', '$header', '$font', '$rules')";

    if ($conn->query($sql) === TRUE) {
        echo "Model in Datenbank geschrieben.<br>";
    } else {
        die_more_elegantly("Fehler: " . $sql . "<br>" . $conn->error . "<br>");
    }

}
// main

$model_as_text_complete = GenerateCompleteModelAsText();
WriteDataToDatabase();


//echo "Generated text:<br><br><textarea id='model_as_text' name='Model_as_text' rows='55' cols='230'>" . htmlspecialchars($model_as_text_complete) . "</textarea><br>";

require_once "vsteno_fullpage_template_bottom.php";


?>