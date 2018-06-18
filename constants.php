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
 
// define constants and global variables and settings

// global settings
mb_internal_encoding('UTF-8');      // with this initialization, string operations should be multibyte-safe
ini_set('display_errors','off');    // turn off errors in order to keep error.log of apache server clean
error_reporting(0);                 // turn off all error reporting

// constants
// for steno tokens (array with header and data tuplets)

// header
const header_length = 24;                       // 24 first values of every tokens is reserved for header
const offs_token_width = 0;                     // offset 0: width of token
const offs_delta_y_before = 1;                  // offset 1: delta_y (if token has to be placed higher)
const offs_delta_y_after = 2;                   // offset 2: baseline after higher position (different for "ich" and "is" for example)
const offs_tension_before = 3;                  // offset 3: tension before first point of token
const offs_additional_x_before = 4;             // offset 4: additional width before token (added to offset 0)
const offs_additional_x_after = 5;              // offset 5: additional width after token (added to offset 0)
const offs_additional_delta_y = 6;              // offset 6: additional vertical delta_y (?) (unclear, probably obsolete!?! => some tokens like "ist" use it => leave it for the moment )
                                                // offsets 7-11: unused (obsolete, free for reuse)
const offs_token_type = 12;                     // offset 12: token type: 0 = normal token (with/without shadows) / 
                                                //            1 = allways shadowed / 2 = "virtual" tokens (defines how the following 
                                                //            token has to be placed (values at offsets 19-21)
const offs_inconditional_delta_y_before = 13;   // offset 13: add this delta_y as relative value to baseline in any case BEFORE drawing token
const offs_inconditional_delta_y_after = 14;    // offset 14: add this delta_y as relative value to baseline in any case BEFORE drawing token
const offs_alternative_exit_point_x = 15;       // offset 15: alternative exit point: 0 = none / != 0: x coordinate of alternative exit point
const offs_alternative_exit_point_y = 16;       // offset 16: alternative exit point: 0 = none / != 0: y coordinate of alternative exit point
const offs_exit_point_to_use = 17;              // offset 17: indicates for the following token, which exit point should be used: 0 = use normal exit point / 1 = use alternative exit point (if available)
const offs_interpretation_y_coordinates = 18;   // offset 18: interpretation for y coordinates: 0 = cordinates are relative (default) / 1 = coordinates are absolute
const offs_vertical = 19;                       // offset 19: variable $vertical (string): "no" = next token has same vertical height / "up" = next token must be placed higher / "down" = next token has to be placed lower (connected to offset 12)
const offs_distance = 20;                       // offset 20: variable $distance (string): "narrow" or "none" = no distance / "wide" = distance defined in constants $horizontal_distance_narrow/wide (connected to offset 12)
const offs_shadowed = 21;                       // offset 21: variable $shadowed (string): "yes" = shadowed / "no" = not shadowed
                                                // offsets 22-23: not used

// data tuplets: each tuplets contains 8 entries like so:  [x1, y1, t1, d1, th, dr, d2, t2 ]
//
// x1: x coordinate of knot
// y1: y coordinate of knot
// t1: tension following the knot (bezier curve, tension preceeding the knot is stored in preceeding knot at offset 7)
// d1: entry data field: 0 = regular point / 1 = entry point / 2 = pivot point / 4 = connecting point (for combined tokens created "on the fly")
// th: relative thickness of knot: 1.0 = normal thickness (lower values = thinner / higher values = thicker)
// dr: data field for drawing function: 0 = normal (i.e. connect points) / 5 = don't connect to this point from preceeding point
// d2: exit data field: 0 = regular point / 1 = exit point / 2 = pivot point / 99 = early exit point (= this point is the last one inserted into splines if token is the last one in tokenlist)
// t2: tension preceeding the following knot (bezier kurve)
//
// tensions: 0 = "sharp" connection (not rounded) / other floating point values between 0 and 1 (typically 0.5) = smooth connection

const offs_x1 = 0;      // offset 0: x1
const offs_y1 = 1;      // offset 1: y1
const offs_t1 = 2;      // offset 2: t1
const offs_d1 = 3;      // offset 3: d1
const offs_th = 4;      // offset 4: th
const offs_dr = 5;      // offset 5: dr
const offs_d2 = 6;      // offset 6: d2
const offs_t2 = 7;      // offset 4: t2

// parser
$punctuation = ".,:;!?";                        // metaparser recognizes these tokens as punctuation and treats them differently 

// variables
$standard_height = 10;                          // height of one token like b, g, m etc.
$svg_height = 6 * $standard_height;             // height for svg image
$height_above_baseline = 4 * $standard_height;  // number of lines available above baseline
$baseline_y = 40;                               // baseline for steno tokens
$half_upordown = $standard_height / 2;          // value for tokens that have to be placed 1/2 line higher or lower
$one_upordown = $standard_height;               //   "                                 "     1   "
$horizontal_distance_none = 0;
$horizontal_distance_narrow = $standard_height / 4;
$horizontal_distance_wide = $standard_height * 1;

// declarations
$splines = array();                             // not really necessary in php

?>