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

function GetPreAndPostTags( $text ) {
        preg_match( "/^<@.+>(?=[^<])/", $text, $pre_tag);                      // suppose regex is greedy
        preg_match( "/(?<=[^>])<@.+>$/", $text, $post_tag);                    // idem
        preg_match( "/^<@.+>$/", $text, $only_tags );
        $pre = $pre_tag[0];
        $post = $post_tag[0];
        $pre_regex = preg_quote( $pre );                                        // found patterns must be escaped before being
        $post_regex = preg_quote( $post );                                      // reused in preg_match() !!!
        preg_match( "/(?<=$pre_regex).+(?=$post_regex)/", $text, $word_array );
        $word = $word_array[0];       
        preg_match( "/^<@.+>$/", $word, $only_tags );
        //$pre = htmlspecialchars_decode( $pre );                                // convert escaped html chars back to normal text
        //$post = htmlspecialchars_decode( $post );                                // convert escaped html chars back to normal text
        if (mb_strlen( $only_tags[0] ) > 0) return array( $only_tags[0], "", "" );
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

function ParseAndSetInlineOptions( $tags ) {
       preg_match_all( "/<@.*?>/", $tags, $matches );                           // .*? makes expression non greedy
       foreach( $matches[0] as $match ) {
            list( $variable, $value ) = GetTagVariableAndValue($match);
            //echo "Match: $match => Variable: $variable Value: $value<br>";
            if (isset($_SESSION[$variable])) $_SESSION[$variable] = $value;     // check if variable has been set before
       }
}

?>