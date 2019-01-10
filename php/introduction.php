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
<i><p><u><b>1. November 2018:</b></u><br>Das Herzstück von VSTENO, d.h. Datenbank, Parser und eine Steno-Engine, ist im Rohbau fertig. Dies bedeutet, dass im Prinzip alle unabdingbaren 
("must-have") Funktionalitäten implementiert sind und nun eigene Stenografiesysteme (Zeichen und Übertragungsregeln) und dazu gehörige Wörterbücher direkt online erstellt und 
verwendet werden können.</p> 
<p><b><u>Ziele:</u></b><br>In den nächsten Wochen und Monaten sollen folgende Ziele erreicht werden:
<ul><li><p><b>Stolze-Schrey:</b> Das System Stolze-Schrey (Grundschrift), welches VSTENO standardmässig verwendet, soll mit externen Interessierten weiter verfeinert und verbessert werden. Als 
Leitfaden hierfür dient <a href="../docs/mitmachen_bei_vsteno.pdf">diese Dokumentation</a>. Verbessert werden sollen einerseits die <a href="../ling/stolze_schrey_grundschrift.txt">Regeln</a>, 
andererseits die Datenbankeinträge für unregelmässige Stenogramme.</p></li>
<li><p><b>Testing:</b> Parallel dazu soll das Programm auf Fehler geprüft werden. Im Hinblick auf eine fehlerfreie Version und eine offizielle Release zwischen Frühjahr und Sommer 2019, wird
die aktuelle Version als 0.1rc alpha deklariert (rc = release candidate, alpha = frühes Teststadium).</li>
<li><b>Weiterentwicklung</b>: Im Prinzip werden in die Version 0.1rc alpha keine neuen Funktionalitäten mehr integriert. Einzige Ausnahmen sind: das Fertigstellen (soweit möglich) von 
Funktionen, die z.T. erst rudimentär implementiert wurden und Bugfixes im Rahmen des Testings.</p></li>
<li><p><b>Zeicheneditor:</b> Parallel zum Testing der Version 0.1 wird ein graphischer Zeicheneditor (basierend auf <a href="http://www.paperjs.org">paper.js</a>) entwickelt, der es ermöglichen 
soll, schönere und einfachere Stenozeichen zu generieren. 
Dafür wird ein vollkommen neuer Ansatz für das Zeichnen der Stenozeichen verfolgt, der den kompletten Austausch der Steno-Engine bedingt. Diese etwas grössere und kompliziertere "Operation 
am offenen Herzen" ist erst für Version 0.2 geplant. Einen Einblick in den aktuellen Entwicklungsstand des Zeicheneditors gibt es <a href="../js/vsteno_editor.html">hier</a>. <b>(Ergänzungen: 8.12.18: <a href="../docs/stenoengines.pdf">STENO-ENGINES</a> / 10.01.19: <a href="../php/export_se1data_to_editor.php">Editor SE1</a>).</b></p></li>
</ul>
</p>
<p>Nicht angestrebt wird im Moment die Umsetzung anderer Stenografie-Systeme. Ebenfalls zurückgestellt wird die Dokumentation der Version 0.1: Wer bereits mit dieser Version ein eigenes 
Stenografie-System umsetzen möchte, darf sich gerne direkt an mich wenden (ich helfe gerne mit den nötigen Infos weiter). Im Hinblick auf die Version 0.2 und die dafür geplante, komplette 
Überarbeitung der Steno-Engine, macht es aber mehr Sinn, das Programm erst dann vollumfänglich zu dokumentieren. 
</p>
</i>
<br>
<?php require "vsteno_template_bottom.php"; ?>
