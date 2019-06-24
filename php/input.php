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
require "vsteno_template_top.php"; require_once "session.php"; $_SESSION['return_address'] = "input.php"; ?>
<br>
<center>
<?php require_once "constants.php"; echo "<i>Commit: " . version_commit_id . " (" . version_date . ")</i><br>"; ?>
<b>HINWEIS: VSTENO ist beta - es sind noch nicht alle Optionen implementiert!<br><br></b>
<div id="order">
<form action="calculate.php" method="post">
<table>
<tr><td>Text</td></tr>
<tr><td>
<input type="radio" name="text_format_metayesno" value="normal" <?php echo ($_SESSION['original_text_format'] === "normal") ? "checked" : "";?>> Langschrift 
<input type="radio" name="text_format_metayesno" value="lng" <?php echo ($_SESSION['original_text_format'] === "lng") ? "checked" : "";?>> Meta (LNG) 
<input type="radio" name="text_format_metayesno" value="std" <?php echo ($_SESSION['original_text_format'] === "std") ? "checked" : "";?>> Meta (STD)
<input type="radio" name="text_format_metayesno" value="prt" <?php echo ($_SESSION['original_text_format'] === "prt") ? "checked" : "";?>> Meta (PRT)
<input type="checkbox" name="text_format_ascii_yesno" value="ascii" <?php echo ($_SESSION['original_text_ascii_yesno']) ? "checked" : "";?>> Breaks<br>
<!-- There's seems to be a bug in the textarea element ?! Any number of linebreaks \n at the beginning disappear when string is reinserted -->
<!-- Adding an additional \n when reinserting the string seems to help ?! (Exact number of linebreaks is preserved.) -->

<textarea id="original_text" name="original_text" rows="10" cols="100"><?php echo "\n" . $_SESSION['original_text_content']; ?>
</textarea>
</td></tr>
<tr><td>Header</td></tr>
<tr><td>
<input type="checkbox" name="title_yesno" value="title_yes" <?php echo ($_SESSION['title_yesno']) ? "checked" : "";?>>Titel 
<input type="text" name="title_text" size="50" value="<?php echo $_SESSION['title_text']; ?>">&nbsp;&nbsp;Grösse
<input type="radio" name="title_size" value="size_h1" <?php echo ($_SESSION['title_size'] == 1) ? "checked" : "";?>>h1
<input type="radio" name="title_size" value="size_h2" <?php echo ($_SESSION['title_size'] == 2) ? "checked" : "";?>>h2&nbsp;&nbsp;Farbe
<input type="text" name="title_color"  size="10" value="<?php echo $_SESSION['title_color']; ?>">
<br>
<input type="checkbox" name="introduction_yesno" value="introduction_yes" <?php echo ($_SESSION['introduction_yesno']) ? "checked" : "";?>>Einleitung
Grösse <input type="Text" name="introduction_size"  size="10" value="<?php echo $_SESSION['introduction_size']; ?>" >
Farbe <input type="Text" name="introduction_color"  size="10" value="<?php echo $_SESSION['introduction_color']; ?>" >
<br>
<textarea name="introduction_text" value="" rows="4" cols="100"><?php echo $_SESSION['introduction_text']; ?></textarea>
</td></tr>

<tr><td>Engine</td></tr>
<tr><td>Modell:<br>

<?php
    $models_list = $_SESSION['standard_models_list'];
    foreach($models_list as $name => $description) {
        if ($name === $_SESSION['actual_model']) {
                $tag_start = "<b>*";
                $tag_end = "</b>"; 
        } else {
                $tag_start = "";
                $tag_end = "";
        }
        echo "<input type='submit' name='action' value='$name'> $tag_start$description$tag_end";
        if (mb_strlen($tag_start)>0) echo " => <a href='model_info.php'>Info</a><br>";
        else echo "<br>";
    }
    if ($_SESSION['user_logged_in']) {
        if ($_SESSION['actual_model'] === GetDBUserModelName()) {
                $tag_start = "<b>*";
                $tag_end = "</b>"; 
        } else {
                $tag_start = "";
                $tag_end = "";
        }
        //$cu_checked = ($_SESSION['model_standard_or_custom'] === "custom") ? " checked" : ""; 
        echo "<input type='submit' name='action' value='" . GetDBUserModelName() . "'> $tag_start" . "Custom (editierbares Modell)$tag_end";
        if (mb_strlen($tag_start)>0) echo " => <a href='model_info.php'>Info</a><br>";
        else echo "<br>";
    }

?>

