<?php

/* VSTENO - Vector Steno Tool with Enhanced Notational Options
 * (c) 2018 - Marcel Maci (m.maci@gmx.ch)
 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 */
 
///////////////////////////////////////////// calculation ///////////////////////////////////////////////

function SetGlobalScalingVariables( $factor ) {
    
    global $standard_height, $svg_height, $height_above_baseline, $half_upordown, $one_upordown, $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide, $baseline_y;
    
    $standard_height = 10 * $factor;                          // height of one token like b, g, m etc.
    $svg_height = 6 * 10 * $factor;                           // height for svg image
    $height_above_baseline = 4 * 10 * $factor;                // number of lines available above baseline
    $baseline_y = 40 * $factor;                               // baseline for steno tokens
    $half_upordown = $standard_height / 2;                    // value for tokens that have to be placed 1/2 line higher or lower
    $one_upordown = $standard_height;                         //   "                                 "     1   "
    $horizontal_distance_none = 0 * $factor;
    $horizontal_distance_narrow = ($standard_height / 8) * $factor;
    $horizontal_distance_wide = $standard_height * 1 * $factor;
}

function CreateDeltaList( $angle ) {
    global $height_above_baseline;
    $deltalist = array();
    for ($y = 0; $y < $height_above_baseline+1; $y++) {
        $deltalist[$y] = $y / tan( deg2rad( $angle ));
    }
    return $deltalist;
}

function TiltWordInSplines( $angle, $splines ) {
    global $height_above_baseline;
    $deltalist = CreateDeltaList( $angle );
    for ($i = 0; $i < count( $splines ); $i += 8) {
        $temp0 = $i;
        $temp1 = $splines[$i];
        $temp2 = $splines[$i+1];
        $temp3 = $deltalist[$splines[$i+1]];
        $absolute_delta = $deltalist[ abs($height_above_baseline - $splines[$i+1])];
        if ($splines[$i+1] > $height_above_baseline) $signed_delta = -$absolute_delta; else $signed_delta=$absolute_delta;
        $splines[$i] += $signed_delta;
    }    
    return $splines;
}

// copy some tokens to $splines array

function ScaleTokens( $steno_tokens_temp,/*_master,*/ $factor ) {
    global $standard_height, $svg_height, $height_above_baseline, $half_upordown, $one_upordown, 
    $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide;
    foreach( $steno_tokens_temp as $token => $definition ) {    
        // scale informations in header
        $steno_tokens_temp[$token][0] *= $factor;
        for ($i = header_length; $i < count($definition); $i += 8) {
            $steno_tokens_temp[$token][$i] *= $factor;
            $steno_tokens_temp[$token][$i+1] *= $factor;
        }
    }
    // scale values for output in svg
    SetGlobalScalingVariables( $factor );
    return $steno_tokens_temp/*_master*/;
}

function CopyTokenToSplinesArray( $token, $base_x, $base_y, $token_list, $splines ) {
        $token_definition_length = count( $token_list[$token] );           // continue splines-list
        // start with $i after header (offset 8)
        for ($i = header_length; $i < $token_definition_length; $i += 8) {
            $splines[] = ($token_list[$token][$i]) + $base_x;              // calculate coordinates inside splines (svg)
            $splines[] = $base_y - ($token_list[$token][$i+1]);            // calculate coordinates inside splines (svg)
            $splines[] = $token_list[$token][$i+2];                        // tension following the point
            $splines[] = 0;                                                // d1: currently not used (reserved space for control points calculated by draw function)
            $splines[] = $token_list[$token][$i+4];                        // relative thickness
            $splines[] = $token_list[$token][$i+5];                        // (unused) - new: drawing variable: 0 = normal point (connected), 5 = dont connect from preceeding point
            $splines[] = 0;                                                // d2: currently not used (reserved space for control points calculated by draw function)
            $splines[] = $token_list[$token][$i+7];                        // tension before next point
        }
        return $splines;
}

