<?php require "vsteno_template_top.php"; require_once "session.php"; require_once "captcha.php"; $_SESSION['return_address'] = "input.php"; 

function RandomString( $length ) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    for ($i = 0; $i < $length; $i++) {
        $randstring .= $characters[rand(0, strlen($characters))];
    }
    return $randstring;
}


?>



<br>
<h1>Konto</h1>
<h2>Warum ein Konto?</h2>
<p>VSTENO arbeitet einerseits regelbasiert, andererseits greift es - eben bei Wörtern, die von den Regeln abweichen - auf ein Lexikon zurück, 
das serverseitig in einer Datenbank gespeichert ist. Da das Erstellen von Lexika eine Aufgabe ist, derer sich typischerweise eine ganze Gruppe von Leuten annimmt, 
soll es interessierten Stenograf/innen ohne Weiteres möglich sein, eigene Anmerkungen, Einträge und Korrekturen zu diesem Lexikon beizusteuern. 
<h2>Mensch oder BOT?</h2>
<p>Für diesen externen Zugriff muss jedoch sichergestellt werden, dass es sich bei den Beiträgen um Daten handelt, die von einem Menschen (und nicht etwa von einem Computer oder BOT) 
stammen, der auch tatsächlich etwas von Steno versteht. Für die Registrierung müssen Sie deshalb ein kurzes Stenogramm
(Captcha) in Langschrift übertragen.</p> 

<h2>Angaben</h2>
<p>
<form action="../php/create_account_execute.php" method="post">
<table><tr><td>Login:<br>
Password:<br>
Captcha:<br>
E-Mail*:
</td>

<td> <input type="text" name="username"  size="20" value="<?php echo RandomString(8); ?>"><br>
<input type="text" name="password"  size="20" value="<?php echo RandomString(8); ?>"><br>
<input type="text" name="captcha"  size="20" value="<?php $temp = GetShorthandCaptcha(4); echo $_SESSION['captcha']; ?>"><br>
<input type="text" name="email"  size="20" value="">
</td> 
<td>
Captcha:<br>
<?php echo $temp; ?>

</td>

</tr>
</table>
<input type="submit" name="action" value="anlegen">
</form>

<h2>Datenschutz</h2>
<p>Aus Datenschutzgründen empfehlen wir zufällige Benutzernamen und Passwörter zu wählen (ein Vorschlag wird automatisch generiert und angezeigt). 
</p><p>(*) Die Angabe einer E-Mail-Adresse ist freiwillig (sie wird nur für Rückfragen zu Einträgen oder
zur Mitteilung von Änderungen betreffend VSTENO verwendet).</p>

<?php require "vsteno_template_bottom.php"; ?>