<?php require "vsteno_template_top.php"; 

function CreateFilterParameterFromGlobalVariables() {
    global $punctuation, $upper_case_punctuation, $numbers, $only_pretokens, $only_posttokens;
    $output = $punctuation . $upper_case_punctuation . $numbers . $only_pretokens . $only_posttokens; 
    return $output;
}
?>

<h1>MKCOR</h1>
<p>Erstellt aus einem originalen, fortlaufenden Text mit oder ohne Tags einen Wortkorpus, in welchem jedes Wort nur 1x vorkommt. 
Tags und die in Filter angegebenen Zeichen werden herausgefiltert. Wörter, die unter 'Ausschliessen' aufgeführt sind, werden nicht in 
den Korpus aufgenommen. Das Erstellen eines Wortkorpus erlaubt ein sehr effizientes Generieren und Lektorieren jener Wörter, die für eine 
Gesamtübertragung eines Textes benötigt werden.</p>

<h2>Daten</h2>

<form action="mkcor_execute.php" method="post">

    <?php $filter = isset($_POST['filter']) ? $_POST['filter'] : CreateFilterParameterFromGlobalVariables(); ?>

    <p>Filter:<br> <input type="text" name="filter" size="60" value='<?php echo $filter; ?>'></p>
    <p>Text:<br>
    <textarea id="original_text" name="original_text" rows="10" cols="100"><?php echo isset($_POST['original_text']) ? $_POST['original_text'] : "" ?></textarea></p>
    <p>Ausschliessen:<br>
    <textarea id="exclude" name="exclude" rows="10" cols="100"><?php echo isset($_POST['exclude']) ? $_POST['exclude'] : "" ?></textarea></p>

    <input type="submit" name="action" value="Ausf&uuml;hren">
</form>

<?php require "vsteno_template_bottom.php"; ?>
