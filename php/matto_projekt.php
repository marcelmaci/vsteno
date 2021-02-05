<?php require "vsteno_template_top.php"; ?>
<h1>Matto regiert</h1>
<h2>Von der Idee zum Buch</h2>
<p><i>Wie entsteht eigentlich eine stenografische Ausgabe? Für alle jene, die sich diese Frage stellen, zeige ich im Folgenden, welche Etappen  
nötig sind: vom gemeinfreien Text über das Lektorieren bis hin zum fertig gedruckten Buch. Mit Ausnahme der Arbeiten am Programm VSTENO (z.B. 
der Korrektur falscher Regeln), sind dies Schritte, die auch jedem/r einzelnen zugänglich sind. In diesem Sinn: Wenn Sie selber ein 
stenografisches Buch herausgeben wollen: Nicht reden, tun! :)</i></p>

<h2>Text</h2>
<table>
<tr><td><a href="../web/matto_regiert_originalausgabe_umschlag.jpg"><img src="../web/matto_regiert_originalausgabe_umschlag.jpg" width="200"></a></td>
<td>
Als erstes braucht man für ein Buch natürlich Text. Verschiedene Szenarien sind denkbar: (a) man ist selber Autor/in (und verfügt über die 
entsprechenden Copyrights), (b) es existiert bereits eine gemeinfreie Textvorlage, die verwendet werden darf (z.B. 
<a href="http://www.gutenberg.org">www.gutenberg.org</a>) oder (c) es existiert zumindest eine gedruckte Ausgabe, dessen Copyright verfallen ist. 
<br><br>Bei <i>Matto regiert</i> war letzteres der Fall: Über ein Inserat konnte ich eine Ausgabe von 1943 auftreiben. Friedrich Glauser, der Autor, 
verstarb 1938. Damit ist in beiden Fällen also die <a href="https://de.wikipedia.org/wiki/Regelschutzfrist">Regelschutzfrist</a> von 70 Jahren 
eingehalten.
</td></tr></table>

<h2>Scannen</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_originalscan.jpg"><img src="../web/matto_regiert_originalscan.jpg" width="200"></a>
<a href="../web/matto_regiert_originalscan2.jpg"><img src="../web/matto_regiert_originalscan2.jpg" width="200"></a></td>
<td>
Da also kein gemeinfreier, elektronischer Text zur Verfügung stand, blieb nichts anderes übrig, als die erwähnte Ausgabe von 1943 Seite 
für Seite zu scannen. Da ich selber über keinen Scanner (mehr) verfüge - und die Sache auch einfacher und von der Qualität her ausreichend ist - 
habe ich mit einem Smartphone jeweils zwei Seiten abfotografiert.<br><br>Getreu meiner Überzeugung, dass es in unserer globalisierten Welt, 
wo einige wenige grosse Akteure den Markt kontrollieren, immer wichtiger wird, Freie Software zu verwenden, erfolgte dieser Schritt auf 
einem Samsung Galaxy S3 mit dem Betriebssystem Replicant (<a href="http://www.replicant.us">www.replicant.us</a>).
</td></tr></table>
    
<h2>OCR</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_ocr_tesseract.jpg"><img src="../web/matto_regiert_ocr_tesseract.jpg" width="200"></a></td>
<td>
Die fotografierten Seiten wurden anschliessend mit der - ebenfalls freien - Texterkennungssoftware 
<a href="https://de.wikipedia.org/wiki/Tesseract_%28Software%29">TESSERACT</a> in elektronischen Text umgewandelt. Bei diesem Schritt kommt es 
sehr darauf an, dass man (a) eine gute Vorlage verwendet (z.B. Seiten bei gleichmässigem, hellem Licht und möglichst flachen Seiten ohne 
Verzerrungen fotografieren) und (b) entsprechende Parameter (Textränder und Kontrast) so einstellt, dass eine möglichst hohe Korrektheit 
erreicht wird.</td></tr>
<tr><td colspan="2">Leider erreicht auch das beste (und TESSERACT ist ein sehr gutes!) Texterkennungsprogramm niemals 100% und so müssen die Fehler 
von Hand nachkorrigiert werden. Besonders perfid sind gewisse Zeichen (wie z.B. Anführungsstriche), welche zum Teil als verschiedene, optisch 
sehr ähnliche Zeichencodes erkannt werden (was dann später bei der Übertragung zu Fehlern führt). Eine wirklich gute Textvorlage zu erstellen 
benötigt somit schon mal einige Zeit (im Falle von <i>Matto regiert</i> dauert es letzen Endes etwas mehr als eine Woche).</p>
</td></tr></table>

