<?php require "vsteno_template_top.php"; ?>
<h1>Einführung</h1>
<p>Herzlich willkommen bei VSTENO!</p>
<p>Der Name VSTENO ist ein Acronym und bedeutet "Vector Shorthand Tool with Enhanced Notational Options", frei übersetzt also in etwa 
"vektorbasiertes Kurzschrift-Werkzeug mit erweiterten Darstellungsoptionen". VSTENO wurde dazu entwickelt, normale Langschrift-Texte im ASCII-Format automatisch in Stenogramme zu übertragen. 
Dadurch wird es also im Prinzip möglich, jedweden elektronisch vorliegenden Text in Steno zu lesen oder eigene Webseiten mit automatisch generierten Stenogrammen zu gestalten.</p>
<h2>System</h2>
<p>
VSTENO verwendet im Moment das Stenografie-System Stolze/Schrey (Deutsch, Spanisch, Französisch, Englisch) wie z.B. in der <a href="http://www.steno.ch/0/images/lehrmittel/systemurkunde.pdf">Systemurkunde</a>
(Schweizerischer Stenografenverband SSV) dargelegt. Das Programm wurde jedoch von Anfang an so angelegt, dass es nicht an ein bestimmtes System gebunden ist und somit auch eigene Zeichen und Regeln für andere (im Prinzip beliebige) 
Stenografie-Systeme definiert werden können.
</p>
<h2>Lizenz</h2>
<p>VSTENO ist Freie Software, d.h. das Programm darf also kopiert, weitergegeben und verändert werden. Bitte beachten Sie weitere Hinweise unter <a href="copyright.php">Copyright</a> und <a href="donation.php">Spende</a>.
</p>
<center>* * *</center>
<h1>Neuigkeiten</h1>

<p><b>20/03/20</b><p>
<p><h2>Der Titan ist da ... :)</h2></p>
<p>Mitte April werden es zwei Jahre sein, das ich an VSTENO arbeite. Rund 1300 Stunden sind inzwischen in dieses Programm geflossen -
ein eigentliches Mammutprojekt, das mir zwischendurch (vor allem wenn es nicht lief ... ;-) doch einiges abverlangte!</p>
<p>Umso mehr freue ich mich,
hier nun die Version 0.3 namens <a href="https://de.wikipedia.org/wiki/Hyperion_(Titan)">Hyperion</a> vorzustellen.</p>

<p><h2>Highlights</h2></p>
<p>Aus meiner Sicht gibt es vor allem drei wichtige Neuerungen:</p>
<ul>
<li><p><b>Eilschrift:</b> Im deutschen System wurden die Regeln der <a href="https://de.wikipedia.org/wiki/Stolze-Schrey">Eilschrift</a> 
integriert und zwar so, dass sie nun 
via Optionen im Eingabeformular zu- oder abschaltbar sind (sodass flexibel zwischen Verkehrs- und Eilschrift gewechselt werden kann). 
<br><i>=> Hanspeter Frech, <a href="http://www.steno.ch/0/index.php/lehrmittel">"Die superschnelle Geheimschrift. Gekürzte Stenografie"</a>, 
Schweizerischer Stenografenverband Stolze/Schrey (SSV), Wetzikon, November 2017 (Band 2).</p></li>
<li><p><b>Französisch:</b> Die Grundschrift wurde mit realen Texten getestet und viele Fehler korrigiert. Die Qualität ist inzwischen so gut, 
dass ganze Bücher problemlos gelesen werden können (<a href="../docs/vol_de_nuit_ch1_interpolated.pdf">Beispiel mit Interpolation</a>).</p></li>
<li><b>Interpolation:</b> VSTENO kann Stenozeichen nun mit 2-, 4- oder 8-facher Auflösung berechnen und erreicht dadurch eine bessere 
Druckqualität (<a href="../docs/interpolation_beispiel.png">Illustration</a>).</li>
</ul>
<p>Darüberhinaus wurden diverse weitere Verbesserungen vorgenommen, die in den <a href="release_notes.php">release notes</a> und in der 
<a href="../docs/documentation_v03.pdf">Dokumentation V0.3</a> genauer beschrieben sind.</p>

