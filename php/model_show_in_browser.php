<?php require "vsteno_fullpage_template_top.php"; ?>
<?php

    require_once "import_model.php";
    
    $actual_model = $_SESSION['actual_model'];
    echo "<h1>$actual_model</h1>";
    echo "<p>Anbei die Definitionen, welche es VSTENO ermöglichen, Langschrifttexte in Kurzschrift zu übertragen. Die Definitionen bestehen
    aus drei Hauptsektionen: Header, Font und Rules. Sie können dieses Modell oder Teile daraus kopieren und für eigene Stenosysteme verwenden.
    Weitere Informationen finden Sie in der <a href='../docs/documentation_v03.pdf'>Hauptdokumentation</a>.</p>";
    $model_code = LoadModelFromDatabase($actual_model);
    echo "<pre>$model_code</pre>";
    
    echo '<a href="input.php"><br><button>zurück</button></a>';
?>
<?php require "vsteno_fullpage_template_bottom.php"; ?>