function CalculateWord( $splines ) {     // parameter $splines
        $array_length = count( $splines );
        // set control points for first knot to coordinates of first knot
        $splines[2] = $splines[0];
        $splines[3] = $splines[1];            
        for ($n = 8; $n < $array_length - 8; $n += 8) {         // start with second point and end with second point before the end
            // define variables
            $x0 = $splines[$n-8];
            $y0 = $splines[$n-7];
            $x1 = $splines[$n];
            $y1 = $splines[$n+1];
            $x2 = $splines[$n+8];
            $y2 = $splines[$n+9];
            $ta = $splines[$n - 1]; // differentiate between 2 tensions (left & right from actual point)
            $tb = $splines[$n + 2];
            // calculate control points
            $d01 = sqrt( pow($x1-$x0,2) + pow($y1-$y0,2) );
            $d12 = sqrt( pow($x2-$x1,2) + pow($y2-$y1,2) );
            $fa = $ta * $d01 / ($d01+$d12); // originally just 1 tension t
            $fb = $tb * $d12 / ($d01+$d12); // originally just 1 tenison t
            $p1x = $x1 - $fa * ($x2 - $x0);
            $p1y = $y1 - $fa * ($y2 - $y0);
            $p2x = $x1 + $fb * ($x2 - $x0);
            $p2y = $y1 + $fb * ($y2 - $y0);
            // write control points to array
            $splines[$n-2] = $p1x; // $q1ax = $p1x; $q1ay = $p1y;
            $splines[$n-1] = $p1y; 
            $splines[$n+2] = $p2x; // $q1bx = $p2x; $q1by = $p2y;
            $splines[$n+3] = $p2y;
        }
        // set control points of last knot to coordinates of last knot
        $splines[$n-2] = $splines[$n];
        $splines[$n-1] = $splines[$n+1];
        $sn = $splines[$n]; 
        $sn1 = $splines[$n+1]; 
        return $splines;
}

function CreateSVG( $splines, $x, $stroke_width, $color_htmlrgb, $stroke_dasharray, $alternative_text ) {
    global $svg_height, $standard_height, $html_comment_open;
    $shift_x = 7; // temporally for z or rück at beginning
    $x = $x+$shift_x;
    $svg_string = "<svg width=\"$x\" height=\"$svg_height\"><title>$alternative_text</title><g stroke-linecap=\"miter\" stroke-linejoin=\"miter\" stroke-miterlimit=\"20\">\n"; // stroke-linejoin=\"round\" stroke-dasharray=\"2,2\">";
    // draw auxiliary lines
    
    for ($y = 1; $y < 6; $y++) {
        $temp = $y * $standard_height;
        if ($y == 4) $width = 0.5; else $width = 0.1;
        $svg_string .= "<line x1=\"0\" y1=\"$temp\" x2=\"$x\" y2=\"$temp\" style=\"stroke:rgb(120,0,0);stroke-width:$width\" />";
    }
    
    $array_length = count( $splines );
    
    for ($n = 0; $n < $array_length - 8; $n += 8) {
        $x1 = $splines[$n] + $shift_x;
        $y1 = $splines[$n+1];
        $q1x = $splines[$n+2] + $shift_x;
        $q1y = $splines[$n+3];
        $relative_thickness = $splines[$n+4];
        $unused = $splines[$n+5];
        $q2x = $splines[$n+6] + $shift_x;
        $q2y = $splines[$n+7];
        $x2 = $splines[$n+8] + $shift_x;
        $y2 = $splines[$n+9];
        $absolute_thickness = $stroke_width * $relative_thickness;
        if ($splines[$n+8+5] == 5) $absolute_thickness = 0; // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
        $svg_string .= "<path d=\"M $x1 $y1 C $q1x $q1y $q2x $q2y $x2 $y2\" stroke-dasharray=\"$stroke_dasharray\" stroke=\"$color_htmlrgb\" stroke-width=\"$absolute_thickness\" shape-rendering=\"geometricPrecision\" fill=\"none\" />\n";        
    }
    $svg_string .= "</g>Sorry, your browser does not support inline SVG.</svg>";
    return $svg_string;
}

