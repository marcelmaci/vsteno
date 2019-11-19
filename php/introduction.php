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
<p><h1>Neuigkeiten</h1>
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
<p><b>So - what's next?</b></p>
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
<p>Mit Ariadne gelange ich zum Schluss, dass folgende Ideen auf ausgemottet gehören:</p> 
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

<center>* * *</center>
<p>=> Hier finden Sie <a href="old_news.php">ältere News-Einträge</a>.
<br>

<?php require "vsteno_template_bottom.php"; ?>
