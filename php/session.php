<?php

require_once "constants.php";
require_once "import_model.php";

function InitializeSessionVariables() {
    global $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide, $distance_words, $space_before_word,
    $left_margin, $right_margin, $top_margin, $bottom_margin, $num_system_lines, $standard_height, $default_model, $standard_models_list;
    // set standard values for use in session
    $_SESSION['standard_models_list'] = $standard_models_list;
    $_SESSION['license'] = "";
    $_SESSION['release_notes'] = "";
    $_SESSION['copyrigt_footer'] = "";
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
    $_SESSION['token_size'] = 1.6; // factor
    $_SESSION['token_type'] = "shorthand";  // defines if tokens are shown as "shorthand", "handwriting", "svgtext", "htmltext" (normal text with browser fonts) - can only be set via inline-tag
    $_SESSION['token_thickness'] = 1.25; // factor
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
    $_SESSION['output_page_number_yesno'] = true;
    $_SESSION['output_page_number_first'] = 1;
    $_SESSION['output_page_number_start'] = 1;
    $_SESSION['output_page_number_posx'] = 330;
    $_SESSION['output_page_number_posy'] = 985;
    $_SESSION['output_page_number_color'] = 'black';
    
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
    $_SESSION['selected_std_model'] = $default_model;
    $_SESSION['last_updated_model'] = "";
    $_SESSION['model_standard_or_custom'] = "standard";
    $_SESSION['rules_count'] = null;
    $_SESSION['language_hunspell'] = "de_CH"; // do not initialize these variables, so that they can be initialized by model!
    $_SESSION['language_hyphenator'] = "de"; // correction: can be done here, since toggle_model now actualizes session variables for specific model
    $_SESSION['language_espeak'] = "de";
    $_SESSION['phonetical_alphabet'] = "espeak";
    // spacer variables
    $_SESSION['spacer_autoinsert'] = false;
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
    
    // layouted svg
    $_SESSION['left_margin'] = htmlspecialchars($_POST['left_margin']);
    $_SESSION['right_margin'] = htmlspecialchars($_POST['right_margin']);
    $_SESSION['top_margin'] = htmlspecialchars($_POST['top_margin']);
    $_SESSION['bottom_margin'] = htmlspecialchars($_POST['bottom_margin']);
    $_SESSION['num_system_lines'] = htmlspecialchars($_POST['num_system_lines']);
    $_SESSION['baseline'] = htmlspecialchars($_POST['baseline']);
    $_SESSION['show_margins'] = (htmlspecialchars($_POST['show_margins']) === "yes") ? true : false;
    $_SESSION['show_distances'] = (htmlspecialchars($_POST['show_distances']) === "yes") ? true : false;
    $_SESSION['svgtext_size'] = 30;         // svgtext size in px
    
    //$_SESSION['model_custom_or_standard'] = (htmlspecialchars($_POST['model_to_load']) === GetDBUserModelName()) ? "custom" : "standard";
    //$_SESSION['actual_model'] = ($_SESSION['model_custom_or_standard'] === "standard") ? $_POST['model_to_load'] : getDBUserModelName();
    // additional session variable necessary to keep track of selected std model (in input form)
    // will be used by toggle_model
    //$_SESSION['selected_std_model'] = ($_SESSION['model_custom_or_standard'] === "standard") ? $_POST['model_to_load'] : $_SESSION['selected_std_model'];
    $_SESSION['language_hunspell'] = htmlspecialchars($_POST['language_hunspell']);
    $_SESSION['language_hyphenator'] = htmlspecialchars($_POST['language_hyphenator']);
    $_SESSION['language_espeak'] = htmlspecialchars($_POST['language_espeak']);
    $_SESSION['phonetical_alphabet'] = htmlspecialchars($_POST['phonetical_alphabet']);
    $_SESSION['spacer_autoinsert'] = ($_POST['spacer_autoinsert'] === "yes") ? true : false;
/*
    echo "model_custom_or_standard: " . $_SESSION['model_custom_or_standard'] . "<br>";
    echo "actual_model: " . $_SESSION['actual_model'] . "<br>";
    echo "selected_std_model: " . $_SESSION['selected_std_model'] . "<br>";
    echo "last_updated_model: " . $_SESSION['last_updated_model'] . "<br>";
*/
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
    // actualize header session values if model is loaded for the first time
    $actual_model = $_SESSION['actual_model'];
    if ($actual_model !== $_SESSION['last_updated_model']) {
            $model_name = ($_SESSION['model_standard_or_custom'] === "custom") ? getDBUserModelName() : $_SESSION['selected_std_model'] ;
            $model = $_SESSION['model_standard_or_custom'];
            $last_updated = $_SESSION['last_updated_model'];
            $output_format = $_SESSION['output_format'];
            // reset all session variables to raw value
            $old_text = $_SESSION['original_text_content']; // backup old text
            $selected_std_model = $_SESSION['selected_std_model'];
            InitializeSessionVariables(); // initialize with raw values
            $_SESSION['original_text_content'] = $old_text; // restore old text
            $_SESSION['selected_std_model'] = $selected_std_model;
            $_SESSION['model_standard_or_custom'] = $model;
            $_SESSION['actual_model'] = $model_name;
            $_SESSION['output_format'] = $output_format;
            
            $text_to_parse = LoadModelFromDatabase($_SESSION['actual_model']);
            //echo ">$text_to_parse<";
            $output = StripOutComments($text_to_parse);
            $output = StripOutTabsAndNewlines($output);
            $header_section = GetSection($output, "header");
            $session_subsection = GetSubSection($header_section, "session");
            //echo "update session for model (1st run for $actual_model - last updated: " . $last_updated . ")<br>";
            ImportSession(); // initialize with values specified by model
            $_SESSION['last_updated_model'] = $actual_model;
    }

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