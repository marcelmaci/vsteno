<?php

// vsteno can use polygon rendering in order to improve final rendering quality, but it is complex,
// doesn't work with all browsers / viewers and may produce wrong results.
// interpolate.php offers a simpler way based on middle line rendering. the idea is to insert 
// additional (interpolated) transition points to reduce aliasing whenever the thickness of the
// curve changes.
// due to complex dependencies this can't be applied to the original font, but must done inside the
// final word spline.
// intermediate points are inserted whenever the thickness of two following point changes,
// making the transition smoother. per default one interpolated point is inserted per step.
// the function InterpolateSpline() can be called several times in order to double the number 
// of inserted points:
// 
// number of        points at            points at                      subdivision of 
// applications     beginning            end                            curve
//  
// 1x:              A B                  A I(1) B                       1 => 2
// 2x:              A I(1) B             A I(2,1) I(1) I(2,2) B         2 => 4
// etc.
//
// the number of times InterpolateSpline() is called will be called iteration.
//
// as to where the interpolated point has to be inserted:
// - normally, it's a good idea to insert it in the middle
// - but when the thickness changes for the first or the last time, the new knot should not
//   be too far (e.g. only 10% instead of middle) away from the knot that has the thicker thickness
// => make this parameter configurable
//
// Ok ... nothing is easy with bezier curves ...
// Thought that the interpolation idea could be realized by just inserting intermediate points
// into the splines. Unfortunately, this is not true ... If you have three points P0(1/400), 
// P1(111/200), P3(211/400) you can easily calculate a intermediate point PI(42.25/300) between 
// P0 and P2 that sits perfectly on the original bezier curve that passes through P0-P1-P2.
// The point is located exactly in the middle between P0 and P1 so everything seems to be fine.
// BUT: When you give this new sequence of points P0-PI-P1-P2 to the general drawing routine
// you'll notice that curve doesn't look the way you wanted ... It overpasses now the horizontal
// line (y = 200) on top (which it shouldn't) and, even worse, the curves from P0 to PI and PI
// to P1 are not continuous ... Why is this so? Because now, with the new intermediate point PI
// the drawing routine does not calculate the control points for P1 based on P0 and P2 but on
// PI (sic!) and P2. In other words: the characteristics of the curve changes completely!
// So, inserting intermediate points just where the thickness changes alone isn't a solution.
// At best, it will work, if we take additional measures to "counterbalance" (or prevent) the 
// unwanted "distortion" of the (original) curve. How could we do that? Two ideas come to my 
// mind: 
// (1) Adjust the tensions: instead of an entry tension of 0.5 for P1 (as in the original
// spline) we could divide that by 2 (since PI sits exactly in the middle). This will indeed
// correct the left side of the curve between PI and P1 - the the problem between P1 and P2
// remains. An additional correction of the "outgoing" tension of P1 will be necessary.
// (2) Insert interpolated knots everywhere. This will probably work, but (a) inserting 
// additional knots between points with equal thickness is not only innecessary it also
// might "break" the line (leave a thin white line between the two segments) and (b) this
// will more or less double the points (and data) of the final page ...
//
// Ok, so here is the solution for now:
// - let's go with option (2) i.e. the algorithm will insert interpolated points everywhere.
// - for the moment, limit it to 1 iteration
// - in some cases where first point of a token is thicker than thickness of preceeding 
// token, the shadowing might start to early (i.e. during "transition" / long connection 
// line towards the token => user is responsible for creating "correct" fonts (i.e. without
// smooth connecting tokens where thickness of 1st knot is thicker)
// - the algorithm reveals some errors in the font used until now, e.g. knots that have
// outgoing tension 0, where it should be 0.5 (in m, l for example) => these errors in
// the font must be corrected
// 
// All in all this is a solution that might work, even if it will be rather slow and 
// produce more data than necessary with an "elegant" solution (interpolating all
// points is kind of "bruteforce" approach ... :)


