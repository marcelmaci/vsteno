<?php session_start(); ?>
<!-- <!DOCTYPE HTML> -->
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