<h2>Formatieren</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_formatierung_anhang.png"><img src="../web/matto_regiert_formatierung_anhang.png" width="200"></a></td>
<td>
Anschliessend muss der Rohtext formatiert werden, d.h. es müssen also Titel, Absätze, Schriftwechsel (z.B. Wechsel von Langschrift zur 
Kurzschrift mitten im Text) entsprechend markiert werden. <i>Matto regiert</i> enthält in der Langschrift-Ausgabe 278 Seiten, die in 26 Kapitel
aufgeteilt sind.</td></tr>
<tr><td colspan="2">Da der stenografische Text in der Regel etwas länger als sein langschriftliches Pendant ausfällt, wurde - um etwas Platz zu 
sparen - beschlossen, jeweils keine neuen Seiten zu beginnen, sondern die Kapitel fortlaufend, mit einem Zeilenabstand vor und nach dem Titel 
anzubringen.
</td></tr></table>

<h2>Korpus</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_korpus.png"><img src="../web/matto_regiert_korpus.png" width="200"></a></td>
<td>
Nun ging es erstmals ans Lektorieren von Stenogrammen:<br><br>Um möglichst effizient voranzukommen wurde deshalb vom Original-Text zuerst ein Wort-
Korpus erstellt. Für registrierte Nutzer/innen bietet hier VSTENO das Zusatzprogramm MKCOR: Man fügt hier also den kompletten, ursprünglichen 
Text ein und das Programm generiert daraus einen Liste von Wörtern, in der jedes Wort - bzw. jedes Wort und seine verschiedenen Formen - 
genau ein Mal vorkommt.<br><br>Um einen ungefähren Eindruck zu vermitteln: der Volltext von <i>Matto</i> regiert umfasst ca. 77'000 Wörter. Der Korpus 
hingegen lediglich ca. 10'000 Wörter.</td></tr>
<tr><td colspan="2">
Dieser Korpus wurde also in Stolze-Schrey übertragen und ein erstes Mal Korrektur gelesen. Das Lektorieren 
in dieser Form ist einigermassen anstrengend: erstens enthält diese Erstübertragung erfahrungsgemäss viele Fehler (da dem Programm noch eine ganze 
Reihe spezifischer Regeln fehlen, um alles fehlerfrei umzusetzen) und zum zweiten muss wirklich jedes Wort konzentriert gelesen werden. Dennoch 
ist es unter dem Strich weniger anstrengend 10'000 statt 77'000 Wörter (also nur etwa einen Achtel) zu lesen.
</td></tr></table>


<h2>Korrekturen</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_debug_ausgabe.jpg"><img src="../web/matto_regiert_debug_ausgabe.jpg" width="200"></a></td>
<td>
Die bei der Korpuslektorierung herausgeschriebenen, falschen Wörter müssen nun einzeln mit der Debug-Funktion von VSTENO analysiert 
werden.<br><br>Anschliessend werden entsprechend Regeln korrigiert oder ergänzt, damit die Wörter beim nächsten Mal richtig generiert werden. In vielen 
Fällen wird auch die Ästhetik der Stenogramme verbessert, z.B. indem Abstände angepasst oder zusätzliche Zeichen für bestimmte Zeichenkombinationen 
erstellt werden.<br><br>Da das entsprechende Modell DESSBAS/GESSBAS (= deutsches System Stolze-Schrey) inzwischen tausende von Regeln umfasst, ist auch 
diese Arbeit sehr langwierig und aufwändig.
</td></tr></table>

