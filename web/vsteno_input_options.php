<?php require "vsteno_template_top.php"; require_once "../php/session.php"; ?>
<br>
<center>
<b>HINWEIS: Es ist noch keine der Optionen freigeschaltet - im Moment funktioniert nur das Textfeld!</b>
<div id="order">
<form action="../php/calculate.php" method="post">
<table>
<tr><td>Text</td></tr>
<tr><td>
<input type="radio" name="text_format_metayesno" value="title_yes" <?php echo ($_SESSION['original_text_format'] === "normal") ? "checked" : "";?>> Langschrift 
<input type="radio" name="text_format_metayesno" value="title_introduction_yes" <?php echo ($_SESSION['original_text_format'] === "metaform") ? "checked" : "";?>> Metaform<br>
<textarea id="original_text" name="original_text" rows="20" cols="100"><?php echo $_SESSION['original_text_content']; ?>
</textarea>
</td></tr>
<tr><td>Header</td></tr>
<tr><td>
<input type="checkbox" name="title_yesno" value="title_yes" <?php echo ($_SESSION['title_yesno']) ? "checked" : "";?>>Titel 
<input type="text" name="title_text" size="50" value="<?php echo $_SESSION['title_text']; ?>">&nbsp;&nbsp;Grösse
<input type="radio" name="title_size" value="size_h1" <?php echo ($_SESSION['title_size'] == 1) ? "checked" : "";?>>h1
<input type="radio" name="title_size" value="size_h2" <?php echo ($_SESSION['title_size'] == 2) ? "checked" : "";?>>h2&nbsp;&nbsp;Farbe
<input type="text" value="<?php echo $_SESSION['title_color']; ?>"><br>
<input type="checkbox" name="title_introduction_yesno" <?php echo ($_SESSION['title_yesno']) ? "checked" : "";?>>Einleitung<br>
<textarea name="title_introduction_text" value="" rows="4" cols="100"><?php echo $_SESSION['introduction_text']; ?></textarea>
<br>Grösse <input type="Text" name="introduction_size" value="<?php echo $_SESSION['introduction_size']; ?>" ><br>
</td></tr>
<tr><td>Zeichen</td></tr>
<tr><td>
Grösse <input type="text" name="token_size" value="<?php echo $_SESSION['token_size']; ?>"><br>
Dicke <input type="text" name="token_thickness" value="<?php echo $_SESSION['token_thickness']; ?>"><br>
Schattierung <input type="text" name="token_shadow_relative_size" value="<?php echo $_SESSION['token_shadow']; ?>"><br>
Abstand: kein <input type="text" name="distance_none" value="<?php echo $_SESSION['token_distance_none']; ?>"> eng
<input type="text" name="distance_narrow" value="<?php echo $_SESSION['token_distance_narrow']; ?>"> weit 
<input type="text" name="distance_wide" value="<?php echo $_SESSION['token_distance_wide']; ?>"><br>
Stil: 
<input type="radio" name="token_line_style" value="style_solid" <?php echo ($_SESSION['token_style_type'] === "solid") ? "checked" : "";?>> Linie 
<input type="radio" name="token_line_style" value="style_dotted" <?php echo ($_SESSION['token_style_type'] === "dotted") ? "checked" : "";?>> gepunktet 
<input type="radio" name="token_line_style" value="style_dashed" <?php echo ($_SESSION['token_style_type'] === "solid") ? "dashed" : "";?>> gestrichelt 
<input type="radio" name="token_line_style" value="style_custom" <?php echo ($_SESSION['token_style_type'] === "custom") ? "checked" : "";?>> benutzerdefiniert:
<input type="text" name="token_line_style_custom_value" value="<?php echo $_SESSION['token_style_custom_value'];?>">
<br>

