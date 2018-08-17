<?php require "vsteno_template_top.php"; require_once "session.php"; $_SESSION['return_address'] = "input.php"; ?>
<br>
<center>
<?php require_once "constants.php"; echo "<i>Commit: " . version_commit_id . " (" . version_date . ")</i><br>"; ?>
<b>HINWEIS: Es sind noch nicht alle Optionen implementiert.<br>
Im Moment funktionieren: Textfeld (Langschrift), Titel, Einleitung (ohne Grösse),<br>
Zeichen (Grösse, Dicke, Neigung), Textfarbe, Hilfslinien, Ausgabe (Fenster integriert,<br>
Vollseite; mit/ohne Button), Inline (mit/ohne Langschrift-Tags).<br><br>
</b>
<div id="order">
<form action="../php/calculate.php" method="post">
<table>
<tr><td>Text</td></tr>
<tr><td>
<input type="radio" name="text_format_metayesno" value="normal" <?php echo ($_SESSION['original_text_format'] === "normal") ? "checked" : "";?>> Langschrift 
<input type="radio" name="text_format_metayesno" value="metaform" <?php echo ($_SESSION['original_text_format'] === "metaform") ? "checked" : "";?>> Metaform<br>
<textarea id="original_text" name="original_text" rows="10" cols="100"><?php echo $_SESSION['original_text_content']; ?>
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
<tr><td>Markieren</td></tr>
<tr><td>
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
<tr><td>Hilfslinien</td></tr>
<tr><td>
Allgemein: 
Farbe <input type="text" name="auxiliary_lines_color"  size="10" value="<?php echo $_SESSION['auxiliary_color_general']; ?>"> 
Dicke <input type="text" name="auxiliary_lines_thickness"  size="10" value="<?php echo $_SESSION['auxiliary_thickness_general']; ?>"> 
Stil <input type="text" name="auxiliary_lines_style"  size="10" value="<?php echo $_SESSION['auxiliary_style_general']; ?>">
<br>
<input type="checkbox" name="baseline_yesno" value="baseline_yes" <?php echo ($_SESSION['auxiliary_baselineyesno']) ? "checked" : "";?>> 
Grundlinie: 
Dicke <input type="text" name="baseline_thickness"  size="10" value="<?php echo $_SESSION['auxiliary_baseline_thickness']; ?>"> 
Farbe <input type="text" name="baseline_color"  size="10" value="<?php echo $_SESSION['auxiliary_baseline_color']; ?>">
Stil <input type="text" name="baseline_style"  size="10" value="<?php echo $_SESSION['baseline_style']; ?>">
<br>
<input type="checkbox" name="upper12_yesno" value="upper12_yes" <?php echo ($_SESSION['auxiliary_upper12yesno']) ? "checked" : "";?>> 
1./2. Oberstufe: 
Dicke <input type="text" name="upper12_thickness"  size="10" value="<?php echo $_SESSION['auxiliary_upper12_thickness']; ?>"> 
Farbe <input type="text" name="upper12_color"  size="10" value="<?php echo $_SESSION['auxiliary_upper12_color']; ?>">
Stil <input type="text" name="upper12_style"  size="10" value="<?php echo $_SESSION['upper12_style']; ?>">
<br>
<input type="checkbox" name="lower_yesno" value="lower_yes" <?php echo ($_SESSION['auxiliary_loweryesno']) ? "checked" : "";?>> 
Unterstufe: 
Dicke <input type="text" name="lower_thickness"  size="10" value="<?php echo $_SESSION['auxiliary_lower_thickness']; ?>"> 
Farbe <input type="text" name="lower_color"  size="10" value="<?php echo $_SESSION['auxiliary_lower_color']; ?>">
Stil <input type="text" name="lower_style"  size="10" value="<?php echo $_SESSION['lower_style']; ?>">
<br>
<input type="checkbox" name="upper3_yesno" value="upper3_yes" <?php echo ($_SESSION['auxiliary_upper3yesno']) ? "checked" : "";?>> 
3. Oberstufe: 
Dicke <input type="text" name="upper3_thickness"  size="10" value="<?php echo $_SESSION['auxiliary_upper3_thickness']; ?>"> 
Farbe <input type="text" name="upper3_color"  size="10" value="<?php echo $_SESSION['auxiliary_upper3_color']; ?>">
Stil <input type="text" name="upper3_style"  size="10" value="<?php echo $_SESSION['upper3_style']; ?>">
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
<input type="checkbox" name="output_text_tags" value="text_tags_yes" <?php echo ($_SESSION['output_texttagsyesno']) ? "checked" : "";?>> Langschrift-Tags
<input type="radio" name="output_format" value="metaform" <?php echo ($_SESSION['output_format'] === "metaform") ? "checked" : "";?>> Metaform
<input type="radio" name="output_format" value="debug" <?php echo ($_SESSION['output_format'] === "debug") ? "checked" : "";?>> Debug<br>

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
<input type="checkbox" name="page_numbers_yesno" value="page_numbers_yes" <?php echo ($_SESSION['output_page_numberyesno']) ? "checked" : "";?>> 
Seitenzahlen: Beginn <input type="text" name="page_numbers_start_number"  size="10" value="<?php echo $_SESSION['output_page_start_value']; ?>"> 
auf Seite <input type="text" name="page_numbers_start_page"  size="10" value="<?php echo $_SESSION['output_page_start_at']; ?>">
</td></tr>
</table>

<input type="submit" name="action" value="abschicken"><input type="submit" name="action" value="zurücksetzen">
</form>
</div>
</center>
<?php require "vsteno_template_bottom.php"; ?>