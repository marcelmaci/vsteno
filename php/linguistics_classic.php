<?php

// classic php analyze_word_linguistically() function as include

function analyze_word_linguistically($word, $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block) {
        global $last_written_form;
        $last_written_form = $word;
        //global $parallel_lng_form; // contains analysis of written form if phonetic transcription is selected
        //echo "<br>analyze_word_linguistically: word=$word hyphenate=$hyphenate decompose=$decompose separate=$separate glue=$glue prefixes=$prefixes stems=$stems suffixes=$suffixes block=$block<br>";
        // explode strings to get rid of commas
        $prefixes_array = explode(",", $prefixes);
        $stems_array = explode(",", $stems);
        $suffixes_array = explode(",", $suffixes);
        //$block_array = explode(",", $block);
        // trim
        $prefixes_array = array_map('trim',$prefixes_array); // use callback for trim
        $stems_array = array_map('trim',$stems_array);
        $suffixes_array = array_map('trim',$suffixes_array);
        //$block_array = array_map('trim',$suffixes_array);
    
        $several_words = explode("-", $word);  // if word contains - => split it into array
        $result = "";
        //echo "prefixes: $prefixes";
        //echo "stems: $stems<br>";
        //echo "suffixes: $suffixes<br>";
        for ($i=0;$i<count($several_words);$i++) {
            $single_result = analyze_one_word_linguistically($several_words[$i], $hyphenate, $decompose, $separate, $glue, $prefixes, $stems, $suffixes, $block);
            //echo "single result: $single_result<br>";
       
            $result .= ($i==0) ? $single_result : "=" . $single_result;     // rearrange complete word using = instead of - (since - is used for syllables)
        }
        //echo "result: $result<br>";
        if ($result === "Array") {
            if ($_SESSION['hyphenate_yesno']) $result = hyphenate($word);    // if word isn't found in dictionary, string "Array" is returned => why?! This is just a quick fix to prevent wrong results
            else $result = $word;
            if ($_SESSION['phonetics_yesno']) $result = GetPhoneticTranscription($result);
            return $result;
            //if ($_SESSION['hyphenate_yesno']) return hyphenate($word);    // if word isn't found in dictionary, string "Array" is returned => why?! This is just a quick fix to prevent wrong results
            //else return $word;
        } else {
            //echo "<br>result(BEFORE): $result<br>";
            if ($_SESSION['affixes_yesno']) $result = mark_affixes($result, $prefixes_array, $suffixes_array);
            else {
                // if affixes should not be marked, it's better to:
                // (1) mark them
                // (2) filter out markings (+, #, |)
                // Reason: parts recognized as words that actually are prefixes (and thus syllables) 
                // will appear as separate words otherwhise 
                // Example: Ein|ga-be
                // Will be processed: Ein|ga-be => Ein+ga-be => Ein-ga-be
                $result = mark_affixes($result, $prefixes_array, $suffixes_array);
                //echo "<br>result(MARKED): $result<br>";
                if ($_SESSION['filter_out_prefixes_yesno']) $result = preg_replace("/(\+)/", "-", $result);
                if ($_SESSION['filter_out_suffixes_yesno']) $result = preg_replace("/(#)/", "-", $result);
                if ($_SESSION['filter_out_words_yesno']) $result = preg_replace("/(\||\|)/", "-", $result);
            }
            //echo "<br>result(AFTER): $result<br>";
            if ($_SESSION['phonetics_yesno']) $result = GetPhoneticTranscription($result);
            //echo "<br>result(PHONETICS): $result<br>";
            return $result;
        }
}

?>