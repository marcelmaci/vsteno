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
<p><b>15/04/20 - Happy Birthday VSTENO!</b><p>
<p>Ziemlich genau zwei Jahre ist es her, dass an einem einzigen Tag die ersten Codezeilen für VSTENO entstanden: eine simple Funktion, welche 
die nötigen Kontrollpunkte berechnete, um eine Folge von Bezier-Kurven (so genannte Splines) durch eine Reihe frei wählbarer Punkte zu legen.
<pre>
function GetControlPoints($px0, $py0, $px1, $py1, $px2, $py2, $t1, $t2) {
    // returns control points for p1
    $d01 = sqrt(pow($px1-$px0,2)+pow($py1-$py0,2));
    $d12 = sqrt(pow($px2-$px1,2)+pow($py2-$py1,2));
    $fa = $t1*$d01 / ($d01+$d12);
    $fb = $t2*$d12 / ($d01+$d12);
    $c1x = $px1 - $fa*($px2-$px0);
    $c1y = $py1 - $fa*($py2-$py0);
    $c2x = $px1 + $fb*($px2-$px0);
    $c2y = $py1 + $fb*($py2-$py0);
    return array($c1x, $c1y, $c2x, $c2y);
}
</pre>
Bis heute ist es für mich erstaunlich zu sehen, dass kein von VSTENO berechnetes Stenogramm ohne diese Funktion auskommt!</p>
<p>Obwohl in diesen (mitunter pythagoräischen) Zeilen im Prinzip die ganze Magie von VSTENO steckt, reicht dies allein noch nicht für 
ein Programm. Stattdessen bedurfte es zweier Jahre, um die "mathematische Essenz" in technische (informatische) Formen zu giessen: ein Programm, 
welches - ausgehend von der Langschrift - Stenogramme erzeugt, die im Browser angezeigt und als PDF exportiert werden können.</p>
<p>Für Stenograf/innen bedeuteten diese zwei Jahre eine lange Durststrecke: Die Stenogramme kamen vorerst 
einigermassen holprig, d.h. mit argen Unschönheiten und plumpen Fehlern behaftet, daher. Zwar verbesserten sie sich im Laufe der Zeit, dennoch 
waren dieser Entwicklung in der ersten Phase relativ enge Grenzen gesetzt. Denn es galt: 
erst das Programm, dann die Stenogramme! Anders gesagt: Es bestand schlicht nicht die Zeit, um nebst der Programmentwicklung zusätzlich an 
der Verbesserung der Stenogramme zu arbeiten.</p>
<p>Dies soll sich nun - im dritten Entwicklungsjahr - ändern. Ein Programm wie VSTENO ist zwar nie fertig (..), aber gerade deshalb ist es wichtig,
hier - und an diesem Jahrestag - einen Strich zu ziehen und das Projekt offiziell auf Eis zu legen. D.h. es werden inskünftig 
keine neuen Funktionalitäten mehr in VSTENO integriert und nur noch Bugfixes vorgenommen. </p>
<p>Stattdessen soll die Zeit nun konsequent für die Entwicklung der stenografischen Modelle verwendet werden. 
Für das deutsche System ist die Arbeit seit einigen Wochen im Gang. Die Stenogramme haben sich bereits stark verbessert und werden in 
den kommenden Wochen und Monaten weiter optimiert. Ultimatives Ziel, wie bereits erwähnt, wird es sein, komplette Romane - in elektronischer 
und gedruckter Form - zu publizieren.</p>
<p>In diesem Sinne: Stay tuned ... there's more to come ... ! ;-)</p>
<p><i><b>[UPDATE 16/04/20]:</b> Eben erreicht mich die traurige Nachricht, dass Luís Sepúlveda, ein Autor, den ich wegen seiner Menschlichkeit 
und seinem gesunden Menschenverstand besonders geschätzt habe, am Coronavirus verstorben ist. Zu seinem Gedenken hier das <a href='../docs/sepulveda_gaviota_cap9.pdf'>Kapitel 9</a> aus Historia 
de una gaviota y del gato que le enseño a volar ... Descansa en paz, ¡compañero!</i></p>
<p><i><b>[UPDATE 27/04/2020]:</b> VSTENO publiziert sein erstes stenografisches <a href="../books/franz_kafka_ein_bericht_fuer_eine_akademie_27042020.pdf">eBook</a>! 
In der neu geschaffenen Ecke namens <a href="library.php">Bibliothek</a> sollen in 
Zukunft weitere eBooks aufgeschaltet werden.</i></p>
<p><i><b>[UPDATE 11/05/2020]:</b> Es freut mich, heute in der Bibliothek das erste längere mit VSTENO generierte eBook auf&shy;zu&shy;schalten. Es ist dies
<a href="library.php">Der Widerspenstigen Zähmung</a> von Karl Ettlinger (230 Seiten) - meines Erachtens 
ein äusserst vergnügliches Werkchen, welches die Charaktere der Hauptfiguren 
auf humoristische Weise und konsequent bis zum Ende ausleuchtet.</p>
<p><b>Technische Info:</b> Das eBook wurde zuerst auf einem Core2Duo mit 2.53Ghz 
generiert, was rund 40 min dauerte. Um das Ganze etwas zu verschnellern, habe ich inzwischen eine separate Maschine mit einem i5 3.3Ghz 
aufgesetzt, welche das Ganze in 20 min schafft.</p><p><b>Druckversion:</b> Nach wie vor trage ich mich mit dem Gedanken, Steno-Lektüre auch in 
gedruckter Version herauszugeben. Ziel in den nächsten Wochen wird es deshalb sein, Schrift und Stenogramme dieses Werks weiter zu verbessern 
und es dann als "Book on demand" herauszugeben.</i></p>

<center>* * *</center>

<p><b>20/03/20 - Der Titan ist da ... :)</b><p>
<p>Nach 23 Monaten und rund 1300 Entwicklungsstunden freue ich mich,
die Version 0.3 namens <a href="https://de.wikipedia.org/wiki/Hyperion_(Titan)">Hyperion</a> vorzustellen. Im 
<a href="https://steno.ch/0/index.php/der-schweizer-stenograf">Schweizer Stenograf</a> (Januar) erschien  
ein <a href="presse.php">Artikel</a> zu VSTENO.</p>

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
<a href="http://zuerituetsch.ch/">Viktor Schobinger</a> erstellt. Die Idee wäre hier also eine Parelledition mit französischer Steno
und schweizerdeutscher Übersetzung.</li>
</ul>

<p>Im Idealfall würden diese Bücher mit einer Gruppe von Stenograf/innen erstellt. Ob und wie sich das umsetzen lässt wird sich zeigen ... 
Es ist aber auf jeden Fall meine erklärte Absicht, in den nächsten Monaten weniger zu programmieren und gewissermassen mehr in den 
editorialen Bereich zu wechseln ... :)</p>

<p><h2>Serendipity</h2></p>
<p>Und dann noch dies: Vor kurzem erhielt ich den Hinweis, dass es tatsächlich ein weiteres Programm gibt, das Langschrift nach Stolze-Schrey 
überträgt. Es ist dies <a href="https://steno.tu-clausthal.de/Stolze-Schrey.php">text2Stolze-Schrey</a> von Prof. Sarman. 

<center>* * *</center>
<p>=> Hier finden Sie <a href="old_news.php">ältere News-Einträge</a>.
<br>

<?php require "vsteno_template_bottom.php"; ?>
