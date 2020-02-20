<?php session_start(); // normally, session is started in template; in order to make the session variable take effect immediately do it manually here (will be called a second time later which is no problem)

if (isset($_SESSION['user_logged_in']) && ($_SESSION['user_logged_in'])) {
    switch ($_SESSION['display_mode']) {
        case "inverted" : $_SESSION['display_mode'] = "normal"; 
                          $_SESSION['token_color'] = "black";
                          $_SESSION['title_color'] = "black";
                          $_SESSION['introduction_color'] = "black";
                          $_SESSION['output_page_number_color'] = "black";
                          $_SESSION['output_line_number_color'] = "black";
                        break;
        default: 
                $_SESSION['display_mode'] = "inverted";
                $_SESSION['token_color'] = "white";
                $_SESSION['title_color'] = "white";
                $_SESSION['introduction_color'] = "white";
                $_SESSION['output_page_number_color'] = "white";
                $_SESSION['output_line_number_color'] = "white";
    }
}

require_once "vsteno_template_top.php";
echo "<h1>View</h1>";
if (isset($_SESSION['user_logged_in']) && ($_SESSION['user_logged_in'])) {
   echo "<p>Display mode: " . $_SESSION['display_mode'] . "</p>";
} else {
    echo "<p>You must be logged in</p>";
}
echo '<a href="input.php"><br><button>zurück</button></a><br><br>';   
require_once "vsteno_template_bottom.php";

?>

