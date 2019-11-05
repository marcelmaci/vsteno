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

// version
const version_name = "Hephaistos => latest, 3. Oktober 2019"; // official release name
const version_number = 0.1; // two digit version number (can be used to test compatibility with models)
const version_commit_id = "57e34ec35c64a11513f941cd379a544875fa063a";   // must be inserted manually after commit => could be from last commit if forgotten
const version_date = "3. Oktober 2019";                                  // idem

// constants
// for steno tokens (array with header and data tuplets)
    
// header
const header_length = 24;                       // 24 first values of every token is reserved for header
const offs_token_width = 0;                     // offset 0: width of token
const offs_delta_y_before = 1;                  // offset 1: delta_y (if token has to be placed higher)
const offs_delta_y_after = 2;                   // offset 2: baseline after higher position (different for "ich" and "is" for example)
const offs_tension_before = 3;                  // offset 3: tension before first point of token
const offs_additional_x_before = 4;             // offset 4: additional width before token (added to offset 0)
const offs_additional_x_after = 5;              // offset 5: additional width after token (added to offset 0)
const offs_additional_delta_y = 6;              // offset 6: additional delta y (used e.g. by [0D-]
//const offs_relative_baseline_shifter = 6;       // offset 6: reused from "additional vertical delta_y" (which seems to be obsolete: WRONG! IS USED BY [0D-])
                                                //           0 = use standard baseline, positive values: 1 = place token 1 line higher
                                                //           negative values: -0.5: place token 1/2 line lower => can be used for [&T] + consonant
                                                // offsets 7-11: unused (obsolete, free for reuse)
const offs_parrotaxis1 = 7;                     // offset 7: reused for parallel rotating axis 1 (rev1)
const offs_parrotaxis2 = 8;                     // offset 8: idem ............................ 2 
const offs_parrotaxis3 = 9;                     // offset 9: idem ............................ 3
const offs_bvectx = 10;                         // offset 10: border vector x (shadowed combined tokens)
                                                //            in combined token: string = "yes" (use bvect compensation), "no" (don't user bvect compensation)
const offs_bvecty = 11;                         // offset 11: border vector y (shadowed combined tokens)
const offs_token_type = 12;                     // offset 12: token type: 0 = normal token (with/without shadows) / 
                                                //            1 = always shadowed / 2 = "virtual" tokens (defines how the following 
                                                //            token has to be placed (values at offsets 19-21)
                                                //            3 = spacer (no points are inserted, the token only contains delta x at offset 0 (width)
                                                //            4 = part of a token (if exit and entry point of two parts are identical, only 1 point is 
                                                //            inserted in splines: entry tension = from first point, exit tension = from second point
const offs_inconditional_delta_y_before = 13;   // offset 13: add this delta_y as relative value to baseline in any case BEFORE drawing token
const offs_inconditional_delta_y_after = 14;    // offset 14: add this delta_y as relative value to baseline in any case BEFORE drawing token
const offs_alternative_exit_point_x = 15;       // offset 15: alternative exit point: 0 = none / != 0: x coordinate of alternative exit point
const offs_alternative_exit_point_y = 16;       // offset 16: alternative exit point: 0 = none / != 0: y coordinate of alternative exit point
const offs_exit_point_to_use = 17;              // offset 17: indicates for the following token, which exit point should be used: 0 = use normal exit point / 1 = use alternative exit point (if available)
const offs_interpretation_y_coordinates = 18;   // offset 18: interpretation for y coordinates: 0 = cordinates are relative (default) / 1 = coordinates are absolute
const offs_vertical = 19;                       // offset 19: variable $vertical (string): "no" = next token has same vertical height / "up" = next token must be placed higher / "down" = next token has to be placed lower (connected to offset 12)
const offs_distance = 20;                       // offset 20: variable $distance (string): "narrow" or "none" = no distance / "wide" = distance defined in constants $horizontal_distance_narrow/wide (connected to offset 12)
const offs_shadowed = 21;                       // offset 21: variable $shadowed (string): "yes" = shadowed / "no" = not shadowed
const offs_dont_connect = 22;                   // offset 22: 0 = default, 1 = don't connect to the following token (i.e. insert it without connection to previous token)
const offs_group = 23;                          // offset 23: group of the token (used for regex_helper.php and spacer)

