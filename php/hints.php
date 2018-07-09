<?php require "vsteno_template_top.php"; ?>
<h1>Tutorial</h1>
<p>Diese Seite ist als Kurzanleitung gedacht, damit Sie VSTENO ohne grosse Vorarbeit ausprobieren und ein paar erste, elektronische Texte in Steno übertragen können. 
Für komplexere Anwendungsbereiche (insbesondere z.B. das Generieren von PDFs zum Ausdrucken der Stenogramme) finden Sie weitere Informationen unter <a href="documentation.php">
Dokumentation</a>.</p>
<h2>Mini-Version</h2>
<p>Am einfachsten ist die <a href="mini.php">Mini-Version</a>: Hier müssen Sie nur den zu übertragenden Text in einem Textfeld eingeben und auf "abschicken" klicken.</p>
<h2>Maxi-Version</h2>
<p>Die <a href="input.php">Maxi-Version</a> enthält sämtliche Gestaltungsmöglichkeiten auf einer Seite an und ist damit weniger übersichtlich. Sämtliche Standardformatierungen (wie sie 
auch von der Mini-Version verwendet werden) sind vorselektiert, sodass Sie also auch hier gezielt ausprobieren und einzelne Optionen anpassen können. Mit dem Button "zurücksetzen" unterhalb
des Formulars können Sie jederzeit zu den Standard-Einstellungen zurückkehren.</p>
<p><i><b>Tipp:</b><br>Sie können jederzeit von der Mini- zur Maxi-Version (und wieder zurück) wechseln. Dabei bleiben alle Einstellungen erhalten. Dies gibt Ihnen zum Beispiel die Möglichkeit,
die gewünschten Optionen mit der Maxiversion einzustellen, und danach auf die Mini-Version zu wechseln, um verschiedene Texte mit den gleichen Einstellungen übertragen zu lassen.</i></p>
<h2>Sonderzeichen</h2>
<p><u>Zeichen |:</u> Deutet an, dass ein zusammengesetztes Wort aus mehreren Wortern besteht. Dadurch können Sie Konsonantenabfolgen klarer gruppieren, also z.B. "Eulen|spiegel"
vs "Lebens|partner": Im ersten Fall "n+sp" gruppiert, im zweiten "ns+p". VSTENO schreibt diese Wörter zusammen.</p>
<p><u>Zeichen \:</u> Gleiche Bedeutung wie |, aber die Wörter werden abgetrennt, eng aneinander geschrieben. Dies eignet sich für Wörter mit starker mehrfacher Hoch- oder Tiefstellung, z.B.
"Monochrom|okular": VSTENO schreibt hier also das Wort "monochrom" (3x Tiefstellung), setzt dann ab und beginnt auf der Grundlinie neu mit dem Wort "Okular" (2x Tiefstellung).
<p>
<?php require "vsteno_template_bottom.php"; ?>