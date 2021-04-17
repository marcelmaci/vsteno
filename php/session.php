<?php

require_once "constants.php";
require_once "import_model.php";

// instead of hardcoded function ResetRestrictedSessionVariables() in import_model.php
// use this array with key => value
global $restricted_session_variables_list;
$restricted_session_variables_list = array( 
    "prefixes_list" => "",
    "suffixes_list" => "",
    "block_list" => "",
    "filter_list" => "",
    "analysis_type" => "none",
    "spacer_token_combinations" => "",
    "spacer_vowel_groups" => "",
    "spacer_rules_list" => "",
    "license" => "",
    "release_notes" => "",
    "copyright_footer" => "",
    "model_version" => "",
    "model_date" => "",
    "font_borrow_yesno" => false,
    "model_use_native_yesno" => false,
 //   "font_borrow_model_name" => "", // must not necessarily be resetted
 //   "font_load_from_file" => false
    // options (must be enumerated)
    "model_option0_text" => "",
    "model_option0_yesno" => false,
    "model_option1_text" => "",
    "model_option1_yesno" => false,
    "model_option2_text" => "",
    "model_option2_yesno" => false,
    "model_option3_text" => "",
    "model_option3_yesno" => false,
    "model_option4_text" => "",
    "model_option4_yesno" => false,
    "model_option5_text" => "",
    "model_option5_yesno" => false,
    "model_option6_text" => "",
    "model_option6_yesno" => false,
    "model_option7_text" => "",
    "model_option7_yesno" => false,
    "model_option8_text" => "",
    "model_option8_yesno" => false,
    "model_option9_text" => "",
    "model_option9_yesno" => false,
);