<h2>Lektorat I</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_e_reader.jpg"><img src="../web/matto_regiert_e_reader.jpg" width="200"></a></td>
<td>
Im Anschluss kann die eigentliche Phase des Lektorierens beginnen: Nun wird der gesamte Text mit VSTENO übertragen und auf einem E-Reader von der ersten 
bis zur letzten Seite Korrektur gelesen. Auch hier tauchen natürlich nach wie vor falsche Wörter auf.</td></tr>
<tr><td colspan="2"> Entweder wurden diese in der 
Korpus-Lektorierung übersehen oder aber gewisse Wörter, die als Einzelwörter nicht eindeutig in Steno übertragen werden können (z.B. Substantiv 
"Waren" vs Verb "waren"), können erst in dieser Phase erkannt werden. Auch die eingefügten Formatierungen (Seiten-, Zeilenumbrüche, 
Schriftwechsel etc.) können erst in dieser Phase überprüft werden.
</td></tr></table>

<h2>Anhang</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_anhang.jpg"><img src="../web/matto_regiert_anhang.jpg" width="200"></a></td>
<td>
Während all der Arbeit mit <i>Matto regiert</i> wurde mir klar, dass auch ein Anhang her musste, denn: Zum einen wollte ich sämtliche 
schweizerdeutschen Passagen ebenfalls stenografieren, zum anderen waren vielleicht gerade gewisse schweizerdeutsche Wörter und die ungewohnten 
Stenogramme in Schweizerdeutsch schwer zu lesen.<br><br>
Der Anhang sollte solche Wörter in einer allgemein verständlichen Form aufnehmen: 
schweizerdeutsches Wort in Langschrift, Übertragung in die Kurzschrift, Übersetzung des Wortes oder der Passage in Langschrift. Im Unterschied 
zum Hauptteil des Buches mit arabischen Seitenzahlen, sollte der Anhang mit römischen Zahlen nummeriert werden. Gerade diese Formatierungen 
(Zahlen und Schriftwechsel) waren einigermassen tricky hinzukriegen.</td></tr><td colspan="2">Ziel war es aber, am Schluss einen "Quelltext" zu haben, der den ganzen 
Buchinhalt inklusive Formatierung in einem Mal berechnen konnte.
</td></tr></table>

<h2>Vorrede</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_notwendige_vorrede_originalausgabe.jpg"><img src="../web/matto_regiert_notwendige_vorrede_originalausgabe.jpg" width="200"></a></td>
<td>
Die Ausgabe von 1943 beginnt gleich mit der Handlung in Kapitel 1. Zweifellos hätte man das auch in der Stenografie-Ausgabe so handhaben 
können. Allerdings wusste ich - aus anderen Ausgaben -, dass Glauser zusätzlich eine "Notwendige Vorrede" verfasst hatte, die ich einfach 
köstlich fand. Bei Wikipedia fand ich dann tatsächlich einen unter Creative Commons veröffentlichten 
<a href="https://de.wikipedia.org/wiki/Matto_regiert#/media/Datei:OeD_Matto.jpg">Abdruck</a> dieser Vorrede.</td></tr>
<tr><td colspan="2">Diese enthielt zwar einen 
kleinen Schönheitsfehler (m.E. enthält diese Version einen Druckfehler, da das Wort "Fussballklub" zwei Mal vorkommt; in anderen Ausgaben steht 
hier beim zweiten Mal der Name "Back"). Ich war versucht, dieses Wort "eigenhändig" zu korrigieren - aber es hätte streng genommen die Übernahme 
nicht gemeinfreier Elemente bedeutet - und so habe ich mich letztlich darauf beschränkt, eine Fussnote anzubringen.</p>
</td></tr></table>

