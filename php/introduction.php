<?php require "vsteno_template_top.php"; ?>
<h1>Einführung</h1>
<p>Herzlich willkommen bei VSTENO!</p>
<p>Der Name VSTENO ist ein Acronym und bedeutet "Vector Shorthand Tool with Enhanced Notational Options", frei übersetzt also in etwa 
"vektorbasiertes Kurzschrift-Werkzeug mit verbesserten Darstellungsoptionen". VSTENO wurde dazu entwickelt, normale Langschrift-Texte im ASCII-Format automatisch in Stenogramme zu übertragen. 
Dadurch wird es also im Prinzip möglich, jedweden elektronisch vorliegenden Text in Steno zu lesen oder eigene Webseiten mit automatisch generierten Stenogrammen zu gestalten.</p>
<h2>System</h2>
<p>
VSTENO verwendet das deutsche Stenografie-System Stolze-Schrey in der Grundschrift (vgl. hierzu die <a href="http://www.steno.ch/0/images/lehrmittel/systemurkunde.pdf">Systemurkunde</a> des 
Schweizerischen Stenografenverbands). Das Programm wurde jedoch von Anfang an so angelegt, dass es nicht an ein bestimmtes System gebunden ist und somit auch eigene Zeichen und Regeln für andere
Stenografie-Systeme definiert werden können.
</p>
<h2>Lizenz</h2>
<p>VSTENO ist Freie Software, d.h. das Programm darf also kopiert, weitergegeben und verändert werden. Bitte beachten Sie weitere Hinweise unter <a href="copyright.php">Copyright</a> und <a href="donation.php">Spende</a>.
</p>
<p><h1>Neuigkeiten</h1>
<p><b><u>25/07/19:</u><br>Here we are ... ! :)</b></p>
<p>Nach 15 Monaten und rund 950 Entwicklungsstunden* freue ich mich, 
heute die erste offizielle Version namens <a href="https://de.wikipedia.org/wiki/Hephaistos"><b>HEPHAISTOS</b></a> zu präsentieren!
Der sagenumwobene Schmied - zweifellos Kind einer humanistischen Tradition und hoffentlich der erste aus dem Geschlecht einer Reihe edler Olympier, 
die folgen - wartet mit 
folgenden Goodies auf:<p>
<ul>
<li><p><b>Programm</b>:<br>
- komplett funktionsfähige** SE1rev0 (= steno engine revision 0) <br>
- automatisierte <a href="installation.php">Installation</a> unter der GNU/Linux-Distribution <a href="http://www.trisquel.info">Trisquel 8</a>***</p></li>
<li><p><b>Dokumentation</b>:<br>
- vollständige <a href="../docs/documentation_v01.pdf">Hauptdokumentation</a> der aktuellen Version<br>
- aktualisierte <a href="documentation.php">Zusammenstellung</a> sämtlicher verfügbarer Dokumentationen</p></li>
<li><p><b>Stenografie</b>: <br>
<u>drei Modelle (Stolze/Schrey):</u><br>
- <a href="https://github.com/marcelmaci/vsteno/blob/master/ling/DESSBAS.txt">Deutsch</a> (V1.0): als Muttersystem (>95% korrekte Stenogramme)<br>
- <a href="https://github.com/marcelmaci/vsteno/blob/master/ling/SPSSBAS.txt">Spanisch</a> (V0.2): Beispiel eines schriftbasierten Systems (Betastatus) <br>
- <a href="https://github.com/marcelmaci/vsteno/blob/master/ling/FRSSBAS.txt">Französisch</a> (V0.1): Beispiel eines lautbasierten/phonetischen Systems (Alphastatus) 
</p></li>
</ul>
<p>Detailliertere Release-Notes zu den einzelnen Modellen können direkt im Eingabeformular via den Link <a href='model_info.php'>Info</a> 
abgerufen werden (das Modell muss vorselektiert werden).</p>

<p><i>
*) die unleugbar auch die eine oder andere Durststrecke enthielten ... :)<br>
**) oder zumindest bereinigte und mehr oder minder getestete ... ;-)<br>
***) andere Distributionen oder Betriebssysteme sind möglich, bedingen aber manuelle Anpassungen.
</i></p>

<h1>Ausblick</h1>
<p>Quo vadis VSTENO ... ? Diese Frage stellt sich mir mehr und mehr. Das Programm hat inzwischen einen Umfang erreicht, der es praktisch 
unmöglich macht, das Projekt als Einzelperson weiterzuführen. Einmal mehr möchte ich an dieser Stelle somit auch Sie zur 
<a href="mailto:m.maci@gmx.ch">Mitarbeit</a> an VSTENO 
einladen - und betone noch einmal, dass man kein/e Programmierer/in sein muss, um sich am Projekt zu beteiligen.</p>
<p><b>Beispiele</b></p>
<ul>
<li><p><b>Stenografische Systeme</b>: Stenografische Systeme können nur getestet werden, wenn anhand von Beispieltexten (Artikel, Kurzgeschichten, 
Romane) Fehler erkannt und korrigiert werden. Sprich: Wenn Sie ein stenografisches System beherrschen und bereit sind, stenografische Texte
Korrektur zu lesen, dann melden Sie sich doch!</p></li>
<li><p><b>Dokumentation</b>: Als Entwickler von VSTENO liegt mir daran, möglichst viel Zeit in die Programmierung zu investieren, was dazu führt, dass
ich praktisch keine Zeit habe, Dokumenationen gegenzulesen (und diese z.B. voller Tippfehler sind). Auch hier - oder zum Beispiel um 
Dokumentationen zu übersetzen (Englisch z.B. wäre wesentlich) - ist jede Unterstützung willkommen!</p></li>
<li><p><b>Werbung</b>: Ebenfalls fehlt mir die Zeit, Stenografenvereine zu kontaktieren und Werbung für das Programm zu machen. Deshalb: Wenn Sie 
die Möglichkeit haben, in Ihrem (stenografischen) Bekanntenkreis auf das Programm hinzuweisen, so leisten Sie auch damit einen Beitrag dazu, 
dass VSTENO bekannter wird (und so in Zukunft vielleicht auf mehr Unterstützung zählen kann)!</p></li>
<li><p><b>DEK, Gregg, Pitmann, Duployé, Stiefografie ... </b>: Schreiben Sie ein anderes stenografisches System als Stolze/Schrey? Dann 
<a href="mailto:m.maci@gmx.ch">melden 
Sie sich</a>! Mit VSTENO lässt sich im Prinzip jedes beliebige System in relativ kurzer Zeit umsetzen.</p></li>
</ul>
<h2>Danke</h2>
<p>Mit der Release von VSTENO V0.1 Hephaistos bedanke ich mich ganz offiziell bei Frau Yvonne Reith vom Schweizerischen 
Stenografenverband, welche seit Februar unzählige Rückmeldungen zu Korrektheit und Schriftbild gemacht und damit einen 
wesentlichen Beitrag dazu geleistet hat, dass auf Deutsch inzwischen über 95% der Stenogramme korrekt generiert werden!</p> 
<p>Ebenfalls bedanke ich mich beim <a href="http://www.steno.ch">Schweizerischen Stenografenverband</a> für die Offenheit und 
Unterstützung gegenüber dem Projekt.</p>
<center>* * *</center>
<p>=> Hier finden Sie <a href="old_news.php">ältere News-Einträge</a>.
<br>

<?php require "vsteno_template_bottom.php"; ?>
