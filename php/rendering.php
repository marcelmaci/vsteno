<?php

require_once "engine.php";

function GetPolygon($splines) {
    global $space_before_word, $correction_shadow_factor;
    $color = $_SESSION['rendering_polygon_color'];
    $outer_line_thickness = 0.001;
    
    //var_dump($splines); echo "<br>";
    // initialize path variable
    $path4 = ""; // final spline path
    
    $i = 0;
    $spline_length = count($splines);
// outer loop: repeat until $i==$length (= all shadows - if there are several - have been rendered)
while ($i<$spline_length) {  
    // determine start and end of shadow
    $start = false;
    $end = false;
    // scan up to thickness > 1.0
    while (($i<$spline_length) && ($splines[$i+offs_th] == 1.0)) $i+=tuplet_length;
    if ($i<$spline_length) $start=$i;
    // if start found => scan up to end of shadow
    if ($start !== false) { // darned php ... 'auf' ended up in an endless loop sind start was found at position 0 ... which, by implicit type cast, is false ... *grrrrr*
        $left_spline = null;
        $right_spline = null;
        $final_spline = null;
        // initialize next path
        $path4 .= "\n<path d='"; // final spline path
        $thickest_thickness = 0;
        $thickest_tuplet = 0;
        while (($i<$spline_length) && ($splines[$i+offs_th] > 1.0)) {
            if ($splines[$i+offs_th] > $thickest_thickness) {
                $thickest_thickness = $splines[$i+offs_th];
                $thickest_tuplet = $i;
            }
            $i+=tuplet_length;
        }
        $end = $i;
      
        // due to early exit points there might be no tuplet with th == 1.0 towards the end
        // in that case, $end points to an inexisting tuplet at the very end of the array
        // correct this by setting $end to preceeding tuplet
        // NOTE: final tuplet (= last of array) in early exit points will be considered as ROUND
        // (so it ends "smoothly" whereas in SE1 it ends "abruptly" like a sharp token)
        if ($end == $spline_length) $end=$spline_length-tuplet_length;
    //}
    //echo "<br><br>spline_length: $spline_length, i: $i, start: $start, end: $end thickest: $thickest_thickness at $thickest_tuplet<br>";
    // determine if start and end points are sharp
    $start_sharp = ($splines[$start+offs_x1] == $splines[$start+offs_t1]) ? true : false;
    $end_sharp = ($splines[$end+offs_x1] == $splines[$end+offs_t1]) ? true : false;
    //echo "start_sharp: #$start_sharp# end_sharp: #$end_sharp#<br>";
    
    // if start (and end) found => calculate polygon for shadow
    //if ($start) {
        // rendering loop
        for ($r=$start; $r<=$end; $r+=tuplet_length) {
            //echo "tuplet: r = $r<br>";
            // for testing just draw lines (shifted by x+=5) 
            $px = null;
            $py = null;
            $fx = null;
            $fy = null;
            $use_this_thickness = null;
            $x = $splines[$r+offs_x1]; // + 25;
            $y = $splines[$r+offs_y1];
            $tt1 = $splines[$r+offs_t1];
            $tt2 = $splines[$r+offs_t2];
            //echo "x = $x; y = $y; t1 = $tt1; t2 = $tt2<br>";
            
            // calculate outer knot
            // needs: perpendicular vector relative to straight line that goes through preceeding and following knot
            // get coordinates of preceeding and following know
            if ($r >= tuplet_length) {
                // preceeding knot exists
                if (($x != $tt1) && ($tt1 != 0)) { // x != tt1 means: original t1 == 0.5 (we are AFTER CalculateWord())
                    // condition $tt1 != 0: necessary because last tt1 in spline (= very end of the word/spline) is 0 if line is sharp
                    // this is probably a bug in CalculateWord (so the additional condition is just a workaround ...)
                    // entry tension is > 0 (round)
                    //echo "tuplet $r has tension != 0 (round)<br>";
                    $px = $splines[$r-tuplet_length+offs_x1]; // preceeding x (assume it exists)
                    $py = $splines[$r-tuplet_length+offs_y1]; //            y
                } else {
                    //echo "tuplet $r has tension 0.0 (sharp)<br>";
                    // problem with the following condition: word "Tube": preceeding t is 0 (but belongs to other token)
                    if ($r == $start) {
                        //echo "start knot: set px/py/fx/fy<br>";
                        $px = $splines[$r+offs_x1];
                        $py = $splines[$r+offs_y1];
                        //echo "set fx/fy to x = $x, y = $y<br>";
                        $fx = $splines[$r+tuplet_length+offs_x1];;
                        $fy = $splines[$r+tuplet_length+offs_y1];;
                        $use_this_thickness = $splines[$r+offs_th];
                        //echo "px = $px, py = $py, fx = $fx, fy = $fy<br>";
                        
                    } elseif ($r == $end) {
                        //echo "end knot: set px/py/fx/fy<br>";
                        $px = $splines[$r-tuplet_length+offs_x1];
                        $py = $splines[$r-tuplet_length+offs_y1];
                        //echo "set fx/fy to x = $x, y = $y<br>";
                        $fx = $splines[$r+offs_x1];;
                        $fy = $splines[$r+offs_y1];;
                        $use_this_thickness = $splines[$r-tuplet_length+offs_th]; // use preceeding thickness (end == "decreasing" part)
                        //echo "px = $px, py = $py, fx = $fx, fy = $fy<br>";
                      
                    } 
                }
            } else {
                // no preceeding knot => set it to central knot x, y
                //echo "no preceeding knot<br>";
                if ($r == $start) {
                        //echo "start knot: set px/py/fx/fy<br>";
                        $px = $splines[$r+offs_x1];
                        $py = $splines[$r+offs_y1];
                        $fx = $splines[$r+tuplet_length+offs_x1];;
                        $fy = $splines[$r+tuplet_length+offs_y1];;
                        $use_this_thickness = $splines[$r+offs_th];
                        //echo "px = $px, py = $py, fx = $fx, fy = $fy<br>";
                        
                    } elseif ($r == $end) {
                        //echo "end knot: set px/py/fx/fy<br>";
                        $px = $splines[$r-tuplet_length+offs_x1];
                        $py = $splines[$r-tuplet_length+offs_y1];
                        $fx = $splines[$r+offs_x1];;
                        $fy = $splines[$r+offs_y1];;
                        $use_this_thickness = $splines[$r-tuplet_length+offs_th]; // use preceeding thickness (end == "decreasing" part)
                        //echo "px = $px, py = $py, fx = $fx, fy = $fy<br>";
                      
                    } else { 
                        $px = $x;
                        $py = $y;
                    }
            }
            if ($fx === null) {
                //echo "fx hasn't been set => set it<br>";
                if ($r + tuplet_length < $length) {
                    // following knot exists
                    $fx = $splines[$r+tuplet_length+offs_x1]; // following x 
                    $fy = $splines[$r+tuplet_length+offs_y1]; //           y
                } else {
                    // no following knot => set it to central knot x, y
                    $fx = $x;
                    $fy = $y;
                }
            }
            //echo "preceeding: $px, $py - central: $x, $y - following: $fx, $fy<br>";
            // calculate vector for straight line (g)
            $vgx = $fx-$px;
            $vgy = $fy-$py;
            // normal vector (left) = rotate -90 degrees => negate x, then flip x and y and divide by length (d)
            $d = sqrt($vgx*$vgx + $vgy*$vgy);
            $nvx = - $vgy / $d;
            $nvy = $vgx / $d;
            // normal vector right = same as left but with negated nvx, nvy (include it directly in the calculation)
            //echo "vectors: line = $vgx, $vgy length = $d normal vector = $nvx, $nvy<br>";
            // new knot = old knot + normal vector * thinkness / 2 (half of th = length of vector)
            // include polygon lines in total area (= subtract outer line thickness)
            if ($use_this_thickness === null) {
                if (($r > $thickest_tuplet) && ($r < $end)) {
                    //echo "tuplet: $r is between $thickest_tuplet and $end<br>";
                    // $r between $thickest_tuplet and $end means: we are in "decreasing" part of shadow
                    // i.e. token gets thinner => at this point: use last thickness for calculation of vectors
                    // since in middle line modelling the thickness continues until the end of the corresponding part!
                    //$use_this_thickness = $thickest_thickness; // use thickest_thickness for beginning and end of thickest part
                    $use_this_thickness = $splines[$r-tuplet_length+offs_th];
                } elseif (($r == $start) && (!$start_sharp)) $use_this_thickness = 0;
                elseif (($r == $end) && (!$end_sharp)) $use_this_thickness = 0;
            }
            $th = ($use_this_thickness === null) ? $splines[$r+offs_th] : $use_this_thickness;
            // adjust thickness with scaling factors
            //$th = $th * $_SESSION['token_size'] / $correction_shadow_factor * $_SESSION['token_shadow'];
            $th = AdjustThickness($th);
                
            //echo "tuplet: $r thickness (se2): $th<br>";
            
            $olx = $x + $nvx * ($th / 2 - $outer_line_thickness); // ol = outer left
            $oly = $y + $nvy * ($th / 2 - $outer_line_thickness);
            $orx = $x - $nvx * ($th / 2 - $outer_line_thickness); // or = outer right (negated x, y of normal vector)
            $ory = $y - $nvy * ($th / 2 - $outer_line_thickness);
            //echo "new knot (th = $th):<br>left:<br>olx = $x + $nvx * $th = $olx<br>oly = $y + $nvy * $th = $oly<br>";
            //echo "right:<br>orx = $x - $nvx * $th = $orx<br>ory = $y - $nvy * $th = $ory<br>";
            
            // write knots to arrays
            // tensions:
            // 0.5 if point is round
            // 0.0 if point is sharp (start and end)
            // left
            $left_spline[] = $olx;
            $left_spline[] = $oly;
            $left_spline[] = ((($r == $start) && ($start_sharp)) || (($r == $end) && ($end_sharp))) ? 0.0 : 0.5; // t1
            $left_spline[] = 0;
            $left_spline[] = 1.0; // th
            $left_spline[] = 0;
            $left_spline[] = 0;
            $left_spline[] = ((($r == $start) && ($start_sharp)) || (($r == $end) && ($end_sharp))) ? 0.0 : 0.5; // t2
            // right
            $right_spline[] = $orx;
            $right_spline[] = $ory;
            $right_spline[] = ((($r == $start) && ($start_sharp)) || (($r == $end) && ($end_sharp))) ? 0.0 : 0.5; // t1
            $right_spline[] = 0;
            $right_spline[] = 1.0; // th
            $right_spline[] = 0;
            $right_spline[] = 0;
            $right_spline[] = ((($r == $start) && ($start_sharp)) || (($r == $end) && ($end_sharp))) ? 0.0 : 0.5; // t2
            
            
        }
        // close path
        
        // test bezier calculation with right spline
        // calculate control points: this is the same calculation as for word (we can therefore "abuse" the function CalculateWord)
        //echo "calculate final spline<br>";
        // compose final spline combinig right and left splines
        // first copy over right spline
        $final_spline = $right_spline;
        // connect right and left spline by a straight line
        // to do so, set entry/exit tensions 0 in last and first knots
        // (this makes it easier to calculate final polygon shape since function CalculateWord can be used)
        // this can only be done once all data has been copied, so for the moment just mark position of tuplet
        $connecting_tuplet = count($final_spline)-tuplet_length;
        // add left spine in inverted order
        $length = count($left_spline);
        //echo "length left spline: $length<br>";
        //var_dump($left_spline);
        for ($ii=$length-tuplet_length; $ii>=0; $ii-=tuplet_length) {
            //echo "add tuplet $i<br>";
            $final_spline[] = $left_spline[$ii+offs_x1];
            $final_spline[] = $left_spline[$ii+offs_y1];
            $final_spline[] = $left_spline[$ii+offs_t1];
            $final_spline[] = $left_spline[$ii+offs_d1];
            $final_spline[] = $left_spline[$ii+offs_th];
            $final_spline[] = $left_spline[$ii+offs_dr];
            $final_spline[] = $left_spline[$ii+offs_d2];
            $final_spline[] = $left_spline[$ii+offs_t2];
        }
        // now set tensions for connecting tuplet
        
        //$final_spline[$connecting_tuplet-1] = 0.0; // (preceeding) entry tension 1st knot
        $final_spline[$connecting_tuplet+offs_t1] = 0.0; // exit tension 1st knot
        $final_spline[$connecting_tuplet+offs_t2] = 0.0; // entry tension 2nd knot
        //$final_spline[$connecting_tuplet+tuplet_length+offs_t1] = 0.0; // (following) exit tension 2nd knot
        
        // calculate complete polygon spline
        $final_spline = CalculateWord($final_spline);
        //var_dump($final_spline);
        $length = count($final_spline);
        $x1 = $final_spline[offs_x1] + $space_before_word;
        $y1 = $final_spline[offs_y1];
        $path4 .= "M $x1 $y1 ";
        //echo "start: M $x1 $y1<br>";
        
        for ($ii=0; $ii<$length-tuplet_length; $ii+=tuplet_length) {
            $qx1 = $final_spline[$ii+offs_qx1] + $space_before_word;
            $qy1 = $final_spline[$ii+offs_qy1];
            $qx2 = $final_spline[$ii+offs_qx2] + $space_before_word;
            $qy2 = $final_spline[$ii+offs_qy2];
            $x2 = $final_spline[$ii+tuplet_length+offs_x1] + $space_before_word;
            $y2 = $final_spline[$ii+tuplet_length+offs_y1];
            $path4 .= "C $qx1 $qy1 $qx2 $qy2 $x2 $y2 ";
            //echo "bezier $ii: C $qx1 $qy1 $qx2 $qy2 $x2 $y2<br>";
        } 
        
        
        // finish path4 
        //fill='none'
        $path4 .= "Z' stroke='$color' stroke-width='$outer_line_thickness' style='fill:$color' />"; // final bezier path        
    
    }
//echo "i at the end of while loop: $i<br>";
//echo "path4: " . htmlspecialchars($path4) . "<br>";
}
    return $path4;
}


?>