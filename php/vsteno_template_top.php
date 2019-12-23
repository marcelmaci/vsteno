<?php session_start(); 
// QUICK&DIRTY way to set inverted mode
// session variable for normal or inverted mode
// comment out for normal mode
// $_SESSION['display_mode'] = "inverted";
$_SESSION['display_mode'] = "normal";

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset='utf-8'>
    <title>VSTENO - Vector Shorthand Tool with Enhanced Notational Options</title>
<?php
    switch ($_SESSION['display_mode']) {
        case "inverted" : echo '<link rel="stylesheet" type="text/css" href="../web/vsteno_style_inverted.css">'; break;
        default : echo '<link rel="stylesheet" type="text/css" href="../web/vsteno_style.css">'; 
    }
?>
</head>
<body>
	<!-- page layout inside div container -->
	<div id="container">
		<div id="title">
            <div id="purelab_banner">
                
<?php
    switch ($_SESSION['display_mode']) {
        case "inverted" : echo '<a href="pen_black_and_white.php"><img src="../web/pen_black_and_white_120px_1_inverted.jpg" height="120"></a>'; break;
        default : echo '<a href="pen_black_and_white.php"><img src="../web/pen_black_and_white_120px_1.jpg" height="120"></a>'; 
    }
?>

            </div>
            <div id="purelab_title">
                <h1>VSTENO</h1>
                <h2>Vector Shorthand Tool with Enhanced Notational Options</h2>
                
            </div>
           <div id="purelab_texts">
                
                <?php 
                    require_once "fortune.php";
                    //$_SESSION['original_text_content'] = $fortune_cookie;
                    echo "<center>" . fortune() . "</center>";
                ?>
           </div>
            
		</div>
		<div id="main">
            <div id="navigation">
                
                <?php require "navigation.php"; /*echo $fortune_cookie;*/ ?>
                
            </div>
            <div id="content">