Spacer: <input type="checkbox" name="spacer_autoinsert" value="yes" <?php echo ($_SESSION['spacer_autoinsert']) ? "checked" : ""?>> automatisch
</td></tr>
<tr><td>Sprache</td></tr>
<tr><td>
Analyse:<br>
<input type="radio" name="analysis_type" value="none"<?php echo ($_SESSION['analysis_type'] === "none") ? " checked" : "";?>> keine<br>
<input type="radio" name="analysis_type" value="written"<?php echo ($_SESSION['analysis_type'] === "written") ? " checked" : "";?>> Schrift: 
<input type="checkbox" name="hyphenate_yesno" value="hyphenate_yes" <?php echo ($_SESSION['hyphenate_yesno']) ? "checked" : "";?>> Silben
<input type="text" name="language_hyphenator"  size="6" value="<?php echo $_SESSION['language_hyphenator']; ?>">
<input type="checkbox" name="composed_words_yesno" value="composed_words_yes" <?php echo ($_SESSION['composed_words_yesno']) ? "checked" : "";?>> Wörter
<input type="text" name="language_hunspell"  size="6" value="<?php echo $_SESSION['language_hunspell']; ?>">
=> <a href="show_analyzer_parameters.php">Parameter</a><br>
<input type="radio" name="analysis_type" value="phonetics"<?php echo ($_SESSION['analysis_type'] === "phonetics") ? " checked" : "";?>> Phonetik: 
<input type="text" name="language_espeak"  size="6" value="<?php echo $_SESSION['language_espeak']; ?>">
<input type="radio" name="phonetical_alphabet" value="espeak"<?php echo ($_SESSION['phonetical_alphabet'] === "espeak") ? " checked" : "";?>>espeak
<input type="radio" name="phonetical_alphabet" value="ipa"<?php echo ($_SESSION['phonetical_alphabet'] === "ipa") ? " checked" : "";?>>IPA
<br>
Markieren:<br>
<input type="checkbox" name="colored_nouns_yesno" value="colored_nouns_yes" <?php echo ($_SESSION['color_nounsyesno']) ? "checked" : "";?>> 
Hauptwörter: 
Farbe <input type="text" name="nouns_color"  size="10" value="<?php echo $_SESSION['color_nouns']; ?>">
Stil <input type="text" name="nouns_style"  size="10" value="<?php echo $_SESSION['nouns_style']; ?>">
<br>
<input type="checkbox" name="colored_beginnings_yesno" value="colored_beginnings_yes" <?php echo ($_SESSION['color_beginningsyesno']) ? "checked" : "";?>> 
Satzanfänge: 
Farbe <input type="text" name="beginnings_color"  size="10" value="<?php echo $_SESSION['color_beginnings']; ?>">
Stil <input type="text" name="beginnings_style" size="10" value="<?php echo $_SESSION['beginnings_style']; ?>">
<br>
Andere: <input type="text" name="marker_word_list" size="100" value="<?php echo $_SESSION['mark_wordlist']; ?>"><br>
</td></tr>

