<?php

require_once "vsteno_template_top.php";

require_once "data.php";
require_once "constants.php";
require_once "parser.php";
require_once "session.php";
require_once "engine.php";
require_once "dbpw.php";

global $separated_std_form, $separated_prt_form;

function die_more_elegantly( $text ) {
    echo "$text";
    echo '<a href="olympus.php"><br><button>zurück</button></a><br><br>';   
    require_once "vsteno_template_bottom.php";
    die();
}

function show_title_test_model() {
    global $model_name, $olympus;
    echo "<h1>Statistik</h1>";
    $model_name = $_SESSION['actual_model'];
    $olympus = GetDBName( "olympus" );
    echo "<p>Berechnung für das Modell <b>" . $_SESSION['model_standard_or_custom'] . "</b> (<b>$model_name</b>) und Olympus (<b>$olympus</b>).</p>";
}

function calculate_and_show_false() {
    global $model_name, $olympus, $separated_std_form, $separated_prt_form, $processing_in_parser;
    global $std_form;
    $conn = connect_or_die();
    
    // Check connection
    if ($conn->connect_error) {
        die_more_elegantly("Verbindung nicht möglich: " . $conn->connect_error . "<br>");
    }
    
    $sql = "SELECT * FROM $olympus";
    echo "<p>QUERY: $sql</p>";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $total_entries = $result->num_rows;
        $is_correct = true;
        $correct_output_with_dictionary = "";
        $correct_output_rules_only = "";
        $wrong_output = "";
        for ($i=0; $i<$total_entries; $i++) {
            $false_forms_string = "(";
            $row = $result->fetch_assoc();
            // set data variables
            $olympus_word_id = $row['word_id'];
            $olympus_word = $row['word'];
            //echo "$i: $olympus_word<br>";
            $olympus_number_forms = $row['number_forms'];
            //$olympus_recommended_form = $row['recommended_form'];
            //$olympus_word = $row['submitted_by'];
            //$olympus_word = $row['reviewed_by'];
            $olympus_single_bas = $row['single_bas'];
            $olympus_single_std = $row['single_std'];
            $olympus_single_prt = $row['single_prt'];
            $olympus_separated_bas = $row['separated_bas'];
            $olympus_separated_std = $row['separated_std'];
            $olympus_separated_prt = $row['separated_prt'];
            // calculate single form
            //$nil = SingleWord2SVG( $olympus_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
            //$nil = NormalText2TokenList( $olympus_word );
            $nil = NormalText2SVG( $olympus_word );
            $calculated_std = mb_strtoupper($separated_std_form);
            $calculated_prt = mb_strtoupper($separated_prt_form);
            $comma = "";
            //echo "$olympus_word: #$calculated_std#$calculated_prt# (olympus: #$olympus_single_std#$olympus_single_prt#)<br>";
            if ($calculated_std !== $olympus_single_std) {
                $is_correct = FALSE;
                $false_forms_string .= "std:$calculated_std=/=$olympus_single_std";
                $comma = "/";
            }
            if ($calculated_prt !== $olympus_single_prt) {
                $is_correct = FALSE;
                $false_forms_string .= $comma . "prt";
            }
            if (mb_strlen($false_forms_string) > 0) $false_forms_string .= ")";
            // calculate composed form (question: can there be a composed form in olympus?!?)
            if (!$is_correct) $wrong_output .= "<a href='olympus1.php?word_id=$olympus_word_id'>$olympus_word</a> $false_forms_string ";
            elseif ($processing_in_parser === "D") $correct_output_with_dictionary .= "<a href='olympus1.php?word_id=$olympus_word_id'>$olympus_word</a> ";
                else $correct_output_rules_only .= "<a href='olympus1.php?word_id=$olympus_word_id'>$olympus_word</a> ";
        }  
        if ($wrong_output === "") $wrong_output = "Keine falschen Berechnungen";
        if ($correct_output_with_dictionary === "") $correct_output_with_dictionary = "Keine richtigen Berechnungen mit Wörterbuch.";
        if ($correct_output_rules_only === "") $correct_output_rules_only = "Keine richtigen Berechnung ohne Wörterbuch (nur Regeln).";
        echo "<h1>Falsch</h1><p>$wrong_output</p><h1>Richtig</h1><h2>Mit Wörterbuch</h2><p>$correct_output_with_dictionary</p>
             <h2>Nur Regeln</h2><p>$correct_output_rules_only</p>";
        echo '<a href="olympus.php"><br><button>zurück</button></a><br><br>';   
    
    } else {
        echo "<p>QUERY: $sql</p>";
        die_more_elegantly("Keine Einträge in Olympus ($olympus) vorhanden.<br>");
    }
}

// main
show_title_test_model();
calculate_and_show_false();

require_once "vsteno_template_bottom.php";


?>