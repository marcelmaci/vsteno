<?php 


require "vsteno_template_top.php";
require_once "data.php";

echo "<h1>Statistik</h1>";
//var_dump($rules); 
$model = $_SESSION['actual_model'];
//var_dump($_SESSION['rules_count']);
//var_dump($rules[$model]);
//echo "rule(130: " . $rules[$model][$i][130] . "<br>";

if (isset($_SESSION['rules_count'])) {
    echo "<p>Die Zahlen geben an, welche Regel wie oft angewandt wurde.</p>";
    echo "<h2>Hinweis</h2>";
    echo "Da Wörter, die im Text mehrmals vorkommen 'gecachet' (zwischengespeichert) werden, geben die Werte lediglich an, wie häufig eine Regel angewandt wurde (nicht aber, in wie vielen Wörtern, bzw. wie oft das durch die Regel abgedeckte Phänomen im ganzen Text vorkommt!)<br>";
    
    echo "<h2>Verwendet</h2>";

    for ($i=0; $i<count($rules[$model]); $i++) {
        if ($_SESSION['rules_count'][$i] > 0) 
        echo "[$i]: [" . $_SESSION['rules_count'][$i] . "]: " . WrapStringAfterNCharacters($rules[$model][$i][0],30) . " => " . WrapStringAfterNCharacters($rules[$model][$i][1],30) . "<br>";   
    }

    echo "<h2>Unbenutzt</h2>";
    for ($i=0; $i<count($rules[$model]); $i++) {
        if ($_SESSION['rules_count'][$i] === 0) {
            if ((mb_strpos($rules[$model][$i][0], "BeginFunction(") !== FALSE) || 
                (mb_strpos($rules[$model][$i][0], "EndFunction(") !== FALSE)) {}
            else echo "[$i]: [" . $_SESSION['rules_count'][$i] . "]: " .  WrapStringAfterNCharacters($rules[$model][$i][0],30) . " => " . WrapStringAfterNCharacters($rules[$model][$i][1],30) . "<br>";   
        }
    }
} else {
    echo "<p>Keine Statistik-Daten verfügbar.<br><i>(Führen Sie zuerst eine Berechnung aus.)</i></p>";
}
 echo '<br><a href="' . $_SESSION['return_address'] . '"><button>zurück</button></a><br><br>';   
 
require "vsteno_template_bottom.php"; 

?>