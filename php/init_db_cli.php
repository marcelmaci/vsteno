<?php 
session_start();
$_SESSION['fortune_cookie'] = "";
ini_set('display_errors','on');    // turn off errors in order to keep error.log of apache server clean
//error_reporting(0);  
/* this file can be used to create the tables and other data (standard user, models) necessary for vsteno database */
require_once "init_db_common.php";

// main
$conn = connect_or_die();
create_tables();
CreateStandardUser();
CreateModels();

?>