// data tuplets: each tuplets contains 8 entries like so:  [x1, y1, t1, d1, th, dr, d2, t2 ]
//
// Data tuplets are used inside token definitions definitions and inside splines list. 
// The meaning of the data inside the tuplets is slightly different.
//
// (1) TOKEN DEFINITIONS
//
// x1: x coordinate of knot
// y1: y coordinate of knot
// t1: tension following the knot (bezier curve, tension preceeding the knot is stored in preceeding knot at offset 7)
// d1: entry data field: 0 = regular point / 1 = entry point / 2 = pivot point / 4 = connecting point (for combined tokens created "on the fly")
//                       5 = "intermediate shadow point" (this point will only be used if the token is shadowed, otherwise it wont be inserted into splines),
//                       3 = conditional pivot point: if token is in normal position this point will be ignored/considered as a normal point (= value 0)
//                      98 = late entry point (= if token is first token in tokenlist then don't draw points before late entry point; consider this point as entry point)
// th: relative thickness of knot: 1.0 = normal thickness (lower values = thinner / higher values = thicker)
// dr: data field for drawing function: 0 = normal (i.e. connect points) / 5 = don't connect to this point from preceeding point / 
//     2 = knots belonging to diacritic token (must be transferred to separate spline)
//     higer values = combined values for knot type and parallel rotating axis (see se1_backports.php)
// d2: exit data field: 0 = regular point / 1 = exit point / 2 = pivot point / 99 = early exit point (= this point is the last one inserted into splines if token is the last one in tokenlist)
//                      3 = conditional pivot point: if token is in normal position this point will be ignored/considered as a normal point (= value 0)
// t2: tension preceeding the following knot (bezier kurve)
//
// tensions: 0 = "sharp" connection (not rounded) / other floating point values between 0 and 1 (typically 0.5) = smooth connection
//
// (2) SPLINES LIST
//
// As a first step, tuplets from tokens are copied over to splines list, so that they are similar. Nonetheless several fields change
// their signification due to later operations on the data:
//
// (a) InsertTokenInSplinesList(): Modifies the dr field by (i) filtering out eventual rev1-backport data and inserting values
//     to mark knots belonging to diacritic tokens (values 2 and 3) and combined tokens (string = vector for knot corrections), only values
//     1 (normal dot) and 5 (don't connect) from original rev0 are preserved. In order to avoid confusions, this new dr field will
//     be called drx ("modified dr").
// (b) CalculateWord() calculates the control points for the bezier curves (qx1, qy1; qx2, qy2) and writes them into offsets 2,3 (t1, d1) 
//     and 6,7 (d2, t2).
//
// offset 0: x1
// offset 1: y1
// offset 2: t1 => qx1*
// offset 3: d1 => qy1*
// offset 4: th
// offset 5: dr => drx**
// offset 6: d2 => qx2*
// offset 4: t2 => qy2*
//
// Notes:
// * Values t1, d1 (offsets 2+3) and d2, t2 (offsets 6+7) are later overwritten by function CalculateWord()
//   The new values written to the tuplet correspond to the control points for the bezier curve (qx1, qy1, qx2, qy2)
// ** Modified dr-field containing one of the following:
//    - value 2: connecting knot belonging to diacritic token, that must be transferred to a separated spline
//    - value 3: non connecting knot "           "                              "
//    - a string with the vector for knot correction in combined tokens 
//    - original values 1 and 5 of the dr-field
//

const tuplet_length = 8; // each tuplet contains 8 entries
const offs_x1 = 0;       // offset 0: x1
const offs_y1 = 1;       // offset 1: y1
const offs_t1 = 2;       // offset 2: t1 => qx1*
const offs_d1 = 3;       // offset 3: d1 => qy1*
const offs_th = 4;       // offset 4: th
const offs_dr = 5;       // offset 5: dr => drx
const offs_d2 = 6;       // offset 6: d2 => qx2*
const offs_t2 = 7;       // offset 4: t2 => qy2*
// offsets after CalculateWord()
const offs_qx1 = 2;
const offs_qy1 = 3;
const offs_qx2 = 6;
const offs_qy2 = 7;


const bezier_offs_qx1 = 3;
const bezier_offs_qy1 = 4;
const bezier_offs_qx2 = 6;
const bezier_offs_qy2 = 7;

// constants for signification of values contained in data tuplets
const regular_point = 0;
const entry_point = 1;
const pivot_point = 2;
//const conditional_pivot_point = 3; // still no idea what this point type was meant for ... orginally used in V        
// probably not a good concept: conflict with alternative exit point (e.g. sch: wischen, rauschen, puschen, preschen 
// => better to leave this job to the parser: define special tokens for higher poisitions, e.g. "^SCH" without alternative exit point (=> "-EN" will be added correctly)
// this means more data, but is less complicated
const connecting_point = 4;
const intermediate_shadow_point = 5;

const exit_point = 1;
const early_exit_point = 99;

const draw_normal = 0;
const draw_no_connection = 5;

// token definitions: distinction between x and y values (offsets)
$x_values = array( offs_token_width, offs_additional_x_before, offs_additional_x_after, offs_parrotaxis1, offs_parrotaxis2, 
                    offs_parrotaxis3, offs_bvectx, offs_alternative_exit_point_x );
$y_values = array( offs_delta_y_before, offs_delta_y_after, offs_additional_delta_y, offs_bvecty, offs_inconditional_delta_y_before,
                    offs_inconditional_delta_y_before, offs_alternative_exit_point_y );

