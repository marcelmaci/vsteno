<?php require "vsteno_template_top.php"; ?>
<h1>Installation</h1>
<h1>Automatisiert</h1>
<p>Mit dem Commit vom 10. Juli 2019 sind erstmals automatisierte Installationsskripts verfügbar. Gehen Sie wie folgt vor, um diese zu verwenden:</p>
<ol>
<li>Laden Sie die ZIP-Datei <a href="../downloads/install.tar.gz">install.tar.gz</a> herunter.</li>
<li>Entpacken Sie die ZIP-Datei (es werden 4 Dateien ins Verzeichnis ./install/ entpackt).</li>
<li>Öffnen Sie eine Shell in diesem Verzeichnis.</li>
<li>Machen Sie die Installationsskripts ausführbar: sudo chmod +x *.sh</li>
<li>Starten Sie die Installation: sudo ./install_vsteno.sh</li>
</ol>
<h2>Ablauf</h2>
<p>Die Skripts aktualisieren die Paketquellen (sudo apt-get update) und installieren diverse Programme (falls Sie dies nicht möchten - 
weil Sie z.B. auf bestimmte, ältere Programmversionen von hunspell, eSpeak, mySQL, git etc. angewiesen sind -, führen Sie den Installer nicht 
aus). Anschliessend lädt es das Programm VSTENO von Github herunter (standardmässig ins Apache-Verzeichnis /var/www/html/ unter /var/www/html/vsteno 
und konfiguriert die Datenbank durch Aufrufen des PHP-Skripts init_db.php
im Webbrowser (ABrowser). Die Installation dauert 5-10 Minuten und während des Vorgangs müssen Sie diverse Vorgänge mit "ja" bestätigen.</p>
<h2>Datenbank</h2>
<p>Etwa in der Mitte der Installation - nach der Installation von MySQL - werden Sie aufgefordert, ein Root-Passwort für die Datenbank einzugeben 
(und zu bestätigen). Etwas später öffnet das Skript dann die Datei dbpw.php im Texteditor vi. Sie müssen dort nun die Datenbankangaben eintragen. Da vi bzgl. 
Tastaturbefehle etwas "tricky" ist, hier ein paar wichtige Hinweise:</p>
<ul>
<li>vi befindet sich standardmässig im Befehlsmodus - es kann also kein Text eingegeben werden. Drücken Sie 1x die Taste I um in den Einfüge-
Modus (insert) zu gelangen.</li>
<li>Navigieren Sie nun mit den Pfeiltasten zu den Datenbankangaben und ändern Sie diese</li>
</ul>
<pre>
const db_servername = "127.0.0.1:3306";
const db_username = "root";
const db_password = "xxxxxxxx";
const db_dbname = "sys";
</pre>
<ul>
<li>Speichern Sie die Änderungen, indem Sie 1x die Taste ESC gefolgt von den Tasten : und X drücken (abschliessend mit Return).</li>
</ul>
<p>Ersetzen Sie xxxxxxx durch Ihr Passwort. Der Datenbankname "sys" entspricht der Standardkonfiguration unter Trisquel8 (ändern Sie diese 
falls nötig - mit der mysql-workbench können Sie die entsprechenden Angaben auf Ihrem System nachschauen).</p>
<h2>Kompatibilität</h2>
<p>Die Installation wurde im Moment nur für Trisquel8 geschrieben und getestet. Falls Sie eine Debian-ähnliche Linux-Distribution verwenden, 
bestehen durchaus Chancen, dass der Installer funktioniert ... ;-) Einzig das Browser-Skript init_db.php am Schluss werden Sie manuell 
aufrufen müssen: Starten Sie hierfür Ihren System-Browser und geben Sie als Adresse http://localhost/vsteno/php/init_db.php ein.</p>
<h2>Passwörter</h2>
<p>VSTENO verwendet das Standardpasswort '11111111'. Die benötigen dieses zur Konfiguration der Datenbank (init_db.php). Ebenso legt das 
Installationsskript einen Standardnutzer 'standard' mit dem Passwort '11111111' an. Löschen Sie nach der Installation die Datei init_db.php 
und ändern Sie das Passwort des Standard-Nutzers, in dem Sie sich einloggen und die Funktion "Passwort ändern" verwenden.</p>
<h2>Haftungsausschluss</h2>
<p>Wie bereits unter der GPL zum Programm hingewiesen übernehme keine Garantie, dass (1) der Installer funktioniert und (2) keinen Schaden 
an Ihrem System anrichtet. Wenn Sie nicht sicher sind, installieren Sie VSTENO in einer virtuellen oder auf einer separaten Maschine.</p> 
<h1>Manuell</h1>
<p>Nach wie vor gültig ist die manuelle Installation. Es ist einzig zu ergänzen, dass zusätzlich das Programm eSpeak installiert werden muss 
(sudo apt-get install espeak)</p>
<pre>
﻿VSTENO INSTALLIEREN

