<?php require "vsteno_template_top.php"; ?>
<h1>Old News</h1>
<p>Hier finden Sie archivierte, ältere News-Einträge.</p>
<i><p><b><u>16. April 2019</u><br><br>Lets fetz - 1 Jahr VSTENO!</b><br><br>
Zugegeben: Es ist nicht gerade die goldene Hochzeit ... noch nicht mal die silberne. Dennoch: Es gibt Grund zum Feiern! Ziemlich genau 1 Jahr ist
her, dass die erste Codezeile für VSTENO entstand. Die früheste "archäologisch auffindbare" Datei datiert vom 10. April 2018: Sie legte 
zwei aneinander anschliessende Bezierkurven durch 3 beliebige Punkte. Dieser Algorithmus der so genannten Splines (<i>splines interpolation</i>)
bildet bis heute das Kernstück von VSTENO.</p>
<p><b>Zeit somit für eine kurze Bilanz und einige Eckdaten zu VSTENO.</b></p>

<ul>
<li><p><b>Code:</b> VSTENO besteht inzwischen aus rund 10'500 Zeilen PHP-Code und 5000 Zeilen JavaScript (VPAINT). Das Gesamtprogramm enthält
zusätzlich die Bibliotheken phpSyllable und PAPERS.JS, die im Rahmen freier Lizenzen integriert wurden. Insgesamt wurden in 220 Commits 
429'994 Zeilen hinzugefügt und 21'841 Zeilen gelöscht.</li>

<li><p><b>Dokumentation</b>: Nebst der Webseite (die etwa 800 Zeilen HTML/CSS-Code enthält) wurden mehrere Dokumentationen erstellt. Im
Hinblick auf die erste offizielle Version 0.1 wurde eine <a href="../docs/documentation_v01rc.pdf">Gesamtdokumentation</a> begonnen, 
die mittlerweile 65 Seiten umfasst.
</li>
<li><b>Stolze-Schrey</b>: Die Datei mit den Definitionen für die Grundschrift Stolze-Schrey umfasst inzwischen 2343 Zeilen. Darin enthalten
sind etwa 1400 Regeln und 360 Zeichen (wovon 200 Grundzeichen, 70 kombinierte Zeichen und 90 verschobene Zeichen).</li>

<li><b>Datenbanken</b>: Die Datenbanken (Purgatorium, Olympus, Elysium) von VSTENO sind nach wie vor leer - und das ist gut so! Das Programm
soll möglichst weit gehend in der Lage sein, Stenogramme anhand von Regeln zu berechnen (die Gruppe an "Ausnahmen" soll also klein gehalten
werden).
</li>
<li><b>Performance</b>: Die zunehmende Komplexität von VSTENO fordert ihren Preis: Die Berechnung einer Stenoseite dauert nun im Schnitt ca.
20 Sekunden. Der Server schafft im Moment maximal rund 20 Seiten aufs Mal. Die Hauptrechenzeit wird nicht zur Generierung der Stenogramme, sondern
für die linguistische Analyse verwendet (diese benötigt rund 80% der Rechenzeit*).
</li>
<li><b>Zeit</b>: Rein auf der Informatikseite flossen rund 740 Stunden Entwicklungszeit in VSTENO. Hinzuzurechnen sind ferner Handkorrekturen, 
welche vor allem zwei "gute Seelen" (Mitglieder des Schweizerischen Stenografenverbands) auf Papier vornahmen, um das Schriftbild zu 
verbessern (diese Arbeit war zum Teil sehr detailreich, professionell und hat wesentlich zur Verbesserung von VSTENO beigetragen).
</li>
<li><b>Finanziell</b>: Im Laufe des Jahres gingen zwei Spenden in der Höhe von 220.- CHF ein. Damit können ungefähr die Infrastrukturkosten 
für das vergangene und das kommende Jahr gedeckt werden (15.- CHF für die Internet-Adresse und ca. 100.- für den Server).</li>
</ul>
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
<center>* * *</center>

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
