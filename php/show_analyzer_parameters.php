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

function ShowListOrEmpty($string) {
    if (mb_strlen($string) === 0) return "(empty)";
    else return $string;
}

require "vsteno_template_top.php"; 
require_once "session.php"; 
$_SESSION['return_address'] = "input.php";

echo "<h1>Parameters</h1>";
$model_name = ($_SESSION['model_standard_or_custom'] === "standard") ? $_SESSION['selected_std_model'] : GetDBUserModelName();
$hunspell_yesno = ($_SESSION['composed_words_yesno']) ? "yes" : "no";
$hyphens_yesno = ($_SESSION['hyphenate_yesno']) ? "yes" : "no";
$phonetics_yesno = ($_SESSION['phonetics_yesno']) ? "yes" : "no";
$analysis_text = ($_SESSION['analysis_type'] === "none") ? "DISABLED" : "SELECTED";
if ($_SESSION['affixes_yesno']) $hunspell_options = "OPTIONS: affixes & words";
else {
    $hunspell_options = "FILTER: " . (($_SESSION['filter_out_prefixes_yesno']) ? "prefixes " : "") . (($_SESSION['filter_out_suffixes_yesno']) ? "suffixes " : "") . (($_SESSION['filter_out_words_yesno']) ? "words " : "");
}
echo "<h2>General</h2>";
echo "MODEL: $model_name";
echo "<br>ANALYSIS: $analysis_text";

if ($_SESSION['analysis_type'] === "selected") {
    echo "<br>HYPHENS: " . $_SESSION['language_hyphenator'] . " ($hyphens_yesno)";
    echo "<br>HUNSPELL: " . $_SESSION['language_hunspell'] . " ($hunspell_yesno)";
    echo "<br>$hunspell_options";
    echo "<br>PHONETICS: " . $_SESSION['language_espeak'] . " ($phonetics_yesno)";
    echo "<br>ALPHABET: " . $_SESSION['phonetical_alphabet']; 
    echo "<br>SEPARATE: " . $_SESSION['composed_words_separate'];
    echo "<br>GLUE: " . $_SESSION['composed_words_glue'];


    echo "<h2>Prefixes</h2>";
    echo ShowListOrEmpty($_SESSION['prefixes_list']);

    echo "<h2>Stems</h3>";
    echo ShowListOrEmpty($_SESSION['stems_list']);

    echo "<h2>Suffixes</h3>";
    echo ShowListOrEmpty($_SESSION['suffixes_list']);
    
    echo "<h2>Filter</h3>";
    echo ShowListOrEmpty($_SESSION['filter_list']);
    
    echo "<h2>Block</h3>";
    echo ShowListOrEmpty($_SESSION['block_list']);

    echo "<h2>Phonetics</h3>";
    echo ShowListOrEmpty($_SESSION['phonetics_transcription_list']);
    //var_dump($_SESSION['phonetics_transcription_array']);
}

echo '<p><a href="input.php"><br><button>zurück</button></a></p>';

require "vsteno_template_bottom.php"; 
?>