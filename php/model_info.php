<?php require "vsteno_template_top.php"; ?>
<?php
    echo "<h1>" . $_SESSION['actual_model'] . "</h1>";
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
    echo "<h1>Weitere</h1>";
    echo "<p><a href='show_analyzer_parameters.php'>Linguistische Parameter</a><br>
    <a href='model_show_in_browser.php'>Quellcode</a></p>";
    
    echo '<a href="input.php"><br><button>zurück</button></a>';
?>
<?php require "vsteno_template_bottom.php"; ?>
