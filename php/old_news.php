<?php require "vsteno_template_top.php"; ?>
<h1>Old News</h1>
<p>Hier finden Sie archivierte, ältere News-Einträge.</p>

<p><b>28/08/20 Post ... !</b></p>
<p>Heute lag ein Päckchen im Briefkasten.</p><p>Lang erwartetes und ersehntes, in Karton gepacktes Objekt von 240 Gramm.</p><p>
Der Traum von VSTENO war es, eines Tages ein gedrucktes Buch in Händen zu halten. Haptik statt Hektik. Blättern 
statt Wischen, Papier statt Bildschirm. Und voilà - hier ist es, das allererste, vollständig mit VSTENO generierte, gedruckte und gebundene 
BOD*:</p>
<img src="../web/dwz_bod1.JPG" height="300"> <img src="../web/dwz_bod2.JPG" height="300">
<p>Es handelt sich vorerst noch um ein Unikat.</p><p>Es ist schwierig abzuschätzen, wie ein Buch letzten Endes aussieht, wenn man es vollständig 
digital erstellt. In diesem Sinne ging es bei diesem Exemplar darum, 1:1 zu sehen, wie sich Titelseite und Text beim Blättern präsentieren, 
wie die Schriftgrösse, das gewählte Papier und das Format wirken.</p><p>Natürlich geht es auch darum, das Werk nun ein letztes Mal Korrektur zu lesen:
Stimmen die Stenogramme? Was muss noch korrigiert werden? Die Fertigstelltung der "Widerspenstigen" scheint mir nun näher als je. 
Insofern: Wer das Buch vorbestellen möchte, kann dies gerne 
per Mail tun.</p>
<p>* Man lese: Book on Demand</p>
<p><b>24/07/20 "Der Widerspenstigen Zähmung" von Karl Ettlinger (gedruckte Ausgabe)</b></p>
<p>Wie bereits angekündigt soll in Bälde eine erste, vollständig mit VSTENO generierte Printausgabe erscheinen. Gewählt wurde der Roman 
"Der Widerspenstigen Zähmung" von Karl Ettlinger. Der stenografierte Text wurde inzwischen korpuslektoriert und liegt nun in einer 
<a href="../books/ettlinger_der_widerspenstigen_zaehmung_31072020.pdf">Prefinal-Version</a> vor. Diese wird nun ein letztes 
Mal komplett Korrektur gelesen. Im Anschluss daran werde ich den Druck organisieren. Im Moment ist noch nicht klar, in welcher Auflage das Werk 
erscheinen wird und was der Druck genau kostet. Wer gerne ein Exemplar vorreservieren möchte, kann sich via 
<a href="mailto:marcel.maci@gmx.ch">Mail</a> bei mir melden.</p>
<p>Die definitive Ausgabe wird - analog zur Ausgabe in der <a href="library.php">Bibliothek</a> - mit einer Titel und einer Schlussseite versehen 
sein. Wer Fehler in der Prefinal-Version findet, darf diese natürlich auch gerne via Mail melden!</p>

<p><b>18/06/20 Italienisch</b></p>
<p>Als inzwischen fünfte Variante von Stolze-Schrey ist ab heute ein italienisches Modell (ITSSBAS) online verfügbar. Es wurde ausgehend von den 
Regeln für Spanisch erstellt und ist zu diesem Zeitpunkt noch absolut experimentell. Für Rückmeldungen zu Fehlern bin ich dankbar.</p>

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


<p><b>15/11/19</b></p>

<h2>Auf Ariadnes (P)faden ... ! :)</h2>
<p>Nach weiteren 200 Entwicklungsstunden (oder rund 1150 Stunden in 19 Monaten insgesamt) freue ich mich, 
Ihnen die zweite offizielle Version namens <a href="https://de.wikipedia.org/wiki/Ariadne"><b>Ariadne</b></a> zu präsentieren! Es wurde eine 
stattliche Zahl neuer Features integriert, die detailliert in den <a href="release_notes.php">Release Notes</a> aufgeführt sind.</p>

