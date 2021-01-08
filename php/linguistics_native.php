<?php

// native cpp wrapper function as include 
// (necessary, because PHP doesn't tolerate presence of unexisting native function if extension is not present)

function analyze_word_linguistically($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block) {
    // $hypenate in php is boolean: do it yes or not
    // do it differently in c++:
    // - hyphenate directly in php (advantage: same hyphenator, functionalities can be implemented one after the other)
    // - use function parameter $hyphenate as string in the following way:
    // -- "" (empty string): false
    // -- "hy-phe-na-ted-word" (not empty string): true (= hyphenated result with - included at the same time)
    $hyphenate = ($hyphenate) ? hyphenate($word) : "";
    /*switch ($_SESSION['native_extensions']) {
        case false : return analyze_word_linguistically_classic($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block);
                    break;
        case true : return analyze_word_linguistically_classic($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block);
                    break;
    }*/
    // or here's the one-liner for the same (more or less ... :)
    //return SimpleTest($hyphenate);
    return analyze_word_linguistically_native($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block);
    //return ($_SESSION['native_extensions']) ? analyze_word_linguistically_native($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block) : analyze_word_linguistically_classic($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block);
}

?>