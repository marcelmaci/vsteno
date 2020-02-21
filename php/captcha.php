<?php
require_once "constants.php";
require_once "data.php";
require_once "engine.php";
require_once "parser.php";

function GetShorthandCaptcha( $length ) {
// captcha completely broken
// use simplified version always
return $GetSimplifiedshorthandCaptcha($length);
/*
        $vowels = array( "a", "e", "i", "o", "u", "au", "ei"); // don't use umlaut for simplicity: "ö", "ü", "ä" ); // don't use äu / eu (ambigous)
        $beginning = array( "b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z", 
            "br", "bl", "dr", "dl", "fr", "gr", "gl", "kr", "kl", "pl", "pr", "rr", "tr", "tl", "sch", "schm", "schw", "zw");
        $consonants = array( "b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "z", 
            "br", "bl", "dr", "dl", "fr", "gr", "gl", "kr", "kl", "pl", "pr", "rr", "tr", "tl", "sch", "schm", "schw", "zw", 
            "tt", "pp", "ff", "nn", "mm", "ll");
        for ($i = 0; $i < $length; $i++) {
                $captcha .= ($i == 0) ? $beginning[rand(0, count($beginning)-1)] : $consonants[rand(0, count($consonants)-1)];
                $captcha .= $vowels[rand(0, count($vowels)-1)];
        }
        $_SESSION['captcha'] = $captcha;
        //$token_list = NormalText2TokenList( $captcha );
        //$svg_captcha = TokenList2SVG( $token_list, 60, 0.8, 1.6, "black", "", "" );
        CreateCombinedTokens(); // ????
        CreateShiftedTokens();  //// ???? necessary?
       
        //$_SESSION['captcha_processing'] = true; // bugfix (you could also call it a "workaround" ...)
        $svg_captcha = SingleWord2SVG( $captcha, 60, 0.8, 1.6, "black", "", "" );
        //$_SESSION['captcha_processing'] = false;
        
        //echo "test: " . htmlspecialchars($svg_captcha);
        
        //return "SHORTHAND CAPTCHA BROKEN - USE:<br>$captcha";
        return $svg_captcha;
 */     
}

function GetSimplifiedShorthandCaptcha( $length ) {
// bug with captcha is even worse: no combined tokens can be generated ...
// therefore us another workaround: simplify captcha (no combined tokens)

        $vowels = array( "a", "e", "i", "o", "u", "au", "ei"); // don't use umlaut for simplicity: "ö", "ü", "ä" ); // don't use äu / eu (ambigous)
        $beginning = array( "b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z", 
            "rr", "sch", "schm", "schw", "zw");
        $consonants = array( "b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "z", 
            "rr", "sch", "schm", "schw", "zw", 
            "tt", "pp", "ff", "nn", "mm", "ll");
        for ($i = 0; $i < $length; $i++) {
                $captcha .= ($i == 0) ? $beginning[rand(0, count($beginning)-1)] : $consonants[rand(0, count($consonants)-1)];
                $captcha .= $vowels[rand(0, count($vowels)-1)];
        }
        $_SESSION['captcha'] = $captcha;
        //$token_list = NormalText2TokenList( $captcha );
        //$svg_captcha = TokenList2SVG( $token_list, 60, 0.8, 1.6, "black", "", "" );
        CreateCombinedTokens(); // ????
        CreateShiftedTokens();  //// ???? necessary?
       
        $_SESSION['captcha_processing'] = true; // bugfix (you could also call it a "workaround" ...)
        $svg_captcha = SingleWord2SVG( $captcha, 60, 0.8, 1.6, "black", "", "" );
        $_SESSION['captcha_processing'] = false;
        
        //echo "test: " . htmlspecialchars($svg_captcha);
        
        //return "SHORTHAND CAPTCHA BROKEN - USE:<br>$captcha";
        return $svg_captcha;
      
}

?>