<p><b>Einige Neuerungen:</b></p>
<ul>
    <li><b>Modelle</b>: Neben <a href="https://github.com/marcelmaci/vsteno/blob/master/ling/DESSBAS.txt">Deutsch</a>, 
    <a href="https://github.com/marcelmaci/vsteno/blob/master/ling/SPSSBAS.txt">Spanisch</a>, 
    <a href="https://github.com/marcelmaci/vsteno/blob/master/ling/FRSSBAS.txt">Französisch</a> (die verbessert wurden) ist nun auch 
    <a href="https://github.com/marcelmaci/vsteno/blob/master/ling/ENSSBAS.txt">Englisch</a> in einer ersten Version verfügbar</li>
    <li><b>Fonts</b>: Neu ist es möglich, ein und dasselbe Font 
    <a href="https://github.com/marcelmaci/vsteno/blob/master/ling/GESSBAS.txt">GESSBAS</a> für verschiedene Sprachen zu verwenden. Nebst den Stenografiezeichen sind nun 
    auch handgeschriebene Blockschriftzeichen verfügbar.</li>
    <li><b>Dokumentation</b>: Die inzwischen 100 Seiten umfassende <a href="../docs/documentation_v02.pdf">Hauptdokumentation</a> wurde angepasst. 
    Zusätzlich steht mit der <a href="../docs/ref/vsteno_reference.txt">Quick Reference</a> erstmals auch eine englische Dokumentation zur 
    Verfügung.
    <li><b>Regeln</b>: Neu können linguistische Analysen (z.B. Laut und Schrift oder Wortanalyse und Phonetik) kombiniert und mit hybriden 
    Regeln komplexe Zusammenhänge abgebildet werden.</li>
    <li><b>Ausgabe</b>: Neu lassen sich Paralleltexte (<a href="../docs/englisch_testtext_parallel.pdf">Beispiel</a>) generieren, also Texte, bei denen sich Kurz- und Langschrift auf der gleichen Seite
    gegenüberstehen.</li>
    <li><b>Engine</b>: Mit Ariadne wurde nun die SE1rev1 vollständig umgesetzt, d.h. es stehen nun Backports wie 
    <a href="../docs/stenoengines.pdf">proportionale Punkte</a> 
    und <a href="../docs/Umrissmodellierung.pdf">Umrissmodellierung</a> zur Verfügung.</li>
</ul>
<p><b>So - what&#39;s next?</b></p>
<p>VSTENO begann als Machbarkeitsstudie. Im Trial-and-Error-Verfahren wurden unzählige Entwicklungsansätze 
erprobt, einige glücklicher, andere weniger. Einige Designentscheidungen erweisen sich nun als Altlasten, die eine Weiterentwicklung 
einschränken. Früher oder später wird sich somit eine Neuimplementierung aufdrängen. Dennoch ist VSTENO ein solides Werkzeug, das nach wie vor 
Potenzial für ein, zwei weitere Relases (mit Bugfixes und moderaten Verbesserungen) hat.</p>

<p><b>Und die Stenografie?</b></p>
<p>Verbessert werden sollen in den nächsten Releases vor allem die stenografischen Modelle. Die Situation ist hier je nach System 
sehr unterschiedlich:</p>
<ul>
    <li><b>Deutsch (V1.1)</b>: Gut getestetes und am weitesten entwickeltes System, das trotz seiner Komplexität (Wortkompositionen, Morphologie) eine 
    hohe Zuverlässigkeit aufweist. Hier gibt es ausreichend Personen, die Korrektur lesen.</li>
    <li><b>Spanisch (V0.3)</b>: Praktisch ungetestetes, relativ einfaches System. Nachforschungen beim <a href="https://www.steno.ch">SSV</a> 
    ergaben, dass es leider so gut wie keine Stenograf/innen mehr, die dieses System noch getreu nach Urkunde schreiben.</li>
    <li><b>Französisch (V0.2)</b>: Noch wenig getestetes und relativ komplexes System (Phonetik).</li>
    <li><b>Englisch (V0.1)</b>: Noch wenig getestetes und relativ komplexes System (Phonetik, Wortkompositionen)</li>
</ul>
<p>Am meisten Entwicklungspotenzial sehe ich somit im Moment bei den Systemen Französisch und Englisch.</p>
<p><b>Falls Sie eines dieser Systeme schreiben und Lust haben, diese zu verbessern, indem Sie Korrektur lesen, dann melden Sie sich doch!</b></p>