function InsertTokenInSplinesList( $token, $position, $splines, $preceeding_token, $actual_x, $actual_y, $vertical, $distance, $shadowed, $factor ) {
        global $steno_tokens, $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide, $half_upordown, $one_upordown, $standard_height, $baseline_y;
        $token_definition_length = count( $steno_tokens[$token] );           // continue splines-list
    if ( count( $steno_tokens[$token] > 0)) { ///????
        // if token is prefix then adjust actual_y
        // add inconditional deltay to token if specified in token_list
        $actual_y -= $steno_tokens[$token][offs_inconditional_delta_y_before] * $standard_height;
        $additional_deltay = 0;
        if ($steno_tokens[$token][offs_additional_delta_y] === 1) {         // probably obsolete with absolute positioning at offset 18 (but leave it for the moment)
            $additional_deltay = $standard_height * $steno_tokens[$token][offs_delta_y_after];
            $actual_y -= $additional_deltay;
        }
        // offset 17 == 1 means: use alternative exit point of preceding token (= $LastToken), if it offers one
        if ($steno_tokens[$token][offs_exit_point_to_use] == 1) {
            // token requires alternative exit point
            $alternative_x = $steno_tokens[$preceeding_token][offs_alternative_exit_point_x];
            $alternative_y = $steno_tokens[$preceeding_token][offs_alternative_exit_point_y];
            if (($alternative_x > 0) || ($alternative_y > 0)) {
                // preceding token offers alternative exit point
                // => adjust $actual_x / $actual_y
                $actual_x += $alternative_x * $factor;
                $actual_y -= $alternative_y * $factor;
            }
        }
        
        // if actual token must be higher or lower and if it has to be joined narrow or wide adjust actual_x and actual_y before insertion
        switch( $distance ) {
            case "narrow" : $actual_x += $horizontal_distance_narrow; break;
            case "wide" : $actual_x += $horizontal_distance_wide; break;
        }
        switch ( $vertical ) {
                case "up" : $actual_y -= ($steno_tokens[$token][offs_delta_y_before] * $standard_height); break;
                case "down" : $actual_y += $half_upordown /*0.5 * $standard_height*/; 
                break; 
        }    
        // start with $i after header (offset header_length)
        $stop_inserting = FALSE; 
        $initial_splines_length = count($splines);
        
        if ($initial_splines_length > 0) {
            // set tension for preceeding point at offset 7 from header offset 3 of token to insert
            $splines[$initial_splines_length - 1] = $steno_tokens[$token][$i+offs_tension_before];
        }
        for ($i = header_length; $i < $token_definition_length; $i += 8) {
            $insert_this_point = TRUE;
            $temp = $steno_tokens[$token][$i+3];
            if ($steno_tokens[$token][$i+offs_d1] == 4) $insert_this_point = FALSE;
           
            if (( $stop_inserting === FALSE ) && ($insert_this_point === TRUE)) {
                //echo "inserting point ...";
                $exit_point_type = $steno_tokens[$token][$i+offs_d2];     // test if point is early exit point with value 99
                if (( $exit_point_type == 99 ) && ( $position === "last")) {
                    $stop_inserting = TRUE;
                    $exit_point_type = 1;                           // make this point a classical exit point
                }
                if ($steno_tokens[$token][offs_interpretation_y_coordinates] == 1) { /*echo "use absolute y"; */$y_interpretation = $baseline_y ;} // offset 18 indicates if y-coordinates are relative or absolute
                else $y_interpretation = $actual_y;
                $splines[] = $steno_tokens[$token][$i] + $actual_x + $steno_tokens[$token][offs_additional_x_before];     // calculate coordinates inside splines (svg) adding pre-offset for x
                $splines[] = $y_interpretation - $steno_tokens[$token][$i+offs_y1];            // calculate coordinates inside splines (svg) $actual_y is wrong!
                $splines[] = $steno_tokens[$token][$i+offs_t1];                        // tension following the point
                $splines[] = $steno_tokens[$token][$i+offs_d1];                        // d1
                if (($shadowed == "yes") || ($steno_tokens[$token][offs_token_type] == "1")) $splines[] = $steno_tokens[$token][$i+offs_th];  // th = relative thickness of following spline (1.0 = normal thickness)
                else $splines[] = 1.0;
                $splines[] = $steno_tokens[$token][$i+offs_dr];                        // dr
                $splines[] = $exit_point_type;              // earlier version: $steno_tokens[$token][$i+6];                        // d2
                //$splines[] = $token_list[$token][$i+7];                          // tension before next point // this line is WRONG !!!!???
                $splines[] = $steno_tokens[$token][$i+offs_t2];
            }
        }
        // correct start tension of preceeding point (for example upper point from d in "leider") - only if tension is 0
        $splines_length = count($splines);
        $token_points_length = count( $steno_tokens[$token] ) - header_length;
 
        // vertical post offset (for example "ich" => baseline has to come down again; 2nd case: grr => baseline has two move up 1 standard_height
        if (($vertical == "up") or ( $steno_tokens[$token][offs_delta_y_after] > 0) /*or ($steno_tokens[$token][6] == 3)*/) $actual_y -= $steno_tokens[$token][offs_delta_y_after] * $standard_height;
        // now set new values for actual_x and actual_y (i.e. create new base for next token)
        $actual_x += $steno_tokens[$token][offs_token_width]+$steno_tokens[$token][offs_additional_x_before]+$steno_tokens[$token][offs_additional_x_after]; // add width of token + pre/post offsets for x to calculate new horizontal position x        
        
        // restore original baseline => add inconditional deltay to token if specified in token_list
        $actual_y -= $steno_tokens[$token][offs_inconditional_delta_y_after] * $standard_height;
    }
    return array( $splines, $actual_x, $actual_y );
}

