<?php require "vsteno_template_top.php"; ?>
<h1>Old News</h1>
<p>Hier finden Sie archivierte, ältere News-Einträge.</p>
<i><p><u><b>15. Februar 2019:</b></u><br>Letzten Samstag hatte ich die charmante Gelegenheit, zwei Mitglieder des <a href="http://www.steno.ch">
Schweizerischen Stenografenverbandes</a> persönlich zu treffen. Der Austausch war sehr anregend und so flossen bereits diese Woche einige
wichtige Inputs in das Projekt ein. Für die kommenden Monate hoffe ich, dass sich VSTENO unter dem ebenso versierten wie wohlwollenden Auge dieser
Spezialist/innen zu einem soliden Werkzeug weiterentwickelt, das auch professionellen, stenografischen Ansprüchen gerecht werden kann.</li>
<p><b><u>Ziele:</u></b><br>Geplante Verbesserungen in nächster Zeit sind:
<ul>
<li><p><b>Schrift:</b> Nun, da das Programm <a href="../js/vsteno_editor.html">VPAINT</a> so weit fortgeschritten ist, dass es die abstrakten Zahlenreihen der Zeichendefinitionen
visuell und interaktiv greifbar macht, soll die Grundschrift Stolze-Schrey komplett überarbeitet werden. Ziele: (1) schönere Zeichen  und 
(2) präzisere Abstände zwischen den Zeichen. Letzteres ist übrigens alles andere als trivial, gibt es doch in Stolze-Schrey aufgrund
der verschiedenen Zeichentypen (Zeichenhöhe, spitze Zeichen, Zeichen mit Bogen etc.) sowie Hoch-/Tiefstellung und Eng-/Weitschreibung weit über
15000 mögliche Zeichenkombinationen, die aus Effizienzgründen nun in einem System von Zeichengruppen erfasst und berechnet werden sollen.</p></li>
<li><p><b>Datenbank:</b> Ausnahmen - d.h. Wörter, die abweichend von den Grundregeln geschrieben werden - und zusammengesetzte Wörter können
bereits seit Anfang November zuverlässig in einer Datenbank erfasst werden. Alles, was es hierfür braucht ist ein Benutzerkonto. Wer gerne mitmachen
und mithelfen möchte, das Programm zu verbessern, findet in dieser <a href="../docs/mitmachen_bei_vsteno.pdf">Dokumentation</a> die entsprechenden
Informationen. 
<li><p><b>Versionen:</b> Bereits früher wurde die Versionsnummern 0.1rc (rc = release candidate) für die steno engine SE1 und die Version 
0.2rc für die SE2 definiert. Im Prinzip war geplant, die SE1 später vollständig durch die SE2 zu ersetzen. Allerdings zeigt sich nach 
rund 3 Monaten Entwicklung, (1) dass die Fertigstellung der SE2 noch in weiter Ferne liegt und dass (2) der Vorteil der SE2 gerade darin besteht, dass
sie sehr effizient und bereits fertig (Beta-Stadium) ist. Um gewisse Einschränkungen der SE1 aufzuheben, war dann geplant, die wichtigsten
Funktionen der SE2 auf die SE1 rückzuportieren. Die SE1 sollte also zwei komplett rückwärtskompatible Versionen vereinen: die ursprüngliche 
SE1 rev0 (rev0 = revision 0) und die erweiterte SE1 rev1. Allerdings zeigte sich letztlich, dass die SE1 rev1 das Programm - aufgrund 
unvermeidbarer Programmierfehler, die erneut gefunden und ausgemerzt werden müssten - wieder in ein Alpha-Stadium zurückwerfen würde. Deshalb 
wird ab jetzt die SE2 und die SE1 rev1 konsequent auf Eis gelegt. Ziel ist somit die Umsetzung von Version 0.1 als SE1 rev0. Weitere Infos 
zu den steno engines sind <a href="../docs/stenoengines.pdf">STENO-ENGINES</a> hier erläutert.
</li>
<li><b>Dokumentation</b>: Da die SE1 rev0 nun als vollwertige Version 0.1 geplant ist, soll sie in den kommenden Monaten auch sauber und
möglichst vollständig dokumentiert werden. <b>[Update 20.02.19: <a href="../docs/documentation_v01rc.pdf">Dokumentation 0.1rc</a> / 
05.03.19: <a href="../docs/gel_speiende_spiegel.pdf">Gel speiende Spiegel</a> (Linguistische Analyse von VSTENO)]</b></p></li>
<li><b>Zeitplan</b>: Es war von Frühling bis Sommer 2019 die Rede - es wird wohl eher Sommer ... ;-)</p></li>
</ul>
</p>
</i>
<p><h2>Teaser</h2>
<p>Und hier abschliessend noch ein kleiner "Teaser" - zwei PDFs, die zeigen, wie die von VSTENO generierten Stenogramme inzwischen
aussehen (die Texte stammen vom <a href="http://gutenberg.spiegel.de">Projekt Gutenberg</a> und sind gemeinfrei):<p>
<ul>
<li>Friedrich Glauser, Matto regiert: <a href="../docs/mattoregiert_notwendige_vorrede.pdf">Notwendige Vorrede</a> <b>[<a href="../docs/matto_regiert_kap1_vers2.pdf">Version2 (05.03.19)</a>]</b></li>
<li>Friedrich Glauser, Matto regiert: <a href="../docs/mattoregiert_kap2_auszug_gruen_dicker.pdf">Kapitel 2 (Auszug)</a> <b>[<a href="../docs/matto_regiert_kap2_ausschnitt_vers2.pdf">Version2 (05.03.19)</a>]</b></li>
</ul>
<p><center>* * *</center></p>
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
