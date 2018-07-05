<?php require "vsteno_template_top_local.php";?>
<h1>VSTENO</h1>
<i><p>(Vector Steno Tool with Enhanced Notational Options)</p></i>
<?php

// include external data and code
require_once "constants.php";
require_once "data.php";
require_once "parser.php";
require_once "engine.php";
require_once "session.php";

$angle = 60;

if (isset($_POST['original_text'])) {
    $test_text = htmlspecialchars($_POST['original_text']);
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
<a href="../web/vsteno_input_options.php"><br><button>"Nochmals!"</button></a>
<?php require "../web/vsteno_template_bottom.php";?>