Vorbemerkung

VSTENO ist nach wie vor in Entwicklung, deshalb gibt es keinen stadardisierten
Installer, um alle Komponenten zum Laufen zu bringen. Die nachfolgenden An-
weisungen sind so detailliert wie möglich, damit auch Interessierte, die wenig
mit PHP, Webservern und Datenbanken vertraut sind, die Möglichkeit haben, das
Programm auf ihrem System zum Laufen zu bringen. Im Gegenzug ist das Dokument
natürlich ziemlich lang geworden (aber "versiertere" Nutzer/innen habe ja 
jederzeit die Möglichkeit, stark detaillierte Passage zu überspringen). Für
Fragen stehe ich gerne zur Verfügung (m.maci@gmx.ch).

Installation

VSTENO wurde unter Trisquel-GNU/Linux entwickelt und läuft somit zunächst 
einmal unter Debian-Abkömmlingen (Debian, Ubuntu*, Trisquel). Auf anderen 
GNU/Linux-Distributionen (und auch anderen Betriebssystemen, die eine LAMP-
Umgebung zur Verfügung stellen können) sollte VSTENO aber ebenfalls (allen-
falls mit minimalen Modifikationen und etwas anderen Installationsbefehlen) 
laufen. Benötigt wird, wie erwähnt, eine LAMP-Umgebung, wobei die 4 Buch-
staben stehen für:

L = Linux (bzw. GNU/Linux*)
A = Apache-Webserver (kann auch ein anderer sein, z.B. nginx)
M = MySQL (kann auch eine andere sein, allerdings müssen die SQL-Abfragen 
    kompatibel sein)
P = PHP-Skriptsprache (ab ca. Version 5.0, je neuer desto besser)

Im Folgenden wird die Installation unter Trisquel (Debian) mit Apache und MySQL 
erklärt.

(A) Vorbereitung

Bevor Sie die benötigten Programme herunterladen und installieren, sollten Sie 
den Paketmanager auf den neuesten Stand bringen. Öffnen Sie hierfür ein 
Terminal und geben Sie dort ein:

sudo apt-get update

Web-Server
1) Browser öffnen und als Adresse „localhost“ oder „127.0.0.1“ eingegeben.
2) Wenn eine Beispielseite angezeigt sind: bestens (Apache ist installiert). 
Sonst Apache installieren:

sudo apt-get install apache2

PHP-installieren

1) Terminal öffen und „php --help“ eingeben.
2) Werden die Optionen von PHP angezeigt: bestens (PHP ist installiert). Sonst 
PHP installieren (es sollten alle Versionen ab ca. PHP5 laufen, je neuer desto 
besser, hier das Beispiel für PHP7.0):

sudo apt-get install php7.0

Wörterbuch Hunspell

1) In Terminal „hunspell“ eingeben
2) Hunspell installieren -D eingeben. Falls eine Verzeichnisliste und die 
installierten Wörterbücher angezeigt werden: bestens (hunspell und die dazu-
gehörigen Wörterbücher sind installiert). Sonst Programm hunspell und Wörter-
bücher installieren:

Programm:

sudo apt-get install hunspell

Wörterbücher (Deutsch und Spanisch)

sudo apt-get install hunspell-de-ch
sudo apt-get install hunspell-es

Datenbank

1) In Terminal „mysql --help“ eingeben
2) Falls Optionen von MySQL angezeigt werden: bestens (mySQL ist installiert). 
Sonst mySQL installieren (Server und Client):

sudo apt-get install mysql-server mysql-client