function InitializeSessionVariables() {
    global $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide, $distance_words, $space_before_word,
    $left_margin, $right_margin, $top_margin, $bottom_margin, $num_system_lines, $standard_height, $default_model, $standard_models_list, 
    $native_extensions;
    // native extensions
    $_SESSION['native_extensions'] = $native_extensions;
    $_SESSION['model_use_native_yesno'] = false;
    // set standard values for use in session
    $_SESSION['captcha_processing'] = false;
    $_SESSION['debug_show_points_yesno'] = false;
    $_SESSION['debug_show_grid_yesno'] = false;
    $_SESSION['interpolated_yesno'] = false;
    $_SESSION['interpolated_iterations'] = 2;
    $_SESSION['standard_models_list'] = $standard_models_list;
    $_SESSION['license'] = "";
    $_SESSION['release_notes'] = "";
    $_SESSION['copyright_footer'] = "";
    $_SESSION['statistics_rules'] = 0;  // use these variables for statistics
    $_SESSION['statistics_tokens'] = 0;
    $_SESSION['statistics_base'] = 0;
    $_SESSION['statistics_combined'] = 0;
    $_SESSION['statistics_shifted'] = 0;
    $_SESSION['statistics_subsections'] = 0;
    $_SESSION['initialized'] = true;
    $_SESSION['original_text_format'] = "normal";
    $_SESSION['original_text_ascii_yesno'] = true;
    $_SESSION['original_text_content'] = "";
    $_SESSION['title_yesno'] = true;
    $_SESSION['title_text'] = "VSTENO";
    $_SESSION['title_size'] = 1; // h1
    $_SESSION['title_color'] = "black";
    $_SESSION['introduction_yesno'] = true;
    $_SESSION['introduction_text'] = "Vector Shorthand Tool with Enhanced Notational Options";
    $_SESSION['introduction_size'] = 1; // h1
    $_SESSION['introduction_color'] = "black";
    $_SESSION['token_size'] = 1.43; // factor
    $_SESSION['token_type'] = "shorthand";  // defines if tokens are shown as "shorthand", "handwriting", "svgtext", "htmltext" (normal text with browser fonts) - can only be set via inline-tag
    $_SESSION['token_thickness'] = 1.35; // factor
    $_SESSION['token_inclination'] = 60; // degree
    $_SESSION['token_shadow'] = 1;
    $_SESSION['token_distance_none'] = $horizontal_distance_none;
    $_SESSION['token_distance_narrow'] = $horizontal_distance_narrow;
    $_SESSION['token_distance_wide'] = $horizontal_distance_wide;
    $_SESSION['token_style_type'] = "solid"; // solid line
    $_SESSION['token_style_custom_value'] = ""; 
    $_SESSION['token_color'] = "black";
    $_SESSION['analysis_type'] = "selected";
    $_SESSION['hyphenate_yesno'] = true;
    $_SESSION['phonetics_yesno'] = false;
    $_SESSION['affixes_yesno'] = true;
    $_SESSION['filter_out_prefixes_yesno'] = false;
    $_SESSION['filter_out_suffixes_yesno'] = false;
    $_SESSION['filter_out_words_yesno'] = false;
    $_SESSION['composed_words_yesno'] = true;
    $_SESSION['composed_words_separate'] = 99; // don't separate words by default (leave that to model rules)
    $_SESSION['composed_words_glue'] = 0;
    $_SESSION['block_list'] = "";
    $_SESSION['filter_list'] = "";
    $_SESSION['prefixes_list'] = ""; //" ge zu un ver mit ent auf ab an "; // test if analyze_word_linguistically can be used to make essential (= only partial, the important ones!) analysis of prefixes
    $_SESSION['stems_list'] = ""; //" gangen "; // irregular stem list (in combination with prefixes list) => can be entered by users in maxi form (later) 
    $_SESSION['suffixes_list'] = ""; //" heit hei-t heits keit keiten keits lich liche lichen liches "; // a problem never is as simple as it seems at first glance ... For example: Gelegenheit => prefix ge- can't be recognized (because "legenheit" is not a valid word) => maybe this can be solved analyzing suffixes as well (Ge+le-gen=heit)?!
    $_SESSION['color_nounsyesno'] = false;      // heiten => "hei" and "ten" are recognized as valid words => therefore correct that in corrector ...
    $_SESSION['color_nouns'] = "black";         // use regex for prefix/suffix list ... otherwhise the lists will be long!
    $_SESSION['color_beginningsyesno'] = false;
    $_SESSION['color_beginnings'] = "black";
    $_SESSION['color_backgroundyesno'] = false;
    $_SESSION['color_background'] = "white";
    $_SESSION['auxiliary_color_general'] = "rgb(0,0,0)";
    $_SESSION['auxiliary_thickness_general'] = 0.1;
    $_SESSION['auxiliary_lines_margin_left'] = 0; // 0 <=> lines have page width
    $_SESSION['auxiliary_lines_margin_right'] = 15; // >0 <=> lines leave blank space of width margin
    $_SESSION['auxiliary_baselineyesno'] = true;
    $_SESSION['auxiliary_upper12yesno'] = false;
    $_SESSION['auxiliary_loweryesno'] = false;
    $_SESSION['auxiliary_upper3yesno'] = false;
    $_SESSION['auxiliary_baseline_color'] = "black";
    $_SESSION['auxiliary_upper12_color'] = "gray";
    $_SESSION['auxiliary_lower_color'] = "gray";
    $_SESSION['auxiliary_upper3_color'] = "gray";
    $_SESSION['auxiliary_baseline_thickness'] = 0.5;
    $_SESSION['auxiliary_upper12_thickness'] = 0.25;
    $_SESSION['auxiliary_lower_thickness'] = 0.25;
    $_SESSION['auxiliary_upper3_thickness'] = 0.25;
    $_SESSION['output_integratedyesno'] = true;
    $_SESSION['output_format'] = "inline";
    $_SESSION['output_without_button_yesno'] = false;
    $_SESSION['output_texttagsyesno'] = yes;
    $_SESSION['output_width'] = 660;
    $_SESSION['output_height'] = 1000;
    $_SESSION['output_style'] = "align_left_right";
    $_SESSION['output_line_number_yesno'] = true;
    $_SESSION['output_line_number_step'] = 5;
    $_SESSION['output_line_number_posx'] = $_SESSION['output_width']-2;
    $_SESSION['output_line_number_deltay'] = 2;
    $_SESSION['output_line_number_color'] = 'black';
    $_SESSION['output_page_number_yesno'] = false;
    $_SESSION['output_page_number_first'] = 1;
    $_SESSION['output_page_number_start'] = 1;
    $_SESSION['output_page_number_posx'] = 330;
    $_SESSION['output_page_number_posy'] = 985;
    $_SESSION['output_page_number_color'] = 'black';
    $_SESSION['page_number_formatting_yesno'] = false;
    $_SESSION['page_number_format'] = "numeric";
    $_SESSION['page_number_format_left'] = "";
    $_SESSION['page_number_format_right'] = "";
    
    $_SESSION['mark_wordlist'] = "";
    
    // later additions
    $_SESSION['distance_words'] = $distance_words;
    $_SESSION['space_before_word'] = $space_before_word;
    
    $_SESSION['style_nouns'] = "";
    $_SESSION['style_beginnings'] = "";
    $_SESSION['baseline_style'] = "1,2";
    $_SESSION['upper12_style'] = "1,1";
    $_SESSION['upper3_style'] = "1,1";
    $_SESSION['lower_style'] = "1,1";
    $_SESSION['auxiliary_style_general'] = "";
    $_SESSION['return_address'] = "input.php";
    
    // no margins for auxiliary lines
    $_SESSION['baseline_nomargin_yesno'] = false;
    $_SESSION['upper12_nomargin_yesno'] = false;
    $_SESSION['upper3_nomargin_yesno'] = false;
    $_SESSION['lower_nomargin_yesno'] = false;
    
    // layouted svg
    $_SESSION['left_margin'] = $left_margin;
    $_SESSION['right_margin'] = $right_margin;
    $_SESSION['top_margin'] = $top_margin;
    $_SESSION['bottom_margin'] = $bottom_margin;
    $_SESSION['num_system_lines'] = $num_system_lines;
    $_SESSION['baseline'] = 4;                                  // start at 4th system line for first shorthand text line in layouted svg
    $_SESSION['layouted_correct_word_width'] = false;
    $_SESSION['show_margins'] = false;
    $_SESSION['show_distances'] = false;
    $_SESSION['svgtext_size'] = 30;         // svgtext size in px
    if (!isset($_SESSION['user_logged_in'])) {       // don't logout user when resetting session variables
        $_SESSION['user_logged_in'] = false;
        $_SESSION['user_username'] = "";
        $_SESSION['user_privilege'] = 0;
        $_SESSION['user_id'] = 0;
    }
    $_SESSION['actual_model'] = $default_model;
    $_SESSION['model_version'] = "";
    $_SESSION['required_version'] = "";
    $_SESSION['model_se_revision'] = 0;
    $_SESSION['model_date'] = "";
    $_SESSION['selected_std_model'] = $default_model;
    $_SESSION['last_updated_model'] = "";
    $_SESSION['model_standard_or_custom'] = "standard";
    $_SESSION['rules_count'] = null;
    $_SESSION['language_hunspell'] = "de_CH"; // do not initialize these variables, so that they can be initialized by model!
    $_SESSION['language_hyphenator'] = "de"; 
    $_SESSION['language_espeak'] = "de";
    $_SESSION['phonetic_alphabet'] = "espeak";
    // phonetics variables
    $_SESSION['phonetics_transcription_list'] = "";
    $_SESSION['phonetics_transcription_array'] = "";
    $_SESSION['phonetics_single_char_yesno'] = false;
    $_SESSION['phonetics_acronyms_yesno'] = false; // don't transcribe acronyms by default
    $_SESSION['phonetics_acronyms_lowercase_yesno'] = yes; // transform acronyms to lower case by default
    // shared font
    $_SESSION['font_borrow_yesno'] = true;
    $_SESSION['font_borrow_model_name'] = "GESSBAS";
    $_SESSION['font_importable_yesno'] = true;
    $_SESSION['font_exportable_yesno'] = false;
    $_SESSION['font_load_from_file_yesno'] = true;
    
    // options for book layouts
    // IMPORTANT: variable names are exactly the other way around
    // i.e. "even" means "odd"
    $_SESSION['layouted_book_yesno'] = false;
    $_SESSION['layouted_book_deltax_odd'] = 0; 
    $_SESSION['layouted_book_deltax_even'] = 20; 
    $_SESSION['layouted_book_lines_odd_yesno'] = true; 
    $_SESSION['layouted_book_lines_posx_odd'] = 658;   
    $_SESSION['layouted_book_lines_even_yesno'] = false; 
    $_SESSION['layouted_book_lines_posx_even'] = 0; 
   
    // books: page and text dimensions
    // page
    $_SESSION['layouted_book_page_dimension_yesno'] = false;
    $_SESSION['layouted_book_page_dimension_x1'] = 0;
    $_SESSION['layouted_book_page_dimension_y1'] = 0;
    $_SESSION['layouted_book_page_dimension_x2'] = $_SESSION['output_width'];
    $_SESSION['layouted_book_page_dimension_y2'] = $_SESSION['output_height'];
    $_SESSION['layouted_book_page_dimension_color'] = "black";
/*
    // text
    $_SESSION['layouted_book_text_dimension_yesno'] = false;
    $_SESSION['layouted_book_text_dimension_x1'] = 0;
    $_SESSION['layouted_book_text_dimension_y1'] = 0;
    $_SESSION['layouted_book_text_dimension_x2'] = 0;
    $_SESSION['layouted_book_text_dimension_y2'] = 0;
    $_SESSION['layouted_book_text_dimension_color'] = "black";
*/    
    // spacer variables
    $_SESSION['spacer_autoinsert'] = false;
    $_SESSION['spacer_vowel_groups'] = "";
    $_SESSION['spacer_rules_list'] = "";
    $_SESSION['spacer_token_combinations'] = "";
    
    // use legacy configuration for www.steno.ch
    $_SESSION['rendering_middleline_yesno'] = true;
    $_SESSION['rendering_polygon_yesno'] = false;
    $_SESSION['rendering_polygon_color'] = $_SESSION['token_color'];
    $_SESSION['rendering_vector_type'] = "middleangle";
    $_SESSION['rendering_sharp_modelling'] = "horizontal";
    $_SESSION['rendering_polygon_opacity'] = "1";
    $_SESSION['rendering_intermediateshadowpoints_yesno'] = false;
    $_SESSION['rendering_lineoverpass_yesno'] = true;
    $_SESSION['rendering_lineoverpass_start_factor'] = 0.3;
    $_SESSION['rendering_lineoverpass_end_factor'] = 0.3;

    $_SESSION['handwriting_marker'] = "1"; // chose this as standard handwriting font


/*
    $_SESSION['rendering_middleline_yesno'] = false;
    $_SESSION['rendering_polygon_yesno'] = true;
    $_SESSION['rendering_polygon_color'] = $_SESSION['token_color'];
    $_SESSION['rendering_vector_type'] = "middleangle";
    $_SESSION['rendering_sharp_modelling'] = "horizontal";
    $_SESSION['rendering_polygon_opacity'] = "1";
    $_SESSION['rendering_intermediateshadowpoints_yesno'] = false;
*/    
    $_SESSION['layouted_original_text_yesno'] = false;
    $_SESSION['layouted_original_text_position'] = before;
    $_SESSION['layouted_original_text_size'] = 23;
    $_SESSION['layouted_original_text_delta'] = 1.4;    
    $_SESSION['layouted_original_text_font'] = "sans-serif";    
    $_SESSION['layouted_original_text_wrap'] = "auto";    
    $_SESSION['layouted_original_text_filter_brackets'] = true;    
    $_SESSION['layouted_original_text_filter_dashes'] = true;    

    // titlebreak and breaks on top of page: 
    $_SESSION['titlebreak_minimum_lines_at_end'] = 4; // empty line + title + empty line + 1st line of following paragraph
    $_SESSION['titlebreak_number_of_breaks_before'] = 1;
    $_SESSION['page_top_avoid_breaks_before_br_yesno'] = true;
    $_SESSION['page_top_avoid_breaks_before_p_yesno'] = true;
    
    // options: reserve 10 variables
    for ($i=0; $i<=9; $i++) {
        $_SESSION["model_option$i" . "_text"] = "";
        $_SESSION["model_option$i" . "_yesno"] = false;
    }
}


