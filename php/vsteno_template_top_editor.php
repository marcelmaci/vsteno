<?php session_start(); ?>
<!-- <!DOCTYPE HTML> -->
<html>
<head>
    <meta charset='utf-8'>
    <title>VSTENO - Vector Shorthand Tool with Enhanced Notational Options</title>
    <link rel="stylesheet" type="text/css" href="../web/vsteno_style.css">
    <script type="text/javascript" src="../js/paper.js/dist/paper-full.js"></script>
    <script src="../js/vsteno_editor.js" type="text/paperscript" canvas="drawingArea"></script>
   
</head>
<body>
	<!-- page layout inside div container -->
	<div id="container">
		<div id="title">
            <div id="purelab_banner">
                <a href="pen_black_and_white.php"><img src="../web/pen_black_and_white_120px_1.jpg" height="120"></a>
            </div>
            <div id="purelab_title">
                <h1>VSTENO</h1>
                <h2>Vector Shorthand Tool with Enhanced Notational Options</h2>
            </div>
           <div id="purelab_texts">
                <?php 
                    require_once "fortune.php";
                    echo "<center>" . fortune() . "</center>";
                ?>
           </div>
            
		</div>
		<div id="main">
            <div id="navigation">
                <?php require "navigation.php"; ?>
            </div>
            <div id="content">

