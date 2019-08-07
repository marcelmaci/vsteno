<?php

require_once "engine.php";

function GetPolygon($splines) {
    global $space_before_word;
    $color = "red";
    $outer_line_thickness = 0.001;
    $left_spline = array();
    $right_spline = array();
    
    var_dump($splines); echo "<br>";
    // initialize path
    $path = "<path d='";
    $path2 = "<path d='"; // outer left (although it's actually on the other side relative to writing direction ...)
    $path3 = "<path d='"; // outer right
    $path4 = "<path d='"; // final spline path
    
    // determine start and end of shadow
    $start = false;
    $end = false;
    // scan up to thickness > 1.0
    $i = 0;
    $length = count($splines);
    while (($i<$length) && ($splines[$i+offs_th] == 1.0)) $i+=tuplet_length;
    if ($i<$length) $start=$i;
    // if start found => scan up to end of shadow
    if ($start) {
        $thickest_thickness = 0;
        $thickest_tuplet = 0;
        while (($i<$length) && ($splines[$i+offs_th] > 1.0)) {
            if ($splines[$i+offs_th] > $thickest_thickness) {
                $thickest_thickness = $splines[$i+offs_th];
                $thickest_tuplet = $i;
            }
            $i+=tuplet_length;
        }
        $end = $i;
    }
    echo "length: $length, i: $i, start: $start, end: $end thickest: $thickest_thickness at $thickest_tuplet<br>";
    
    // if start (and end) found => calculate polygon for shadow
    if ($start) {
        // rendering loop
        for ($r=$start; $r<=$end; $r+=tuplet_length) {
            echo "tuplet: r = $r<br>";
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
            echo "x = $x; y = $y; t1 = $tt1; t2 = $tt2<br>";
            
            // testing2: draw outer lines
            // calculate outer knot
            // needs: perpendicular vector relative to straight line that goes through preceeding and following knot
            // get coordinates of preceeding and following know
            if ($r > tuplet_length) {
                // preceeding knot exists
                if ($x != $tt1) { // x !== tt1 means: original t1 != 0.5 (we are AFTER CalculateWord())
                    // entry tension is > 0 (round)
                    $px = $splines[$r-tuplet_length+offs_x1]; // preceeding x (assume it exists)
                    $py = $splines[$r-tuplet_length+offs_y1]; //            y
                } else {
                    echo "tuplet $r has tension 0.0<br>";
                    // problem with the following condition: word "Tube": preceeding t is 0 (but belongs to other token)
                    if (($splines[$r-tuplet_length+offs_x1] == $splines[$r-tuplet_length+offs_t1])
                        && ($r-tuplet_length >= $start)) {
                        echo "tuplet $r-8 has tension 0.0 and belongs to same token<br>";
                        $px = $splines[$r-tuplet_length+offs_x1];
                        $py = $splines[$r-tuplet_length+offs_y1];
                        echo "set fx/fy to x = $x, y = $y<br>";
                        $fx = $x;
                        $fy = $y;
                        $use_this_thickness = $splines[$r-tuplet_length+offs_th];
                    } else {
                        echo "tuplet $r-8 has tension != 0.0<br>"; 
                        // entry tension == 0 (sharp connection => don't use preceeding knot for calculation)
                        $px = $x;
                        $py = $y;
                    }
                }
            } else {
                // no preceeding knot => set it to central knot x, y
                $px = $x;
                $py = $y;
            }
            if ($fx === null) {
                echo "fx hasn't been set => set it<br>";
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
            echo "preceeding: $px, $py - central: $x, $y - following: $fx, $fy<br>";
            // calculate vector for straight line (g)
            $vgx = $fx-$px;
            $vgy = $fy-$py;
            // normal vector (left) = rotate -90 degrees => negate x, then flip x and y and divide by length (d)
            $d = sqrt($vgx*$vgx + $vgy*$vgy);
            $nvx = - $vgy / $d;
            $nvy = $vgx / $d;
            // normal vector right = same as left but with negated nvx, nvy (include it directly in the calculation)
            echo "vectors: line = $vgx, $vgy length = $d normal vector = $nvx, $nvy<br>";
            // new knot = old knot + normal vector * thinkness / 2 (half of th = length of vector)
            // include polygon lines in total area (= subtract outer line thickness)
            if ($use_this_thickness === null) {
                if ($r == $thickest_tuplet+tuplet_length) {
                    $use_this_thickness = $thickest_thickness; // use thickest_thickness for beginning and end of thickest part
                } elseif (($r == $start) && ($spline[$r+offs_x1] != $spline[$r+offs_t1])) $use_this_thickness = 0;
                elseif ($r == $end) $use_this_thickness = 0;
            }
            $th = ($use_this_thickness === null) ? $splines[$r+offs_th] : $use_this_thickness;
            if ($th == $thickest_thickness) {
                echo "use thickest thickness for tuplet $r<br>";
            }
            
            $olx = $x + $nvx * ($th / 2 - $outer_line_thickness); // ol = outer left
            $oly = $y + $nvy * ($th / 2 - $outer_line_thickness);
            $orx = $x - $nvx * ($th / 2 - $outer_line_thickness); // or = outer right (negated x, y of normal vector)
            $ory = $y - $nvy * ($th / 2 - $outer_line_thickness);
            echo "new knot (th = $th):<br>left:<br>olx = $x + $nvx * $th = $olx<br>oly = $y + $nvy * $th = $oly<br>";
            echo "right:<br>orx = $x - $nvx * $th = $orx<br>ory = $y - $nvy * $th = $ory<br>";
            
            // write knots to arrays
            // left
            $left_spline[] = $olx;
            $left_spline[] = $oly;
            $left_spline[] = 0.5; // t1
            $left_spline[] = 0;
            $left_spline[] = 1.0; // th
            $left_spline[] = 0;
            $left_spline[] = 0;
            $left_spline[] = 0.5; // t2
            // right
            $right_spline[] = $orx;
            $right_spline[] = $ory;
            $right_spline[] = 0.5; // t1
            $right_spline[] = 0;
            $right_spline[] = 1.0; // th
            $right_spline[] = 0;
            $right_spline[] = 0;
            $right_spline[] = 0.5; // t2
            
            // middle path
            $defx = $x + $space_before_word; // definitive x
            if ($r == $start) $path .= "M $defx $y";
            else $path .= "L $defx $y";
            // add separator
            if ($r < $end-tuplet_length) $path .= " ";
            
            // outer left path
            $defx = $olx + $space_before_word;
            $defy = $oly;
            if ($r == $start) $path2 .= "M $defx $defy";
            else $path2 .= "L $defx $defy";
            // add separator
            if ($r < $end-tuplet_length) $path2 .= " ";
            
            // outer right path
            $defx = $orx + $space_before_word;
            $defy = $ory;
            if ($r == $start) $path3 .= "M $defx $defy";
            else $path3 .= "L $defx $defy";
            // add separator
            if ($r < $end-tuplet_length) $path3 .= " ";
        
        }
        // close path
        $path .= "' stroke='red' fill='none' />";
        $path2 .= "' stroke='green' fill='none' />"; // outer left
        $path3 .= "' stroke='blue' fill='none' />"; // outer right
        
        // test bezier calculation with right spline
        // calculate control points: this is the same calculation as for word (we can therefore "abuse" the function CalculateWord)
        echo "calculate final spline<br>";
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
        for ($i=$length-tuplet_length; $i>=0; $i-=tuplet_length) {
            echo "add tuplet $i<br>";
            $final_spline[] = $left_spline[$i+offs_x1];
            $final_spline[] = $left_spline[$i+offs_y1];
            $final_spline[] = $left_spline[$i+offs_t1];
            $final_spline[] = $left_spline[$i+offs_d1];
            $final_spline[] = $left_spline[$i+offs_th];
            $final_spline[] = $left_spline[$i+offs_dr];
            $final_spline[] = $left_spline[$i+offs_d2];
            $final_spline[] = $left_spline[$i+offs_t2];
        }
        // now set tensions for connecting tuplet
        echo "connecting tuplet (before): $connecting_tuplet<br>";
        $ctx1 = $final_spline[$connecting_tuplet+offs_x1];
        $cty1 = $final_spline[$connecting_tuplet+offs_y1];
        $ctt1 = $final_spline[$connecting_tuplet+offs_t1];
        $ctd1 = $final_spline[$connecting_tuplet+offs_d1];
        $ctth = $final_spline[$connecting_tuplet+offs_th];
        $ctdr = $final_spline[$connecting_tuplet+offs_dr];
        $ctd2 = $final_spline[$connecting_tuplet+offs_d2];
        $ctt2 = $final_spline[$connecting_tuplet+offs_t2];
        $pt2 = $final_spline[$connecting_tuplet-1];
        $ft1 = $final_spline[$connecting_tuplet+tuplet_length+offs_t1];
        echo "($pt2) $ctx1 $cty1 $ctt1 $ctd1 $ctth $ctdr $ctd2 $ctt2 ($ft1)<br>";
        
        //$final_spline[$connecting_tuplet-1] = 0.0; // (preceeding) entry tension 1st knot
        $final_spline[$connecting_tuplet+offs_t1] = 0.0; // exit tension 1st knot
        $final_spline[$connecting_tuplet+offs_t2] = 0.0; // entry tension 2nd knot
        //$final_spline[$connecting_tuplet+tuplet_length+offs_t1] = 0.0; // (following) exit tension 2nd knot
        
        echo "connecting tuplet (after): $connecting_tuplet<br>";
        $ctx1 = $final_spline[$connecting_tuplet+offs_x1];
        $cty1 = $final_spline[$connecting_tuplet+offs_y1];
        $ctt1 = $final_spline[$connecting_tuplet+offs_t1];
        $ctd1 = $final_spline[$connecting_tuplet+offs_d1];
        $ctth = $final_spline[$connecting_tuplet+offs_th];
        $ctdr = $final_spline[$connecting_tuplet+offs_dr];
        $ctd2 = $final_spline[$connecting_tuplet+offs_d2];
        $ctt2 = $final_spline[$connecting_tuplet+offs_t2];
        $pt2 = $final_spline[$connecting_tuplet-1];
        $ft1 = $final_spline[$connecting_tuplet+tuplet_length+offs_t1];
        echo "($pt2) $ctx1 $cty1 $ctt1 $ctd1 $ctth $ctdr $ctd2 $ctt2 ($ft1)<br>";
        
        // calculate complete polygon spline
        $final_spline = CalculateWord($final_spline);
        //var_dump($final_spline);
        $length = count($final_spline);
        $x1 = $final_spline[offs_x1] + $space_before_word;
        $y1 = $final_spline[offs_y1];
        $path4 .= "M $x1 $y1 ";
        echo "start: M $x1 $y1<br>";
        
        for ($i=0; $i<$length-tuplet_length; $i+=tuplet_length) {
            $qx1 = $final_spline[$i+offs_qx1] + $space_before_word;
            $qy1 = $final_spline[$i+offs_qy1];
            $qx2 = $final_spline[$i+offs_qx2] + $space_before_word;
            $qy2 = $final_spline[$i+offs_qy2];
            $x2 = $final_spline[$i+tuplet_length+offs_x1] + $space_before_word;
            $y2 = $final_spline[$i+tuplet_length+offs_y1];
            $path4 .= "C $qx1 $qy1 $qx2 $qy2 $x2 $y2 ";
            echo "bezier $i: C $qx1 $qy1 $qx2 $qy2 $x2 $y2<br>";
        } 
        
        
        // finish path4 
        //fill='none'
        $path4 .= "' stroke='$color' stroke-width='$outer_line_thickness' style='fill:$color' Z/>"; // final bezier path
        
   }
    //echo htmlspecialchars($path);
    
    /*
    // if start (and end) found => calculate polygon for shadow
    if ($start) {
        // rendering loop
        for ($r=$start; $r<=$end; $r+=tuplet_length) {
            // for testing just draw lines (shifted by x+=5) 
            $x = $splines[$r+offs_x1] + 25;
            $y = $splines[$r+offs_y1];
            if ($r == $start) $path .= "M $x $y";
            else $path .= "L $x $y";
            // add separator
            if ($r < $end-tuplet_length) $path .= " ";
        }
        // close path
        $path .= "' stroke='red' fill='none' />";
    }
    //echo htmlspecialchars($path);
    */
    
    return /*$path . $path2 . $path3 .*/ $path4;
}


?>