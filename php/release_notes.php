<?php require "vsteno_template_top.php"; ?>
<h1>Release notes</h1>
<h1>Hyperion (Version 0.3)</h1>
<p>Die Version 0.3 mit dem Namen <a href="https://de.wikipedia.org/wiki/Hyperion_(Titan)">Hyperion</a> wurde am 20. März 2020 veröffentlicht und 
enthält folgende Verbesserungen.</p>

<ul>
    <li><b>Modelle</b>:<br>
    Export/Import von Modellen via Datei auf Server (MDUMP/MLOAD)<br>
    Optionale Regeln: definierbare Optionen in Eingabeformular (Prüfung innerhalb Regeln)</li>
    <li><b>Ausgabe</b><br>
    - Yinyang-Funktion: Umschalten heller / dunkle Darstellung (nur wenn eingeloggt)<br>
    - &LT;newpage&GT;-Tag: manuelles Einfügen von Seitenumbrüchen</li>
    <li><b>Engine</b><br>
    - TokenShifter: Funktionserweiterung Spacer (Zeichengruppeninformation)<br>
    - Performance: diverse Optimierungen (Anpassen von Algorithmen und Code in Kernfunktionen)<br>
    - Interpolation: Errechnen von Zwischenpunkten (bessere Auflösung / Druckqualität)<br>
    - Akronyme: phonetische Transkription / Konvertierung zu Kleinbuchstaben selektierbar</li>
    <li><b>Debugging</b><br>
    - Stage-Wechsel: wird für Stages 3 und 4 angegeben<br>
    - Grafische Darstellung: Punkten & Koordinaten bei Interpolation (inline)<br>
    - Caching: Angabe, ob ein Resultat aus dem Cache stammt oder nicht</li>
    <li><b>Modelle</b><br>
    - Deutsch (DESSBAS): die Eilschrift nach Hanspeter Frech integriert (selektierbare Option)<br>
    - Spanisch (SPSSBAS): keine Änderungen<br>
    - Französisch (FRSSBAS): diverse Korrekturen (hohe Zuverlässigkeit)<br>
    - English (ENSSBAS): keine Änderungen<br>
    </li>
    <li><b>Dokumentation</b><br>
    - Printeditions: Anleitungen zur Erstellung von Taschenbüchern<br>
    </li>
</ul>

<h1>Ariadne (Version 0.2)</h1>
<p>Die Version 0.2 mit dem Namen <a href="http://de.wikipedia.org/wiki/Ariadne">Ariadne</a> wurde am 20. November 2019 veröffentlicht und 
enthält folgende Verbesserungen.</p>
<ul>
<li>Steno Engine: Integration ausgewählter Funktionalitäten der SE2:
<ul>
    <li>Variable, proportionale Schattierung</li>
    <li>Automatisierte Umriss-Schattierung (Polygon-Modellierung) ab Daten der SE1</li>
    <li>Orthogonale und proportionale Knots mit parallelen Rotationsachsen</li>
    <li>Paralleledition: Originaltext (Langschrift) neben Kurzschrift</li>
    <li>TokenShifter: Erweiterung um Scaling-Funktion (inklusive Anpassung der Strichdicke)</li>
    <li>Font: Import/Export (shared fonts)</li>
</ul>
</li>
<li>Phonetik: 
<ul>
    <li>Patchliste für falsch transkribierte Wörter (eSpeak)</li>
    <li>Hybride Regeln (Schrift/Analyse*/Phonetik) für Analyzer&Regel-Teil</li>
    <li>Transkription Einzelbuchstaben: selektierbar</li>
</ul>
</li>
<li>Modelle:
<ul>
    <li>Verbesserungen Deutsch, Spanisch, Französisch</li>
    <li>Französisch: laut- und schriftbasierte hybride Regeln</li>
    <li>ENSSBAS: Grundschrift Englisch (neu)</li>
    <li>GESSBAS: Shared Font (inkl. Blockschrift)</b>
</ul>
<li>Dokumentation</li>
<ul>
    <li>Deutsch: Ergänzungen neue Funktionen</li>
    <li>English: Quick-Reference (Kurzdokumentation)</li>
</ul>
<li>Bugfixes</li>
</ul>
<p>* In Kombination mit phonetischer Transkription nur Wortanalyse (keine Silben, Präfixe, Suffixe).</p>

