<?php

// contains functions to process inline-options in VSTENO
// Inline-Options = options that can be indicated inside the text that has to be transcribed (normal text or metaform)
//
// Format for Inline-Option-Tags:   <@variable="value">
//
// variable = session-variable $_SESSION['$variable'] that will be set
// value = value that will be written to $_SESSION['$variable']
//
// Option Tags can be combined with words (in two places called pre_tag and post_tag) or stand alone (i.e. without a word), 
// e.g.:
//
// combined:                <@color_text_in_general="red">Achtung!<@color_text_in_general="rgb(0,0,0)">
//                          +------- pre_tag ------------+         +------------ post_tag ------------+

// stands alone:            <@color_text_in_general="red">
//                          +-------- pre_tag -----------+
// 
// If the tag stands alone, it will be called pre_tag.
//
// Several tags can stand together, e.g.:
//
//                          <@color_text_in_general="red"><@token_thickness="1.0">Achtung!<@color_text_in_general="rgb(0,0,0)">
//                          +-------------- 2 x pre_tag -------------------------+         +------------ post_tag ------------+
//
// Important: It is NOT possible to have additional tags inside the words (as this would affect the operation of the 
// parser), therefore <@color_text_in_general="red">Achtung<@color_text_in_general="rgb(0,0,0)">! (with the exlamation
// mark at the end is NOT VALID).
//
// For commodity " and ' can be used without any difference.
//
// Security: Input value from form is already escaped, but no validation is made whetcher the variable is a valid
// $_SESSION[]-element. Therefore, it probably is possible to create a buffer overflow setting a high amount of (non used,
// existing, non defined) session-variables.
// SOLUTION: Check if variable has been set before
//
// Extension: normal HTML-tags (like "<br>" can be combined with inline-option-tags, e.g:
//
// <@token_color="red"><br>Achtung!<br><@token_color="black">
//

require_once "errors_and_warnings.php";

// The HTML-tags can stand at any position (before or after inline tags or mixed), but not inside words (like inline-option-tags)
// HTML-tags will be inserted into HTML-page without modifications when stenograms are generated. Any HTML-tag is allowed (it's up to the user
// to provide correct and working tags).
$whitelist_variables = " original_text_format title_yesno title_text title_size title_color introduction_yesno introduction_text introduction_size introduction_color token_size ";
$whitelist_variables .= "token_type token_thickness token_inclination token_shadow token_distance_none token_distance_narrow token_distance_wide token_style_type token_style_custom_value ";
$whitelist_variables .= " token_color color_nounsyesno color_nouns color_beginningsyesno color_beginnings color_backgroundyesno color_background auxiliary_color_general ";
$whitelist_variables .= " auxiliary_thickness_general auxiliary_baselineyesno auxiliary_upper12yesno auxiliary_loweryesno auxiliary_upper3yesno auxiliary_baseline_color ";
$whitelist_variables .= " auxiliary_upper12_color auxiliary_lower_color auxiliary_upper3_color auxiliary_baseline_thickness auxiliary_upper12_thickness auxiliary_lower_thickness ";
$whitelist_variables .= " auxiliary_upper3_thickness output_texttagsyesno output_width output_height output_style output_page_numberyesno output_page_start_value ";
$whitelist_variables .= " output_page_start_at mark_wordlist distance_words space_before_word style_nouns style_beginnings baseline_style upper12_style upper3_style lower_style ";
$whitelist_variables .= " auxiliary_style_general left_margin right_margin top_margin bottom_margin num_system_lines baseline show_margins show_distances svgtext_size ";
$whitelist_variables .= " actual_model model_custom_or_standard prefixes_list stems_list suffixes_list hyphenate_yesno composed_words_yesno language_hyphenator language_hunspell ";
$whitelist_variables .= " spacer_token_combinations spacer_vowel_groups spacer_rules_list spacer_autoinsert license release_notes copyright_footer language_espeak analysis_type ";
$whitelist_variables .= " phonetic_alphabet filter_out_prefixes_yesno filter_out_suffixes_yesno filter_out_words_yesno affixes_yesno phonetics_yesno block_list filter_list ";
$whitelist_variables .= " model_version model_date required_version rendering_middleline_yesno rendering_polygon_yesno rendering_polygon_color rendering_vector_type rendering_sharp_modelling ";
$whitelist_variables .= " rendering_polygon_opacity rendering_intermediateshadowpoints_yesno rendering_lineoverpass_yesno layouted_originalt_text_yesno layouted_original_text_position ";
$whitelist_variables .= " layouted_original_text_size layouted_original_text_delta layouted_original_text_font layouted_original_text_wrap layouted_original_text_filter_brackets ";
$whitelist_variables .= " layouted_original_text_filter_dashes phonetics_transcription_list phonetics_transcription_array phonetics_single_char_yesno ";
$whitelist_variables .= " rendering_lineoverpass_start_factor rendering_lineoverpass_start_factor font_borrwo_yesno font_borrow_model_name ";

