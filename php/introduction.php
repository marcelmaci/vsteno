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
möglichst vollständig dokumentiert werden. <b>[Update 20.02.19: <a href="../docs/documentation_v01rc.pdf">Dokumentation 0.1rc</a>]</b></p></li>
<li><b>Zeitplan</b>: Es war von Frühling bis Sommer 2019 die Rede - es wird wohl eher Sommer ... ;-)</p></li>
</ul>
</p>
</i>
<p><h2>Teaser</h2>
<p>Und hier abschliessend noch ein kleiner "Teaser" - zwei PDFs, die zeigen, wie die von VSTENO generierten Stenogramme inzwischen
aussehen (die Texte stammen vom <a href="http://gutenberg.spiegel.de">Projekt Gutenberg</a> und sind gemeinfrei):<p>
<ul>
<li>Friedrich Glauser, Matto regiert: <a href="../docs/mattoregiert_notwendige_vorrede.pdf">Notwendige Vorrede</a></li>
<li>Friedrich Glauser, Matto regiert: <a href="../docs/mattoregiert_kap2_auszug_gruen_dicker.pdf">Kapitel 2 (Auszug)</a></li>
</ul>
<p><center>* * *</center></p>
<p>=> Hier finden Sie <a href="old_news.php">ältere News-Einträge</a>.
<br>
<?php require "vsteno_template_bottom.php"; ?>