<tr><td>Zeichen</td></tr>
<tr><td>
Grösse <input type="text" name="token_size"  size="10" value="<?php echo $_SESSION['token_size']; ?>">
Dicke <input type="text" name="token_thickness"  size="10" value="<?php echo $_SESSION['token_thickness']; ?>">
Neigung <input type="text" name="token_inclination"  size="10" value="<?php echo $_SESSION['token_inclination']; ?>">
<br>
Farbe <input type="text" name="token_color"  size="10" value="<?php echo $_SESSION['token_color']; ?>">
Schattierung <input type="text" name="token_shadow"  size="10" value="<?php echo $_SESSION['token_shadow']; ?>">
<input type="checkbox" name="background_color_yesno" value="background_color_yes" <?php echo ($_SESSION['color_backgroundyesno']) ? "checked" : "";?>> 
Hintergrund <input type="text" name="background_color"  size="10" value="<?php echo $_SESSION['color_background']; ?>">
<br>
Abstand: kein <input type="text" name="distance_none"  size="10" value="<?php echo $_SESSION['token_distance_none']; ?>"> eng
<input type="text" name="distance_narrow" size="10" value="<?php echo $_SESSION['token_distance_narrow']; ?>"> weit 
<input type="text" name="distance_wide"  size="10" value="<?php echo $_SESSION['token_distance_wide']; ?>">
Wort: <input type="text" name="distance_words"  size="10" value="<?php echo $_SESSION['distance_words']; ?>">
<br>
Stil: 
<input type="radio" name="token_line_style" value="solid" <?php echo ($_SESSION['token_style_type'] === "solid") ? "checked" : "";?>> Linie 
<input type="radio" name="token_line_style" value="dotted" <?php echo ($_SESSION['token_style_type'] === "dotted") ? "checked" : "";?>> gepunktet 
<input type="radio" name="token_line_style" value="dashed" <?php echo ($_SESSION['token_style_type'] === "dashed") ? "checked" : "";?>> gestrichelt 
<input type="radio" name="token_line_style" value="custom" <?php echo ($_SESSION['token_style_type'] === "custom") ? "checked" : "";?>> benutzerdefiniert:
<input type="text" name="token_line_style_custom_value"  size="10" value="<?php echo $_SESSION['token_style_custom_value'];?>">
<br>
</td></tr>
<tr><td>Hilfslinien</td></tr>
<tr><td>
Allgemein: 
Farbe <input type="text" name="auxiliary_lines_color"  size="10" value="<?php echo $_SESSION['auxiliary_color_general']; ?>"> 
Dicke <input type="text" name="auxiliary_lines_thickness"  size="10" value="<?php echo $_SESSION['auxiliary_thickness_general']; ?>"> 
Stil <input type="text" name="auxiliary_lines_style"  size="10" value="<?php echo $_SESSION['auxiliary_style_general']; ?>">
Ränder: 
L<input type="text" name="auxiliary_lines_margin_left"  size="2" value="<?php echo $_SESSION['auxiliary_lines_margin_left']; ?>">
R<input type="text" name="auxiliary_lines_margin_right"  size="2" value="<?php echo $_SESSION['auxiliary_lines_margin_right']; ?>">
<br>
<input type="checkbox" name="baseline_yesno" value="baseline_yes" <?php echo ($_SESSION['auxiliary_baselineyesno']) ? "checked" : "";?>> 
Grundlinie: 
Dicke <input type="text" name="baseline_thickness"  size="10" value="<?php echo $_SESSION['auxiliary_baseline_thickness']; ?>"> 
Farbe <input type="text" name="baseline_color"  size="10" value="<?php echo $_SESSION['auxiliary_baseline_color']; ?>">
Stil <input type="text" name="baseline_style"  size="10" value="<?php echo $_SESSION['baseline_style']; ?>">
<input type="checkbox" name="baseline_nomargin" value="nomargin" <?php echo ($_SESSION['baseline_nomargin_yesno']) ? "checked" : "";?>> 
kein Rand
<br>
<input type="checkbox" name="upper12_yesno" value="upper12_yes" <?php echo ($_SESSION['auxiliary_upper12yesno']) ? "checked" : "";?>> 
1./2. Oberstufe: 
Dicke <input type="text" name="upper12_thickness"  size="10" value="<?php echo $_SESSION['auxiliary_upper12_thickness']; ?>"> 
Farbe <input type="text" name="upper12_color"  size="10" value="<?php echo $_SESSION['auxiliary_upper12_color']; ?>">
Stil <input type="text" name="upper12_style"  size="10" value="<?php echo $_SESSION['upper12_style']; ?>">
<input type="checkbox" name="upper12_nomargin" value="nomargin" <?php echo ($_SESSION['upper12_nomargin_yesno']) ? "checked" : "";?>> 
kein Rand
<br>
<input type="checkbox" name="lower_yesno" value="lower_yes" <?php echo ($_SESSION['auxiliary_loweryesno']) ? "checked" : "";?>> 
Unterstufe: 
Dicke <input type="text" name="lower_thickness"  size="10" value="<?php echo $_SESSION['auxiliary_lower_thickness']; ?>"> 
Farbe <input type="text" name="lower_color"  size="10" value="<?php echo $_SESSION['auxiliary_lower_color']; ?>">
Stil <input type="text" name="lower_style"  size="10" value="<?php echo $_SESSION['lower_style']; ?>">
<input type="checkbox" name="lower_nomargin" value="nomargin" <?php echo ($_SESSION['lower_nomargin_yesno']) ? "checked" : "";?>> 
kein Rand
<br>
<input type="checkbox" name="upper3_yesno" value="upper3_yes" <?php echo ($_SESSION['auxiliary_upper3yesno']) ? "checked" : "";?>> 
3. Oberstufe: 
Dicke <input type="text" name="upper3_thickness"  size="10" value="<?php echo $_SESSION['auxiliary_upper3_thickness']; ?>"> 
Farbe <input type="text" name="upper3_color"  size="10" value="<?php echo $_SESSION['auxiliary_upper3_color']; ?>">
Stil <input type="text" name="upper3_style"  size="10" value="<?php echo $_SESSION['upper3_style']; ?>">
<input type="checkbox" name="upper3_nomargin" value="nomargin" <?php echo ($_SESSION['upper3_nomargin_yesno']) ? "checked" : "";?>> 
kein Rand
<br>
</td></tr>
<tr><td>Ausgabe</td></tr>
<tr><td>
Fenster: 
<input type="radio" name="output_integratedyesno" value="output_integrated" <?php echo ($_SESSION['output_integratedyesno']) ? "checked" : "";?>>integriert 
<input type="radio" name="output_integratedyesno" value="output_full_page" <?php echo (!$_SESSION['output_integratedyesno']) ? "checked" : "";?>>Vollseite
<input type="checkbox" name="output_without_button_yesno" value="output_without_button_yes" <?php echo ($_SESSION['output_without_button_yesno']) ? "checked" : "";?>>ohne Button
<br>
<input type="radio" name="output_format" value="inline" <?php echo ($_SESSION['output_format'] === "inline") ? "checked" : "";?>> Inline 
(<input type="checkbox" name="output_text_tags" value="text_tags_yes" <?php echo ($_SESSION['output_texttagsyesno']) ? "checked" : "";?>> Tags)
<input type="radio" name="output_format" value="meta_lng" <?php echo ($_SESSION['output_format'] === "meta_lng") ? "checked" : "";?>> LNG
<input type="radio" name="output_format" value="meta_std" <?php echo ($_SESSION['output_format'] === "meta_std") ? "checked" : "";?>> STD
<input type="radio" name="output_format" value="meta_prt" <?php echo ($_SESSION['output_format'] === "meta_prt") ? "checked" : "";?>> PRT
<input type="radio" name="output_format" value="train" <?php echo ($_SESSION['output_format'] === "train") ? "checked" : "";?>> Training
<input type="radio" name="output_format" value="debug" <?php echo ($_SESSION['output_format'] === "debug") ? "checked" : "";?>> Debug 
=> <a href="rules_statistics.php">Regeln</a>
<br>