function GetWordSetPreAndPostTags( $text ) {
        global /*$inline_options_pretags, $inline_options_posttags,*/ $html_pretags, $html_posttags, $combined_pretags, $combined_posttags;
       // echo "GetWordSetPreAndPostTags(): text: $text<br>";
       
        // preg_match( "/^<@.+>(?=[^<])/", $text, $pre_tag);                      // suppose regex is greedy // old version with @ (= no html-tags)
        // preg_match( "/(?<=[^>])<@.+>$/", $text, $post_tag);                    // idem
        // preg_match( "/^<@.+>$/", $text, $only_tags );
        $text = htmlspecialchars_decode( $text );                                 // work with unescaped text
        $text = preg_replace( "/[<]\//", "<!", $text );                               // replace / in </.>-html-tags in order to avoid escaping problems => is this necessary?
        $pre_tags_pattern = "^<.+>(?=[^<])";
        $post_tags_pattern = "(?<=[^>])<.+>$";
        $general_tags_pattern = "^<.+>$";
        preg_match( "/$pre_tags_pattern/", $text, $pre_tag);                      // suppose regex is greedy // include html-tags also (search for <, not only <@)
        preg_match( "/$post_tags_pattern/", $text, $post_tag);                    // idem
        preg_match( "/$general_tags_pattern/", $text, $only_tags );
        //echo "<br>Inside GetPreAndPostTags:<br>- pre_tag: " . htmlspecialchars($pre_tag[0]) . "<br>- post_tag: " . $post_tag[0] . "<br>- only_tags: " . htmlspecialchars($only_tags[0]) . "<br><br>";
        $pre = $pre_tag[0];
        $post = $post_tag[0];
        $pre_regex = preg_quote( $pre );                                        // found patterns must be escaped before being
        $post_regex = preg_quote( $post );                                      // reused in preg_match() !!!
        preg_match( "/(?<=$pre_regex).+(?=$post_regex)/", $text, $word_array );
        $word = $word_array[0];    
        //echo "<br>Inside GetPreAndPostTags:<br>- word: " . htmlspecialchars($word) . "<br><br>";
        // convert escaped html chars back to normal text
        $only_tags[0] = preg_replace( "/\!/", "/", $only_tags[0] );              // convert / back in </.>-html-tags => is this necessary?
        $pre = preg_replace( "/\!/", "/", $pre );
        $post = preg_replace( "/!/", "/", $post);                                // "/" <=> chr(47)
        // write result to global variables (instead of passing them through several functions
        $combined_pretags = $pre;
        $combined_posttags = $post;
        
        if ((mb_strlen( $pre ) >0) && (mb_strlen( $post ) > 0) && (mb_strlen( $only_tags[0]) > 0) && (mb_strlen( $word ) > 0)) return $word;
        elseif ((mb_strlen( $only_tags[0] ) > 0) && (mb_strlen( $word ) > 0)) {
            $combined_pretags = $only_tags[0];
            $combined_posttags = "";
            return "";
        } else return $word;
      //  echo "GetWordSetPreAndPostTags(): word: $word<br>";
       
        return $word;
}
/*
function GetPreAndPostTags( $text ) {
        global $html_pretags, $html_posttags, $combined_pretags, $combined_posttags;
        // preg_match( "/^<@.+>(?=[^<])/", $text, $pre_tag);                      // suppose regex is greedy // old version with @ (= no html-tags)
        // preg_match( "/(?<=[^>])<@.+>$/", $text, $post_tag);                    // idem
        // preg_match( "/^<@.+>$/", $text, $only_tags );
        $text = htmlspecialchars_decode( $text );                                 // work with unescaped text
        $text = preg_replace( "/\//", "!", $text );                               // replace / in </.>-html-tags in order to avoid escaping problems => is this necessary?
        $pre_tags_pattern = "^<.+>(?=[^<])";
        $post_tags_pattern = "(?<=[^>])<.+>$";
        $general_tags_pattern = "^<.+>$";
        preg_match( "/$pre_tags_pattern/", $text, $pre_tag);                      // suppose regex is greedy // include html-tags also (search for <, not only <@)
        preg_match( "/$post_tags_pattern/", $text, $post_tag);                    // idem
        preg_match( "/$general_tags_pattern/", $text, $only_tags );
        //echo "<br>Inside GetPreAndPostTags:<br>- pre_tag: " . htmlspecialchars($pre_tag[0]) . "<br>- post_tag: " . $post_tag[0] . "<br>- only_tags: " . htmlspecialchars($only_tags[0]) . "<br><br>";
        $pre = $pre_tag[0];
        $post = $post_tag[0];
        $pre_regex = preg_quote( $pre );                                        // found patterns must be escaped before being
        $post_regex = preg_quote( $post );                                      // reused in preg_match() !!!
        preg_match( "/(?<=$pre_regex).+(?=$post_regex)/", $text, $word_array );
        $word = $word_array[0];    
        //echo "<br>Inside GetPreAndPostTags:<br>- word: " . htmlspecialchars($word) . "<br><br>";
        // convert escaped html chars back to normal text
        $only_tags[0] = preg_replace( "/\!/", "/", $only_tags[0] );              // convert / back in </.>-html-tags => is this necessary?
        $pre = preg_replace( "/\!/", "/", $pre );
        $post = preg_replace( "/!/", "/", $post);                                // "/" <=> chr(47)
        // write result to global variables (instead of passing them through several functions
        $combined_pretags = $pre;
        $combined_posttags = $post;
        if ((mb_strlen( $pre ) >0) && (mb_strlen( $post ) > 0) && (mb_strlen( $only_tags[0]) > 0) && (mb_strlen( $word ) > 0)) return array( $pre, $word, $post );
        elseif ((mb_strlen( $only_tags[0] ) > 0) && (mb_strlen( $word ) > 0)) return array( $only_tags[0], "", "" );
        else return array($pre, $word, $post);
}
*/