function InterpolateSpline($spline) {
    // this functions gets the original spline, patches it and returns the patched spline
    // step 1: identify points that have to be patched:
    //$patch_list = IdentifyTransitionPoints($spline);
    $patch_list = GetPatchListForAllPoints($spline);
    
    //echo "patch_list:<br>"; var_dump($patch_list);
    //echo "spline (before)<br>"; var_dump($spline);
    $spline = CreateNewInterpolatedSpline($spline,$patch_list);
    //echo "spline (after)<br>"; var_dump($spline);
    return $spline;
}

function CalculateIntermediatePointAndPrepareTupletToInsert($p1x, $p1y, $c1x, $c1y, $p2x, $p2y, $c2x, $c2y, $percent, $intth, $p1t1, $p1t2, $dr) {
    //echo "CalculateBezierPoint($percent%):<br>";
    //echo "p1x = $p1x<br>p1y = $p1y<br>c1x = $c1x<br>c1y = $c1y<br>p2x = $p2x<br>p2y = $p2y<br>c2x = $c2x<br>c2y = $c2y<br>";
    list($intx, $inty) = CalculateBezierPoint($p1x, $p1y, $c1x, $c1y, $p2x, $p2y, $c2x, $c2y, $percent);
    //echo "result:<br>intx = $intx<br>inty = $inty<br>";
    //$intx = ($p1x + $p2x) / 2;
    //$inty = ($p1y + $p2y) / 2;
    //echo "comparison:<br>x = $intx<br>y = $inty<br><br>";
    
    // set fix tensions 0.5 in order to create a "smooth" integration into curve
    // set knot types d1, d2 and dr to 0 (normal point)
    return array($intx,$inty,$p1t1,0,$intth,$dr,0,$p1t2);
}

function GetConnectedControlPoint( $p1x, $p1y, $cx, $cy, $color) {
    global $space_before_word;
    $p1x += $space_before_word;
    $cx += $space_before_word;
    $output = "<line x1='$p1x' y1='$p1y' x2='$cx' y2='$cy' style=\"stroke:$color;stroke-width:1\" />";
    $output .= "<circle cx='$cx' cy='$cy' r='2' stroke=\"$color\" stroke-width=\"1\" fill=\"$color\" />";
    return $output;
}


function GetCubicBezierCurve($x1, $y1, $q1x, $q1y, $q2x, $q2y, $x2, $y2, $color) {
    global $space_before_word;
    $x1 += $space_before_word;
    $q1x += $space_before_word;
    $q2x += $space_before_word;
    $x2 += $space_before_word;
    $output = "<path d=\"M $x1 $y1 C $q1x $q1y $q2x $q2y $x2 $y2\" stroke=\"$color\" stroke-width=\"$color\" shape-rendering=\"geometricPrecision\" fill=\"none\" />\n";
    return $output;
}

