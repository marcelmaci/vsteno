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
        for ($n = tuplet_length; $n < $array_length - tuplet_length; $n += tuplet_length) {         // start with second point and end with second point before the end
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
        // Feature of non-connecting knots (= value 5 in position offs_dr in data-tuplet) has been introduced after original CalculatWord-function.
        // Non-connecting knots means that the control-points calculated by the function are superflous / wrong (instead of the calculated values,
        // the control point of the last knot should be set to the same value (i.e. x-/y-coordinates of last knot).
        // In order to simplify things, this correction is applied as a second step after the original calculation.
        // too complicated: leave this to function CreateSVG() --- following code is WRONG!
        /*
        for ($n = 0; $n < $array_length; $n += tuplet_length) {
            $value_dr_next_tuplet = $splines[$n+tuplet_length+offs_dr];
            if ($value_dr_next_tuplet == draw_no_connection) {
                $splines[$n+bezier_offs_qx1] = $splines[$n+tuplet_length+offs_x1];
                $splines[$n+bezier_offs_qy1] = $splines[$n+tuplet_length+offs_y1];
            }
        }
        */
        return $splines;
}

// TrimSplines: finds left and right x-borders (= min x / max x) and adjust coordinates in splines
// returns max_width
function TrimSplines( $splines ) {
         global $border_margin;
         $left_x = 9999; $right_x = -9999;
         $length_splines = count( $splines );
         // first find left_x / right_x;
         for ($i = 0; $i < $length_splines; $i += tuplet_length) {
                $test_x = $splines[$i+offs_x1];
                if ($test_x < $left_x) $left_x = $test_x;
                if ($test_x > $right_x) $right_x = $test_x;
         }
         // now left_x / right_x contain min x / max x => use as delta_x to place splines at coordinate 0 at the left
         $left_x -= $border_margin; $right_x += $border_margin;
         //echo "left_x = $left_x / right_x = $right_x <br><br>";
         for ($i = 0; $i < $length_splines; $i += tuplet_length) {
                //echo "splines($i) OLD: p1(" . $splines[$i+offs_x1] . "/" . $splines[$i+offs_y1] . ") q1(" . $splines[$i+bezier_offs_qx1] . "/" . $splines[$i+bezier_offs_qy1] . ") q2(" . $splines[$i+bezier_offs_qx2] . "/" . $splines[$i+bezier_offs_qy2] . ")<br>";
                $splines[$i+offs_x1] -= $left_x;
                $splines[$i+bezier_offs_qx1] -= $left_x;
                $splines[$i+bezier_offs_qx2] -= $left_x;
                //echo "splines($i) NEW: p1(" . $splines[$i+offs_x1] . "/" . $splines[$i+offs_y1] . ") q1(" . $splines[$i+bezier_offs_qx1] . "/" . $splines[$i+bezier_offs_qy1] . ") q2(" . $splines[$i+bezier_offs_qx2] . "/" . $splines[$i+bezier_offs_qy2] . ")<br><br>";
         }
         $width = round($right_x - $left_x)+1;
         //echo "width = $width<br>";
         return array( $splines, $width );
}

function InsertAuxiliaryLines( $width ) {
    global $standard_height;
    $lines_string = "";
    if ($_SESSION['auxiliary_upper3yesno']) {
        $thickness = $_SESSION['auxiliary_upper3_thickness'];
        $color = $_SESSION['auxiliary_upper3_color'];
        $tempy = 1 * $standard_height;
        $lines_string .= "<line x1=\"0\" y1=\"$tempy\" x2=\"$width\" y2=\"$tempy\" style=\"stroke:$color;stroke-width:$thickness\" />";
    }
    if ($_SESSION['auxiliary_upper12yesno']) {
        $thickness = $_SESSION['auxiliary_upper12_thickness'];
        $color = $_SESSION['auxiliary_upper12_color'];
        for ($i = 2; $i <= 3; $i++) {
            $tempy = $i * $standard_height;
            $lines_string .= "<line x1=\"0\" y1=\"$tempy\" x2=\"$width\" y2=\"$tempy\" style=\"stroke:$color;stroke-width:$thickness\" />";
        }
    }
    if ($_SESSION['auxiliary_baselineyesno']) {
        $thickness = $_SESSION['auxiliary_baseline_thickness'];
        $color = $_SESSION['auxiliary_baseline_color'];
        $tempy = 4 * $standard_height;
        $lines_string .= "<line x1=\"0\" y1=\"$tempy\" x2=\"$width\" y2=\"$tempy\" style=\"stroke:$color;stroke-width:$thickness\" />";
    
    }
    if ($_SESSION['auxiliary_loweryesno']) {
        $thickness = $_SESSION['auxiliary_lower_thickness'];
        $color = $_SESSION['auxiliary_lower_color'];
        $tempy = 5 * $standard_height;
        $lines_string .= "<line x1=\"0\" y1=\"$tempy\" x2=\"$width\" y2=\"$tempy\" style=\"stroke:$color;stroke-width:$thickness\" />";
    }

/*
    for ($y = 1; $y < 6; $y++) {
        $temp = $y * $standard_height;
        if ($y == 4) $thickness = 0.2; else $thickness = 0.1;
        $lines_string .= "<line x1=\"0\" y1=\"$temp\" x2=\"$width\" y2=\"$temp\" style=\"stroke:rgb(120,0,0);stroke-width:$thickness\" />";
    }
*/
    return $lines_string;
}