function SmoothenEntryAndExitPoints( $splines ) {
    $entry_x = 0; $entry_y = 0; $entry_i = 0; $entry_yes = false;
    $pivot_entry_x = 0; $pivot_entry_y = 0; $pivot_entry_i = 0; $pivot_entry_yes = false;
    $exit_x = 0; $exit_y = 0; $exit_i = 0; $exit_yes = false;
    $pivot_exit_x = 0; $pivot_exit_y = 0; $pivot_exit_i = 0; $pivot_exit_yes = false;
    $end_of_tuplet_list = false; $length_splines = count( $splines );
    for ( $i = 0; $i < $length_splines; $i += tuplet_length) { // this is just a QUICK-FIX!!!!!!!!!!!!!!!!! => should be a clean solution now
        switch ( $splines[$i+offs_d1] ) {           // test, if point is entry or pivot; ignore entry if no exit is defined
            case "1" : if ($exit_yes) { $entry_x = $splines[$i+offs_x1]; $entry_y = $splines[$i+offs_y1]; $entry_i = $i; $entry_yes = true; }
            break;
            case "2" : if ($exit_yes) { $pivot_entry_x = $splines[$i+offs_x1]; $pivot_entry_y = $splines[$i+offs_y1]; $pivot_entry_i = $i; $pivot_entry_yes = true; }
            break;
        }
        switch ( $splines[$i+offs_d2] ) {           // test, if point is exit or pivot
            case "1" : $exit_x = $splines[$i+offs_x1]; $exit_y = $splines[$i+offs_y1]; $exit_i = $i; $exit_yes = true; 
            break;
            case "2" : $pivot_exit_x = $splines[$i+offs_x1]; $pivot_exit_y = $splines[$i+offs_y1]; $pivot_exit_i = $i; $pivot_exit_yes = true;
            break;
        }
        // as soon as an exit- and an entry-point are defined, smoothen the connecting line => NO!
        // problem: if entry-point is found, scanning stops and we can't know if there is a pivot-point after it or notes_body
        // solution: test if following tuplet contains exit point or pivot exit point (= there's no more chance to find a pivot point belonging to 
        // entry point of the actual token => problem: the following tuplet can be non-defined (end of array) => test also, if we are at the end of the array)
        // echo "i = $i: splines = (" . $splines[$i+offs_x1] . "/" . $splines[$i+offs_y1] . ") exit = ($exit_x/$exit_y;$pivot_exit_x/$pivot_exit_y) entry = ($entry_x/$entry_y;$pivot_entry_x/$pivot_entry_y)<br>";
        $value_d2_next_tuplet = $splines[$i+tuplet_length+offs_d2];
        if (($entry_yes) && (($i + tuplet_length >= $length_splines) || ($value_d2_next_tuplet == 1) || ($value_d2_next_tuplet == 2))) { /*echo "endoftuplet: i = $i / length_splines = $length_splines <br><br>";*/ $end_of_tuplet_list = yes; }
        if (($end_of_tuplet_list) && ($exit_yes) && ($entry_yes)) {
            //echo "entry and exit point defined:<br> exit = ($exit_x/$exit_y) ($pivot_exit_x/$pivot_exit_y) / entry = ($entry_x/$entry_y) ($pivot_entry_x/$pivot_entry_y)<br>"; 
            //echo "pivot_exit_yes = " . !$pivot_exit_yes . " / pivot_entry_yes = $pivot_entry_yes<br>" ;
            // four cases: (1) just entry points (without pivots), (2a) exit point with pivot, (2b) entry point with pivot, (4) both with pivot
            // case 1: trivial => don't do anything
            // case 2a:
            if (($pivot_exit_yes) && (!$pivot_entry_yes)) {
                //echo "case 2a<br>";
                // define line going from pivot to entry point: y = m*x + c, where m = dy / dx and c = py - m * px
                $dx = $entry_x - $pivot_exit_x;
                $dy = $entry_y - $pivot_exit_y;
                $m = $dy / $dx;
                $c = $pivot_exit_y - ( $m * $pivot_exit_x );
                // now calculate new exit point keeping x-coordinate the same (adapting just y)
                $new_exit_y = $m * $exit_x + $c;
                // replace y-value for exit-point in splines with new value
                $splines[$exit_i+1] = $new_exit_y;
            }
            // case 2b
            if ((!$pivot_exit_yes) && ($pivot_entry_yes)) {
                //echo "case 2b<br>";
                // define line going from exit point to pivot: y = m*x + c, where m = dy / dx and c = exit_y - m * exit_x
                $dx = $pivot_entry_x - $exit_x;
                $dy = $pivot_entry_y - $exit_y;
                $m = $dy / $dx;
                $c = $exit_y - ( $m * $exit_x );
                // now calculate new entry point keeping x-coordinate the same (adapting just y)
                $new_entry_y = $m * $entry_x + $c;
                //echo "old_entry_y = " . $splines[$entry_i+1] . " new_entry_y = $new_entry_y<br>";
                // replace y-value for entry-point in splines with new value
                $splines[$entry_i+1] = $new_entry_y; // why the hell + 1 ?!?!?
            }
            // case 4:
            if (($pivot_entry_yes) && ($pivot_exit_yes)) {
                //echo "in case 4<br>"; // define line going from pivot to pivot: y = m*x + c, where m = dy / dx and c = pivot_exit_y - m * pivot_exit_x
                $dx = $pivot_entry_x - $pivot_exit_x;
                $dy = $pivot_entry_y - $pivot_exit_y;
                $m = $dy / $dx;
                $c = $pivot_exit_y - ( $m * $pivot_exit_x );
                // now calculate new exit and entry points keeping x-coordinate the same (adapting just y)
                $new_exit_y = $m * $exit_x + $c;
                $new_entry_y = $m * $entry_x + $c;
                // replace y-value for exit- and entry-points in splines with new value
                $splines[$exit_i+1] = $new_exit_y;
                $splines[$entry_i+1] = $new_entry_y;
            }
            // reset all variables in order to gather new points for next connection
            $entry_x = 0; $entry_y = 0; $entry_i = 0; $entry_yes = false;
            $pivot_entry_x = 0; $pivot_entry_y = 0; $pivot_entry_i = 0; $pivot_entry_yes = false;
            $exit_x = 0; $exit_y = 0; $exit_i = 0; $exit_yes = false;
            $pivot_exit_x = 0; $pivot_exit_y = 0; $pivot_exit_i = 0; $pivot_exit_yes = false;
            $end_of_tuplet_list = false;
        }
    }
    return $splines;
}

