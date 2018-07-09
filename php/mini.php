<?php require "vsteno_template_top.php"; require_once "session.php"; $_SESSION['return_address'] = "mini.php"; ?>
<br>
<center>
<b>Mini-Version</b><br>
<div id="order">
<form action="../php/calculate.php" method="post">
<table>
<tr><td>Text</td></tr>
<tr><td>
<input type="radio" name="text_format_metayesno" value="normal" <?php echo ($_SESSION['original_text_format'] === "normal") ? "checked" : "";?>> Langschrift 
<input type="radio" name="text_format_metayesno" value="metaform" <?php echo ($_SESSION['original_text_format'] === "metaform") ? "checked" : "";?>> Metaform<br>
<textarea id="original_text" name="original_text" rows="20" cols="100"><?php echo $_SESSION['original_text_content']; ?>
</textarea>
</td></tr>
</table>
<input type="submit" name="action" value="abschicken"><a href="input.php"><input type="button" value="Optionen"></a>
</form>
</div>
</center>
<?php require "vsteno_template_bottom.php"; ?>