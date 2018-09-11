<?php 
require "vsteno_template_top.php";

// main
        
//if ($_POST['action'] === "=> DATENBANK") {
 //   SubmitDataToPurgatory();
/*} else {                // don't test for "zurücksetzen" (if it should be tested, careful with umlaut ...)
   InsertHTMLHeader();
   echo "go back";
   InsertHTMLFooter();
}
*/
$i = 0;
while (isset($_POST["txtstd$i"])) {
    echo "Write:" . $_POST["txtstd$i"] . "<br>";
    $i++;
}

require "vsteno_template_bottom.php";
?>