function CopyFormToSessionVariablesMaxi() {
    global $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide; // probably superfluous .. ?!

if ($_POST['token_size'] != "") {    
    // ok, this is an ugly bugfix: when editor is called in SE1-mode, php somehow executes this code (don't know why and it shouldn't)
    // the thing is that the $_POST-variable is empty (the editor isn't called via a form, so again: no idea why this happens!) and
    // therefore all session variables get overwritten. Since I can't disable the (erroneous) call for the moment, the only workaround
    // is to avoid execution of the code below by checking if one of the variables - $_POST['token_size'] in this case - is empty ...
    
    $_SESSION['original_text_format'] = htmlspecialchars($_POST['text_format_metayesno']);
    $_SESSION['original_text_ascii_yesno'] = ($_POST['text_format_ascii_yesno'] === "ascii") ? true : false;
    $_SESSION['original_text_content'] = htmlspecialchars($_POST['original_text']);
    $_SESSION['title_yesno'] = (htmlspecialchars($_POST['title_yesno']) === "title_yes") ? true : false;
    $_SESSION['title_text'] = htmlspecialchars($_POST['title_text']);
    $_SESSION['title_size'] = (htmlspecialchars($_POST['title_size']) === "size_h1") ? 1 : 2; 
    $_SESSION['title_color'] = htmlspecialchars($_POST['title_color']);
    $_SESSION['introduction_yesno'] = (htmlspecialchars($_POST['introduction_yesno']) === "introduction_yes") ? true : false;
    $_SESSION['introduction_text'] = htmlspecialchars($_POST['introduction_text']);
    $_SESSION['introduction_size'] = htmlspecialchars($_POST['introduction_size']); 
    $_SESSION['introduction_color'] = htmlspecialchars($_POST['introduction_color']);
    $_SESSION['token_size'] = htmlspecialchars($_POST['token_size']); 
    $_SESSION['token_thickness'] = htmlspecialchars($_POST['token_thickness']); 
    $_SESSION['token_inclination'] = htmlspecialchars($_POST['token_inclination']);
    $_SESSION['token_shadow'] = htmlspecialchars($_POST['token_shadow']);
    $_SESSION['token_distance_none'] = htmlspecialchars($_POST['distance_none']);
    $_SESSION['token_distance_narrow'] = htmlspecialchars($_POST['distance_narrow']);
    $_SESSION['token_distance_wide'] = htmlspecialchars($_POST['distance_wide']);
    $_SESSION['token_style_type'] = htmlspecialchars($_POST['token_line_style']); 
    $_SESSION['token_style_custom_value'] = htmlspecialchars($_POST['token_line_style_custom_value']); 
    $_SESSION['token_color'] = htmlspecialchars($_POST['token_color']);
    $_SESSION['analysis_type'] = htmlspecialchars($_POST['analysis_type']);
    $_SESSION['hyphenate_yesno'] = (htmlspecialchars($_POST['hyphenate_yesno']) === "hyphenate_yes") ? true : false;
    $_SESSION['phonetics_yesno'] = (htmlspecialchars($_POST['phonetics_yesno']) === "yes") ? true : false;
    $_SESSION['filter_out_prefixes_yesno'] = (htmlspecialchars($_POST['filter_out_prefixes_yesno']) === "yes") ? true : false;
    $_SESSION['filter_out_suffixes_yesno'] = (htmlspecialchars($_POST['filter_out_suffixes_yesno']) === "yes") ? true : false;
    $_SESSION['filter_out_words_yesno'] = (htmlspecialchars($_POST['filter_out_words_yesno']) === "yes") ? true : false;
    $_SESSION['affixes_yesno'] = (htmlspecialchars($_POST['affixes_yesno']) === "yes") ? true : false;
    $_SESSION['composed_words_yesno'] = (htmlspecialchars($_POST['composed_words_yesno']) === "composed_words_yes") ? true : false;
    // disable these two paramaters => can only be used via inline-options in the future (because dependant on model)
    //$_SESSION['composed_words_separate'] = htmlspecialchars($_POST['composed_words_separate']);
    //$_SESSION['composed_words_glue'] = htmlspecialchars($_POST['composed_words_glue']);
    $_SESSION['color_nounsyesno'] = (htmlspecialchars($_POST['colored_nouns_yesno']) === "colored_nouns_yes") ? true : false;
    $_SESSION['color_nouns'] = htmlspecialchars($_POST['nouns_color']);
    $_SESSION['color_beginningsyesno'] = (htmlspecialchars($_POST['colored_beginnings_yesno']) === "colored_beginnings_yes") ? true : false;
    $_SESSION['color_beginnings'] = htmlspecialchars($_POST['beginnings_color']);
    $_SESSION['color_backgroundyesno'] = (htmlspecialchars($_POST['background_color_yesno']) === "background_color_yes") ? true : false;
    $_SESSION['color_background'] = htmlspecialchars($_POST['background_color']);
    $_SESSION['auxiliary_color_general'] = htmlspecialchars($_POST['auxiliary_lines_color']);
    $_SESSION['auxiliary_thickness_general'] = htmlspecialchars($_POST['auxiliary_lines_thickness']);
    $_SESSION['auxiliary_lines_margin_left'] = htmlspecialchars($_POST['auxiliary_lines_margin_left']); 
    $_SESSION['auxiliary_lines_margin_right'] = htmlspecialchars($_POST['auxiliary_lines_margin_right']);
    $_SESSION['auxiliary_baselineyesno'] = (htmlspecialchars($_POST['baseline_yesno']) === "baseline_yes") ? true : false;
    $_SESSION['auxiliary_upper12yesno'] = (htmlspecialchars($_POST['upper12_yesno']) === "upper12_yes") ? true : false;
    $_SESSION['auxiliary_loweryesno'] = (htmlspecialchars($_POST['lower_yesno']) === "lower_yes") ? true : false;
    $_SESSION['auxiliary_upper3yesno'] = (htmlspecialchars($_POST['upper3_yesno']) === "upper3_yes") ? true : false;
    $_SESSION['auxiliary_baseline_color'] = htmlspecialchars($_POST['baseline_color']);
    $_SESSION['auxiliary_upper12_color'] = htmlspecialchars($_POST['upper12_color']);
    $_SESSION['auxiliary_lower_color'] = htmlspecialchars($_POST['lower_color']);
    $_SESSION['auxiliary_upper3_color'] = htmlspecialchars($_POST['upper3_color']);
    $_SESSION['auxiliary_baseline_thickness'] = htmlspecialchars($_POST['baseline_thickness']);
    $_SESSION['auxiliary_upper12_thickness'] = htmlspecialchars($_POST['upper12_thickness']);
    $_SESSION['auxiliary_lower_thickness'] = htmlspecialchars($_POST['lower_thickness']);
    $_SESSION['auxiliary_upper3_thickness'] = htmlspecialchars($_POST['upper3_thickness']);
    $_SESSION['output_integratedyesno'] = (htmlspecialchars($_POST['output_integratedyesno']) === "output_integrated") ? true : false;
    $_SESSION['output_without_button_yesno'] = (htmlspecialchars($_POST['output_without_button_yesno']) === "output_without_button_yes") ? true : false;
    $_SESSION['output_format'] = htmlspecialchars($_POST['output_format']);
    $_SESSION['output_texttagsyesno'] = (htmlspecialchars($_POST['output_text_tags']) === "text_tags_yes") ? true : false;
    $_SESSION['output_width'] = htmlspecialchars($_POST['layout_width']);
    $_SESSION['output_height'] = htmlspecialchars($_POST['layout_height']);
    $_SESSION['output_style'] = htmlspecialchars($_POST['layout_style']);
    $_SESSION['output_line_number_yesno'] = (htmlspecialchars($_POST['line_number_yesno']) === "yes") ? true : false;
    $_SESSION['output_line_number_step'] = htmlspecialchars($_POST['line_number_step']);
    $_SESSION['output_line_number_posx'] = htmlspecialchars($_POST['line_number_posx']);
    $_SESSION['output_line_number_deltay'] = htmlspecialchars($_POST['line_number_deltay']);
    $_SESSION['output_line_number_color'] = htmlspecialchars($_POST['line_number_color']);
    $_SESSION['output_page_number_yesno'] = (htmlspecialchars($_POST['page_number_yesno']) === "yes") ? true : false;
    $_SESSION['output_page_number_first'] = htmlspecialchars($_POST['page_number_first']);
    $_SESSION['output_page_number_start'] = htmlspecialchars($_POST['page_number_start']);
    $_SESSION['output_page_number_posx'] = htmlspecialchars($_POST['page_number_posx']);
    $_SESSION['output_page_number_posy'] = htmlspecialchars($_POST['page_number_posy']);
    $_SESSION['output_page_number_color'] = htmlspecialchars($_POST['page_number_color']);
    
    // page number formatting
    $_SESSION['page_number_formatting_yesno'] = (htmlspecialchars($_POST['page_number_formatting_yesno']) === "yes") ? true : false;
    $_SESSION['page_number_format'] = htmlspecialchars($_POST['page_number_format']);
    // if ($_SESSION['page_number_formatting'] !== true) $_SESSION['page_number_format'] = "roman_upper";
    $_SESSION['page_number_format_left'] = htmlspecialchars($_POST['page_number_format_left']);
    $_SESSION['page_number_format_right'] = htmlspecialchars($_POST['page_number_format_right']);
    
    $_SESSION['debug_show_points_yesno'] = (htmlspecialchars($_POST['debug_show_points']) === "debug_show_points_yes") ? true : false;
    $_SESSION['debug_show_grid_yesno'] = (htmlspecialchars($_POST['debug_show_grid']) === "yes") ? true : false;
    $_SESSION['interpolated_yesno'] = (htmlspecialchars($_POST['interpolated_yesno']) === "yes") ? true : false;
    $_SESSION['interpolated_iterations'] = htmlspecialchars($_POST['interpolated_iterations']);
    
    $_SESSION['mark_wordlist'] = htmlspecialchars($_POST['marker_word_list']);
    
    // later additions
    $_SESSION['distance_words'] = htmlspecialchars($_POST['distance_words']);
    $distance_words = $_SESSION['distance_words'];                                  // maybe not a good idea to save this values in two different variables (session and global var in constants.php) ...
    // form field for space_before_words has to be inserted here (do that later)
    $_SESSION['style_nouns'] = htmlspecialchars($_POST['nouns_style']);
    $_SESSION['style_beginnings'] = htmlspecialchars($_POST['beginnings_style']);
    $_SESSION['baseline_style'] = htmlspecialchars($_POST['baseline_style']);
    $_SESSION['upper12_style'] = htmlspecialchars($_POST['upper12_style']);
    $_SESSION['upper3_style'] = htmlspecialchars($_POST['upper3_style']);
    $_SESSION['lower_style'] = htmlspecialchars($_POST['lower_style']);
    $_SESSION['auxiliary_style_general'] = htmlspecialchars($_POST['auxiliary_lines_style']);
    
    // margins for auxiliary lines
    $_SESSION['baseline_nomargin_yesno'] = (htmlspecialchars($_POST['baseline_nomargin']) === "nomargin") ? true : false;
    $_SESSION['upper12_nomargin_yesno'] = (htmlspecialchars($_POST['upper12_nomargin']) === "nomargin") ? true : false;
    $_SESSION['upper3_nomargin_yesno'] = (htmlspecialchars($_POST['upper3_nomargin']) === "nomargin") ? true : false;
    $_SESSION['lower_nomargin_yesno'] = (htmlspecialchars($_POST['lower_nomargin']) === "nomargin") ? true : false;
    
    // options for book layouts
    // left/right shifting and line numbers
    $_SESSION['layouted_book_yesno'] = (htmlspecialchars($_POST['layouted_book_yesno']) === "yes") ? true : false;
    $_SESSION['layouted_book_deltax_odd'] = htmlspecialchars($_POST['layouted_book_deltax_odd']);
    $_SESSION['layouted_book_deltax_even'] = htmlspecialchars($_POST['layouted_book_deltax_even']);
    $_SESSION['layouted_book_lines_odd_yesno'] = (htmlspecialchars($_POST['layouted_book_lines_odd_yesno']) === "yes") ? true : false;
    $_SESSION['layouted_book_lines_posx_odd'] = htmlspecialchars($_POST['layouted_book_lines_posx_odd']);
    $_SESSION['layouted_book_lines_even_yesno'] = (htmlspecialchars($_POST['layouted_book_lines_even_yesno']) === "yes") ? true : false;
    $_SESSION['layouted_book_lines_posx_even'] = htmlspecialchars($_POST['layouted_book_lines_posx_even']);
    
    // books: page and text dimensions
    // page
    $_SESSION['layouted_book_page_dimension_yesno'] = (htmlspecialchars($_POST['layouted_book_page_dimension_yesno']) === "yes") ? true : false;
    $_SESSION['layouted_book_page_dimension_x1'] = htmlspecialchars($_POST['layouted_book_page_dimension_x1']);
    $_SESSION['layouted_book_page_dimension_y1'] = htmlspecialchars($_POST['layouted_book_page_dimension_y1']);
    $_SESSION['layouted_book_page_dimension_x2'] = htmlspecialchars($_POST['layouted_book_page_dimension_x2']);
    $_SESSION['layouted_book_page_dimension_y2'] = htmlspecialchars($_POST['layouted_book_page_dimension_y2']);
    $_SESSION['layouted_book_page_dimension_color'] = htmlspecialchars($_POST['layouted_book_page_dimension_color']);
/*    
    // books: text
    $_SESSION['layouted_book_text_dimension_yesno'] = (htmlspecialchars($_POST['layouted_book_text_dimension_yesno']) === "yes") ? true : false;
    $_SESSION['layouted_book_text_dimension_x1'] = htmlspecialchars($_POST['layouted_book_text_dimension_x1']);
    $_SESSION['layouted_book_text_dimension_y1'] = htmlspecialchars($_POST['layouted_book_text_dimension_y1']);
    $_SESSION['layouted_book_text_dimension_x2'] = htmlspecialchars($_POST['layouted_book_text_dimension_x2']);
    $_SESSION['layouted_book_text_dimension_y2'] = htmlspecialchars($_POST['layouted_book_text_dimension_y2']);
    $_SESSION['layouted_book_text_dimension_color'] = htmlspecialchars($_POST['layouted_book_text_dimension_color']);
*/    
    // layouted svg
    $_SESSION['left_margin'] = htmlspecialchars($_POST['left_margin']);
    $_SESSION['right_margin'] = htmlspecialchars($_POST['right_margin']);
    $_SESSION['top_margin'] = htmlspecialchars($_POST['top_margin']);
    $_SESSION['bottom_margin'] = htmlspecialchars($_POST['bottom_margin']);
    $_SESSION['num_system_lines'] = htmlspecialchars($_POST['num_system_lines']);
    $_SESSION['baseline'] = htmlspecialchars($_POST['baseline']);
    $_SESSION['layouted_correct_word_width'] = (htmlspecialchars($_POST['layouted_correct_word_width']) === "yes") ? true : false;
    $_SESSION['show_margins'] = (htmlspecialchars($_POST['show_margins']) === "yes") ? true : false;
    $_SESSION['show_distances'] = (htmlspecialchars($_POST['show_distances']) === "yes") ? true : false;
    $_SESSION['svgtext_size'] = 30;         // svgtext size in px
    
    $_SESSION['language_hunspell'] = htmlspecialchars($_POST['language_hunspell']);
    $_SESSION['language_hyphenator'] = htmlspecialchars($_POST['language_hyphenator']);
    
    // echo "language_espeak:<br>SESSION: " . $_SESSION['language_espeak'] . "<br>POST: " . $_POST['language_espeak'] . "<br>";
    // there's an annoying bug: when vsteno is newly started, changing the espeak-language and other analyzer options has no effect
    // meaning: old values are used for calculation and to show input form again after the calculation
    // the options are only changed the 2nd time, i.e. when you change the settings again an perform a calculation ...
    // no idea why that happens (and it is completely strange ...) - everything works and both the session- and post-variables
    // are exactly the same in the 1st and 2nd run ... ?!
    // Ok, found the bug: problem was that when data.php loaded a new model, the session variable last_updated_model wasn't set to the
    // new model. Consequence: when making the first calculation, the program reseted all session variables (erasing post variables) and
    // setting only then last_updated_model correctly.
    // The whole concept of last_updated_model is a relict from the time, model selection was possible via input form. This approach has
    // been abandoned (too complicated to keep all variables correctly updated) and therefore the whole stuff is obsolete and should be
    // eliminated in the next code cleaning ...
    
    $_SESSION['language_espeak'] = htmlspecialchars($_POST['language_espeak']);
    $_SESSION['phonetic_alphabet'] = htmlspecialchars($_POST['phonetic_alphabet']);
    $_SESSION['phonetics_single_char_yesno'] = ($_POST['phonetics_single_char_yesno'] === "yes") ? true : false;;
    $_SESSION['phonetics_acronyms_yesno'] = ($_POST['phonetics_acronyms_yesno'] === "yes") ? true : false;;
    $_SESSION['phonetics_acronyms_lowercase_yesno'] = ($_POST['phonetics_acronyms_lowercase_yesno'] === "yes") ? true : false;;
    $_SESSION['spacer_autoinsert'] = ($_POST['spacer_autoinsert'] === "yes") ? true : false;
    $_SESSION['rendering_middleline_yesno'] = ($_POST['rendering_middleline_yesno'] === "yes") ? true : false;
    $_SESSION['rendering_polygon_yesno'] = ($_POST['rendering_polygon_yesno'] === "yes") ? true : false;
    $_SESSION['rendering_polygon_color'] = htmlspecialchars($_POST['rendering_polygon_color']);
    $_SESSION['rendering_vector_type'] = htmlspecialchars($_POST['rendering_vector_type']);
    $_SESSION['rendering_sharp_modelling'] = htmlspecialchars($_POST['rendering_sharp_modelling']);
    $_SESSION['rendering_polygon_opacity'] = $_POST['rendering_polygon_opacity'];
    $_SESSION['rendering_intermediateshadowpoints_yesno'] = ($_POST['rendering_intermediateshadowpoints_yesno'] === "yes") ? true : false;
    $_SESSION['rendering_lineoverpass_yesno'] = ($_POST['rendering_lineoverpass_yesno'] === "yes") ? true : false;
    $_SESSION['rendering_lineoverpass_start_factor'] = htmlspecialchars($_POST['rendering_lineoverpass_start_factor']);
    $_SESSION['rendering_lineoverpass_end_factor'] = htmlspecialchars($_POST['rendering_lineoverpass_end_factor']);
        
    $_SESSION['layouted_original_text_yesno'] = ($_POST['layouted_original_text_yesno'] === "yes") ? true : false;
    $_SESSION['layouted_original_text_position'] = ($_POST['layouted_original_text_position'] === "before") ? "before" : "after";    
    $_SESSION['layouted_original_text_size'] = $_POST['layouted_original_text_size'];
    $_SESSION['layouted_original_text_delta'] = $_POST['layouted_original_text_delta'];    
    $_SESSION['layouted_original_text_font'] = $_POST['layouted_original_text_font'];    
    $_SESSION['layouted_original_text_wrap'] = $_POST['layouted_original_text_wrap'];    
    $_SESSION['layouted_original_text_filter_brackets'] = ($_POST['layouted_original_text_filter_brackets'] === "yes") ? true : false;
    $_SESSION['layouted_original_text_filter_dashes'] = ($_POST['layouted_original_text_filter_dashes'] === "yes") ? true : false;
    
    // shared font
    $_SESSION['font_borrow_yesno'] = ($_POST['font_borrow_yesno'] === "yes") ? true : false;
    $_SESSION['font_borrow_model_name'] = htmlspecialchars($_POST['font_borrow_model_name']);
    $_SESSION['font_load_from_file_yesno'] = ($_POST['font_load_from_file_yesno'] === "yes") ? true : false;
   
    $_SESSION['handwriting_marker'] = htmlspecialchars($_POST['handwriting_marker']);
    
    // titlebreak and breaks on top of page
    $_SESSION['titlebreak_minimum_lines_at_end'] = htmlspecialchars($_POST['titlebreak_minimum_lines_at_end']);
    $_SESSION['titlebreak_number_of_breaks_before'] = htmlspecialchars($_POST['titlebreak_number_of_breaks_before']);
    $_SESSION['page_top_avoid_breaks_before_br_yesno'] = ($_POST['page_top_avoid_breaks_before_br_yesno'] === "yes") ? true : false;
    $_SESSION['page_top_avoid_breaks_before_p_yesno'] = ($_POST['page_top_avoid_breaks_before_p_yesno'] === "yes") ? true : false;

    
/*
    echo "model_custom_or_standard: " . $_SESSION['model_custom_or_standard'] . "<br>";
    echo "actual_model: " . $_SESSION['actual_model'] . "<br>";
    echo "selected_std_model: " . $_SESSION['selected_std_model'] . "<br>";
    echo "last_updated_model: " . $_SESSION['last_updated_model'] . "<br>";
*/
    // options for optional rules
    for ($i=0; $i<=9; $i++) {
        $option_yesno = "model_option" . $i . "_yesno";
        $option_text = "model_option" . $i . "_text";
        if (mb_strlen($_SESSION[$option_text])>0) {
            // option that needs a yesno variable to be set
            if ((isset($_POST[$option_yesno])) && ($_POST[$option_yesno] === "yes")) $_SESSION[$option_yesno] = true;
            else  $_SESSION[$option_yesno] = false;
        }
    }
    

}
}

