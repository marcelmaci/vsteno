<?php require "vsteno_template_top.php"; ?>
<h1>Gemeinschaftsprojekt</h1>
<h2>Anfang</h2>
<p>VSTENO begann als privates Projekt im April 2018.</p><p>Im Mai 2018 gelangte eine erste lauffähige Version auf GitHub (<a href="http://www.github.com/marcelmaci/vsteno">www.github.com/marcelmaci/vsteno</a>).</p>
<h2>Konkretisierung</h2>
<p>Ab Juni 2018 verdichteten sich die Ziele, das Projekt ... <ol><li>weiterzuentwickeln</li><li>sauber zu versionieren</li><li>publik zu machen</li><li>gegen aussen zu öffnen (via Github)</li></ol></p>
<h2>Aktuell</h2>
<p>Mit Stand Oktober 2018 umfasst das Projekt nun rund 17000 Zeilen (wovon ca. 5700 Zeilen Code, 1000 Zeilen linguistische Daten, 800 Zeilen Webseite und der Rest Dokumentation). Trotz dieser 
ansehnlichen Grösse steht das Projekt nach wie vor am Anfang und wird vermutlich etwa 1-2 Jahre Entwicklungsarbeit benötigen (Grundversion, danach regelmässige Updates).</p>
<h2>Mitmachen</h2>
<p>VSTENO ist von Anfang an als Gemeinschaftsprojekt gedacht: Einerseits ist der Quellcode via <a href="http://www.github.com/marcelmaci/vsteno">GitHub</a> öffentlich zugänglich. Darüberhinaus bietet VSTENO verschiedene Online-Möglichkeiten, auch als Nicht-Programmierer/in einen Beitrag
zur Verbesserung der linguistischen Daten zu leisten: z.B.
<ol><li><b>Online-Editoren:</b> Damit können eigene Stenografie-Systeme angelegt oder das bestehende Stenografie-System (Grundschrift Stolze-Schrey, deutsch) verbessert werden kann.</li>
<li><b>Datenbank-Anbindung:</b> Bietet die Möglichkeit, falsche Stenogramme via eine simple Maske direkt im Browser zu melden oder zu korrigieren.</li>
</ol>
<p><b><i>=> Da es sich bei VSTENO um ein komplexes und aufwändiges Projekt mit vielen Bereichen handelt, soll diese Seite vor allem dazu dienen, ganz offiziell zur Mitarbeit aufzurufen.</i></b></p>
<h1>Warum mitmachen bei VSTENO?</h1>
<h2>Spannend!</h2>
<p>VSTENO ist ein sehr abwechslungsreiches und spannendes Projekt, welches sich weit über die Informatik und die Stenografie in weitere (Lebens-)Bereiche hineinerstreckt. Anbei ein kleiner 
Überblick über die Themenbereiche, die VSTENO direkt oder indirekt betrifft oder tangiert:</p>
<ul>
    <li><b>Linguistik:</b> Lösungen (und Formeln/Regeln) in den Bereichen Phonetik, Morphologie und Lexika.</li>
    <li><b>Stenografie:</b> Umsetzung weiterer Systeme (andere Sprachen, Eilschrift, DEK etc.)</li>
    <li><b>Programmierung:</b> LAMP-Entwicklung (Linux, Apache, MySQL, PHP). Ferner: Kenntnisse in git, REGEX, CSS, HTML 
    und JavaScript (Entwicklung eines interaktiven Zeicheneditors).</li>
    <li><b>Grafik:</b> Stenozeichen als Vektorgrafiken (Zeicheneditor) und Webdesign</li>
    <li><b>Mathematik:</b> Algorithmen rund um Bezier-Kurven (z.B. Zeichenverbindungen, Schattierungen von Zeichen)</li>
    <li><b>Übersetzung:</b> Verfassen, Aktualisieren und Übersetzen von Dokumentationen.</li>
    <li><b>Testing:</b> Erstellen und Durchführen von Test-Suiten (Wortlisten und Texte).</li>
    <li><b>Literatur:</b> Lektorat und Herausgabe von Texten und Büchern in Steno (z.B. als E-Book)</li> 
    <li><b>Rechtliches:</b> Abklärung von Rechten (Autoren, Texte u.a.).</li>
