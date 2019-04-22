<?php require "vsteno_template_top.php"; require_once "session.php"; require_once "captcha.php"; $_SESSION['return_address'] = "show_account_information.php"; $_SESSION['output_format'] = "inline";

?>

<br>
<h1>Passwort ändern</h1>

<?php
if ((isset($_SESSION['user_logged_in'])) && ($_SESSION['user_logged_in'])) {

?>
<p>Vervollständigen Sie: altes (1x), neues (2x) Passwort und Captcha.</h2>
<p>Für diesen externen Zugriff muss sichergestellt werden, dass es sich bei den Beiträgen um Daten handelt, die von einem Menschen (und nicht etwa von einem Computer oder BOT) 
stammen, der auch tatsächlich etwas von Steno versteht. Für die Registrierung müssen Sie deshalb ein kurzes Stenogramm
(Captcha) in Langschrift übertragen.</p> 

<h2>Angaben</h2>
<p>
<form action="../php/change_password_execute.php" method="post">
<table><tr><td>User:<br>
PW&nbsp;(old):<br>
PW&nbsp;(new):<br>
PW&nbsp;(2x): <br>
Captcha:<br>
</td>

<td><i><?php echo $_SESSION['user_username']; ?></i><br>
<input type="password" name="password_old"  size="30" value=""><br>
<input type="password" name="password_new1"  size="30" value=""><br>
<input type="password" name="password_new2"  size="30" value=""><br>
<input type="text" name="captcha"  size="30" value=""><br>
</td> 
<td>
Captcha:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
<?php $temp = GetShorthandCaptcha(4); echo $temp; ?>

</td>
<td>
<b>Tipp:</b><br>Schalten Sie die <a href="input.php">Hilfslinien</a> ein. Das Captcha wird dann besser lesbar!<br>=> <a href="change_password.php">anderes Captcha</a>
</td>
</tr>
</table>
<input type="submit" name="action" value="ändern">
</form>

<?php } else {
    
    echo "<p>Bitte loggen Sie sich zuerst ein!</p>";
}

?>

<?php require "vsteno_template_bottom.php"; ?>  