function TokenList2SVG( $TokenList, $angle, $stroke_width, $scaling, $color_htmlrgb, $stroke_dasharray, $alternative_text ) {
        // initialize variables
        global $baseline_y, $steno_tokens_master, $steno_tokens, $baseline_y, $punctuation;
        SetGlobalScalingVariables( $scaling );
        CreateCombinedTokens();
        $actual_x = 1;                      // start position x
        $actual_y = $baseline_y;            // start position y
        $splines = array();                 // contains all information for later drawing routine
        $steno_tokens = ScaleTokens( $steno_tokens_master, $scaling );        
        $vertical = "no"; $distance = "none"; $shadowed = "no";
       
        $LastToken = ""; $length_tokenlist = count($TokenList); $position = "inside";
        for ($i = 0; $i < count($TokenList); $i++) {
            $temp = $TokenList[$i];
            $temp1 = $TokenList[$i+1];
            // last position = length - 1 if normal word, length - 2 if word is followed by punctuation
            $last_position = strpos( $punctuation, $temp1 ) !== false ? -2 : -1;
            //echo "<p>Zeichen: $temp - i+1 = $temp1 - punctuation = $punctuation - last_position: $last_position</p>";
            if ($i == $length_tokenlist + $last_position) $position = "last";
            //echo "<p>tokenlist($i) = $temp</p>";
            // if token is a vowel ("virtual token") then set positioning variables
            // vowel <=> value 2 at offset 12 --- positioning variables $vertical, $distance, $shadowed at offsets 19, 20, 21
            if ($steno_tokens[$TokenList[$i]][offs_token_type] == 2) {
                //echo "tokenlist(i) = " . $TokenList[$i] . " offsets: 12 = " . $steno_tokens[$TokenList[$i]][12] . " 19 = " . $steno_tokens[$TokenList[$i]][19] . " 20 = " . $steno_tokens[$TokenList[$i]][20] . " 21 = " . $steno_tokens[$TokenList[$i]][21] . " <br>";
                $vertical = $steno_tokens[$TokenList[$i]][offs_vertical];
                $distance = $steno_tokens[$TokenList[$i]][offs_distance];
                $shadowed = $steno_tokens[$TokenList[$i]][offs_shadowed];
            } else {
                list( $splines, $actual_x, $actual_y) = InsertTokenInSplinesList( $TokenList[$i], $position, $splines, $LastToken, $actual_x, $actual_y, $vertical, $distance, $shadowed, $scaling );
                $vertical = "no"; $distance = "none"; $shadowed = "no";
                $LastToken = $TokenList[$i];
            }
        }
        // first tilt and then smoothen for better quality!!!
        $splines = TiltWordInSplines( $angle, $splines );
        $splines = SmoothenEntryAndExitPoints( $splines );
        $splines = CalculateWord( $splines );
        $svg_string = CreateSVG( $splines, $actual_x + 20 * $scaling, $stroke_width, $color_htmlrgb, $stroke_dasharray, $alternative_text ); // width is handgelenk mal pi ...
        return $svg_string;
}


