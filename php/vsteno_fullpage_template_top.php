<?php 
//session_set_cookie_params(3600); // set session cookie lifetime to 1 hour if fullpage is selected as output
//ini_set('session.gc_maxlifetime', 3600); // idem for session maxlifetime
////ini_set('max_input_time', 3600); // idem pour max_input_time
//ini_set('memory_limit', '2048M');
session_start(); 
?>

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
