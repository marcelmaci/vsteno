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
    if (($_SESSION['title_yesno']) && ($_SESSION['output_format'] !== "debug")) {
            $size_tag = "h" . $_SESSION['title_size'];
            $size_tag_start = "<$size_tag>";
            $size_tag_end = "</$size_tag>";
            echo "$size_tag_start" . $_SESSION['title_text'] . "$size_tag_end\n";
    }
}

function InsertIntroduction() {
    // size is ignored for the moment
    if (($_SESSION['introduction_yesno']) && ($_SESSION['output_format'] !== "debug")) {
            $p_tag_start = "<p>";
            $p_tag_end = "</p>";
            echo "$p_tag_start" . $_SESSION['introduction_text'] . "$p_tag_end\n";
    }
}

function InsertReturnButton() {
    if (!$_SESSION['output_without_button_yesno']) {
        echo '<a href="' . $_SESSION['return_address'] . '"><br><button>"zurück"</button></a><br><br>';   
    }
}

function CalculateStenoPage() {
    $there_is_text = (isset($_POST['original_text']) && (strlen($_POST['original_text']) > 0));
    CopyFormToSessionVariables();
    InsertHTMLHeader();
    if ($there_is_text) {
        InsertTitle();
        InsertIntroduction();
    }
    $angle = 60;
    if (isset($_POST['original_text']) && (strlen($_POST['original_text']) > 0)) {
        $test_text = /*htmlspecialchars(*/$_POST['original_text']; //);     // do not escape entered text
        $test_text = preg_replace( "/>([^<])/", "> $1", $test_text );         // with this replacement inline- and html-tags can be placed everywhere
        $test_text = preg_replace( "/([^>])</", "$1 <", $test_text );         // with this replacement inline- and html-tags can be placed everywhere
        
        //echo "Eingegeben: >$test_text<";
    } else echo "<h1>Optionen</h2><p>Die neuen Optionen wurden gesetzt.</p>";

    $test_text = preg_replace( '/\s{2,}/', ' ', ltrim( rtrim($test_text)) );
    
    // Original idea: use spaces to separate words that have to be transformed into shorthand-sgvs
    // Problem: there are html-tags which have spaces inside, e.g. <font size="7"> (consequence: the tags will get separated and the different
    // parts will be treated as words to transform.
    // Solution: Replace temporarily all spaces inside html-tags with $nbsp; => separate the words => replace all &nbsp; with ' '
    // (Potential problem with that solution: $nbsp; inside html-tags inserted by user will also get converted => don't think that should happen)
    //echo "Text before: $test_text<br>";
    
    $test_text = replace_all( '/(<[^>]*?) (.*?>)/', '$1#XXX#$2', $test_text );
    //echo "<br>Replaced Spaces:<br>" . htmlspecialchars($test_text) . "<br><br>";
    $test_text_array = explode( " ", $test_text);
    foreach ( $test_text_array as $key => $separate_entry) {
        //echo "<br>Entry aus Array: " . htmlspecialchars($separate_entry) . " => ";
        $test_text_array[$key] = replace_all( '/(<[^>]*?)#XXX#(.*?>)/', '$1 $2', $separate_entry);
        //echo htmlspecialchars($separate_entry) . "<br>";
        
    }
    //foreach ( $test_text_array as $separate_entry) echo "$separate_entry Test";
    
    foreach ( $test_text_array as $test_wort ) {
        
        $original = $test_wort;
        $globalized = Globalizer( $test_wort );
        $lookuped = Lookuper( $test_wort );
        //$test_wort = Trickster( $test_wort);
        $decapitalized = Decapitalizer( $test_wort );
        $shortened = Shortener( $decapitalized );
        $normalized = Normalizer( $shortened );
        $bundled = Bundler( $normalized );
        $transcripted = Transcriptor( $bundled );
        $substituted = Substituter( $transcripted );
        list($pre, $metaparsed, $post) = MetaParser( $test_wort );
        $alternative_text = $original;
        //$stenogramm = TokenList2SVG( $token_list, $angle, 0.8, 1.5, "black", "", $alternative_text);   
        //$stenogramm = NormalText2SVG( $test_wort, $angle, 0.8, 1.5, "black", "", $alternative_text);
        $angle = $_SESSION['token_inclination'];
        $thickness = $_SESSION['token_thickness'];
        $zoom = $_SESSION['token_size'];
        $color = $_SESSION['token_color'];
        if (!$_SESSION['output_texttagsyesno']) $alternative_text = "";
        
        $stenogramm = NormalText2SVG( $test_wort, $angle, $thickness, $zoom, $color, "", $alternative_text);
     
        if (mb_strlen($stenogramm) > 0) {
            if ($_SESSION['output_format'] === "debug") {
                echo "<p>Start: $original<br>==0=> $globalized<br>==1=> /$lookuped/<br>==2=> $decapitalized<br>==3=> $shortened<br>==4=> $normalized<br>==5=> $bundled<br>==6=> $transcripted<br>==7=> $substituted<br>=17=> $test_wort<br> Meta: $metaparsed<br><br>";
                //   echo "<br>$token_list[0]/$token_list[1]/$token_list[2]/$token_list[3]/$token_list[4]/$token_list[5]/$token_list[6]<br>$stenogramm</p>";
            } 
            echo "$stenogramm";
            //echo "Trickster: " . Trickster("Markthalle") . "<br>";
        
            //$incremental_string .= $stenogramm . "<!-- -->";
        }
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

// just create combined/shifted-tokens once per call of calculate.php (performace)
CreateCombinedTokens();
CreateShiftedTokens();
        
if ($_POST['action'] === "abschicken") {
    CalculateStenoPage();
} else {                // don't test for "zurücksetzen" (if it should be tested, careful with umlaut ...)
    ResetSessionGetBackPage();
}

?>