</td></tr>
<tr><td>Farbe</td></tr>
<tr><td>
Text <input type="text" name="token_color" value="<?php echo $_SESSION['color_text_in_general']; ?>"><br>
<input type="checkbox" name="colored_nouns_yesno" value="colored_nouns_yes" <?php echo ($_SESSION['color_nounsyesno']) ? "checked" : "";?>> 
Substantive <input type="text" name="nouns_color" value="<?php echo $_SESSION['color_nouns']; ?>"><br>
<input type="checkbox" name="colored_beginnings_yesno" value="colored_beginnings_yes" <?php echo ($_SESSION['color_beginningsyesno']) ? "checked" : "";?>> 
Satzanfänge<input type="text" name="beginnings_color" value="<?php echo $_SESSION['color_beginnings']; ?>"><br>
<input type="checkbox" name="background_color_yesno" value="background_color_yes" <?php echo ($_SESSION['color_backgroundyesno']) ? "checked" : "";?>> 
Hintergrund<input type="text" name="background_color" value="<?php echo $_SESSION['color_background']; ?>"><br>
</td></tr>
<tr><td>Hilfslinien</td></tr>
<tr><td>
Farbe <input type="text" name="auxiliary_lines_color" value="<?php echo $_SESSION['auxiliary_color_general']; ?>"> 
Dicke <input type="text" name="auxiliary_lines_thickness" value="<?php echo $_SESSION['auxiliary_thickness_general']; ?>"> 
<br>
<input type="checkbox" name="baseline_yesno" value="baseline_yes" <?php echo ($_SESSION['auxiliary_baselineyesno']) ? "checked" : "";?>> 
Grundlinie: Dicke <input type="text" name="baseline_thickness" value="<?php echo $_SESSION['auxiliary_baseline_thickness']; ?>"> 
Farbe <input type="text" name="baseline_color" value="<?php echo $_SESSION['auxiliary_baseline_color']; ?>">
<br>
<input type="checkbox" name="1st_2nd_upper_line_yesno" value="1st_2nd_upper_line_yes" <?php echo ($_SESSION['auxiliary_upper12yesno']) ? "checked" : "";?>> 
1./2. Oberstufe: Dicke <input type="text" name="1st_2nd_upperline_thickness" value="<?php echo $_SESSION['auxiliary_upper12_thickness']; ?>"> 
Farbe <input type="text" name="1st_2nd_upperline_color" value="<?php echo $_SESSION['auxiliary_upper12_color']; ?>">
<br>
<input type="checkbox" name="lower_line_yesno" value="lower_line_yes" <?php echo ($_SESSION['auxiliary_loweryesno']) ? "checked" : "";?>> 
Unterstufe: Dicke <input type="text" name="lower_line_thickness" value="<?php echo $_SESSION['auxiliary_lower_thickness']; ?>"> 
Farbe <input type="text" name="lower_line_color" value="<?php echo $_SESSION['auxiliary_lower_color']; ?>">
<br>
<input type="checkbox" name="3rd_upper_line_yesno" value="3rd_upper_line_yes" <?php echo ($_SESSION['auxiliary_upper3yesno']) ? "checked" : "";?>> 
3. Oberstufe: Dicke <input type="text" name="3rd_upper_line_color" value="<?php echo $_SESSION['auxiliary_upper3_thickness']; ?>"> 
Farbe <input type="text" name="3rd_upper_line_color" value="<?php echo $_SESSION['auxiliary_upper3_color']; ?>">
<br>

</td></tr>
<tr><td>Ausgabe</td></tr>
<tr><td>
<input type="radio" name="output_format" value="output_metaform" <?php echo ($_SESSION['output_format'] === "metaform") ? "checked" : "";?>> Metaform<br>
<input type="radio" name="output_format" value="output_debug" <?php echo ($_SESSION['output_format'] === "debug") ? "checked" : "";?>> Debug<br>
<input type="radio" name="output_format" value="output_inline" <?php echo ($_SESSION['output_format'] === "inline") ? "checked" : "";?>> Inline 
<input type="checkbox" name="inline_text_tags" value="inline_text_tags_yes" <?php echo ($_SESSION['output_texttagsyesno']) ? "checked" : "";?>> Langschrift-Tags<br>
<input type="radio" name="output_format" value="output_layout" <?php echo ($_SESSION['output_format']) === "layout" ? "checked" : "";?>> Layout<br>
Breite: <input type="text" name="layout_width" value="<?php echo $_SESSION['output_width']; ?>"> 
Höhe: <input type="text" name="layout_height" value="<?php echo $_SESSION['output_height']; ?>"><br>
Stil: <input type="radio" value="style_align_left" <?php echo ($_SESSION['output_style'] === "align_left") ? "checked" : "";?>> 
Flattersatz <input type="radio" value="style_block" <?php echo ($_SESSION['output_texttagsyesno'] == "block") ? "checked" : "";?>> Blocksatz
<br>
<input type="checkbox" value="page_numbers_yes" <?php echo ($_SESSION['output_page_numbersyesno']) ? "checked" : "";?>> 
Seitenzahlen: Beginn <input type="text" name="page_numbers_start_number" value="<?php echo $_SESSION['output_page_start_value']; ?>"> 
auf Seite <input type="text" name="page_numbers_start_page" value="<?php echo $_SESSION['output_page_start_at']; ?>">

</td></tr>
<tr><td>Markieren</td></tr>
<tr><td>
Wortliste: <input type="text" name="marker_word_list" size="80" value="<?php echo $_SESSION['mark_wordlist']; ?>"><br>
Marker: <input type="text" name="marker_style_list" size="80" value="<?php echo $_SESSION['mark_formatlist']; ?>">
</td></tr>
</table>

<input type="submit" value="berechnen">
</form>
</div>
</center>
<?php require "vsteno_template_bottom.php"; ?>