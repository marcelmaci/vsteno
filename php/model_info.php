<?php require "vsteno_template_top.php"; ?>
<?php
     echo "FontBorrow: " . $_SESSION['font_borrow_yesno'] . " " . $_SESSION['font_borrow_model_name'] . "<br>";
    $_POST['font_borrow_yesno'] = ($_SESSION['font_borrow_yesno']) ? "yes" : "no"; // uh ... very ugly ... :):):) set POST to make data.php load borrowed font ...
    $_POST['font_borrow_model_name'] = $_SESSION['font_borrow_model_name'];
    require_once "data.php"; // unfortunately, this is necessary to have correct statistics data ...
    $actual_model = $_SESSION['actual_model'];
    
    echo "<h1>$actual_model</h1>";
    echo "<h1>Version</h1>";
    if ((mb_strlen($_SESSION['model_version'])==0) && (mb_strlen($_SESSION['model_date'])==0)) echo "<p>(empty)</p>"; 
    else {
        echo "<p>";
        if (mb_strlen($_SESSION['model_version'])>0) {
            echo "Version: " . $_SESSION['model_version'];
            if (mb_strlen($_SESSION['model_date'])>0) echo "<br>";
        }
        if (mb_strlen($_SESSION['model_date'])>0) echo "Date: " . $_SESSION['model_date'];
        echo "</p>";
    }
    echo "<h1>License</h1>";
    if (mb_strlen($_SESSION['license'])>0) echo "<p>" . $_SESSION['license'] . "</p>";
    else echo "<p>empty</p>";
    echo "<h1>Notes</h1>";
    if (mb_strlen($_SESSION['release_notes'])>0) echo "<p>" . $_SESSION['release_notes'] . "</p>";
    else echo "<p>empty</p>";
    echo "<h1>Footer</h1>";
    if (mb_strlen($_SESSION['copyright_footer'])>0) echo "<p>" . $_SESSION['copyright_footer'] . "</p>";
    else echo "<p>empty</p>";
    echo "<h1>Stats</h1>";
    $number_rules = $_SESSION['statistics_rules'] - ($_SESSION['statistics_subsections'] * 2);
    $number_analyzer_rules = $_SESSION['statistics_analyzer_rules'];
    $number_tokens = $_SESSION['statistics_tokens'];
    $number_base = $_SESSION['statistics_base'];
    $number_combined = $_SESSION['statistics_combined'];
    $number_shifted = $_SESSION['statistics_shifted'];
    $number_subsections = $_SESSION['statistics_subsections'];
    echo "<p>Tokens: $number_tokens (base: $number_base / combined: $number_combined / shifted: $number_shifted)<br>Rules: SE: $number_rules (subsections: $number_subsections) / Analyzer: $number_analyzer_rules<br></p>";
    echo "<h1>More</h1>";
    echo "<p><a href='show_analyzer_parameters.php'>Linguistische Parameter</a><br>
    <a href='model_show_in_browser.php'>Quellcode</a></p>";
    
    echo '<a href="input.php"><br><button>zurück</button></a>';
?>
<?php require "vsteno_template_bottom.php"; ?>
