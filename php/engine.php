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
 
/* SE1 BACKPORTS

   Ok, first of all, let's be honest: this code (i.e. SE1) is a MESS! It's the 
   result of the fact that the concept for a working "steno engine" was developped
   at the same time when it was coded. In other words: No real concept existed at 
   the beginning and the process was trial and error: add one feature and if it
   worked, leave it. If it didn't work: Adapt it or delete it (entirely or partially)
   and go on to the next step (next feature needed by the Stolze-Schrey-System).
   For short: The SE1 - as a "proof of concept" developpement - is the result
   of those constant addings and deletings without a global concept (and with
   meany tweaks and ad-hoc solutions). That was the reason why a new SE2 should
   be developped with all the lessons learned.
   
   Unfortunately, SE1 is the only engine that really works up to this date (january
   2019) and the SE2 is still a lot of work and a long road to go. So let's see
   if we can add a last tweak to at least add orthogonal, proportional knots and
   parallel rotating axis to SE1 as described in se1_backports.php.
   
   Again: This implementation should have as little impact on the working engine
   as possible. Theoretically, the only thing that changes with orthogonal and
   proportional knots is the calculation for the x and y coordinates for the knots.
   But even if it looks easy to substitute those coordinates in SE1, it is not:
   there isn't one single function that would allow us to change them. Instead,
   stenograms in SE1 are calculated in multiple steps:
   
   1st: Tokens are scaled ($steno_tokens_master is copied to $steno_tokens with
        scaled token information)
   2nd: Scaled tokens are inserted into a splines list. For a word this means that
        SEVERAL tokens are inserted (concatenated) and their relative coordinates
        are transformed to word coordinates. For the SE1 this seemed to be a good
        approach: Since all knots were horizontal, the x coordinates of the whole 
        word could then be shifted to the right.
   3rd: The shifting is done by calling the TiltWordInSplines function which uses 
        uses the same delta list for all points). Nonetheless, for the SE2 this is 
        a very bad idea: the x and y coordinates have to be calculated BEFORE the 
        are concatenated.
   4th: Via SmoothenEntryAndExitPoints, the whole word (splines array) is then 
        "smoothened", which means that entry and exit points are adapted according
        to pivot points if they exist. Those functions are very basic due to the
        fact that the SE1 can't handle tangent points to bezier curves (it
        actually repositions entry and exit points using a straight line between
        pivot points).
   5th: Finally, calling CalculateWord the SE1 calculated the the bezier curves
        for the whole word (splines array) in one go. This was and is a very
        efficient way to calculate stenograms can be used without any problem
        for SE2.
   6th: The splines list is finally used to create a single SVG (CreateSVG)
        or a layouted SVG (CalculateLayoutedSVG). [The only difference in 
        layouted mode, CalculateLayoutedSVG creates an array of splines
        list, each corresponding to one word; at the end of a line, the
        list is then inserted to the layouted SVG; beforehand, the same
        TokenList2WordSplines function is used, making it basically
        compatible with modifications we want to apply to single word 
        calculations).
        
    Ŝo, what can we do to "hack" the SE1 in order to integrate our backports?
  The only viable way seems to be to "hijack" the scaling function (step 1)
  since it is the last moment when coordinates can be calculated inside - i.e.
  relative to - single tokens). Unfortunately, this creates a conflict with
  the later TiltWordInSplinesFunction (which therefore has to be disabled).
  So, basically the strategy is the following:
  
  (1)   Hijack the ScaleTokens function to a ScaleAndTiltTokens function
        that scales and tilts tokens in one step and creates the $steno_tokens
        variable as a copy of $steno_tokens_master.
  (2)   The insertion of the tokens into the splines list (step 2) should work 
        the same way even if the tokens are already inclined since the width 
        of the token is the same vertically (i.e. for all y). [CROSS FINGERS 
        ABOUT THAT OTHERWISE IT'LL GET HORRIBLY COMPLICATED ... ;-)]
  (3)   The shifting (step 3) is disables (already done in step 1).
  (4)   Steps 4-6 should work without any modifications: The deal with
        the $splines variable which should contain different values now, but
        be fully compatible with all the calculations (also for single or
        layouted SVG). [AGAIN: CROSS FINGERS ...]

  Backporting the features means I high risk to corrupt the working SE1, so
  the backport will be controlled by one variable: $backport_revision1
  
        true = enable backport
        false = disable backport (use "legacy" revision 0)
        
  No - or only 2 existing - functions will be modified and it will be done
  via the mentioned "hijacking" strategy. In other words, the functions are
  hijacked when the $backport_revision1 variable is set:
  
        ScaleTokens: redirects to ScaleAndTiltTokens
        TiltWordInSplines: redirects to nothing (don't execute function)

  In addition, all other SE1-backport-function will be hold in a separate
  file (se1_backports.php) which will only be included if variable 
  $backport_revision1 is set.
  
*/

require_once "data.php";
require_once "constants.php";
require_once "rendering.php";
require_once "svgtext.php";
require_once "interpolate.php";

// SE1-BACKPORTS: revision1
// disable SE1 rev1 again: problem with vertical_compensation_x ?!
//echo "set that stuff: " . $_SESSION['model_se_revision'] . gettype($_SESSION['model_se_revision']) . "<br>";
$backport_revision1 = ($_SESSION['model_se_revision'] == 1) ? true : false;  // vertical_compensation_x is (probably) not compatible with revision1 => disable it for release 0.1!
//echo "backport_revision1: " . $backport_revision1 . gettype($backport_revision1) . "<br>";

//if ($backport_revision1) {
    require_once "se1_backports.php"; // always include se1_backports.php to make VPAINT work even if backports are disabled
//}

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

function AdjustThickness($thickness) {
    global $correction_shadow_factor;
    // adjust thickness using scaling factors
    // global adjustment (shadowed and not shadowed)
    // NOTE: $_SESSION['token_thickness'] is not taken into account here, since it is given as a parameter to CreateSVG() and DrawOneLineInLayouted()
    $adjusted_thickness = $thickness * $_SESSION['token_size'] / $correction_shadow_factor;
    // adjustment for shadowed parts
    if ($thickness > 1.0) $adjusted_thickness *= $_SESSION['token_shadow'];
    return $adjusted_thickness;
}

function CreateDeltaList( $angle ) {
    global $height_above_baseline, $height_for_delta_array;
    $deltalist = array();
    for ($y = 0; $y < $height_for_delta_array+1/*$height_above_baseline+1*/; $y++) {
        $deltalist[$y] = $y / tan( deg2rad( $angle ));
    }
    return $deltalist;
}

