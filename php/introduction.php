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
<i><p><b><u>9. Juni 2019</u></b><br>
Es ist wieder einiges geschehen rund um VSTENO:
<ul>
<li><p>
<b>Spanish</b>: Erstmals ist es möglich, Stenogramme in einer anderen Sprache zu generieren! Die Wahl fiel auf Spanisch, weil dies 
eine sehr regelmässige Sprache mit wenigen Unterschieden zwischen Laut und Schrift ist. Die Implementierung ist zur 
Zeit natürlich noch alles andere als perfekt und fehlerfrei. Wie immer handelt es sich um einen ersten Wurf, der sich im Laufe der nächsten
Wochen und Monate verbessern wird. Die ersten Resultate sind jedoch sehr vielversprechend - werfen Sie doch einen Blick auf den <a href="../docs/donquijote1.pdf">Preview</a>!
</p>
</li>
<li><p>
<b>Installation</b>: Nachdem der letzte Upload reichlich schief lief, hatte ich das unaussprechliche Vergnügen, stundenlang nach dem Fehler zu
suchen - und unter anderem eine komplette Neuinstallation von VSTENO vorzunehmen. Immerhin bot dies Gelegenheit, aus der Not eine Tugend zu machen, und
diesen Prozess minutiös zu dokumentieren. Daraus resultierte also die folgende <a href="installation.php">Installationsanleitung</a>. Nerds, die sich das gerne antun wollen, können diese gerne
konsultieren ... ;-)</p>
</li>
<li><p>
<b>Dokumentation</b>: Die <a href="../docs/documentation_v01rc.pdf">Hauptdokumentation</a> wurde in verschiedenen Punkten ergänzt (neue Funktionalitäten wie wechseln zwischen
verschiedenen Stenografie-Systemen, neues Abstandskonzept für Zeichen und zusätzliche Lizenzierungshinweise). Ebenfalls noch (mehr
oder minder) aktuell sind: <a href="../docs/gel_speiende_spiegel.pdf">Gel speiende Spiegel</a> und <a href="../docs/stenoengines.pdf">STENO-ENGINES</a>. 
</p>
</li>
<li><b>Mitarbeit</b>: Das Dokument <a href="../docs/mitmachen_bei_vsteno.pdf">Mitmachen</a> ist nur noch teilweise gültig. Insbesondere bin ich eher
wieder davon abgekommen, Wortkorrekturen via Datenbank vorzunehmen. Eine bewährtere Form der Mitarbeit ist die direkte Korrektur von Stenogrammen
auf dem Papier (und die Integrierung der Korrekturen als Transkriptions-Regeln). Falls jemand Kenntnisse - insbesondere im Spanisch - hat, darf er/sie
sich gerne melden! Ebenfalls biete ich nach wie vor gerne Hilfestellung, falls jemand gerne ein weiteres (eigenes) Stenografie-System mit VSTENO
umsetzen möchte.
</p>
</li>
</ul>
<p>Nach wie vor ist unklar, wann die erste offizielle Version 0.1 spruchreif ist (die Schätzung liegt zwischen "bald" und "nie";) und in welcher Form sie publiziert
werden soll (z.B. Installation ab GIT-Archiv oder ZIP-Datei). So oder so: Mit der erwähnten <a href="installation.php">Installationsanleitung</a> kann bereits jetzt jede/r
Interessierte im Sinne eines "rolling release" Modells eine lokale Instanz von VSTENO installieren.</p>
<p>In diesem Sinne wünsche ich frohes Stenografieren!</p>

<center>* * *</center>
<p>=> Hier finden Sie <a href="old_news.php">ältere News-Einträge</a>.
<br>

<?php require "vsteno_template_bottom.php"; ?>