function MetaForm2TokenList( $text ) {
    global $steno_tokens_master;
    $token_list_to_return = array();   
    $text_length = mb_strlen( $text );
    $i = 0; 
    
    //echo "text: $text / length: $text_length<br>";
    while ($i < $text_length) {
        $temp = mb_substr( $text, $i, 1 );
        //echo "actual token ($i): $temp<br>";
        $old_i = $i;
        if ( mb_substr( $text, $i, 1 ) === "{" ) {
            $closing_accolade = mb_strpos( $text, "}", $i+1);
            $token_to_insert = mb_substr( $text, $i+1, $closing_accolade - $i-1 );
            /*if (array_key_exists( $token_to_insert, $steno_tokens_master))*/ $token_list_to_return[] = $token_to_insert;
            $i = $closing_accolade+1;
            //echo "in accolade: insert $token_to_insert - new i = $i<br>";
        } elseif ( mb_substr( $text, $i, 1) === "[" ) {
            $closing_bracket = mb_strpos( $text, "]", $i+1);
            $token_to_insert = mb_substr( $text, $i+1, $closing_bracket - $i-1 );
            /*if (array_key_exists( $token_to_insert, $steno_tokens_master))*/ $token_list_to_return[] = $token_to_insert;
            $i = $closing_bracket+1;
            //echo "in bracket: insert $token_to_insert - new i = $i<br>";
        } else {
            // steno token is "single" token (= just 1 character) => copy it over
            $token_to_insert = mb_substr( $text, $i, 1 );
            $token_list_to_return[] = $token_to_insert;
            $i++;
            //echo "in single token: insert $token_to_insert - new i = $i<br>";
        }
    }
    return $token_list_to_return;
}

function NormalText2TokenList( $text ) {
    $metaform = MetaParser( $text );
    $tokenlist = MetaForm2TokenList( $metaform );
    return $tokenlist;
}

function NormalText2SVG( $text, $angle, $stroke_width, $scaling, $color_htmlrgb, $stroke_dasharray, $alternative_text ) {
    $tokenlist = NormalText2TokenList( $text );
    $svg = TokenList2SVG( $tokenlist, $angle, $stroke_width, $scaling, $color_htmlrgb, $stroke_dasharray, $alternative_text );
    return $svg;
}