<p><b>... ab in die Mottenkiste</b></p>
<p>Mit Ariadne gelange ich zum Schluss, dass folgende Ideen ausgemottet gehören:</p> 
<ul>
    <li><p><b>VPAINT</b>: Rückblickend 3 Monate Entwicklung für die Katze! Grundidee damals: einen grafischen Editor schreiben, der vollständig 
    in einem Browser läuftm, einfach zu bedienen ist und auf der SE2 basiert. Summa summarum viel Aufwand für wenig Ertrag. Fortan wird VSTENO also
    wieder zum "Hacker-Tool für Nerds", bei dem man sich die Stenofonts hart verdienen muss, indem man sie (übrigens wie bei Metafont) via einen Texteditor designt ... ;-)
    </p></li>
    <li><p><b>SE2</b>: Ein nettes, aber letztlich unbrauchbares Konstrukt. Die angedachte Umrissmodellierung ist zu komplex und die Idee, auf 
    Combiner und Shifter aus der SE1 zu verzichten schlicht unbrauchbar. Die SE2 ist somit zu verwerfen, bevor sie überhaupt jemals (in PHP) umgesetzt 
    wurde und durch eine SE3 zu ersetzen, welche gute Ideen aus beiden SEs vereint.</p></li>
    <li><p><b>Datenbank</b>: Ursprünglich ging ich davon aus, dass ein Wörterbuch mit "Ausnahmen" Sinn macht und dieses sogar via Webaccounts 
    bewirtschaftet werden könnte. Die Verzettelung, die dies mit sich bringt, ist grauenhaft. Es ist somit besser, saubere "Modelle" zu schreiben 
    (die allfällige Ausnahmen als Regeln integrieren) und diese in eine Datei zu schreiben.</p></li>
</ul>
<p><b>Visionen?</b><br>
<p>Erlauben wir uns zum Abschluss vielleicht doch noch einen Blick in die Kristallkugel: Wie sähe das perfekte Stenografietool aus?</p>
<p>Ingredienzien wären hier aus meiner Sicht:<p>
<ul>
    <li>Umsetzung C++ (oder Java)</li>
    <li>Verwendung von lex und yacc für den Parser</li>
    <li>Metafont und TEX (LaTeX) für die Zeichengenerierung und den Satz</li>
</ul>

<p>Dies ergäbe aus meiner Sicht eine solide SE3 mit verschiedenem Einsatzzweck: (1) E-Reader oder (2) Desktop-Publishing-System für qualitativ 
hochwertige Ausgaben von Stenografie-Texten - um nur die zwei wichtigsten zu nennen.</p>

<p><i><b>[UPDATE 23/12/19]</b> Hier eine kleine Weihnachtsedition: Inzwischen wurden 20 von 35 Kapiteln aus Hanspeter Frechs Buch "Die superschnelle Geheimschrift", 
Band 2, integriert. Die (noch experimentelle) gekürzte Form (Eilschrift) der Steno ist auf Deutsch nun parallel zur Verkehrsschrift (Grundstufe) verfügbar und kann durch Setzen 
der Parameter HF1 und HF2 im <a href="input.php">Eingabeformular</a> (unter "Engine") aktiviert werden. In diesem Sinne: Frohes Fest!</p>

<center>* * *</center>

