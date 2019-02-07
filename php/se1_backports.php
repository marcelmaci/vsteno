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
 
/*
    This file contains functions that enable SE2 features in SE1. The features
    are "backported" (which means: integrated in SE1 which, initially, wasn't
    planned to get those features.
    
    The SE1 with the following functionalities will be called "SE1 revision 1"
    (whereas the original SE1 will retroactively be named "SE1 revision 0")
    
    What features are backported:
    
    (1) Knot types "orthogonal" and "proportional" (means better aesthetics for
        tilted tokens - the new knot types "extend" the default knot type 
        "horizontal" (= the only one present in revision 0))
    (2) Parallel rotating axis: Necessary for proportional and orthogonal knots.
        Due to the limitations of SE1, only 4 parallel rotating axis per tokens
        will be possible (but this will be enough for 99% of the tokens, even
        if these are created with the SE2-editor, where - theoretically - each
        token/point can have an unlimited number of rotating axis).
        
    How will these features be implemented?
    
    The first rule for those backports will be: Change as little as possible
    in the original code. Why? Every change required in the original engine may
    corrupt the correct functioning of the existing enging/code as a whole.
    At this point, the SE1 runs very stabily so"never change a running system"
    (even if - like the SE1 - it's not optimal from many points of view).
    This also means the other nice-to-have features (like optimize the entire
    system SE1 treats "knot types" - which sincerely is a mess (!)) WON'T BE
    CHANGED!
    
    The second rule is the logical consequence (or corrolar;-) of the first one: 
    Everything must fit inside the existing data structures! Since to it's
    "flat array with fix header length" conceptions, the SE1 leaves, indeed,
    very little room for more data. But as in the first rule, NO NEW DATA 
    FIELDS WILL BE CREATED.
    
    For short, those are the IMPACTS that the backports will have on
    SE1-data:
    
    (1) Knot types: Since every knot can be of a different type, the data
        must be included inside the 8-tuplets. From the original fields
        (x1, y1, t1 => qx1, t2 => qy1, th, dr, d2 => qx2, t2 => qy2) all
        fields are used 1x or even 2x (d1, t2, d2, t2) and they are heavily
        needed during the calculation. The only field with the least possible
        implication in the calculation is dr, which only contains two values:
        
            0   =      connect the knot to the preceeding knot
            5   =      don't connect the knot to the preceeding knot
        
        Using "bitwise" (binary) operations this integer field can be used to 
        store more data:
        
        Type of knot (2 bits):  normal:         decimal: 0 => binary: 00
                                orthogonal:     decimal: 1 => binary: 01
                                proportional:   decimal: 2 => binary: 10
        
        => max. 4 values available (3 used)
        
        The values will be written starting at bit 4 (bits 0-2 used for
        original dr information remains intact, bit 3 will be left unused).
        See these examples for 8bit integer:
        
          00000101: decimal  5 <=> normal knot (00), don't connect (101)
          00010000: decimal 16 <=> orthogonal knot (01), connect (000)
          00100101: decimal 37 <=> proportional knot (10), don't connect (101)
          
    (2) Parallel rotating axis: We will again use two bits for 4 rotating
        axis:
        
            00 = 1st axis (main axis as default)
            01 = 2nd axis
            10 = 3rd axis
            11 = 4th axis
            
        Each axis is defined by a shiftX value, i.e. the delta that indicates
        how many points the parallel rotating axis stands to the left (negative)
        or to the right (positive) relative to the main axis (with shiftX = 0).
        ShiftX is a floating point values which can't be included in the tuplet
        (no more space). Therefore, it will be written at offsets 7-9 in the 
        header:
        
            header[7] = 2nd axis
            header[8] = 3rd axis
            header[9] = 4th axis
    
        Which means:
        
            1st axis: doesn't have a value in the header (shiftX is always 0,
                      it's considered the default main axis passing through
                      the origin of the coordinate system)
            
            2nd-4th axis: shiftX = header[6 + axis_number]
            
        The number of the rotating axis (2 bits) will be written at offsets
        7-8 in the dr field (i.e. "following" the knot type value). See the 
        following examples as 8bit integer:
        
            01010000: decimal 80  <=> orthogonal knot with 1st rotating axis
            11100101: decimal 229 <=> proportional knot with 3rd rotating axis
            
    This "bitwise" encoding has the following advantages:
    
    (1) All data can be stored within the few reamining data fields
    (2) only 8Bits (1byte) of dr field is used (leaves some bits for further
        adjustments if necessary in the future)
    (3) It's fun ... remember the time, when we shl ax, 1 ... ? ;-)
            
*/

class ContainerDRField {
    public $connect;
    public $connect_boolean;
    public $knottype;
    public $ra_number;
    public $ra_offset;
    function ContainerDRField($dr) {
        $this->setPropertiesDRField($dr);
    }
    function setPropertiesDRField($dr) {
        $this->connect = get_connect_value($dr);
        $this->connect_boolean = connect_true_false($dr);
        $this->knottype = get_knot_type($dr);
        $this->ra_number = get_rotating_axis_number($dr);
        $this->ra_offset = get_rotating_axis_header_offset($dr);
    }
}

// knot type and rotating axis functions
function get_connect_value($dr) {
    $connect_value = $dr & bindec("111"); // bits 0-3 = old (legacy) connect value
    return $connect_value;
}

