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

require_once "constants.php";
require_once "session.php";
//$cookie_number = 16;

$cookie_number = 0;
$cookie_table = array(
    // width, height, align, margin_left, margin_right, margin_top, margin_bottom, num_system_lines, baseline, shorthand_size, svgtext_size, $text
    /*0*/ array( 370, 120, "align_left", 5, 0, 10, 0, 2.5, 3, 1.5, 14, "Man muss die Welt nicht verstehen, man muss sich nur darin zu+recht\\finden.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    /*1*/ array( 380, 120, "align_left", 5, 0, 8, 0, 2.5, 3, 1.3, 14, "Um ein tadelloses Mitglied einer Schafherde sein zu können, muß man vor allem ein Schaf sein.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    /*2*/ array( 370, 120, "align_left", 5, 0, 8, 0, 2.5, 2.6, 1.2, 14, "Zwei Dinge sind unendlich, das Universum und die menschliche Dummheit, aber bei dem Universum bin ich mir noch nicht ganz sicher.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    /*3*/ array( 380, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Wenn man zwei Stunden lang mit einem Mädchen zusammensitzt, meint man, es wäre eine Minute. Sitzt man jedoch eine Minute auf einem heißen Ofen, meint man, es wären zwei Stunden. Das ist Relativität.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    /*4*/ array( 240, 120, "align_left", 5, 0, 15, 0, 2.7, 2.6, 1.6, 14, "Zeit ist das, was man an der Uhr abliest.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    /*5*/ array( 240, 120, "align_left", 5, 0, 10, 0, 2.7, 2.6, 1.25, 14, "Logik bringt dich von A nach B. Deine Phantasie bringt dich überall hin.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    /*6*/ array( 360, 120, "align_left", 5, 0, 6, 0, 3.15, 2.6, 1.1, 14, "Um eine Ein|kommens\steuer[#N]rklärung abgeben zu können, muss man Philosoph sein. Für einen Mathematiker ist es zu schwierig.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    /*7*/ array( 360, 120, "align_left", 5, 0, 20, 0, 2.7, 2.6, 1.3, 14, "Ich gebe meinem Psüchiater noch ein Jahr, dann fahre ich nach Lourdes.<@token_type=svgtext>Woody Allen<@token_type=shorthand>" ),
    /*8*/ array( 340, 120, "align_left", 5, 0, 15, 0, 2.7, 2.6, 1.05, 14, "Es gibt Schlimmeres als den Tod. Wer schon einmal einen Abend mit einem Versicherungs|ver|treter zugebracht hat, wird wissen, was ich meine.<@token_type=svgtext>Woody Allen<@token_type=shorthand>" ),
    /*9*/ array( 240, 120, "align_left", 5, 0, 20, 0, 2.7, 2.6, 1.4, 14, "Die Ewigkeit dauert lange, besonders gegen Ende.<@token_type=svgtext>Woody Allen<@token_type=shorthand>" ),
   /*10*/ array( 340, 120, "align_left", 5, 0, 2, 0, 2.7, 2.5, 1.0, 14, "Als ich vierzehn war, war mein Vater so unwissend. Ich konnte den alten Mann kaum in meiner Nähe ertragen. Aber mit einundzwanzig war ich verblüfft, wieviel er in sieben Jahren dazugelernt hatte. <@token_type=svgtext>Mark Twain<@token_type=shorthand>" ),
   /*11*/ array( 360, 120, "align_left", 5, 0, 8, 0, 3.4, 2.6, 1.05, 14, "Getöse beweist gar nichts. Eine Henne, die gerade ein Ei gelegt hat, gackert häufig so, als hätte sie einen Asteroiden gelegt. <@token_type=svgtext>Mark Twain<@token_type=shorthand>" ),
   /*12*/ array( 300, 120, "align_left", 5, 0, 6, 0, 2.7, 2.6, 1.3, 14, "Welt\\verbesserer gibt es genug, aber einen Nagel richtig einschlagen können die wenigsten.<@token_type=svgtext>Henrik Ibsen<@token_type=shorthand>" ),
   /*13*/ array( 355, 120, "align_left", 5, 0, 8, 0, 2.7, 2.6, 1.25, 14, "Was auch immer geschieht: Nie dürft ihr so tief sinken, von dem Kak[A][&A][[#N][&O], durch den man euch zieht, auch noch zu trinken.<@token_type=svgtext>Erich Kästner<@token_type=shorthand>" ),
   /*14*/ array( 320, 120, "align_left", 5, 0, 22, 0, 2.7, 2.6, 1.3, 14, "Es gibt wichtige und unwichtige Dinge im Leben. Die meisten Dinge sind unwichtig.“<@token_type=svgtext>Erich Kästner<@token_type=shorthand>" ),
   /*15*/ array( 320, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.3, 14, "Der Mensch: ein Wesen, das am Ende einer Woche Arbeit entstand, als Gott bereits sehr müde war.<@token_type=svgtext>Mark Twain<@token_type=shorthand>" ),
   /*16*/ array( 300, 160, "align_left", 5, 0, -5, 0, 3.1, 3.5, 1.2, 14, "Seit jenem Tag, an dem der erste Schuft seinen ersten Dummkopf fand, gibt es Quacksalber.<@token_type=svgtext>Voltaire<@token_type=shorthand>" ),
   /*17*/ array( 300, 160, "align_left", 5, 0, -5, 0, 3.1, 3.5, 1.2, 14, "Je planmässiger die Menschen vorgehen, desto wirksamer vermag sie der Zufall zu treffen.<@token_type=svgtext>Friedrich Dürrenmatt<@token_type=shorthand>" ),
   
 /*  
    //array( 400, 120, "align_left", 30, 0, 10, 0, 2.5, 3, 1.6, 16, "Phantasie ist wichtiger als Wissen, denn Wissen ist begrenzt.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    //array( 400, 120, "align_left", 5, 0, 10, 0, 2.5, 3, 1.3, 16, "Seit die Mathematiker über die Relativitätstheorie hergefallen sind, verstehe ich sie selbst nicht mehr.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    //array( 340, 120, "align_left", 5, 0, 2, 0, 2.5, 2.6, 1.4, 16, "Die Wissenschaft ist eine wunderbare Sache, wenn man nicht seinen Lebensunterhalt damit verdienen muss.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 400, 120, "align_left", 5, 0, 10, 0, 2.7, 2.6, 1.6, 14, "Versuche zu kriegen, wen du liebst, ansonsten musst du lieben, wen du kriegst.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Zeit ist das, was man an der Uhr abliest.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Man hat den Eindruck, dass die moderne Physik auf Annahmen beruht, die irgendwie dem Lächeln einer Katze gleichen, die gar nicht da ist.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Manche Männer bemühen sich lebenslang, das Wesen einer Frau zu verstehen. Andere befassen sich mit weniger schwierigen Dingen z. B. der Relativitätstheorie.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Wenn eine Idee am Anfang nicht absurd klingt, dann gibt es keine Hoffnung für sie.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Bildung ist das, was übrig bleibt, wenn man all das, was man in der Schule gelernt hat, vergisst.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Mehr als die Vergangenheit interessiert mich die Zukunft, denn in ihr gedenke ich zu leben.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
    array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Schämen sollten sich die Menschen, die sich gedankenlos der Wunder der Wissenschaft und Technik bedienen und nicht mehr davon geistig erfasst haben als die Kuh von der Botanik der Pflanzen, die sie mit Wohlbehagen frisst.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
     array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Um eine Einkommenssteuererklärung abgeben zu können, muss man Philosoph sein. Für einen Mathematiker ist es zu schwierig.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Wenn ich die Folgen geahnt hätte, wäre ich Uhrmacher geworden.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Universitäten sind schöne Misthaufen, auf denen gelegentlich einmal eine edle Pflanze gedeiht.<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "Schlagfertigkeit ist etwas, worauf man erst 24 Stunden später kommt.<@token_type=svgtext>Mark Twain<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
   array( 360, 120, "align_left", 5, 0, 2, 0, 2.7, 2.6, 1.05, 14, "<@token_type=svgtext>Albert Einstein<@token_type=shorthand>" ),
  */ 
);

function prepare_engine() {
    global $cookie_table, $cookie_number;
   
    push_session();
    // echo var_dump($cookie_table);
    if ($cookie_table !== NULL) {   // workaround for unknown bug ($cookie_table can become NULL - no idea why ...)
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
}

function restore_engine() {
    pop_session();
}

// there's a bug: if a new fortune cookie is calculate each time a new page is loaded and the new page calculates a new shorthand svg
// (like in calculate.php) the page hangs: it never get's beyond the fortune cookie calculation actually, so only the upper part of the page is 
// visible (without fortune cookie), and when you go back to the calculation page afterwards all session-variables are a mess (which seems logical
// since function restore_engine() is not executed) ... no idea why the fortune cookie isn't calculated in that case ... ?!?
// This bug shouldn't affect normal use of the page (since the fortune cookie is calculated only 1x / session and the user never has the chance
// to access a page tha calculates a shorthand svg when the fortune cookie is calculated at the same time, but even so it'd be nice to fix it ... ;-)
// for debugging purposes: do not calculate a new fortune cookie while trying to make a shorthand svg calculation at the same time ...
// $cookie_table is NULL in the above case ... still no idea why ... !?!
// in order to prevent the error from happening check if $cookie_table is NULL (and if so, renounce to fortune cookie caluclation ... this is only
// a workaround but it's better than having the programm hang each time ... )

//unset($_SESSION['fortune_cookie']); // unset session variable to generate new fortune cookie each time a page is loaded (for testing purposes)
if (!isset($_SESSION['fortune_cookie'])) {
    require_once "engine.php";
}

// returns a random fortune cookie if $fortune_cookie = "be_lucky"
// if $fortune_cookie == int number, this cookie is returned
function fortune() {
    global $fortune_cookie;
   
    switch ($fortune_cookie) {
            case "be_lucky" : return be_lucky(); break;
            default : return get_fortune( (int)$fortune_cookie ); break;
    }
}

function get_fortune( $number ) {
    global $cookie_table, $cookie_number;
    // returns fortune cookie $number
    //$_SESSION['original_text_content'] .= "get_fortune: $number";
    $cookie_number = $number; // use -1 to test 0 (0 doesn't work)
    
    prepare_engine();
    $svg = NormalText2SVG( $cookie_table[$cookie_number][11] );
    restore_engine();
    return $svg;
}

function be_lucky() {
    // return a random cookie number
    //$_SESSION['original_text_content'] .= "be_lucky()";
    global $cookie_table, $cookie_number;  
    $total_cookies = count($cookie_table);
    $cookie_number = (int)rand(0, $total_cookies-1);
    
    if (isset($_SESSION['fortune_cookie'])) return $_SESSION['fortune_cookie'];
    else {
        // only calculate 1 cookie per session
        prepare_engine();
        $svg = NormalText2SVG( $cookie_table[$cookie_number][11] );
        restore_engine();
        $_SESSION['fortune_cookie'] = "$svg";
        return $_SESSION['fortune_cookie']; //$svg;
    } 
}

?>