<h2>Vorwort</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_lektorat_vorwort.jpg"><img src="../web/matto_regiert_lektorat_vorwort.jpg" width="200"></a></td>
<td>
Um die Ausgabe noch etwas zu veredeln, beschloss ich schliesslich, der Vorrede auch noch ein selbst verfasstes Vorwort voranzustellen. Die 
Aufgabe reizte mich ohnehin, da ich - seit ich, irgendwann in den 2000er Jahren vermutlich, einen ersten Glauser Roman in die Hände bekam - ein 
grosser Fan des Autors bin. Ich hatte also schon einige (ziemlich viele) Werke gelesen und wusste auch das eine oder andere über sein Leben. 
Überhaupt: Jetzt, wo ich diese Zeilen schreibe, kann ich - vielleicht zur Unterstreichung - vielleicht noch die Anekdote erwähnen, dass Glauser 
der Grund war, warum zwei Personen Jonny Cash hörend in einem grünen VW-Polo auch schon extra eine Reise nach Charleroi (Belgien) unternahmen, 
um dort, die Kohlebergwerke zu besichtigen, in denen Glauser - wieder einmal auf der Flucht vor den Behörden - gearbeitet hatte. </td></tr>
<tr><td colspan="2">Kurzum: Es war 
für mich eine Gelegenheit, wieder einmal etwas im "Reiche Glausers" zu recherchieren und an so etwas wie die Quintessenz des Romans zu gelangen. 
Für mich war dies zweifellos Glausers Kritik an den damaligen Psychiatern und Irrenanstalten und seinem Plädoyer für die Armen und Schwachen, also 
letztlich die Verrückten. Übrigens gibt es ein paar Stellen im Roman, die ich besonders mag: z.B. das Gespräch zwischen Studer und dem Obersten 
Caplaun ... speziell der Moment, als der Oberst endlich tscheggt, dass er Studer nicht zum ersten Mal begegnet ...; oder die Szene, wo Studer 
ganz am Schluss den Pieterlen mit dem lapidaren Satz "Man konnte nicht überall sein" laufen lässt. Item: Alles hatte in diesem Vorwort natürlich 
nicht Platz, trotzdem sollte es eine gebührende Einleitung für "Matto, den grossen Geist" sein; ein Geist, dem man m.E. nach wie vor huldigen 
darf, da er - so wie's aussieht - ja auch heute noch fröhlich die Welt beherrscht ... </p>
</td></tr></table>

<h2>Buchblock</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_kompletter_buchblock.png"><img src="../web/matto_regiert_kompletter_buchblock.png" width="200"></a>
<a href="../web/matto_regiert_titelblatt.jpg"><img src="../web/matto_regiert_titelblatt.jpg" width="200"></a>
</td>
<td>
Damit war der Roman nun praktisch komplett und es blieb nur noch das Übliche zu ergänzen, was ein Buch so benötigt: (a) Ein Titelblatt (das 
ich ebenfalls in Anlehung an die Ausgabe von 1943 mit einem Spinnennetz gestaltete) und (b) ein Impressum, in dem alle Quellen und rechtlichen 
Hinweise aufgeführt sind.<br><br>Beides wurde mit dem freien Programm LibreOffice erstellt und als PDF exportiert. Ergänzend füge ich am Schluss auch 
noch ein Inhaltsverzeichnis an, in dem die einzelnen Kapitel mit den entsprechenden Seitenzahlen aufgeführt sind. Ziel dieser Etappe war es, das 
komplette Buch mit VSTENO zu berechnen (was auf einem i5 mit 3.3 GHz ca. 38 Minuten dauert) und dann mit GhostScript die verschiedenen PDFs zu 
einem gesamten Buchblock zusammenzubringen (dies wird in der Hauptdokumentation unter "Längere Texte" im Zusammenhang mit PDFs beschrieben). 
<br><br>Auch diese Phase dauerte länger und war ein Hin- und her zwischen erneutem Überlesen, Anpassen von Formaten und Regeln, Neugenerieren des 
kompletten Buchblocks etc.</p>
</td></tr></table>