<p><h2>Modelle</h2></p>
<p>Der aktuelle Stand der stenografischen Modelle:</p>

<ul>
<li><b><a href="https://github.com/marcelmaci/vsteno/blob/master/ling/DESSBAS.txt">Deutsch (V1.3):</a></b> Am weitesten fortgeschrittenes System, Grundschrift weit gehend fehlerfrei, die Eilschrift ist als
erste Version (Alpha) zu betrachten.</li>
<li><b><a href="https://github.com/marcelmaci/vsteno/blob/master/ling/SPSSBAS.txt">Spanisch (V0.3):</a></b> keine Änderungen (brauchbar, aber Korrektheit der Stenogramme z.T. unklar)</li>
<li><b><a href="https://github.com/marcelmaci/vsteno/blob/master/ling/FRSSBAS.txt">Französisch (V0.3):</a></b> Am zweit weitesten fortgeschrittenes System, Stenogramme in der Grundschrift werden sehr oft korrekt wiedergegeben.</li>
<li><b><a href="https://github.com/marcelmaci/vsteno/blob/master/ling/ENSSBAS.txt">Englisch (V0.1):</a></b> keine Änderungen (Alpha-Stadium, Erstversion)</li>
</ul>
<p>Inzwischen verwenden alle Modelle das gemeinsame Font <a href="https://github.com/marcelmaci/vsteno/blob/master/ling/GESSBAS.txt">GESSBAS</a>, das kontinuierlich verbessert wird.</p>
<p><h2>Ausblick</h2></p>
<p>Voraussichtlich wird es noch eine weitere Release geben, in der Fehler bereinigt und auch der ganze Code noch einmal durchgekämmt wird. 
Im Grunde soll die Entwicklung des Programmes aber an dieser Stelle abgeschlossen werden. Dies durchaus mit der Idee, auf andere Aspekte zu 
fokussieren. Konkret soll VSTENO nun dazu verwendet werden, um stenografische Lektüre aufzubereiten und herauszugeben.</p>
<p>Konkret schweben mir folgende Buchprojekte vor:</p>
<ul>
<li><p><b><u>Ein Roman von Friedrich Glauser:</u></b><br>
Es sind mehrere Romane im <a href="http://www.projekt-gutenberg.com">Projekt Gutenberg</a> vorhanden (z.B. 
<a href="https://www.projekt-gutenberg.org/glauser/krock/krock.html">Krock&Co</a>), die sich nach Abklärung des Copyrights vermutlich drucken liessen. Der 
Druck würde als reine Stenoausgabe oder als Paralleledition (mit Langschrifttext gegenüber) erfolgen.</p></li>
<li><p><b><u>Vol de nuit von Saint-Exupéry:</u></b><br>Auch dieser Text ist in der <a href="https://ebooks-bnr.com">bibliothèque numérique 
romande</a> bereits als 
<a href="https://ebooks-bnr.com/saint-exupery-antoine-de-vol-de-nuit/">freies E-Book</a> 
verfügbar. Zusätzlich habe ich hier vor drei Jahren eine schweizerdeutsche Übersetzung z.T. nach Richtlinien und mit Materialien von 
<a href="http://zuerituetsch.ch/">Viktor Schobinger</a> erstellt. Die Idee wäre hier also eine eine Parelledition mit französischer Steno
und schweizerdeutscher Übersetzung.</li>
</ul>

<p>Im Idealfall würden diese Bücher mit einer Gruppe von Stenograf/innen erstellt. Ob und wie sich das umsetzen lässt wird sich zeigen ... 
Es ist aber auf jeden Fall meine erklärte Absicht, in den nächsten Monaten weniger zu programmieren und gewissermassen mehr in den 
editorialen Bereich zu wechseln ... :)</p>

<center>* * *</center>
<p>=> Hier finden Sie <a href="old_news.php">ältere News-Einträge</a>.
<br>

<?php require "vsteno_template_bottom.php"; ?>
