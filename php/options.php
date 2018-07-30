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
// The HTML-tags can stand at any position (before or after inline tags or mixed), but not inside words (like inline-option-tags)
// HTML-tags will be inserted into HTML-page without modifications when stenograms are generated. Any HTML-tag is allowed (it's up to the user
// to provide correct and working tags).

function GetPreAndPostTags( $text ) {
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
        if ((mb_strlen( $pre ) >0) && (mb_strlen( $post ) > 0) && (mb_strlen( $only_tags[0]) > 0) && (mb_strlen( $word ) > 0)) return array( $pre, $word, $post );
        elseif ((mb_strlen( $only_tags[0] ) > 0) && (mb_strlen( $word ) > 0)) return array( $only_tags[0], "", "" );
        else return array($pre, $word, $post);
}

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
                //echo "Match: " . htmlspecialchars($match) . " => Variable: $variable Value: $value<br>";
                if (isset($_SESSION[$variable])) $_SESSION[$variable] = $value;     // check if variable has been set before
            }
       }
       //$esc_html_tag_list = htmlspecialchars( $html_tag_list );
       //echo "html-tag-list: $esc_html_tag_list<br>";
       return $html_tag_list;
}


?>