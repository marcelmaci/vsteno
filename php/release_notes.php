<?php require "vsteno_template_top.php"; ?>
<h1>Release notes</h1>
<h1>Hephaistos (Version 0.1)</h1>
<p>Die erste offiziell releaste Version trägt den Namen <a href="http://de.wikipedia.org/wiki/Hephaistos">Hephaistos</a> und wurde am 26. Juli 2019 veröffentlicht.</p>
<h2>Merkmale</h2>
<ul>
<li>Steno Engine: SE1 rev0, also die urpsrünglich definierte SE mit Spline Interpolation (Bezier-Kurven), Mittellinienmodellierung (treppenförmige Schattierungen) und Horizontalneigung (Neigewinkel nur bedingt variabel)</li>
<li>Linguistische Analyse: Silbenanalyse mit phpSyllable und morphologische Analyse (Vor-/Nachsilben, Stämme) mit Hunspell</li>
<li>Phonetische Transkription: eSpeak (IPA oder Kirshenbaum Alphabet)</li>
<li>Datenbank: Tabellen für fehlerhafte, korrigierte und richtige Stenogramme (Purgatorium, Elysium, Olympus) mit der Möglichkeit, diese in 
einem Trainingsmodus zu verwalten. 
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

<h1>Ariadne (Preview)</h1>
<p>Die nächste Version 0.2 wird den Namen <a href="http://de.wikipedia.org/wiki/Ariadne">Ariadne</a> tragen und enthält voraussichtliche 
folgende geplante Verbesserungen. Das Publikationsdatum ist offen*.</p>
<ul>
<li>Steno Engine: Integration ausgewählter Funktionalitäten der SE2, z.B.
<ul>
    <li>Variable, proportionale Schattierung (umgesetzt, Testphase)</li>
    <li>Automatisierte Umriss-Schattierung (Polygon-Modellierung) ab Daten der SE1 (umgesetzt, Testphase)</li>
    <li>Orthogonale und proportionale Knots mit parallelen Rotationsachsen (gemäss SE1 rev1, todo) 
</ul>
</li>
<li>Bugfixes und weitere noch nicht definierte neue Features</li>
</ul>
<p><i>(*) Als terminus a quo gilt im Zweifelsfall: wenn es fertig ist ... :)</i></p>
<a href="input.php"><br><button>zurück</button></a>

<?php require "vsteno_template_bottom.php"; ?>