<p><b><u>25/07/19:</u><br>Here we are ... ! :)</b></p>
<p>Nach 15 Monaten und rund 950 Entwicklungsstunden* freue ich mich, 
heute die erste offizielle Version namens <a href="https://de.wikipedia.org/wiki/Hephaistos"><b>HEPHAISTOS</b></a> zu präsentieren!
Der sagenumwobene Schmied - zweifellos Kind einer humanistischen Tradition und hoffentlich der erste aus dem Geschlecht einer Reihe edler Olympier, 
die folgen - wartet mit 
diesen Goodies auf:<p>
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
<p><b>[UPDATE 14.08.19:]</b> <a href="release_notes.php">Release notes</a> mit Preview auf die nächste Version veröffentlicht. Aktuelle Informationen 
zur neuen <a href="../docs/Umrissmodellierung.pdf">Umrissmodellierung</a>.</p>
<h2>Danke</h2>
<p>Mit der Release von VSTENO V0.1 Hephaistos bedanke ich mich ganz offiziell bei Frau Yvonne Reith vom Schweizerischen 
Stenografenverband, welche seit Februar unzählige Rückmeldungen zu Korrektheit und Schriftbild gemacht und damit einen 
wesentlichen Beitrag dazu geleistet hat, dass auf Deutsch inzwischen über 95% der Stenogramme korrekt generiert werden!</p> 
<p>Ebenfalls bedanke ich mich beim <a href="http://www.steno.ch">Schweizerischen Stenografenverband</a> für die Offenheit und 
Unterstützung gegenüber dem Projekt.</p>
<center>* * *</center>
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
<i><p><b><u>16. April 2019</u><br><br>Lets fetz - 1 Jahr VSTENO!</b><br><br>
Zugegeben: Es ist nicht gerade die goldene Hochzeit ... noch nicht mal die silberne. Dennoch: Es gibt Grund zum Feiern! Ziemlich genau 1 Jahr ist
her, dass die erste Codezeile für VSTENO entstand. Die früheste "archäologisch auffindbare" Datei datiert vom 10. April 2018: Sie legte 
zwei aneinander anschliessende Bezierkurven durch 3 beliebige Punkte. Dieser Algorithmus der so genannten Splines (<i>splines interpolation</i>)
bildet bis heute das Kernstück von VSTENO.</p>
<p><b>Zeit somit für eine kurze Bilanz und einige Eckdaten zu VSTENO.</b></p>

<ul>
<li><p><b>Code:</b> VSTENO besteht inzwischen aus rund 10'500 Zeilen PHP-Code und 5000 Zeilen JavaScript (VPAINT). Das Gesamtprogramm enthält
zusätzlich die Bibliotheken phpSyllable und PAPERS.JS, die im Rahmen freier Lizenzen integriert wurden. Insgesamt wurden in 220 Commits 
429'994 Zeilen hinzugefügt und 21'841 Zeilen gelöscht.</p></li>

<li><p><b>Dokumentation</b>: Nebst der Webseite (die etwa 800 Zeilen HTML/CSS-Code enthält) wurden mehrere Dokumentationen erstellt. Im
Hinblick auf die erste offizielle Version 0.1 wurde eine <a href="../docs/documentation_v01rc.pdf">Gesamtdokumentation</a> begonnen, 
die mittlerweile 65 Seiten umfasst.</p>
</li>
<li><p><b>Stolze-Schrey</b>: Die Datei mit den Definitionen für die Grundschrift Stolze-Schrey umfasst inzwischen 2343 Zeilen. Darin enthalten
sind etwa 1400 Regeln und 360 Zeichen (wovon 200 Grundzeichen, 70 kombinierte Zeichen und 90 verschobene Zeichen).</li>

<li><b>Datenbanken</b>: Die Datenbanken (Purgatorium, Olympus, Elysium) von VSTENO sind nach wie vor leer - und das ist gut so! Das Programm
soll möglichst weit gehend in der Lage sein, Stenogramme anhand von Regeln zu berechnen (die Gruppe an "Ausnahmen" soll also klein gehalten
werden).
</p></li>
<li><p><b>Performance</b>: Die zunehmende Komplexität von VSTENO fordert ihren Preis: Die Berechnung einer Stenoseite dauert nun im Schnitt ca.
20 Sekunden. Der Server schafft im Moment maximal rund 20 Seiten aufs Mal. Die Hauptrechenzeit wird nicht zur Generierung der Stenogramme, sondern
für die linguistische Analyse verwendet (diese benötigt rund 80% der Rechenzeit*).
</p></li>
<li><p><b>Zeit</b>: Rein auf der Informatikseite flossen rund 740 Stunden Entwicklungszeit in VSTENO. Hinzuzurechnen sind ferner Handkorrekturen, 
welche vor allem zwei "gute Seelen" (Mitglieder des Schweizerischen Stenografenverbands) auf Papier vornahmen, um das Schriftbild zu 
verbessern (diese Arbeit war zum Teil sehr detailreich, professionell und hat wesentlich zur Verbesserung von VSTENO beigetragen).
<p></li>
<li><p><b>Finanziell</b>: Im Laufe des Jahres gingen zwei Spenden in der Höhe von 220.- CHF ein. Damit können ungefähr die Infrastrukturkosten 
für das vergangene und das kommende Jahr gedeckt werden (15.- CHF für die Internet-Adresse und ca. 100.- für den Server).<p></li>
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