3) Ebenfalls empfiehlt es sich sehr die MySQL-Workbench zu installieren. Dadurch 
kann die Konfiguration der Datenbank unter der Grafikoberfläche vorgenommen 
werden, was die Sache sehr vereinfacht:

sudo apt-get install mysql-workbench

(B) VSTENO herunterladen

Um VSTENO herunterladen zu können muss das Quellcode-Verwaltungssystem git 
installiert sein:
1) Terminal öffnen und „git help“ eingeben.
2) Wird die Hilfsseite für git angezeig: bestens (git ist installiert). Sonst git 
installieren:

sudo apt-get install git

VSTENO muss nun in Webserver-Pfad heruntergeladen werden. Standardmässig unter 
Trisquel (Debian) ist dies /var/www/html:
1) Wechseln Sie in dieses Verzeichnis:

cd /var/www/html

2) Klonen Sie nun VSTENO in dieses Verzeichnis:

sudo git clone https://github.com/marcelmaci/vsteno

Um die Sache etwas zu vereinfachen, empfiehlt es sich, den Owner und die 
Zugriffsrechte der Dateien anzupassen. 

sudo -i
cd /var/www/html/vsteno
chown -R user:user *
chmod -R ugo+rw *

Ersetzen Sie hierfür „user“ durch Ihren Usernamen/Benutzergruppe. Dadurch 
kann das Vorstellen von „sudo“ in allen folgenden Kommandozeilenbefehlen 
entfallen.

(C) Konfigurieren

Nun befinden sich alle Programme und Quelldateien auf dem Computer. Damit 
VSTENO nun läuft, müssen noch verschiedene manuelle Konfigurationen vor-
genommen und Daten (z.B. für die Stenografie-Systeme Deutsch und Spanisch) 
kopiert werden.

Datenbank einrichten

1) Starten Sie die MySQL-Workbench, z.B. via Terminal:

mysql-workbench &

2) Loggen Sie sich als Administrator in die automatisch generierte (standard) 
Datenbank ein (z.B. „root@localhost:3306“), indem Sie auf die MySQL-Connection 
klicken und anschliessend das Root-Passwort (Administratorpasswort für System-
verwaltung) eingeben. Wenn dies klappt, zeigt Ihnen MySQL-Workbench auf der 
linken Seite den Namen der Datenbank an (z.B. „phpgacl“).
3) Tragen Sie diese Werte nun in die PHP-Datei dbpw.php ein (dbpw = database 
password):

gedit /var/www/html/vsteno/php/dbpw.php

Zu ändern sind die vier Zeilen:

const db_servername = "127.0.0.1:3306";
const db_username = "root";
const db_password = "xxxxxxxxx";
const db_dbname = "phpgacl";

Ersetzen Sie das Passwort durch Ihr eigenes.

4) Legen Sie nun die benötigten Tables für VSTENO an, indem Sie die folgende 
Seite im Browser aufrufen (der PHP-Code legt die Tables automatisch an):

http://localhost/vsteno/php/init_db.php

Verwenden Sie das Passwort „11111111“.

Es sollten 8 Tables (users, models, 2x purgatorium/elysium/olympus) mit der 
Meldung „created successfully“ angelegt werden (sonst ist entweder die Daten-
bank nicht eingerichtet oder die Zugangsdaten stimmen nicht). Sie können nun 
überprüfen, ob die Tables angelegt wurden, indem Sie in MySQL auf die Daten-
bank „phpgacl“ klicken, „refresh all“ mit rechtem Mausklick wählen und Daten-
bank und Tables mit dem Dreieck öffnen (linker Mausklick). Angezeigt werden 
sollten Tables mit den Namen users, models, ZEDESSBAS/ZODESSBAS/ZPDESSBAS, 
ZESPSSBAS/ZOSPSSBAS/ZPSPSSBAS (DE = Deutsch, SP = Spanisch, E = Elysium, 
O = Olympus, P = Purgatorium).

(Fakultativ: Löschen Sie danach die Datei init_db.php:

rm init_db.php

Wenn Sie dies nicht tun, ist das nicht weiter schlimm. Zwar ist „11111111“ 
kein besonders sicheres Passwort ;), aber init_db.php kann keinen Schaden 
anrichten, auch wenn es ein zweites Mal – wenn die Tables bereits angelegt 
sind - aufgerufen wird.)