<h2>Testdruck</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_buchumschlag_version1.jpg"><img src="../web/matto_regiert_buchumschlag_version1.jpg" width="200"></a></td>
<td>
An diesem Punkt kann dann der Buchblock im Prinzip bei einer Druckerei eingereicht werden. Im Fall von <i>Matto regiert</i>, der nicht in 
grosser Auflage erscheinen wird, habe ich mich für ein BOD-Verfahren entschieden: Die Abkürzung bedeutet "book-on-demand" und ist eine 
Dienstleistung von Verlagen, welche über vollautomatisierte Produktionsstrassen verfügen, wo bereits ein einzelnes Buchexemplar direkt ab einem 
PDF gedruckt und gebunden werden kann.</td></tr>
<tr><td colspan="2">Meistens bieten die Druckereien auch ein Online-Tool an, um den Buchumschlag zu gestalten. Das korrekte 
Bemassen des Buchumschlags ist insofern etwas tricky, als dass der Buchrücken miteinberechnet werden muss (und dieser wiederum hängt von der 
Anzahl Seiten des Buchblocks, aber auch von der gewählten Papierstärke und der verwendeten Buchbindung (Klebebindung vs Fadenbindung). Aus 
Gründen der Bequemlichkeit habe ich hier alles im Online-Generator vorgenommen. Am geeignetsten und kostengünstigsten schien mir hier das 
Taschenbuchformat 12x19cm, mit gelbweissem 90gm Papier und Klebebindung. Den Umschlag gestaltete ich tiefschwarz - das schien mir für einen 
Roman, in dem ein nachmitternächtlicher Mord in den Heizungsräumen einer Irrenanstalt vorkommt, sehr passend.</p>
</td></tr></table>

<h2>Lektorat II</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_verworfener_testdruck.jpg"><img src="../web/matto_regiert_verworfener_testdruck.jpg" width="200"></a></td>
<td>
Anfang Dezember 2020 - die Arbeiten an Matto regiert hatten irgendwann im September begonnen - hielt ich dann, endlich, das erste Exemplar 
von <i>Matto regiert</i> in den Händen.<br><br>Das Resultat war allerdings mehrfach enttäuschend: Der Buchumschlag, der auf dem Bildschirm so gut 
ausgesehen hatte, sah gedruckt katastrophal aus. Aufgrund der schwarzen Farbe und der glänzenden Oberfläche spiegelte sich das Licht in einem 
sehr unästhetischen Wirrwarr von Strichen, welche die Produktionsmaschinen hinterlassen hatten. Ausserdem war der Buchdeckel, aufgrund der 
Buchdicke (und vermutlich automatischer Packmaschinen) am Rand geknickt worden.</td></tr>
<tr><td colspan="2">Auch die Strichdicke der Stenogramme war durchgehend zu dünn. 
Ich hatte mich hier zwar auf die Werte aus dem ersten Buch (<i>Der Widerspenstigen Zähmung</i>) gestützt, aber aufgrund einer leicht geänderten 
Schriftgrösse, war das Resultat unbefriedigend. Zu guter Letzt fiel mir auch noch auf, dass die neu eingeführte Funktionalität zur 
automatischen Handhabung von Titelumbrüchen falsch gearbeitet hatte. Kurzum: Das ganze Buch musste noch einmal komplett überarbeitet werden.
</td></tr></table>

<h2>Warten</h2>
<table>
<tr><td width="200"><a href="../web/matto_regiert_1.jpg"><img src="../web/matto_regiert_1.jpg" width="200"></a></td>
<td>
Am 28. Dezember 2020 war es schliesslich soweit und ich bestellte die - hoffentlich - letzte gedruckte Vorversion zur Kontrolle. Bereits 
bei der ersten Version hatte mich der Verlag informiert, dass - bedingt durch Weihnachtsbestellungen und Corona - mit längeren Lieferfristen 
zu rechnen sei. Bei der ersten Version waren es ca. 2 Wochen gewesen.<br><br>Nun aber war Warten angesagt: Am 7. Januar erhielt ich die 
Bestellbestätigung, am 14. Januar den Hinweis, dass man mich nicht vergessen habe ... Es brauchte also einiges an Geduld, aber am 1. Februar 2021 
lag schliesslich das langersehnte Päckchen im Briefkasten: 375 gr, 380 Seiten.</td></tr>
<tr><td colspan="2">Nach der langen Warterei hoffte ich natürlich inständig, dass der Buchumschlag und die Schrift 
diesmal besser aussähen - und wurde wahrlich nicht enttäuscht: Ehrlich gesagt hat mich das Resultat selber weggehauen, ich hatte wirklich 
nochmal alles daran gesetzt, eine wirklich ästetisches Top Druck-Exemplar zu gestalten, aber dass es dann 
<a href="matto_regiert_details.php">SOOOO schön</a> aussah, hätte ich mir selber nicht träumen lassen.</p>
</td></tr></table>
 
<h2>Kleinauflage</h2>
<p>Auch bei BOD-Projekten ist es so, dass die Produktion günstiger wird, je mehr Bücher man aufs Mal bestellt. Ausserdem kann man die Kosten 
auch optimieren, indem man die Versandgewichte optimal ausschöpft. Theoretisch ist es möglich, BOD-Bücher direkt über den Verlag zu 
verkaufen, aber ich erachte dies als die schlechtere Option: erstens sind die Bücher dann teuerer, zweitens muss man je nachdem lange auf die 
Bücher warten und drittens, besteht - gerade bei dicken Büchern wie <i>Matto regiert</i> ein gewisses Risiko, dass Buchdeckel geknickt werden 
(was beim Paketversand einer Kleinauflage nicht passiert). Ich gehe somit, zumindest in der Schweiz, den Weg einer Kleinauflage, die ich 
selber versende.
</p>

<h2>Finanzielles</h2>
<p>Mein Ziel mit den BOD-Büchern wäre im Prinzip, dass die Kosten zumindest selbsttragend sind. Mit dem ersten Buch - <i>Der Widerspenstigen 
Zähmung</i> - dürfte ich dieses Ziel, dank ausreichender Bestellungen und Stückzahlen, voraussichtlich knapp erreichen. Mit der Vorbestellung 
einer ersten Kleinauflage von <i>Matto regiert</i> drücke ich die Bilanz der Jahresrechnung von VSTENO (die von April zu April läuft) wieder 
ziemlich ins Minus (aktuell etwa CHF 930.- Verlust). Mir persönlich ist das im Prinzip egal: Ich betreibe VSTENO als Hobbyprojekt und 
bin damit sogar bereit, solche Kosten zu tragen. Aber ich will dies der Transparenz halber doch anfügen - falls jemand das Gefühl hat, ich 
verdiene mir hier eine goldene Nase ... oder CHF 25.- + 7.- Porto für ein Buch sei zu teuer oder so ... (mit seinen 375 gr geht Matto regiert 
leider nicht mehr unter "Brief", sondern "Paket":) Trotz dieser "à fonds perdu"-Rechnung hätte ich natürlich auch nichts dagegen, wenn das 
Projekt nicht nur keinen Verlust, sondern allenfalls sogar etwas Gewinn macht. Ich diesem Sinne: Sollten Sie zum Schluss kommen, dass dieses 
Projekt ihre finanzielle Unterstützung verdient, so bedanke ich mich bereits jetzt für eine <a href="donation.php">Spende</a>.</p>

<?php require "vsteno_template_bottom.php"; ?>
