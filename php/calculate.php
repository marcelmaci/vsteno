<?php 
function InsertHTMLHeader() {
    if ($_SESSION['output_integratedyesno']) {
        require "vsteno_template_top.php";
    } else {
        require "vsteno_fullpage_template_top.php";
    }
}

function InsertHTMLFooter() {
    if ($_SESSION['output_integratedyesno']) {
        require "vsteno_template_bottom.php";
    } else {
        require "vsteno_fullpage_template_bottom.php";
    }
}

function ResetSessionGetBackPage() {
    InitializeSessionVariables();   // output is reseted to integrated, so that the following message will appear integrated
    InsertHTMLHeader();
    echo "<p>Die Optionen wurden zurückgesetzt.</p>";
    echo '<a href="input.php"><br><button>"zurück"</button></a>';
    InsertHTMLFooter();
}

function InsertTitle() {
    if ($_SESSION['title_yesno']) {
            $size_tag = "h" . $_SESSION['title_size'];
            $size_tag_start = "<$size_tag>";
            $size_tag_end = "</$size_tag>";
            echo "$size_tag_start" . $_SESSION['title_text'] . "$size_tag_end\n";
    }
}

function InsertIntroduction() {
    // size is ignored for the moment
    if ($_SESSION['introduction_yesno']) {
            $p_tag_start = "<p>";
            $p_tag_end = "</p>";
            echo "$p_tag_start" . $_SESSION['introduction_text'] . "$p_tag_end\n";
    }
}

function InsertReturnButton() {
    if (!$_SESSION['output_without_button_yesno']) {
        echo '<a href="input.php"><br><button>"zurück"</button></a><br><br>';   
    }
}

function CalculateStenoPage() {
    CopyFormToSessionVariables();
    InsertHTMLHeader();
    InsertTitle();
    InsertIntroduction();
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
        //$stenogramm = NormalText2SVG( $test_wort, $angle, 0.8, 1.5, "black", "", $alternative_text);
        $angle = $_SESSION['token_inclination'];
        $thickness = $_SESSION['token_thickness'];
        $zoom = $_SESSION['token_size'];
        $color = $_SESSION['color_text_in_general'];
        if (!$_SESSION['output_texttagsyesno']) $alternative_text = "";
        
        $stenogramm = NormalText2SVG( $test_wort, $angle, $thickness, $zoom, $color, "", $alternative_text);
     
        //     echo "<p>Start: $original<br>==1=> /$lookuped/<br>==2=> $decapitalized<br>==3=> $shortened<br>==4=> $normalized<br>==5=> $bundled<br>==6=> $transcripted<br>==7=> $substituted<br>=17=> $test_wort<br> Meta: $metaparsed";
        //   echo "<br>$token_list[0]/$token_list[1]/$token_list[2]/$token_list[3]/$token_list[4]/$token_list[5]/$token_list[6]<br>$stenogramm</p>";

        echo "$stenogramm";
        //$incremental_string .= $stenogramm . "<!-- -->";
    }
    //echo '<a href="input.php"><br><button>"Nochmals!"</button></a>';
    InsertReturnButton();
    InsertHTMLFooter();
}

// include external data and code
require_once "constants.php";
require_once "data.php";
require_once "parser.php";
require_once "engine.php";
require_once "session.php";

// main

if ($_POST['action'] === "berechnen") {
    CalculateStenoPage();
} else {                // don't test for "zurücksetzen" (if it should be tested, careful with umlaut ...)
    ResetSessionGetBackPage();
}

?>