function CreateNewInterpolatedSpline($spline, $patch_list) {
    // gets original spline and patch list
    // creates and returns new spline
    global $global_interpolation_debug_string;
    global $global_interpolation_debug_svg;
    
    //$global_interpolation_debug_string = "";
    //$global_interpolation_debug_svg = "";
    $shift_x = 10;
    $new_spline = array();
    $last_slice = 0;
    $end = count($patch_list);
    $length = count($spline);
    // loop through patch list
    //echo "patch list:<br>"; var_dump($patch_list);
    for ($i=0; $i<$end; $i++) {
        // get indices of two points between which interpolation is necessary
        $i_p1 = $patch_list[$i][0];
        $th_p1 = $patch_list[$i][1];
        $i_p2 = $patch_list[$i][2];
        $th_p2 = $patch_list[$i][3];
        $th_new = $patch_list[$i][4];
        if ($_SESSION['debug_show_points_yesno']) {
            $point_i1 = ($i_p1/tuplet_length)+1;
            $point_i2 = ($i_p2/tuplet_length)+1;
            $global_interpolation_debug_string .= "P($point_i1)-P($point_i2):";
        }
        //echo "patch_list($i): $i_p1:$th_p1 => $i_p2:$th_p2<br>";
        // calculate intermediate point
        // prepare all data
        // given points p1, p2
        // coordinates
        $p1x = $spline[$i_p1+offs_x1]; $p1y = $spline[$i_p1+offs_y1];
        $p2x = $spline[$i_p2+offs_x1]; $p2y = $spline[$i_p2+offs_y1];
        // tensions
        $p1t1 = ($i_p1 == 0) ? 0 : $spline[$i_p1-tuplet_length+offs_t2]; 
        $p1t2 = $spline[$i_p1+offs_t2];
        $p2t1 = $spline[$i_p1+offs_t2]; 
        $p2t2 = $spline[$i_p2+offs_t1]; // exists always, may not be valid, but will be ignored (?!)
        // dr-field
        $p1dr = $spline[$i_p1+offs_dr];
        $p2dr = $spline[$i_p2+offs_dr];
        // preceeding point
        // coordinates
        $p0x = ($i_p1 == 0) ? $p1x : $spline[$i_p1-tuplet_length+offs_x1];
        $p0y = ($i_p1 == 0) ? $p1y : $spline[$i_p1-tuplet_length+offs_y1];
        // tensions
        $p0t1 = ($i_p1 == 0) ? 0 : $spline[$i_p1-tuplet_length+offs_t1];
        $p0t2 = ($i_p1 == 0) ? 0 : $spline[$i_p1-tuplet_length+offs_t2];
        // following point
        //echo "i_p2 = $i_p2 / length = $length<br>";
        $p3x = ($i_p2 >= $length-tuplet_length) ? $p2x : $spline[$i_p2+tuplet_length+offs_x1];
        $p3y = ($i_p2 >= $length-tuplet_length) ? $p2y : $spline[$i_p2+tuplet_length+offs_y1];
        $p3dr = $spline[$i_p2+tuplet_length+offs_dr];
        // tensions
        $p3t1 = ($i_p2 >= $length-tuplet_length) ? $p2t1 : $spline[$i_p2+tuplet_length+offs_t1];
        $p3t2 = ($i_p2 >= $length-tuplet_length) ? $p2t2 : $spline[$i_p2+tuplet_length+offs_t2];
        
        // tensions not necessary
        // we want to calculate intermediate point between p1 and p2 => we must get control ponts for p1 (direction p2) and p2 (direction p1)
        // in order to get them, we must also take into consideration preceeding and following points p0 and p2 (since they determine curve
        // between p1 and p2). That's why we must call GetControlPoints twice:
        // (1) with p0, p1, p2 and tensions t1, t2 from p1
        //echo "GetControlPoints():<br>";
        //echo "1st:<br>P0($p0x/$p0y) - P1($p1x/$p1y) - P2($p2x/$p2y) - P1T12($p1t1;$p1t2)<br>";
        list($p1c1x, $p1c1y, $p1c2x, $p1c2y) = GetControlPoints($p0x,$p0y,$p1x,$p1y,$p2x,$p2y,$p1t1,$p1t2);
        // problem: if p0 == p1 (at beginning of spline) control point c2 for p1 is wrong
        // correct it setting it to p0/p1
        if (($p0x == $p1x) && ($p0y == $p1y)) {
            //echo "beginning: adjust p1c2 ... <br>";
            $p1c2x = $p1x;
            $p1c2y = $p1y;
        }
        // similar problem inside spline: sharp tensions (tensions == 0) need the control point to be corrected
        if (($p0t2 == 0) && ($p1t1 == 0)) {
            $p1c1x = $p1x;
            $p1c1y = $p1y;
            $p1c2x = $p1x;
            $p1c2y = $p1y;
        }
        
        
        //echo "GetControlPoints():<br>p1x = $p1x<br>p1y = $p1y<br>p2x = $p2x<br>p2y = $p2y<br>p3x = $p3x<br>p3y = $p3y<br>";
        //list($p1c1x, $p1c1y, $p1c2x, $p1c2y) = GetControlPoints($p1x,$p1y,$p2x,$p2y,$p3x,$p3y,$p2t1,$p2t2);
        if ($_SESSION['debug_show_points_yesno']) {
            // connection line from p1 to control point
            //$global_interpolation_debug_svg .= "<line x1='" . $p1x+$shift_x . "' y1='$p1y' x2='" . $p1c1x+$shift_x . "' y2='$p1c1y' style='stroke:purple;stroke-width:1' />";
            // control point
            $global_interpolation_debug_svg .= GetConnectedControlPoint($p1x, $p1y, $p1c1x, $p1c1y, "purple");
            $global_interpolation_debug_svg .= GetConnectedControlPoint($p1x, $p1y, $p1c2x, $p1c2y, "purple");
            
        }
        //echo "P1[C1]: $p1c1x/$p1c1y - P1[C2]: $p1c2x/$p1c2y<br>";
        
        // (2) with p1, p2, p3 and tension t1, t2 from p2
        list($p2c1x, $p2c1y, $p2c2x, $p2c2y) = GetControlPoints($p1x,$p1y,$p2x,$p2y,$p3x,$p3y,$p2t1,$p2t2);
        
        // correction control point: same problem at the end
        if (($p2x == $p3x) && ($p2y == $p3y)) {
            //echo "end: adjust p2c1 ... <br>";
            $p2c1x = $p2x;
            $p2c1y = $p2y;
        }
        // similar problem inside spline: sharp tensions (tensions == 0) need the control point to be corrected
        if (($p1t2 == 0) && ($p2t1 == 0)) {
            $p2c1x = $p2x;
            $p2c1y = $p2y;
            $p2c2x = $p2x;
            $p2c2y = $p2y;
        }
        // and in the list of never ending special cases - here is another one:
        // when the following point is non connecting (dr field == 5), connection points must also be corrected
        if ($p3dr == 5) {
            $p2c1x = $p2x;
            $p2c1y = $p2y;
        }
        
        //echo "2nd:<br>P1($p1x/$p1y) - P2($p2x/$p2y) - P3($p3x/$p3y) - P2T12($p2t1;$p2t2)<br>";
        //echo "P2[C1]: $p2c1x/$p2c1y - P2[C2]: $p2c2x/$p2c2y<br><br>";
        if ($_SESSION['debug_show_points_yesno']) {
            $global_interpolation_debug_svg .= GetConnectedControlPoint($p2x, $p2y, $p2c1x, $p2c1y, "purple");
            $global_interpolation_debug_svg .= GetConnectedControlPoint($p2x, $p2y, $p2c2x, $p2c2y, "purple");
            $global_interpolation_debug_svg .= GetCubicBezierCurve($p1x, $p1y, $p1c2x, $p1c2y, $p2c1x, $p2c1y, $p2x, $p2y, "purple"); 
            
        }
        // determine dr-field
        $dr_new = $p2dr;
        // echo "P($i): dr = $p1dr - P(i+1): dr = $p2dr<br>";
        // now get the entire tuplet
        $intermediate_point_as_tuplet = CalculateIntermediatePointAndPrepareTupletToInsert($p1x, $p1y, $p1c2x, $p1c2y, $p2x, $p2y, $p2c1x, $p2c1y, 50, $th_new, $p1t1, $p2t2, $dr_new);
        
        // slice original array and new point to new spline
        // first part: from last_slice to i_p1 (inclusive) / i_p2 (exclusive)
        //echo "slice: $last_slice, $i_p2<br>";
        $new_spline = array_merge( $new_spline, array_slice($spline, $last_slice, $i_p2-$last_slice), $intermediate_point_as_tuplet); //array(0,0,0.5,0,1.0,0,0,0.5));
        //echo "new spline: <br>"; var_dump($new_spline);
        // new point
        //$new_spline[] = array(0,0,0.5,0,1.0,0,0,0.5);
        $last_slice = $i_p2;
    }
    // add last part
    $new_spline = array_merge( $new_spline, array_slice($spline, $last_slice));
    return $new_spline;
}

