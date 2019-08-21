<?php

require_once "constants.php";

// Guess what: svg doesn't offer the possibility to create text fields with line breaks ...
// I mean: with SVG you can do such complicated things like place a text along a path (like a bezier curve)
// but you can't do a simple thing like a line break ...
// Anyway: this function divides the text that should be displayed into single lines and uses the text- and 
// tspan-tags to insert each line individually into the svg ...

function GuessAverageNumberOfCharactersThatFitWidth($size, $width) {
    $chars = $width / ($size * 0.59871795);
    return $chars;
}

function GetSVGTextInsideRectangle($size, $wrap, $text) {
// returns a string corresponding to the text part like so:
// <text x="0" y="0" style="fill:$color;">
//    <tspan x="$left" y="$line1">First line.</tspan>
//    <tspan x="$left" y="$line2">Second line.</tspan>
//    etc.
//  </text>
// parameters: wrap = insert linebreak after x characters
    
    // variables 
    global $standard_height;
    // hardcoded for test purposes (later take them as function parameters from session variables)
    $color = $_SESSION['token_color']; // text color
    //$size = 40;     // font size
    $left = 10;     // left margin
    // calculate first line like in layouted svg
    $token_size = $_SESSION['token_size'];
    $session_baseline = $_SESSION['baseline'];
    $top_margin = $_SESSION['top_margin'];
    $system_lines = $_SESSION['num_system_lines'];
    $top = $standard_height * $token_size * $session_baseline + $top_margin - $standard_height * $token_size * ($system_lines-1); // vertical text position (baseline)
    // distance between lines
    $delta = $size * $_SESSION['layouted_original_text_delta'];    // vertical distance to next line
    //$width = 400;   // right margin
     // initialize string
    $output = "<text x='0' y='0' font-size='$size' style='fill:$color;'>\n";
    // transform text to textarray and loop
    $textarray = explode(" ", $text);
    foreach($textarray as $word) {
        if (($word === "<br>") || ($word === "<p>") || ($word === "</p>")) {
            // print line immediately after linebreak
            $output .= "<tspan x='$left' y='$top'>$single_line_text</tspan>\n";
            $single_line_text = "";
            $top += $delta;
            continue;
        }
        $single_line_text .= "$word ";
        if (mb_strlen($single_line_text)>$wrap) {
            // line is full => add it to svg as tspan
            $output .= "<tspan x='$left' y='$top'>$single_line_text</tspan>\n";
            $single_line_text = "";
            $top += $delta;
        }
    }
    // add last line if it partially full
    if (mb_strlen($single_line_text)>0)
        $output .= "<tspan x='$left' y='$top'>$single_line_text</tspan>\n";
    // close text-tag
    $output .= "</text>\n";
    return $output;
}

function GetCompleteSVGTextPage($width, $height, $size, $text) {
    global $svg_not_compatible_browser_text;
    $text = preg_replace("/<@.*?>/", "", $text); // for the moment strip out all inline-option-tags (keep html-tags)
    
    $svg_string = "<svg width=\"$width\" height=\"$height\">\n<g>\n";
    // add text
    $wrap = GuessAverageNumberOfCharactersThatFitWidth($size, $width);
    $svg_string .= GetSVGTextInsideRectangle($size, $wrap, $text);
    $svg_string .= InsertPageNumber(); // give a page number to the separate page (original text)
    // close svg
    $svg_string .= "</g>$svg_not_compatible_browser_text</svg>";
    return $svg_string;
}

?>