5) Legen Sie einen Nutzer „dummy“ an. Öffnen Sie hierfür in MySQL-Work-
bench links die Table „users“ => „columns“ (Klick auf Dreieck), wählen Sie 
alle Felder mit Shift-Pfeil (user_id, username, email etc.) und wählen Sie 
„select rows“ (Rechtsklick). Tragen Sie nun in den Feldern folgende Werte ein:

user_id: 1
username: dummy
salt: ghEybGJv
pw_hash: 8118f331c255a3b0fe66496f659182d4827cdbb47e4ee2daf481e7ab4391c7fd
privilege: 2

Die übrigen Felder können Sie frei lassen . Wählen Sie anschliessend 2x „apply“ 
(rechts unten) und „close“ (im Fenster, das sich öffnet).

6) Desaktivieren Sie das Fortune-Cookie im PHP-Quellcode.

gedit /var/www/html/vsteno/php/vsteno_template_top.php

Kommentieren Sie nun die beiden folgenden Zeilen aus, indem Sie // davorstellen:

//require_once "fortune.php";
//echo "<center>" . fortune() . "</center>";

Warum dies nötig ist: VSTENO generiert jeweils zu Beginn einen Glücks-Keks, 
indem es das deutsche Stenografie-System Stolze-Schrey anwendet. Da dieses 
System jedoch noch nicht installiert ist, würde VSTENO noch nicht funktionieren 
und wir können uns nicht einloggen, um VSTENO weiter zu konfigurieren.

7) Wir loggen uns nun erstmalig in VSTENO ein. Starten Sie hierfür die Ein-
leitungsseite von VSTENO, indem Sie im Webbrowser die folgende Adresse eingeben:

http://localhost/vsteno/php/introduction.php

Klicken Sie zum Anmelden auf „Einloggen“ und geben Sie die folgenden Daten ein:

Login: dummy
Passwort: 11111111

8) Gehen Sie nun zurück zu MySQL-Workbench und legen Sie ein leeres Modell für 
das deutsche Stenografie-System (DESSBAS). Wählen Sie hierfür auf der linken 
Seite „models“ => „columns“ (Linksklick auf Dreieck), markieren Sie alle 
Elemente (model_id, user_id etc.) mit Shift-Pfeiltasten und wählen Sie „select 
rows“ (Rechtsklick). Geben Sie nun folgende Werte ein:

model_id: 1
user_id: 99999
name: DESSBAS
header: header
font: font
rules: rules

Wählen Sie wiederum „apply“ (2x, gefolgt von „close“ im Fenster, das aufgeht). 
Sie haben nun ein leeres Datenfeld für das deutsche Stenografie-System angelegt, 
das wir im folgenden Schritt mit Daten befüllen.

9) Kehren Sie zurück zum eingeloggten User „dummy“ im Webbrowser und wählen Sie 
links „->Header“. Sie sollten nun ein Textfeld mit dem Wort „header“ sehen (das 
wir in Schritt 8 eingegeben haben). Öffnen Sie nun im Terminal die Definitionen 
für das deutsche System:

gedit /var/www/html/vsteno/ling/grundschrift_stolze_schrey_redesign.txt

Wählen Sie nun allen Text zwischen „#BeginSection(header)“ und 
„#EndSection(header)“ aus und kopieren Sie ihn in das Textfeld im Browser 
(Copy&Paste). Der ursprüngliche Text „header“ muss dabei vollständig durch den 
kopierten Text ersetzt werden (also „header“ vor dem Kopieren löschen). Klicken 
Sie zum Schluss auf den Button „speichern“ unterhalb des Textfeldes.

10) Wiederholen Sie das gleiche für „->Zeichen“ (Text zwischen 
„#BeginSection(font)“ und „#EndSection(font)“) und „->Regeln“ (Text zwischen 
„#BeginSection(rules)“ und „#EndSection(rules)“). Kontrollieren Sie zum 
Abschluss, dass in allen drei Textfeldern im Browser der kopierte Text 
erscheint.

11) Testen Sie, ob das deutsche Stenografie-System funktioniert. Schliessen Sie 
hierfür den Browser, starten Sie ihn erneut und geben Sie folgende Adresse ein:

http://localhost/vsteno/php/input.php

