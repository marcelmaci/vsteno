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
soll es interessierten Stenograf/innen ohne Weiteres möglich sein, eigene Anmerkungen, Einträge und Korrekturen zu diesem Lexikon beizusteuern.</p>
<h2>Mensch oder BOT?</h2>
<p>Für diesen externen Zugriff muss sichergestellt werden, dass es sich bei den Beiträgen um Daten handelt, die von einem Menschen (und nicht etwa von einem Computer oder BOT) 
stammen, der auch tatsächlich etwas von Steno versteht. Für die Registrierung müssen Sie deshalb ein kurzes Stenogramm
(Captcha) in Langschrift übertragen.</p> 

<h2>Angaben</h2>
<p>
<form action="../php/create_account_execute.php" method="post">
<table><tr><td>Login:<br>
Password:<br>
Captcha:<br>
System:<br>
Name*:<br>
E-Mail*:<br>
Infos*:
</td>

<td> <input type="text" name="username"  size="30" value="<?php echo RandomString(8); ?>"><br>
<input type="text" name="password"  size="30" value="<?php echo RandomString(8); ?>"><br>
<input type="text" name="captcha"  size="30" value="<?php $temp = GetShorthandCaptcha(4); /*echo $_SESSION['captcha'];*/ ?>"><br>
<input type="radio" name="model" value="standard" checked> Standard
<input type="radio" name="model" value="empty"> leer<br>
<input type="text" name="realname"  size="30" value=""><br>
<input type="text" name="email"  size="30" value=""><br>
<input type="radio" name="info" value="infono" checked> nein
<input type="radio" name="info" value="infoyes"> ja<br>
</td> 
<td>
Captcha:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
<?php echo $temp; ?>

</td>
<td>
<b>Tipp:</b><br>Schalten Sie die <a href="input.php">Hilfslinien</a> ein. Das Captcha wird dann besser lesbar!<br>=> <a href="create_account.php">anderes Captcha</a>
</td>
</tr>
</table>
<input type="submit" name="action" value="anlegen">
</form>
<h2>Eigenes Stenografiesystem</h2>
<p>Nach Eröffnung des Nutzerkontos haben Sie auch die Möglichkeit, ein eigenes Stenografiesystem anzulegen (mit eigenen Zeichen und Regeln zur Übertragung von Langschrifttexten in Kurzschrift).</p>
<h2>Datenschutz</h2>
<p>Aus Sicherheits- und Datenschutzgründen empfehlen wir zufällige Benutzernamen und Passwörter zu wählen (ein Vorschlag wird automatisch generiert und angezeigt). Angaben mit Stern sind 
freiwillig (optional) und werden folgendermassen verwendet:<p/>
<p><b>E-Mail:</b> Rückfragen zu Einträgen im Wörterbuch, die von Ihnen stammen, und - falls Sie die Option anwählen - Versand von allgemeinen Infos zu VSTENO.
Empfohlenes Format: beispiel at domainname dot ch.</p>
<p><b>Name:</b> Wenn vorhanden, wird Ihr Name als Urherber zu Ihren Beiträgen im Wörterbuch vermerkt (falls kein Name vorhanden ist, wird nur der Login-Name verlinkt). 
Grundsätzlich ist somit für jeden Wörterbucheintrag der/die Urherber/in feststellbar. Diese Verlinkung erlischt automatisch, wenn Sie Ihren Account löschen (die Wörterbucheinträge bleiben 
dann bestehen, können dann aber keinem Login/Namen mehr zugeordnet werden).</p>

<?php require "vsteno_template_bottom.php"; ?>  