function GetTagVariableAndValue( $tag ) {
        preg_match( "/(?<=<@).*?(?==)/", $tag, $variable);                      
        preg_match( "/(?<==).*?(?=>)/", $tag, $temp );
        //preg_match( "/(?<=^[\"\']).*?(?=[\"\']$)/", $temp[0], $value );         // strip out " and '
        preg_match( "/(?<=^).*?(?=$)/", $temp[0], $value );         // strip out " and '
        $result = preg_replace( "/[\"\']/", "", $value[0] );
        //return array( $variable[0], $value[0] );
        return array( $variable[0], $result );

}

// INCREDIBLE: Pattern "/<@.*?[>]/": "?" . ">", must be written as ?[>] otherwhise PHP-Parser thinks it's the end of PHP-code (even inside comments) ... ?!?!
function ParseAndSetInlineOptions( $tags ) {
       global $global_error_string, $whitelist_variables;
       //echo "ParseAndSetInlineOptions(): tags: $tags<br>";
       // preg_match_all( "/<@.*?[>]/", $tags, $matches );                        // .*? makes expression non greedy // old version with @ (= no html-tags)
       preg_match_all( "/<[^>]+[>]/", $tags, $matches );                         // .*? makes expression non greedy; parse all tags of both types (inline- and html-)
       //var_dump($matches);
       $html_tag_list = "";
       foreach( $matches[0] as $match ) {
            if (preg_match( "/<@/", $match ) == 0) {$html_tag_list .= $match;    // match is html-tag
            //$esc_html_tag_list = htmlspecialchars( $html_tag_list );
            //echo "html-tag-list: $esc_html_tag_list<br>";
            }
            else {                                                              // match is inline-tag => set values
                list( $variable, $value ) = GetTagVariableAndValue($match);
                // echo "Match: " . htmlspecialchars($match) . " => Variable: #$variable# Value: #$value#<br>";
                CheckAndSetSessionVariable( $variable, $value );
            }
       }
       //$esc_html_tag_list = htmlspecialchars( $html_tag_list );
       //echo "html-tag-list: $esc_html_tag_list<br>";
       return $html_tag_list;
}

function CheckAndSetSessionVariable( $variable, $value ) {
    global $whitelist_variables, $global_error_string;
    if (isset($_SESSION[$variable])) {  // check if variable has been set before (= exists)
                    if (mb_strpos($whitelist_variables, " $variable ") === false) {
                        AddError("ERROR: you are not allowed to set variable '" . htmlspecialchars($variable) . "' to '" . htmlspecialchars($value) . "'!");
                    } else {
                        //echo "session[$variable] = $value<br>";
                        switch ($value) {
                            case "true" : $_SESSION[$variable] = true; break;
                            case "false" : $_SESSION[$variable] = false; break;
                            case "yes" : $_SESSION[$variable] = true; break;
                            case "no" : $_SESSION[$variable] = false; break;
                            default : $_SESSION[$variable] = $value; break;
                        }
                        // adjust phonetics array necessary
                        if ($variable === "phonetics_transcription_list") {
                            // 2nd paramter true = create array (instead of stdobj)
                            $_SESSION['phonetics_transcription_array'] = json_decode( "{" . $_SESSION['phonetics_transcription_list'] . "}", true);
                        }
                    } 
    } else {
            AddError("ERROR: session-variable '" . htmlspecialchars($variable) . "' doesn't exist.");
    }
    
}

?>