<input type="radio" name="output_format" value="layout" <?php echo ($_SESSION['output_format']) === "layout" ? "checked" : "";?>> Layout 
Breite: <input type="text" name="layout_width"  size="10" value="<?php echo $_SESSION['output_width']; ?>"> 
Höhe: <input type="text" name="layout_height"  size="10" value="<?php echo $_SESSION['output_height']; ?>"><br>
Ränder: 
L: <input type="text" name="left_margin"  size="10" value="<?php echo $_SESSION['left_margin']; ?>">
R: <input type="text" name="right_margin"  size="10" value="<?php echo $_SESSION['right_margin']; ?>">
O: <input type="text" name="top_margin"  size="10" value="<?php echo $_SESSION['top_margin']; ?>">
U: <input type="text" name="bottom_margin"  size="10" value="<?php echo $_SESSION['bottom_margin']; ?>">
<input type="checkbox" name="show_margins" value="yes" <?php echo ($_SESSION['show_margins']) ? "checked" : "";?>> anzeigen

<br>
Systemhöhe: <input type="text" name="num_system_lines"  size="10" value="<?php echo $_SESSION['num_system_lines']; ?>">
1. Zeile: <input type="text" name="baseline"  size="10" value="<?php echo $_SESSION['baseline']; ?>">
<br>
Stil: <input type="radio" name="layout_style" value="align_left" <?php echo ($_SESSION['output_style'] === "align_left") ? "checked" : "";?>> 
Flattersatz <input type="radio" name="layout_style" value="align_left_right" <?php echo ($_SESSION['output_style'] == "align_left_right") ? "checked" : "";?>> Blocksatz
<input type="checkbox" name="show_distances" value="yes" <?php echo ($_SESSION['show_distances']) ? "checked" : "";?>> anzeigen

<br>
<input type="checkbox" name="line_number_yesno" value="yes" <?php echo ($_SESSION['output_line_number_yesno']) ? "checked" : "";?>> 
Linien: jede <input type="text" name="line_number_step"  size="1" value="<?php echo $_SESSION['output_line_number_step']; ?>">. 
Position: x <input type="text" name="line_number_posx"  size="3" value="<?php echo $_SESSION['output_line_number_posx']; ?>">
Anheben: <input type="text" name="line_number_deltay"  size="3" value="<?php echo $_SESSION['output_line_number_deltay']; ?>">
Farbe: <input type="text" name="line_number_color"  size="4" value="<?php echo $_SESSION['output_line_number_color']; ?>">

<br>
<input type="checkbox" name="page_number_yesno" value="yes" <?php echo ($_SESSION['output_page_number_yesno']) ? "checked" : "";?>> 
Seitenzahlen: Beginn <input type="text" name="page_number_first"  size="3" value="<?php echo $_SESSION['output_page_number_first']; ?>"> 
auf Seite <input type="text" name="page_number_start"  size="3" value="<?php echo $_SESSION['output_page_number_start']; ?>">
Position: x <input type="text" name="page_number_posx"  size="4" value="<?php echo $_SESSION['output_page_number_posx']; ?>">
y <input type="text" name="page_number_posy"  size="4" value="<?php echo $_SESSION['output_page_number_posy']; ?>">
Farbe <input type="text" name="page_number_color"  size="4" value="<?php echo $_SESSION['output_page_number_color']; ?>">

</td></tr>
</table>

<input type="submit" name="action" value="abschicken"><input type="submit" name="action" value="aktualisieren">
</form>
</div>
</center>
<?php require "vsteno_template_bottom.php"; ?>