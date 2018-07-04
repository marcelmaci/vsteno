<html>
<!-- <!DOCTYPE HTML> -->
<!-- 
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
-->
<head>
    <meta charset='utf-8'>
    <title>VSTENO - Vector Steno Tool with Enhanced Notational Options"</title>
</head>
<body>
<h1>VSTENO</h1>
<i><p>(Vector Steno Tool with Enhanced Notational Options)</p></i>
<?php

// include external data and code
require_once "constants.php";
require_once "data.php";
require_once "parser.php";
require_once "engine.php";

$angle = 60;

if (isset($_POST['langtext'])) {
    $test_text = htmlspecialchars($_POST['langtext']);
    //echo "eingeben: $test_text";
} else echo "Hm ... seems as if there's no text ...";

$test_text = preg_replace( '/\s{2,}/', ' ', ltrim( rtrim($test_text)) );
$test_text_array = explode( " ", $test_text);

foreach ( $test_text_array as $test_wort ) {
     $original = $test_wort;
     
     $lookuped = Lookuper( $test_wort );
     //$test_wort = Trickster( $test_wort);
     $decapitalized = Decapitalizer( $test_wort );
     $shortened = Shortener( $decapitalized );
     $normalized = Normalizer( $shortened );
     $bundled = Bundler( $normalized );
     $transcripted = Transcriptor( $bundled );
     $substituted = Substituter( $transcripted );
     $metaparsed = MetaParser( $test_wort );
     $alternative_text = $original;
     //$stenogramm = TokenList2SVG( $token_list, $angle, 0.8, 1.5, "black", "", $alternative_text);   
     $stenogramm = NormalText2SVG( $test_wort, $angle, 0.8 /*0.5*/, 1.5 /*1*/, "black", "", $alternative_text);
     
//     echo "<p>Start: $original<br>==1=> /$lookuped/<br>==2=> $decapitalized<br>==3=> $shortened<br>==4=> $normalized<br>==5=> $bundled<br>==6=> $transcripted<br>==7=> $substituted<br>=17=> $test_wort<br> Meta: $metaparsed";
  //   echo "<br>$token_list[0]/$token_list[1]/$token_list[2]/$token_list[3]/$token_list[4]/$token_list[5]/$token_list[6]<br>$stenogramm</p>";

     echo "$stenogramm";
     //$incremental_string .= $stenogramm . "<!-- -->";
}
?>
<a href="input.php"><br><button>"Nochmals!"</button></a>
</body>
</html> 