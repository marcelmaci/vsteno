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
// the number of times InterpolateSpline() is called iteration.
//
// as to where the interpolated point has to be inserted:
// - normally, it's a good idea to insert it in the middle
// - but when the thickness changes for the first or the last time, the new knot should not
//   be too far (e.g. only 10% instead of middle) away from the knot that has the thicker thickness
// => make this parameter configurable

function InterpolateSpline($spline) {
    // this functions gets the original spline, patches it and returns the patched spline
    // step 1: identify points that have to be patched:
    $patch_list = IdentifyTransitionPoints($spline);
    //echo "patch_list:<br>"; var_dump($patch_list);
    //echo "spline (before)<br>"; var_dump($spline);
    $spline = CreateNewInterpolatedSpline($spline,$patch_list);
    //echo "spline (after)<br>"; var_dump($spline);
    return $spline;
}

function CalculateIntermediatePointAndPrepareTupletToInsert($p1x, $p1y, $c1x, $c1y, $p2x, $p2y, $c2x, $c2y, $percent, $intth) {
    //echo "CalculateBezierPoint($percent%):<br>";
    //echo "p1x = $p1x<br>p1y = $p1y<br>c1x = $c1x<br>c1y = $c1y<br>p2x = $p2x<br>p2y = $p2y<br>c2x = $c2x<br>c2y = $c2y<br>";
    list($intx, $inty) = CalculateBezierPoint($p1x, $p1y, $c1x, $c1y, $p2x, $p2y, $c2x, $c2y, $percent);
    //echo "result:<br>intx = $intx<br>inty = $inty<br>";
    //$intx = ($p1x + $p2x) / 2;
    //$inty = ($p1y + $p2y) / 2;
    //echo "comparison:<br>x = $intx<br>y = $inty<br><br>";
    
    // set fix tensions 0.5 in order to create a "smooth" integration into curve
    // set knot types d1, d2 and dr to 0 (normal point)
    return array($intx,$inty,0.5,0,$intth,0,0,0.5);
}

function CreateNewInterpolatedSpline($spline, $patch_list) {
    // gets original spline and patch list
    // creates and returns new spline
    $new_spline = array();
    $last_slice = 0;
    $end = count($patch_list);
    // loop through patch list
    for ($i=0; $i<$end; $i++) {
        // get indices of two points between which interpolation is necessary
        $i_p1 = $patch_list[$i][0];
        $th_p1 = $patch_list[$i][1];
        $i_p2 = $patch_list[$i][2];
        $th_p2 = $patch_list[$i][3];
        $th_new = $patch_list[$i][4];
        //echo "patch_list($i): $i_p1:$th_p1 => $i_p2:$th_p2<br>";
        // calculate intermediate point
        // prepare all data
        // given points p1, p2
        // coordinates
        $p1x = $spline[$i_p1+offs_x1]; $p1y = $spline[$i_p1+offs_y1];
        $p2x = $spline[$i_p2+offs_x1]; $p2y = $spline[$i_p2+offs_y1];
        // tensions
        $p1t1 = ($i_p1 == 0) ? 0 : $spline[$i_p1+offs_t2]; 
        $p1t2 = $spline[$i_p2+offs_t1];
        $p2t1 = $spline[$i_p1+offs_t2]; 
        $p2t2 = $spline[$i_p2+offs_t1]; // exists always, may not be valid, but will be ignored (?!)
        // preceeding point
        // coordinates
        $p0x = ($i_p1 == 0) ? $p1x : $spline[$i_p1-tuplet_length+offs_x1];
        $p0y = ($i_p1 == 0) ? $p1y : $spline[$i_p1-tuplet_length+offs_y1];
        // tensions not necessary
        // following point
        $p3x = ($i_p1 >= $end-tuplet_length) ? $p1x : $spline[$i_p1+tuplet_length+offs_x1];
        $p3y = ($i_p1 >= $end-tuplet_length) ? $p1y : $spline[$i_p1+tuplet_length+offs_y1];
        // tensions not necessary
        // we want to calculate intermediate point between p1 and p2 => we must get control ponts for p1 (direction p2) and p2 (direction p1)
        // in order to get them, we must also take into consideration preceeding and following points p0 and p2 (since they determine curve
        // between p1 and p2). That's why we must call GetControlPoints twice:
        // (1) with p0, p1, p2 and tensions t1, t2 from p1
        //echo "GetControlPoints():<br>p0x = $p0x<br>p0y = $p0y<br>p1x = $p1x<br>p1y = $p1y<br>p2x = $p2x<br>p2y = $p2y<br>";
        list($p1c1x, $p1c1y, $p1c2x, $p1c2y) = GetControlPoints($p0x,$p0y,$p1x,$p1y,$p2x,$p2y,$p1t1,$p1t2);
        //echo "result:<br>p1c1x = $p1c1x<br>p1c1y = $p1c1y<br>p1c2x = $p1c2x<br>p1c1y = $p1c2y<br><br>";
        
        // (2) with p1, p2, p3 and tension t1, t2 from p2
        list($p2c1x, $p2c1y, $p2c2x, $p2c2y) = GetControlPoints($p1x,$p1y,$p2x,$p2y,$p3x,$p3y,$p2t1,$p2t2);
        
        // now get the entire tuplet
        $intermediate_point_as_tuplet = CalculateIntermediatePointAndPrepareTupletToInsert($p1x, $p1y, $p1c2x, $p1c2y, $p2x, $p2y, $p2c1x, $p2c1y, 50, $th_new);
        
        // slice original array and new point to new spline
        // first part: from last_slice to i_p1 (inclusive) / i_p2 (exclusive)
        //echo "slice: $last_slice, $i_p2<br>";
        $new_spline = array_merge( $new_spline, array_slice($spline, $last_slice, $i_p2-$last_slice), $intermediate_point_as_tuplet); //array(0,0,0.5,0,1.0,0,0,0.5));
        // new point
        //$new_spline[] = array(0,0,0.5,0,1.0,0,0,0.5);
        $last_slice = $i_p2;
    }
    // add last part
    $new_spline = array_merge( $new_spline, array_slice($spline, $last_slice));
    return $new_spline;
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
        if ($i == 0) {
            $th_last = $spline[$i+offs_th];
            $i_last = $i;
        } else {
            $th_act = $spline[$i+offs_th];
            $t1_act = $spline[$i+offs_t1]; // test only t1 for the moment (t2 should also be tested)
            if (($th_act !== $th_last) && ($t1_act > 0)) {
                // not yet implemented: increasing vs decreasing thickness
                //echo "transition: spline($i_last) $th_last => spline($i) $th_act (t1_act = $t1_act)<br>";
                $th_new = ($th_last + $th_act) / 2; // not correct for decreasing thickness
                $patch_list[] = array( $i_last, $th_last, $i, $th_act, $th_new);
            }
            $th_last = $th_act;
            $i_last = $i;
        }
        $i+=tuplet_length;
    }
    return $patch_list;
}
?>