// parser
$punctuation = ".,:;!?…";                        // metaparser recognizes these tokens as punctuation and treats them differently 
$upper_case_punctuation = ".:!?";
$numbers = "01234567890";
$only_pretokens = "(\[{\"\'¿¡";                 // include pretokens for spanish (question mark / exclamation mark)
$only_posttokens = ")\]}\"\'";
$pretokenlist = $numbers . $only_pretokens;               // metaparser recognizes these tokens as pre/posttokens and treats them differently 
$posttokenlist = $punctuation . $only_posttokens . $numbers;  // treat punctuation and numbers as posttokens

// variables
$standard_height = 10;                          // height of one token like b, g, m etc.
$svg_height = 6 * $standard_height;             // height for svg image
$height_above_baseline = 4 * $standard_height;  // number of lines available above baseline
$height_for_delta_array = $height_above_baseline + 10 * $standard_height; // create more deltas for words that go beyond system lines
$baseline_y = 40;                               // baseline for steno tokens
$half_upordown = $standard_height / 2;          // value for tokens that have to be placed 1/2 line higher or lower
$one_upordown = $standard_height;               //   "                                 "     1   "
$horizontal_distance_none = 0;
//$horizontal_distance_narrow = $standard_height / 4;
$horizontal_distance_narrow = 0;
$correction_shadow_factor = 1.6;                // needed for backwards compatibility with existing models

$horizontal_distance_wide = $standard_height * 1.5;
$space_at_end_of_stenogramm = $horizontal_distance_wide;    // defines horizontal distance between stenogramms
$border_margin = 1;                                         // additional ("security") margin for trimmed stenogramms (should be > 0, since bezier curves tend to go outside of min and max coordinates
$distance_words = 5;                           // distance between words (added to svg at the end of the stenogram)
$space_before_word = 10;                        // part of $distance_words that goes to the left side of the word (inside SVG); $distance_word = 20 and $space_before_word means that the word will have 10 pixels of free space on both sides (= half of the total distance, in other words: the word is centered inside the svg)
$svg_not_compatible_browser_text = "";         // only add text if you really want to be nice to people with old browsers (it creates overhead ...)
// $svg_not_compatible_browser_text = "Sorry, your browser does not support inline SVG.";
$vector_value_precision = 3;                    // number of decimals (eliminates overhead, but reduces precision)

// layouted svg
$left_margin = 5;                               // margins for layouted svg
$right_margin = 25;
$top_margin = 5;
$bottom_margin = 30;
$num_system_lines = 3;                          // number of shortand system lines between two text lines (lower values = narrower)

// flags
$dont_connect = 0;

// variables for procedural approach
$original_word = "";
$result_after_last_rule = "";
$combined_pretags = "";
$combined_posttags = "";
$last_pretoken_list = "";
$last_posttoken_list = "";
//$inline_options_pretags = "";                    // better: leave procedural approach as it is
//$inline_options_posttags = "";                   // add oop on top of functions
$html_pretags = ""; 
$html_posttags = "";
$global_debug_string = "";
$global_textparser_debug_string = "";
$global_linguistical_analyzer_debug_string = "";
$global_number_of_rules_applied = 0;
$std_form = "";
$prt_form = "";
$lin_form = "";
$separated_std_form = "";
$separated_prt_form = "";
$processing_in_parser = "";                        // variable that indicates if word has been generated from rules or taken from dictionary: R = rules, D = dictionary
$sentence_start = true;                            // variable needed for training mode to offer lower case checkbox
$last_word_punctuation = false;
$this_word_punctuation = true;

// rules
$rules_pointer_start_std2prt = null;
$rules_pointer_start_stage2 = null;
$rules_pointer_start_stage3 = null;
$rules_pointer_start_stage4 = null;

// phonetics
$last_written_form = "";                        // contains written form of last word that has called GetPhoneticTranscription() in linguistics.php
$parallel_lng_form = "";                        // when phonetic transcription is calculated, this global variable contains analysis of written form (if it is selected)

// error handling
$global_error_string = "";
$global_warnings_string = "";

// declarations
$splines = array();                             // not really necessary in php
$separate_spline = array();

// models
$default_model = "DESSBAS";
$standard_models_list = array(
    "$default_model" => "Deutsch Grundschrift (Stolze-Schrey)",
    "SPSSBAS" => "Spanisch Grundschrift (Stolze-Schrey)",
    "FRSSBAS" => "Französisch Grundschrift (Stolze-Schrey)",
    "ENSSBAS" => "Englisch Grundschrift (Stolze-Schrey)"
);
//$default_model = "99999_default_backup";

// fonts (whitelist or allowed or accessible fonts)
$font_files_list = array( "ENSSBAS", "GESSBAS" ); // use ENSSBAS.txt as shared font for now (.txt is added by LoadFontFromFile)

// caching
$cached_results = array();

// fortune cookie
$fortune_cookie = "be_lucky"; //"be_lucky"; // if this is set to an int number, this cookie is returned // value 0 doesn't work (use -1 in get_fortune() instead) 


?>