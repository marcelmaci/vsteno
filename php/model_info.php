<?php require "vsteno_template_top.php"; ?>
<?php
    echo "<h1>" . $_SESSION['actual_model'] . "</h1>";
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
    echo "<p><a href='show_analyzer_parameters.php'>Linguistische Parameter</a></p>";
    echo '<a href="input.php"><br><button>zurück</button></a>';
?>
<?php require "vsteno_template_bottom.php"; ?>
