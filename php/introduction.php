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
<p><h2>Neuigkeiten</h2>
<i><p><b><u>16. April 2019</u><br><br>Lets fetz - 1 Jahr VSTENO!</b><br><br>
Zugegeben: Es ist nicht gerade die goldene Hochzeit ... noch nicht mal die silberne. Dennoch: Es gibt Grund zum Feiern! Ziemlich genau 1 Jahr ist
her, dass die erste Codezeile für VSTENO entstand. Die früheste "archäologisch auffindbare" Datei datiert vom 10. April 2018: Sie legte 
zwei aneinander anschliessende Bezierkurven durch 3 beliebige Punkte. Dieser Algorithmus der so genannten Splines (<i>splines interpolation</i>)
bildet bis heute das Kernstück von VSTENO.</p>
<p><b>Zeit somit für eine kurze Bilanz und einige Eckdaten zu VSTENO.</b></p>
</li>
<ul>
<li><p><b>Code:</b> VSTENO besteht inzwischen aus rund 10'500 Zeilen PHP-Code und 5000 Zeilen JavaScript (VPAINT). Das Gesamtprogramm enthält
zusätzlich die Bibliotheken phpSyllable und PAPERS.JS, die im Rahmen freier Lizenzen integriert wurden. Insgesamt wurden in 220 Commits 
429'994 Zeilen hinzugefügt und 21'841 Zeilen gelöscht.</li>

<li><p><b>Dokumentation</b>: Nebst der Webseite (die etwa 800 Zeilen HTML/CSS-Code enthält) wurden mehrere Dokumentationen erstellt. Im
Hinblick auf die erste offizielle Version 0.1 wurde eine <a href="../docs/documentation_v01rc.pdf">Gesamtdokumentation</a> begonnen, 
die mittlerweile 65 Seiten umfasst.</b> 
</li>
<li><b>Stolze-Schrey</b>: Die Datei mit den Definitionen für die Grundschrift Stolze-Schrey umfasst inzwischen 2343 Zeilen. Darin enthalten
sind etwa 1400 Regeln und 360 Zeichen (wovon 200 Grundzeichen, 70 kombinierte Zeichen und 90 verschobene Zeichen).</p></li>

<li><b>Datenbanken</b>: Die Datenbanken (Purgatorium, Olympus, Elysium) von VSTENO sind nach wie vor leer - und das ist gut so! Das Programm
soll möglichst weit gehend in der Lage sein, Stenogramme anhand von Regeln zu berechnen (die Gruppe an "Ausnahmen" soll also klein gehalten
werden).
</b></p></li>
<li><b>Performance</b>: Die zunehmende Komplexität von VSTENO fordert ihren Preis: Die Berechnung einer Stenoseite dauert nun im Schnitt ca.
20 Sekunden. Der Server schafft im Moment maximal rund 20 Seiten aufs Mal. Die Hauptrechenzeit wird nicht zur Generierung der Stenogramme, sondern
für die linguistische Analyse verwendet (diese benötigt rund 80% der Rechenzeit*).
</b></p></li>
<li><b>Zeit</b>: Rein auf der Informatikseite flossen rund 740 Stunden Entwicklungszeit in VSTENO. Hinzuzurechnen sind ferner Handkorrekturen, 
welche vor allem zwei "gute Seelen" (Mitglieder des Schweizerischen Stenografenverbands) auf Papier vornahmen, um das Schriftbild zu 
verbessern (diese Arbeit war zum Teil sehr detailreich, professionell und hat wesentlich zur Verbesserung von VSTENO beigetragen).
</b></p></li>
<li><b>Finanziell</b>: Im Laufe des Jahres gingen zwei Spenden in der Höhe von 220.- CHF ein. Damit können ungefähr die Infrastrukturkosten 
für das vergangene und das kommende Jahr gedeckt werden (15.- CHF für die Internet-Adresse und ca. 100.- für den Server).</p></li>
</ul>
</p>
</i>
<p>Ganz herzlich bedanken möchte ich mich am Ende des ersten Jahres beim <a href="http://www.steno.ch">Schweizer Stenografenverband</a>, 
einerseits beim Verband (der dem Projekt von Anfang an sehr offen gegenüberstand und -steht), andererseits aber auch bei den erwähnten 
Mitgliedern, die durch Korrekturen und Rückmeldungen zum jetztigen Stand beigetragen haben. Dieses Interesse - und die Zuversicht, 
dass VSTENO dereinst (wenn es dann mal "fertig"** ist;-) auch tatsächlich von Stenograf/innen genutzt wird, waren ein sehr willkommener 
Motivationsschub!
<br>
<p>Zum Abschluss wiederum ein kleiner Teaser: ein <a href="../docs/matura93_teaser.pdf">Artikel</a>, der nach meiner letzten Info, auch in den 
Titlis-Grüssen erschien. Dazu klar die Anmerkung: Nach wie vor sind weder sämtliche Stenogramme korrekt, noch brilliert VSTENO durch eine
besonders ästhetische Klaue ... ;-)
</p>
<i><p><b>*</b> Wer wissen möchte, wie schnell VSTENO ohne linguistische Analyse lief, kann einen Blick auf die
<a href="http://www.purelab-tefc.ch/test/input.php">cling on - Klingon</a>-Version vom September 2018 werfen;-).<br>
<b>**</b> ... es dürfte zwischenzeitlich wohl klar sein, dass ein Projekt wie VSTENO niemals "fertig" (sondern - wie Valserwasser ... im besten Fall - 
ständig besser) wird ... ;-)</p></i>
<p><center>* * *</center></p>
<p>=> Hier finden Sie <a href="old_news.php">ältere News-Einträge</a>.
<br>

<?php require "vsteno_template_bottom.php"; ?>
