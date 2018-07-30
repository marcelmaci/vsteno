<?php

require_once "constants.php";

function InitializeSessionVariables() {
    global $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide, $distance_words;
    // set standard values for use in session
    $_SESSION['initialized'] = true;
    $_SESSION['original_text_format'] = "normal";
    $_SESSION['original_text_content'] = "";
    $_SESSION['title_yesno'] = true;
    $_SESSION['title_text'] = "VSTENO";
    $_SESSION['title_size'] = 1; // h1
    $_SESSION['title_color'] = "black";
    $_SESSION['introduction_yesno'] = true;
    $_SESSION['introduction_text'] = "Vector Steno Tool with Enhanced Notational Options";
    $_SESSION['introduction_size'] = 1; // h1
    $_SESSION['introduction_color'] = "black";
    $_SESSION['token_size'] = 1.5; // factor
    $_SESSION['token_type'] = "shorthand";  // defines if tokens are shown as "shorthand", "handwriting", "svgtext", "htmltext" (normal text with browser fonts) - can only be set via inline-tag
    $_SESSION['token_thickness'] = 0.8; // factor
    $_SESSION['token_inclination'] = 60; // degree
    $_SESSION['token_shadow'] = 1;
    $_SESSION['token_distance_none'] = $horizontal_distance_none;
    $_SESSION['token_distance_narrow'] = $horizontal_distance_narrow;
    $_SESSION['token_distance_wide'] = $horizontal_distance_wide;
    $_SESSION['token_style_type'] = "solid"; // solid line
    $_SESSION['token_style_custom_value'] = ""; 
    $_SESSION['token_color'] = "black";
    $_SESSION['color_nounsyesno'] = false;
    $_SESSION['color_nouns'] = "black";
    $_SESSION['color_beginningsyesno'] = false;
    $_SESSION['color_beginnings'] = "black";
    $_SESSION['color_backgroundyesno'] = false;
    $_SESSION['color_background'] = "white";
    $_SESSION['auxiliary_color_general'] = "rgb(120,0,0)";
    $_SESSION['auxiliary_thickness_general'] = 0.1;
    $_SESSION['auxiliary_baselineyesno'] = true;
    $_SESSION['auxiliary_upper12yesno'] = true;
    $_SESSION['auxiliary_loweryesno'] = true;
    $_SESSION['auxiliary_upper3yesno'] = true;
    $_SESSION['auxiliary_baseline_color'] = "rgb(120,0,0)";
    $_SESSION['auxiliary_upper12_color'] = "rgb(120,0,0)";
    $_SESSION['auxiliary_lower_color'] = "rgb(120,0,0)";
    $_SESSION['auxiliary_upper3_color'] = "rgb(120,0,0)";
    $_SESSION['auxiliary_baseline_thickness'] = 0.2;
    $_SESSION['auxiliary_upper12_thickness'] = 0.1;
    $_SESSION['auxiliary_lower_thickness'] = 0.1;
    $_SESSION['auxiliary_upper3_thickness'] = 0.1;
    $_SESSION['output_integratedyesno'] = true;
    $_SESSION['output_format'] = "inline";
    $_SESSION['output_without_button_yesno'] = false;
    $_SESSION['output_texttagsyesno'] = yes;
    $_SESSION['output_width'] = 0;
    $_SESSION['output_height'] = 0;
    $_SESSION['output_style'] = "align_left";
    $_SESSION['output_page_numberyesno'] = false;
    $_SESSION['output_page_start_value'] = "";
    $_SESSION['output_page_start_at'] = "";
    $_SESSION['mark_wordlist'] = "";
    
    // later additions
    $_SESSION['distance_words'] = $distance_words;
    $_SESSION['style_nouns'] = "";
    $_SESSION['style_beginnings'] = "";
    $_SESSION['baseline_style'] = "";
    $_SESSION['upper12_style'] = "";
    $_SESSION['upper3_style'] = "";
    $_SESSION['lower_style'] = "";
    $_SESSION['auxiliary_style_general'] = "";
    $_SESSION['return_address'] = "input.php";
}

function CopyFormToSessionVariablesMaxi() {
    global $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide;
    $_SESSION['original_text_format'] = htmlspecialchars($_POST['text_format_metayesno']);
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
    $_SESSION['color_nounsyesno'] = (htmlspecialchars($_POST['colored_nouns_yesno']) === "colored_nouns_yes") ? true : false;
    $_SESSION['color_nouns'] = htmlspecialchars($_POST['nouns_color']);
    $_SESSION['color_beginningsyesno'] = (htmlspecialchars($_POST['colored_beginnings_yesno']) === "colored_beginnings_yes") ? true : false;
    $_SESSION['color_beginnings'] = htmlspecialchars($_POST['beginnings_color']);
    $_SESSION['color_backgroundyesno'] = (htmlspecialchars($_POST['background_color_yesno']) === "background_color_yes") ? true : false;
    $_SESSION['color_background'] = htmlspecialchars($_POST['background_color']);
    $_SESSION['auxiliary_color_general'] = htmlspecialchars($_POST['auxiliary_lines_color']);
    $_SESSION['auxiliary_thickness_general'] = htmlspecialchars($_POST['auxiliary_lines_thickness']);
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
    $_SESSION['output_page_numberyesno'] = (htmlspecialchars($_POST['page_numbers_yesno']) === "page_numbers_yes") ? true : false;
    $_SESSION['output_page_start_value'] = htmlspecialchars($_POST['page_numbers_start_number']);
    $_SESSION['output_page_start_at'] = htmlspecialchars($_POST['page_numbers_start_page']);
    $_SESSION['mark_wordlist'] = htmlspecialchars($_POST['marker_word_list']);
    
    // later additions
    $_SESSION['distance_words'] = htmlspecialchars($_POST['distance_words']);
    $distance_words = $_SESSION['distance_words'];                                  // maybe not a good idea to save this values in two different variables (session and global var in constants.php) ...
    $_SESSION['style_nouns'] = htmlspecialchars($_POST['nouns_style']);
    $_SESSION['style_beginnings'] = htmlspecialchars($_POST['beginnings_style']);
    $_SESSION['baseline_style'] = htmlspecialchars($_POST['baseline_style']);
    $_SESSION['upper12_style'] = htmlspecialchars($_POST['upper12_style']);
    $_SESSION['upper3_style'] = htmlspecialchars($_POST['upper3_style']);
    $_SESSION['lower_style'] = htmlspecialchars($_POST['lower_style']);
    $_SESSION['auxiliary_style_general'] = htmlspecialchars($_POST['auxiliary_lines_style']);
    
}

function CopyFormToSessionVariablesMini() {
    global $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide;
    $_SESSION['original_text_format'] = htmlspecialchars($_POST['text_format_metayesno']);
    $_SESSION['original_text_content'] = htmlspecialchars($_POST['original_text']);
}

function CopyFormToSessionVariables() {
    if ($_SESSION['return_address'] === "input.php") CopyFormToSessionVariablesMaxi();
    else CopyFormToSessionVariablesMini();
}


    session_start();
    if (!isset($_SESSION['initialized'])) {
        InitializeSessionVariables();
    }
?>