</ul>
<h2>Interessiert?</h2>
<p>Haben Sie Kenntnisse in einem dieser Bereich und sind interessiert, an VSTENO mitzuarbeiten? Dann werden Sie Teil der "Community" bzw. helfen Sie mit, diese zu begründen! Wie bereits
erwähnt ist VSTENO Freie Software, d.h. das Projekt gehört Ihnen genauso wie mir. Mit anderen Worten: Obwohl zwangsläufig jemand Initiator und Koordinator der Idee ist, soll 
das Projekt am Schluss ein Gesamtwerk sein, für das ein Kollektiv zeichnet (und das auch dem besagten Kollektiv gehört).</p>
<h2>Ich kann aber gar nicht programmieren ...</h2>
<p>
Wie bereits erwähnt müssen Sie ausdrücklich NICHT programmieren können, um bei diesem Projekt mitzuwirken! Im Gegenteil: Als Stenograf oder Stenografin können Sie einen entscheidenden Beitrag
zum Projekt beitragen, indem Sie z.B. falsche Stenogramme melden oder selber korrigieren! Falls Sie darüberhinaus ein anderes stenografisches System (Stolze-Schrey, DEK, Gregg, Duployé etc. auf Deutsch, Französisch,
Englisch etc.) beherrschen, dann können Sie dieses über die Formelsprache von VSTENO und im Online-Editor auch selber anlegen (und die dazugehörigen Wörterbücher und Datenbanken verwalten). 
Falls Sie nicht wissen, wo und wie das genau funktioniert (oder ob dies für Ihr System überhaupt möglich ist), dürfen Sie sich gerne mit mir via <A href="mailto:m.maci@gmx.ch">Mail</a> 
in Verbindung setzen, um weitere Details zu besprechen!
</p>
<h1>Ziele</h1>
<p>VSTENO setzt sich für die nächsten Monate folgende Ziele (ungefähre Prioritätenangabe falls mehrere):</p>
<ul>
    <li><b>Stenografie</b> (1) Möglichst systemtreue Umsetzung der Grundschrift von Stolze-Schrey anhand von Regeln, (2) Ergänzung der Regeln durch ein Wörterbuch (Database) mit Ausnahmen, 
    (3) Integrierung einer morphologischen Analyse (z.B. in dem ein Silbentrenner wie hunspell o.ä. angebunden wird), (4) Umsetzung anderer Systeme (Grundschriften: Spanisch, Englisch, Französisch,
    Italienisch; Eilschrift: Deutsch)</li>
    <li><b>Programmierung:</b> (1) Fertigstellung eines abstrakten, regelbasierten Parsers <b>[Okt18: erledigt!]</b>, (2) Fertigstellung sämtlicher Optionen <b>[Okt18: 95% erledigt!]</b>, 
    (3) Anbindung Datenbank und Interface <b>[Okt18: 90% erledigt!]</b>, 
    (4) Entwicklung eines grafischen Zeicheneditors (JavaScript)</li>
    <li><b>Grafik:</b> Feindesign Stenozeichen (erst nach Priorität 4 in Programmierung möglich)</li>
    <li><b>Mathematik:</b> saubere Zeichenverbindung (z.B. "tangentiales" Anlegen von runden Zeichen; kann ich definitiv nicht selber machen, mir fehlt hier das mathematische Rüstzeug!)</li>
    <li><b>Übersetzung:</b> (1) Verfassen Anwendertutorial, (2) Linguistisches Handbuch, (3) Programmier-Dokumentation</li>
    <li><b>Literatur:</b> Herausgabe eines (oder mehrerer) Romane von Friedrich Glauser in Stenografie (dies ist mein persönliches Ziel bei diesem Projekt;-)</li> 
</ul>
<p>Innerhalb der verschiedenen Kategorien hat die Programmierung die höchste Priorität, da die Implementierung der entsprechenden Funktionen als Basis für alle weiteren Schritte unabdingbar ist.
<h2>Zeitrahmen</h2>
<p>Angestrebt wird - vorsichtig ausgedrückt - eine Betaversion im Frühjahr 2019.</p>
<?php require "vsteno_template_bottom.php"; ?>