// SE1-BACKPORT: revision1 - disable function if $backport_revision1 is set
function TiltWordInSplines( $angle, $splines ) {
    global $backport_revision1;
if ($backport_revision1) {
    return $splines;
} else { // preserve legacy code without any modification
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
}

// SE1-BACKPORTS: revision1 (orthogonal, proporitional knots,

function ScaleAndTiltTokens($steno_tokens_temp, $factor, $angle) {
    // use exactly the same code for the moment in order to see if structure works
    //echo "ScaleAndTiltTokens()<br>";
    global $standard_height, $svg_height, $height_above_baseline, $half_upordown, $one_upordown, 
    $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide;
    //echo "ScaleTokens(): variable steno_tokens ist set (global)<br>";
    //var_dump($steno_tokens_temp);
    //foreach( $steno_tokens_temp as $token => $definition ) {    
    foreach( $steno_tokens_temp as $token => $definition) {    
        // scale informations in header
        $steno_tokens_temp[$token][0] *= $factor; // scale width
        $steno_tokens_temp[$token][4] *= $factor; // scale additional width before
        $steno_tokens_temp[$token][5] *= $factor; // scale additional width after
    
        
        //if ($token === "SP") {
               // var_dump($definition1);
               // echo "key: $token factor: $factor<br>";
               //echo "token: $token<br>";
        //}
        $definition_length = count($steno_tokens_temp[$token]);
        //echo "token: $token definition length: $definition_length<br>";
        
       // for ($i = header_length; $i < count($definition); $i += tuplet_length) {
        for ($i = header_length; $i < $definition_length; $i += tuplet_length) {
            //echo "iterate through definition ...<br>";
            $def_x = $steno_tokens_temp[$token][$i];  // orginal x-coordinate
            $def_y = $steno_tokens_temp[$token][$i+1]; // original y-coordinate
            // scale
            $steno_tokens_temp[$token][$i] *= $factor;  // x-coordinate
            $steno_tokens_temp[$token][$i+1] *= $factor; // y-coordinate
            $x = $steno_tokens_temp[$token][$i];
            $y = $steno_tokens_temp[$token][$i+1];
            //echo "i=$i: values: def_x: $def_x, def_y: $def_y; => x: $x, y: $y<br>";
            
            $legacy_dr = $steno_tokens_temp[$token][$i+offs_dr];
            //echo "legacy_dr: $legacy_dr<br>";
            
            $dr = new ContainerDRField($legacy_dr); 
            //$shiftX = $steno_tokens_temp[$token][$dr->ra_offset];
            $shiftX = $steno_tokens_temp[$token][$dr->ra_offset];
            //$scaled_shiftX = $shiftX * $factor; // don't scale shiftx here => will be scaled later!!!
            
            //echo "i: $i => get_absolut_knot_coordinates(): x: $x, y: $y, dr->knottype: " . $dr->knottype . ", shiftX: $shiftX, angle: $angle<br>";
            
            $new_point = get_absolute_knot_coordinates($x, $y, $dr->knottype, $shiftX, $angle);
            //echo "new coordinates: x: " . $new_point->x . ", y: " . $new_point->y . "<br>";
            
            //var_dump($new_point);
        
            //if ($token === "SP") {
                //echo "key: $token i: $i<br>";
                //echo "i: $i dr: ra_number: " . $dr->ra_number . " ra_offset: " . $dr->ra_offset . " shiftX: " . $shiftX . "<br>";
                //echo "old: x/y: $x/$y new: x/y: " . $new_point->x . "/" . $new_point->y . "<br>";
            //}
            
            
            $steno_tokens_temp[$token][$i] = $new_point->x;
            $steno_tokens_temp[$token][$i+1] = $new_point->y;
        }
    }
    // scale values for output in svg
    SetGlobalScalingVariables( $factor );
    //echo "stenotokens (dump): ";
    //var_dump($steno_tokens_temp);
    return $steno_tokens_temp;
}

// copy some tokens to $splines array
function ScaleTokens( $steno_tokens_temp,/*_master,*/ $factor ) {
    global $standard_height, $svg_height, $height_above_baseline, $half_upordown, $one_upordown, 
    $horizontal_distance_none, $horizontal_distance_narrow, $horizontal_distance_wide, $backport_revision1;
if ($backport_revision1) {
        //echo "execute backport revision 1<br>";
        return ScaleAndTiltTokens($steno_tokens_temp, $factor, $_SESSION['token_inclination']);
} else { // leave SE1 legacy function without any modification
    //echo "ScaleTokens(): variable steno_tokens ist set (global)<br>";
    foreach( $steno_tokens_temp as $token => $definition ) {    
        // scale informations in header
        $steno_tokens_temp[$token][0] *= $factor; // scale width
        $steno_tokens_temp[$token][4] *= $factor; // scale additional width before -- bugfix: this scaling has been forgotten in legacy SE1
        $steno_tokens_temp[$token][5] *= $factor; // scale additional width after -- bugfix: this scaling has been forgotten in legacy SE1
       
        $length = count($definition);  // optimise (this function shouldn't be called inside loop
        for ($i = header_length; $i < $length; $i += 8) {
            $steno_tokens_temp[$token][$i] *= $factor;
            $steno_tokens_temp[$token][$i+1] *= $factor;
        }
    }
    // scale values for output in svg
    SetGlobalScalingVariables( $factor );
    //echo "stenotokens (dump): ";
    //var_dump($steno_tokens_temp);
    return $steno_tokens_temp/*_master*/;
}
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

function GetControlPoints( $px0, $py0, $px1, $py1, $px2, $py2, $t1, $t2) {
    // taken from global_functions.js
    // returns control points for p1
    $d01 = sqrt(pow($px1-$px0,2)+pow($py1-$py0,2));
    $d12 = sqrt(pow($px2-$px1,2)+pow($py2-$py1,2));
    $fa = $t1*$d01 / ($d01+$d12);
    $fb = $t2*$d12 / ($d01+$d12);
    $c1x = $px1 - $fa*($px2-$px0);
    $c1y = $py1 - $fa*($py2-$py0);
    $c2x = $px1 + $fb*($px2-$px0);
    $c2y = $py1 + $fb*($py2-$py0);
    return array( $c1x, $c1y, $c2x, $c2y );
/* 
    var d01=Math.sqrt(Math.pow(p0.x-p1.x,2)+Math.pow(p1.y-p0.y,2)); // 01 vs 10 - this is probably a bug in JS-code?!?
    var d12=Math.sqrt(Math.pow(p2.x-p1.x,2)+Math.pow(p2.y-p1.y,2));
    var fa=t1*d01/(d01+d12);   // scaling factor for triangle Ta
    var fb=t2*d12/(d01+d12);   // ditto for Tb, simplifies to fb=t-fa
    var p1x=p1.x-fa*(p2.x-p0.x);    // x2-x0 is the width of triangle T
    var p1y=p1.y-fa*(p2.y-p0.y);    // y2-y0 is the height of T
    var p2x=p1.x+fb*(p2.x-p0.x);
    var p2y=p1.y+fb*(p2.y-p0.y);  
    return [ new Point( p1x, p1y ), new Point( p2x, p2y ) ];
*/
}
/*
function AvoidDivisionBy0($x) {
  if ($x == 0) return (float)0.000000000000001; // yes ... learn to live with imperfections ... ! :x)  
} 
*/
function CalculateBezierPoint($p1x, $p1y, $c1x, $c1y, $p2x, $p2y, $c2x, $c2y, $percent) {
	// considers length of complete bezier curve as 100%
	// calculates point situated at percent percent of the curve (starting at p1)
	// returns coordinates of point and m of tangent
	// calculate 3 outer lines 
	//echo "CalculateBezierPoint():<br>P1($p1x/$p1y) C1($c1x/$c1y) P2($p2x/$p2y) C2($c2x/$c2y)<br>";
    // note: code comes from global_functions.js
    $dx1 = $c1x - $p1x;
    $dy1 = $c1y - $p1y;
    $dx2 = $c2x - $c1x;
    $dy2 = $c2y - $c1y;
    $dx3 = $p2x - $c2x;
    $dy3 = $p2y - $c2y;
	// calculate 2 inner lines
	// coordinates
	$factor = 1 / 100 * $percent;
	//console.log("factor: ", factor);
	$ix1 = $p1x + $dx1 * $factor;
    $iy1 = $p1y + $dy1 * $factor;
    $ix2 = $c1x + $dx2 * $factor;
    $iy2 = $c1y + $dy2 * $factor;
    $ix3 = $c2x + $dx3 * $factor;
    $iy3 = $c2y + $dy3 * $factor;
	// deltas
	$dix1 = $ix2 - $ix1;
    $diy1 = $iy2 - $iy1;
    $dix2 = $ix3 - $ix2;
    $diy2 = $iy3 - $iy2;
	// calculate last inner line that touches bezier curve
	// coordinates
	$tx1 = $ix1 + $dix1 * $factor;
    $ty1 = $iy1 + $diy1 * $factor;
    $tx2 = $ix2 + $dix2 * $factor;
    $ty2 = $iy2 + $diy2 * $factor;
	// deltas
	$dtx = $tx2 - $tx1;
    $dty = $ty2 - $ty1; // avoid division by 0 for bm!?
	// calculate bezier point (coordinates and m)
	$bx = $tx1 + $dtx * $factor;
    $by = $ty1 + $dty * $factor;
    //$bm = $dtx / AvoidDivisionBy0($dty);
	return array($bx, $by); //, $bm);  // bm is not necessary here 
}

function CalculateWord( $splines ) {     // parameter $splines
        global $global_interpolation_debug_svg;
        // $global_interpolation_debug_svg = "";
        // interpolate 
        if ($_SESSION['interpolated_yesno']) 
            for ($i=0; $i<$_SESSION['interpolated_iterations']; $i++) $splines = InterpolateSpline($splines);
            
        // define length
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
            //echo "CalculateWord: ($x0/$y0)-$ta-($x1/$y1)-$tb-($x2/$y2)=>($p1x/$p1y)($p2x/$p2y)<br>";
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
         global $border_margin, $baseline_y;
         $left_x = 9999; $right_x = -9999;
         $left_y = 9999; $right_y = 9999;
         $length_splines = count( $splines );
         // first find left_x / right_x;
         for ($i = 0; $i < $length_splines; $i += tuplet_length) {
                $test_x = $splines[$i+offs_x1];
                $test_y = $splines[$i+offs_y1];
                if ($test_x < $left_x) { $left_x = $test_x; $left_y = $test_y; }
                if ($test_x > $right_x) { $right_x = $test_x; $right_y = $test_y; }
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
         
         // fix problem of exagerated spacing between very high words and low words that follow immediately each other
         // not beautiful for the human eye
         // method: correct (reduce) width of word according to angle and y position of left_x / right_x
         //echo "TrimSplines: left: ($left_x / $left_y) right: ($right_x / $right_y) width: $width<br>";
         //echo "Baseline: $baseline_y (Session: " . $_SESSION['baseline'] . " * TokenSize: " . $_SESSION['token_size'] . " * 10) Angle: " . $_SESSION['token_inclination'] . "<br>";
         // ok, width can't be corrected here because it breaks aligning to left and right margin
         // => return the values and try to solve that in calling function
         $correction_lx = 0; $correction_rx = 0; // don't leave this variables undefined
         if ($_SESSION['layouted_correct_word_width']) {
                // correct left_x
                $delta_y = $baseline_y - $left_y;
                $temp_x = $delta_y / tan( deg2rad( $_SESSION['token_inclination'] ));
                if ($temp_x < 0) $correction_lx = $temp_x;
                //echo "Correction: delta_y: $delta_y left_x: $temp_x => width: $width<br>";
                // correct right_x
                $delta_y = $baseline_y - $right_y;
                $temp_x = $delta_y / tan( deg2rad( $_SESSION['token_inclination'] ));
                if ($temp_x > 0) $correction_rx = -$temp_x;
                //echo "Correction: delta_y: $delta_y right_x: $temp_x => width: $width<br>";
         }
         return array( $splines, $width, $correction_lx, $correction_rx );
}

function InsertAuxiliaryLines( $width ) {
    global $standard_height;
    $lines_string = "";
    if ($_SESSION['auxiliary_upper3yesno']) {
        $thickness = $_SESSION['auxiliary_upper3_thickness'];
        $color = $_SESSION['auxiliary_upper3_color'];
        $stroke_dasharray = $_SESSION['upper3_style'];
        $tempy = 1 * $standard_height;
           $lines_string .= "<line x1=\"0\" y1=\"$tempy\" x2=\"$width\" y2=\"$tempy\" stroke-dasharray=\"$stroke_dasharray\" style=\"stroke:$color;stroke-width:$thickness\" />";
    }
    if ($_SESSION['auxiliary_upper12yesno']) {
        $thickness = $_SESSION['auxiliary_upper12_thickness'];
        $color = $_SESSION['auxiliary_upper12_color'];
        $stroke_dasharray = $_SESSION['upper12_style'];
        for ($i = 2; $i <= 3; $i++) {
            $tempy = $i * $standard_height;
            $lines_string .= "<line x1=\"0\" y1=\"$tempy\" x2=\"$width\" y2=\"$tempy\" stroke-dasharray=\"$stroke_dasharray\" style=\"stroke:$color;stroke-width:$thickness\" />";
        }
    }
    if ($_SESSION['auxiliary_baselineyesno']) {
        $thickness = $_SESSION['auxiliary_baseline_thickness'];
        $color = $_SESSION['auxiliary_baseline_color'];
        $stroke_dasharray = $_SESSION['baseline_style'];
        $tempy = 4 * $standard_height;
        $lines_string .= "<line x1=\"0\" y1=\"$tempy\" x2=\"$width\" y2=\"$tempy\" stroke-dasharray=\"$stroke_dasharray\" style=\"stroke:$color;stroke-width:$thickness\" />";
    
    }
    if ($_SESSION['auxiliary_loweryesno']) {
        $thickness = $_SESSION['auxiliary_lower_thickness'];
        $color = $_SESSION['auxiliary_lower_color'];
        $stroke_dasharray = $_SESSION['lower_style'];
        $tempy = 5 * $standard_height;
        //echo "thickness: $thickness color: $color dasharray: $stroke_dasharray tempy: $tempy<br>";
        $lines_string .= "<line x1=\"0\" y1=\"$tempy\" x2=\"$width\" y2=\"$tempy\" stroke-dasharray=\"$stroke_dasharray\" style=\"stroke:$color;stroke-width:$thickness\" />";
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

function InsertGrid($width) {
    global $standard_height, $space_before_word;
    // define values
    $shift_x = $space_before_word;
    $grid_string = "";
    $leftx = 0; $rightx = $width;
    $stroke_dasharray = "1,1";
    $color = $_SESSION['token_color']; // make it work with inverted mode
    $csc = "red"; // color screen coordinates
    $coc = ($color === "white") ? "yellow" : "blue"; // color original coordinates
    $thickness = 1;
    $thickness_border = 2;
    $bottom = 6*$standard_height;
    $top = 0;
    $standard_width = 10*$_SESSION['token_size'];
    
    // add horizontal lines
    for ($y=$bottom; $y>=$top; $y-=$standard_height) {
        $grid_string .= "<line x1=\"$leftx\" y1=\"$y\" x2=\"$rightx\" y2=\"$y\" stroke-dasharray=\"$stroke_dasharray\" style=\"stroke:$color;stroke-width:$thickness\" />";
    }

    // add vertical lines
    for ($x=$leftx+$shift_x; $x<=$rightx+$shift_x; $x+=$standard_width) {
        $grid_string .= "<line x1=\"$x\" y1=\"$top\" x2=\"$x\" y2=\"$bottom\" stroke-dasharray=\"$stroke_dasharray\" style=\"stroke:$color;stroke-width:$thickness\" />";
    }

    // add borders
    $grid_string .= "<rect width=\"$rightx\" height=\"$bottom\" style=\"stroke-width:$thickness_border;stroke:$color;$fill:white;fill-opacity:0\" />"; 
    
    // add labels
    // screen coordinates
    // horizontal
    for ($x=$leftx+$shift_x; $x<=$rightx+$shift_x; $x+=$standard_width) {
        $posx = $x+5;
        $posy =  18; //3 * $_SESSION['token_size'];
        $grid_string .= "<text x=\"$posx\" y=\"$posy\" fill=\"$csc\">$x</text>"; 
    }
    
    // vertical
    for ($y=$standard_height; $y<=$bottom; $y+=$standard_height) {
        $posx = $shift_x - 8;
        $posy = $y + 18; //3 * $_SESSION['token_size'];
        $grid_string .= "<text x=\"$posx\" y=\"$posy\" fill=\"$csc\">$y</text>"; 
    }

    // original coordinates
    // horizontal
    $orig_x = 0;
    for ($x=$leftx; $x<=$rightx; $x+=$standard_width) {
        $posx = $shift_x + $x + 5;
        $posy =  $bottom - 12; // * $_SESSION['token_size'];
        $grid_string .= "<text x=\"$posx\" y=\"$posy\" fill=\"$coc\">$orig_x</text>"; 
        $orig_x += 10;
    }
    
    // vertical
    $orig_y = 30;
    for ($y=$standard_height; $y<=$bottom; $y+=$standard_height) {
        $posx = $width - 24; //4 * $_SESSION['token_size'];
        $posy = $y + 18; //3 * $_SESSION['token_size'];
        $grid_string .= "<text x=\"$posx\" y=\"$posy\" fill=\"$coc\">$orig_y</text>"; 
        $orig_y -= 10;
    }

    
    return $grid_string; 
}

function CreateSVG( $splines, $x, $width, $stroke_width, $color_htmlrgb, $stroke_dasharray, $alternative_text ) {
    global $svg_height, $standard_height, $html_comment_open, $space_before_word, $svg_not_compatible_browser_text, $vector_value_precision,
    $combined_pretags, $separate_spline, $space_before_word;
    global $global_interpolation_debug_string, $global_interpolation_debug_svg;
   
    $shift_x = $space_before_word ; // use session-variable for $space_before_word when implemented // don't multiply with $_SESSION['token_size']; (consider both values as absolute ?!) 
    $csc = "red"; // color screen coordinates
    $coc = ($_SESSION['token_color'] === "white") ? "yellow" : "blue"; // color original coordinates
    
    // text list with coordinates of points
    $points_list = "";
    
    // calculate polygon shadow before generating svg
    // reason: in some cases GetPolygon() has to adjust values in splines
    // these modifications have to occur before so that the take effect in the final svg
    if ($_SESSION['rendering_polygon_yesno']) list($polygon_shadow,$splines) = GetPolygon( $splines, $space_before_word, 0 );
    else $polygon_shadow = "";
    
    //list( $splines, $width ) = TrimSplines( $splines );
    $pre = $combined_pretags;
    // if ($_SESSION['token_type'] !== "htmlcode") {
        //echo "CreateSVG: Pre: $pre Post: $post<br>";
        list( $variable, $newcolor_htmlrgb ) = GetTagVariableAndValue( $pre ); 
        //if (mb_strlen($newcolor_htmlrgb) > 0) $color_htmlrgb = $newcolor_htmlrgb;
        //echo "CreateSVG:<br>Pre: $pre<br>Post: $post<br>colorhtmlrgb: $color_htmlrgb<br>";
        //if (mb_strlen($pre)>0) ParseAndSetInlineOptions( $pre );        // set inline options
        $svg_string = "<svg width=\"$width\" height=\"$svg_height\"><title>$alternative_text</title><g stroke-linecap=\"miter\" stroke-linejoin=\"miter\" stroke-miterlimit=\"20\">\n"; // stroke-linejoin=\"round\" stroke-dasharray=\"2,2\">";
        // draw grid or auxiliary lines
        if ($_SESSION['debug_show_grid_yesno']) $svg_string .= InsertGrid($width);
        else $svg_string .= InsertAuxiliaryLines( $width ); // don't draw auxiliary lines when grid is selected
        
        $svg_string .= "\n"; // separate line from curves in html
        $array_length = count( $splines );

        // add word to svg
        for ($n = 0; $n <= $array_length - (tuplet_length*2); $n += tuplet_length) {
            
            $testx = $splines[$n];
            $testy = $splines[$n+1];
            
            $x1 = round($splines[$n] + $shift_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $y1 = round($splines[$n+1], $vector_value_precision, PHP_ROUND_HALF_UP);
            $q1x = round($splines[$n+2] + $shift_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $q1y = round($splines[$n+3], $vector_value_precision, PHP_ROUND_HALF_UP);
            $relative_thickness = ($_SESSION['rendering_middleline_yesno']) ? $splines[$n+4] : 1.0;
            $unused = $splines[$n+5];
            $q2x = round($splines[$n+6] + $shift_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $q2y = round($splines[$n+7], $vector_value_precision, PHP_ROUND_HALF_UP);
            $x2 = round($splines[$n+8] + $shift_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $y2 = round($splines[$n+9], $vector_value_precision, PHP_ROUND_HALF_UP);
            
            //$absolute_thickness = $stroke_width * $relative_thickness * $_SESSION['token_size'] / $correction_shadow_factor * $_SESSION['token_shadow']; // echo "splines($n+8+offs_dr) = " . $splines[$n+8+5] . " / thickness(before) = $absolute_thickness / ";
            $absolute_thickness = $stroke_width * AdjustThickness($relative_thickness);
                
            // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
            // this method doesn't work with n, m, b ... why???
            if ($splines[$n+(1*tuplet_length)+offs_dr] == draw_no_connection) { $absolute_thickness = 0; /*$color_htmlrgb="red";*/ /*$x2 = $x1; $y2 = $y1;*/} //echo "absolute_thickness(after) = $absolute_thickness<br>"; // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
            // correct control points if following point is non-connecting (see CalculateWord() for more detail)
            // search 2 tuplets ahead because data af knot 2 is stored in preceeding knot 1 (so knot 3 contains draw_no_connection info at offset offs_dr) 
            if ($splines[$n+(2*tuplet_length)+offs_dr] == draw_no_connection) { $q2x = $x2; $q2y = $y2; } 
            $svg_string .= "<path d=\"M $x1 $y1 C $q1x $q1y $q2x $q2y $x2 $y2\" stroke-dasharray=\"$stroke_dasharray\" stroke=\"$color_htmlrgb\" stroke-width=\"$absolute_thickness\" shape-rendering=\"geometricPrecision\" fill=\"none\" />\n";        
            // add knots
            if ($_SESSION['debug_show_points_yesno']) {
                $svg_string .= "<circle cx=\"$x1\" cy=\"$y1\" r=\"2\" stroke=\"$csc\" stroke-width=\"1\" fill=\"$csc\" />";
                $point_i = $n / tuplet_length + 1;
                $points_list .= "<font color=\"$csc\">P($point_i): $x1 / $y1";
                // original coordinates
                $ox = round(($x1-$shift_x) / $_SESSION['token_size'], $vector_value_precision, PHP_ROUND_HALF_UP);
                $oy = round(((4*$standard_height) - $y1) / $_SESSION['token_size'], $vector_value_precision, PHP_ROUND_HALF_UP);
                $points_list .= "<font color=\"" . $_SESSION['token_color'] . "\"> <=> <font color=\"$coc\">O($point_i): $ox / $oy<br>";
            }
        }
        // add last knot
        if ($_SESSION['debug_show_points_yesno']) { 
            $svg_string .= "<circle cx=\"$x2\" cy=\"$y2\" r=\"2\" stroke=\"red\" stroke-width=\"1\" fill=\"red\" />";
            $svg_string .= "\n$global_interpolation_debug_svg\n";
            $point_i = $n / tuplet_length + 1;
            $points_list .= "<font color=\"$csc\">P($point_i): $x2 / $y2";
            // original coordinates
            $ox = round(($x2-$shift_x) / $_SESSION['token_size'], $vector_value_precision, PHP_ROUND_HALF_UP);
            $oy = round(((4*$standard_height) - $y2) / $_SESSION['token_size'], $vector_value_precision, PHP_ROUND_HALF_UP);
            $points_list .= "<font color=\"" . $_SESSION['token_color'] . "\"> <=> <font color=\"$coc\">O($point_i): $ox / $oy<br>";
            $points_list .= "<font color=\"" . $_SESSION['token_color'] . "\">";
            $points_list .= "<br>ShiftX: $shift_x<br>";
            $points_list .= "Factor: " . $_SESSION['token_size'];
            // dont show svg debug text
            //$points_list .= "<p><br>" .  htmlspecialchars($global_interpolation_debug_svg) . "</p>";
        }
        
        // add separate_spline to svg
        //echo "create svg for separate_spline<br>";
        //var_dump($separate_spline); echo "<br>";
        $array_length = count( $separate_spline );
        for ($n = 0; $n <= $array_length - (tuplet_length*2); $n += tuplet_length) {
            
            $x1 = round($separate_spline[$n] + $shift_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $y1 = round($separate_spline[$n+1], $vector_value_precision, PHP_ROUND_HALF_UP);
            $q1x = round($separate_spline[$n+2] + $shift_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $q1y = round($separate_spline[$n+3], $vector_value_precision, PHP_ROUND_HALF_UP);
            $relative_thickness = $separate_spline[$n+4];
            $unused = $separate_spline[$n+5];
            $q2x = round($separate_spline[$n+6] + $shift_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $q2y = round($separate_spline[$n+7], $vector_value_precision, PHP_ROUND_HALF_UP);
            $x2 = round($separate_spline[$n+8] + $shift_x, $vector_value_precision, PHP_ROUND_HALF_UP);
            $y2 = round($separate_spline[$n+9], $vector_value_precision, PHP_ROUND_HALF_UP);
          
            // corrections for upper / lower position
            /*$x1 -= 5;
            $q1x -= 5;
            $x2 -= 5;
            $q2x -= 5;
            */
            
            
            $absolute_thickness = $stroke_width * $relative_thickness; // echo "splines($n+8+offs_dr) = " . $splines[$n+8+5] . " / thickness(before) = $absolute_thickness / ";
            // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
            // this method doesn't work with n, m, b ... why???
            if ($separate_spline[$n+(1*tuplet_length)+offs_dr] == draw_no_connection) { $absolute_thickness = 0; } //echo "absolute_thickness(after) = $absolute_thickness<br>"; // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
            // correct control points if following point is non-connecting (see CalculateWord() for more detail)
            // search 2 tuplets ahead because data af knot 2 is stored in preceeding knot 1 (so knot 3 contains draw_no_connection info at offset offs_dr) 
            if ($separate_spline[$n+(2*tuplet_length)+offs_dr] == draw_no_connection) { $q2x = $x2; $q2y = $y2; } 
            // red instead of $color_htmlrgb
            $svg_string .= "<!-- separate --><path d=\"M $x1 $y1 C $q1x $q1y $q2x $q2y $x2 $y2\" stroke-dasharray=\"$stroke_dasharray\" stroke=\"$color_htmlrgb\" stroke-width=\"$absolute_thickness\" shape-rendering=\"geometricPrecision\" fill=\"none\" />\n";        
        }
       
       
        
        $svg_string .= "$polygon_shadow</g>$svg_not_compatible_browser_text</svg>";
        //if (mb_strlen($post)>0) ParseAndSetInlineOptions( $post );        // set inline options
    // } 
    // add points string if not empty
    if (mb_strlen($points_list)>0) $svg_string .= "\n<p>$points_list</p>\n";
    
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
        $standard_height, $baseline_y, $dont_connect, $separate_spline, $backport_revision1;
        $token_definition_length = count( $steno_tokens[$token] );           // continue splines-list
        //echo "stenotokens($token) - DUMP: ";
       // var_dump($steno_tokens["IST"]);
       //echo "START: InsertToken(): token = $token distance = $distance actual_x = $actual_x<br>";
        $shadowed = ($steno_tokens[$token][offs_token_type] == 1) ? "yes" : $shadowed;
        
        //$old_dont_connect = $dont_connect;
        $late_entry_position = GetLateEntryPoint( $steno_tokens[$token] );
        //echo "Token: $token - LateEntry: $late_entry_position<br>";
        // if there is a late_entry-position and the token is at beginning of tokenlist (i.e. $position === "first") => set $start_position (= values will be inserted from here on) to $entry_position; otherwise start as usual at beginning (position 0 after header)
        if (($late_entry_position) && ($position === "first")) $start_position = $late_entry_position;
        else $start_position = 0;
        //echo "token: $token position: $position startposition: $start_position<br>";
        //echo "InserTokenInSplinesList(): $token<br>";
        //var_dump($steno_tokens);
        
    if ( count( $steno_tokens[$token] > 0)) { ///????
        // ********************** header operations *************************************
        // if token is prefix then adjust actual_y
        // add inconditional deltay to token if specified in token_list
        $old_y = $actual_y;
        $actual_y -= $steno_tokens[$token][offs_inconditional_delta_y_before] * $standard_height;
        
        
        //echo "vertical_compensation_x: $vertical_compensation_x<br>";
        // PROBLEM WITH vertical_compensation_x and backport_revision1: (?!)
        // visible in german "steckte": [&T][&E] not correctly rendered when SE1 rev1 is selected!
        $vertical_compensation_x = ($old_y-$actual_y) / tan(deg2rad($_SESSION['token_inclination']));
        if (!($backport_revision1)) $vertical_compensation_x = 0; // disable vertical compensation (compatibility issue with revision1) - revision0 doesn't need compensation!
        //echo "inconditional_delta_y_before: vertical_compensation_x = $vertical_compensation_x<br>";
        $actual_x += $vertical_compensation_x; // vertical compensation for actual_x is necessary!
                            
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
                $old_y = $actual_y;
                $actual_y -= $alternative_y * $factor;
                $vertical_compensation_x = ($old_y-$actual_y) / tan(deg2rad($_SESSION['token_inclination']));
                if (!($backport_revision1)) $vertical_compensation_x = 0; // disable vertical compensation (compatibility issue with revision1) - revision0 doesn't need compensation!
        
                //echo "alternative_exit_point: vertical_compensation_x = $vertical_compensation_x<br>"; // leave this line active to find error if necessary
                $actual_x += $vertical_compensation_x; // vertical compensation for actual_x is necessary! // untested!
            }
        }
        
        // if actual token must be higher or lower and if it has to be joined narrow or wide adjust actual_x and actual_y before insertion
        switch( $distance ) {
            case "none" : $actual_x += $_SESSION['token_distance_none'] * $_SESSION['token_size']; /*$horizontal_distance_narrow;*/ break;
            case "narrow" : $actual_x += $_SESSION['token_distance_narrow'] * $_SESSION['token_size']; /*echo "add narrow: " . $_SESSION['token_distance_narrow'] * $_SESSION['token_size'] . "<br>";*/ /*$horizontal_distance_narrow;*/ break;
            case "wide" : $actual_x += $_SESSION['token_distance_wide'] * $_SESSION['token_size']; /*$horizontal_distance_wide;*/ break;
        }
        switch ( $vertical ) {
                case "up" : $old_y = $actual_y;
                            $actual_y -= ($steno_tokens[$token][offs_delta_y_before] * $standard_height); 
                            $vertical_compensation_x = ($old_y-$actual_y) / tan(deg2rad($_SESSION['token_inclination']));
                            if (!($backport_revision1)) $vertical_compensation_x = 0; // disable vertical compensation (compatibility issue with revision1) - revision0 doesn't need compensation!
        
                            //echo "vertical_compensation_x = $vertical_compensation_x<br>";
                            $actual_x += $vertical_compensation_x; // vertical compensation for actual_x is necessary!
                            break;
                case "down" : $old_y = $actual_y; 
                            $actual_y += $half_upordown /*0.5 * $standard_height*/; 
                            $vertical_compensation_x = ($old_y-$actual_y) / tan(deg2rad($_SESSION['token_inclination']));
                            if (!($backport_revision1)) $vertical_compensation_x = 0; // disable vertical compensation (compatibility issue with revision1) - revision0 doesn't need compensation!
        
                            //echo "vertical_compensation_x = $vertical_compensation_x<br>";
                            $actual_x += $vertical_compensation_x; // vertical compensation for actual_x is necessary!
                            break; 
        }   
        $old_dont_connect = $dont_connect;
        $dont_connect = $steno_tokens[$token][offs_dont_connect]; // echo "$token: old_dont_connect=$old_dont_connect / dont_connect = $dont_connect<br>";
        if ($dont_connect == 1) $actual_y = $baseline_y;
        //echo "before data operations: token: $token coordinates: ($actual_x/$actual_y)<br>";
        // ******************************** data operations *************************************************
        //echo "start data operations ...<br>"; 
        // start with $i after header (offset header_length)
        $stop_inserting = FALSE; 
        $initial_splines_length = count($splines);
        
        if ($initial_splines_length > 0) {
            if ($steno_tokens[$token][offs_token_type] != 4) {
                // set tension for preceeding point at offset 7 from header offset 3 of token to insert
                $splines[$initial_splines_length - 1] = $steno_tokens[$token][$i+offs_tension_before];
                //echo "<br>token = $token set preceeding tension = " . $splines[$initial_splines_length - 1] . "<br>";
                //if ($token === "CH") echo "first tension at " . ($initial_splines_length - 1) . " set to " .  $steno_tokens[$token][$i+offs_tension_before];
            } else {
                // if type == 4 (<=> part of a token), don't correct preceeding tension, because if points fall together, they are "joined"
                // in this case, the entry tension must be from 1st point and the exit tension must be from 2nd point
                // leaving the existing tension as it is, makes sure that entry tension will be from preceeding knot
                // the else-branch is superfluous here - just leave it to make it more clear that this case exists
               //echo "<br>type 4 (part of a token): don't overwrite tension of preceeding knot<br>"; 
            }
        }
        $preceeding_point_x = -99;
        $preceeding_point_y = -99;
        $splines_length = count($splines);
        if ($initial_splines_length > 0) {
            $preceeding_point_x = $splines[$splines_length-8];
            $preceeding_point_y = $splines[$splines_length-8+1];
        }
            
        for ($i = header_length+$start_position * tuplet_length; $i < $token_definition_length; $i += tuplet_length) {
            //echo "token: $token i = $i<br>";
              $insert_this_point = TRUE;
            //$pt_type_entry = $steno_tokens[$token][$i+offs_d1];
            $pt_type_entry = ($steno_tokens[$token][$i+offs_d1] == 98) ? 1 : $steno_tokens[$token][$i+offs_d1]; // not sure if this is correct ... ?! maybe distinguish: if token is first position => transform 98 to 1; if token is inside or last position => transform 98 to 0 (!?)
            $pt_type_exit = $steno_tokens[$token][$i+offs_d2];
            // dont insert: (1) connecting points, (2) intermediate shadow points, if token is not shadowed, 
            
            // test for polygon rendering
            // don't insert intermediate shadow points if polygon rendering is active
            //$_SESSION['rendering_intermediatshadowpoints_yesno'] = false;
            //echo "intermediateyesno: #" . $_SESSION['rendering_intermediateshadowpoints_yesno'] . "#<br>";
            //echo "polygonyesno: #" . $_SESSION['rendering_polygon_yesno'] . "#<br>";
            if (($_SESSION['rendering_polygon_yesno']) && (!$_SESSION['rendering_intermediateshadowpoints_yesno']) && ($steno_tokens[$token][$i+offs_d1] == 5)) {
                //echo "tuplet: $i => don't insert (intermediate shadow point with polygon rendering)<br>";
                $insert_this_point = false;
            } //else echo "tuplet: $i => insert<br>";
        
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
                if ($steno_tokens[$token][offs_interpretation_y_coordinates] == 1) { 
                    //echo "$token: use absolute y = $baseline_y"; 
                    $y_interpretation = $baseline_y; $actual_y = $baseline_y; 
                } // offset 18 indicates if y-coordinates are relative or absolute
                else $y_interpretation = $actual_y;
                
                //echo "<br>token: $token i=$i steno_tokens(x/y): (" . $steno_tokens[$token][$i] . "/" . $steno_tokens[$token][$i+offs_y1] . ")";
                $new_x = $steno_tokens[$token][$i] + $actual_x + $steno_tokens[$token][offs_additional_x_before];
                $new_y = $y_interpretation - $steno_tokens[$token][$i+offs_y1];
                //echo "<br>token: $token type = " . $steno_tokens[$token][offs_token_type] . " actual_x/y: $actual_x/$actual_y<br>";
                //echo "newx/y: $new_x/$new_y preceeding_point_x/y: $preceeding_point_x/$preceeding_point_y<br>";
               
                //if (($steno_tokens[$token][offs_token_type] != 4) || (($preceeding_point_x !== $new_x) || ($preceeding_point_y !== $new_y))) {
                //if (($steno_tokens[$token][offs_token_type] !== 4) || (!(($preceeding_point_x === $new_x) or ($preceeding_point_y === $new_y)))) {
                if (($steno_tokens[$token][offs_token_type] !== 4) || 
                    ($steno_tokens[$token][offs_token_type] === 4) && (!(($preceeding_point_x === $new_x) && ($preceeding_point_y === $new_y)))) {
                
                    $x1_t = $steno_tokens[$token][$i+offs_x1];
                    $y1_t = $steno_tokens[$token][$i+offs_y1];
                    $t1_t = $steno_tokens[$token][$i+offs_t1];
                    $d1_t = $steno_tokens[$token][$i+offs_d1];
                    $th_t = $steno_tokens[$token][$i+offs_th];
                    $dr_t = $steno_tokens[$token][$i+offs_dr];
                    $d2_t = $steno_tokens[$token][$i+offs_d2];
                    $t2_t = $steno_tokens[$token][$i+offs_t2];
                    
                    //echo "insert knot: { $x1_t, $y1_t, $t1_t, $d1_t, $th_t, $dr_t, $d2_t, $t2_t } <br>";
                    if (($dr_t == 2) || ($dr_t == 3)) {
                        // this knot belongs to a diacritic token => insert it into separate spline
                        //echo "insert this knot into separate spline<br>";
                        
                        $x1_t = $x1_t + $actual_x + $steno_tokens[$token][offs_additional_x_before];     // calculate coordinates inside splines (svg) adding pre-offset for x
                        $y1_t = $y_interpretation - $y1_t;            // calculate coordinates inside splines (svg) $actual_y is wrong!
                        $t1_t = $t1_t;                        // tension following the point
                        $d1_t = $d1_t; // diacritic tokens CANNOT contain pivot points
                        
                        if (($shadowed === "yes") /*&& ($_SESSION['rendering_middleline_yesno'])*//*|| ($steno_tokens[$token][offs_token_type] == 1)*/) {
                            $th_t = $th_t;  // th = relative thickness of following spline (1.0 = normal thickness)
                        } else $th_t = 1.0;
                        //$tempdr = (($old_dont_connect) && ($i+offsdr < header_length+tuplet_length)) ? 5 : $steno_tokens[$token][$i+offs_dr]; $splines[] = $tempdr; //echo "$token" . "[" . $i . "]:  old_dont_connect = $old_dont_connect / dr = $tempdr<br>";                       // dr
                        //$value_to_insert = $exit_point_type;
                        // $splines[] = $value_to_insert; //$exit_point_type;              // earlier version: $steno_tokens[$token][$i+6];                        // d2
                        //$dr_t = 0; //$dr_t; ?
                        $dr_t = ($dr_t == 3) ? 5 : 0; // 3 => 5 (non connecting) ; 2 => 0 (connecting)
                        $d2_t = $d2_t;
                        $t2_t = $t2_t;
                
                        // correct vertical deltax (according to higher / lower position)
                        $vertical_compensation_x = ($old_y-$actual_y) / tan(deg2rad($_SESSION['token_inclination']));
                        // NOTE: This compensation corrects vertical deltax and thus improves the position of diacritics.
                        // Nonetheless, diacritics are still not 100% accurate. Probably, this has to do with the spacer:
                        // not sure if spacing is applied correctly to diacritics (it is applied correctly to tokens though).
                        // This needs further investigation inside generation of SVG (since spacer distances are only applied then).
                        
                        // some code snippets for vertical compensation
                        //echo "token: $token old_y: $old_y vs actual_y: $actual_y vertical_compensation_x: $vertical_compensation_x<br>";
                        //$compensation_x = $raw_x * $_SESSION['token_shadow'] * $_SESSION['token_thickness'];
                        //$compensation_y = (-$raw_y) * $_SESSION['token_shadow'] * $_SESSION['token_thickness'];
                        //$delta_x = (-$compensation_y) / tan( deg2rad( $_SESSION['token_inclination'] ));
                        //$compensation_x += $delta_x;
                        
                        //echo "final tuplet for separate_spline: { $x1_t, $y1_t, $t1_t, $d1_t, $th_t, $dr_t, $d2_t, $t2_t } <br>";
                        $separate_spline[] = $x1_t+$vertical_compensation_x;
                        $separate_spline[] = $y1_t;
                        $separate_spline[] = $t1_t;
                        $separate_spline[] = $d1_t;
                        $separate_spline[] = $th_t;
                        $separate_spline[] = $dr_t;
                        $separate_spline[] = $d2_t;
                        $separate_spline[] = $t2_t;
                        
                    } else {
                        
                        // if shadowed and combined => compensate x/y with border vector (offsets 10 and 11)
                        // only do this from the second knot on (first knot has to stay where it is otherwhise base token will be deformed ...)
                        $actual_length = count($splines);
                        $preceeding_knot_type = $splines[$actual_length-tuplet_length+offs_d1];
                        if (($d1_t == 3) && ($preceeding_knot_type === 3) && ($shadowed === "yes")) {
                            //echo "insert knot: { $x1_t, $y1_t, $t1_t, $d1_t, $th_t, $dr_t, $d2_t, $t2_t } <br>";
                            //echo "knot type: $d1_t<br>";  
                            $raw_x = $steno_tokens[$token][offs_bvectx];
                            $raw_y = $steno_tokens[$token][offs_bvecty];
                            //echo "rawx/y: ($raw_x, $raw_y)<br>";
                            $compensation_x = $raw_x * $_SESSION['token_shadow'] * $_SESSION['token_thickness'];
                            $compensation_y = (-$raw_y) * $_SESSION['token_shadow'] * $_SESSION['token_thickness'];
                            $delta_x = (-$compensation_y) / tan( deg2rad( $_SESSION['token_inclination'] ));
                            $compensation_x += $delta_x;
                            //echo "compensation: ($compensation_x, $compensation_y)<br>";
                        } else {
                            $compensation_x = 0;
                            $compensation_y = 0;
                        }
                        
                        $splines[] = $steno_tokens[$token][$i] + $actual_x + $steno_tokens[$token][offs_additional_x_before] + $compensation_x;     // calculate coordinates inside splines (svg) adding pre-offset for x
                        $splines[] = $y_interpretation - $steno_tokens[$token][$i+offs_y1] + $compensation_y;            // calculate coordinates inside splines (svg) $actual_y is wrong!
                        
                        $splines[] = $steno_tokens[$token][$i+offs_t1];                        // tension following the point
                        // echo "insert tension t1: " . $steno_tokens[$token][$i+offs_t1] . "<br>";
                        // pivot point: if entry/exit point is conditional pivot (= value 3) 
                        // (1) if token in normal position or down => insert pivot as normal point (= value 0)
                        // (2) if token in up position => insert normal pivot point (= value 2)
                        $value_to_insert = $steno_tokens[$token][$i+offs_d1];
                    
                        //if ($value_to_insert == conditional_pivot_point) {
                        //  if ($vertical !== "up") $value_to_insert = 0;
                        //else $value_to_insert = 2;
                        //}
                    
                        $splines[] = $value_to_insert;                        // d1
                        if (($shadowed == "yes") /*&& ($_SESSION['rendering_middleline_yesno'])*//* || ($steno_tokens[$token][offs_token_type] == "1")*/) {
                            $splines[] = $steno_tokens[$token][$i+offs_th];  // th = relative thickness of following spline (1.0 = normal thickness)
                        } else $splines[] = 1.0;
                        $tempdr = (($old_dont_connect) && ($i+offsdr < header_length+tuplet_length)) ? 5 : $steno_tokens[$token][$i+offs_dr]; $splines[] = $tempdr; //echo "$token" . "[" . $i . "]:  old_dont_connect = $old_dont_connect / dr = $tempdr<br>";                       // dr
                        //echo "token = $token / i = $i / old_dont_connect = $old_dont_connect / tempdr = $tempdr<br>";
                        $value_to_insert = $exit_point_type;
                    
                        //if ($value_to_insert == conditional_pivot_point) {
                        //  if ($vertical !== "up") $value_to_insert = 0;
                            //else $value_to_insert = 2;
                        //}
                    
                        $splines[] = $value_to_insert; //$exit_point_type;              // earlier version: $steno_tokens[$token][$i+6];                        // d2
                        //$splines[] = $token_list[$token][$i+7];                          // tension before next point // this line is WRONG !!!!???
                        //echo "i = $i / token_definition_length = $token_definition_length / position = $position<br>";
                        $splines[] = $steno_tokens[$token][$i+offs_t2];
                        //echo "insert tension t2: " . $steno_tokens[$token][$i+offs_t2] . "<br>";
                
                        // duplicate last point of last token in order to avoid weired lines before punctuation
                            //if (($position === "last") && ($i == ($token_definition_length - tuplet_length))) {
                            //  $splines_actual_length = count( $splines );
                            //$start_last_point = $splines_actual_length - tuplet_length;
                            //for ($t = 0; $t < 8; $t++) $splines[] = $splines[$start_last_point + $t];
                            //}
                    }
                } else {
                        //echo "don't insert knot<br>";
                        // preceeding and new points are identical and type is 4 (<=> part of a token)
                        // this means that the two points are "joined": only one knot is inserted
                        // this knot contains t2 from preceeding knot as entry tension (is already there,
                        // has been inserted automatically before) and t1 from new knot (must be inserted
                        // here)
                        if ($initial_splines_length > 0) {
                            $splines[$initial_splines_length-8+offs_t1] = $steno_tokens[$token][$i+offs_t1];
                            //echo "insert t2 into preceeding knot (joined knots): " . $steno_tokens[$token][$i+offs_t1] . "<br>";
                        }
                        // adjust actual_x
                        //echo "<br>actual_x: $actual_x token: $token type: " . $steno_tokens[$token][offs_token_type] . "width: " . $steno_tokens[$token][0] . "<br>"; 
                        //if ($steno_tokens[$token][offs_token_type] == 4) $actual_x += $steno_tokens[$token][0];
                }
            }

        }

        // correct start tension of preceeding point (for example upper point from d in "leider") - only if tension is 0
        $splines_length = count($splines);
        $token_points_length = count( $steno_tokens[$token] ) - header_length;
 
        // vertical post offset (for example "ich" => baseline has to come down again; 2nd case: grr => baseline has two move up 1 standard_height
        $old_y = $actual_y; 
        if (($vertical == "up") or ( $steno_tokens[$token][offs_delta_y_after] > 0)) $actual_y -= $steno_tokens[$token][offs_delta_y_after] * $standard_height;
        $vertical_compensation_x = ($old_y-$actual_y) / tan(deg2rad($_SESSION['token_inclination']));
        if (!($backport_revision1)) $vertical_compensation_x = 0; // disable vertical compensation (compatibility issue with revision1) - revision0 doesn't need compensation!
        
        //echo "old_y = $old_y actual_y = $actual_y<br>"; 
        //echo "offset_delta_y_after: token = $token vertical_compensation_x = $vertical_compensation_x<br>";
        $actual_x += $vertical_compensation_x; // vertical compensation for actual_x is necessary!
        
        // now set new values for actual_x and actual_y (i.e. create new base for next token)
        //echo "token: $token position: $position actual_x: BEFORE: $actual_x ";
        //echo "actual_x = $actual_x width = " . $steno_tokens[$token][offs_token_width] . " additional: before: " . $steno_tokens[$token][offs_additional_x_before] . " after: " . $steno_tokens[$token][offs_additional_x_after]. "<br>";
        $actual_x += $steno_tokens[$token][offs_token_width]+$steno_tokens[$token][offs_additional_x_before]+$steno_tokens[$token][offs_additional_x_after]; // add width of token + pre/post offsets for x to calculate new horizontal position x        
        
        
        //echo "AFTER: $actual_x<br>";
        // CONCLUSIONS after examining "Wachtmeister" with and without tokens | and \:
        // - \ and | can be defined as token => width (offset 0) and additional width before/after (offsets 4 and 5) are added to actual_x (if no token is defined, nothing is added)
        // - horizontal delta x is wrong if following token is added at higer position (due to angle; this occurs here with &T)
        // this is quite a complex problem:
        // - adding a "scaled delta x" (e.g. higher values in higer positions, depending on angle) might be complicated due to proportional knots and parallel rotating axis
        // - ignoring | and \ for tokens that lead to higer positions (typically &T in Stolze-Schrey) requires "global" replacement (before parser separates word parts) and might lead to other unpleasant phenomenons (since mecanism \ | introduced for composed words will not work any more ...)
        // no idea how to solve the problem for the moment ...
        
        // restore original baseline => add inconditional deltay to token if specified in token_list
        $actual_y -= $steno_tokens[$token][offs_inconditional_delta_y_after] * $standard_height;
}
//echo "<br>InsertTokenInSplinesList(): SPLINES actual_x: $actual_x<br>";
//var_dump($splines);
    //echo "END: InsertToken(): actual_x = $actual_x actual_y = $actual_y<br>";
        
    return array( $splines, $actual_x, $actual_y );
}

function SmoothenEntryAndExitPoints( $splines ) {
    $entry_x = 0; $entry_y = 0; $entry_i = 0; $entry_yes = false;
    $pivot_entry_x = 0; $pivot_entry_y = 0; $pivot_entry_i = 0; $pivot_entry_yes = false;
    $exit_x = 0; $exit_y = 0; $exit_i = 0; $exit_yes = false;
    $pivot_exit_x = 0; $pivot_exit_y = 0; $pivot_exit_i = 0; $pivot_exit_yes = false;
    $end_of_tuplet_list = false; $length_splines = count( $splines );
    $dont_connect_flag = false;
    //var_dump($splines);

    for ( $i = 0; $i < $length_splines; $i += tuplet_length) { // this is just a QUICK-FIX!!!!!!!!!!!!!!!!! => should be a clean solution now
        //echo "<br>i=$i: test: d1 ";
        switch ( $splines[$i+offs_d1] ) {           // test, if point is entry or pivot; ignore entry if no exit is defined
            case "1" :  if ($exit_yes) { 
                            //echo "=> entry point ";
                            $entry_x = $splines[$i+offs_x1]; 
                            $entry_y = $splines[$i+offs_y1]; 
                            $entry_i = $i; 
                            $entry_yes = true; 
                        }
            break;
            case "2" : if ($exit_yes) { 
                            //echo "=> pivot entry point ";
                            $pivot_entry_x = $splines[$i+offs_x1]; 
                            $pivot_entry_y = $splines[$i+offs_y1]; 
                            $pivot_entry_i = $i; $pivot_entry_yes = true; 
                        }
            break;
        }
        //echo "test: d2 ";
        switch ( $splines[$i+offs_d2] ) {           // test, if point is exit or pivot
            case "1" :  //echo "=> exit point ";
                        $exit_x = $splines[$i+offs_x1]; 
                        $exit_y = $splines[$i+offs_y1]; 
                        $exit_i = $i; 
                        $exit_yes = true; 
                    break;
            case "2" :  //echo "=> pivot exit point ";
                        $pivot_exit_x = $splines[$i+offs_x1]; 
                        $pivot_exit_y = $splines[$i+offs_y1]; 
                        $pivot_exit_i = $i; 
                        $pivot_exit_yes = true;
                    break;
        }
        //echo "[exit: pivot: $pivot_exit_yes exit: $exit_yes // entry: pivot: $pivot_entry_yes entry: $entry_yes dont_connect: $dont_connect_flag]";
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
                //echo "=> case 2a (pivot -> entry)";
                // define line going from pivot to entry point: y = m*x + c, where m = dy / dx and c = py - m * px
                $dx = $entry_x - $pivot_exit_x;
                $dy = $entry_y - $pivot_exit_y;
                $m = $dy / $dx;
                $c = $pivot_exit_y - ( $m * $pivot_exit_x );
                // now calculate new exit point keeping x-coordinate the same (adapting just y)
                $new_exit_y = $m * $exit_x + $c;
                // replace y-value for exit-point in splines with new value
                // $splines[$exit_i+1] = $new_exit_y;
                ///////////////// experimental
                // new: do this only, if new_exit_y > original y (goal: get smoother connection with high tokens
                //echo "<br>EXIT: pivot: ($pivot_exit_x/$pivot_exit_y) exit: ($exit_x/$exit_y)<br>ENTRY: entry: ($entry_x/$entry_y) pivot: ($pivot_entry_x/$pivot_entry_y)<br>";
                //echo "corrected knot: ($exit_x/$new_exit_y)<br>";
                $splines[$exit_i+1] = ($new_exit_y > $splines[$exit_i+1]) ? $new_exit_y : $splines[$exit_i+1];
                //echo "written knot (splines): (" . $splines[$exit_i] . "/" . $splines[$exit_i+1] . ")<br>";
            }
            // case 2b
            if ((!$pivot_exit_yes) && ($pivot_entry_yes)) {
                //echo "=> case 2b (exit -> pivot)";
                // define line going from exit point to pivot: y = m*x + c, where m = dy / dx and c = exit_y - m * exit_x
                $dx = $pivot_entry_x - $exit_x;
                $dy = $pivot_entry_y - $exit_y;
                $m = $dy / $dx;
                $c = $exit_y - ( $m * $exit_x );
                // now calculate new entry point keeping x-coordinate the same (adapting just y)
                $new_entry_y = $m * $entry_x + $c;
                //echo "<br>EXIT: y: " . $splines[$entry_i+1] . " ENTRY: PIVOT: y: $pivot_entry_y<br>";
                //echo "old_entry_y = " . $splines[$entry_i+1] . " new_entry_y = $new_entry_y<br>";
                // replace y-value for entry-point in splines with new value
                //$splines[$entry_i+1] = $new_entry_y; // why the hell + 1 ?!?!? => because it's y-coordinate!
                ///////////////// experimental
                // new: do this only, if new_entry_y < original y (goal: get smoother connection with high tokens
                $splines[$entry_i+1] = ($new_entry_y < $splines[$entry_i+1]) ? $new_entry_y : $splines[$entry_i+1];
                //echo "new splines y: " . $splines[$entry_i+1] . "<br>"; 
            }
            // case 4:
            if (($pivot_entry_yes) && ($pivot_exit_yes)) {
                //echo "=> in case 4 (pivot -> pivot)"; // define line going from pivot to pivot: y = m*x + c, where m = dy / dx and c = pivot_exit_y - m * pivot_exit_x
                $dx = $pivot_entry_x - $pivot_exit_x;
                $dy = $pivot_entry_y - $pivot_exit_y;
                $m = $dy / $dx;
                $c = $pivot_exit_y - ( $m * $pivot_exit_x );
                // now calculate new exit and entry points keeping x-coordinate the same (adapting just y)
                $new_exit_y = $m * $exit_x + $c;
                $new_entry_y = $m * $entry_x + $c;
                // replace y-value for exit- and entry-points in splines with new value
                //$splines[$exit_i+1] = $new_exit_y;
                //$splines[$entry_i+1] = $new_entry_y;
                // $splines[$exit_i+1] = $new_exit_y;
                ////////////// experimental
                // new: do this only, if new_exit_y > original y (goal: get smoother connection with high tokens
                $splines[$exit_i+1] = ($new_exit_y > $splines[$exit_i+1]) ? $new_exit_y : $splines[$exit_i+1];
                // new: do this only, if new_entry_y < original y (goal: get smoother connection with high tokens
                $splines[$entry_i+1] = ($new_entry_y < $splines[$entry_i+1]) ? $new_entry_y : $splines[$entry_i+1];
                // other possibility with two pivot would be to take average y for each entry/exit point
            }
            // reset all variables in order to gather new points for next connection
            $entry_x = 0; $entry_y = 0; $entry_i = 0; $entry_yes = false;
            $pivot_entry_x = 0; $pivot_entry_y = 0; $pivot_entry_i = 0; $pivot_entry_yes = false;
            $exit_x = 0; $exit_y = 0; $exit_i = 0; $exit_yes = false;
            $pivot_exit_x = 0; $pivot_exit_y = 0; $pivot_exit_i = 0; $pivot_exit_yes = false;
            $end_of_tuplet_list = false; $dont_connect_flag = false;
        } elseif (($exit_yes) && ($entry_yes) && ($dont_connect_flag)) {
            // if dont_connect_flag is set => reset all variables
            // can be written more clearly: integrate this in above if-block
            // dont test for flag first and test only inside of block
            // leave it like that for the moment
            //echo "dont_connect_flag => reset variables";
            $entry_x = 0; $entry_y = 0; $entry_i = 0; $entry_yes = false;
            $pivot_entry_x = 0; $pivot_entry_y = 0; $pivot_entry_i = 0; $pivot_entry_yes = false;
            $exit_x = 0; $exit_y = 0; $exit_i = 0; $exit_yes = false;
            $pivot_exit_x = 0; $pivot_exit_y = 0; $pivot_exit_i = 0; $pivot_exit_yes = false;
            $end_of_tuplet_list = false; $dont_connect_flag = false;
        }
    }
    //echo "<br>";
    return $splines;
}

function TokenList2SVG( $TokenList, $angle, $stroke_width, $scaling, $color_htmlrgb, $stroke_dasharray, $alternative_text ) {
        // initialize variables
        global $baseline_y, $steno_tokens_master, $steno_tokens, $punctuation, $space_at_end_of_stenogramm, $distance_words, $separate_spline;
        SetGlobalScalingVariables( $scaling );
        //echo "TokenList2SVG(): tokenlist dump: "; var_dump($TokenList);
    
        //call the following function only once per text (performance)
        //CreateCombinedTokens();
        //CreateShiftedTokens();
        //if (mb_strlen($pre)>0) ParseAndSetInlineOptions( $pre );        // set inline options
        
        $actual_x = 1;                      // start position x
        $actual_y = $baseline_y;            // start position y
        $splines = array();                 // contains all information for later drawing routine
        $steno_tokens = ScaleTokens( $steno_tokens_master, $scaling );        
        //echo "stenotokens(dump): "; var_dump($steno_tokens);
        
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
            if (($i == $length_tokenlist -1) || ($temp1_punctuation) || ($temp1_separator) || ($temp1_separator2)) $position = "last"; // test: added || $temp1_separator2 9.2.19 => not sure if this is correct!
            
            //echo "<p>tokenlist($i) = $temp</p>";
            // if token is a vowel ("virtual token") then set positioning variables
            // vowel <=> value 2 at offset 12 --- positioning variables $vertical, $distance, $shadowed at offsets 19, 20, 21
            if ($steno_tokens[$TokenList[$i]][offs_token_type] == 2) {
                //echo "tokenlist(i) = " . $TokenList[$i] . " offsets: 12 = " . $steno_tokens[$TokenList[$i]][12] . " 19 = " . $steno_tokens[$TokenList[$i]][19] . " 20 = " . $steno_tokens[$TokenList[$i]][20] . " 21 = " . $steno_tokens[$TokenList[$i]][21] . " <br>";
                $vertical = $steno_tokens[$TokenList[$i]][offs_vertical];
                $distance = $steno_tokens[$TokenList[$i]][offs_distance];
                $shadowed = $steno_tokens[$TokenList[$i]][offs_shadowed];
            } elseif ($steno_tokens[$TokenList[$i]][offs_token_type] == 3) {    // token type == spacer
                $actual_x += $steno_tokens[$TokenList[$i]][offs_token_width];   // only add width and leave the rest as is (and cross fingers that this patch works;-)
                //echo "spacer";
            } else {
                list( $splines, $actual_x, $actual_y) = InsertTokenInSplinesList( $TokenList[$i], $position, $splines, $LastToken, $actual_x, $actual_y, $vertical, $distance, $shadowed, $scaling );
                //echo "after InsertTokenInSplinesList()<br>";
                //var_dump($splines);
                $vertical = "no"; $distance = "none"; $shadowed = "no";
                $LastToken = $TokenList[$i];
            }
            $position = "inside";
        }
        // first tilt and then smoothen for better quality!!!
        //echo "SPLINES:<br>";
        //var_dump($splines);
        //echo "TokenList:<br>";
        //var_dump($TokenList);
        
        $splines = TiltWordInSplines( $angle, $splines );
        //echo "<br><br>new word:<br>TokenList2SVG(): var_dump(splines) after TiltWordInSplines()<br>";
        //var_dump($splines);
      
        $splines = SmoothenEntryAndExitPoints( $splines );
        //echo "<br><br>TokenList2SVG(): var_dump(splines) before CalculateWord() after SmoothenEntryAndExitPoints()<br>";
        //var_dump($splines);
        
        list( $splines, $width, $correction_lx, $correction_rx) = TrimSplines( $splines );
        //echo "<br><br>TokenList2SVG(): var_dump(splines) before CalculateWord() after TrimSplines()<br>";
        //var_dump($splines);
        $copy = $splines;
        //echo "separate_spline (before calculateword):<br>";
        //var_dump($separate_spline);
        
        $splines = CalculateWord( $splines );
        //$separate_spline = CalculateWord( $copy );
        $separate_spline = CalculateWord( $separate_spline );
       
        //echo "separate_spline;<br>";
        //var_dump($separate_spline);
        //echo "<br><br>var_dump(splines) after CalculateWord()<br>";
        //$separate_spline = CalculateWort( $separate_spline );
        
        //var_dump($splines);
        $svg_string = CreateSVG( $splines, $actual_x + $distance_words * $scaling, $width + $space_at_end_of_stenogramm * $scaling, $stroke_width, $color_htmlrgb, $stroke_dasharray, $alternative_text );
        // replace space_at_end_stenogramm by distance_words at the end (necessary so that inclinated high stenogramms can be written 
        // not possible ... stenograms with parts below need space on left side / high stenograms need space at the right
        //$svg_string = CreateSVG( $splines, $actual_x + ($distance_words * $scaling) / 2, $width + ($distance_words * $scaling) / 2, $stroke_width, $color_htmlrgb, $stroke_dasharray, $alternative_text );
        
        //if (mb_strlen($post)>0) ParseAndSetInlineOptions( $post );        // set inline options
        $separate_spline = null;
        return $svg_string;
}
/*
function TokenList2SVGWithSessionVariables( $TokenList ) {
        $angle = $_SESSION['token_inclination'];
        $stroke_width = $_SESSION['token_thickness'];
        $scaling = $_SESSION['token_size'];
        $color_htmlrgb = $_SESSION['token_color'];
        $stroke_dasharray = $_SESSION['token_style_custom_value'];
        $alternative_text = "";
        echo "fct-call: tokenlist: $TokenList angle: $angle stroke_width: $stroke_width scaling: $scaling color_htmlrgb: $color_htmlrgb stroke_dasharray: $stroke_dasharray alternativ_text: $alternative_text<br>";
        $temp = TokenList2SVGWithSessionVariables( $TokenList, $angle, $stroke_width, $scaling, $color_htmlrgb, $stroke_dasharray, $alternative_text ); 
        echo "svg: $temp<br>";
        return $temp;
}
*/

function NormalText2NormalTextInSVG( $text, $size ) {
    global $svg_height, $baseline_y;
    // $size contains the text size in pixels (height)
    // font: the Courier font is used since all characters have the same width => allows the calculation of svg-width in advance
    $svg_width = $size * 0.59871795 * mb_strlen( $text ) + 6;    // note: factor is an empirical value (estimation); height: use $svg_height from constants (= same height as shorthand system); add additional 8px to width for spacing
    //$svg_width = $size * 0.8 * mb_strlen( $text ) + 6;    // note: factor is an empirical value (estimation); height: use $svg_height from constants (= same height as shorthand system); add additional 8px to width for spacing
    
    $svg_baseline = $baseline_y;    // use same baseline as shorthand words
    $svg_color = $_SESSION['token_color'];                  // use same color as shorthand text
    //echo "SVG: height=$svg_height width=$svg_width baseline=$svg_baseline color=$svg_color text=$text<br>";
    $svg  = "<svg height=\"$svg_height\" width=\"$svg_width\">";
    $svg .= "<text x=\"0\" y=\"$svg_baseline\" fill=\"$svg_color\" font-size=\"$size\" font-family=\"Courier\">";
    $svg .= "$text</text></svg>";
    return $svg;
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
    //list( $pre, $word, $post ) = GetPreAndPostTags( $text );
    //echo "NormalText2TokenList(): text = $text<br>";
    $text = htmlspecialchars_decode( $text );
    $metaform = MetaParser( $text );
    //echo "NormalText2tokenlist(): text = $text metaform = $metaform<br>";
    //echo "Metaform: $metaform<br>";
    if (mb_strlen($metaform)>0) {
        $tokenlist = MetaForm2TokenList( $metaform );     // somehow idiot to pass $pre and $post through this function without changing anything - but do it like that for the moment
        return $tokenlist;
    } else {
        return null;
    }

}

function SingleWord2SVG( $text, $angle, $stroke_width, $scaling, $color_htmlrgb, $stroke_dasharray, $alternative_text ) {
    global $combined_pretags, $combined_posttags, $html_pretags, $html_posttags;
    //echo "Singleword2svg(): text = $text<br>";
   
    $tokenlist = NormalText2TokenList( $text );
    //echo "(2)Singleword2svg(): text: $text tokenlist = $tokenlist<br>";
    //var_dump($tokenlist); echo "<br>";
    
    //echo "SingleWord2SVG(): tokenlist dump: "; var_dump($tokenlist);
    $pre = $combined_pretags;
    $post = $combined_posttags;
    if (mb_strlen($pre)>0) $pre_html_tag_list = ParseAndSetInlineOptions( $pre );        // set inline options
    $html_pretags = $pre_html_tag_list; // set global variable
    $svg = $pre_html_tag_list;
    //$esc_svg = htmlspecialchars($svg);
   // echo "svg: $esc_svg<br>";
    
    // ugly solution to set parameters (just a quick fix: has to be replaced later)
    $angle = $_SESSION['token_inclination'];
    $stroke_width = $_SESSION['token_thickness'];
    $scaling = $_SESSION['token_size'];
    $color_htmlrgb = $_SESSION['token_color'];
   // $stroke_dasharray = $_SESSION['token_style_custom_value'];
    //echo "singleword2svg(): text: $text pre: $pre post: $post htmlpre: $pre_html_tag_list htmlpost: $post_html_tag_list<br>";
    switch ($_SESSION['token_type']) {
        case "htmltext" : 
            $middle = GetWordSetPreAndPostTags( $text );
            //echo "<br>Inside SingleWord2SVG:<br>-text: " . htmlspecialchars($text) . "<br>- pre_nil: $pre_nil<br>- middle: $middle<br>- post_nil: $post_nil<br><br>";
            $pre_nil = $combined_pretags;   // get global variables
            $post_nil = $combined_posttags;
            $pre_html_tag_list .= ParseAndSetInlineOptions ( $pre_nil );
            $svg .= $pre_html_tag_list;
            $svg .= " " . $middle;          // use raw text and insert it directly between $pre and $post html-text (= raw html code); add a space because spaces have been parsed out ...
            $post_html_tag_list = ParseAndSetInlineOptions( $post_nil );
            $html_posttags = $post_html_tag_list; // set global variable
            $svg .= $post_html_tag_list;
            return $svg;
            break;
        case "svgtext" : 
            $middle = GetWordSetPreAndPostTags( $text );
            $pre_nil = $combined_pretags;   // get global variables
            $post_nil = $combined_posttags;
            //echo "<br>Inside SingleWord2SVG:<br>-text: " . htmlspecialchars($text) . "<br>- pre_nil: $pre_nil<br>- middle: $middle<br>- post_nil: $post_nil<br><br>";
            $pre_html_tag_list .= ParseAndSetInlineOptions ( $pre_nil );
            $svg .= $pre_html_tag_list; // set global variable
            $svg .= $pre_html_tag_list;
            $text_as_svg .= NormalText2NormalTextInSVG( $middle, 20 );  // use fix size
            $svg .= $text_as_svg;
            $post_html_tag_list = ParseAndSetInlineOptions( $post_nil );
            $html_posttags = $post_html_tag_list; // set global variable
            $svg .= $post_html_tag_list;
            return $svg;
            break;
        default:
            //if ($tokenlist !== null) {
            if (count($tokenlist)>0) {
                //echo "SingleWord2SVG(): tokenlist dump: "; var_dump($tokenlist);
    
                $svg .= TokenList2SVG( $tokenlist, $angle, $stroke_width, $scaling, $color_htmlrgb, $stroke_dasharray, $alternative_text );
                if (mb_strlen($post)>0) {
                    $post_html_tag_list = ParseAndSetInlineOptions( $post );        // set inline options
                    $html_posttags = $post_html_tag_list; // set global variable
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

function GetDebugInformation( $word ) {
        global $globalizer_table, /*$trickster_table, $dictionary_table,*/ $filter_table, $shortener_table, $normalizer_table, 
            $bundler_table, $transcriptor_table, $substituter_table, $global_debug_string, $global_number_of_rules_applied,
            $processing_in_parser, $separated_std_form, $separated_prt_form, $global_textparser_debug_string, $std_form, $prt_form;
        global $global_linguistical_analyzer_debug_string, $cached_result;
            
/*
        $original = $word;
        $globalized = GenericParser( $globalizer_table, $word ); // Globalizer( $word );
        $lookuped = Lookuper( $word );
        //$test_wort = Trickster( $test_wort);
        $decapitalized = Decapitalizer( $word );
        $shortened = GenericParser( $shortener_table, $decapitalized ); // Shortener( $decapitalized );
        $normalized = GenericParser( $normalizer_table, $shortened ); // Normalizer( $shortened );
        $bundled = GenericParser( $bundler_table, $normalized ); // Bundler( $normalized );
        $transcripted = GenericParser( $transcriptor_table, $bundled ); // Transcriptor( $bundled );
        $substituted = GenericParser( $substituter_table, $transcripted ); // Substituter( $transcripted );
        $metaparsed = MetaParser( $word );
        $alternative_text = $original;
        $debug_text = "<p>Start: $original<br>==0=> $globalized<br>==1=> /$lookuped/<br>==2=> $decapitalized<br>==3=> $shortened<br>==4=> $normalized<br>==5=> $bundled<br>==6=> $transcripted<br>==7=> $substituted<br>=17=> $test_wort<br> Meta: $metaparsed<br><br>";
*/
        //echo "global_linguistical_analyzer_debug_string: $global_linguistical_analyzer_debug_string<br>";
    
        if (($global_linguistical_analyzer_debug_string !== "") || ($global_debug_string !== ""))
            $debug_text .= "<br><b>WORD: $word</b><br><div id='debug_table'><table><tr><td><b>STEPS</b></td><td><b>RULES</b></td><td><b>FUNCTIONS</b></td></tr>" . "$global_linguistical_analyzer_debug_string" . "$global_debug_string</table></div>" . "<p>STD: " . mb_strtoupper($std_form) . "<br>PRT: $prt_form<br>TYPE: $processing_in_parser<br>RULES: $global_number_of_rules_applied</p>";
        else {
            if ($cached_result) $debug_text .= "<br><b>WORD: $word</b><br>no rules (cached)<br>";
            else $debug_text .= "<br><b>WORD: $word</b><br>no rules<br>";
        }
        $global_number_of_rules_applied = 0; // suppose, this function is called at the end of the calculation (not before ... since this will give false information then ... ;-)
        return $debug_text;        
    
    // return "debugging disabled<br>";
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

function CalculateInlineSVG( $text_array ) {
    global $original_word, $combined_pretags, $html_pretags, $result_after_last_rule, $global_debug_string, $global_numbers_of_rules_applied;
    $output = "";
    
    foreach ( $text_array as $this_word ) {
        //echo "in calculateinline..(): this_word = $this_word<br>";
        $global_debug_string = "";
        $global_number_of_rules_applied = 0;
        $bare_word = /*html_entity_decode(*/GetWordSetPreAndPostTags( $this_word )/*)*/;           // decode html manually ...
        // echo "in calculateinline..(): bare_word = $bare_word<br>";
        $html_pretags = ParseAndSetInlineOptions( $combined_pretags );
        $original_word = $bare_word;
        $result_after_last_rule = $bare_word;
        
        //echo "CalculateInlineSVG(): this_word: $this_word bare_word: $bare_word html_pretags: $html_pretags<br>";
        
        if (mb_strlen($bare_word)>0) {
            $alternative_text = ($_SESSION['output_texttagsyesno']) ? /*$SingleWord->Original*/ $bare_word : "";
            //echo "CalculateInlineSVG()1111: bare_word: $bare_word<br>";
            switch ($_SESSION['token_type']) {
                case "handwriting" : 
                    $hw_meta = GetHandwriting($bare_word);
                    //echo "Handwriting: $hw_meta<br>";
                    $hw_token_list = MetaForm2TokenList($hw_meta);
                    $output .= TokenList2SVG( $hw_token_list, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], $_SESSION['token_style_custom_value'], $alternative_text );
                    break;
                default:
                    $output .= $html_pretags . SingleWord2SVG( /*$SingleWord->Original*/ $bare_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
            }
        
            $debug_information = GetDebugInformation( /*$SingleWord->Original*/ $bare_word );       // revert back to procedural-only version
        } else {
            $output .= $html_pretags;
        }
    }
    //echo "output: #$output#<br>";
    return $output;
}

// functions to handle line number (for <titlebreak>)
function GetMaxLinesOnPage() {
    $first_line = $_SESSION['top_margin'] + ($_SESSION['baseline'] * $_SESSION['token_size'] * 10); // hardcode standard line height, because $standard_height get's changed (it shouldn't!?)
    $delta_y_per_line = $_SESSION['num_system_lines'] * $_SESSION['token_size'] * 10;
    $total_lines = (int)($_SESSION['output_height'] - $first_line) / $delta_y_per_line;
    return $total_lines + 1;
}

function GetActualLineFromYOnPage($y) {
    // calculation is inaccurate for first line (i.e. as long as there has not been a <br> before
    // no idea why => I suppose a problem with with variable synchronization from html tag processing
    // the function is "good enough" for the moment (i.e. for <titlebreak>)
    // ok, the bug can be corrected, using $y position:
    // case 1: ($actual_line == 0) && ($y == $first_line) => $actual_line = 1;
    // case 2 (all others): => $actual_line += 2
    $first_line = $_SESSION['top_margin'] + ($_SESSION['baseline'] * $_SESSION['token_size'] * 10); // hardcode standard line height, because $standard_height get's changed (it shouldn't!?)
    $delta_y_per_line = $_SESSION['num_system_lines'] * $_SESSION['token_size'] * 10;
    $actual_line = (int) (($y - $first_line) / $delta_y_per_line);
    if ($y == $first_line) $final_result = 1;
    else $final_result = $actual_line + 2;
    return $final_result;
}

function AvoidLineBreakOnTopOfPage($type) {
    global $word_position_y;
    $break_type = "page_top_avoid_breaks_before_" . $type . "_yesno";
    $actual_line = GetActualLineFromYOnPage($word_position_y);
    //echo "Actual line: $actual_line<br>";
    //echo "Avoid break ($type): " . ($_SESSION["$break_type"] ? "true" : "false") . "<br>";
    if (($_SESSION["$break_type"]) && ($actual_line == 1)) return true;
    else return false;
}

function LayoutedSVGProcessHTMLTags( $html_string, $ignore_non_breaking ) {
    global $word_position_y;
    // Unlike inline-svgs (= svgs containing each one only one word that is given to the browser as inline-element), 
    // HTML-Tags in layouted-SVG can not handled by browser.
    // To offer some basic layout control to the user, the tags <br> and </p> are used as linebreak (newline).
    // All the other HTML-tags are filtered out!!! (In other words: no support for html-tags in layouted svgs).
    // This function filters out the tags and returns number of linebreaks as int value.
    //echo "LayoutedSVGProcessHTMLTags(): text: $html_string<br>";
    // preg_match_all( "/<[^>]+[>]/", $html_string, $matches );                         // .*? makes expression non greedy; parse all tags of both types (inline- and html-)
    preg_match_all( "/<.*?[>]/", $html_string, $matches );                         // .*? makes expression non greedy; parse all tags of both types (inline- and html-)
    
    $number_linebreaks = 0;
    foreach( $matches[0] as $match ) {
        $match_as_lower = mb_strtolower($match);
        //echo "match before: #" . htmlspecialchars($match_as_lower) . "#<br>";
        $match_as_lower = preg_replace( "/[<](.+) .*?[>]/", "<$1>", $match_as_lower );      // strip out all additional parameters => keep only bare html tags
        //echo "match after: #" . htmlspecialchars($match_as_lower) . "#<br>";
        switch ($match_as_lower) {
                case "<br>"         : if ($ignore_non_breaking) $number_linebreaks++;
                                      elseif (!AvoidLineBreakOnTopOfPage("br")) $number_linebreaks++; break;
                case "<break>"      : $number_linebreaks++; break; // offer this as inconditional break
                case "</p>"         : $number_linebreaks++; break;
                case "<p>"          : if (!AvoidLineBreakOnTopOfPage("p"))$number_linebreaks++; break;
                case "<newpage>"    : $number_linebreaks=9999; break; // let's try ...
                case "<titlebreak>" : 
                    //echo "max_lines: " . GetMaxLinesOnPage() . " actual_line: " . GetActualLineFromYOnPage($word_position_y) . " number_line_breaks: $number_linebreaks word_position_y: $word_position_y<br>"; 
                    $max_lines = GetMaxLinesOnPage();
                    $actual_line = GetActualLineFromYOnPage($word_position_y);
                    $min_necessary_lines_for_title =  $_SESSION['titlebreak_minimum_lines_at_end']; // default = 4 lines: empty line + title + empty line + 1 line of following paragraphe
                    if ($actual_line > $max_lines - $min_necessary_lines_for_title + 1) $number_linebreaks=9999; 
                    elseif ($actual_line != 1) $number_linebreaks = $_SESSION['titlebreak_number_of_breaks_before']; // add a distance before title
                    //elseif ($word_position_y == 115.11) $number_linebreaks = 1;
                    break; // conditional newpage
        }
    }
    return $number_linebreaks;
}

function InsertAuxiliaryLinesInLayoutedSVG( $starty, $system_line_height, $line_height ) {
    global $actual_page_deltax;
    $lines_string = "";
    $x = 0; //$_SESSION['left_margin'];
    $width = $_SESSION['output_width'];
    // $starty = $_SESSION['baseline'];
    $maxy = $_SESSION['output_height'] - $_SESSION['bottom_margin'];
    
    //echo "in Auxiliary Lines: starty: $starty maxy: $maxy line_height: $line_height ...<br>";
    //echo "baseline_nomargin: >" . $_SESSION['baseline_nomargin_yesno'] . "< upper12_nomargin: >" . $_SESSION['upper12_nomargin_yesno'] . "< upper3_nomargin: >" . $_SESSION['upper3_nomargin_yesno'] . "< lower_nomargin: >" . $_SESSION['lower_nomargin_yesno'] . "<<br>";
    
    for ($y = $starty; $y <= $maxy; $y += $line_height) {
        //echo "drawing at: $y maxy: $maxy line_height: $line_height<br>";
        if ($_SESSION['auxiliary_upper3yesno']) {
            $thickness = $_SESSION['auxiliary_upper3_thickness'];
            $color = $_SESSION['auxiliary_upper3_color'];
            $stroke_dasharray = $_SESSION['upper3_style'];
            $tempy = $y - 3 * $system_line_height;
            
            $leftx = $x + $_SESSION['auxiliary_lines_margin_left'] + $actual_page_deltax;
            if ($_SESSION['upper3_nomargin_yesno']) $rightx = $width + $actual_page_deltax;
            else $rightx = $width - $_SESSION['auxiliary_lines_margin_right'] + $actual_page_deltax;
            
            $lines_string .= "<line x1=\"$leftx\" y1=\"$tempy\" x2=\"$rightx\" y2=\"$tempy\" stroke-dasharray=\"$stroke_dasharray\" style=\"stroke:$color;stroke-width:$thickness\" />";
        }
        if ($_SESSION['auxiliary_upper12yesno']) {
            $thickness = $_SESSION['auxiliary_upper12_thickness'];
            $color = $_SESSION['auxiliary_upper12_color'];
            $stroke_dasharray = $_SESSION['upper12_style'];
            
            $leftx = $x + $_SESSION['auxiliary_lines_margin_left'] + $actual_page_deltax;
            if ($_SESSION['upper12_nomargin_yesno']) $rightx = $width + $actual_page_deltax;
            else $rightx = $width - $_SESSION['auxiliary_lines_margin_right'] + $actual_page_deltax;
            
            for ($i = 1; $i < 3; $i++) {
                $tempy = $y - $i * $system_line_height;
                $lines_string .= "<line x1=\"$leftx\" y1=\"$tempy\" x2=\"$rightx\" y2=\"$tempy\" stroke-dasharray=\"$stroke_dasharray\" style=\"stroke:$color;stroke-width:$thickness\" />";
            }
        }
        if ($_SESSION['auxiliary_baselineyesno']) {
            $thickness = $_SESSION['auxiliary_baseline_thickness'];
            $color = $_SESSION['auxiliary_baseline_color'];
            $stroke_dasharray = $_SESSION['baseline_style'];
            $tempy = $y;
            
            $leftx = $x + $_SESSION['auxiliary_lines_margin_left'] + $actual_page_deltax;
            if ($_SESSION['baseline_nomargin_yesno']) $rightx = $width + $actual_page_deltax;
            else $rightx = $width - $_SESSION['auxiliary_lines_margin_right'] + $actual_page_deltax;
            
            $lines_string .= "<line x1=\"$leftx\" y1=\"$tempy\" x2=\"$rightx\" y2=\"$tempy\" stroke-dasharray=\"$stroke_dasharray\" style=\"stroke:$color;stroke-width:$thickness\" />";
        }
        if ($_SESSION['auxiliary_loweryesno']) {
            $thickness = $_SESSION['auxiliary_lower_thickness'];
            $color = $_SESSION['auxiliary_lower_color'];
            $stroke_dasharray = $_SESSION['lower_style'];
            $tempy = $y + $system_line_height;
            
            $leftx = $x + $_SESSION['auxiliary_lines_margin_left'] + $actual_page_deltax;
            if ($_SESSION['lower_nomargin_yesno']) $rightx = $width + $actual_page_deltax;
            else $rightx = $width - $_SESSION['auxiliary_lines_margin_right'] + $actual_page_deltax;
            
            $lines_string .= "<line x1=\"$leftx\" y1=\"$tempy\" x2=\"$rightx\" y2=\"$tempy\" stroke-dasharray=\"$stroke_dasharray\" style=\"stroke:$color;stroke-width:$thickness\" />";
        }
    }
    //echo "auxiliary lines: ". htmlspecialchars($lines_string) . "<br><br>";
    return $lines_string;
}

function TokenList2WordSplines( $TokenList, $angle, $scaling, $color_htmlrgb, $line_style) {
        global $baseline_y, $steno_tokens_master, $steno_tokens, $punctuation, $space_at_end_of_stenogramm, $distance_words, $separate_spline;
        SetGlobalScalingVariables( $scaling );
        // reset separate_spline for every word!
        $separate_spline = null;
        
        
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
            } elseif ($steno_tokens[$TokenList[$i]][offs_token_type] == 3) {    // token type == spacer
                $actual_x += $steno_tokens[$TokenList[$i]][offs_token_width];   // only add width and leave the rest as is (and cross fingers that this patch works;-)
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
        list($splines, $width, $correction_lx, $correction_rx) = TrimSplines( $splines );        
        $splines = CalculateWord( $splines );
        $separate_spline = CalculateWord( $separate_spline );
        
        //if (mb_strlen($post)>0) ParseAndSetInlineOptions( $post );        // set inline options
        
        return array( $splines, $separate_spline, $width, $correction_lx, $correction_rx );
}

function DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_separate_spline, $word_width, $last_word, $force_left_align ) {
    global $distance_words, $vector_value_precision, $baseline_y, $word_tags, $actual_page_deltax;
    global $word_widths;
    //echo "DrawOneLineInLayoutedSVG(): word_position_y = $word_position_y<br>";
    //var_dump($word_tags);
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
    //echo "In DrawOneLineInLayoutedSVG(): word_position_x: $word_position_x word_position_y: $word_position_y last_word: $last_word stroke_width: $stroke_width<br>word_splines[]<br>";
    //var_dump($word_splines);
    //
    $normal_distance = $_SESSION['distance_words'];
    if (($_SESSION['output_style'] === "align_left_right") && (!$force_left_align)) {
        $number_of_words = count($word_splines) - 1;
        $number_of_gaps = $number_of_words - 1;
        $width_without_correction = 0;
        $normal_distance = $_SESSION['distance_words'];
        if ($_SESSION['layouted_correct_word_width']) {
            for ($i = 0; $i<$number_of_words; $i++) $width_without_correction += $normal_distance + $word_widths[$i][0] + $word_widths[$i][1] + $word_widths[$i][2];
        } else {
            for ($i = 0; $i<$number_of_words; $i++) $width_without_correction += $normal_distance + $word_width[$i];
        }
        $width_without_correction -= $normal_distance;  // first word has no distance
        $leftover_right_side = $_SESSION['output_width'] - $_SESSION['left_margin'] - $_SESSION['right_margin'] - $width_without_correction;
        $additional_distance = $leftover_right_side / $number_of_gaps;
        //echo "number_of_words: $number_of_words number_of_gaps: $number_of_gaps width_without_correction: $width_without_correction leftover_right_side: $leftover_right_side additional_distance: $additional_distance<br>";
    } else $additional_distance = 0;
    
    for ($i = 0; $i < $last_word; $i++) {
        //echo "calculating word_splines($i)";
        //echo " array-length($i) = " . count($word_splines[$i]) . "<br>";
        $extra_shift_y = -$baseline_y; // - ( $line_height + $system_line_height );  // something is wrong with vertical postioning of shorthand text ...
        if ($i == 0) $align_shift_x = 0;
        else $align_shift_x = $additional_distance;
        
        if ($_SESSION['show_distances']) {
            // mark distances graphically
            // normal distance = blue
            $ndx = $word_position_x - $normal_distance + $actual_page_deltax;
            $ndy = $word_position_y - 30;
            $ndwidth = $normal_distance + $actual_page_deltax;
            $ndheight = 40;
            //echo "ndx: $ndx ndy: $ndy normal_distance: $normal_distance ndwidth: $ndwidth ndheight: $ndheight<br>";
            if ($i > 0) $svg_string .= "<rect x=\"$ndx\" y=\"$ndy\" width=\"$ndwidth\" height=\"$ndheight\" style=\"fill:white;stroke:blue;stroke-width:1;opacity:0.5\" />";
            // additional distance = purple
            $adx = $ndx + $normal_distance + $actual_page_deltax;
            $ady = $word_position_y - 30;
            $adwidth = $align_shift_x + $actual_page_deltax;
            $adheight = 40;
            //echo "ndx: $ndx ndy: $ndy normal_distance: $normal_distance ndwidth: $ndwidth ndheight: $ndheight<br>";
            if ($i > 0) $svg_string .= "<rect x=\"$adx\" y=\"$ady\" width=\"$adwidth\" height=\"$adheight\" style=\"fill:white;stroke:purple;stroke-width:1;opacity:0.5\" />";
        }
        
        if (count($word_splines[$i])>0) { // ignore empty arrays (can be rests of filtered out html-tags)
          $type_of_first_element = gettype($word_splines[$i][0]);
          $first_element = $word_splines[$i][0];
         //echo "type_of_first_element: $type_of_first_element first_element: $first_element token_type: " . $_SESSION['token_type'] . "<br>";
          switch ($type_of_first_element) {
            case "string" : // treat it as svgtext (...)
                            //echo "Treat it as svgtext ... word_tags($i): " . $word_tags[$i] . "<br>";
                            // process word tags
            //echo "before: " . $_SESSION['token_type'] . " " . $word_splines[$i][2] . " <br>";
            ParseAndSetInlineOptions($word_tags[$i]);
            //echo "after: " . $_SESSION['token_type'] . "<br>";
            $angle = $_SESSION['token_inclination'];
            $stroke_width = $_SESSION['token_thickness'];
            $scaling = $_SESSION['token_size'];
            // not sure how the following line affects the output ...
            $svg_color = "black";  // fix this with a hardcoded value for the moment (too complicated otherwhise to fix this bug ...)
            $stroke_dasharray = $_SESSION['token_style_custom_value']; 
            
                            $scale = 1;
                            $tsize = $word_splines[$i][1] ;                         // element 1 contains size
                            $tx = ($word_position_x + $align_shift_x) / $scale + $actual_page_deltax;
                            $ty = $word_position_y / $scale; // + $extra_shift_y;
                            // $svg_color = $_SESSION['token_color'];                  // use same color as shorthand text (that was a bad idea by the way ... :)
                            //$svg_color = "black";  // fix this with a hardcoded value for the moment (too complicated otherwhise to fix this bug ...)
                            $svg_color = $_SESSION['token_color']; // might not produce correct results in every case, but better than hardcoded ... (?!)
                            
                            $ttext = $word_splines[$i][2];                          // element 2 contains text
                            //echo "SVG: height=$svg_height width=$svg_width baseline=$svg_baseline color=$svg_color text=$text<br>";
                            //$to_add = "<text x=\"$0\" y=\"0\" fill=\"$svg_color\" font-size=\"14px\" transform=\"scale($scale) translate($tx $ty)\" font-family=\"Courier\">$ttext Fuck</text>";
                            $to_add = "<text x=\"$tx\" y=\"$ty\" fill=\"$svg_color\" font-size=\"$tsize" . "px\" font-family=\"Courier\">$ttext</text>";
                            //echo "to_add: " . htmlspecialchars($to_add) . "<br>";
                            $svg_string .= $to_add;
                            $word_position_x += $word_width[$i] + $normal_distance + $align_shift_x;
                            break; 
            default :       // treat it as splines
            //echo "treated as spline ... word_tags($i): " . $word_tags[$i] . "<br>";
            // process word tags
 //           echo "before: " . $_SESSION['token_type'] . " tags($i): " . $word_tags[$i] . "<br>";
            ParseAndSetInlineOptions($word_tags[$i]);
 //           echo "after: " . $_SESSION['token_type'] . "<br>";
            $angle = $_SESSION['token_inclination'];
            $stroke_width = $_SESSION['token_thickness'];
            $scaling = $_SESSION['token_size'];
            $color_htmlrgb = $_SESSION['token_color'];
            $stroke_dasharray = $_SESSION['token_style_custom_value']; 
            
            // add polygon spline
            if ($_SESSION['rendering_polygon_yesno']) list($polygon_spline, $word_splines[$i]) = GetPolygon($word_splines[$i], $word_position_x + $align_shift_x, $word_position_y + $extra_shift_y);
            else $polygon_spline = "";
            
            // insert word
            for ($n = 0; $n < count($word_splines[$i])-tuplet_length; $n+=tuplet_length) {
            
                $x1 = round($word_splines[$i][$n] + $word_position_x + $align_shift_x, $vector_value_precision, PHP_ROUND_HALF_UP) + $actual_page_deltax;
                $y1 = round($word_splines[$i][$n+1] + $word_position_y + $extra_shift_y, $vector_value_precision, PHP_ROUND_HALF_UP);
                $q1x = round($word_splines[$i][$n+2] + $word_position_x + $align_shift_x, $vector_value_precision, PHP_ROUND_HALF_UP) + $actual_page_deltax;
                $q1y = round($word_splines[$i][$n+3] + $word_position_y + $extra_shift_y, $vector_value_precision, PHP_ROUND_HALF_UP);
                $relative_thickness = ($_SESSION['rendering_middleline_yesno']) ? $word_splines[$i][$n+4] : 1.0;
                $relative_thickness = AdjustThickness($relative_thickness);
                // adjustment for shadowed parts
                if ($original_thickness > 1.0) $relative_thickness *= $_SESSION['token_shadow'];
                $unused = $word_splines[$i][$n+5];
                $q2x = round($word_splines[$i][$n+6] + $word_position_x + $align_shift_x, $vector_value_precision, PHP_ROUND_HALF_UP) + $actual_page_deltax;
                $q2y = round($word_splines[$i][$n+7] + $word_position_y + $extra_shift_y, $vector_value_precision, PHP_ROUND_HALF_UP);
                $x2 = round($word_splines[$i][$n+8] + $word_position_x + $align_shift_x, $vector_value_precision, PHP_ROUND_HALF_UP) + $actual_page_deltax;
                $y2 = round($word_splines[$i][$n+9] + $word_position_y + $extra_shift_y, $vector_value_precision, PHP_ROUND_HALF_UP);
                
                // if necessary correct position based on separate word_widths array
                if ($_SESSION['layouted_correct_word_width']) {
                    //echo "DRAW-CORRECTION: i: $i lx: " .$word_widths[$i][1]. " rx: ".$word_widths[$i][2] . "<br>";
                    $x1 += $word_widths[$i][1] ;
                    $q1x += $word_widths[$i][1] ;
                    $q2x += $word_widths[$i][1] ;
                    $x2 += $word_widths[$i][1] ;
                }
                
                $absolute_thickness = $stroke_width * $relative_thickness; // echo "splines($n+8+offs_dr) = " . $splines[$n+8+5] . " / thickness(before) = $absolute_thickness / ";
                // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
                // this method doesn't work with n, m, b ... why???
                if ($word_splines[$i][$n+(1*tuplet_length)+offs_dr] == draw_no_connection) { $absolute_thickness = 0; /*$color_htmlrgb="red";*/ /*$x2 = $x1; $y2 = $y1;*/} //echo "absolute_thickness(after) = $absolute_thickness<br>"; // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
                // correct control points if following point is non-connecting (see CalculateWord() for more detail)
                // search 2 tuplets ahead because data af knot 2 is stored in preceeding knot 1 (so knot 3 contains draw_no_connection info at offset offs_dr) 
                if ($word_splines[$i][$n+(2*tuplet_length)+offs_dr] == draw_no_connection) { $q2x = $x2; $q2y = $y2; } 
            
                //echo "ins: wrd($i): n=$n => path: x1: $x1 y1: $y1 q1x: $q1x q1y: $q1y q2x: $q2x q2y: $q2y x2: $x2 y2: $y2<br>";
                $stroke_dasharray=GetLineStyle();
                $svg_string .= "<path d=\"M $x1 $y1 C $q1x $q1y $q2x $q2y $x2 $y2\" stroke-dasharray=\"$stroke_dasharray\" stroke=\"$color_htmlrgb\" stroke-width=\"$absolute_thickness\" shape-rendering=\"geometricPrecision\" fill=\"none\" />\n";        
                $svg_string .= "$polygon_spline\n";
            }    
            // insert separate spline 
            // (note: there's only one separate spline per word ... which means: only one token can use a diacritic token per word ...
            for ($n=0; $n<count($word_separate_spline[$i])-tuplet_length; $n+=tuplet_length) {
                
                $x1 = round($word_separate_spline[$i][$n] + $word_position_x + $align_shift_x, $vector_value_precision, PHP_ROUND_HALF_UP) + $actual_page_deltax;
                $y1 = round($word_separate_spline[$i][$n+1] + $word_position_y + $extra_shift_y, $vector_value_precision, PHP_ROUND_HALF_UP);
                $q1x = round($word_separate_spline[$i][$n+2] + $word_position_x + $align_shift_x, $vector_value_precision, PHP_ROUND_HALF_UP) + $actual_page_deltax;
                $q1y = round($word_separate_spline[$i][$n+3] + $word_position_y + $extra_shift_y, $vector_value_precision, PHP_ROUND_HALF_UP);
                $relative_thickness = $word_separate_spline[$i][$n+4];
                $relative_thickness = AdjustThickness($relative_thickness);
                
                $unused = $word_separate_spline[$i][$n+5];
                $q2x = round($word_separate_spline[$i][$n+6] + $word_position_x + $align_shift_x, $vector_value_precision, PHP_ROUND_HALF_UP) + $actual_page_deltax;
                $q2y = round($word_separate_spline[$i][$n+7] + $word_position_y + $extra_shift_y, $vector_value_precision, PHP_ROUND_HALF_UP);
                $x2 = round($word_separate_spline[$i][$n+8] + $word_position_x + $align_shift_x, $vector_value_precision, PHP_ROUND_HALF_UP) + $actual_page_deltax;
                $y2 = round($word_separate_spline[$i][$n+9] + $word_position_y + $extra_shift_y, $vector_value_precision, PHP_ROUND_HALF_UP);
                $absolute_thickness = $stroke_width * $relative_thickness; // echo "splines($n+8+offs_dr) = " . $splines[$n+8+5] . " / thickness(before) = $absolute_thickness / ";
                
                // if necessary correct position based on separate word_widths array
                if ($_SESSION['layouted_correct_word_width']) {
                    //echo "DRAW-CORRECTION: i: $i lx: " .$word_widths[$i][1]. " rx: ".$word_widths[$i][2] . "<br>";
                    $x1 += $word_widths[$i][1] ;
                    $q1x += $word_widths[$i][1] ;
                    $q2x += $word_widths[$i][1] ;
                    $x2 += $word_widths[$i][1] ;
                }
                
                // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
                // this method doesn't work with n, m, b ... why???
                if ($word_separate_spline[$i][$n+(1*tuplet_length)+offs_dr] == draw_no_connection) { $absolute_thickness = 0; /*$color_htmlrgb="red";*/ /*$x2 = $x1; $y2 = $y1;*/} //echo "absolute_thickness(after) = $absolute_thickness<br>"; // quick and dirty fix: set thickness to 0 if following point is non-connecting (no check if following point exists ...)
                // correct control points if following point is non-connecting (see CalculateWord() for more detail)
                // search 2 tuplets ahead because data af knot 2 is stored in preceeding knot 1 (so knot 3 contains draw_no_connection info at offset offs_dr) 
                if ($word_separate_spline[$i][$n+(2*tuplet_length)+offs_dr] == draw_no_connection) { $q2x = $x2; $q2y = $y2; } 
            
                //echo "ins: wrd($i): n=$n => path: x1: $x1 y1: $y1 q1x: $q1x q1y: $q1y q2x: $q2x q2y: $q2y x2: $x2 y2: $y2<br>";
                $svg_string .= "<path d=\"M $x1 $y1 C $q1x $q1y $q2x $q2y $x2 $y2\" stroke-dasharray=\"$stroke_dasharray\" stroke=\"$color_htmlrgb\" stroke-width=\"$absolute_thickness\" shape-rendering=\"geometricPrecision\" fill=\"none\" />\n";        
                
            }
            
            // correct position if width correction is active
            if ($_SESSION['layouted_correct_word_width']) $word_position_x += $word_widths[$i][0] + $word_widths[$i][1] + $word_widths[$i][2] + $normal_distance + $align_shift_x;
            else $word_position_x += $word_width[$i] + $normal_distance + $align_shift_x;
        }
      }
    }
    return $svg_string;            
}

function GetWidthNormalTextAsLayoutedSVG( $single_word, $size) {
    //$width = $size * 0.59871795 * mb_strlen( $text ) + 6;                   // empirical value for courrier font
    $single_word = html_entity_decode($single_word);
    //$width = $size * 0.59871795 * mb_strlen( $single_word );                   // empirical value for courrier font
    $width = $size * 0.59871795 * (mb_strlen( $single_word )+0.5);               // +0.5 quick fix to have some spaces between text ... 
    return $width;
}

function ToRoman($num) { 
    // Be sure to convert the given parameter into an integer
    $n = intval($num);
    $result = ''; 
 
    // Declare a lookup array that we will use to traverse the number: 
    $lookup = array(
        'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 
        'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 
        'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
    ); 
 
    foreach ($lookup as $roman => $value) {
        // Look for number of matches
        $matches = intval($n / $value); 
 
        // Concatenate characters
        $result .= str_repeat($roman, $matches); 
 
        // Substract that from the number 
        $n = $n % $value; 
    } 
    return $result; 
}

function FormatPageNumber($p, $t, $l = "", $r = "") {
    // can be used to transform raw page number ($p) to different formats using types ($t) and left / right strings ($l, $r), e.g:
    // type ($t):
    // numeric or default: no transformation
    // alpha_lower: 1, 2, 3 ... => a, b, c ...
    // alpha_upper: 1, 2, 3 ... => A, B, C ...
    // roman_lower: 1, 2, 3 ... => i, ii, ii ...
    // roman_upper: 1, 2, 3 ... => I, II, II ...
    // left / right strings ($l, $r):
    // $l = "- " / $r = " -": 1, 2, 3 ... => - 1 -, - 2 -, - 3 - ...
    // default value (if omitted) is an empty string
   
    // transform to alpha or roman
    switch ($t) {
        case "numeric" : $output = $p; break;
        case "alpha_lower" : $output = chr($p + ord('a') - 1); break;
        case "alpha_upper" : $output = chr($p + ord('A') - 1); break;
        case "roman_lower" : $output = mb_strtolower(ToRoman($p)); break;
        case "roman_upper" : $output = ToRoman($p); break;
        default : $output = $p; 
    }
    
    // add left / right part
    $output = $l . $output .$r;
    
    //echo "formatted: $output ord('a'): " . ord('a') . " chr(97): " . chr(97) . "<br>";
    return $output;
}

function InsertPageNumber() {
    global $actual_page_number, $actual_page_deltax;
    $output = "";
    // IMPORTANT:
    // the book layout (deltax for odd vs even pages) doesn't take are of first and start values 
    // it is always calculated directly from raw variable actual_page_number!
    // due to inverted variables, take deltax value from last page
    $last_page_deltax = GetDeltaXForActualPage($actual_page_number-1);
    //echo "actual_page_number: $actual_page_number actual_page_deltax: $actual_page_deltax last_page_deltax: $last_page_deltax<br>"; 
    $first = $_SESSION['output_page_number_first'];     // first page number that has to be printed
    $start = $_SESSION['output_page_number_start'];     // first page on which first page number has to be printed
    $posx = $_SESSION['output_page_number_posx'] + $last_page_deltax;
    $posy = $_SESSION['output_page_number_posy'];
    $color = $_SESSION['output_page_number_color'];
    
    switch ($_SESSION['output_page_number_yesno']) {
        case "yes" : if ($actual_page_number >= $start) {
                        $print_number = $actual_page_number - $start + $first;
                        if ($_SESSION['page_number_formatting_yesno']) $formatted_number = FormatPageNumber($print_number, $_SESSION['page_number_format'], $_SESSION['page_number_format_left'], $_SESSION['page_number_format_right']);
                        else $formatted_number = $print_number;
                        $output = "<text x='$posx' y='$posy' fill='$color' text-anchor='middle'>$formatted_number</text>";
                    }
                    break;
    }
    // must be done outside of this function with book options active
    $actual_page_number++;      // InsertPageNumber() increments page number (in order to be sure that it's incremented each time function is called and only here)
    $actual_page_deltax = GetDeltaXForActualPage($actual_page_number); // adjust global variable actual_page_deltax just once (after incrementing actual_page_number)
    return $output;
}

function InsertLineNumbers() {
    global $baseline_y, $standard_height, $distance_words, $original_word, $combined_pretags, $combined_posttags, $html_pretags, $html_posttags, $result_after_last_rule,
        $global_debug_string, $global_number_of_rules_applied, $actual_page_number, $actual_page_deltax;
    
   $output = "";
   $output_line_numbers_on_this_page = false;
   //echo "layouted_book_yesno: >" . $_SESSION['layouted_book_yesno'] . "<<br>";
   // define x position for line number
   $posx = $_SESSION['output_line_number_posx']; // default value
   // check if book layout is selected and adjust value if necessary
   // We're definitely fighting spaghetti code here ... turns out that the algorithm works, BUT:
   // things are exactly the other way around, i.e: deltax and line numbers are inserted on odd
   // pages when even pages are selected and viceversa ... I suppose this comes from the fact
   // that page number is automatically incremented when InsertPageNumber() is called.
   // separating incrementation from this function is not possible (produces strange horizontal
   // layouts). So personally I think it's better to live with this imperfection and simply
   // change the labelling of the fields in the input form, i.e: where it says "even" actually
   // the odd value is set and viceversa ...
   if ($_SESSION['layouted_book_yesno']) {
        switch ($actual_page_number % 2) { 
            case 1 : $posx = $_SESSION['layouted_book_lines_posx_odd'] + $actual_page_deltax; 
                     if ($_SESSION['layouted_book_lines_odd_yesno']) $output_line_numbers_on_this_page = true;
                     break;
            case 0 : $posx = $_SESSION['layouted_book_lines_posx_even'] + $actual_page_deltax;
                     if ($_SESSION['layouted_book_lines_even_yesno']) $output_line_numbers_on_this_page = true;
                     break;
        }
   }
   //echo "actual_page_number: >$actual_page_number< actual_page_deltax: >$actual_page_deltax< output_line_numbers_on_this_page: >$output_line_numbers_on_this_page<<br>";
   if ((($_SESSION['layouted_book_yesno'] === false) && ($_SESSION['output_line_number_yesno']))
       || (($_SESSION['layouted_book_yesno']) && ($output_line_numbers_on_this_page))) {   
        //echo "page: $actual_page_number => print line numbers on posx: $posx<br>";
        $standard_height = 10; // why does standard_height get modified?!? (shouldn't!)
        $system_line_height = $standard_height * $_SESSION['token_size'];
        $num_system_lines = $_SESSION['num_system_lines'];
        $line_height = $system_line_height * $num_system_lines; 
   
        $token_size = $_SESSION['token_size'];
        $session_baseline = $_SESSION['baseline'];
        $top_margin = $_SESSION['top_margin'];
        $bottom_margin = $_SESSION['bottom_margin'];
    
        $top_start_on_page = $standard_height * $token_size * $session_baseline + $top_margin;
        $starty = $top_start_on_page;
        $bottom_limit = $max_height-$bottom_margin; // -$line_height; // baseline_y-bug: impossible to set baseline to 0 in calculation; extra_shift_y to correct bug etc. => has to be investigated!
    
        $step = $_SESSION['output_line_number_step'];
        // $posx = $_SESSION['output_line_number_posx'];  // has been defined above
        $color = $_SESSION['output_line_number_color'];
    
        $loop_end = (int)(($_SESSION['output_height'] - $bottom_margin - $starty) / $line_height) + 1;
        /*
        for ($i=1; $i<$loop_end; $i++) {
            $posy = $starty + ($i * $line_height);
            $output .= "<text x='$posx' y='$posy' fill='$color'>$i</text>";
        }
        */
        //echo "starty: $starty line_height: $line_height standard_height: $standard_height num_system_lines: $num_system_lines SESSION[token_size]: " . $_SESSION['token_size'] . " <br>";
        //echo "bottom_margin: $bottom_margin loop_end: $loop_end<br>";
        for ($i=0; $i<$loop_end; $i++) {
            $posy = $starty + $i*$line_height - $_SESSION['output_line_number_deltay'];
            $print_i = $i+1;
            if ($print_i%$step === 0) $output .= "<text x='$posx' y='$posy' fill='$color' font-size='10' text-anchor='end'>$print_i</text>";
        }
    }   
    return $output;
}

function InsertSeparatePageForOriginalText($max_width, $max_height, $svg_string, $original_text_last_page_buffer, $where) {
    global $actual_page_number, $actual_page_deltax;
    if ($where === "before") {
            // adjust page number
            //echo "actual_page_number: $actual_page_number<br>";
            $actual_page_number -= 2;   // reference is first shorthand page: if this one has page number 1, preceeding page with original text will have number 0
            $actual_page_deltax = GetDeltaXForActualPage($actual_page_number); // no guaranteed to work ...
            $separate_page = GetCompleteSVGTextPage($max_width, $max_height, $_SESSION['layouted_original_text_size'], $original_text_last_page_buffer);
            // restore original page number + 1
            $actual_page_number += 2; // only add 2 because actual page number has be incremented by InsertPageNumber() called via GetCompleteSVGTextPage()
            $actual_page_deltax = GetDeltaXForActualPage($actual_page_number); // no guaranteed to work ...
            $svg_string = preg_replace("/#P#L#A#C#E#H#O#L#D#E#R#B#E#F#O#R#E#/", "\n$separate_page", $svg_string);     
    } else {
            // if page with original text comes after page with shorthand text, no adaptions are necessary
            $separate_page = GetCompleteSVGTextPage($max_width, $max_height, $_SESSION['layouted_original_text_size'], $original_text_last_page_buffer);
            $svg_string = preg_replace("/#P#L#A#C#E#H#O#L#D#E#R#B#E#F#O#R#E#/", "\n", $svg_string);
            $svg_string .= "\n$separate_page\n";
    }    
    return $svg_string;
}

function FilterOriginalWord($word) {
    // rules that modify the original text in stage 0 (= entire text) modify the final result of the text in the parallel edition
    // therefore, offer two possibilities to filter out "artefacts", i.e. additional tokens introduced by such rules:
    // - brackets: all [] are filtered out (bundling when several characters correspond to one token)
    // - dash: all # are filtered out (phonetical transcription with eSpeak)
    if ($_SESSION['layouted_original_text_filter_brackets']) $word = preg_replace("/\[(.*?)\]/","$1", $word);
    if ($_SESSION['layouted_original_text_filter_dashes']) $word = preg_replace("/#/","", $word);
    return $word;
}

function GetDeltaXForActualPage( $page ) {
    switch ($page % 2) {
        // return delta x for left (odd) page
        case 1 : if ($_SESSION['layouted_book_yesno']) return $_SESSION['layouted_book_deltax_odd'];
                 else return 0; 
                 break;
        // return delta x for right (even) page
        case 0 : if ($_SESSION['layouted_book_yesno']) return $_SESSION['layouted_book_deltax_even'];
                 else return 0;
                 break;
    }
}

function InsertPageAndTextDimensions() {
    $output="";
    if ($_SESSION['layouted_book_page_dimension_yesno']) {
        $x1 = $_SESSION['layouted_book_page_dimension_x1'];
        $y1 = $_SESSION['layouted_book_page_dimension_y1'];
        $width = $_SESSION['layouted_book_page_dimension_x2'] - $x1;
        $height = $_SESSION['layouted_book_page_dimension_y2'] - $y1;
        $color = $_SESSION['layouted_book_page_dimension_color'];
        $output .= "<rect x='$x1' y='$y1' width='$width' height='$height' style='stroke:$color;stroke-width:5;stroke-width:5;fill:white;fill-opacity:1' />";
    }
/*
    if ($_SESSION['layouted_book_text_dimension_yesno']) {
        $x1 = $_SESSION['layouted_book_text_dimension_x1'];
        $y1 = $_SESSION['layouted_book_text_dimension_y1'];
        $width = $_SESSION['layouted_book_text_dimension_x2'] - $x1;
        $height = $_SESSION['layouted_book_text_dimension_y2'] - $y1;
        $color = $_SESSION['layouted_book_text_dimension_color'];
        // correct x
        $x1 += GetDeltaXForActualPage();
        $output .= "<rect x='$x1' y='$y1' width='$width' height='$height' style='stroke:$color;stroke-width:5;fill:white;fill-opacity:1' />";
    }
*/
    return $output;
}

function CalculateLayoutedSVG( $text_array ) {
    // function for layouted svg
    global $baseline_y, $standard_height, $distance_words, $original_word, $combined_pretags, $combined_posttags, $html_pretags, $html_posttags, $result_after_last_rule,
        $global_debug_string, $global_number_of_rules_applied, $actual_page_number, $actual_page_deltax, $word_tags, $word_position_y;
    global $word_widths; // declare it global instead of adapting the function_exists and all function calls ...
    // set variables
    $actual_page_number = 1;
    $actual_page_deltax = GetDeltaXForActualPage($actual_page_number);
    $original_text_last_page_buffer = "";
    //$left_margin = 5; $right_margin = 5;
    //$num_system_lines = 3;  // inline = 6 (default height); 5 means that two shorthand text lines share bottom and top line; 4 means that they share 2 lines aso ...
    $system_line_height = $standard_height * $_SESSION['token_size'];
    //echo "calculatlay: sys_lin_hght = $system_line_height<br>std_height=$standard_height<br>token_size=" . $_SESSION['token_size'] . "<br>";
    $num_system_lines = $_SESSION['num_system_lines'];
    $line_height = $system_line_height * $num_system_lines;   // these abandoned variables cause problems with fortune cookie (?) ... => checked: it's not the variables (they seem to be fine)
   // $line_height = $system_line_height * $_SESSION['num_system_lines'];
    //echo "line_height = $line_height (sys_lin_height = $system_line_height / num_sys_lin = $num_system_lines<br>";
    $token_size = $_SESSION['token_size'];
    $session_baseline = $_SESSION['baseline'];
    $top_margin = $_SESSION['top_margin'];
    $bottom_margin = $_SESSION['bottom_margin'];
    $left_margin =  $_SESSION['left_margin'];
    $right_margin =  $_SESSION['right_margin'];
    $top_start_on_page = $standard_height * $token_size * $session_baseline + $top_margin;
    $starty = $top_start_on_page;
    $word_position_x = $left_margin; $max_width = $_SESSION['output_width'];
    $word_position_y = $starty; $max_height = $_SESSION['output_height'];
    $bottom_limit = $max_height-$bottom_margin; // -$line_height; // baseline_y-bug: impossible to set baseline to 0 in calculation; extra_shift_y to correct bug etc. => has to be investigated!
    
    $placeholder = ($_SESSION['layouted_original_text_yesno']) ? "#P#L#A#C#E#H#O#L#D#E#R#B#E#F#O#R#E#" : "";
    
    // insert separate page here if it should be displayed before shorthand page
    $svg_string = "$placeholder<svg width=\"$max_width\" height=\"$max_height\"><g stroke-linecap=\"miter\" stroke-linejoin=\"miter\" stroke-miterlimit=\"20\" style=\"shape-rendering:geometricPrecision\">\n";
    $svg_string .= InsertPageNumber();
    $svg_string .= InsertLineNumbers();
    $svg_string .= InsertPageAndTextDimensions();
    
    if ($_SESSION['show_margins']) {
        // rectangle to show width&heigt of svg
        $svg_string .= "<rect width=\"$max_width\" height=\"$max_height\" style=\"fill:white;stroke:red;stroke-width:5;opacity:0.5\" />";
        //rectangle to show margin
        $mx = $_SESSION['left_margin'];
        $my = $_SESSION['top_margin'];
        $mwidth = $max_width - $mx - $_SESSION['right_margin'];
        $mheight = $max_height - $my - $_SESSION['bottom_margin'];
        // echo "mx: $mx my: $my mwidth: $mwidth mheight: $mheight<br>";
        $svg_string .= "<rect x=\"$mx\" y=\"$my\" width=\"$mwidth\" height=\"$mheight\" style=\"fill:white;stroke:green;stroke-width:1;opacity:0.5\" />";
    }
    
    //echo "before<br>";
    // the following line causes troubles with fortune cookies !!!!!!
    $svg_string .= InsertAuxiliaryLinesInLayoutedSVG( $word_position_y, $system_line_height, $line_height);
    //echo "after<br>";
    //echo "standard_height: $standard_height line_height: $line_height starty: $starty token_size: $token_size session_baseline: $session_baseline top_margin: $top_margin num_system_lines: $num_system_lines word_position_y: $word_position_y<br>";
    //echo "auxiliary: " . htmlspecialchars(InsertAuxiliaryLinesInLayoutedSVG()) . "<br>";
            
    //$svg_string .= "<line x1=\"0\" y1=\"1\" x2=\"$max_width\" y2=\"1\" style=\"stroke:red;stroke-width:1\" />";
    //$svg_string .= "<line x1=\"$max_width\" y1=\"1\" x2=\"$max_width\" y2=\"$max_height\" style=\"stroke:red;stroke-width:1\" />";
    
    $temp_width = 0;
    $actual_word = 0;
    $text_array_length = count($text_array);
    $collected_inline_option_tags = "";
    
    foreach ( $text_array as $key => $single_word ) {
            $global_debug_string = ""; // even if there is no debug output in layouted svg, set $debug_string = "" in order to avoid accumulation of data in this variable by parser functions
    //if ($_SESSION['token_type'] === "shorthand") {
     //       echo "-------------------------------------------- $single_word -----------------------------------------<br>";
            $original_word = $single_word;
            //echo "-----------------------------<br>layoutedsvg: key: $key word: " . htmlspecialchars($single_word) . "<br>";
            $bare_word = GetWordSetPreAndPostTags( $single_word ); // ???"<@token_type=\"svgtext\">" );
            $temp_pre = $combined_pretags;
            $collected_inline_option_tags .= $temp_pre; ///////////////////// is this correct ?!?!?!?
            $temp_post = $combined_posttags;
     //       echo "pro/bare/post: $temp_pre - $bare_word - $temp_post<br>";
            $result_after_last_rule = $bare_word;
            //echo "CalculateLayouted(): bare_word = $bare_word pretags: $temp_pre posttags: $temp_post<br>";
            /*
            $bare_word = GetWordSetPreAndPostTags( $single_word );
            $temp_pre = $combined_pretags;
            $temp_post = $combined_posttags;
            */ 
            
            $tokenlist = NormalText2TokenList( $bare_word );
            //var_dump($tokenlist);
            //echo "pretags: " . htmlspecialchars($pre) . "<br>";
            //echo "Session(token_color): " . $_SESSION['token_color'] . "<br>";
            $pre_html_tag_list = "";                                                             // must be set to "", because following options returns tags that aren't there ... ?!?
            if (mb_strlen($temp_pre)>0) $pre_html_tag_list = ParseAndSetInlineOptions( $temp_pre );        // must be a bug in ParseAndSetInlineOptions() ... !!! => fix it later, workaround works for the moment
            $html_pretags = $pre_html_tag_list;
            //echo "Session(token_color): " . $_SESSION['token_color'] . "<br>";
            
            //echo "====> set inline options: " . htmlspecialchars($pre) . " session_token_type: " . $_SESSION['token_type'] . "<br>";
            
            // non breaking breaking (= blocked breaking) should only occur with empty lines
            // i.e. when word_position_x is equal to left_margin
            // if line contains some word, the breaking should occur
            // test this and send boolean variable to LayoutedSVGProcessHTMLTags()
            if ($word_position_x == $left_margin) $ignore_non_breaking = true;
            else $ignore_non_breaking = false;
            
            $number_linebreaks = LayoutedSVGProcessHTMLTags( $pre_html_tag_list, $ignore_non_breaking ); 
            
            //echo "number_linebreaks: $number_linebreaks<br>";
            
            $angle = $_SESSION['token_inclination'];
            $stroke_width = $_SESSION['token_thickness'];
            $scaling = $_SESSION['token_size'];
            $color_htmlrgb = $_SESSION['token_color'];
            // $stroke_dasharray = $_SESSION['token_style_custom_value'];
            
            
            //echo "tokenlist: $tokenlist isset(): " . isset($tokenlist) . " count(): " . count($tokenlist) . "<br>";
            if ((count($tokenlist) > 0) || ($_SESSION['token_type'] === "svgtext")) { // adapt this for svgtext!
                //echo "Processing tokenlist ...<br>";
                //echo "inserting: key: $key word: $single_word => word_splines($actual_word)<br>";
      //          echo "collected inline-option tags: $collected_inline_option_tags<br>"; 
      //          echo "collected post inline-option tags: $combined_posttags<br>"; 
                
                //echo "token_type: " . $_SESSION['token_type'] . "<br>";
                // build a "parallel" array: $word_tags[$actual_word] contains all inline-options belonging to 
                // (preceeding the) $word_splines[$actual_word]
                $word_tags[$actual_word] = $combined_posttags; // $collected_inline_option_tags; // ist this correct ?!?!?!?!?
                $collected_inline_option_tags = ""; // reset variable for next word
                //var_dump($word_tags);
                
                if (($_SESSION['token_type'] === "shorthand") || ($_SESSION['token_type'] === "handwriting")) {
                    if ($_SESSION['token_type'] === "handwriting") {
                        // create tokenlist for handwriting
                        //echo "word: " . $word_separate_spline[$actual_word] . " - $bare_word<br>"; 
                        $hw_meta = GetHandwriting($bare_word);
                        //echo "Handwriting: $hw_meta<br>";
                        $tokenlist = MetaForm2TokenList($hw_meta);
                        //$output .= TokenList2SVG( $hw_token_list, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], $_SESSION['token_style_custom_value'], $alternative_text );
                    
                    }
                    list( $word_splines[$actual_word], $word_separate_spline[$actual_word], $delta_width, $correction_lx, $correction_rx) = TokenList2WordSplines( $tokenlist, $angle, $scaling, $color_htmlrgb, GetLineStyle());
                    //echo "actual_word: $actual_word delta_width: $delta_width correction_lx: $correction_lx correction_rx: $correction_rx temp_width: $temp_width<br>";
                    
                    
                    // calculate correct width to use inside one line
                    if ($_SESSION['layouted_correct_word_width']) {
                        // correct left x: can be corrected whenever word is not at position 0 (= to the left at the beginning of the line)
                        if ($actual_word > 0) $additional_correction_lx = $correction_lx;
                        else $additional_correction_lx = 0;
                        
                        // right x is more complicated: first check if word overpasses right margin
                        // if yes: the previous (..) word should not have the correction => correct that
                        // => this word can have the correction
                        if ($temp_width + $delta_width + $additional_correction_lx > $max_width-$right_margin-$left_margin) {
                            // word overpasses right margin => this word can have the correction
                            $additional_correction_rx = $correction_rx;
                            // on the otherhand (if it overpasses) it should not have the left_x correction (readjust that)
                            $additional_correction_lx = 0;
                            // the previous word should align to the right (= should not have the correction)
                            if ($actual_word>0) $word_widths[$actual_word-1][2] = 0;
                        } else {
                            // there is still space left to the right after the word => suppose the correction can be used
                            // will be corrected if the following word overpasses
                            $additional_correction_rx = $correction_rx;
                        }
   //                     echo "additional_correction_lx: $additional_correction_lx additional_correction_rx: $additional_correction_rx<br>";
                        // write all that to a separate array ...
                        $word_widths[$actual_word] = array( $delta_width, $additional_correction_lx, $additional_correction_rx);
                    } else $word_widths = null;
                    
                    $word_width[$actual_word] = $delta_width;
                    //var_dump($word_splines[$actual_word]);
                    $temp_width += $distance_words + $delta_width + $additional_correction_lx + $additional_correction_rx;
                    //echo "tempwidth: $temp_width / delta_width: $delta_width<br>";
                    //echo "word: $single_word tempwidth: $temp_width / delta_width: $delta_width<br>";
                } else {
                    if (mb_strlen($bare_word)>0) {
                        
                        // BUG: all session-variables don't work in this part of code!!! REASON: no tokenlist is created, word is not drawn => this will only be done by 
                        // DrawOneLineInLayouted ... so either the whole line gets the color (if the line is full) or the line is drawn with old color, for example)
                        // Solution: 
                        // (1) Process pre/post-tags only inside DrawOneLineInLayouted ...
                        // (2) check Session-Variables inside DrawOneLineInLayouted SVG...
                        // => fix this later
                        
                        
                       //echo "single_word: $single_word bare_word: $bare_word => mach daraus text ...<br>";
                        $size = $_SESSION['svgtext_size']; 
                        //echo "text_size: $size session: " . $_SESSION['svgtext_size'] . "<br>";
                        $word_splines[$actual_word][0] =  "svgtext"; // use element 0 as indicator that it is svgtext for DrawOneLine...-function
                        $word_splines[$actual_word][1] =  $size; /// use element 2 for text_size
                        $word_splines[$actual_word][2] =  $bare_word; /// wrong - can be a tag: $single_word; // abuse word_splines to store svgtext as string ... 
                   
                        $word_width[$actual_word] = GetWidthNormalTextAsLayoutedSVG( $single_word, $size );
                        //echo "text_width = " . $word_width[$actual_word] . "<br>";
                        $temp_width += $distance_words + $word_width[$actual_word];
                        //if ($_SESSION['token_type'] === "svgtext") {
                            //$_SESSION['token_type'] = "shorthand";
                          //  echo "setze session token_type zurück<br>";
                        //}
                    }
                    //$temp = ParseAndSetInlineOptions( $pre ); // is done 2x for first pretag ...
                }
            }
            /*
            if (mb_strlen($post)>0) {
                $post_html_tag_list = ParseAndSetInlineOptions( $post );        // set inline options
                $svg .= $post_html_tag_list;
            }
            */ 
            if (mb_strlen($bare_word)>0) $actual_word++;
            
            //echo "number_linebreaks = $number_linebreaks<br>";
            
            if ($number_linebreaks > 0) {
                // echo "num_linebreaks: $number_linebreaks => inserting linebreak ...<br>";
                $last_word = $actual_word;
                // echo "Drawoneline: word_position_x: $word_position_x word_position_y: $word_position_y word_splines: $word_splines word_width: $word_width last_word: $last_word <br>";
                $svg_string .= DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_separate_spline, $word_width, $last_word, true );
                //$last_word_splines = $word_splines[$actual_word-1];
                unset($word_splines);
                //$word_splines[0] = $last_word_splines;
                //$word_width[0] = $word_width[$actual_word-1];
                $actual_word = 0;
                //$old_temp_width = $temp_width;
                //$last_word = 2;
                $temp_width = 0;
                $word_position_x = $left_margin;
                //echo "add several lines ($number_linebreaks)<br>";
                $word_position_y += $line_height * $number_linebreaks;  // what happens if number_linebreaks exceeds bottom limit ... ?!?
                //if ($number_linebreaks == 9999) $word_position_y = $top_start_on_page; // doesn't work: newpage is drawn at correct y position but on the SAME page (no new svg)
            }
            
            // echo "position_x: $word_position_x temp_width: $temp_width<br>";
            
            //echo "key: " . $key . " count: " . count($text_array) . "<br>";
            if (($temp_width > $max_width-$right_margin-$left_margin) || ($key == $text_array_length-1)) {
            // if ($temp_width > $max_width) {
                //echo "Draw actual line at key: $key word: $single_word temp_width: $temp_width actual_word: $actual_word<br>";
                // we have reached end of actual line => draw that line and set curser at beginning of next line
                $word_position_x = $left_margin;
                //echo "actual_word: $actual_word<br>";

                //$last_word = (($key == $text_array_length-1) && ($temp_width <= $max_wdith-$right_margin)) ? $actual_word : $actual_word-1;
               //$last_word = (($key == $text_array_length-1) && ($temp_width <= $max_wdith-$right_margin)) ? $actual_word : $actual_word-1;
                //echo "last_word: $last_word<br>";
                //$svg_string .= DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_width, $last_word );
                            
                
                if (($temp_width < $max_width-$right_margin) && ($key == $text_array_length-1)) {
                    //echo "Draw shorter (incomplete) line before leaving foreach-loop<br>";
                    $last_word = $actual_word;
                    $svg_string .= DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_separate_spline, $word_width, $last_word, true );
                } else {
                    $last_word = (($key == $text_array_length-1) && ($temp_width <= $max_wdith-$right_margin)) ? $actual_word : $actual_word-1;
                    $svg_string .= DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_separate_spline, $word_width, $last_word, false );
                }
                //echo "actual_word: $actual_word<br>";
                //echo "actual_word[0]: " . $word_splines[$actual_word][0] . "<br>";
                
                $last_word_splines = $word_splines[$actual_word-1];
                // bugfix: save also separate spline of last word
                $last_word_separate_spline = $word_separate_spline[$actual_word-1];
                unset($word_splines);
                // bugfix: unset also separate spline of last word
                unset($word_separate_spline);
                $word_splines[0] = $last_word_splines;
                if ($_SESSION['layouted_correct_word_width']) {
                    // in addition to word_splines[0] corrected word widths must also be copied
                    $word_widths[0] = $word_widths[$actual_word-1];
                }
                // bugfix: copy also separate spline of last word
                $word_separate_spline[0] = $last_word_separate_spline;
                $word_width[0] = $word_width[$actual_word-1];
                $actual_word = 1;
                $old_temp_width = $temp_width;
                $last_word = 2;
                $temp_width = $left_margin + $word_width[0];
                //echo "add one line ...<br>";
                $word_position_y += $line_height;
                // echo "word_position_y: $word_position_y bottom_limit: $bottom_limit<br>";
                
                
            }
            if (($word_position_y > $bottom_limit) && ($key != $text_array_length-1)) {
                //echo "<br>-------------------------------<br>start new page (linebreaks = $number_linebreaks)...<br>";
                //echo "word_position_y: $word_position_y max_height: $max_height bottom_margin: $bottom_margin => start new svg ...<br>";
                // close svg-tag 
                $svg_string .= "</g>$svg_not_compatible_browser_text</svg>";
                //echo "text last page<br>$original_text_last_page_buffer<br>";
                // insert original text as separate page
                
                if ($_SESSION['layouted_original_text_yesno']) 
                    $svg_string = InsertSeparatePageForOriginalText($max_width, $max_height, $svg_string, $original_text_last_page_buffer, $_SESSION['layouted_original_text_position']);
                /*
                $where = "after"; // insert it before or after shorthand page
                if ($where === "before") {
                    // adjust page number
                    //echo "actual_page_number: $actual_page_number<br>";
                    $actual_page_number -= 2;   // reference is first shorthand page: if this one has page number 1, preceeding page with original text will have number 0
                    $separate_page = GetCompleteSVGTextPage($max_width, $max_height, 20, $original_text_last_page_buffer);
                    // restore original page number + 1
                    $actual_page_number += 2; // only add 2 because actual page number has be incremented by InsertPageNumber() called via GetCompleteSVGTextPage()
                    $svg_string = preg_replace("/#P#L#A#C#E#H#O#L#D#E#R#B#E#F#O#R#E#/", "\n$separate_page", $svg_string);     
                } else {
                    // if page with original text comes after page with shorthand text, no adaptions are necessary
                    $separate_page = GetCompleteSVGTextPage($max_width, $max_height, 20, $original_text_last_page_buffer);
                    $svg_string = preg_replace("/#P#L#A#C#E#H#O#L#D#E#R#B#E#F#O#R#E#/", "\n", $svg_string);
                    $svg_string .= "\n$separate_page\n";
                }
                */
                // reset and reinitialize text buffer for original text
                // filter word if necessary
                $filtered_word = FilterOriginalWord($single_word);
                $original_text_last_page_buffer = "$filtered_word ";
                // reopen svg-tag 
                $svg_string .= "$placeholder<svg width=\"$max_width\" height=\"$max_height\"><g stroke-linecap=\"miter\" stroke-linejoin=\"miter\" stroke-miterlimit=\"20\">\n";
                // insert page number
                $svg_string .= InsertPageNumber();
                $svg_string .= InsertLineNumbers();
                $svg_string .= InsertPageAndTextDimensions();
    
                 // rectangle to show width&heigt of svg
                if ($_SESSION['show_margins']) $svg_string .= "<rect width=\"$max_width\" height=\"$max_height\" style=\"fill:white;stroke:red;stroke-width:5;opacity:0.5\" />";
                // insert auxiliary lines
                $svg_string .= InsertAuxiliaryLinesInLayoutedSVG( $starty, $system_line_height, $line_height);
                //$svg_string .= InsertAuxiliaryLinesInLayoutedSVG();
                //echo "auxiliary: " . htmlspecialchars(InsertAuxiliaryLinesInLayoutedSVG()) . "<br>";
    //echo "baseline_y: $baseline_y<br>";
        
                // $word_position_y = $baseline_y- (10 * $_SESSION['token_size']) + $top_margin; // baseline_bug ....................................
                $word_position_y = $top_start_on_page;
                /*
                if ($number_linebreaks == 9999) {
                    echo "case 1: word_position_y = $word_position_y top_start_on_page = $top_start_on_page number_linebreaks = $number_linebreaks<br>";
                    $word_position_y -= (10 * $_SESSION['token_size']) * $_SESSION['num_system_lines'];
                }*/
            } else {
                $filtered_word = FilterOriginalWord($single_word);
                $original_text_last_page_buffer .= "$filtered_word ";
            }
    //} else {
        // NormalText2NormalTextInLayoutedSVG();
    //}   
    
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
            if ($_SESSION['show_margins']) $svg_string .= "<rect width=\"$max_width\" height=\"$max_height\" style=\"fill:white;stroke:red;stroke-width:5;opacity:0.5\" />";
            // insert auxiliary lines
            $svg_string .= InsertAuxiliaryLinesInLayoutedSVG( $starty, $system_line_height, $line_height);
            //echo "auxiliary: " . htmlspecialchars(InsertAuxiliaryLinesInLayoutedSVG()) . "<br>";
            $word_position_y = $baseline_y- (10 * $_SESSION['token_size']) + $top_margin; // baseline_bug ....................................
            //if ($number_linebreaks == 9999) $word_position_y -= (10 * $_SESSION['token_size']) * $_SESSION['num_system_lines']; // NOT TESTED
            /*
            if ($number_linebreaks == 9999) {
                echo "case 2<br>";
                $word_position_y = $top_start_on_page; // NOT TESTED
            }
            */
        }
        //echo "insert last line<br>";
        //var_dump($word_splines);
        $svg_string .= DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_separate_spline, $word_width, $last_word, true );
        
    } 
    /*elseif ($old_temp_width <= $max_width-$right_margin) {
        echo "Draw shorter (incomplete) line<br>";
        $svg_string .= DrawOneLineInLayoutedSVG( $word_position_x, $word_position_y, $word_splines, $word_width, $last_word );
    }*/
    
    
    $svg_string .= "</g>$svg_not_compatible_browser_text</svg>";
    if ($_SESSION['layouted_original_text_yesno'])
        $svg_string = InsertSeparatePageForOriginalText($max_width, $max_height, $svg_string, $original_text_last_page_buffer, $_SESSION['layouted_original_text_position']);
    return $svg_string;
}
/*
function StripOutPunctuation( $word ) {
    global $punctuation;
    $output = "";
    for ($i=0; $i<mb_strlen($word); $i++) {
            $character = mb_substr( $word, $i, 1);
            if (mb_strpos($punctuation, $character) === false) $output .= $character; // caution: automatic type cast in php: first position = 0 (is equal to false = character not found if == is used instead of ===!)
    }
    return $output;
}

// necessary to distinguish between normal punctuation (which includes , and ; for example) and punctuation followed by uppercase (like .!?)
function StripOutUpperCasePunctuation( $word ) {
    global $upper_case_punctuation;
    $output = "";
    for ($i=0; $i<mb_strlen($word); $i++) {
            $character = mb_substr( $word, $i, 1);
            if (mb_strpos($punctuation, $character) === false) $output .= $character; // caution: automatic type cast in php: first position = 0 (is equal to false = character not found if == is used instead of ===!)
    }
    return $output;    
}
*/

function CalculateTrainingSVG( $text_array ) {
    global $original_word, $combined_pretags, $html_pretags, $result_after_last_rule, $global_debug_string, $global_numbers_of_rules_applied, $std_form, $prt_form, 
        $separated_std_form, $separated_prt_form, $this_word_punctuation, $last_word_punctuation, $sentence_start, $upper_case_punctuation, $lin_form;
    $output = "";
    $sentence_start = true;
    
    $output .= "<div id=\"order\"><table>";
    $i = 0;
    foreach ( $text_array as $this_word ) {
        $global_debug_string = "";
        $global_number_of_rules_applied = 0;
        $bare_word = GetWordSetPreAndPostTags( $this_word );
        $before = $bare_word;
        //$bare_word_without_punctuation = StripOutPunctuation( $bare_word );
        list($pre, $bare_word, $post) = GetPreAndPostTokens( $bare_word );
        //echo "bare_word: $bare_word<br>";
        
        // handle lower/upper-case at beginning of a sentence
        if ($sentence_start) $checkbox_kleinschreibung = "<input type='checkbox' name='lowercase$i' value='1'> Kleinschreibung";
        else $checkbox_kleinschreibung = "";
        $last_char = mb_substr($post, mb_strlen($post)-1, 1);
        if (mb_strpos($upper_case_punctuation, $last_char) !== false) $sentence_start = true;
        else $sentence_start = false;
        
        //$html_pretags = ParseAndSetInlineOptions( $combined_pretags );
        $original_word = $bare_word;
        $result_after_last_rule = $bare_word;
        
       //echo "CalculateInlineSVG(): this_word: $this_word bare_word: $bare_word html_pretags: $html_pretags<br>";
        
        if (mb_strlen($bare_word)>0) {
            $alternative_text = ($_SESSION['output_texttagsyesno']) ? /*$SingleWord->Original*/ $bare_word : "";
            // echo "CalculateInlineSVG()1111: bare_word: $bare_word<br>";
            $output .= "<tr><td><center><i>$bare_word</i><br>";
            $output .= SingleWord2SVG( $bare_word, $_SESSION['token_inclination'], $_SESSION['token_thickness'], $_SESSION['token_size'], $_SESSION['token_color'], GetLineStyle(), $alternative_text);
            // since SingleWord2SVG is given a bare word (i.e. without punctuation), training_execute must handle global punctuation variables by itself
            // (when SingleWord2SVG is given a full word (i.e. with punctuation) it handles these variables automatically via GetPreAndPostTokens() - welcome to procedural programming ... ! ;-)
            // (actually training_execute must CORRECT these variables: GetPreAntPostTokens() is set anyway and sets them to wrong values!
            if ($sentence_start) {
                $this_word_punctuation = true;  
                $last_word_punctuation = false;
            } else { 
                $this_word_punctuation = false; 
                $last_word_punctuation = false; 
            }
            
            $output .= "</center></td>";
            //echo "std_form_upper: $std_form<br>";
            //$std_form_upper = ($_SESSION['token_type'] !== "handwriting") ? mb_strtoupper($std_form ) : $std_form;
            $std_form_upper = mb_strtoupper($std_form );
            
            // modification (05.03.19): use old cmp form as new lng (lin/ling) form
            // in the database: single_bas and separated_bas (in total there are 6 forms and 1 preference information in db)
            // many combinations are possible:
            // - default: write single or separated according to preference (eventual rules based separation MUST be DISABLED (not possible for the moment)
            // - override preference: use single or separated from database (rules based separation must be disabled)
            // - force (single or separated): stronger than override all subwords are written single or separated (in database they can be mixed, e.g. Bad|meister\stelle: Bad|meister = single; Badmeister\stelle = separated
            //   forced single: Bad|meister|stelle; force separated: Bad\meister\stelle
            // in addition: all words from database can have 1 (2) - 3 (6) forms. The user must have the possibility to chose which form should be taken
            // as the base for the calculation if several forms exist:
            // - only LNG/STD/PRT: calculate from LNG/STD/PRT
            // - LNG+STD: calculate from LNG or STD (user decides)
            // - LNG+STD+PRT: idem
            // - STD+PRT: idem
            // - LNG+PRT: idem (rather bizarre as a combination but theoretically possible)
            $output .= "<td>
                <input type='hidden' name='original$i' value='$bare_word'>
                <input type='radio' name='result$i' value='wrong$i'>F
                <input type='radio' name='result$i' value='correct$i'>R
                <input type='radio' name='result$i' value='undefined$i' checked>U $checkbox_kleinschreibung
                
                <br>
                <input type='checkbox' name='chkcut$i' value='chkcutyes$i'> LNG: 
                <input type='text' name='txtcut$i'  size='30' value='$lin_form'> 
                <br>
                <input type='checkbox' name='chkstd$i' value='chkstdyes$i'> STD: 
                <input type='text' name='txtstd$i'  size='30' value='$std_form_upper'>
                <br>
                <input type='checkbox' name='chkprt$i' value='chkprtyes$i'> PRT: 
                <input type='text' name='txtprt$i'  size='30' value='$prt_form'>
                <br>
                
            </td>
                <td>
                    Anmerkung:<br><textarea id='comment$i' name='comment$i' rows='4' cols='40'></textarea>
                </td>
            </tr>";
            
            $debug_information = GetDebugInformation( /*$SingleWord->Original*/ $bare_word );       // revert back to procedural-only version
        }
        $i++;
    }
    $output .= "</table></div>";
    
    return $output;
}

function CalculateInlineSTD( $text_array ) {
    global $original_word, $combined_pretags, $html_pretags, $result_after_last_rule, $global_debug_string, $global_numbers_of_rules_applied;
    global $std_form, $separated_std_form, $combined_posttags, $last_pretoken_list, $last_posttoken_list;
    global $caching_temporarily_disabled;
    $format = $_SESSION['original_text_format'];
    if (($format === "prt") || ($format === "std")) return "Wrong or identical input->output format (" . $format . "->std)";
    $caching_temporarily_disabled = true;
    $output = "";
    //var_dump($text_array);
    foreach ( $text_array as $this_word ) {
        //echo "this word: $this_word<br>";
        $global_debug_string = "";
        $global_number_of_rules_applied = 0;
        $bare_word = /*html_entity_decode(*/GetWordSetPreAndPostTags( $this_word )/*)*/;           // decode html manually ...
        $html_pretags = ParseAndSetInlineOptions( $combined_pretags );
        $original_word = $bare_word;
        $result_after_last_rule = $bare_word;
        //echo "bare_word: $bare_word<br>";
        if (mb_strlen($bare_word)>0) {
            $nil = MetaParser( $bare_word );
            //echo "nil: " . htmlspecialchars($nil) . "<br>";
            //echo "std_form: " . htmlspecialchars($std_form) . "<br>";
            // check if { and ] have already been added by MetaParser (don't add them twice)
            $final_std_form = ($_SESSION['token_type'] !== "handwriting") ? mb_strtoupper($std_form) : $bare_word; 
            if (($last_pretoken_list !== "{") && ($last_pretoken_list !== "[")) $output .= $combined_pretags . $last_pretoken_list . $final_std_form . $last_posttoken_list  . $combined_posttags . " ";
            else $output .= $combined_pretags . $final_std_form . $combined_posttags . " ";
            //if (($last_pretoken_list !== "{") && ($last_pretoken_list !== "[")) $output .= $combined_pretags . $last_pretoken_list . mb_strtoupper($std_form) . $last_posttoken_list  . $combined_posttags . " ";
            //else $output .= $combined_pretags . mb_strtoupper($std_form) . $combined_posttags . " ";
        } else {
            $output .= $combined_pretags . $combined_posttags . " ";
        }
    }
    $caching_temporarily_disabled = true;
    return $output;
}

function CalculateInlinePRT( $text_array ) {
    global $original_word, $combined_pretags, $html_pretags, $result_after_last_rule, $global_debug_string, $global_numbers_of_rules_applied;
    global $separated_prt_form, $prt_form, $std_form, $separated_std_form, $combined_posttags, $last_pretoken_list, $last_posttoken_list, $original_word;
    //global $caching_temporarily_disabled;
    $format = $_SESSION['original_text_format'];
    if ($format === "prt") return "Wrong or identical input->output format (" . $format . "->prt)";
    //$caching_temporarily_disabled = true; // not necessary: cached result always corresponds to prt
    $output = "";
    foreach ( $text_array as $this_word ) {
        //echo "this word: $this_word<br>";
        $global_debug_string = "";
        $global_number_of_rules_applied = 0;
        $bare_word = /*html_entity_decode(*/GetWordSetPreAndPostTags( $this_word )/*)*/;           // decode html manually ...
        $html_pretags = ParseAndSetInlineOptions( $combined_pretags );
        $original_word = $bare_word;
        $result_after_last_rule = $bare_word;
        //echo "bare_word: $bare_word SESSION(original_text_format): " . $_SESSION['original_text_format'] . "<br>";
        if (mb_strlen($bare_word)>0) {
            $nil = MetaParser( $bare_word );
            //echo "nil: " . htmlspecialchars($nil) . "<br>";
            //echo "prt_form: " . htmlspecialchars($prt_form) . " original word: $original_word<br>";
            // check if { and ] have already been added by MetaParser (don't add them twice)
            $final_prt_form = ($_SESSION['token_type'] !== "handwriting") ? mb_strtoupper($prt_form) : $original_word; 
            if (($last_pretoken_list !== "{") && ($last_pretoken_list !== "[")) $output .= $combined_pretags . $last_pretoken_list . $final_prt_form . $last_posttoken_list  . $combined_posttags . " ";
            else $output .= $combined_pretags . $final_prt_form . $combined_posttags . " ";
       
            //if (($last_pretoken_list !== "{") && ($last_pretoken_list !== "[")) $output .= $combined_pretags . $last_pretoken_list . mb_strtoupper($prt_form) . $last_posttoken_list  . $combined_posttags . " ";
            //else $output .= $combined_pretags . mb_strtoupper($prt_form) . $combined_posttags . " ";
       
            //$output .= $combined_pretags . $last_pretoken_list . mb_strtoupper($separated_prt_form) . $last_posttoken_list . $combined_posttags . " ";
        } else {
            $output .= $combined_pretags . $combined_posttags . " ";
        }
    }
    //$caching_temporarily_disabled = true;
    return $output;
}
     
function CalculateInlineLNG($text_array) {
    global $original_word, $combined_pretags, $html_pretags, $result_after_last_rule, $global_debug_string, $global_numbers_of_rules_applied;
    global $separated_prt_form, $prt_form, $std_form, $lin_form, $separated_std_form, $combined_posttags, $last_pretoken_list, $last_posttoken_list;
    //global $caching_temporarily_disabled;
    $format = $_SESSION['original_text_format'];
    if (($format === "prt") || ($format === "std") || ($format === "lng")) return "Wrong or identical input->output format (" . $format . "->lng)";
    //$caching_temporarily_disabled = true; // not necessary: caching starts after lng-form
    $output = "";
    //var_dump($text_array);
    foreach ( $text_array as $this_word ) {
        //echo "this word: $this_word<br>";
        $global_debug_string = "";
        $global_number_of_rules_applied = 0;
        list( $pretokens, $bare_word, $posttokens ) = GetPreAndPostTokens( $this_word );
        
        $bare_word = /*html_entity_decode(*/GetWordSetPreAndPostTags( $bare_word )/*)*/;           // decode html manually ...
        //list( $pretokens, $bare_word, $posttokens ) = GetPreAndPostTokens( $bare_word );
           
        //echo "bare_word: >$bare_word< pretokens: $pretokens posttokens: $posttokens<br>";
        $html_pretags = ParseAndSetInlineOptions( $combined_pretags );
        $original_word = $bare_word;
        $result_after_last_rule = $bare_word;
        //echo "bare_word: >$bare_word< SESSION(original_text_format): " . $_SESSION['original_text_format'] . "<br>";
        if (mb_strlen($bare_word)>0) {
            switch ($_SESSION['token_type']) {
                    case "handwriting" : 
                        //echo "generate handwriting<br>"; 
                        $output .= $combined_pretags . $bare_word . $combined_posttags . " ";
                        break;
                    default:
                        $lin_form = MetaParser( $bare_word );
                        $lin_form = $pretokens . $lin_form . $posttokens;
                        //echo "nil: " . htmlspecialchars($nil) . "<br>";
                        //echo "lin_form: $lin_form<br>";
                        //echo "prt_form: " . htmlspecialchars($prt_form) . "<br>";
                        // check if { and ] have already been added by MetaParser (don't add them twice)
            
                        if (($last_pretoken_list !== "{") && ($last_pretoken_list !== "[")) $output .= $combined_pretags . $last_pretoken_list . $lin_form . $last_posttoken_list  . $combined_posttags . " ";
                        else $output .= $combined_pretags . $lin_form . $combined_posttags . " ";
            }
            //$output .= $combined_pretags . $last_pretoken_list . mb_strtoupper($separated_prt_form) . $last_posttoken_list . $combined_posttags . " ";
        } else {
            // fix bug: words consisting only of pre/posttokens and/or numbers are returned as empty strings when LNG-form is calculated
            // => if $bare_word is empty, add pre/posstokens here
            $output .= $combined_pretags . $pretokens . $posttokens . $combined_posttags . " ";
        }
    }
    //$caching_temporarily_disabled = true;
    //echo "<pre>$output</pre><br>";
    return $output;
}

function NormalText2SVG( $text ) {
    global $cached_results;
    
    $text = PreProcessNormalText( $text );
    
    // first apply rules to whole text (if there are any)
    //echo "preprocess (=stage1)<br>";
    $text = PreProcessGlobalParserFunctions( $text ); // corresponds to stage1 (full text)
    if ($text === "") $global_warnings_string .= "STAGE0 (Preprocessing): RETURNS EMPTY STRING<br>";
    
    //echo "preprocess (=stage1) finished<br>";
    $text_array = PostProcessTextArray(explode( " ", $text));
    //echo "\nText aus Normaltext2svg()<br>$text<br>\n";
    prepare_optimized_cache_array($text);
    //var_dump($cached_results);
    //var_dump($text_array);
    //echo "session(ouptut_format): " . $_SESSION['output_format'] . "<br>"; 
    switch ($_SESSION['output_format']) {
            case "layout" : $svg = CalculateLayoutedSVG( $text_array ); break;
            case "train" : $svg = CalculateTrainingSVG( $text_array ); break;
            case "meta_lng" : $svg = "<p>" . htmlspecialchars(CalculateInlineLNG( $text_array )) . "</p>"; break;
            case "meta_std" : $svg = "<p>" . htmlspecialchars(CalculateInlineSTD( $text_array )) . "</p>"; break;    // abuse svg variable for std and prt also ...
            case "meta_prt" : $svg = "<p>" . htmlspecialchars(CalculateInlinePRT( $text_array )) . "</p>"; break;
            default : $svg = CalculateInlineSVG( $text_array );
    }
    //echo var_dump($svg);
    return $svg;
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

function TokenCombiner( $first_token, $second_token, $arg1, $arg2 ) {
    // user same TokenCombiner function for legacy token combination (connecting point, d1 == 4) and diacritics
    // call functions from here according to type
    if ((is_integer($arg1)) || (is_integer($arg2))) TokenCombinerClassic( $first_token, $second_token, $arg1, $arg2);
    else TokenCombinerDiacritics( $first_token, $second_token, $arg1, $arg2 );
}

function TokenCombinerDiacritics( $first_token, $second_token, $pattern, $replacement ) {
    global $steno_tokens_master;
    // Add diacritic token to base token
    //echo "add diacritics<br>";
    $new_definition = array();
    // copy header
    $header =  array_slice( $steno_tokens_master[$first_token], 0 , header_length , true ); // true = preserve keys
    $new_definition = $header;
    //echo "copy header<br>";
    //var_dump($new_definition); echo "<br>";
    // copy tuplets up to insertion point
    $length = count($steno_tokens_master[$first_token]);
    for ($i=header_length; $i<$length-1; $i+=tuplet_length) {
        $type = $steno_tokens_master[$first_token][$i+offs_d1];
        if ($type === $second_token) {
            // read first token x,y coordinates for diacritics
            $dx1 = $steno_tokens_master[$first_token][$i+offs_x1];
            $dy1 = $steno_tokens_master[$first_token][$i+offs_y1];
            // correct delta
            //echo "dx1 before: $dx1<br>";
            
            $delta_x = $dy1 / tan( deg2rad( $_SESSION['token_inclination'] ));
            $dx1 += $delta_x;
            //echo "delta: $delta_x<br>";
            //echo "dx1 after: $dx1<br>";
            
            //echo "dx1=$dx1 / dy1=$dy1<br>";
            // start insertion
            //echo "start insertion<br>";
            //echo "original second_token: $second_token<br>";
            //var_dump($steno_tokens_master[$second_token]); echo "<br>";
            //var_dump($new_definition); echo "<br>";
            $length_j = count($steno_tokens_master[$second_token]);
            for ($j=header_length; $j<$length_j-1; $j+=tuplet_length) {
                //echo "steno_tokens_master[second_token][$j]: " . $steno_tokens_master[$second_token][$j] . " (= x1)<br>";
                $tuplet = array_slice( $steno_tokens_master[$second_token], $j, tuplet_length);
                $x1 = $tuplet[offs_x1] + $dx1;
                $y1 = $tuplet[offs_y1] + $dy1;
                //echo "insert j($j): x1 = x1 + dx1 <=> " . $tuplet[offs_x1] . " + $dx1<br>";
                $t1 = $tuplet[offs_t1];
                $d1 = $tuplet[offs_d1];
                $th = $tuplet[offs_th];
                $drx = ($j == header_length) ? 3 : 2; // drx = modified dr field: 3 = non connecting, 2 = connecting knot
                $dr = $drx; // value 2 or 3 = knot belonging to a diacritic token that must be transferred to a separate spline before CalculateWord is called
                $d2 = $tuplet[offs_d2];
                $t2 = $tuplet[offs_t2];
                // correct values in tuplet
                $tuplet[offs_x1] = $x1;
                $tuplet[offs_y1] = $y1;
                $tuplet[offs_dr] = $dr;
                foreach ($tuplet as $element) $new_definition[] = $element;
            }
            //echo "result<br>";
            //var_dump($new_definition); echo "<br>";
        } else {
            // copy first_token tuplet
            $tuplet = array_slice( $steno_tokens_master[$first_token], $i, tuplet_length);
            //echo "copy tuplet<br>";
            //var_dump($tuplet); echo "<br>";
            //$copy_array = $new_definition;
            //$new_definition = array_merge($copy_array, $tuplet);
            //$new_definition += $tuplet;
            foreach ($tuplet as $element) $new_definition[] = $element;
            //echo "result<br>";
            //var_dump($new_definition); echo "<br>";
         
        }
    }
    // insert new token
    $key = $first_token . $second_token;
    //if ($first_token === "B") {
      //  echo "insert new key: $key<br>";
       // var_dump($new_definition); echo "<br>";
    //}
    $steno_tokens_master[$key] = $new_definition;
}

function TokenCombinerClassic( $first_token, $second_token, $deltay_before, $deltay_after ) {
    global $steno_tokens_master, $steno_tokens_type;
    $new_token = array();
    $new_token_key = $first_token . $second_token;
     // enter token in $steno_tokens_table
    $steno_tokens_type[$new_token_key] = "combined";
    // second token defines if compensation offered by first token is used or not
    $use_bvect_compensation = ($steno_tokens_master[$second_token][offs_bvectx] === "yes") ? true : false;
    
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
                for ($j = 0; $j < tuplet_length; $j++) $new_token[] = $steno_tokens_master[$first_token][$i+$j];
 
        } else {
                // point is a connection point => copy it over marking it as a normal point (= value 0)
                // new: mark it as type 3 (for later compensation if shadowed)
                // first copy over data without modifications
  // bugfix [P@L] => don't insert connection point if no bvect compensation is necessary ("destroys" round connections before connection point ...)
  // experimental
  if ($use_bvect_compensation) {
  /**/              for ($j = 0; $j < tuplet_length; $j++) $new_token[] = $steno_tokens_master[$first_token][$i+$j];
                // change type from 4 to 0 in $newtoken

  /**/              $base = count($new_token) - tuplet_length;
  /**/              $type_offset = $base + offs_d1; // offset 3 in the data tuplet
  /**/              if ($use_bvect_compensation) $new_token[$type_offset] = 3; // former type = 0; new: 3 for compensation with shadowed combined token
  /**/              else $new_token[$type_offset] = 0;
  }
                // store connection_x and connection_y to calculate relative coordinates of second token
                $connection_x = $steno_tokens_master[$first_token][$i+offs_x1]; //$new_token[$base+0];    // x
                $connection_y = $steno_tokens_master[$first_token][$i+offs_y1]; //$new_token[$base+1];    // y
                // now, after connection point, insert all the points of the second token
                // since second token has RELATIVE coordinates, calculate x, y from connection point
            
                // first copy all the values without modification
                for ($n = header_length; $n < count($steno_tokens_master[$second_token]); $n += tuplet_length) {
                       // echo "$first_token + $second_token => copying: n = $n / connectionx = $connection_x / connectiony = $connection_y / x 2nd token = " . $steno_tokens_master[$second_token][$n] . "<br>"; // / value = $temp2 / count(new_token): $temp1<br>";
                        $new_token[] = $connection_x + $steno_tokens_master[$second_token][$n+offs_x1]; // - $steno_tokens_master[$first_token][4];
                        $new_token[] = $connection_y + $steno_tokens_master[$second_token][$n+offs_y1];
                        $new_token[] = $steno_tokens_master[$second_token][$n+offs_t1];
                        if ($use_bvect_compensation) $new_token[] = 3; // see above // $steno_tokens_master[$second_token][$n+offs_d1];
                        else $new_token[] = 0;
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
    //if ($new_token_key === "P@L") var_dump($steno_tokens_master[$new_token_key]);
}

function CreateCombinedTokens() {
    global $combiner_table;
    foreach ($combiner_table as $entry ) TokenCombiner( $entry[0], $entry[1], $entry[2], $entry[3] );
}

// TokenShifter: 
// - shifts tokens adding deltax, deltay to coordinates of original token
// - additionally TokenShifter writes values offs_inconditional_delta_y_before/after (offsets 13 & 14) to header of new token
//

function TokenShifter( $base_token, $key_for_new_token, $delta_x, $delta_y, $arg1, $arg2) {
    // As for the TokenCombiner include a second function without adapting original model / parser structure
    // If arg1 and arg2 are integer values => use classic functionality
    // Otherwhise (= if they are strings) => use shrinking
    //echo "TokenShifter(): $base_token, $key_for_new_token, $delta_x, $delta_y, $arg1, $arg2<br>";
    // MODIFY THIS FUNCTION ONCE MORE ... !
    // The problem is as follows:
    // - TokenShifterClassic by default deletes the group information in the header (= offset 23). This leads to correct spacing with RX-GEN
    // for all shiftings used in the base system of Stolze-Schrey. Unfortunately, the next faster level of Stolze-Schrey (Eilschrift) makes
    // use of 3-lines-high tokens that must be shifted 1 line lower. These new tokens, however, need a group information in order to get
    // correct spacing. It's no option to simply copy the group information for all tokens (because then, tokens of base system will get wrong
    // spacing). Only solution: copying of group information must be selectable.
    // Best way to integrate that: 
    // - check only arg1 (integer or string?) to know if TokenShifterClassic or TokenShifterShrinking must be called
    // - use arg2 as in two different forms:
    // (1) integer => delete group information (classic behaviour, backward compatibility)
    // (2) string => convert string to integer an use value like in classic TokenShifter, but don't delete group information!
    // this is probably a bizarre way to do things, but backwards compatibility must be garantueed!!!
    //if ((is_integer($arg1)) || (is_integer($arg2))) TokenShifterClassic( $base_token, $key_for_new_token, $delta_x, $delta_y, $arg1, $arg2);
    if (is_integer($arg1)) TokenShifterClassic( $base_token, $key_for_new_token, $delta_x, $delta_y, $arg1, $arg2);
    else TokenShifterShrinking( $base_token, $key_for_new_token, $delta_x, $delta_y, $arg1, $arg2 );    
}

function CalculateNewThickness( $tht, $thf, $thickness ) {
    switch ($tht) {
        case "a" : return $thickness * $thf; break; // all
        case "p" : return ($thickness > 1.0) ? $thickness * $thf : $thickness; // partial
        case "n" : return $thickness; break; // none
    }
}

function TokenShifterShrinking($b, $k, $sx, $sy, $arg1, $arg2)  {
    // interpret data in the following way:
    // $b: base token (example: "#A+")
    // $k: key for new token (example: "#A+0") NOTE: #A0+ is not a good idea since it can not be used with session-variable token_marker
    // $sx / $sy: shrinking x / y => this argument can have two formats
    // - integer: normal scaling (without delta)
    // - string: "d:f" => subtract delta, shrink and readd delta 
    // additional function: adjust thickness of token lines
    // in order to invoke this funtionality, arg1 must be a non empty string that contain the following information:
    // - type: a = all (including th === 1.0), p = partial (only th > 1.0)
    // - factor: >1 = thicker <1 = thinner; or "sx" / "sy" / "sf" = shrinking factor (general or x / y) is used 
    // - shadowing: ! = always; "" (empty) = default (= only when shadowed)
    // Type and factor have to be separated by :
    // Example: "a:1.2!" = apply factor 1.2 to all and patch header at offset 12 (offs_token_type)
    // NOTE: This function can also be used to redefine (adjust) tokens defined in base section.
    // To do so, use same key for original and new token. The function then replaces the old token with the new one.
    // Similarly, a token that exists already is replaced with the new token.
    //echo "TokenShifterShrinking(): $b, $k, $sx, $sy, $arg1, $arg2<br>";
    global $steno_tokens_master, $x_values, $y_values;
    $length = count($steno_tokens_master[$b]);
    //echo "original:<br>"; var_dump($steno_tokens_master[$b]); echo "<br>";
    // adapt header values
    // prepare variables
    $new_token = array();
    if (!(is_string($sx))) { $dx = 0; $fx = $sx; }
    else { list($dx, $fx) = explode(":", $sx); }
    if (!(is_string($sy))) { $dy = 0; $fy = $sy; }
    else { list($dy, $fy) = explode(":", $sy); }
    if ($arg1 !== "") {
        if (mb_substr($arg1, -1) === "!") { $arg1 = preg_replace( "/^(.*?)\!$/", "$1", $arg1); $patch = true; }
        list($tht, $thf) = explode(":", $arg1);  
    } else { $thf = 1.0; $patch = false; $tht = "n"; };
    switch ($thf) {
        case "sx" : $thf = $fx; break; // use $fx, $fy, or avg($fx, $fy) to reduce/increase thickness
        case "sy" : $thf = $fy; break;
        case "sf" : $thf = ($fx + $fy) / 2; break;
    }
    //echo "tht: $tht thf: $thf patch: [$patch]<br>";
    // copy and modify header
    for ($i=0; $i<header_length; $i++) {
        $value = $steno_tokens_master[$b][$i];
        if (in_array($i, $x_values)) $new_token[] = ($value - $dx) * $fx + $dx;
        elseif (in_array($i, $y_values)) $new_token[] = ($value - $dy) * $fy + $dy;
        else $new_token[] = $value; // default: copy value (without modification)
    }
    if ($patch) $new_token[offs_token_type] = 1; // set token to "always shadowed" (= new thickness will be applied inconditionally)
    //echo "header: "; var_dump($new_token); echo "<br>";
    for ($i=header_length; $i<$length; $i+=tuplet_length) {
        //echo "i: $i<br>";
        // read, modify and copy original values (8-tuplet)
        $new_token[] = ($steno_tokens_master[$b][$i+offs_x1]-$dx) * $fx + $dx;
        $new_token[] = ($steno_tokens_master[$b][$i+offs_y1]-$dy) * $fy + $dy;
        $new_token[] = $steno_tokens_master[$b][$i+offs_t1];
        $new_token[] = $steno_tokens_master[$b][$i+offs_d1];
        $new_token[] = CalculateNewThickness($tht, $thf, $steno_tokens_master[$b][$i+offs_th]);
        $new_token[] = $steno_tokens_master[$b][$i+offs_dr];
        $new_token[] = $steno_tokens_master[$b][$i+offs_d2];
        $new_token[] = $steno_tokens_master[$b][$i+offs_t2];
    }
    // insert new token in steno_tokens_master
    //echo "<br>"; var_dump($new_token); echo "<br>";
    if ($b === $k) $steno_tokens_master[$k] = null; // delete base token if keys are identical (= replace original token)
    if ((isset($steno_tokens_master[$k])) && ($steno_tokens_master[$k] !== null)) $steno_tokens_master[$k] = null; // similar function: replace old definition with new one if $k-token exists
    $steno_tokens_master[$k] = $new_token;
}

function TokenShifterClassic( $base_token, $key_for_new_token, $delta_x, $delta_y, $inconditional_delta_y_before, $inconditional_delta_y_after ) {
    global $steno_tokens_master, $steno_tokens_type;
    $new_token = array();
    $new_token_key = $key_for_new_token;
    // enter token in $steno_tokens_table
    $steno_tokens_type[$new_token_key] = "shifted";
    
    // copy the header of $base_token
    // first copy all values without modification
    //echo "TokenShifter: $base_token => $key_for_new_token: deltax = $delta_x / deltay = $delta_y / inc_deltay_bef = $inconditional_delta_y_before / inc_deltay_after = $inconditional_delta_y_after<br>";
    //echo "Header:<br>";
    for ($i = 0; $i < header_length; $i++) {
        $new_token[] = $steno_tokens_master[$base_token][$i]; //echo "Offset $i: " . $steno_tokens_master[$base_token][$i] . "<br>";
    }
    if (is_integer($inconditional_delta_y_after)) $new_token[offs_group] = ""; // delete group information in shifter => for regex_helper.php
    // if $inconditional_delta_y_after is string, group information is not deleted!
    
    // now adjust inconditional_deltay_before/after (offsets 13 & 14) and new width (add delta_x to width)
    //echo "<br>Adjustments:<br>";
    $new_token[offs_token_width] += $delta_x;
    $new_token[offs_inconditional_delta_y_before] += /*$steno_tokens_master[$base_token][offs_inconditional_delta_y_before] +*/ $inconditional_delta_y_before;
    // if $inconditional_delta_y_after is of type string an implicit type conversion will be done in the following line
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

function StripOutUnusedTuplets() {
    global $steno_tokens_master;
    //var_dump($steno_tokens_master);
    //$actual_model = $_SESSION['actual_model'];
    foreach ($steno_tokens_master/*['actual_model']*/ as $key => $definition) {
        //echo "Stripping out $key ...<br>";
        //if ($key === "#0") var_dump($definition);
        $new_definition = null;
        $length = count($definition);
        //echo "copy header:";
        for ($i=0; $i<header_length; $i++) {
            //echo " $i";
            $new_definition[] = $definition[$i];
        }
        //echo "<br>";
        for ($i=header_length; $i<$length-1; $i+=tuplet_length) {
            $type = $definition[$i+offs_d1];
            if (($type !== 4) && (!(is_string($type)))) {
                // this tuplet has to be inserted
                $new_definition[] = $definition[$i+offs_x1];
                $new_definition[] = $definition[$i+offs_y1];
                $new_definition[] = $definition[$i+offs_t1];
                $new_definition[] = $definition[$i+offs_d1];
                $new_definition[] = $definition[$i+offs_th];
                $new_definition[] = $definition[$i+offs_dr];
                $new_definition[] = $definition[$i+offs_d2];
                $new_definition[] = $definition[$i+offs_t2];
            } else {
                // tuplets of type 4 or string (diacritics) are not copied 
                $x1 = $definition[$i+offs_x1];
                $x2 = $definition[$i+offs_y1];
                $t1 = $definition[$i+offs_t1];
                $d1 = $definition[$i+offs_d1];
                $th = $definition[$i+offs_th];
                $dr = $definition[$i+offs_dr];
                $d2 = $definition[$i+offs_d2];
                $t2 = $definition[$i+offs_t2];
                //if (($key === "B@#/") || ($key === "B@#_")) {
                  //  echo "strip out tuplet($i): { $x1, $x2, $t1, $d1, $th, $dr, $d2, $t2 }<br>";
                //}
            }
        }
        
        $steno_tokens_master/*[$actual_model]*/[$key] = $new_definition;
        //if (($key === "B@#/") || ($key === "B@#_")) {
          //  echo "key = $key after stripout:<br>";
            //var_dump($steno_tokens_master[$key]); echo "<br>";
        //}
    }
}

?>