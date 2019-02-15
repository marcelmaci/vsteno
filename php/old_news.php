<?php require "vsteno_template_top.php"; ?>
<h1>Old News</h1>
<p>Hier finden Sie archivierte, ältere News-Einträge.</p>
<br>
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
am offenen Herzen" ist erst für Version 0.2 geplant. Einen Einblick in den aktuellen Entwicklungsstand des Zeicheneditors gibt es <a href="../js/vsteno_editor.html">hier</a>. <b>(Ergänzungen: 
8.12.18: <a href="../docs/stenoengines.pdf">STENO-ENGINES</a> / 19.01.19:</b> Editor SE1 verfügbar als <b><a href="../php/export_se1data_to_editor.php">VPAINT</a></b> - siehe 
<a href="https://github.com/marcelmaci/vsteno/blob/master/readme.txt">readme</a>).</p></li>
</ul>
</p>
<p>Nicht angestrebt wird im Moment die Umsetzung anderer Stenografie-Systeme. Ebenfalls zurückgestellt wird die Dokumentation der Version 0.1: Wer bereits mit dieser Version ein eigenes 
Stenografie-System umsetzen möchte, darf sich gerne direkt an mich wenden (ich helfe gerne mit den nötigen Infos weiter). Im Hinblick auf die Version 0.2 und die dafür geplante, komplette 
Überarbeitung der Steno-Engine, macht es aber mehr Sinn, das Programm erst dann vollumfänglich zu dokumentieren. 
</p>
</i>
<?php require "vsteno_template_bottom.php"; ?>