function GetPatchListForAllPoints($spline) {
    // gets the original spline and returns patch list with for all points
   // define empty patch list
    $patch_list = array();
    
    // scan through data tuplets
    $i=0;
    $end = count($spline);
    while ($i<$end) {
        //echo "check $i:<br>";
        if ($i == 0) {
            $th_last = $spline[$i+offs_th];
            $i_last = $i;
            //echo "set th_last = $th_last<br>";
        } else {
            $th_act = $spline[$i+offs_th];
            //echo "set th_act = $th_act<br>";
            $t1_act = $spline[$i+offs_t1]; // test only t1 for the moment (t2 should also be tested)
            //echo "set t1_act = $t1_act<br>";
           // insert all points without condition
           // if (($th_act !== $th_last) && ($t1_act > 0)) {
                // not yet implemented: increasing vs decreasing thickness
                $point_i1 = ($i_last / tuplet_length)+1;
                $point_i2 = ($i / tuplet_length)+1;
                //echo "transition: P($point_i1)[$i_last] $th_last => P($point_i2)[$i] $th_act ";
                if ($th_last > $th_act) {
                    //echo " ... decreasing ... thickness ";
                    // decreasing thickness
                    // if t1 of second point == 0: maintain same thickness!
                    // not sure which tension should be tested here ... seems to work ... :)
                    $test_tension = $spline[$i+offs_t1];
                    //echo " ... test tension: $test_tension ";
                    if ($test_tension == 0) {
                        $th_new = $th_last;
                    } else {
                        $th_new = ($th_last + $th_act) / 2; // decrease thickness by 50%
                    }
                } else {
                    // this part is for increasing thicknesses
                    // if incoming tension of following knot is 0, don't increase thickness (must be a sharp transition)
                    $test_tension = $spline[$i+offs_t1];
                    if ($test_tension == 0) {
                        $th_new = $th_last; // same thickness
                    } else {
                        // normal case
                        $th_new = ($th_last + $th_act) / 2; 
                    }
                }
                //echo " (th_new = $th_new)<br>";
                $patch_list[] = array( $i_last, $th_last, $i, $th_act, $th_new);
            //}
            $th_last = $th_act;
            //echo "set th_last = $th_last<br>";
            $i_last = $i;
        }
        $i+=tuplet_length;
    }
    return $patch_list; 
}

