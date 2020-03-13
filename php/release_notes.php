<?php require "vsteno_template_top.php"; ?>
<h1>Release notes</h1>
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

Steno Engine:
<ul>
    <li><b>Modelle</b>:<br>
    Import/Export via file (MDUMP/MLOAD)<br>
    Optionale Regeln</li>
    <li><b>Ausgabe</b><br>
    - Inverted mode (teilweise, nur für Entwicklung)<br>
    - &LT;newpage&GT;-Tag (Seitenumbruch)</li>
    <li><b>Engine</b><br>
    - Rendering: Refine-Funktion mit Mittellinienmodellierung (Zwischenpunkte) (to do)<br>
    - TokenShifter: Funktionserweiterung Spacer (Zeichengruppeninformation)<br>
    - Diverse Performance-Optimierungen<br>
    - Interpolation zur Steigerung der Druckqualität</li>
    <li><b>Debugging</b><br>
    - Ausgabe Stage-Wechsel (nur Stages 3 und 4)<br>
    - Anzeigen von Punkten & Koordinaten (inline)<br>
    - Angabe zum Caching</li>
</ul>
</li>
<p><i>(*) Als terminus a quo gilt im Zweifelsfall: wenn es fertig ist ... :)<br>(**)Nach Hephaistos und Ariadne dürfte nun wieder ein männlicher Name aus der griechischen 
Mythologie zum Zuge kommen ... :)</i></p>

<a href="input.php"><br><button>zurück</button></a>

<?php require "vsteno_template_bottom.php"; ?>
