<?php

// native cpp wrapper function as include 
// (necessary, because PHP doesn't tolerate presence of unexisting native function if extension is not present)

function analyze_word_linguistically($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block) {
    /*switch ($_SESSION['native_extensions']) {
        case false : return analyze_word_linguistically_classic($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block);
                    break;
        case true : return analyze_word_linguistically_classic($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block);
                    break;
    }*/
    // or here's the one-liner for the same (more or less ... :)
    return ($_SESSION['native_extensions']) ? analyze_word_linguistically_native($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block) : analyze_word_linguistically_classic($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block);
}

?>