function IdentifyTransitionPoints($spline) {
    // gets the original spline and returns patch list with points
    // that have to be interpolated
    
    // define empty patch list
    $patch_list = array();
    // scan through data tuplets
    $i=0;
    $end = count($spline);
    while ($i<$end) {
        //echo "check $i:<br>";
        if ($i == 0) {
            $th_last = $spline[$i+offs_th];
            $i_last = $i;
            //echo "set th_last = $th_last<br>";
        } else {
            $th_act = $spline[$i+offs_th];
            //echo "set th_act = $th_act<br>";
            $t1_act = $spline[$i+offs_t1]; // test only t1 for the moment (t2 should also be tested)
            //echo "set t1_act = $t1_act<br>";
            if (($th_act !== $th_last) && ($t1_act > 0)) {
                // not yet implemented: increasing vs decreasing thickness
                $point_i1 = ($i_last / tuplet_length)+1;
                $point_i2 = ($i / tuplet_length)+1;
                //echo "transition: P($point_i1)[$i_last] $th_last => P($point_i2)[$i] $th_act<br>";
                $th_new = ($th_last + $th_act) / 2; // not correct for decreasing thickness
                $patch_list[] = array( $i_last, $th_last, $i, $th_act, $th_new);
            }
            $th_last = $th_act;
            //echo "set th_last = $th_last<br>";
            $i_last = $i;
        }
        $i+=tuplet_length;
    }
    return $patch_list;
}
?>