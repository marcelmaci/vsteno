<?php
require_once "constants.php";
require_once "data.php";
require_once "engine.php";
require_once "parser.php";

function GetShorthandCaptcha( $length ) {
        $vowels = array( "a", "e", "i", "o", "u", "au", "ei"); // don't use umlaut for simplicity: "ö", "ü", "ä" ); // don't use äu / eu (ambigous)
        $beginning = array( "b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z", 
            "br", "bl", "dr", "dl", "fr", "gr", "gl", "kr", "kl", "pl", "pr", "rr", "tr", "tl", "sch", "schm", "schw", "zw");
        $consonants = array( "b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z", 
            "br", "bl", "dr", "dl", "fr", "gr", "gl", "kr", "kl", "pl", "pr", "rr", "tr", "tl", "sch", "schm", "schw", "zw", 
            "tt", "pp", "ff", "nn", "mm", "ll");
        for ($i = 0; $i < $length; $i++) {
                $captcha .= ($i == 0) ? $beginning[rand(0, count($beginning)-1)] : $consonants[rand(0, count($consonants)-1)];
                $captcha .= $vowels[rand(0, count($vowels)-1)];
        }
        $_SESSION['captcha'] = $captcha;
        //$token_list = NormalText2TokenList( $captcha );
        //$svg_captcha = TokenList2SVG( $token_list, 60, 0.8, 1.6, "black", "", "" );
        CreateCombinedTokens();
        CreateShiftedTokens();
        
        $svg_captcha = SingleWord2SVG( $captcha, 60, 0.8, 1.6, "black", "", "" );
        return $svg_captcha;
}


?>