<?php require "vsteno_template_top.php"; ?>
<h1>Versionen</h1>
<p>Diese Seite gibt einen Überblick über das Release-Modell von VSTENO sowie die offiziellen Versionen</p>
<h2>Releases</h2>
<p>VSTENO bietet zwei Release-Modelle</p>
<ul>
<li><p><b>Rolling (fortlaufend)</b>: Hier kann die jeweils neueste Version ab Github-Repository installiert werden, wobei es zwei Varianten gibt: (a) latest 
hier wird die allerneuste Version installiert) oder (b) latest stable (hier wird die letzte "stabile" - d.h. garantiert lauffähige - Version 
installiert). Zusätzlich bietet der Installer von VSTENO auch die Möglichkeit, jeden beliebigen Commit aus dem Github-Repository zu installieren. 
In allen Fällen gilt, dass diese Version nicht offiziell released wurden und somit als experimentell zu betrachten sind. Ihr Vorteil liegt darin,
dass sie die jeweils neuesten Features und Entwicklungen von VSTENO enthalten.</p></li>
<li><p><b>Fixed (eingefroren)</b>: Eine fixe Veröffentlichung entspricht einem Snapshot - also einem bestimmten Commit - innerhalb des 
Github-Repositories. Bei einer fixen Version gilt: (a) sie ist lauffähig und getestet, (b) sie wird mit Anmerkungen (release notes) veröffentlicht 
und enthält eine aktuelle Dokumentation und (c) die enthaltenen stenografischen Modelle wurden ebenfalls so weit als möglich vorangetrieben, 
getestet und dokumentiert.</p></li>
</ul>
<p>Grundsätzlich wird empfohlen, die Version latest stable zu installieren: Dadurch erhalten Sie jeweils auch die neuesten Versionen der 
überarbeiteten stenografischen Modelle.
</p>
<h1>Versionen</h1>
<p>Jede offizielle Version von VSTENO erhält eine zweistellige Versionsnummer und einen Versionsnamen. Letzterer wird aus der griechischen 
Götterwelt entnommen. Im folgenden die bis jetzt veröffentlichten, offiziellen Versionen.</p>
<div id="versions">
<table>
<tr>
<td>Nummer</td>
<td>Name</td>
<td>Datum</td>
<td>Anmerkungen</td>
</tr>
<tr>
<td>0.1</td>
<td>Hephaistos</td>
<td>Juli 2019</td>
<td><a href="release_notes.php">release notes</a></td>
</tr>
<tr>
<td>0.2</td>
<td>Ariadne</td>
<td>November 2019</td>
<td><a href="release_notes.php">release notes</a></td>
</tr>
<tr>
<td>0.3</td>
<td>Hyperion</td>
<td>März 2020</td>
<td><a href="release_notes.php">release notes</a></td>
</tr>

</table>
</div>

<h1>Models</h1>
<p>Linguistische Modelle (stenografische Systeme) haben ihrerseits Versionsnummern. Dabei ist zu unterscheiden zwischen:</p>
<ul>
<li><p><b>Version</b>: Entspricht der Session-Variablen <i>model_version</i> und gibt die Versionsnummer - d.h. den Entwicklungsstand - 
des Modells an.</p></li>
<li><p><b>Required</b>: Entspricht der Session-Variablen <i>required_version</i> und gibt die Versionsnummer von VSTENO, die das Modell 
mindestens benötigt.</p></li>
</ul>
<p>Grundsätzlich sollen neuere Versionen von VSTENO abwärtskompatibel sein, d.h. ältere Modelle sollen auch von neueren Programmversionen 
korrekt abgearbeitet werden. Neuere Modelle - die Funktionalitäten und somit auch Formalismen verwenden, welche in älteren Versionen noch 
nicht vorhanden waren - können jedoch mit älteren Versionen nicht ausgeführt werden (und führen zu einer Fehlermeldung).</p>
<h1>Entwicklung</h1>
<p>Um die Sache zu vereinfachen und Verwirrungen zwischen Versionsnummern zu vermeiden, verwendet VSTENO nur einen Entwicklungsstrang sowohl 
für das Programm als auch für die linguistischen Modelle. Dadurch ist sichergestellt, dass bei der Installation von latest stable jeweils 
(a) kompatible und (b) die neuesten Versionen der enthaltenen Modelle installiert werden.</p> 
<a href="input.php"><br><button>zurück</button></a>

<?php require "vsteno_template_bottom.php"; ?>
