<?php 
session_start();
$_SESSION['fortune_cookie'] = "";
ini_set('display_errors','on');    // turn off errors in order to keep error.log of apache server clean
//error_reporting(0);  
/* this file can be used to create the tables and other data (standard user, models) necessary for vsteno database */
require_once "dbpw.php";
require_once "init_db_common.php";

// main
if (isset($_POST["mpw"])) {
    if (isset($_POST['mpw'])) $temphash = hash( "sha256", $_POST['mpw']);
    if ($temphash === master_pwhash) {
        echo "start initialization ...<br>";
        echo "createdatabase";
        create_database( db_servername, db_username, db_password, db_dbname);
        // do the rest with newly created database
        $conn = connect_or_die();
        echo "createtables<br>";
        create_tables();
        echo "createstandarduser<br>";
        CreateStandardUser();
        echo "createmodels<br>";
        CreateModels();
        echo "<h1>Run the programm ...</h1>";
        echo "<p>Click on <a href='http://localhost/vsteno/php/input.php'>this link</a> to go to the input form for VSTENO.</p>";
    } 
} else {
        echo "<h1>Password</h1>";
        echo "<form action='init_db.php' method='post'>
                <input type='text' name='mpw'  size='30' value=''><br>
                <input type='submit' name='action' value='senden'>
             </form>";
}

?>