function CreateSVG( $pre, $splines, $post, $x, $width, $stroke_width, $color_htmlrgb, $stroke_dasharray, $alternative_text ) {
    global $svg_height, $standard_height, $html_comment_open, $space_before_word, $svg_not_compatible_browser_text, $vector_value_precision;
    $shift_x = $space_before_word ; // use session-variable for $space_before_word when implemented // don't multiply with $_SESSION['token_size']; (consider both values as absolute ?!) 
    
    //list( $splines, $width ) = TrimSplines( $splines );
    
    // if ($_SESSION['token_type'] !== "htmlcode") {
        // echo "CreateSVG: Pre: $pre Post: $post<br>";
        list( $variable, $newcolor_htmlrgb ) = GetTagVariableAndValue( $pre ); 
        //if (mb_strlen($newcolor_htmlrgb) > 0) $color_htmlrgb = $newcolor_htmlrgb;
        //echo "CreateSVG:<br>Pre: $pre<br>Post: $post<br>colorhtmlrgb: $color_htmlrgb<br>";
        //if (mb_strlen($pre)>0) ParseAndSetInlineOptions( $pre );        // set inline options
        $svg_string = "<svg width=\"$width\" height=\"$svg_height\"><title>$alternative_text</title><g stroke-linecap=\"miter\" stroke-linejoin=\"miter\" stroke-miterlimit=\"20\">\n"; // stroke-linejoin=\"round\" stroke-dasharray=\"2,2\">";
        // draw auxiliary lines
        $svg_string .= InsertAuxiliaryLines( $width );
        $array_length = count( $splines );

        for ($n = 0; $n <= $array_length - (tuplet_length*2); $n += tuplet_length) {
            
            $x1 = round($splines[$n] + $shift_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $y1 = round($splines[$n+1], $vector_value_precision, PHP_ROUND_HALF_UP);
            $q1x = round($splines[$n+2] + $shift_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $q1y = round($splines[$n+3], $vector_value_precision, PHP_ROUND_HALF_UP);
            $relative_thickness = $splines[$n+4];
            $unused = $splines[$n+5];
            $q2x = round($splines[$n+6] + $shift_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $q2y = round($splines[$n+7], $vector_value_precision, PHP_ROUND_HALF_UP);
            $x2 = round($splines[$n+8] + $shift_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $y2 = round($splines[$n+9], $vector_value_precision, PHP_ROUND_HALF_UP);
            //echo "n($n): y2 = $y2<br>";
            /*
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
            echo "n($n): y2 = $y2<br>";
            */
            $absolute_thickness = $stroke_width * $relative_thickness; // echo "splines($n+8+offs_dr) = " . $splines[$n+8+5] . " / thickness(before) = $absolute_thickness / ";
            // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
            // this method doesn't work with n, m, b ... why???
            if ($splines[$n+(1*tuplet_length)+offs_dr] == draw_no_connection) { $absolute_thickness = 0; /*$color_htmlrgb="red";*/ /*$x2 = $x1; $y2 = $y1;*/} //echo "absolute_thickness(after) = $absolute_thickness<br>"; // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
            // correct control points if following point is non-connecting (see CalculateWord() for more detail)
            // search 2 tuplets ahead because data af knot 2 is stored in preceeding knot 1 (so knot 3 contains draw_no_connection info at offset offs_dr) 
            if ($splines[$n+(2*tuplet_length)+offs_dr] == draw_no_connection) { $q2x = $x2; $q2y = $y2; } 
            $svg_string .= "<path d=\"M $x1 $y1 C $q1x $q1y $q2x $q2y $x2 $y2\" stroke-dasharray=\"$stroke_dasharray\" stroke=\"$color_htmlrgb\" stroke-width=\"$absolute_thickness\" shape-rendering=\"geometricPrecision\" fill=\"none\" />\n";        
        }
        $svg_string .= "</g>$svg_not_compatible_browser_text</svg>";
        //if (mb_strlen($post)>0) ParseAndSetInlineOptions( $post );        // set inline options
    // } 
    return $svg_string;
}

function GetLateEntryPoint( $token ) {
        $length = count( $token );
        $result = 0;
        for ($i = header_length; $i < $length; $i += tuplet_length) {
            if ($token[$i+offs_d1] == 98) {
                $result = $i;
                break;
            }
        }
        if ($result) $result = (int)(($result - header_length) / tuplet_length);
        return $result;         // returns 0 if no late_entry-point is defined, otherwise returns tuplet after header which contains late_entry-point
}

function InsertTokenInSplinesList( $token, $position, $splines, $preceeding_token, $actual_x, $actual_y, $vertical, $distance, $shadowed, $factor ) {
        global $steno_tokens, $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide, $half_upordown, $one_upordown, 
        $standard_height, $baseline_y, $dont_connect;
        $token_definition_length = count( $steno_tokens[$token] );           // continue splines-list
        //$old_dont_connect = $dont_connect;
        $late_entry_position = GetLateEntryPoint( $steno_tokens[$token] );
        //echo "Token: $token - LateEntry: $late_entry_position<br>";
        // if there is a late_entry-position and the token is at beginning of tokenlist (i.e. $position === "first") => set $start_position (= values will be inserted from here on) to $entry_position; otherwise start as usual at beginning (position 0 after header)
        if (($late_entry_position) && ($position === "first")) $start_position = $late_entry_position;
        else $start_position = 0;
        //echo "token: $token position: $position startposition: $start_position<br>";
        
    if ( count( $steno_tokens[$token] > 0)) { ///????
        // ********************** header operations *************************************
        // if token is prefix then adjust actual_y
        // add inconditional deltay to token if specified in token_list
        $actual_y -= $steno_tokens[$token][offs_inconditional_delta_y_before] * $standard_height;
        $additional_deltay = 0; // probably obsolete ?!
        if ($steno_tokens[$token][offs_additional_delta_y] === 1) {         // probably obsolete with absolute positioning at offset 18 (but leave it for the moment)
            $additional_deltay = $standard_height * $steno_tokens[$token][offs_delta_y_after];
            $actual_y -= $additional_deltay;
        }
        //$actual_y -= $steno_tokens[$token][offs_relative_baseline_shifter] * $standard_height; // didn't work ...
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
        $old_dont_connect = $dont_connect;
        $dont_connect = $steno_tokens[$token][offs_dont_connect]; // echo "$token: old_dont_connect=$old_dont_connect / dont_connect = $dont_connect<br>";
        if ($dont_connect == 1) $actual_y = $baseline_y;
        
        // ******************************** data operations *************************************************
        // start with $i after header (offset header_length)
        $stop_inserting = FALSE; 
        $initial_splines_length = count($splines);
        
        if ($initial_splines_length > 0) {
            // set tension for preceeding point at offset 7 from header offset 3 of token to insert
            $splines[$initial_splines_length - 1] = $steno_tokens[$token][$i+offs_tension_before];
        }
        //for ($i = header_length /* + $late_entry_position * tuplet_length */; $i < $token_definition_length; $i += tuplet_length) {
        for ($i = header_length+$start_position * tuplet_length; $i < $token_definition_length; $i += tuplet_length) {
        
            $insert_this_point = TRUE;
            //$pt_type_entry = $steno_tokens[$token][$i+offs_d1];
            $pt_type_entry = ($steno_tokens[$token][$i+offs_d1] == 98) ? 1 : $steno_tokens[$token][$i+offs_d1]; // not sure if this is correct ... ?! maybe distinguish: if token is first position => transform 98 to 1; if token is inside or last position => transform 98 to 0 (!?)
            $pt_type_exit = $steno_tokens[$token][$i+offs_d2];
            // dont insert: (1) connecting points, (2) intermediate shadow points, if token is not shadowed, 
            if (
                ($pt_type_entry == connecting_point) 
                || (($pt_type_entry == intermediate_shadow_point) && ($shadowed == "no")) 
               ) $insert_this_point = FALSE;
            // pivot point: if entry/exit point is conditional pivot (= value 3) 
            // (1) if token in normal position => insert pivot as normal point (= value 0)
            // (2) if token in up oder down position => insert normal pivot point (= value 2)
               
               
            //   || (($pt_type_entry == conditional_pivot_point) && ($vertical !== "no"))
            //    || (($pt_type_exit == conditional_pivot_point) && ($vertical !== "no"))
               
            //echo "$token\[$i\]: type: entry = $pt_type_entry, exit = $pt_type_exit / shadowed = $shadowed  / vertical = $vertical / insert = $insert_this_point<br>";
            if (( $stop_inserting === FALSE ) && ($insert_this_point === TRUE)) {
                //echo "inserting point ...";
                $exit_point_type = $steno_tokens[$token][$i+offs_d2];     // test if point is early exit point with value 99
                if (( $exit_point_type == 99 ) && ( $position === "last")) {
                    $stop_inserting = TRUE;
                    $exit_point_type = 1;                           // make this point a classical exit point
                }
                if ($steno_tokens[$token][offs_interpretation_y_coordinates] == 1) { /*echo "$token: use absolute y = $baseline_y"; */ $y_interpretation = $baseline_y; $actual_y = $baseline_y; } // offset 18 indicates if y-coordinates are relative or absolute
                else $y_interpretation = $actual_y;
                $splines[] = $steno_tokens[$token][$i] + $actual_x + $steno_tokens[$token][offs_additional_x_before];     // calculate coordinates inside splines (svg) adding pre-offset for x
                $splines[] = $y_interpretation - $steno_tokens[$token][$i+offs_y1];            // calculate coordinates inside splines (svg) $actual_y is wrong!
                $splines[] = /*(($old_dont_connect) && ($i+offsdr < header_length+tuplet_length)) ? 0 :*/ $steno_tokens[$token][$i+offs_t1];                        // tension following the point
                // pivot point: if entry/exit point is conditional pivot (= value 3) 
                // (1) if token in normal position or down => insert pivot as normal point (= value 0)
                // (2) if token in up position => insert normal pivot point (= value 2)
                $value_to_insert = $steno_tokens[$token][$i+offs_d1];
                /*
                if ($value_to_insert == conditional_pivot_point) {
                    if ($vertical !== "up") $value_to_insert = 0;
                    else $value_to_insert = 2;
                }
                */
                $splines[] = $value_to_insert;                        // d1
                if (($shadowed == "yes") || ($steno_tokens[$token][offs_token_type] == "1")) $splines[] = $steno_tokens[$token][$i+offs_th];  // th = relative thickness of following spline (1.0 = normal thickness)
                else $splines[] = 1.0;
                $tempdr = (($old_dont_connect) && ($i+offsdr < header_length+tuplet_length)) ? 5 : $steno_tokens[$token][$i+offs_dr]; $splines[] = $tempdr; //echo "$token" . "[" . $i . "]:  old_dont_connect = $old_dont_connect / dr = $tempdr<br>";                       // dr
                //echo "token = $token / i = $i / old_dont_connect = $old_dont_connect / tempdr = $tempdr<br>";
                $value_to_insert = $exit_point_type;
                /*
                if ($value_to_insert == conditional_pivot_point) {
                    if ($vertical !== "up") $value_to_insert = 0;
                    else $value_to_insert = 2;
                }
                */
                $splines[] = $value_to_insert; //$exit_point_type;              // earlier version: $steno_tokens[$token][$i+6];                        // d2
                //$splines[] = $token_list[$token][$i+7];                          // tension before next point // this line is WRONG !!!!???
                /*echo "i = $i / token_definition_length = $token_definition_length / position = $position<br>";*/ $splines[] = /*(($position === "last") && ($i == ($token_definition_lenght - tuplet_length))) ? 0 :*/ $steno_tokens[$token][$i+offs_t2];
                // duplicate last point of last token in order to avoid weired lines before punctuation
            /*    if (($position === "last") && ($i == ($token_definition_length - tuplet_length))) {
                    $splines_actual_length = count( $splines );
                    $start_last_point = $splines_actual_length - tuplet_length;
                    for ($t = 0; $t < 8; $t++) $splines[] = $splines[$start_last_point + $t];
                } */
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
    $dont_connect_flag = false;
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
        // problem: if entry-point is found, scanning stops and we can't know if there is a pivot-point after it or not
        // solution: test if following tuplet contains exit point or pivot exit point (= there's no more chance to find a pivot point belonging to 
        // entry point of the actual token => problem: the following tuplet can be non-defined (end of array) => test also, if we are at the end of the array)
        // additional problem: if last token has pivot point and is followed by punctuation token (which should be completely disconnected from preceeding
        // token) in also smoothens the exit point (which is wrond). Solution: test if following entry point is non-connection (= value 5 in offs_dr)
        // => if following point is non-connecting, don't smoothen the exit point!
        // echo "i = $i: splines = (" . $splines[$i+offs_x1] . "/" . $splines[$i+offs_y1] . ") exit = ($exit_x/$exit_y;$pivot_exit_x/$pivot_exit_y) entry = ($entry_x/$entry_y;$pivot_entry_x/$pivot_entry_y)<br>";
        $value_d2_next_tuplet = $splines[$i+tuplet_length+offs_d2];
        $value_dr_next_tuplet = $splines[$i+tuplet_length+offs_dr]; // echo "value_dr_next_tuplet = $value_dr_next_tuplet / dont_connect_flag = $dont_connect_flag<br>";
        if ($value_dr_next_tuplet == draw_no_connection) $dont_connect_flag = true;
        if (($entry_yes) && (($i + tuplet_length >= $length_splines) || ($value_d2_next_tuplet == 1) || ($value_d2_next_tuplet == 2))) { /*echo "endoftuplet: i = $i / length_splines = $length_splines <br><br>";*/ $end_of_tuplet_list = yes; }
        if (($end_of_tuplet_list) && ($exit_yes) && ($entry_yes) && (!$dont_connect_flag)) {
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
            $end_of_tuplet_list = false; $dont_connect_flag = false;
        }
    }
    return $splines;
}

function TokenList2SVG( $pre, $TokenList, $post, $angle, $stroke_width, $scaling, $color_htmlrgb, $stroke_dasharray, $alternative_text ) {
        // initialize variables
        global $baseline_y, $steno_tokens_master, $steno_tokens, $punctuation, $space_at_end_of_stenogramm, $distance_words;
        SetGlobalScalingVariables( $scaling );
      
        //call the following function only once per text (performance)
        //CreateCombinedTokens();
        //CreateShiftedTokens();
        //if (mb_strlen($pre)>0) ParseAndSetInlineOptions( $pre );        // set inline options
        
        $actual_x = 1;                      // start position x
        $actual_y = $baseline_y;            // start position y
        $splines = array();                 // contains all information for later drawing routine
        $steno_tokens = ScaleTokens( $steno_tokens_master, $scaling );        
        $vertical = "no"; $distance = "none"; $shadowed = "no";
      
        $LastToken = ""; $length_tokenlist = count($TokenList); $position = "first";
        for ($i = 0; $i < count($TokenList); $i++) {
            $temp = $TokenList[$i];
            $temp1 = $TokenList[$i+1];
            // last position = length - 1 if normal word, length - 2 if word is followed by punctuation => wrong: there may be more than one punctuation char, in addition there might be the "|" char ... !
            $temp1_punctuation = mb_strpos( $punctuation, $temp1 ) !== false ? true : false;
            $temp1_separator1 = mb_strpos( "\\", $temp1) !== false ? true : false;
            $temp1_separator2 = mb_strpos( "|", $temp1) !== false ? true : false;
            $temp1_separator = $temp1_separator1; // || $temp1_separator2;
            //echo "<p>Zeichen: $temp - i+1 = $temp1 - punctuation = $punctuation - last_position: $last_position</p>";
            if (($i == $length_tokenlist -1) || ($temp1_punctuation) || ($temp1_separator)) $position = "last";
            
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
            $position = "inside";
        }
        // first tilt and then smoothen for better quality!!!
        $splines = TiltWordInSplines( $angle, $splines );
        $splines = SmoothenEntryAndExitPoints( $splines );
        list( $splines, $width) = TrimSplines( $splines );        
        $splines = CalculateWord( $splines );
        $svg_string = CreateSVG( $pre, $splines, $post, $actual_x + $distance_words * $scaling, $width + $space_at_end_of_stenogramm * $scaling, $stroke_width, $color_htmlrgb, $stroke_dasharray, $alternative_text );
        //if (mb_strlen($post)>0) ParseAndSetInlineOptions( $post );        // set inline options
        
        return $svg_string;
}

function NormalText2NormalTextInSVG( $text, $size ) {
    global $svg_height, $baseline_y;
    // $size contains the text size in pixels (height)
    // font: the Courier font is used since all characters have the same width => allows the calculation of svg-width in advance
    $svg_width = $size * 0.59871795 * mb_strlen( $text ) + 6;    // note: factor is an empirical value (estimation); height: use $svg_height from constants (= same height as shorthand system); add additional 8px to width for spacing
    $svg_baseline = $baseline_y;    // use same baseline as shorthand words
    $svg_color = $_SESSION['token_color'];                  // use same color as shorthand text
    //echo "SVG: height=$svg_height width=$svg_width baseline=$svg_baseline color=$svg_color text=$text<br>";
    $svg  = "<svg height=\"$svg_height\" width=\"$svg_width\">";
    $svg .= "<text x=\"0\" y=\"$svg_baseline\" fill=\"$svg_color\" font-size=\"$size\" font-family=\"Courier\">";
    $svg .= "$text</text></svg>";
    return $svg;
}

function MetaForm2TokenList( $pre, $text, $post ) {
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
    return array ($pre, $token_list_to_return, $post);
}

function NormalText2TokenList( $text ) {
    //list( $pre, $word, $post ) = GetPreAndPostTags( $text );
    $text = htmlspecialchars_decode( $text );
    list( $pre, $metaform, $post) = MetaParser( $text );
    //echo "Metaform: $metaform<br>";
    if (mb_strlen($metaform)>0) {
        list( $pre, $tokenlist, $post ) = MetaForm2TokenList( $pre, $metaform, $post );     // somehow idiot to pass $pre and $post through this function without changing anything - but do it like that for the moment
        return array( $pre, $tokenlist, $post );
    } else {
        return array( $pre, null, $post );
    }
}

function SingleWord2SVG( $text, $angle, $stroke_width, $scaling, $color_htmlrgb, $stroke_dasharray, $alternative_text ) {
    list( $pre, $tokenlist, $post ) = NormalText2TokenList( $text );
    if (mb_strlen($pre)>0) $pre_html_tag_list = ParseAndSetInlineOptions( $pre );        // set inline options
    $svg = $pre_html_tag_list;
    //$esc_svg = htmlspecialchars($svg);
   // echo "svg: $esc_svg<br>";
    
    // ugly solution to set parameters (just a quick fix: has to be replaced later)
    $angle = $_SESSION['token_inclination'];
    $stroke_width = $_SESSION['token_thickness'];
    $scaling = $_SESSION['token_size'];
    $color_htmlrgb = $_SESSION['token_color'];
   // $stroke_dasharray = $_SESSION['token_style_custom_value'];
    
    switch ($_SESSION['token_type']) {
        case "htmltext" : 
            list( $pre_nil, $middle, $post_nil) = GetPreAndPostTags( $text );
            //echo "<br>Inside SingleWord2SVG:<br>-text: " . htmlspecialchars($text) . "<br>- pre_nil: $pre_nil<br>- middle: $middle<br>- post_nil: $post_nil<br><br>";
            $pre_html_tag_list .= ParseAndSetInlineOptions ( $pre_nil );
            $svg .= $pre_html_tag_list;
            $svg .= " " . $middle;          // use raw text and insert it directly between $pre and $post html-text (= raw html code); add a space because spaces have been parsed out ...
            $post_html_tag_list = ParseAndSetInlineOptions( $post_nil );
            $svg .= $post_html_tag_list;
            return $svg;
            break;
        case "svgtext" : 
            list( $pre_nil, $middle, $post_nil) = GetPreAndPostTags( $text );
            //echo "<br>Inside SingleWord2SVG:<br>-text: " . htmlspecialchars($text) . "<br>- pre_nil: $pre_nil<br>- middle: $middle<br>- post_nil: $post_nil<br><br>";
            $pre_html_tag_list .= ParseAndSetInlineOptions ( $pre_nil );
            $svg .= $pre_html_tag_list;
            $text_as_svg .= NormalText2NormalTextInSVG( $middle, 20 );  // use fix size
            $svg .= $text_as_svg;
            $post_html_tag_list = ParseAndSetInlineOptions( $post_nil );
            $svg .= $post_html_tag_list;
            return $svg;
            break;
        default:
            if ($tokenlist !== null) {
                
                $svg .= TokenList2SVG( $pre, $tokenlist, $post, $angle, $stroke_width, $scaling, $color_htmlrgb, $stroke_dasharray, $alternative_text );
               
                if (mb_strlen($post)>0) {
                    $post_html_tag_list = ParseAndSetInlineOptions( $post );        // set inline options
                    $svg .= $post_html_tag_list;
                }
                // include debug information directly in $svg
                if ($_SESSION['output_format'] === "debug") {
                    $debug_information = GetDebugInformation( $text );
                    $svg = "$debug_information<br>$svg";
                    //   echo "<br>$token_list[0]/$token_list[1]/$token_list[2]/$token_list[3]/$token_list[4]/$token_list[5]/$token_list[6]<br>$stenogramm</p>";
                } 
              
                return $svg;
            } else return $svg;
            break;
    } 
}

function GetDebugInformation( $word) {
        $original = $word;
        $globalized = Globalizer( $word );
        $lookuped = Lookuper( $word );
        //$test_wort = Trickster( $test_wort);
        $decapitalized = Decapitalizer( $word );
        $shortened = Shortener( $decapitalized );
        $normalized = Normalizer( $shortened );
        $bundled = Bundler( $normalized );
        $transcripted = Transcriptor( $bundled );
        $substituted = Substituter( $transcripted );
        list($pre, $metaparsed, $post) = MetaParser( $word );
        $alternative_text = $original;
        $debug_text = "<p>Start: $original<br>==0=> $globalized<br>==1=> /$lookuped/<br>==2=> $decapitalized<br>==3=> $shortened<br>==4=> $normalized<br>==5=> $bundled<br>==6=> $transcripted<br>==7=> $substituted<br>=17=> $test_wort<br> Meta: $metaparsed<br><br>";
        return $debug_text;        
}

function PreProcessNormalText( $text ) {
    $text = preg_replace( "/>([^<])/", "> $1", $text );         // with this replacement inline- and html-tags can be placed everywhere
    $text = preg_replace( "/([^>])</", "$1 <", $text );         // with this replacement inline- and html-tags can be placed everywhere
    $text = preg_replace( '/\s{2,}/', ' ', ltrim( rtrim($text)) );
    
    // Original idea: use spaces to separate words that have to be transformed into shorthand-sgvs
    // Problem: there are html-tags which have spaces inside, e.g. <font size="7"> (consequence: the tags will get separated and the different
    // parts will be treated as words to transform.
    // Solution: Replace temporarily all spaces inside html-tags with $nbsp; => separate the words => replace all &nbsp; with ' '
    // (Potential problem with that solution: $nbsp; inside html-tags inserted by user will also get converted => don't think that should happen)
    //echo "Text before: $text<br>";
    
    $text = replace_all( '/(<[^>]*?) (.*?>)/', '$1#XXX#$2', $text );
    //echo "<br>Replaced Spaces:<br>" . htmlspecialchars($text) . "<br><br>";
    return $text;
}

function PostProcessNormalText( $text ) {
    $text = replace_all( '/(<[^>]*?)#XXX#(.*?>)/', '$1 $2', $text);
    return $text;
}

function PostProcessTextArray( $text_array ) {
    foreach ( $text_array as $key => $separate_entry) {
        $text_array[$key] = PostProcessNormalText($separate_entry);
    }
    return $text_array;
}

function GetLineStyle() {
    switch ($_SESSION['token_style_type']) {
        case "solid" : return ""; break;
        case "dotted" : return "1,1"; break;
        case "dashed" : return "3,1"; break;
        case "custom" : return $_SESSION['token_style_custom_value']; break;
    }
}

function CalculateInlineSVG( $text_array) {
    $output = "";
    foreach ( $text_array as $single_word ) {
        
        $debug_information = GetDebugInformation( $single_word );
        $alternative_text = ($_SESSION['output_texttagsyesno']) ? $single_word : "";
       
        $output .= SingleWord2SVG( $single_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
    }
    return $output;
}

function LayoutedSVGProcessHTMLTags( $html_string ) {
    // Unlike inline-svgs (= svgs containing each one only one word that is given to the browser as inline-element), 
    // HTML-Tags in layouted-SVG can not handled by browser.
    // To offer some basic layout control to the user, the tags <br> and </p> are used as linebreak (newline).
    // All the other HTML-tags are filtered out!!! (In other words: no support for html-tags in layouted svgs).
    // This function filters out the tags and returns number of linebreaks as int value.
   return 0;    // for the moment return 0 (= no linebreak)
}

function InsertAuxiliaryLinesInLayoutedSVG( $starty, $system_line_height, $line_height ) {
    $lines_string = "";
    $x = 0; //$_SESSION['left_margin'];
    $width = $_SESSION['output_width'];
    // $starty = $_SESSION['baseline'];
    $maxy = $_SESSION['output_height'] - $_SESSION['bottom_margin'];
    
    //echo "in Auxiliary Lines: starty: $starty maxy: $maxy line_height: $line_height ...<br>";
    
    for ($y = $starty; $y < $maxy; $y += $line_height) {
        
        if ($_SESSION['auxiliary_upper3yesno']) {
            $thickness = $_SESSION['auxiliary_upper3_thickness'];
            $color = $_SESSION['auxiliary_upper3_color'];
            $tempy = $y - 3 * $system_line_height;
            $lines_string .= "<line x1=\"$x\" y1=\"$tempy\" x2=\"$width\" y2=\"$tempy\" style=\"stroke:$color;stroke-width:$thickness\" />";
        }
        if ($_SESSION['auxiliary_upper12yesno']) {
            $thickness = $_SESSION['auxiliary_upper12_thickness'];
            $color = $_SESSION['auxiliary_upper12_color'];
            for ($i = 1; $i < 3; $i++) {
                $tempy = $y - $i * $system_line_height;
                $lines_string .= "<line x1=\"$x\" y1=\"$tempy\" x2=\"$width\" y2=\"$tempy\" style=\"stroke:$color;stroke-width:$thickness\" />";
            }
        }
        if ($_SESSION['auxiliary_baselineyesno']) {
            $thickness = $_SESSION['auxiliary_baseline_thickness'];
            $color = $_SESSION['auxiliary_baseline_color'];
            $tempy = $y;
            $lines_string .= "<line x1=\"$x\" y1=\"$tempy\" x2=\"$width\" y2=\"$tempy\" style=\"stroke:$color;stroke-width:$thickness\" />";
        }
        if ($_SESSION['auxiliary_loweryesno']) {
            $thickness = $_SESSION['auxiliary_lower_thickness'];
            $color = $_SESSION['auxiliary_lower_color'];
            $tempy = $y + $system_line_height;
            $lines_string .= "<line x1=\"$x\" y1=\"$tempy\" x2=\"$width\" y2=\"$tempy\" style=\"stroke:$color;stroke-width:$thickness\" />";
        }
    }
    //echo "auxiliary lines: ". htmlspecialchars($lines_string) . "<br><br>";
    return $lines_string;
}

function TokenList2WordSplines( $TokenList, $angle, $scaling, $color_htmlrgb, $line_style) {
        global $baseline_y, $steno_tokens_master, $steno_tokens, $punctuation, $space_at_end_of_stenogramm, $distance_words;
        SetGlobalScalingVariables( $scaling );
        
        $actual_x = 1;                      // start position x
        $actual_y = $baseline_y;                      // start position y => set this to 0 since it will be shifted later!!! => does't work: why?!
        //echo "TokenList2WordSplines(): baseline_y: $baseline_y<br>";
        
        $splines = array();                 // contains all information for later drawing routine
        $steno_tokens = ScaleTokens( $steno_tokens_master, $scaling );        
        $vertical = "no"; $distance = "none"; $shadowed = "no";
       
        $LastToken = ""; $length_tokenlist = count($TokenList); $position = "first";
        for ($i = 0; $i < count($TokenList); $i++) {
            $temp = $TokenList[$i];
            $temp1 = $TokenList[$i+1];
            // last position = length - 1 if normal word, length - 2 if word is followed by punctuation => wrong: there may be more than one punctuation char, in addition there might be the "|" char ... !
            $temp1_punctuation = mb_strpos( $punctuation, $temp1 ) !== false ? true : false;
            $temp1_separator1 = mb_strpos( "\\", $temp1) !== false ? true : false;
            $temp1_separator2 = mb_strpos( "|", $temp1) !== false ? true : false;
            $temp1_separator = $temp1_separator1; // || $temp1_separator2;
            //echo "<p>Zeichen: $temp - i+1 = $temp1 - punctuation = $punctuation - last_position: $last_position</p>";
            if (($i == $length_tokenlist -1) || ($temp1_punctuation) || ($temp1_separator)) $position = "last";
            
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
            $position = "inside";
        }
        // first tilt and then smoothen for better quality!!!
        $splines = TiltWordInSplines( $angle, $splines );
        $splines = SmoothenEntryAndExitPoints( $splines );
        list($splines, $width) = TrimSplines( $splines );        
        $splines = CalculateWord( $splines );
        
        //if (mb_strlen($post)>0) ParseAndSetInlineOptions( $post );        // set inline options
        
        return array( $splines, $width );
}

function DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_width, $last_word ) {
    global $distance_words, $vector_value_precision, $baseline_y;
    $angle = $_SESSION['token_inclination'];
    $stroke_width = $_SESSION['token_thickness'];
    $scaling = $_SESSION['token_size'];
    $color_htmlrgb = $_SESSION['token_color'];
    /*
    global $left_margin, $right_margin, $top_margin, $bottom_margin, $num_system_lines;
    $line_height = $standard_height * $_SESSION['token_size'] * $num_system_lines;
    $baseline = 4 * $standard_height;   // set baseline at 4th line <=> first line has enough space above
    $word_position_x = $left_margin; $max_width = $_SESSION['output_width'];
    $word_position_y = $baseline; $max_height = $_SESSION['output_height'];
    */
    //echo "In DrawOneLineInLayoutedSVG: word_position_x: $word_position_x word_position_y: $word_position_y last_word: $last_word stroke_width: $stroke_width<br>word_splines[]<br>";
    //var_dump($word_splines);
    
    for ($i = 0; $i < $last_word; $i++) {
        // echo "calculating word_splines($i)<br>";
        // echo "array-length($i) = " . count($word_splines[$i]) . "<br>";
        $extra_shift_y = -$baseline_y; // - ( $line_height + $system_line_height );  // something is wrong with vertical postioning of shorthand text ...
        //echo "extra_shift_y: $extra_shift_y<br>";
        
        for ($n = 0; $n < count($word_splines[$i])-tuplet_length; $n+=tuplet_length) {
            $x1 = round($word_splines[$i][$n] + $word_position_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $y1 = round($word_splines[$i][$n+1] + $word_position_y + $extra_shift_y, $vector_value_precision, PHP_ROUND_HALF_UP);
            $q1x = round($word_splines[$i][$n+2] + $word_position_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $q1y = round($word_splines[$i][$n+3] + $word_position_y + $extra_shift_y, $vector_value_precision, PHP_ROUND_HALF_UP);
            $relative_thickness = $word_splines[$i][$n+4];
            $unused = $word_splines[$i][$n+5];
            $q2x = round($word_splines[$i][$n+6] + $word_position_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $q2y = round($word_splines[$i][$n+7] + $word_position_y + $extra_shift_y, $vector_value_precision, PHP_ROUND_HALF_UP);
            $x2 = round($word_splines[$i][$n+8] + $word_position_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $y2 = round($word_splines[$i][$n+9] + $word_position_y + $extra_shift_y, $vector_value_precision, PHP_ROUND_HALF_UP);
            $absolute_thickness = $stroke_width * $relative_thickness; // echo "splines($n+8+offs_dr) = " . $splines[$n+8+5] . " / thickness(before) = $absolute_thickness / ";
            // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
            // this method doesn't work with n, m, b ... why???
            if ($word_splines[$i][$n+(1*tuplet_length)+offs_dr] == draw_no_connection) { $absolute_thickness = 0; /*$color_htmlrgb="red";*/ /*$x2 = $x1; $y2 = $y1;*/} //echo "absolute_thickness(after) = $absolute_thickness<br>"; // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
            // correct control points if following point is non-connecting (see CalculateWord() for more detail)
            // search 2 tuplets ahead because data af knot 2 is stored in preceeding knot 1 (so knot 3 contains draw_no_connection info at offset offs_dr) 
            if ($word_splines[$i][$n+(2*tuplet_length)+offs_dr] == draw_no_connection) { $q2x = $x2; $q2y = $y2; } 
            //echo "ins: wrd($i): n=$n => path: x1: $x1 y1: $y1 q1x: $q1x q1y: $q1y q2x: $q2x q2y: $q2y x2: $x2 y2: $y2<br>";
            $svg_string .= "<path d=\"M $x1 $y1 C $q1x $q1y $q2x $q2y $x2 $y2\" stroke-dasharray=\"$stroke_dasharray\" stroke=\"$color_htmlrgb\" stroke-width=\"$absolute_thickness\" shape-rendering=\"geometricPrecision\" fill=\"none\" />\n";        
        }    
        $word_position_x += $word_width[$i] + $distance_words;
    }
    
    return $svg_string;            
}

function CalculateLayoutedSVG( $text_array ) {
    // function for layouted svg
    global $baseline_y, $standard_height, $distance_words, $left_margin, $num_system_lines;
    // set variables
    //$left_margin = 5; $right_margin = 5;
    //$num_system_lines = 3;  // inline = 6 (default height); 5 means that two shorthand text lines share bottom and top line; 4 means that they share 2 lines aso ...
    $system_line_height = $standard_height * $_SESSION['token_size'];
    $line_height = $system_line_height * $num_system_lines;
    $token_size = $_SESSION['token_size'];
    $session_baseline = $_SESSION['baseline'];
    $top_margin = $_SESSION['top_margin'];
    $starty = $standard_height * $token_size * $session_baseline + $top_margin;   // set baseline at 4th line <=> first line has enough space above
    $word_position_x = $left_margin; $max_width = $_SESSION['output_width'];
    $word_position_y = $starty; $max_height = $_SESSION['output_height'];
    $bottom_limit = $max_height-$bottom_margin-$line_height; // baseline_y-bug: impossible to set baseline to 0 in calculation; extra_shift_y to correct bug etc. => has to be investigated!
    
    $svg_string = "\n<svg width=\"$max_width\" height=\"$max_height\"><g stroke-linecap=\"miter\" stroke-linejoin=\"miter\" stroke-miterlimit=\"20\">\n";
    // rectangle to show width&heigt of svg
 //   $svg_string .= "<rect width=\"$max_width\" height=\"$max_height\" style=\"fill:white;stroke:red;stroke-width:5;opacity:0.5\" />";
    
    $svg_string .= InsertAuxiliaryLinesInLayoutedSVG( $starty, $system_line_height, $line_height);
    //echo "standard_height: $standard_height line_height: $line_height starty: $starty token_size: $token_size session_baseline: $session_baseline top_margin: $top_margin num_system_lines: $num_system_lines word_position_y: $word_position_y<br>";
    //echo "auxiliary: " . htmlspecialchars(InsertAuxiliaryLinesInLayoutedSVG()) . "<br>";
            
    //$svg_string .= "<line x1=\"0\" y1=\"1\" x2=\"$max_width\" y2=\"1\" style=\"stroke:red;stroke-width:1\" />";
    //$svg_string .= "<line x1=\"$max_width\" y1=\"1\" x2=\"$max_width\" y2=\"$max_height\" style=\"stroke:red;stroke-width:1\" />";
    
    $temp_width = 0;
    $actual_word = 0;
    $text_array_length = count($text_array);
    
    foreach ( $text_array as $key => $single_word ) {
            //echo "layoutedsvg: key: $key word: $single_word<br>";
            list( $pre, $tokenlist, $post ) = NormalText2TokenList( $single_word );
            if (mb_strlen($pre)>0) $pre_html_tag_list = ParseAndSetInlineOptions( $pre );        // set inline options
            // following line is inactive for the moment (just dummy-code)
            $number_line_breaks = LayoutedSVGProcessHTMLTags( $pre_html_tag_list ); 

            $angle = $_SESSION['token_inclination'];
            $stroke_width = $_SESSION['token_thickness'];
            $scaling = $_SESSION['token_size'];
            $color_htmlrgb = $_SESSION['token_color'];
            // $stroke_dasharray = $_SESSION['token_style_custom_value'];
    
            if ($tokenlist !== null) {
                //echo "inserting: key: $key word: $single_word => word_splines($actual_word)<br>";
                list( $word_splines[$actual_word], $delta_width) = TokenList2WordSplines( $tokenlist, $angle, $scaling, $color_htmlrgb, GetLineStyle());
                $word_width[$actual_word] = $delta_width;
                //var_dump($word_splines[$actual_word]);
                $temp_width += $distance_words + $delta_width;
                //echo "tempwidth: $temp_width / delta_width: $delta_width<br>";
                //echo "word: $single_word tempwidth: $temp_width / delta_width: $delta_width<br>";
            }
            /*
            if (mb_strlen($post)>0) {
                $post_html_tag_list = ParseAndSetInlineOptions( $post );        // set inline options
                $svg .= $post_html_tag_list;
            }
            */
            $actual_word++;
            //echo "key: " . $key . " count: " . count($text_array) . "<br>";
            if (($temp_width > $max_width-$right_margin) || ($key == $text_array_length-1)) {
            // if ($temp_width > $max_width) {
                //echo "Draw actual line at key: $key word: $single_word temp_width: $temp_width actual_word: $actual_word<br>";
                // we have reached end of actual line => draw that line and set curser at beginning of next line
                $word_position_x = $left_margin;
                //echo "actual_word: $actual_word<br>";

                //$last_word = (($key == $text_array_length-1) && ($temp_width <= $max_wdith-$right_margin)) ? $actual_word : $actual_word-1;
               //$last_word = (($key == $text_array_length-1) && ($temp_width <= $max_wdith-$right_margin)) ? $actual_word : $actual_word-1;
                //echo "last_word: $last_word<br>";
                //$svg_string .= DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_width, $last_word );
                            
                
                if (($temp_width <= $max_width-$right_margin) && ($key == $text_array_length-1)) {
                    //echo "Draw shorter (incomplete) line before leaving foreach-loop<br>";
                    $last_word = $actual_word;
                    $svg_string .= DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_width, $last_word );
                } else {
                    $last_word = (($key == $text_array_length-1) && ($temp_width <= $max_wdith-$right_margin)) ? $actual_word : $actual_word-1;
                    $svg_string .= DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_width, $last_word );
                }
                $last_word_splines = $word_splines[$actual_word-1];
                unset($word_splines);
                $word_splines[0] = $last_word_splines;
                $word_width[0] = $word_width[$actual_word-1];
                $actual_word = 1;
                $old_temp_width = $temp_width;
                $last_word = 2;
                $temp_width = $left_margin + $word_width[0];
                $word_position_y += $line_height;
                if (($word_position_y > $bottom_limit) && ($key != $text_array_length-1)) {
                        //echo "word_position_y: $word_position_y max_height: $max_height bottom_margin: $bottom_margin => start new svg ...<br>";
                        // close svg-tag 
                        $svg_string .= "</g>$svg_not_compatible_browser_text</svg>";
                        // reopen svg-tag 
                        $svg_string .= "\n<svg width=\"$max_width\" height=\"$max_height\"><g stroke-linecap=\"miter\" stroke-linejoin=\"miter\" stroke-miterlimit=\"20\">\n";
                        // rectangle to show width&heigt of svg
                 //       $svg_string .= "<rect width=\"$max_width\" height=\"$max_height\" style=\"fill:white;stroke:red;stroke-width:5;opacity:0.5\" />";
                        // insert auxiliary lines
                        $svg_string .= InsertAuxiliaryLinesInLayoutedSVG( $starty, $system_line_height, $line_height);
                        //$svg_string .= InsertAuxiliaryLinesInLayoutedSVG();
                        //echo "auxiliary: " . htmlspecialchars(InsertAuxiliaryLinesInLayoutedSVG()) . "<br>";
            //echo "baseline_y: $baseline_y<br>";
            
                        $word_position_y = $baseline_y- (10 * $_SESSION['token_size']) + $top_margin; // baseline_bug ....................................
                }
                
            }
        
    }
    // PROBLEM: (1) if temp_width exceeds right border at the same time as last
    // element in array is reached (in foreach-loop), the remaining word won't
    // be drawn (should be written to following line) => call draw function once more in this case
    // (2) Similar problem with page-break => check that first and open new svg if necessary
    
    //echo "old_temp_width: $old_temp_width word_position_x: $word_position_x word_position_y: $word_position_y key: $key text_array_length: $text_array_length last_word: $last_word<br>";
    if (($old_temp_width > $max_width-$right_margin) && ($key == $text_array_length-1)) {
        if ($word_position_y > $bottom_limit) {
            //echo "word_position_y: $word_position_y max_height: $max_height bottom_margin: $bottom_margin => start new svg ...<br>";
            // close svg-tag 
            $svg_string .= "</g>$svg_not_compatible_browser_text</svg>";
            // reopen svg-tag 
            $svg_string .= "\n<svg width=\"$max_width\" height=\"$max_height\"><g stroke-linecap=\"miter\" stroke-linejoin=\"miter\" stroke-miterlimit=\"20\">\n";
            // rectangle to show width&heigt of svg
      //      $svg_string .= "<rect width=\"$max_width\" height=\"$max_height\" style=\"fill:white;stroke:red;stroke-width:5;opacity:0.5\" />";
            // insert auxiliary lines
            $svg_string .= InsertAuxiliaryLinesInLayoutedSVG( $starty, $system_line_height, $line_height);
            //echo "auxiliary: " . htmlspecialchars(InsertAuxiliaryLinesInLayoutedSVG()) . "<br>";
            $word_position_y = $baseline_y- (10 * $_SESSION['token_size']) + $top_margin; // baseline_bug ....................................
        }
        //echo "insert last line<br>";
        //var_dump($word_splines);
        $svg_string .= DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_width, $last_word );
    } 
    
    /*elseif ($old_temp_width <= $max_width-$right_margin) {
        echo "Draw shorter (incomplete) line<br>";
        $svg_string .= DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_width, $last_word );
    }*/
    
    
    $svg_string .= "</g>$svg_not_compatible_browser_text</svg>";
    return $svg_string;
}

function NormalText2SVG( $text ) {
    $text = PreProcessNormalText( $text );
    $text_array = PostProcessTextArray(explode( " ", $text));
    
    switch ($_SESSION['output_format']) {
            case "layout" : $svg = CalculateLayoutedSVG( $text_array ); break;
            default : $svg = CalculateInlineSVG( $text_array );
    }
    echo "$svg";
}


// TokenCombiner combines 2 tokens, creating a new token (as an array) and adds it into the multidimensional array steno_tokens_master[]
// connecting points: 
// - 1st token: value 4 at offset 6 in header
// - 2nd token: entry point
// IMPORTANT BUG: second token may not have entry point! Example: B + @L => B@L / afterwards, the word Publizität (which uses B@L is rendered
// wrong (bug in SmoothenEntryExitPoints()) - for the moment: live with the bug (if no entry point is given, the word is rendered correctly); 
// Needs further investigation.
// Solution: entry- and "entry"-pivot- points "inside" are DELETED ("inside" means: "keep" the first and the last one, delete the other; assuming
// that those points come frome the second token (...)
//
// New feature: TokenCombiner accepts two more parameters: inconditional_deltay_before & inconditional_deltay_after like TokenShifter
// Values are written to header of new token

function TokenCombiner( $first_token, $second_token, $deltay_before, $deltay_after ) {
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
    // adjust inconditional_deltay_before/after at offsets 13/14
    $new_token[offs_inconditional_delta_y_before] = $deltay_before;
    $new_token[offs_inconditional_delta_y_after] = $deltay_after;

    // now copy all the points of the $first_token, inserting second token at connection point
    for ($i = header_length; $i < count($steno_tokens_master[$first_token]); $i += tuplet_length) {
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
    // same problem with entry- and "entry"-pivot point => DELETE them
    $last_pivot = 0; $last_exit = 0;
    $first_pivot = 9999; $first_entry = 9999;
    for ($i = header_length; $i < count($new_token); $i+=8) {
        if ($new_token[$i+offs_d2] == 2) {
           if ($last_pivot > 0) $new_token[$last_pivot+offs_d2] = 0; // transform last pivot point to normal point // $last_pivot?!? why not $i?!?
            $last_pivot = $i; 
        }
        if ($new_token[$i+offs_d2] == 1) {
            if ($last_exit > 0) $new_token[$last_exit+offs_d2] = 0; // transform last exit point to normal point
            $last_exit = $i;
        }
        if ($new_token[$i+offs_d1] == 1) {          // test if it is an entry-point
            if ($first_entry < 9999) $new_token[$i+offs_d1] = 0;
            $first_entry = $i;
        }
        if ($new_token[$i+offs_d1] == 2) {          // test if it is an entry-pivot-point
            if ($first_pivot < 9999) $new_token[$i+offs_d1] = 0;
            $first_pivot = $i;
        }
    }
    $steno_tokens_master["$new_token_key"] = $new_token; 
}

function CreateCombinedTokens() {
    global $combiner_table;
    foreach ($combiner_table as $entry ) TokenCombiner( $entry[0], $entry[1], $entry[2], $entry[3] );
}

// TokenShifter: 
// - shifts tokens adding deltax, deltay to coordinates of original token
// - additionally TokenShifter writes values offs_inconditional_delta_y_before/after (offsets 13 & 14) to header of new token
//

function TokenShifter( $base_token, $key_for_new_token, $delta_x, $delta_y, $inconditional_delta_y_before, $inconditional_delta_y_after ) {
    global $steno_tokens_master;
    $new_token = array();
    $new_token_key = $key_for_new_token;
    // copy the header of $base_token
    // first copy all values without modification
    //echo "TokenShifter: $base_token => $key_for_new_token: deltax = $delta_x / deltay = $delta_y / inc_deltay_bef = $inconditional_delta_y_before / inc_deltay_after = $inconditional_delta_y_after<br>";
    //echo "Header:<br>";
    for ($i = 0; $i < header_length; $i++) {
        $new_token[] = $steno_tokens_master[$base_token][$i]; //echo "Offset $i: " . $steno_tokens_master[$base_token][$i] . "<br>";
    }
    // now adjust inconditional_deltay_before/after (offsets 13 & 14) and new width (add delta_x to width)
    //echo "<br>Adjustments:<br>";
    $new_token[offs_token_width] += $delta_x;
    $new_token[offs_inconditional_delta_y_before] += /*$steno_tokens_master[$base_token][offs_inconditional_delta_y_before] +*/ $inconditional_delta_y_before;
    $new_token[offs_inconditional_delta_y_after] += /*$steno_tokens_master[$base_token][offs_inconditional_delta_y_after] +*/ $inconditional_delta_y_after;
    //echo "delta_y_before: " . $new_token[offs_inconditional_delta_y_before] . "<br>";
    //echo "delta_y_after: " . $new_token[offs_inconditional_delta_y_after] . "<br><br>";
    //echo "Data tuplets:<br>";
   
    // now copy all the points of the $first_token, inserting second token at connection point
    for ($i = header_length; $i < count($steno_tokens_master[$base_token]); $i += tuplet_length) {
        $new_token[] = $steno_tokens_master[$base_token][$i+offs_x1] + $delta_x;
        $new_token[] = $steno_tokens_master[$base_token][$i+offs_y1] + $delta_y;
        $new_token[] = $steno_tokens_master[$base_token][$i+offs_t1];
        $new_token[] = $steno_tokens_master[$base_token][$i+offs_d1];
        $new_token[] = $steno_tokens_master[$base_token][$i+offs_th];
        $new_token[] = $steno_tokens_master[$base_token][$i+offs_dr];
        $new_token[] = $steno_tokens_master[$base_token][$i+offs_d2];
        $new_token[] = $steno_tokens_master[$base_token][$i+offs_t2];
    }
    //for ($i = header_length; $i < count( $new_token ); $i++) echo "Offset $i: " . $new_token[$i] . "<br>";
    $steno_tokens_master["$key_for_new_token"] = $new_token; 
}

function CreateShiftedTokens() {
    global $shifter_table;
    foreach ($shifter_table as $entry ) { /*var_dump($entry);*/ TokenShifter( $entry[0], $entry[1], $entry[2], $entry[3], $entry[4], $entry[5] );}
}
?>