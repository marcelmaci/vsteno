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
echo "<h2>General</h2>";
echo "MODEL: $model_name<br>HUNSPELL: " . $_SESSION['language_hunspell'] . " ($hunspell_yesno)<br>HYPHENATOR: " . $_SESSION['language_hyphenator'] . " ($hyphens_yesno)";
echo "SEPARATE: " . $_SESSION['composed_words_separate'];
echo "GLUE: " . $_SESSION['composed_words_glue'];

echo "<h2>Prefixes</h2>";
echo ShowListOrEmpty($_SESSION['prefixes_list']);

echo "<h2>Stems</h3>";
echo ShowListOrEmpty($_SESSION['stems_list']);

echo "<h2>Suffixes</h3>";
echo ShowListOrEmpty($_SESSION['suffixes_list']);

echo '<p><a href="input.php"><br><button>zurück</button></a></p>';

require "vsteno_template_bottom.php"; 
?>