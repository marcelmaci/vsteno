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
 
 // The good old "fortune" program of the unix shell ...
 // fortune.php generates fortune cookies in steno 

require_once "session.php";

$cookie_table = array(
    // width, height, align, margin_left, margin_right, margin_top, margin_bottom, num_system_lines, baseline, shorthand_size, svgtext_size, $text
    array( 370, 120, "align_left", 5, 0, 10, 0, 2.5, 3, 1.6, 14, "Man muss die Welt nicht verstehen, man muss sich nur darin zurecht\\finden.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 440, 120, "align_left", 5, 0, 20, 0, 2.5, 3, 1.3, 14, "Um ein tadelloses Mitglied einer Schafherde sein zu können, muß man vor allem ein Schaf sein.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    //array( 400, 120, "align_left", 30, 0, 10, 0, 2.5, 3, 1.6, 16, "Phantasie ist wichtiger als Wissen, denn Wissen ist begrenzt.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 355, 120, "align_left", 5, 0, 0, 0, 2.5, 2.6, 1.3, 14, "Zwei Dinge sind unendlich, das Universum und die menschliche Dummheit, aber bei dem Universum bin ich mir noch nicht ganz sicher.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    //array( 400, 120, "align_left", 5, 0, 10, 0, 2.5, 3, 1.3, 16, "Seit die Mathematiker über die Relativitätstheorie hergefallen sind, verstehe ich sie selbst nicht mehr.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    //array( 340, 120, "align_left", 5, 0, 2, 0, 2.5, 2.6, 1.4, 16, "Die Wissenschaft ist eine wunderbare Sache, wenn man nicht seinen Lebensunterhalt damit verdienen muss.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Wenn man zwei Stunden lang mit einem Mädchen zusammensitzt, meint man, es wäre eine Minute. Sitzt man jedoch eine Minute auf einem heißen Ofen, meint man, es wären zwei Stunden. Das ist Relativität.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 400, 120, "align_left", 5, 0, 10, 0, 2.7, 2.6, 1.6, 14, "Versuche zu kriegen, wen du liebst, ansonsten musst du lieben, wen du kriegst.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Zeit ist das, was man an der Uhr abliest.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Man hat den Eindruck, dass die moderne Physik auf Annahmen beruht, die irgendwie dem Lächeln einer Katze gleichen, die gar nicht da ist.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Manche Männer bemühen sich lebenslang, das Wesen einer Frau zu verstehen. Andere befassen sich mit weniger schwierigen Dingen z. B. der Relativitätstheorie.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Wenn eine Idee am Anfang nicht absurd klingt, dann gibt es keine Hoffnung für sie.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Bildung ist das, was übrig bleibt, wenn man all das, was man in der Schule gelernt hat, vergisst.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Mehr als die Vergangenheit interessiert mich die Zukunft, denn in ihr gedenke ich zu leben.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Schämen sollten sich die Menschen, die sich gedankenlos der Wunder der Wissenschaft und Technik bedienen und nicht mehr davon geistig erfasst haben als die Kuh von der Botanik der Pflanzen, die sie mit Wohlbehagen frisst.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Logik bringt dich von A nach B. Deine Phantasie bringt dich überall hin.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Um eine Einkommenssteuererklärung abgeben zu können, muss man Philosoph sein. Für einen Mathematiker ist es zu schwierig.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Wenn ich die Folgen geahnt hätte, wäre ich Uhrmacher geworden.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Universitäten sind schöne Misthaufen, auf denen gelegentlich einmal eine edle Pflanze gedeiht.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   
);

function prepare_engine() {
    global $temp_output_format, $temp_output_width, $temp_output_height, $temp_svgtext_size, $cookie_table, $temp_token_size, $cookie_number;
    global $temp_output_style, $temp_left_margin, $temp_top_margin, $temp_right_margin, $temp_bottom_margin, $temp_num_system_lines, $temp_baseline;
    $temp_output_format = $_SESSION['output_format'];
    $temp_output_width = $_SESSION['output_width'];
    $temp_output_height = $_SESSION['output_height'];
    $temp_output_style = $_SESSION['output_style'];
    $temp_left_margin = $_SESSION['left_margin'];
    $temp_right_margin = $_SESSION['right_margin'];
    $temp_top_margin = $_SESSION['top_margin'];
    $temp_bottom_margin = $_SESSION['bottom_margin'];
    $temp_num_system_lines = $_SESSION['num_system_lines'];
    $temp_baseline = $_SESSION['baseline'];                                  // start at 4th system line for first shorthand text line in layouted svg
    $temp_svgtext_size = $_SESSION['svgtext_size'] = 30;
    $temp_token_size = $_SESSION['token_size']; // factor
    $_SESSION['output_format'] = "layout";
    $_SESSION['output_width'] = $cookie_table[$cookie_number][0];
    $_SESSION['output_height'] = $cookie_table[$cookie_number][1];
    $_SESSION['output_style'] = $cookie_table[$cookie_number][2];
    $_SESSION['left_margin'] = $cookie_table[$cookie_number][3];
    $_SESSION['right_margin'] = $cookie_table[$cookie_number][4];
    $_SESSION['top_margin'] = $cookie_table[$cookie_number][5];
    $_SESSION['bottom_margin'] = $cookie_table[$cookie_number][6];
    $_SESSION['num_system_lines'] = $cookie_table[$cookie_number][7];
    $_SESSION['baseline'] = $cookie_table[$cookie_number][8];         
    $_SESSION['token_size'] = $cookie_table[$cookie_number][9]; // factor
    $_SESSION['svgtext_size'] = $cookie_table[$cookie_number][10];
    
}

function restore_engine() {
    global $temp_output_format, $temp_output_width, $temp_output_height, $temp_svgtext_size, $cookie_table, $temp_token_size;
    global $temp_output_style, $temp_left_margin, $temp_top_margin, $temp_right_margin, $temp_bottom_margin, $temp_num_system_lines, $temp_baseline;
    $_SESSION['output_format'] = $temp_output_format;
    $_SESSION['output_width'] = $temp_output_width;
    $_SESSION['output_height'] = $temp_output_height;
    $_SESSION['output_style'] = $temp_output_style;
    $_SESSION['left_margin'] = $temp_left_margin;
    $_SESSION['right_margin'] = $temp_right_margin;
    $_SESSION['top_margin'] = $temp_top_margin;
    $_SESSION['bottom_margin'] = $temp_bottom_margin;
    $_SESSION['num_system_lines'] = $temp_num_system_lines;
    $_SESSION['baseline'] = $temp_baseline;  
    $_SESSION['svgtext_size'] = $temp_svgtext_size;
    $_SESSION['token_size'] = $temp_token_size; // factor
    
}
/*
if (!isset($_SESSION['fortune_cookie'])) {
    require_once "constants.php";
    require_once "data.php";
    require_once "parser.php";
    require_once "engine.php";
}
*/
unset($_SESSION['fortune_cookie']);
if (!isset($_SESSION['fortune_cookie'])) {
    require_once "engine.php";
}


function fortune() {
    global $cookie_table, $cookie_number;
    
    // returns a random fortune cookie
    $cookie_number = (int)rand(0, 4);
    if (isset($_SESSION['fortune_cookie'])) return $_SESSION['fortune_cookie'];
    else {
        // only calculate 1 cookie per session
        prepare_engine();
        //$svg = NormalText2SVG( "Man muss die Welt nicht verstehen, man muss sich nur darin zurecht\\finden.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" );
        $svg = NormalText2SVG( $cookie_table[$cookie_number][11] );
        restore_engine();
        $_SESSION['fortune_cookie'] = "$svg";
        return $_SESSION['fortune_cookie']; //$svg;
    } 
}

?>