<h1>Hephaistos (Version 0.1)</h1>
<p>Die erste offiziell releaste Version trägt den Namen <a href="http://de.wikipedia.org/wiki/Hephaistos">Hephaistos</a> und wurde am 26. Juli 2019 veröffentlicht.</p>
<h2>Merkmale</h2>
<ul>
<li>Steno Engine: SE1 rev0, also die urpsrünglich definierte SE mit Spline Interpolation (Bezier-Kurven), Mittellinienmodellierung (treppenförmige Schattierungen) und Horizontalneigung (Neigewinkel nur bedingt variabel)</li>
<li>Ausgaboptionen:</li>
<ul>
    <li>Inline- (ein Wort pro SVG) und Layout-Modus (eine Seite pro SVG)</li>
    <li>Normalschrift (Courier) und Stenogramme (separat und gemischt)</li>
    <li>Parameter wie Grösse, Farbe, Strichdicke, Schattierungsstärke etc. einstellbar (Eingabeformular) bzw. programmierbar (Inline-Option-Tags)</li>
    <li>Layout-Modus: linksbündig und Blocksatz, mit/ohne Hilfslinien, mit/ohne Linien-/Seitenzahlen</li>
</ul>
<li>Linguistische Analyse: Silbenanalyse mit phpSyllable und morphologische Analyse (Vor-/Nachsilben, Stämme) mit Hunspell</li>
<li>Phonetische Transkription: eSpeak (IPA oder Kirshenbaum Alphabet)</li>
<li>Datenbank: Tabellen für fehlerhafte, korrigierte und richtige Stenogramme (Purgatorium, Elysium, Olympus) mit der Möglichkeit, diese in 
einem Trainingsmodus zu verwalten. 
<li>Debug-Modus: zur Fehlersuche in eigenen Stenografie-Modellen (Inline-Modus)</li>
<li>Parser: REGEX-basierter Regelparser, der in der Datenbank abgelegte stenografische Modelle liest und abarbeitet (Interpreter).</li>
<li>Benutzerverwaltung: Login-Möglichkeit zur Online-Nutzung des Programmes, inklusive Möglichkeit ein eigenes stenografisches System anzulegen 
und zu verwalten (benutzerspezifische Tables analog zu öffentlichen Standard-Modellen)</li>
<li>Stenografische Systeme: 
<ul>
    <li>DESSBAS 1.0: Grundschrift Deutsch (morphologische Analyse, >95% korrekt generierte Stenogramme)</li>
    <li>SPSSBAS 0.2: Grundschrift Spanisch (rein schriftbasiert,  Beta-Status)</li>
    <li>FRSSBAS 0.1: Grundschrift Franzöisch (rein lautbasiert/phonetisch, Alpha-Status)</li>
</ul>
</li>
<li>Domentation: Version 0.1 (übernommen aus 0.1rc)</li>
<li>Installer: Skript zur automatischen Installation unter Trisquel GNU/Linux 8</li>
</ul>


<h1>Preview</h1>
<p>Das Publikationsdatum der nächsten Release ist offen(*) und der Name ist noch nicht festgelegt(**). Im Folgenden eine Liste mit Features, die nach und nach integriert werden.</p>

<ul>
<li><b>Modelle:</b>
<br>DESSBAS: Umfassende Verbesserungen in den Regeln betreffend Grundschrift
<br>GESSBAS: Umfassende Verbesserungen der Zeichendarstellungen (geteiltes Font)</li>
<li><b>Bugfixes:</b>
<br>Switching: Wechseln von Kurzschrift zu Handschrift in Layouted-Modus (Zeilenumbruch nun i.O.)
<br>Warnings: Globalen Scope korrigiert und Erkennen "leerer" Regeln verbessert
</li>
<li><b>Neu:</b>
<br>Statistik: Anzahl Regeln Analyzer / Anzahl gecachte Resultate
<br>Buch: Horizontaler Versatz und Zeilennummern in Abhängigkeit gerader / ungerader Seiten
<br>Ränder: Visualisierung Beschnitt (Seite)
<br>Scripts: Deinstallation + Überarbeitung Installation (To do) 
</ul>
<p><i>(*) Als terminus a quo gilt im Zweifelsfall: wenn es fertig ist ... :)<br>(**) Nach Hephaistos, Ariadne und Hyperion dürfte wieder ein weiblicher Name (vielleicht mit A:) aus der griechischen 
Mythologie zum Zuge kommen ... :)</i></p>

<a href="input.php"><br><button>zurück</button></a>

<?php require "vsteno_template_bottom.php"; ?>