function connect_true_false($dr) {
    $temp = get_connect_value($dr); 
    if ($temp == 5) return false;
    else return true;
}

function get_knot_type($dr) {
    $knot_type_value = ($dr & bindec("110000")) >> 4; // bits 4-5 = value for knot type
    switch ($knot_type_value) {
        case 0 : return "horizontal"; break;
        case 1 : return "orthogonal"; break;
        case 2 : return "proportional"; break;
        default : return null; break;
    }
}

function get_rotating_axis_number($dr) {
    $rotating_axis_number = ($dr & bindec("11000000")) >> 6; // bits 6-7 = number of rotating axis
    return $rotating_axis_number;
}

function get_rotating_axis_header_offset($dr) {
    $number = get_rotating_axis_number($dr);
    $offset = 6 + $number;
    return $offset;
}

function get_rotating_axis_shiftX($token, $dr) {    // token = array() = header + data
    $offset = get_rotating_axis_header_offset($dr);
    if ($offset == 6) return 0; // main axis
    else return $token[$offset];
}

// calculations for orthogonal and proportional knots

// use this class just for return values
class Point {
    public $x;
    public $y;
    function Point($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }
}

function get_absolute_knot_coordinates($x, $y, $type, $shiftX, $angle) {
    $scaled_shiftX = $_SESSION['token_size'] * $shiftX; // scale shiftX
    switch ($type) {
        case "horizontal" : $coordinates = calculate_horizontal_coordinates($x, $y, $scaled_shiftX, $angle); break;    // calculation for horizontal could be taken from se1, but to be more systematic integrate it here as a function like the other calculations
        case "orthogonal" : $coordinates = calculate_orthogonal_coordinates($x, $y, $scaled_shiftX, $angle); break;
        case "proportional" : $coordinates = calculate_proportional_coordinates($x, $y, $scaled_shiftX, $angle); break;
    }
    return $coordinates;
}

function calculate_horizontal_coordinates($x, $y, $shiftX, $angle) {    // shiftX is irrelevant for horizontal knots but leave it as parameter for calling compatibility
    $rad = deg2rad($angle);
    $dx = $y / tan($rad);
    return new Point($x+$dx,$y);
}

function calculate_orthogonal_coordinates($x, $y, $shiftX, $angle) {
    /* test: possibility to do it recursively (works)
    if ($shiftX != 0) {
            $result = calculate_orthogonal_coordinates($x-$shiftX, $y, 0, $angle);
            $result->x += $shiftX;
            return $result;
    } else {
    */
    $x -= $shiftX;
    $rad = deg2rad($angle);
    $v1x = cos($rad) * $y;
    $v1y = sin($rad) * $y;
    //echo "v1x: $v1x, v1y: $v1y<br>";
    $angle2 = 90 - $angle; 
    $rad2 = deg2rad($angle2);
    $v2x = cos($rad2) * $x;
    $v2y = - sin($rad2) * $x;
    //echo "v2x: $v2x, v2y: $v2y (x=$x; y=$y)<br>";
    $newX = $v1x + $v2x;
    $newY = $v1y + $v2y;
    $newX += $shiftX;
    return new Point($newX,$newY);
    //}
}

function calculate_proportional_coordinates($x, $y, $shiftX, $angle) {
    //echo "proportional: ($x/$y) shiftx=$shiftX angle=$angle";
    $rad = deg2rad($angle);
    $dx = 1 / tan($rad);
    $factor = sqrt($dx*$dx + 1);
    $x -= $shiftX;
    $rad = deg2rad($angle);
    $v1x = cos($rad) * $y * $factor;
    $v1y = $y; // equivalent to: sin($rad) * $y * $factor; (but faster)
    //echo "v1x: $v1x, v1y: $v1y (x=$x)<br>";
    $angle2 = 90 - $angle; 
    $rad2 = deg2rad($angle2); 
    $v2x = cos($rad2) * $x; // second vector isn't scaled: is that correct?
    $v2y = - sin($rad2) * $x; // idem
    //echo "v2x: $v2x, v2y: $v2y<br>";
    $newX = $v1x + $v2x;
    $newY = $v1y + $v2y;
    $newX += $shiftX;
    //echo " => new: ($newX/$newY)<br>";
    return new Point($newX,$newY);
}

// test
/*
$token = array( 0,0,0,0,0,0,0,1,  2,3,0,0,0,0,0,0,0  ); // 1,2,3 = rotating axis "shiftX" (here just corresponding number of rotating axis)

$binary = "11100101";
$dr1 = bindec($binary);
$connect_value = get_connect_value($dr1);
$connect_yesno = connect_true_false($dr1);
$knot_type = get_knot_type($dr1);
$axis_number = get_rotating_axis_number($dr1);
$offset = get_rotating_axis_header_offset($dr1);
$shiftX = get_rotating_axis_shiftX($token, $dr1);

echo "Binary: $binary => decimal: $dr1 => connect: $connect_value ($connect_yesno) $knot_type_value axis: $axis_number offset: $offset shiftX: $shiftX<br>";

$coordinates = calculate_proportional_coordinates(4,4,4, 0);
//$coordinates = calculate_orthogonal_coordinates(4,4,4,0);

$x = $coordinates->x;
$y = $coordinates->y;

echo "coordinates: x: $x y: $y";
*/

?>