<?php

require_once "constants.php";

function set_session_variables() {
    global $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide;
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
    $_SESSION['token_thickness'] = 0.8; // factor
    $_SESSION['token_shadow'] = 1;
    $_SESSION['token_distance_none'] = $horizontal_distance_none;
    $_SESSION['token_distance_narrow'] = $horizontal_distance_narrow;
    $_SESSION['token_distance_wide'] = $horizontal_distance_wide;
    $_SESSION['token_style_type'] = "solid"; // solid line
    $_SESSION['token_style_custom_value'] = "1,1"; 
    $_SESSION['color_text_in_general'] = "black";
    $_SESSION['color_nounsyesno'] = false;
    $_SESSION['color_nouns'] = "black";
    $_SESSION['color_beginningsyesno'] = false;
    $_SESSION['color_beginnings'] = "black";
    $_SESSION['color_backgroundyesno'] = false;
    $_SESSION['color_background'] = "white";
    $_SESSION['auxiliary_color_general'] = "rgb(120,0,0)";
    $_SESSION['auxiliary_thickness_general'] = 0.2;
    $_SESSION['auxiliary_baselineyesno'] = true;
    $_SESSION['auxiliary_upper12yesno'] = true;
    $_SESSION['auxiliary_loweryesno'] = true;
    $_SESSION['auxiliary_upper3yesno'] = true;
    $_SESSION['auxiliary_general_color'] = "rgb(120,0,0)";
    $_SESSION['auxiliary_baseline_color'] = "rgb(120,0,0)";
    $_SESSION['auxiliary_upper12_color'] = "rgb(120,0,0)";
    $_SESSION['auxiliary_lower_color'] = "rgb(120,0,0)";
    $_SESSION['auxiliary_upper3_color'] = "rgb(120,0,0)";
    $_SESSION['auxiliary_general_thickness'] = 0.2;
    $_SESSION['auxiliary_baseline_thickness'] = 0.2;
    $_SESSION['auxiliary_upper12_thickness'] = 0.2;
    $_SESSION['auxiliary_lower_thickness'] = 0.2;
    $_SESSION['auxiliary_upper3_thickness'] = 0.2;
    $_SESSION['output_format'] = "inline";
    $_SESSION['output_texttagsyesno'] = yes;
    $_SESSION['output_width'] = 0;
    $_SESSION['output_height'] = 0;
    $_SESSION['output_style'] = "align_left";
    $_SESSION['output_page_numberyesno'] = false;
    $_SESSION['output_page_start_value'] = "";
    $_SESSION['output_page_start_at'] = "";
    $_SESSION['mark_wordlist'] = "";
    $_SESSION['mark_formatlist'] = "";
    return;
}

    session_start();
    if (!isset($_SESSION['initialized'])) {
        set_session_variables();
    }
?>