Geben Sie irgendwelchen Text ein (z.B. „Dies ist ein Test“) und klicken Sie 
unten auf „abschicken“. Sie sollten nun den Text in Steno geschrieben sehen.

12) Legen Sie nun ein richtiges Nutzerkonto (z.B. „realuser“) an, indem Sie 
links „Konto -> anlegen“ wählen (Sie müssen hierfür das Steno-Captcha lösen). 
Wenn das Konto erfolgreich angelegt werden kann (Textmeldungen im Browser 
beachten), sind Sie automatisch als neuer Nutzer „realuser“ eingeloggt.

13) Löschen Sie nun den dummy Nutzer und gewähren Sie „realuser“ Superuser-
Rechte. Kehren Sie hierfür zurück zu MySQL-Workbench, wählen Sie „tables“ => 
„users“ => „columns“ (Klick auf Dreieck), markieren Sie alle Spalten 
(Shift+Pfeil) und wählen Sie „select rows“ (Rechtsklick). Sie sollen nun den 
dummy-user und den neu angelegten „realuser“ sehen. Markieren Sie nun die 
ganze Zeile des dummy-users (Klick ganz links auf erste Spalte 1), wählen Sie 
oben das Tabellen-Symbol mit dem roten Minuszeichen (Zeile löschen) und klicken 
Sie anschliessen auf „apply“ (der dummy-user wird gelöscht). Wählen Sie an-
schliessend in der verbleibenden Zeile „realuser“ das Feld „privilege“ und 
ändern Sie den Wert von 1 auf 2 (schliessen Sie wiederum mit „apply“ ab, um den 
Wert in die Datenbank zu schreiben). Der Wert 2 bedeutet, dass „realuser“ nun 
Superuser-Rechte hat und ein weiteres Stenografie-System (Spanisch) anlegen 
kann.

(14) Bereiten Sie nun die Table für das spanische System vor, indem Sie links 
„models“ => „columns“ wählen und wiederum alle Spalten anzeigen lassen (mit 
Rechtsklick „select row“). Sie sehen nun 2 Modelle: „DESSBAS“ (das wir mit dem 
dummy-user angelegt hatten) und XM00000002 (das für realuser automatisch ange-
legt wurde). Erstellen Sie hier nun eine dritte Zeile mit den Werten:

model_id: 3
user_id: 99999
name: SPSSBAS
header: header
font: font
rules: rules

(15) Kehren Sie nun zum eingeloggten realuser im Browser zurück und ändern Sie 
das Modell auf Spanisch, indem Sie links unten auf den Knopf „standard“ 
klicken. Wählen Sie auf der folgenden Seite „Spanisch: Stolze-Schrey Grund-
schrift (SPSSBAS)“ und klicken Sie auf „wählen“.

(16) Öffnen Sie nun im Terminal die Definitionen des spanischen System:

gedit /var/www/html/vsteno/ling/SPSSBAS.txt

Kopieren Sie nun die Definitionen wie in Schritten 9 und 10 beschrieben (simples 
Copy&Paste vom Texteditor in den Browser, „speichern“ wählen). Vergewissern Sie 
sich vor dem Kopieren, dass das Browser-Textfeld leer ist (Platzhalterwörter 
„header“, „font“, „rules“ aus Datenbank vorgängig löschen).

(17) Testen Sie, ob das spanische System funktioniert, indem links „Maxi“ 
wählen, anschliessend einen Text eingeben (z.B. „Un ejemplo en español.“), unter 
„Engine“ Spanisch wählen und ganz unten auf „abschicken“ klicken. Sie sollten 
nun den eingegebenen Text als spanisches Stenogramm sehen.

18) Wenn all dies funktioniert ;), können Sie die „Glückskekse“ (fortune 
cookies) wieder aktivieren:

gedit /var/www/html/vsteno/php/vsteno_template_top.php

Entfernen Sie zuvor hinzugefügten // wieder aus den beiden Zeilen:

require_once "fortune.php";
echo "<center>" . fortune() . "</center>";

Schliessen Sie den Browser, starten Sie ihn anschliessend neu und geben Sie die 
folgende Seite ein:

http://localhost/vsteno/php/introduction.php

Rechts oben sollte nun ein Glückskeks erscheinen.
</pre>




<?php require "vsteno_template_bottom.php"; ?>