function CopyFormToSessionVariablesMini() {
    global $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide;
    $_SESSION['original_text_format'] = htmlspecialchars($_POST['text_format_metayesno']);
    $_SESSION['original_text_content'] = htmlspecialchars($_POST['original_text']);
}

function CopyFormToSessionVariables() {
    global $session_subsection; // necessary, because ImportSession() works with this global variable
    if ($_SESSION['return_address'] === "input.php") CopyFormToSessionVariablesMaxi();
    else CopyFormToSessionVariablesMini();
}

// backup all session variables except some specific ones
// namely (no backup):
// - original_text_content (too much text)
// - all user* variables (too risky to mess up with those)
// this function is needed if the engine has to be used for other purposes than the main calculations
function push_session() {
    global $session_backup; // use this variable to backup session (session must be restored before script finishes)
    //echo "backup session<br>";
    foreach ($_SESSION as $key => $value) {
        if (($key !== "original_text_content") && (mb_substr($key,0,4) !== "user")) {
            //echo "session_backup($key) = $value<br>";
            $session_backup[$key] = $value;
        }

    }
}

// restore original session variables from backup in $session_backup
function pop_session() {
    global $session_backup;
    //echo "restore session<br>";
    foreach ($session_backup as $key => $value) {
        //echo "session($key) = $value<br>";
        $_SESSION[$key] = $value;
    }
}

// main
    session_start();
    if (!isset($_SESSION['initialized'])) {
        InitializeSessionVariables(); // initialize with raw values
        $text_to_parse = LoadModelFromDatabase($_SESSION['actual_model']);
        $output = StripOutComments($text_to_parse);
        $output = StripOutTabsAndNewlines($output);
        $header_section = GetSection($output, "header");
        $session_subsection = GetSubSection($header_section, "session");
        ImportSession(); // initialize with values specified by model
    }
?>