// TokenCombiner combines 2 tokens, creating a new token (as an array) and adds it into the multidimensional array steno_tokens_master[]
// connecting points: 
// - 1st token: value 4 at offset 6 in header
// - 2nd token: entry point

function TokenCombiner( $first_token, $second_token ) {
    global $steno_tokens_master;
    $new_token = array();
    $new_token_key = $first_token . $second_token;
    // copy the header of $first_token adding x width for new token
    // first copy all values without modification
    for ($i = 0; $i < header_length; $i++) {
        $new_token[] = $steno_tokens_master[$first_token][$i];
    }
    // now adjust width adding width (offset 0) of second token to pre-offset of new_token at offset 4
    $new_token[offs_additional_x_before] = $new_token[offs_additional_x_before] + $steno_tokens_master[$second_token][offs_token_width];

    // now copy all the points of the $first_token, inserting second token at connection point
    for ($i = header_length; $i < count($steno_tokens_master[$first_token]); $i += 8) {
        if ( $steno_tokens_master[$first_token][$i+offs_d1] != 4 ) {
                // point is not a connection point => copy point without modification 
                for ($j = 0; $j < 8; $j++) $new_token[] = $steno_tokens_master[$first_token][$i+$j];
 
        } else {
                // point is a connection point => copy it over marking it as a normal point (= value 0)
                // first copy over data without modifications
                for ($j = 0; $j < 8; $j++) $new_token[] = $steno_tokens_master[$first_token][$i+$j];
                // change type from 4 to 0 in $newtoken

                $base = count($new_token) - 8;
                $type_offset = $base + offs_d1; // offset 3 in the data tuplet
                $new_token[$type_offset] = 0;
                // store connection_x and connection_y to calculate relative coordinates of second token
                $connection_x = $steno_tokens_master[$first_token][$i+offs_x1]; //$new_token[$base+0];    // x
                $connection_y = $steno_tokens_master[$first_token][$i+offs_y1]; //$new_token[$base+1];    // y
                // now, after connection point, insert all the points of the second token
                // since second token has RELATIVE coordinates, calculate x, y from connection point

                // first copy all the values without modification
                for ($n = header_length; $n < count($steno_tokens_master[$second_token]); $n += 8) {
                       // echo "$first_token + $second_token => copying: n = $n / connectionx = $connection_x / connectiony = $connection_y / x 2nd token = " . $steno_tokens_master[$second_token][$n] . "<br>"; // / value = $temp2 / count(new_token): $temp1<br>";
                        $new_token[] = $connection_x + $steno_tokens_master[$second_token][$n+offs_x1]; // - $steno_tokens_master[$first_token][4];
                        $new_token[] = $connection_y + $steno_tokens_master[$second_token][$n+offs_y1];
                        $new_token[] = $steno_tokens_master[$second_token][$n+offs_t1];
                        $new_token[] = $steno_tokens_master[$second_token][$n+offs_d1];
                        $new_token[] = $steno_tokens_master[$second_token][$n+offs_th];
                        $new_token[] = $steno_tokens_master[$second_token][$n+offs_dr];
                        $new_token[] = $steno_tokens_master[$second_token][$n+offs_d2];
                        $new_token[] = $steno_tokens_master[$second_token][$n+offs_t2];
                }
        }
    }
    // the problem now is, that the combined token may have 2 exit/pivot points
    // assume that the last exit/pivot points are the valid ones (and therefore delete preceeding ones, if they exist)
    $last_pivot = 0; $last_exit = 0;
    for ($i = header_length; $i < count($new_token); $i+=8) {
        if ($new_token[$i+offs_d2] == 2) {
           if ($last_pivot > 0) $new_token[$last_pivot+offs_d2] = 0; // transform last pivot point to normal point
            $last_pivot = $i; 
        }
        if ($new_token[$i+offs_d2] == 1) {
            if ($last_exit > 0) $new_token[$last_exit+offs_d2] = 0; // transform last exit point to normal point
            $last_exit = $i;
        }
    }
    $steno_tokens_master["$new_token_key"] = $new_token; 
}

function CreateCombinedTokens() {
    global $combiner_table;
    foreach ($combiner_table as $entry ) TokenCombiner( $entry[0], $entry[1] );
}

?>