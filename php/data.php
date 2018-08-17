<?php

/* VSTENO - Vector Steno Tool with Enhanced Notational Options
 * (c) 2018 - Marcel Maci (m.maci@gmx.ch)
 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 */
 
 
/*
14. August 2018: New rule formalism

Either:    A => B
Or:        A => array(a, b, c, d ...)
where:     a is the original B (i.e. replacement for REGEX)
           b, c, d ... are exceptions: if one of the matches, the rule won't be applied
           
           can be used for example for word "geschäft": 
           define a rule which replaces "schäft" => "{SCHAFT}"
           define exception "geschäft" (rule shouldn't be applied to that word)

           in PHP:
           "schaft$ => array( "{SCHAFT}", "Geschäft(en?)?" )

           Advantages:
           (1) exceptions can be indicated together with rules (more logical and better to understand)
           (2) possible to use REGEX also for exceptions (big plus)
           
           Performance-whise this should also be beneficial since exceptions are only tested if first part of rule matches
           (in the old version, every exception was tested on every word).
*/
 
/////////////////////////////////////// data definitions /////////////////////////////////////////////////////

// define decapitalizer (changes all uppercase caracters to lower case => marks works starting with uppercase as "nouns" in order to show them in different color (not yet implemented))
// see function that uses mb_strtolower (no table)


// define globalizer

$globalizer_table = array(
    //"^(.+)\.\.\." => "$1\\...",            // resolve multiple punctuation problem (= end vowels missing if not separated ... ")
    //"^[\[(]" => "[#BO#]\\",        // opening brackets
    //"[\])]$" => "\\[#BC#]",        // closing brackets
    //"[\n]" => "<br>",
    // the following lines convert html-chars => should be put into a special parser (e.g. "filter") which can be selected/deselected as an option in the input-form
 
    // the following is all rubbish ... do this for every word separately using the function html_entity_decode() => only leave those who replace chars the engine can't handle, e.g. &ndash; => -
    // IMPORTANT: globalizer - despite it's name - isn't applied to the whole text (but to single words)
    "&ndash;" => "-",
    "&mdash;" => "-",
    "&uuml;" => "ü",
    "&auml;" => "ä",
    "&ouml;" => "ö",
    "&Uuml;" => "Ü",
    "&Auml;" => "Ä",
    "&Ouml;" => "Ö",
    "&raquo;" => "\"",
    "&laquo;" => "\"",
    "&rsquo;" => "'",
    "&lsquo;" => "'",
    "&ldquo;" => "\"",
    "&rdquo;" => "\"",
    "&quot;" => "\"",
    "&hellip;" => "...",
    "#hellip#" => "...",
    
    "&nbsp;" => " ",
    "&deg;" => "°", 
    "&szlig;" => "ss",
    /*
    "&ccedil;" => "c",
    "&ntilde;" => "n",
    */
    /*
    // accents (simplify)
    "&agrave;" => "a",
    "&egrave;" => "e",
    "&igrave;" => "i",
    "&ograve;" => "o",
    "&ugrave;" => "u",
    
    "&aacute;" => "a",
    "&eacute;" => "e",
    "&iacute;" => "i",
    "&oacute;" => "o",
    "&uacute;" => "u",
    
    "&acirc;" => "a",
    "&ecirc;" => "e",
    "&icirc;" => "i",
    "&ocirc;" => "o",
    "&ucirc;" => "u",
    */
    
    // use the following for word separation with -
    "-" => "|[~~]\\",
    //"><" => "> <",                  // test (with this rule inline- and html-tags can be placed everywhere)
    //"&(quot|QUOT);" => "\"",
   // "\n" => "<br>",                        // do some basic formatting (doesn't work)
);

// define helvetizer
$filter_table = array(
    // use this to filter out special characters that the program can't handle
    "»" => "",
    "«" => "",
    
    "&.*;-" => "", // filter out all escaped character from htmlspecialchars
    //"\uxC2" => "",
);

$helvetizer_table = array (
    "ß" => "ss",
    
);

// trickster is applied before decapitalizer ?!? (maybe that's not good ...)
// the trickster tries to avoid some automatical changes done by the parserchain (especially shortings by the shortener)
$trickster_table = array(
    
     //"([Vv])orzurücken" => "{VOR}{ZU}[AR]ück{EN}",
     //"([Hh])ierauf" => "hier{AUF}",
     "([Dd])ahinter" => "{DA}[H][N][&TVR]",
     //"(Ü|ü)berdies" => "{ÜBER}{DIS}",
     "([Dd])urchaus" => "{DURCH}{AUS}",
     "([Jj])edem" => "$1e[D]{EM}",
     "([Gg])eradem" => "$1era[D]{EM}",
     "[Dd]asein" => "{DA}s[EI]n",
     "^([Oo])dem" => "[0N-]$1[D]em",
     "^([Ee])rheiter" => "{ER}h[EI]t{ER}",
     "^([Bb])eb(en|s?t)" => "$1Eb$2",
     "([Hh])ohn(.+)" => "$1ohn|$2",
     "([Pp])astete" => "$1ast[E]te",
      "^([Ff])röhlich" => "$1rö[H]lich", 
     "^([Rr])ohh?eit" => "$1o[H]{HEIT}",  // can be written in two ways in steno (with &O or H)
     "^([Uu])ngetüm" => "$1nge[T]ü[M]",
     "^([Mm])isser" => "$1iss{ER}",
     "([Dd])iskret" => "$1iskr[E][T]",
     "([Uu])nbeirrt" => "{UN}{BE}irrt",
     //"([Ag])gent" => "$1g[E]nt",
     "([Rr])echtz" => "$1e[CH][&T][Z]",
     //"([Qq]u)ant" => "$1[A]nt",
     //"([Kk])ent" => "$1[E]nt",      // shortening "ANT" produces too many exceptions that have to be corrected by trickster => other solution needed
     //"([Kk])ant" => "$1[A]nt",
     //"([Pp])ant" => "$1[A]nt",
     "([Hh])aftbar" => "$1[A]ftbar",
     "([Ww])eltall" => "$1elt{ALL}",
     "([Jj])enseits" => "$1en[S]eits",
     "([Zz])entr" => "$1[E]nt[R]",
     "([Zz])ent" => "$1[E]nt",
     "Variete" => "var[#N][&I][E]te", // should go into dictionary ?!
    // "Uri" => "[0N-][U]r[#N][&I]", // should go into dictionary ?!
     "Arie" => "[0N-][A]r[#N][&I][-E]", // should go into dictionary ?!
     "([Ss])een" => "$1e[#W][&E]en",
     "pfent" => "pf[E]nt[&E]",
     "[fF]alsch" => "fAlsch",
     "([MmLl])e[eh]r" => "$1[E]r",
     "gebe" => "Gebe",
     "\[qQ\]uai" => "qu[&A]i",
     "\[mM\]eter" => "m[E][T]er",
     "û" => "ü",
     "é" => "e",
     "bete" => "b[E]te",
     "Hals" => "H[A]ls",
     "^besten$" => "best{EN}",
     //"[sS]ten" => "st[E]n",   // wrong in "testen"    
     "[aA]usspr" => "{AUS}spr",
     "ausst" => "{AUS}st",
     "[Mm]ante" => "m[A]nte",
     "okument" => "okumEnt",
     "beizu" => "{BEI}{ZU}",
     "[Bb]eiz" => array("B[EI]z", "[Bb]eizeit"),
     "[Ss]erviet" => "servIEt",
     "schin" => "[SCH]in",
     "[Aa]uster" => "[AU]ster",
     "mitinhab" => "mit{IN}hab",
     "([Bb])esser" => "$1Esser",
     "([Bb])enzin" => "$1Enzin", // avoid shortening
     "([Zz])uch" => "z[U][CH]",
     "([Zz])uck" => "z[U][CK]",
     "([Gg])eld" => "$1[E]ld",
     "[Ww]ieso" => "{WI}|[0N-][SS]",
     "([Zz]u)dem" => "{Z}{EM}",
     "([Ii]n)dem" => "{IN}{EM}",
     "([Vv]or)dem" => "{VOR}{EM}",
     "([tr])los$" => "$1[L]os", // avoid bundling tl, rl => [TL], [RL] if ending is los (like "wertlos", "wehrlos")
     "([aeiouäöü])ther$" => "$1t[H]er", // avoid normalizing (= falling out) of h in ending her (like "seither")
//     "thin$" => "t[H][N]", // avoid normalizing (= falling out) of h in ending her (like "letzthin")
//     "thaft" => "t{HAFT}", // avoid normalizing (= falling out) of h in ending her (like "gesamthaft")
     //"[Hh]all" => "[$1][A][LL]", // avoid shortening "all" and falling out of "h"
     //"hall" => "ha[LL]", // avoid shortening "all" and falling out of "h"
    
);

// define lexical entries in dictionary
$dictionary_table = array (  
                        
                       "Athen" => "[0N-][A][T][H][E][N]",
                       "Athens" => "[0N-][A][T][H][E][NS]",
                       "Zuge" => "ZUG[-E]",
                       "Anton" => "[0N-][A][N][&T][&E][O][N]",
                       "eins" => "[0D-][EI][NS]",
                       "Familie" => "FAMIL[#N][&I][-E]",
                       "Familien" => "FAMIL[#N][&I][EN]",
                       
                       "ungern" => "[#NS][&U][G][E][VR]N",
                       "sofort" => "[0N-][SS][FORT]",
                       "wodurch" => "[WO][DURCH]",
                       "wofür" => "[WO][F]",
                       "wogegen" => "[WO][GEGEN]",
                       "seitdem" => "[0N-][S][HEIT][&E][EM]",
                       "wozu" => "[WO][Z]",
                       "wo" => "[WO]",
                       "er" => "[0N-][E][VR]",
                       "etwas" => "[0N-][#W][T][WAS]",
                       "etwa" => "[0N-][#W][T][W][A][&A]",
                       "wir" => "[W]",
                       "union" => "[0N-][U][N][ION]",
                       "denen" => "[0N-][EN][O][&E][0N-][EN]",
                       "was" => "[WAS]",
                       "gegend" => "[G][E][G][E][ND]",
                       "umso" => "[0N-][U][M][0N-][#N][SS]",
                       "karriere" => "[K][A][RR][&I][E][VR][-E]",
                       "beste" => "[B][E][ST][-E]",
                       "oder" => "[0N-][O][D][#N][VR]",
                       "somit" => "[0N-][#N][SS][#N][M]",
                       "all" => "[ALL]",
                       "allein" => "[ALL][EI][N]",
                       "alleine" => "[ALL][EI][N][-E]",
                       "ihn" => "[0D-][I][N]",
                       "ihm" => "[0D-][I][M]",
                       "so" => "[0N-][SS]",
                       "sogar" => "[0N-][#N][SS][G][A][VR]",
                       "also" => "[0N-][A][L][SS]",
                       "beret" => "[B][E][VR][E][T]", // béret doesn't work as key ...
                       "ebenso" => "[0N-][E][B][E][N][SS]",
                       "sogleich" => "[0N-][SS][G@L3][EI][^CH]",
                       //"sozusagen" => "{so}zsagen",
                       //"soweit" => "{so}w{heit}",
                       //"solange" => "{so}lange",
                       //"worauf" => "{wor}{auf}",
                       //"wonach" => "{wo}nach",
                       //"wohin" => "{wo}hn",
                       "daraus" => "[DA@R][AUS]",
                       "darin" => "[DA@R][IN]",
                       "überaus" => "[0N-][#WS][&U][AUS]",
                       "Schaft" => "[SCH][A][F][&T]",
                       "beim" => "[B][EI][M]",
                       "dabei" => "[DA][BEI]",
                       "beim" => "[B][EI][M]",
                       "salbei" => "[0N-][S][A][L][B][-EI]",
                       "dessen" => "[D][E][SS][EN]",
                       "es" => "[0N-][#N][-E]",      
                       "pforte" => "[PF][O][R][&T][&E][-E]",
                       "dafür" => "[DA]F",
                       "dagegen" => "[DA][GEGEN]",
                       "dahin" => "[DA]HN",
                       "insofern" => "[IN][SS][F][E][VR][N]",
                       "ins" => "[0D-]I[NS]",
                       "sich" => "[SICH]",
                       "daselbst" => "[DA][SELB][ST]",
                       "bevorzugen" => "[B][VOR][Z]UG[EN]",
                       "zug" => "ZUG",
                       "da" => "[DA]",
                       "Wien" => "[W][&I][E][N]",
                       "hier" => "[H][I][VR]",
                       "beissen" => "[B][EI][SS][EN]",
                       "bellen" => "[B][E][LL][EN]",
                       "zu" => "[Z]",
                      
                    );
              
// define abbreviations, prefixes and suffixes
$shortener_table = array    (   
                            
                            "wil$" => "WIL",
                            "los(igkeit)?$" => "Los$1",
                            "^[Uu]nbeirr" => "{UN}{BE}irr",
                            "ie([mn])$" => "[&I]e$1",
                            "ietät" => "[&I]{HEIT}",
                            "t(u|ü)mlich" => "{TUM}{LICH}",
                            "^[Bb]erück" => array("{BE}{RÜCK}", "[Bb]erück[et]"),
                            "[Uu]nbeein" => "{UN}{BE}[EI]N",
                            "eitlich" => "{HEIT}{LICH}",
                            "eitig" => "{HEIT}ig",
                            "eitens" => "{HEIT}ens", 
                            "keits" => "{HEIT}s",
                            "iet$" => "[I]T",
                            "solch" => "{SOLCH}",
                            "^und\$" => "{UND}",
                            "usw." => "{USW.}",
                            "usw" => "{USW}",
                            "usf" => "{USF}",
                            "vielleicht" => "{VILLEICHT}",
                            "von" => array("{VON}", "Yvonne"),
                             "^war(st|en)?$" => "{WAR}$1",
                            "^wär(e?st|e|en|et|t)?$" => "{WÄR}$1",
                            "welch" => "{WELCH}",
                            "wenn" => "{WENN}",
                            "^werd" => "{WERD}",
                            "^wie$" => "{WI}",     // ! is wrong!
                            "wird" => "{WIRD}",
                            "vor" => "{VOR}",
                            "fort" => "{FORT}",
                            "eiht" => "eit",        // according to manual, geweiht is written geweit (phonetically)
                            "usw" => "{USW}",
                            "usf" => "{USF}",
                            "[Pp]lanet" => "[P@L][A][N][E][T]",
                            "etektiv" => "[E]Tektiv",   // avoid schortening et in "Detektiv"
 //                           "([Pp])flicht" => "[$1F@L]icht",
                            "zusammen" => "{ZUSAMMEN}",
                            "nieder" => "nied{ER}",
                            "f(o|ö)rder" => "f$1rd{ER}", 
                            "^unange" => "{UN}an{GE}",
                            "^unt([^e])" => "{UN}t$1",
                            "^(dar|wor|her|hin|hier)?auf" => "$1{AUF}",
                            "(?<!sc)haft(e|en|es)?" => "{HAFT}$1",
                            "gegen" => "{GEGEN}",
                            "zum" => "Zum",
                            "geheim" => "{GE}heim",
                            "etz" => "ez",
                            "^woher" => "{WO}her",
                            "ndwo$" => "nd{WO}",
                            "trotzdem" => "trotz{DEM}", 
                            "(s|m)ehr" => "$1Er",
                            "^beid(e[n|r|s]?)" => "[B]eid$1",
                            "(?<!f)all(e[n|m|s]?)$" => "{ALL}$1",
                            "^all" => "{ALL}",
                            "mal(s)" => "MAL\$1",
                            "^[Aa]ls$" => "{ALS}",
                            "^zurückzu" => "{ZURÜCK}{ZU}",
                            "zurück" => array ("{ZURÜCK}", "[Vv]orzurück*" ),
                            "(\[^a-z\])all" => "{\$1ALL}",
                            "^auch$" => "{AUCH}",
                            "^aussen" => "au[SS]en",
                            "^ausser" => "au[SS]er",
                            "^auss([aeiou])" => "{AUS}s$1",
                            "bei(nahe|zeit(en)?)" => "{BEI}$1",
                            "bein" => "[B][EI][N]",
                            "^darf" => "[D]arf",
                            "^dar" => "{DA@R}",
                            "dast(.+)" => "{DA}st$1",
                            "dasselb" => "{DASS}{SELB}",
                            "dass?(?!t)" => "{DASS}",
                            "^dem" => "{DEM}",
                            "(?<!n)(nach|zu)?dem$" => array("$1{DEM}", "Odem"),
                            "^denn" => "de[NN]",
                            "denk" => "de[NK]",         // avoid shortening of "denker, denken" etc.
                            "^den" => "{DEN}",
                            "^des" => "{DES}",
                            "^dies" => "{DIS}",
                            "dies$" => array("{DIS}", ".*[Pp]aradies"),
                            "^die$" => "{DI}",
                            "durch" => "{DURCH}",
                            "fürch" => "Fürch",
                            "für(?!st)" => "{FÜR}",
                            "gehabt" => "{GEHABT}",
                            "hab" => "{HAB}",
                            "^[Hh]att?" => "{HAT}",
                            "^chin" => "[CH]in",       // avoid shortening "hin" in "chin"
                            "^hinzu" => "{HIN}{ZU}",
                            "^in$" => "{IN}",
                            "^ist$" => "{IST}",
                            "mitt" => "m1i2t3t4",           // convert mitt to something else
                            "mit" => array("{M}", "Eremit*"),                   // perform mit substitution
                            "m1i2t3t4" => "mitt",           // convert mitt back
                            "^nicht" => "{NICHT}",
                            "^sie$" => "{SI}",
                            "^[Ss]ind$" => "{SIND}",
                            "(?<!^)lich(s?t?e?[mn]?|ere?[mn]?|keit(en)?|es)?$" => array("{LICH}$1", "Pflicht(en)?"),  // testregel
                          
                            
                            "^(.?)(eit)(et?(re?)?[nms]?)?" => "$1{HEIT}$3",
                            "([hk]?eit)(et?(re?)?[nms]?)?$" => "{HEIT}$2",
                            "([ie]?tät)(en)?$" => "{HEIT}$2",
                            "bei(?!l)" => "{BEI}",
                            "geh" => "Geh",
                            "^([Mm])iss" => "$1i[SS]",
                            "([Hh])inter" => "HN[&TVR]", 
                             "^([Hh])ing$" => "$1I[NG]",
                            "(?<!sc)hin(?![dz])" => "{HIN}",
                            "tret" => "trEt",
                            "^gebe" => "Gebe",
                            "^gern" => "Gern",
                            "^erd" => "[0N-]Erd",
                            "^angab" => "aNgab",
                            "heraus" => "her{AUS}",
                            "rosen" => "roseN",
                            "^zur$" => "[Z]ur",
                            "^uns(er.*)?$" => "u[NS]$1",
                           //"Uri" => "[0N-][U]r[#N][&I]", // should go into dictionary ?!
                           "(^|\|)({?des}?)?inter" => "$1$2{INTER}",
                           "^({?des}?)?[Ii]n([^n])" => "$1{IN}$2",
                            "damit" => "{DA}{MIT}",
                            "davon" => "{DA}{VON}",
                            "dazu" => "{DA}{ZU}",
                            "damals" => "{DA}mals",
                           "über" => "{ÜBER}",
                            "dieselb" => "{DI}{SELB}",
                            "selb" => "{SELB}",
                            "^der" => "{DER}",
                            "unter" => "{UNTER}",
                            "(^|\|)(((un)?(ver|be|über))|(gegen|neben|rück|fehl|ur|nach|vor|voll))?[ae]nt([^i])" => "$1$2{ANT}$7",
                            "^unent" => "{UN}{ANT}",
                            "^aufent" => "{AUF}{ANT}",
                            "^(ein|auf|bei|an|vor|ab)?zuer" => "$1{ZU}{ER}",
                            "^[Zz]uver" => "{ZU}{VER}",
                            
                            "^(ein|auf|bei|an|vor|ab|aus|durch|dar|hervor|mit|vorher|nieder|zusammen)zu(?![mrc])" => "$1{ZU}",
                            "^[Zz]u(?![mrc])" => array("$1{ZU}","Zuoz"),
                            "^([Zz])ur([aeiouäö])" => "{ZU}r$2",
                            
                            "^([Nn])achzu" => "$1ach{ZU}",
                             //"ietät" => "[&I]{HEIT}",   //////////////// ?!?
                            "^[Gg]ea([bcdfghjklmnpqrstvx])" => "{GE}a$1",
                            "^[Bb]eur" => "{BE}{UR}",
                            "^[Bb]eun" => "{BE}{UN}",
                            "^[Rr]ückver" => "{RÜCK}{VER}",
                            "^[Rr]ücker" => "{RÜCK}{ER}",
                            "^[Rr]ück" => array("{RÜCK}", "[Rr]ücke"),
                            
                            "^[Mm]itver" => "{M}{VER}",
                            "^([Mm]iss|[Kk]riminal|[Ee]in)?ver" => "$1{VER}",
                            "(^|\|)[Aa]nti[kc]on" => "$1{ANTI}{C}",
                            "(^|\|)[Zz]uver" => "$1{Z}{VER}",
                            "wie?der" => "{WIDER}",
                            "([AEIOUaeiouäöü\]+[bcdfghjklmnpqrstvwxyz]*)et(e?|e(n|s)?)$" => array("$1{ET}\$2", "[Rr]aket"), // only multisyllabic words
                            "erer([se]?[nm]?)$" => "{ER}{ER}$1",
                            "rr" => "[RR]", // avoid shortening {er} in following rule
                            "([Bb])ech" => "$1Ech",
                            "(w|qu)er" => "$1Er", // temporary substitution for preceeding rule that doesn't work ...
                            "(?<!i)er(?=t?e?[nmrs]?$)" => "{ER}",
                            "erei(en)?$" => "{ER}ei$1",
                            "\[RR\]" => "rr", // set [rr] back to rr
                            "qu" => "q",                                                // makes it easier to handle => normally, should go to a separate, preceeding parser function, e.g. "simplifier"
                            "(?<!sc)([Hh])all" => "[$1]a[LL]", // avoid shortening "all" and falling out of h
      /*                      
                            "th" => "t",                                                // should be done in a separate part (e.g. "prenormalizer")
                            "ä([eaio])" => "ä[&A]$1",                                   // easier to do here, but should go to separate parser
                            "ö([aeiou])" => "ö[&O]$1",                                   // easier to do here, but should go to separate parser
                            "a([aeio])" => "a[&A]$1",                                   // easier to do here, but should go to separate parser
                            "o([aeiu])" => "[&O]$1",                                   // easier to do here, but should go to separate parser
                            "ü([aeiou])" => "[#WS][&U]$1",                                   // easier to do here, but should go to separate parser
                  */          
                   /*         "eiu" => "[EI][&E]u",
                            "i([aou])" => "[&I]$1",                                   // easier to do here, but should go to separate parser
          */                  "(?<![Aa])e([ao])" => "[#W][&E]$1",                                   // easier to do here, but should go to separate parser
                            "ei([aeou])" => "[EI][&E]$1",                                   // easier to do here, but should go to separate parser
                            "eu([aeou]|lich)" => "[EU][&E]$1",                                   // easier to do here, but should go to separate parser
                            "ei([aeou]|lich)" => "[EI][&E]$1",                                   // easier to do here, but should go to separate parser
                            //"([Ee])in(?=[kg][aeiou])" => "$1in|",
                            "^([Aa])n([bfghklmnprswz][aeiouäöü])" => array("$1[N]$2", ".*[Aa]nker.*"),             // don't add combination "and": too many wrong cases (andere, android vs. Andenken)
                            "sch(a|ä)ft$" => "{SCHAFT}",     // CAUTION WITH THOSE UMLAUT: [aä] doesn't work - (a|ä) is a workaround ...
                            
                            "t(u|ü)m(ers?|in|innen|s)?$" => "{TUM}$2",           // umlaut .. workaround see above
                            "^(ein|an|au[fs]|zu|ab|nieder|auseinander)ge([bcdfghjklmnpqrstvwxyz]*[AEIOUÄÖÜaeiouäöü]+)" => "$1{GE}$2",
                            "^(herein|an|un|gegen|ab|auf)?ge([bcdfghjklmnpqrstvwxyz]+[AEIOUÄÖÜaeiouäöü]+)" => "$1{GE}$2",        
                            "(^|\|)be(un|ur)" => "$1{BE}$2",
                            "^beu" => "b[EU]",              // if combination is not be(un|ur) consider eu as diphtong
                            "^(an)?be(?!(tte?n?s?$)|i)" => "\$1{BE}",                
                            "^anti" => "{ANTI}",
                            "einander" => "einand{ER}",
                            "^(un)?aus(?!s)" => "$1{AUS}",
                            "sein" => "{SEIN}",
                            "anten$" => "[A]nten",
                            "(^|\|)(in|un)?[k|c]on([^n])" => "$1$2{CON}\$3",
                            "^(ur|an|un|zu|selb|selbst)?ver(?!((se?n?s?$)|(sion(en)?$)))" => "\$1{VER}",
                            "i[o|ö]nn?" => "{ION}",
                            "(^|\|)(un)?er" => "$1$2{ER}",
                            "(^|\|)(un)?zer" => "$1$1{ZER}",
                            "(^|\|)rück(?=!ens?$)" => "$1{RÜCK}",           // why doesn't it work?!?
                            "^([Aa])llerun" => "{ALL}{ER}|{UN}",
                            "(^|\|)({?ver}?)?un(?!t)" => "$1$2{UN}",      
                            "(^|\|)({?ver}?)?ur" => array("$1$2{UR}", "Uri"),       
                            //"(^|\|)({?des}?)?in" => "$1$2{IN}",
                            "eien" => "[EI][&E]{EN}",
                            "^([Ss]ch|[Zz])ien$" => "$1[I]n", // avoid the following rule
                            "(?<=pb)ien$" => "[&I]{EN}",
                            "(?<!(^[Ww])|i)en$" => array("{EN}", "Eugen", "[Hh]omogen"), 
                            "(?<!^[Ww])em$" => "{EM}",
                             "je" => "[J][E]",
                            "iet" => "[I]t",
                            "([^c])(haft)\$" => "$1{HAFT}",
                            "{ANT}{LICH}" => "en{TLICH}",
                            "{ET}t" => "e[TT]",
                        );

// define normalizer (handles ortographical irregularities and corrects them)
$normalizer_table = array(
                            "ç" => "c",
                            "[Aa]eo" => "äo", // Aeolus
                            "-\]" => "=]",      // quick fix: avoid replacement of - in [0D-] (= substitute with = and change it back afterwards)
                            "-(i|au|eu|äu|ei)" => "|[-]\\[0D-]$1",            // words with "-" in the middle
                            "-(a|e|o|u)" => "|[-]\\[0N-]$1",            // words with "-" in the middle => problem with \\ character (probably escaping; no other rule find's \ afterwards ... ?!)
                            "-" => "|[-]\\",
                            "=\]" => "-]",      // quick fix: change = back to - (see above)
                            
                            "'(i|au|eu|äu|ei)" => "|[']\\[0D-]$1",            // words with "-" in the middle
                            "'(a|e|o|u)" => "|[']\\[0N-]$1",            // words with "-" in the middle => problem with \\ character (probably escaping; no other rule find's \ afterwards ... ?!)
                            "'" => "|[']\\",
                            
                             "th(?!of$)" => "t",                                                // should be done in a separate part (e.g. "prenormalizer")
                             "th(?!er)" => "t",
                             "th(?!in)" => "t", 
                             "th(?!aft)" => "t", 
                             
   /*                         "ä([eaio])" => "ä[&A]$1",                                   // easier to do here, but should go to separate parser
                            "ö([aeiou])" => "ö[&O]$1",                                   // easier to do here, but should go to separate parser
                            "a([aeio])" => "a[&A]$1",                                   // easier to do here, but should go to separate parser
                            "o([aeiu])" => "[&O]$1",                                   // easier to do here, but should go to separate parser
                            "ü([aeiou])" => "[#WS][&U]$1",                                   // easier to do here, but should go to separate parser
                            "i[o|ö]nn?" => "{ION}",
                            "eiu" => "[EI][&E]u",
                            "i([aou])" => "[&I]$1",                                   // easier to do here, but should go to separate parser
                            "e([ao])" => "[#W][&E]$1",                                   // easier to do here, but should go to separate parser
        */
        /*                    "ei([aeou])" => "[EI][&E]$1",                                   // easier to do here, but should go to separate parser
                            "eu([aeou]|lich)" => "[EU][&E]$1",                                   // easier to do here, but should go to separate parser
                            "ei([aeou]|lich)" => "[EI][&E]$1",                                   // easier to do here, but should go to separate parser
          */                  
                            //"a([ah])([flmnrst])?" => "a$2",
                            "aa" => "a",
                            "ah(?=[lmnrstz])" => "a",
                            "äh(?=[lmnrstz])" => "ä",
                            "uh(?=[lmnrstz])" => "u",
                            "o([oh])([flmnrst])" => "o$2",
                            "ieh([tmn])" => "i$1",
                            "e([eh])([flmnrst])" => "e$2",
                            "äh([nrl])" => "ä$1",
                            "öh(?![aeiouäöüAEIOUÄÖÜ])" => "ö",
                            "ih([r|n])" => "i$1",
                            "äht" => "ät",
                            "tz" => "z",
                            "ph" => "f",
                            "ß" => "ss", // or [SS]?
                            "ühl" => "ül",
                            "uhr" => "ur",
                            "ie" => "i",
                            "ühr" => "ür",
                            "\/" => "", 
                            "\(" => "", // filter out all brackets
                            "\)" => "", // filter out all brackets
                            "\""=>"",
                            "zz" => "z",    // skizziert
                            
                            
);

// defines all available stenotokens and if they are longer than 1 character, bundles them into []
$bundler_table = array( 
                    //"([bcdfgklmnprstxyz])([lr])" => "[\$1\$2]",
                    "([aeiouäöüAEIOUÄÖÜ]\]?)ssch((\[|{)?[aeiouäöüAEIOUÄÖÜ])" => "$1[SS][CH]$2",
                    "ndl" => "[ND@L3]",
                    "schl" => "[SCHL]",
                    "schm" => "[SCHM]",
                    "schw" => "[SCHW]",
                    "schr" => "[SCHR]",
                    "schl" => "[SCHL]",
                    
                    "(?<!n)ndr" => "[ND@R]",
                    "sch" => "[SCH]",
                    "(?<!m)mpfr" => "[MPFR]",
                    "(?<!m)mpfl" => "[MPFL]",
                    "(?<!m)mpf" => "[MPF]",
                    "str" => "[STR]",
                    "(?<!n)nkr" => "[NKR]",
                    "(?<!n)nkl" => "[CHR]",
                    "(?<!s)stl" => "[STL]",
                    "(?<!s)spl" => "[SPL]",
                    "spr" => "[SPR]",
                    "(?<!n)nkl" => "[NKL]",
                    "pfl" => "[PFL]",
                    "pfr" => "[PFR]",
                    "chr" => "[CHR]",
                    "chl" => "[CHL]",
                    
                    "pf" => "[PF]",
                    "(ck|kk)" => "[CK]",
                    
                    // alle Doppelkonsonanten müssten hierher?!
                    "ss" => "[SS]",
                    "nn" => "[NN]",
                    "st" => "[ST]",
                    "^eins" => "[EI][N]s",
                    "sp" => "[SP]",  // anspielungen: sp must come before ns (sp taken from line 447)
                    "ns" => "[NS]",
                    
                    "bl" => "[BL]",
                    "cl" => "[CL]",
                    "dl" => "[DL]",
                    "fl" => "[FL]",
                    "ngl" => "[NGL]",
                    "ng(?!r)" => "[NG]",     // before gl, e.e. englisch => e[NG]lisch => en[NGL]isch => e[NG@L3]isch // Knochengrube
                    //"ng(?=r)" => "[NG]",     // before gl, e.e. englisch => e[NG]lisch => en[NGL]isch => e[NG@L3]isch // Knochengrube
                    "gl" => "[GL]",
                    "kl" => "[KL]",
                    "ll" => "[LL]",
                    "ml" => "[ML]",
                    "nl" => "[NL]",
                    "pl" => "[PL]",
                    "rl" => "[RL]",
                    "sl" => "[SL]",
                    "tl" => "[TL]",
                    "vl" => "[VL]",
                    "wl" => "[WL]",
                    "xl" => "[XL]",
                    "yl" => "[YL]",
                    "zl" => "[ZL]",
                    "br" => "[BR]",
                    "cr" => "[CR]",
                    "dr" => "[DR]",
                    "fr" => "[FR]",
                    "gr" => "[GR]",
                    "kr" => "[KR]",
                    "lr" => "[LR]",
                    "mr" => "[MR]",
                    "nr" => "[NR]",
                    "pr" => "[PR]",
                    "rr" => "[RR]",
                    "sr" => "[SR]",
                    "tt" => "[TT]",
                    "tr" => "[TR]",
                    "vr" => "[VR]",
                    "wr" => "[WR]",
                    "xr" => "[XR]",
                    "yr" => "[YR]",
                    "zr" => "[ZR]",
                    
                    "ff" => "[FF]",
                    "ll" => "[LL]",
                    "mm" => "[MM]",
                    "pp" => "[PP]",
                    "rr" => "[RR]",
                    //"tt" => "[TT]", // placed befor tr for ttr
                    "ch" => "[CH]", // (*^\])])
                    "nd" => "[ND]",
                    
                    "nk" => "[NK]",
                    "pf" => "[PF]",
                     "zw" => "[ZW]",
                    "au" => "[AU]",
                    "[Ää]u" => "[EU]",
                    "[Ee]u" => "[EU]",
                    "ei" => "[EI]", 
                    "ai" => "[EI]",
                    // "qu" => "[QU]",              // not necessary if qu => qu
                        
);


// transcriptor
$transcriptor_table = array(   

                    "^\[EI\]$" => "[0D-][EI][&E]",
                    "^[Oo]" => "[0N-][O]",
                    "^[Rr]" => "[AR]",
                    "^äo" => "[0N-][Ä][&E][O]",
                    "oa" => "[&O]A",
                    "äi" => "[Ä][&E][I]",
                    "oe" => "[&O][E]",
                    "io$" => "[&I][&O]",
                    "{VOR}r" => "[VOR+AR]",
                    "{ER}r" => "[VR+AR]",
                    "{DEM}n" => "[^DEM]N",
                    "{DE(M|N)}j" => "[^^DE$1]J",
                    "{DE(M|N)}\[J\]" => "[^^DE$1][J]",
                    "{DE(M|N)}g" => "[^^DE$1]G",
                    "{DE(M|N)}{G" => "[^^DE$1]{G",
                    "{DE(M|N)}{GE}" => "[^^DE$1]{GE}",
                    "{DE(M|N)}z" => "[^^DE$1]Z",
                    "{DE(M|N)}{ZU}" => "[^^DE$1]{ZU}",
                    
                    "o{HEIT}" => "[&O]{HEIT}",
                    "\[AU\]{HEIT}" => "[AU][&E]{HEIT}",
                    "{SCHAFT}ler(n|in(nen)?)?$" => "{SCHAFT}[&L]{-ER}$1",
                    "{TUM}er(s|in|innen)?$" => "{TUM}[VR]$1",
                    "^[Aa]g" => "[0N-][A][G]",
                    "\[GR\]" => "[G@R]",
                    "\[CHR\]" => "[CH@R]",
                    "\[KR\]" => "[K@R]",
                    "\[TR\]]" => "[T@R]",
                    "\[NKR\]" => "[NK@R]",
                    "\[SCHR\]" => "[SCH@R]",
                    "\[STR\]" => "[ST@R]",
                    "\[LL\]" => "[L@L]",
                    "\[BL\]" => "[B@L]",
                    "\[ML\]" => "[M@L]",
                    "\[FL\]" => "[F@L]",
                    "\[PL\]" => "[P@L]",
                    "\[PFL\]" => "[PF@L]",
                    "\[VL\]" => "[V@L]",
                    "\[WL\]" => "[W@L]",
                    "(?<=([bcdfghjklmnpqrsvwxyzBCDFGHJKLMNPQRVWXYZS])|(\[CH\]))\[TL\]" => "[&T@L3]", // Flüchtling, Textlein
                    "\[TL\]" => "[T@L3]",
                    "([bcdfghjklmnpqrvwxyzsBCDFGHJKLMNPQRVWXYZS])\[T" => "$1][&T][",
                    "\[TT\]r" => "[TT@R]",
                    "\[TR\]" => "[T@R]",
                    "\[DR\]" => "[D@R]",
                    "\[CK\]l" => "[CK@L]",
                    "\[NKL\]" => "[NK@L3]",
                    "\[NGL\]" => "[NG@L3]",
                    "\[NR\]" => "[N@R6]",
                    "\[NL\]" => "[N@L]",
                    "\[KL\]" => "[K@L3]",
                    "\[ZL\]" => "[Z@L3]",
                    "\[SCHL\]" => "[SCH@L3]",
                    "\[CHL\]" => "[CH@L3]",
                    "\[BR\]" => "[B@R6]",
                    "\[SPL\]" => "[SP@L]",
                    "\[FR\]" => "[F@R6]",
                    "\[LR\]" => "[L@R6]",
                    "\[MR\]" => "[M@R6]",
                    "\[SPR\]" => "[SP@R6]",
                    "{TUM}\[VR\]" => "{TUM}{VR}",     // workaround (curly bracket)
                    "\[VR\]" => "[V@R6]",
                    "{TUM}{VR}" => "{TUM}[VR]",     // workaround (curly bracket)
                    "\[PR\]" => "[P@R6]",
                    "\[PFR\]" => "[PF@R6]",
                    "\[WR\]" => "[WR@R6]",
                    "\[ZL\]" => "[Z@L3]",
                    "\[RL\]" => "[VR@L]",
                    "\[GL\]" => "[G@L3]",
                    "\[SR\]" => "[S][AR]",
                    "\[DL\]" => "[D@L3]",
                    "\[STL\]" => "[ST@L3]",
                    "\[SL\]" => "s[@L]",        // only a quick fix: a special token for sl should be defined (e.g S1 with line at the end, which is combined with @L to form [S1@L]
                    
                    "R}t" => "R}[&T]",
                    
                    "\{ANT\}" => "[0N-][#N]{ANT}",
                    "{HEIT}{ET}" => "{HEIT}[&E][ET3/4]",
                    "{ET}(e[^s]|{EN}|{EM})" => "{ET}[&E]$1",
                    "^\{ER\}" => "[0N-][#N][VR]", 
                    "\[EI\]t" => "[EI]T", 
                    "([BCDFGHJKLMNPQRVWXYZS])\]t" => "$1][&T]",
                     
                    "([bcdfghjklmnpqrvwxyz])t" => "$1[&T]",
                    "\[&T\]{ER}" => "[&T][VR]",
                    "\[&T\]{ALL}" => "[TALL]",
                    "\[&T\]e\$" => "[&T][&E][-E]",
                    
                    "\[&T\]er" => "[&T][VR]",
                    "\[&T\]{EN}$" => "[&T][&E][EN]",
                    "\[&T\]{EM}$" => "[&T][&E][EM]",
                    
                    "{HEIT}({EN}|{EM})" => "{HEIT}[&E]$1",
                    "{HEIT}er" => "{HEIT}[VR]",
                    "{HEIT}el" => "{HEIT}[&E][-E]L",
                    
                    "{HEIT}e$" => "{HEIT}[&E][-E]",
                    //"{HEIT}es" => "{HEIT}[E][S]",
                    "{HEIT}es" => "{&EITES}",
                    "{HEIT}e\[NS\]" => "[&EITNS]",
                    "{HEIT}ig" => "[&EITG]",
                    "{HEIT}u\[NG\]" => "[A][&EITNG]",
                    "{HEIT}e" => "{HEIT}[&E]e",
                    
                    //"\[KR\]" => "[K][@R]", // test
                    //"\[SCHL\]" => "[SCH][@L]", // TEST
                    "\[EI\]\$" =>"[#W][-E]",
                    "\[&T\]\{HEIT\}" => "[&T][&E]{HEIT}",
                    "\[EU\]({?)E" => "[EU][&E]\$1E",
                    "\[&T\]\{LICH\}" => "[TLICH]",
                    "\[&T\]u\[NG\]" => "[TUNG]",
                    "\[&T\]\{ER\}" => "[T][VR]",
                    "T}er" => "T}[VR]",
                    "\[&T\]\ig" => "[TIG]",
                    "\[&T\]{ET}" => "[&T][&E][ET3/4]",
                    "\[&T\]es" => "[&TES]",
                    "\[&T\]e" => "[&T][&E]E",
                    "\[&T\]a" => "[&T][&E]A",
                    "\[&T\]\[?o\]?$" => "[&T&O]",
                    "\[&T\]o$" => "[&T][&E]O",
                    "\[&T\]u([aeiouäöü])" => "[A][&T&U]$1",
                    "\[&T\]u" => "[&T][&E]U",
                    "\[&T\]\[?i\]?$" => "[&T&I]",
                    "\[&T\]i" => "[&T][&E]I",
                    "erer$" => "[VR][#N][VR]",
                    //"er$" => "[#N][VR]",                // wrong: only shorten in monosillabic words!
                    "eres$" => "[VR][E][S]",
                    "([aeiouäöü])r" => "$1[VR]",
                    "(\[^@\])r" => "\$1[AR]",
                    "}r" => "}[AR]",
                    "^s" => "[0n-][S]",
                    "^n" => "[0n-][N]",
                    "u([aeiouäöü])" => "[#NS][&U]$1",
                    "oi" => "[&O][I]",
                
                    // end vowels
                    "[Aa]$" => "[A][&A]", 
                    "[Ee]$" => "[-E]",
                    "[Ii]$" => "[#N][&I]",
                    "[Oo]$" => "[#N][&O]",
                    "[Uu]$" => "[#NS][&U]",
                    "ä$" => "[#WS][&A]",        // include uppercase!
                    "ö$" => "[#W][&O]",
                    "ü$" => "[#WS][&U]",
                    "\[EU\]$" => "[EU][&E]",
                    "\[AU\]$" => "[AU][&E]",
                    "\[EI\]$" => "[#W][-E]",
                    
                    
                    // initial vowels
                    "^a" => "[0N-][A]",
                    "^e" => "[0N-][E]",
                    //"^i\[CH" => "[0N-][I][CH",
                    "^i\[(S?CH|ZW?)" => "[0N-][I][\$1",
                    "^iz" => "[0N-][I][Z]",
                    "^i" => "[0D-][I]",
                    "^o" => "[0N-][O]",
                    "^u" => "[0N-][U]",
                    "^ö" => "[0N-][#WD]",
                    "^ä" => "[0N-][#WS]",
                    "^ü" => "[0N-][#WDS]",
                    "^\[EU\]\[CH\]" => "[0N-][EU][CH]",
                    "^\[EI\]\[CH\]" => "[0N-][EI][CH]",
                    "^\[AU\]\[CH\]" => "[0N-][AU][CH]",
                    "^\[AU\]" => "[0D-][AU]",
                    "^\[EU\]" => "[0D-][EU]",
                    "^\[EI\]" => "[0D-][EI]",
                    
                    // all other vowels
                    // in combination
                    "öo" => "[#W][&O][O]",
                    // single vowels
                    "^[Aa]" => "[0N-]A",
                    "a" => "A",
                    "e" => "E",
                    "i" => "I",
                    "o" => "O",
                    "u" => "U",
                    "ä" => "Ä",
                    "ö" => "Ö",
                    "ü" => "Ü",
                    "\[AU\]({?E)" => "[AU][&E]$1",
                    
                    "b" => "B",
                    "c" => "C",
                    "d" => "D",
                    "f" => "F",
                    "g" => "G",
                    "h" => "H",
                    "j" => "J",
                    "k" => "K",
                    "l" => "L",
                    "m" => "M",
                    "n" => "N",
                    "p" => "P",
                    "q" => "Q",
                    "^r" => "[AR]",
                    "r" => "[VR]",
                    "s" => "S",
                    "t" => "T",
                    "v" => "V",
                    "w" => "W",
                    "x" => "X",
                    "y" => "Y",
                    "z" => "Z",
                    "&T]{EN}" => "&T][&E][EN]",
                    "\[AU\]$" => "[AU][&E]",
                    "\[EU\]\[-E\]" => "[EU][&E][-E]",
                    "\[EU\]\[&E\]E\[VR\]U\[NG\]" => "[EU][&E][VR]U[NG]",
                    "\[VR\]{AUS}" => "[VR]{-AUS}",
                    //"\[VR\]\[VR\]" => "[VR+AR]", //???
                    "\[VR\]\|?\[VR\]" => "[VR+AR]",
                    "^{HEIT}" => "[0N-]{HEIT}",
                    
                    
                    "IU" => "[&I]U", 
                    "IE" => "[&I]E",
                    "IA" => "[&I]A",
                    "IO([^N])" => "[&I]O$1",
                    "II" => "[&I]I",
                    "OU" => "[&O]U",
                    "\[EU\]E" => "[EU][&E]",
                    "E\[-E\]" => "[#N][-E]",
                    "\[E\]$" => "[-E]",
                    "E{EN}" => "E[&E]{EN}",
                    "\[EI\]{HEIT}" => "[EI][&E]{HEIT}",
                    "\[AU\]I" => "[AU][&E]I",
                    
                   // "\[(CH|SCH)\]{EN}" => "[$1][EN2]",
                    
                    // test with special stenotoken for SCH and others in higher position
                    "(I|\[EI\]|\[AU\]|\[EU\])\[SCH\]" => "$1[^SCH]",
                    "(I|\[EI\]|\[AU\]|\[EU\])\[SCHW\]" => "$1[^SCHW]",
                    "(I|\[EI\]|\[AU\]|\[EU\])\[SCHM\]" => "$1[^SCHM]",
                    "(I|\[EI\]|\[AU\]|\[EU\])\[ZW\]" => "$1[^ZW]",
                    "(I|\[EI\]|\[AU\]|\[EU\])Z" => "$1[^Z]",
                    "(I|\[EI\]|\[AU\]|\[EU\])\[CH\]" => "$1[^CH]",
                    
                    "\[&T\]\[?B\]?" => "[&TB]",
                    "\[&T\]\[?L\]?(OS)" => "{&T-L}$1", // avoid bundling tl in transcriptor => use curly brackets and replace them later (see below)
                    "\[&T\]\[NG\]" => "[&TNG]",
                    "\[&T\]\[NS\]" => "[&TNS]",
                    "\[&T\]\[?ST\]?" => "[&TST]",
                    "\[&T\]\[?CH\]?" => "[&T^CH]",
                    "\[&T\]\[?C\]?" => "[&TC]",
                    "\[&T\]\[?D\]?" => "[&TD]",
                    "\[&T\]\[?G\]?" => "[&TG]",
                    "\[&T\]\[?H\]?" => "[&TH]",
                    "\[&T\]\[?J\]?" => "[&TJ]",
                    "\[&T\]\[?ALL\]?" => "[&TALL]",
                    "\[&T\]\[?M\]?" => "[&TM]",
                    "\[&T\]\[?W\]?" => "[&TW]",
                    "\[&T\]\[?X\]?" => "[&TX]",
                    "\[&T\]\[?Y\]?" => "[&TY]",
                    "\[&T\]\[?&E\]?" => "[&T&E]",
                    "\[&T&E\]E" => "[&T&E][-E]",
                    "\[&T\]{HAFT}" => "[&THAFT]",
                    "{HEIT}{LICH}" => "[&EITH]",
                    "\[&T\]{SCHAFT}" => "[&TSCHAFT]",
                    "{HEIT}{SCHAFT}" => "[&EITSCHAFT]",
                    "\[&T\]{ION}" => "[&TION]",
                    
                    "\[&T\]{ALL}" => "{&TALL}",         // curly brackets in order to cirumvent following rule
                    "\[&T\](\[?[AEIOU]\]?)" => "[&T&E]$1",
                    "{&TALL}" => "[&TALL]",         // replace curly brackets
                    
                    "\[&T\]\[?N\]?" => "[&TN]",
                    "\[&T\]\[?VR\]?" => "[&TVR]",
                    "\[&T\]\[?R\]?" => "[&TAR5]",
                    "\[&TL\]" => "[&T@L3]",
                    "\[&T\]\[L\]" => "[&T@L3]",
                    "{&T-L}" => "[&TL]",    // replace curly brackets; rule for tl in "wertlos" for example
                    
                    
                    "\[&T\]\[SCH\]" => "[&T^SCH]",
                    "\[&T\]\[?S\]?" => "[&TS]",
                    "\[&T\]\[?U\]?" => "[&TU]",
                    "\[&T\]\[?SCH\]?" => "[&T^SCH]",
                    "\[&T\]\[?Z\]?" => "[&T^Z]",
                    "\[&T\]\[F@L\]" => "[&TF@L]",
                    "\[&T\]F" => "[&TF]",
                    "\[&T\]\[?K\]?" => "[&TK]",
                    "\[&T\]\[?P\]?" => "[&TP]",
                    "\[&T\]\[?Q\]?" => "[&TQ]",
                    "\[&T\]\[?V\]?" => "[&TV]",
                    "\[&T\]\[?NK\]?" => "[&TNK]",
                    "\[&T\]\[?PF\]?" => "[&TPF]",
                    "\[&T\]\[?SP\]?" => "[&TSP]",
                    "\[&T\]\[?SCHW\]?" => "[&TSCHW]",
                    "\[&T\]\[?ZW\]?" => "[&TZW]",
                    "\[&T\]Ä" => "[&T&E]Ä",
                    "\[&T\]Ü" => "[&T&E]Ü",
                    "\[&T\]\[#W\]\[-E\]" => "[&T&E][#W][-E]",
                    

                    // idem {HEIT}
                    "{HEIT}\[?B\]?" => "[&EITB]",
                    "{HEIT}\[?L\]?" => "[&EITL]",
                    "{HEIT}\[NG\]" => "[&EITNG]",
                    "{HEIT}\[NS\]" => "[&EITNS]",
                    "{HEIT}\[?ST\]?" => "[&EITST]",
                    "{HEIT}\[?SCH\]?" => "[&EITSCH]",
                    "{HEIT}\[?CH\]?" => "[&EITCH]",
                    "{HEIT}\[?C\]?" => "[&EITC]",
                    "{HEIT}\[?D\]?" => "[&EITD]",
                    "{HEIT}\[?G\]?" => "[&EITG]",
                    "{HEIT}\[?H\]?" => "[&EITH]",
                    "{HEIT}\[?J\]?" => "[&EITJ]",
                    "{HEIT}\[?ALL\]?" => "[&EITALL]",
                    "{HEIT}\[?M\]?" => "[&EITM]",
                    "{HEIT}\[?W\]?" => "[&EITW]",
                    "{HEIT}\[?X\]?" => "[&EITX]",
                    "{HEIT}\[?Y\]?" => "[&EITY]",
                    "{HEIT}\[?&E\]?" => "[&EIT&E]",
                    "{HEIT}\[?N\]?" => "[&EITN]",
                    "{HEIT}\[?VR\]?" => "[&EITVR]",
                    "{HEIT}\[?S\]?" => "[&EITS]",
                    "{HEIT}\[?U\]?" => "[&EITU]",
                    "{HEIT}\[?O\]?" => "[&EITO]",
                    "{HEIT}\[?Z\]?" => "[&EITZ]",
                    "{HEIT}\[?F\]?" => "[&EITF]",
                    "{HEIT}\[?K\]?" => "[&EITK]",
                    "{HEIT}\[?P\]?" => "[&EITP]",
                    "{HEIT}\[?Q\]?" => "[&EITQ]",
                    "{HEIT}\[?V\]?" => "[&EITV]",
                    "{HEIT}\[?NK\]?" => "[&EITNK]",
                    "{HEIT}\[?PF\]?" => "[&EITPF]",
                    "{HEIT}\[?SP\]?" => "[&EITSP]",
                    "{HEIT}\[?SCHW\]?" => "[&EITSCHW]",
                    "{HEIT}\[?ZW\]?" => "[&EITZW]",
                    //"[VR]|[AR]
                    
                    "{TUM}" => "[A][&TM]",
                    "\[TLICH\]T" => "[TLICH][&T]",
                    "\[TLICH\]" => "[&TH]",
                    "\[&T\](\[-E[MN]?\])" => "[&T][&E]$1", 
                    "\[&EITNG\]\[SP\]" => "[&EITNG][S][P]",     // "Zeitungspapier"
                    "\[AU\]\[-E\]" => "[AU][&E][-E]",
                    "\[EI\]{ER}" => "[EI][&E]{ER}",
                    "\[AU\]U" => "[AU][&E]U",
                    "\[O\]U" => "[&O]U",
                    
                    //"VR@L" => "/VR@L",      // replace combination VR+@L by /VR@L (<=> deltay of +0.5) => not necessary if functionality of TokenCombiner is extended
                    
                    //"\[CH\]\[T@L3\]", "[CH][&T@L3]",
                   // "T@L3", "&T@L3",
                
);

// substituter table: replaces shortings by steno tokens that can be handled directly by steno engine
$substituter_table = array(
                            "{DASS?}" => "[0N-][#NS][S]",
                            "{HEIT}{ER}" => "[&EITVR]",
                            "{ER}" => "[#N][VR]",
                            "{BE}" => "[B]",
                            "{GE}" => "[G]",
                            "{UND}" => "[#NS][&U]",
                            "{DI}" => "[D]",
                            "{MIT}" => "[M]",
                            "{VON}" => "[V]",
                            "{FÜR}" => "[F]",
                            "{LICH}" => "[H]",
                            "{ALS}" => "[L]",
                            "{CON}" => "[C]",
                            "{WENN}" => "[NN]",
                            "{WIR}" => "[W]",
                            "{ZU}" => "[Z]",
                            "{DER}" => "[VR]",
                            "{NICHT}" => "[NICHT]",
                            "{HEIT}ig" => "[#W][TIG]",
                            "\|{VER}" => "[EN][AR]",            // leave out | ?! (yes!)
                            "([^\|]){VER}" => "$1[0N-][EN][AR]",
                            
                            "{VER}" => "[0N-][EN][AR]", 
                            //"([^\|]){VER}" => "$1[0N-][EN][AR]",
                            
                            "{DEM}" => "[0N-][EM]",
                            "{DEN}" => "[0N-][EN]",
                            "{SI}" => "[0N-][#N][S][/I]",
                            "{DER}" => "[VR]",
                            "{UN}" => "[#NS][&U]",
                            "{IN}" => "[IN]",
                            "{WAR}" => "[0U-][#0S][&A]",
                            "{WÄR}" => "[0U-][#WS][&A]",
                            "{ET}" => "[ET]",
                            "{HAT}" => "[HAT]",
                            "{DA}" => "[DA]",
                            "{DES}" => "[0N-][S]",
                            "{SIND}" => "[SIND]",
                            "{DIS}" => "[0N-][I][S]",
                            "{ZER}" => "[Z@R]",
                            "{ANTI}" => "[0N-][ANTI]",
                            "{ALS}" => "[L]",
                            "^{UNTER}" => "[0N-][N@R6]",
                            "{UNTER}" => "[N@R6]",
                            "^{ÜBER}" => "[0N-][#WS][&U]",
                            "{ÜBER}" => "[#WS][&U]",
                            "{WI}" => "[W][/I]",
                            "{WIDER}" => "[W@R6]",
                            "{SOLCH}" => "[SOLCH]",
                            "{WELCH}" => "[CH]",
                            "{HEIT}{HIN}" => "[&EITH][N]",
                            "{HIN}" => "[H][N]",
                            "\[&T\]{EN}$" => "[&T][&E][EN]",
                            "\[&T\]{EM}$" => "[&T][&E][EM]",
                            "\[&T\]\[#N\]\[VR\]$" => "[&T][VR]",
                            
                            "{-ER}" => "[VR]",
                            "{USW}" => "[A][&U][SS]",
                            "{USF}" => "[A][&U][SS][FORT]",
                            "{GEHABT}" => "[G][&T]",
                            "{HAB}T" => "[HAB][&T]",
                            "{HAB}" => "[HAB]",
                            "{ZUSAMMEN}" => "[Z][A][S]",
);

// base definitions for all tokens  x, y, t, d1, th, 0, d2, t2, /**/  
// IMPORTANT: when defining abbreviations you cannot use the same key in array twice! E.g: if you define token m and then try to define abbreviation "mit" => m it wont work! Workaround: define "mit" => "m/"
$steno_tokens_master = array(  

                        // rescription rules
                        // shortings - IMPORTANT: shortings must be returned with a "/" at the end, otherwise there is a key conflict in the array (2 x same key) !!! 
                        // shortings that can be represented as a single steno-token (=> now integrated above as normal rules with multiple search_strings)
                        "IST" => array( /*header*/5,   0.5,0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0.5, 9.5, 0.5, 1, 1.0, 0, 0, 0.5, /**/  1, 10, 0.5, 0, 1.0, 0, 0, 0.5, /**/  0.5, 10.5, 0.5, 0, 1.5, 0, 0, 0.5, /**/  0, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0.5, 9.5, 0.5, 0, 3.0, 0, 1, 0.5 ),
                        "WAS"  => array( /*header*/ 5, 0.5, 0, 0.5, 2, 1, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/1.5, 8.5, 0.5, 1, 1.0, 0, 0, 0.5, /**/  2.25,9.25, 0.5, 2, 1.5, 0, 0, 0.5, /**/ 5, 10, 0.5, 98, 1.5, 0, 0, 0.5, /**/ 2.25,9.25, 0.5, 2, 1.5, 0, 0, 0.5, /**/1.5,8.5,0.5, 0, 2.5, 0, 0, 0.5,/**/0,6, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0.5,  2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2.5,0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  4.5, 2, 0.5, 0, 1.0, 0, 1, 0.5 /**/ ),
                        "AUCH" => array( /*header*/ 5,   1,-1, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",1,1,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 8.5, 0.5, 1, 1.3, 0, 0, 0.5, /**/   2.5, 10, 0.7, 2, 2.5, 0, 0, 0.8, /**/  5, 7, 0.8, 0, 3.0, 0, 0, 0.5, /**/   5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 2, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "TIG"  => array( /*heade*/ 10, 0, 0, 0.5,   1,   1, 0, ""/**/,"","","","",0,0,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5,  18.75, 0.5, 1, 1.5, 0, 0,   0, /**/6, 20, 0.5, 2, 2.5, 0, 0, 0.5, /**/  7.5,  18, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 7.5, 10,  0, 0, 1.0, 0, 1,   0),                        
                        "NICHT"  => array( /*header*/15, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0.5,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,     0, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 3,     4, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 5,     5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 6.5,  5, 0.5, 2.5, 2.0, 0, 0, 0.5, /**/  7.75,  4, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 11, 1, 0.5, 0, 2.5, 0, 0, 0.5, /**/  12.5, 0, 0.5, 0, 1.5, 0, 0, 0.5, /**/ 14, 1, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "ALL"  => array( /*header*/ 3, 0.5, 0, 0.5,0.75,0.75, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  5, 0.5, 1, 1.5, 0, 0, 0.5, /**/2,  8.5, 0.5, 2, 2, 0, 0, 0.5, /**/   1.1, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 0, 7, 0.5, 0, 3.0, 0, 0, 0.5, /**/   0, 2, 0.5, 0, 2.5, 0, 0,   0, /**/ 2, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 3, 2, 0.5, 0, 1.0, 0, 1, 0.5),                       
                        "WERD" => array( /*hear*/1.25, 0.5, 0, 0.5,   0,   2, 0, ""/**/,"","","","",0,0.5,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 3.0, 0, 0, 0, /**/  0, 1, 0.5, 0, 2.5, 0, 0, 0.5, /**/  1.25, 0, 0.5, 0, 1.2, 0, 1, 0.5 /*  2, 0.5, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "IN" => array( /*hear*/5, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 3, 0, 1, 1.0, 0, 0, 0, /**/   3, 4, 0, 0, 1.0, 0, 0, 0, /**/  2, 0, 0, 0, 0, 0, 0, 0, /**/  5, 1, 0, 0, 1.0, 0, 1, 0 /**/  ),
                        "SEIN" => array( /*hear*/20, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0.5,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 0, 0, 1, 1.0, 0, 0, 0.5, /**/   3, 3, 0.5, 0, 1.0, 0, 0, 0.5, /**/  10, 5, 0.5, 0, 0, 0, 0, 0.5, /**/  17, 3, 0.5, 0, 1.0, 0, 0, 0.5, /**/  19, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 20, 0, 0.5, 0, 1.0, 0, 1, 0 ),
                        "GEGEN"  => array( /*heade*/3, 0.5, 0, 0.5,   3,   5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  -1,  8.75, 0.5, 1, 1.0, 0, 0,   0, /**/1, 10, 0.5, 2, 1.0, 0, 0, 0.5, /**/  2.5,  8, 0.5, 0, 1.0, 0, 0, 0, /**/  0,  6, 0, 0, 1.0, 0, 0, 0.5, /**/ 2.5, 5,   0.5, 0, 1.0, 0, 0,   0, /**/ 1, 0, 0, 0, 1.0, 0, 1, 0,),                        
                        "HAB"  => array( /*header*/ 4, 0.5, 0,   0, 1.5, 1.5, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10,   0, 1, 3.0, 0, 0, 0.5, /**/  0,  2, 0.5, 0, 2.5, 0, 0, 0.5, /**/   2.5,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/    4,  1,0.5, 0, 1.0, 0, 1,   0),
                        "HAT"  => array( /*headr*/4.5, 0.5, 0, 0.5,   1,   1, 0, "" /*"lich,h"*//**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  8.75, 0.5, 1, 1.5, 0, 0.5, 0, /**/1, 10, 0.5, 2, 2.5, 0, 0, 0.5, /**/   2.5,  8, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1.0, 0, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 1.5, 0.5, 0, 1.5, 0, 2, 0.5, /**/       ),
                        "DA"  => array( /*header*/ 0, 0.5, 0,   0,0 /*2.25*/ ,2.25, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10,   0, 1, 3.0, 0, 0,   0, /**/  0,  0,   0, 0, 1.0, 0, 1,  0, /**/ 0, 2.5, 0, 4, 1.0, 0, 0, 0.5,), 
                        "SIND" => array( /*headr*/2.5, 0.5, 0, 0.5,   1, 2.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  5, 0.5, 1, 1.5, 0, 0, 0.5, /**/2.5,  8.25, 0.5, 2, 2, 0, 0, 0.5, /**/   1.1, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 0, 7, 0.5, 0, 3.0, 0, 0, 0.5, /**/   0, 0, 0.0, 0, 1.0, 0, 0,   0 ),                       
                        "VOR" => array( /*header*/ 5, 0.5, 0, 0,   2,   2, 0, ""/**/,"","","","",0,0,0,2.5, /*++*/ 5,0,0,0,0,0,0,0, /*data*/   0, 10, 0, 1, 1.0, 0, 0, 0.5, /**/  0, 6.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/  1.25, 5.75, 0.5, 0, 1.0, 0, 0, 0.5, /**/ /* x, y, t, d1, th, 0, d2, t2, *//**/ 2.5, 5, 0.5, 1, 3.0, 0, 0, 0.5, /**/ 5, 2.5, 0.7, 0, 3.0, 0, 0, 0.7, /**/  2.5, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 1.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 0, 1.0, 0, 1, 0.0),    
                        "VOR+AR" => array( /*header*/ 5, 0.5, 0, 0,   2,   2, 0, ""/**/,"","","","",0,0,0.5,2.5, /*++*/ 5,0,0,0,0,0,0,0, /*data*/   0, 10, 0, 1, 1.0, 0, 0, 0.5, /**/  0, 6.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/  1.25, 5.75, 0.5, 0, 1.0, 0, 0, 0.5, /**/ /* x, y, t, d1, th, 0, d2, t2, *//**/ 3.5, 5, 0.5, 1, 3.0, 0, 0, 0.5, /**/ 8, 2.5, 0.7, 0, 3.0, 0, 0, 0.7, /**/  5.5, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/  3, 2.5, 0.7, 0, 1.0, 0, 0, 0.5, /**/ 5.5, 5, 0.5, 0, 1.0, 0, 0, 0.0, /**/ 8, 7.5, 0.5, 0, 1.0, 0, 0, 0.0, /**/ 5.5, 10, 0.5, 0, 1.0, 0, 0, 0.0, /**/ 3, 7.5, 0.5, 0, 1.0, 0, 0, 0.0, /**/ 5.5, 5, 0.5, 0, 1.0, 0, 1, 0.0 ),    
                        
                        "ANT"  => array( /*header*/25, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4,     4.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 8,  5, 0.5, 2, 2.0, 0, 0, 0.5, /**/  11,  4, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 18, 1, 0.5, 0, 2.5, 0, 0, 0.5, /**/  22, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 25, 0, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "RÜCK" => array( /*header*/ 8, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  2.5, 0, 0.5, 1, 1.0, 0, 0, 0.7, /**/ 5, 2.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 5, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 0, 0.5, 0, 1.0, 0, 1, 0.0),    
                        "UR" => array( /*header*/ 5, 0.5, 0, 0,   0,   1, 0, ""/**/,"","","","",1,0,0,2.5, /*++*/ 5,0,0,0,0,0,0,0, /*data*/    -2, 10, 0, 1, 3.0, 0, 0, 0.5, /**/  3, 5, 0.5, 0, 1.0, 0, 0, 0.7, /**/5, 2.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 1.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 0, 1.0, 0, 1, 0.0),    
                        "ANTI"  => array( /*header*/25, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4,     4.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 8,  5, 0.5, 2.5, 2.0, 0, 0, 0.5, /**/  11,  4, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 18, 1, 0.5, 0, 2.5, 0, 0, 0.5, /**/   22, 0, 0.5, 0, 1.5, 0, 0, 0.5, /**/ 22.5, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 23.5, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/  24.5, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/  23.5, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/  22.5, 1, 0.5, 0, 1.0, 0, 1, 0.5,      ),
                        "ZURÜCK" => array( /*header*/ 5, 0.5, 0, 0.5,   2,   1, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   -2, -4, 0.5, 1, 1.0, 0, 0, 0.5, /**/  1.5, -3, 0.5, 0, 1.0, 0, 0, 0.5, /**/  4, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/  5, 2.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 5, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 0, 0.5, 0, 1.0, 0, 0, 0.0, /**/ 3.5, 0, 0.5, 1, 1.0, 0, 1, 0.7, /**/),    
                        "INTER" => array( /*header*/ 5, 0.5, 0, 0.5,   2,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   -2, -4, 0.5, 1, 1.0, 0, 0, 0.5, /**/  1.5, -3, 0.5, 0, 1.0, 0, 0, 0.5, /**/  4, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/  5, 2.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 5, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 0, 0.5, 0, 1.0, 0, 0, 0.0, /**/ 3.5, 0, 0.5, 1, 1.0, 0, 1, 0.7, /**/),    
                        "ION"  => array( /*header*/25, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0.5,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4,     4.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 8,  5, 0.5, 2.5, 2.0, 0, 0, 0.5, /**/  11,  4, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 18, 1, 0.5, 0, 2.5, 0, 0, 0.5, /**/  22, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 25, 0, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "HAFT"  => array( /*header*/ 4, 0.5, 0,   0,   4,   1, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10, 0.5, 1, 1.5, 0, 0, 0.5, /**/  3,  17, 0.5, 2, 2.0, 0, 0, 0.5, /**/  1.8, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 18, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 2, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  4, 2, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "SCHAFT" => array( /*hear*/20, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 1.5, 0, 0, 0.5, /**/  0.75, 3, 0.5, 0, 3.0, 0, 0, 0.5, /**/   19.25, 2.5, 0.5, 0, 1.5, 0, 0, 0.5, /**/   20, 0, 0.5, 0, 1.0, 0, 1, 0 /**/ ),
                        //"TUM"  => array( /*headr*/10.5, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",1,1,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5,  8.75, 0.5, 1, 1.5, 0, 0,   0, /**/  6, 10, 0.5, 2, 2.5, 0, 0, 0.5, /**/  7.5,  8, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 7.5,   2,0.5, 0, 2.5, 0, 0,   0, /**/ 9.0,  0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 9.0,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 9.5,  2,0.5, 0, 1.0, 0, 1,   0.5),                                                
                        "TALL"  => array( /*header*/ 3, 0.5, 0, 0.5,0.75,0.75, 0, ""/**/,"","","","",1,0,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5,  15, 0.5, 1, 1.5, 0, 0, 0.5, /**/7,  18.5, 0.5, 2, 2, 0, 0, 0.5, /**/   6.1, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 5, 17, 0.5, 0, 3.0, 0, 0, 0.5, /**/   5, 12, 0.5, 0, 2.5, 0, 0,   0, /**/ 7, 10, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 8, 12, 0.5, 0, 1.0, 0, 1, 0.5),                       
                        //"TLICH"  => array( /*headr*/4.5, 0.5, 0, 0.5,   1,   1, 0, "","","","","",0,0,1,0, /*++*/ 1,0,0,0,0,0,0,0, /*data*/  5,  18.75, 0.5, 1, 1.5, 0, 0.5, 0, /**/6, 20, 0.5, 2, 2.5, 0, 0, 0.5, /**/   7.5,  18, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 7.5, 12, 0.5, 0, 3.0, 0, 0, 0.5, /**/  6.0, 10, 0.5, 0, 2.5, 0, 99, 0.5, /**/  5, 11.5, 0.5, 0, 1.5, 0, 2, 0.5, /**/  7.5, 15, 0.5, 0, 1.0, 0, 1, 0.5 /**/     ),
                        "TIG"  => array( /*heade*/7.5, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5,  18.75, 0.5, 1, 1.5, 0, 0,   0, /**/6, 20, 0.5, 2, 2.5, 0, 0, 0.5, /**/  7.5,  18, 0.5, 0, 3.0, 0, 0, 0, /**/ 7.5, 10,   0, 0, 1.0, 0, 1,   0, /**/ 7.5, 12.5, 0, 4, 1.0, 0, 0, 0,),                        
                        "TUNG" => array( /*headr*/8, 0.5, 0, 0.5,   2,   1, 0, ""/**/,"","","","",1,0,1,0, /*++*/ 1,0,0,0,0,0,0,0, /*data*/   7, 18, 0.5, 1, 1.3, 0, 0, 0.5, /**/   8, 19.5, 0.5, 0, 1.3, 0, 0, 0.5, /**/   9, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  8, 19.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/  7, 18, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 7, 11.5, 0.5, 0, 2.5, 0, 0, 0.5, /**/  6, 10, 0.5, 0, 1.5, 0, 99, 0.5, /**/  5, 12, 0.5, 0, 1.5, 0, 2, 0.5, /**/  7, 14, 0.5, 0, 1.0, 0, 1, 0.5 /**/     ),                        
                        "AUF" => array( /*header*/ 9, 0.5, 0, 0,   2,   2, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 3.0, 0, 0, 0.5, /**/   2, 2, 0.5, 0, 2.0, 0, 0, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/   7.5, 2, 0.5, 0, 1.0, 0, 2, 0.5, /**/   9, 5, 0.5, 0, 1.0, 0, 1, 0.5,   ),    
                        "AUS" => array( /*hear*/20, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 0, 0, 1, 1.0, 0, 0, 0.5, /**/   3, 3, 0.5, 0, 1.0, 0, 0, 0.5, /**/  10, 5, 0.5, 0, 1.5, 0, 0, 0.5, /**/  17, 3, 0.5, 0, 2.5, 0, 0, 0.5, /**/  19, 1, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 20, 0, 0.5, 0, 1.0, 0, 1, 0 ),
                        "-AUS" => array( /*hear*/20, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/    10, 5, 0.5, 1, 1.5, 0, 0, 0.5, /**/  17, 3, 0.5, 0, 2.5, 0, 0, 0.5, /**/  19, 1, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 20, 0, 0.5, 0, 1.0, 0, 1, 0 ),                        
                        "BEI" => array( /*header*/18, 0.5, 0, 0,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 3.0, 0, 0, 0.5, /**/   3, 2, 0.5, 0, 2.0, 0, 0, 0.5, /**/   9, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/   15, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/   18, 5, 0.5, 0, 1.0, 0, 1, 0.5,   ),    
                        "DURCH" => array( /*header*/10, 0.5, 0, 0,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  3, 10, 0, 1, 1.0, 0, 0, 0.3, /**/   1.5, 3.5, 0.3, 0, 1.0, 0, 0, 0, /**/   8, 0, 0, 0, 1.0, 0, 1, 0, /**/   ),    
                        "SICH"  => array( /*hear*/11.75, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 1.0, 0, 0, 0.5, /**/      2,    9.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 3,  10, 0.5, 2.5, 1.0, 0, 0, 0.5, /**/  5,  9.5, 0.5, 0, 1.0, 0, 0, 0, /**/ 8, 5, 0, 0, 1.0, 0, 1, 0, /**/   ),
                        "SOLCH" => array( /*header*/ 15,   1,-1, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/    0, 0, 0, 1, 1.0, 0, 0, 0.5, /**/  2.5, 4, 0.5, 0, 1.0, 0, 0, 0.5, /**/  4, 5, 0.5, 0, 1.0, 0, 0, 0.5, /**/  5.5, 4, 0, 0, 1.0, 0, 0, 0.5, /**/  9, 0, 0.5, 0, 1.0, 0, 0, 0, /*ch=>*/   /**/ 10, 3.5, 0, 1, 1.3, 0, 0, 0.5, /**/   12.5, 5, 0.7, 2, 2.5, 0, 0, 0.8, /**/  15, 2, 0.8, 0, 3.0, 0, 0, 0.5, /**/   15, -13, 0.5, 0, 2.5, 0, 0, 0.5, /**/   13, -15, 0.5, 0, 2, 0, 99, 0.5, /**/   11.5, -14, 0.5, 0, 1.5, 0, 0, 0.5, /**/   10, -12, 0.5, 0, 1.0, 0, 2, 0.5, /**/   13, -7, 0.5, 0, 1.0, 0, 1, 0.5, /**/ 15, -2.5, 0, 4, 1.0, 0, 0, 0.5,/**/  ),
                        "WIRD" => array( /*header*/ 5, 0.5, 0, 0.5,   2,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 0, 0.5, 1, 1.0, 0, 0, 0.5, /**/  3.5, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/  5, 5, 0.5, 0, 1.0, 0, 0, 0.5, /**/  5, 7.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 10, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 7.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 0, 1.0, 0, 0, 0.0, /**/ 5, 5, 0.5, 1, 1.0, 0, 1, 0.7, /**/),    
                        "SELB"  => array( /*hear*/ 8, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,1,0,0,0,0,0,0, /*data*/   0.5, 0, 0.5, 1, 1.5, 0, 0, 0.5, /**/   1, 0.5, 0.5, 0, 1.5, 0, 0, 0.5, /**/   0.5, 1, 0.5, 0, 1.5, 0, 0, 0.5, /**/   0, 0.5, 0.5, 0, 1.5, 0, 0, 0.5, /**/   0.5, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/  4, 0, 0.5, 0, 1.0, 0, 2, 0.5, /**/   7, 2, 0.5, 0, 1.0, 0, 1, 0.5, /**/   ),
                        "WO"  => array( /*header*/ 7, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/1.5, 8.5, 0.5, 1, 1.0, 0, 0, 0.5, /**/  2.25,9.25, 0.5, 2, 1.5, 0, 0, 0.5, /**/ 5, 10, 0.5, 2, 1.5, 0, 0, 0.5, /**/ 2.25,9.25, 0.5, 2, 1.5, 0, 0, 0.5, /**/1.5,8.5,0.5, 0, 2.5, 0, 0, 0.5,/**/0,6, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1,  3.25, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2.5,1.5, 0.5, 4, 1.5, 0, 0, 0.5, /**/  6,1.5, 0.5, 0, 1.5, 0, 0, 0.5, /**/  6.5, 0, 0.5, 0, 1.0, 0, 1, 0 /**/ ),
                        "VILLEICHT"  => array( /*header*/ 5, 0.5, 0, 0.5,0.75,0.75, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  5, 0.5, 1, 1.5, 0, 0, 0.5, /**/2,  8.5, 0.5, 2, 2, 0, 0, 0.5, /**/   1.1, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 0, 7, 0.5, 0, 3.0, 0, 0, 0.5, /**/   0, 2, 0.5, 0, 2.5, 0, 0,   0,  /**/ 2, 0, 0.5, 0, 1.5, 0, 0, 0.5, /**/ 5, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 7, 12, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 5, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 2, 20, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 0, 17, 0.5, 0, 1.0, 0, 1, 0.5, ),                       
                        "HEIT" => array(/*heade*/18, 0, 1.5, 0.5,   0,   0, 0, "" /**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  18, 20, 0, 1, 1.0, 0, 0, 0, /**/ 18, 20, 0, 0, 1.0, 0, 1, 0 ),
                        "FORT"  => array( /*header*/ 8, 0.5, 0,   0, 1, 1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5, 10, 0.5, 1, 1.5, 0, 0, 0.5, /**/  8,  17, 0.5, 2, 2.0, 0, 0, 0.5, /**/  6.8, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  5, 18, 0.5, 0, 3.0, 0, 0, 0.5, /**/  5, 0, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 5, -5, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 4.25, -9, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 5, 2.5, 0.5, 4, 1.0, 0, 0, 0.5, /**/ 5, 0, 0.5, 0, 1.0, 0, 1, 0.5,/**/ ),

                        // Special chars: | = separate word (with no space / separation in stenogramm) - \ = separate word (with space / separation in stenogramm)
                        // (the | char can be used as "morphological" boundary: some rules use it to determine prefixes inside a word (in these cases | is considered as equivalent to ^ in REGEX)
                        "\\"  => array( /*header*/ 2, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,1,0, /*data*/ 0, 0, 0.5, 5, 1.0, 5, 0, 0, /**/  0, 0, 0, 5, 1.0, 5, 1, 0, /**/ ),
                        //"|"  => array( /*header*/ 2, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,1,0, /*data*/ 0, 0, 0.5, 5, 1.0, 5, 0, 0, /**/  0, 0, 0, 5, 1.0, 5, 1, 0, /**/ ),
                       
                        // token rules
                        // rules with 3 characters
                        "SCHM" => array(/*header*/6,   1,-1, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  2, 9, 0.5, 1, 1.0, 0, 0, 0.5, /**/  4,10, 0.5, 0, 1.5, 0, 0, 0.5, /**/  6, 9, 0.5, 0, 2.5, 0, 0, 0.5, /**/   5, 5, 0.5, 0, 3.0, 0, 0, 0.5, /**/  3, 2.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 3, 1.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/   /**/  5, 0.5, 0.5, 0, 3.0, 0, 0, 0, /**/   5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "SCHW" => array(/*header*/7,   1,-1, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/ 5, 17, 0.5, 1, 1.0, 0, 0, 0.5, /**/  7, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/  9, 20, 0.5, 98, 1.5, 0, 0, 0.5, /**/  7, 19, 0.5, 0, 2.5, 0, 0, 0.5, /**/  5, 17, 0.5, 0, 3.0, 0, 0, 0.5, /**/  5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "MPF" => array( /*header*/6, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  9, 0.5, 1, 1.5, 0, 0,   0, /**/  1.5, 10, 0.5, 2, 2.5, 0, 0, 0.5, /**/  3,  8, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 3,   2,0.5, 0, 2.5, 0, 0,   0, /**/ 4.5,  0, 0.5, 0, 1.5, 0, 0, 0.5, /**/ 5.75,  2,0.5, 0, 1.0, 0, 0,   0.5, /**/  4.5, 3.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 1.5, 3.1, 0.5, 0, 1.0, 0, 0, 0.8, /**/ 0.75, 2.6, 0.8, 0, 1.0, 0, 0, 0.5, /*+++*/ 1.5, 2.0, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 4.5, 2, 0.5, 0, 1.0, 0, 2, 0.5, /**/ 5.75, 2, 0.5, 0, 1.0, 0, 1, 0.5 ),                                                
                        "SCH" => array( /*header*/9,   1,-1, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5, 7, 0.5, 1, 1.0, 0, 0, 0.5, /**/  7, 9, 0.5, 0, 1.0, 0, 0, 0.5, /**/  9, 10, 0.5, 98, 1.5, 0, 0, 0.5, /**/  7, 9, 0.5, 0, 2.5, 0, 0, 0.5, /**/  5, 7, 0.5, 0, 3.0, 0, 0, 0.5, /**/  5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0 /*3*/, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/5, 2.5, 0, 4, 1.0, 0, 0, 0.5,/**/  ),
                        
                        // distinguish between normal sch, ch, z, zw, schw, schm and "upper"-versions (after i, for example)
                        // offsets 15 + 16 <=> alternative exit points // not very beautiful: x-coordinate cannot be 0 (since alternative exit point is defined as non-0-value => has to be changed later
                        "^SCH" => array( /*header*/9,   1,-1, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0.1, /*++*/ 3,0,0,0,0,0,0,0, /*data*/  5, 7, 0.5, 1, 1.0, 0, 0, 0.5, /**/  7, 9, 0.5, 0, 1.0, 0, 0, 0.5, /**/  9, 10, 0.5, 0, 1.5, 0, 0, 0.5, /**/  7, 9, 0.5, 0, 2.5, 0, 0, 0.5, /**/  5, 7, 0.5, 0, 3.0, 0, 0, 0.5, /**/  5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 2, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/5, 2.5, 0, 4, 1.0, 0, 0, 0.5,/**/  ),
                        "^SCHM" => array(/*header*/6,   1,-1, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0.1, /*++*/ 3,0,0,0,0,0,0,0, /*data*/  2, 9, 0.5, 1, 1.0, 0, 0, 0.5, /**/  4,10, 0.5, 0, 1.5, 0, 0, 0.5, /**/  6, 9, 0.5, 0, 2.5, 0, 0, 0.5, /**/   5, 5, 0.5, 0, 3.0, 0, 0, 0.5, /**/  3, 2.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 3, 1.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/   /**/  5, 0.5, 0.5, 0, 3.0, 0, 0, 0, /**/   5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 2, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "^SCHW" => array(/*header*/7,   1,-1, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0.1, /*++*/ 3,0,0,0,0,0,0,0, /*data*/ 5, 17, 0.5, 1, 1.0, 0, 0, 0.5, /**/  7, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/  9, 20, 0.5, 0, 1.5, 0, 0, 0.5, /**/  7, 19, 0.5, 0, 2.5, 0, 0, 0.5, /**/  5, 17, 0.5, 0, 3.0, 0, 0, 0.5, /**/  5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 2, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "^CH" => array( /*header*/ 5,   1,-1, 0.5, 2, 2, 0, ""/**/,"","","","",0,0,0,0.1, /*++*/ 3,0,0,0,0,0,0,0, /*data*/   0, 8.5, 0.5, 1, 1.3, 0, 0, 0.5, /**/   2.5, 10, 0.7, 2, 2.5, 0, 0, 0.8, /**/  5, 7, 0.8, 0, 3.0, 0, 0, 0.5, /**/   5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 99, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 0, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 2, 0.5, /**/   3, -2, 0.5, 0, 1.0, 0, 1, 0.5, /**/ 5, 2.5, 0, 4, 1.0, 0, 0, 0.5,/**/  ),
                        "^ZW" => array( /*header*/ 5,   1,-1, 0,   1,   0, 0, ""/**/,"","","","",0,0,0,0.1, /*++*/ 3,0,0,0,0,0,0,0, /*data*/  5, 20, 0, 1, 3.0, 0, 0, 0, /**/   5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 0, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 2, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "^Z"  => array( /*header*/ 5,   1,-1, 0, 2, 2, 0, ""/**/,"","","","",0,0,0,0.1, /*++*/ 3,0,0,0,0,0,0,0, /*data*/  5, 10, 0, 1, 3.0, 0, 0, 0, /**/   5, -5, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 4.25, -9, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 2, 0.5, /**/ 5, 2.5, 0.5, 4, 1.0, 0, 0, 0.5, /**/ 5, 0, 0.5, 0, 1.0, 0, 1, 0.5,/**/ ),

                        // rules with 2 characters
                        "CK" => array( /*headr*/2.5, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 28.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/1.25, 30, 0.5, 2, 2, 0, 0, 0.5, /**/   2.5, 27, 0.5, 0, 2.5, 0, 0, 0, /**/ 2.5, 25, 0, 0, 3, 0, 0, 0, /**/ 2.5, 0, 0, 0, 1.0, 0, 1, 0.0, /**/ 2, 2, 0, 4, 1.0, 0, 0, 0.0),
                        "FF" => array( /*headr*/2.5, 0.5, 0,   0,   2,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10, 0.5, 1, 1.5, 0, 0, 0.5, /**/  3,  27, 0.5, 2, 2.0, 0, 0, 0.5, /**/  1.8, 30, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 28, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  4, 1, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "LL" => array( /*header*/ 3, 0.5, 0, 0.5,1.25,1.25, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  5, 0.5, 1, 1.5, 0, 0, 0.5, /**/2,  8.5, 0.5, 2, 2, 0, 0, 0.5, /**/   1.1, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 0, 7, 0.5, 0, 3.0, 0, 0, 0.5, /**/   0, 2, 0.5, 0, 2.5, 0, 0,   0, /**/ 2, 0, 0.5, 0, 1.5, 0, 0, 0.5, /**/  2.5, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/   2.5, 1.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 2, 0.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/  2.5, 0, 0.5, 0, 1.0, 0, 2, 0.5, /**/  3, 2, 0.5, 0, 1.0, 0, 1, 0.5),                       
                        // immatrikulieren: why isn't mm smooth?
                        "MM" => array( /*header*/11, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/ -2,  5, 0.5, 1, 1.0, 0, 0,   0,  /**/ 1.75, 10, 0.5, 0, 1.2, 0, 0, 0, /**/  6, 10, 0.5, 2, 2.0, 0, 0, 0.5, /**/  8,  8.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 8,   2,0.5, 0, 2.5, 0, 0,   0, /**/ 9.5,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 11,  2,0.5, 0, 1.0, 0, 1,   0.5),                                                
                        "NN" => array( /*header*/17, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/ 0,     9, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 2,  10, 0.5, 2.5, 2.0, 0, 0, 0.5, /**/  5,  8, 0.5, 0, 3.0, 0, 0, 0.5, /**//* 7,  6.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 11, 2, 0.5, 0, 2.5, 0, 0, 0.5, /**/  14, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 17, 2, 1, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "PP" => array( /*headr*/2.5, 0.5, 0, 0,   2, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,    30,   0, 1, 3.0, 0, 0, 0.0, /**/    0,  3, 0.5, 0, 2.5, 0, 0, 0.5, /**/      1.25,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 2.5, 2, 0, 0, 1.0, 0, 1, 0),
                        "RR" => array( /*header*/10, 0.5, 1, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/ 1, 7.75, 0.5, 1, 1.0, 0, 0, 0.5, /**/  5, 10, 0.7, 0, 3.0, 0, 0, 0.8, /**/  10, 5, 0.8, 0, 3.0, 0, 0, 0.7, /**/  5, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/  0, 5, 0.5, 0, 1.0, 0, 0, 0.5, /**/  1, 7.75, 0.5, 0, 1.0, 0, 0, 0.5, /**/  5, 10, 0.5, 0, 1.0, 0, 1, 0.5, /**/  ),    
                        "SS" => array( /*header*/14, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,   9, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 3,  10, 0.5, 2.5, 2.0, 0, 0, 0.5, /**/  7.5,  8, 0, 0, 3.0, 0, 0, 0, /**/ 9.5,  6, 0, 0, 3.0, 0, 0, 0, /**/ 14, 0, 0, 0, 1.0, 0, 1, 0 /**/  ),
                        "TT" => array( /*header*/ 0, 0.5, 0, 0,   5,   3, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 30,   0, 1, 3.0, 0, 0, 0, /**/  0,  0,   0, 0, 1.0, 0, 1, 0, /**/ /**/ 0, 2.5, 0, 4, 1.0, 0, 0, 0.5,),
                        "CH" => array( /*header*/ 5,   1,-1, 0.5, 2, 2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 8.5, 0.5, 1, 1.3, 0, 0, 0.5, /**/   2.5, 10, 0.7, 2, 2.5, 0, 0, 0.8, /**/  5, 7, 0.8, 0, 3.0, 0, 0, 0.5, /**/   5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 99, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 0, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/ 5, 2.5, 0, 4, 1.0, 0, 0, 0.5,/**/  ),
                        "ND" => array( /*headr*/2.5, 0.5, 0, 0.5,   1, 3, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  5, 0.5, 1, 1.5, 0, 0, 0.5, /**/2.5,  8.25, 0.5, 2, 2, 0, 0, 0.5, /**/   1.1, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 0, 7, 0.5, 0, 3.0, 0, 0, 0, /**/   0, 0, 0.0, 0, 1.0, 0, 1,   0, /**/ 0, 2.5, 0, 4, 1.0, 0, 0, 0.5, ),                       
                        "NG" => array( /*headr*/3, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 1,0,0,0,0,0,0,0, /*data*/   2, 8, 0.5, 1, 1.3, 0, 0, 0.5, /**/   3, 9.5, 0.5, 0, 1.3, 0, 0, 0.5, /**/   4, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/  3, 9.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2, 8, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 2, 1.5, 0.5, 0, 2.5, 0, 0, 0.5, /**/  1, 0, 0.5, 0, 1.5, 0, 99, 0.5, /**/  0, 2, 0.5, 0, 1.5, 0, 2, 0.5, /**/  2, 4, 0.5, 0, 1.0, 0, 1, 0.5, /**/ 2, 4, 0, 4, 1.0, 0, 0, 0.5,    ),                        
                        "NK" => array( /*header*/ 6, 0.5, 0,   0,   1,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/1,0,0,0,0,0,0,0, /*data*/  3, 10, 0.5, 1, 1.5, 0, 0, 0.5, /**/  6,  17, 0.5, 2, 2.0, 0, 0, 0.5, /**/  4.8, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  3, 18, 0.5, 0, 3.0, 0, 0, 0.5, /**/  3, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1.5, 0, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 2, 0.5, 0, 1.5, 0, 99, 0.5, /**/ 0, 2, 0.5, 0, 1.5, 0, 2, 0.5, /**/  4, 5, 0.5, 0, 1.0, 0, 1, 0.5, /**/  4, 5.5, 0, 4, 1.0, 0, 0, 0.5,/**/),
                        "NS" => array( /*head*/3.75, 0.5, 0,   0,   1,   1, 0, ""/**/ ,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0.75, 5, 0.5, 1, 1.5, 0, 0, 0.5, /**/  3.75,  8.5, 0.5, 2, 2.0, 0, 0, 0.5, /**/  2.65, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/  1.75, 9, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1.75, 1, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0.75, 0, 0.5, 0, 2.5, 0, 99, 0.5, /**/  0, 2.25, 0.5, 0, 1.5, 0, 2, 0.5, /**/  1.75, 3, 0.5, 0, 1.0, 0, 1, 0.5 /**/ ),
                        "PF" => array( /*header*/ 8, 0.5, 0, 0.5,   1,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/  4,  9.5, 0.5, 2, 2.0, 0, 0, 0.5, /**/ 7,  13, 0.8, 2, 2.0, 0, 0, 0.9, /**/ 8,  16, 0.9, 2, 2.0, 0, 0, 0.7, /**/  1.8, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 18, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/  2, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  4, 2, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "SP" => array( /*header*/ 8, 0.5, 0, 0.5,0.75,0.75, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 18, 0.5, 1, 1.5, 0, 0, 0.5, /**/2, 20, 0.5, 2, 2, 0, 0, 0.5, /**/   4, 18, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 4, 15, 0.5, 0, 3, 0, 0, 0.5, /**/  4, 5, 0.5, 0, 2.5, 0, 0, 0.5, /**/  4, 3, 0.5, 0, 2, 0, 0, 0.5, /**/ 6, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 6, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 8, 2, 0.5, 0, 1.0, 0, 1, 0),
                        "ST" => array( /*header*/ 3, 0.5, 0,   0,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10, 0.5, 1, 1.5, 0, 0, 0.5, /**/  3,  17, 0.5, 2, 2.0, 0, 0, 0.5, /**/  1.8, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 18, 0.5, 0, 3.0, 0, 0, 0, /**/  0, 0, 0, 0, 1.0, 0, 1, 0, /**/ 0, 2.5, 0, 4, 1.0, 0, 1, 0 /**/ ),
                        "ZW" => array( /*header*/ 5,   1,-1, 0,   1,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5, 20, 0, 1, 3.0, 0, 0, 0, /**/   5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 0, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "EN" => array( /*header*/ 5, 0,  0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,1,0,0,0,0,0,0, /*data*/ /* 1, 0, 0, 1, 1.0, 0, 0, 0, /**/ 5, 0, 0, 1, 1.0, 0, 0, 0, /**/ 5, 0, 0, 0, 1.0, 0, 1, 0 ),
                        "EM" => array( /*header*/18,   5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,1,0,0,0,0,0,0, /*data*/  /*1, 0,  0, 1, 1.0, 0, 0,  0,*/ /**/ 5, 0,  0, 1, 1.0, 0, 0,  0, /**/ 13, 0,  0.5, 1, 1.0, 0, 1,  0 /**/),
                        // dem, den = em, en but with first point set with x = 0
                        "DEN" => array( /*header*/ 5, 0,  0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,1,0,0,0,0,0,0, /*data*/ /* 1, 0, 0, 1, 1.0, 0, 0, 0, /**/ 0, 0, 0, 1, 1.0, 0, 0, 0, /**/ 5, 0, 0, 0, 1.0, 0, 1, 0 ),
                        "DEM" => array( /*header*/13,   5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,1,0,0,0,0,0,0, /*data*/  /*1, 0,  0, 1, 1.0, 0, 0,  0,*/ /**/ 0, 0,  0, 1, 1.0, 0, 0,  0, /**/ 13, 0,  0.5, 1, 1.0, 0, 1,  0 /**/),
                       
                        "VR+AR" => array( /*header*/ 5, 0.5, 0, 0.5,   0,   1, 0, ""/**/,"","","","",0,0,0.5,2.5, /*++*/ 5,0,0,0,0,0,0,0, /*data*/  2.5, 5, 0.5, 1, 3.0, 0, 0, 0.5, /**/ 5, 2.5, 0.7, 0, 3.0, 0, 0, 0.7, /**/  2.5, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 1.0, 0, 0, 0.5,  /**/ 2.5, 5, 0.5, 0, 1.0, 0, 0, 0.0, /*AR*/ 2.5, 5, 0.5, 0, 1.0, 0, 0, 0.7, /**/ 5, 7.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 10, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 7.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 0, 1.0, 0, 1, 0.0, ),    
                        // special en and em endings with to points (necessary for correct calculation after tokens with pivot points, e.g. "chen"
                   //     "EN2" => array( /*header*/ 5, 0,  0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,1,0,0,0,0,0,0, /*data*/ -4, 0, 0.5, 1, 1.0, 0, 0, 0, /**/ 5, 0, 0, 1, 1.0, 0, 0, 0, /**/ 5, 0, 0, 0, 1.0, 0, 1, 0 ),
                       
                        // rules with 1 character
                        "B"  => array( /*header*/ 5, 0.5, 0,   0, 1, 1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10,   0, 1, 3.0, 0, 0, 0.5, /**/  0,  2, 0.5, 0, 2.5, 0, 0, 0.5, /**/   2.5,  0, 0.5, 4, 1.5, 0, 0, 0.5, /**/  2.5,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 5,  2,0.5, 0, 1.0, 0, 1,   0),
                        "C"  => array( /*header*/ 5, 0.5, 0, 0.5, 2, 2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  1,9.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/  3, 10, 0.5, 98, 3.0, 0, 0, 0.5, /**/     1,9.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/    0,7.5, 0.5, 0, 3.0, 0, 0,   0, /**/ 0,  0,  0, 0, 1.0, 0, 1,   0),
                        "D"  => array( /*header*/ 0, 0.5, 0,   0, 2 ,3, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10,   0, 1, 3.0, 0, 0,   0, /**/  0,  0,   0, 0, 1.0, 0, 1,  0, /**/ 0, 2.5, 0, 4, 1.0, 0, 0, 0.5, ), 
                        "F"  => array( /*header*/ 4, 0.5, 0,   0, 1, 1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10, 0.5, 1, 1.5, 0, 0, 0.5, /**/  3,  17, 0.5, 2, 2.0, 0, 0, 0.5, /**/  1.8, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 18, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 2, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  4, 2, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "G"  => array( /*heade*/2.5, 0.5, 0, 0.5,   2,   3, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  8.75, 0.5, 1, 1.5, 0, 0,   0, /**/1, 10, 0.5, 2, 2.5, 0, 0, 0.5, /**/  2.5,  8, 0.5, 0, 3.0, 0, 0, 0, /**/ 2.5, 0,   0, 0, 1.0, 0, 1,   0, /**/ 2.5, 2.5, 0, 4, 1.0, 0, 0, 0,),                        
                        "H"  => array( /*headr*/4.5, 0.5, 0, 0.5,   2,   2, 0, "","","","","",0,0,0,0, /*++*/ 1,0,0,0,0,0,0,0, /*data*/  0,  8.75, 0.5, 1, 1.5, 0, 0.5, 0, /**/1, 10, 0.5, 2, 2.5, 0, 0, 0.5, /**/   2.5,  8, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1.0, 0, 0.5, 0, 2.5, 0, 99, 0.5, /**/  0, 1.5, 0.5, 0, 1.5, 0, 2, 0.5, /**/  2.5, 5, 0.5, 0, 1.0, 0, 1, 0.5 /**/     ),
                        "J"  => array( /*header*/ 3, 0.5, 0,   0,2,2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  3,  10, 0, 1, 3.0, 0, 0, 0.5, /**/ 3, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1.5, 0, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 1.5, 0.5, 0, 1.5, 0, 2, 0.5, /**/  3, 5, 0.5, 0, 1.0, 0, 1, 0.5 /**/     ),
                        "K"  => array( /*headr*/2.5, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 18.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/1.25, 20, 0.5, 2, 2, 0, 0, 0.5, /**/   2.5, 17, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 2.5, 15, 0.5, 0, 3, 0, 0, 0, /**/ 2.5, 0, 0, 0, 1.0, 0, 1, 0.0, /**/ 2.5, 2.5, 0, 4, 1.0, 0, 0, 0.5,),
                        "L"  => array( /*header*/ 3, 0.5, 0, 0.5,0.75,0.75, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  5, 0.5, 1, 1.5, 0, 0, 0.5, /**/2,  8.5, 0.5, 2, 2, 0, 0, 0.5, /**/   1.1, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 0, 7, 0.5, 0, 3.0, 0, 0, 0.5, /**/   0, 2, 0.5, 0, 2.5, 0, 0,   0, /**/ 2, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 2, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 3, 0, 0.5, 0, 1.0, 0, 1, 0.5),                       
                        "@L" => array( /*header*/ 0, 0, 0, 0.5, 2, 2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/    0, 0, 1, 0.5, 1.0, 0, 0, 0.5, /**/  1, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/  0, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/  -1, 1, 0.5, 0, 1.0, 0, 2, 0.5, /**/  0, 0, 0.5, 0, 1.0, 0, 1, 0.5 /**/ ),                       
                        "@L3" => array( /*header*/ 0, 0, 0, 0.5, 0, 0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/    0, 0, 1, 0.5, 1.0, 0, 0, 0.5, /**/  -1, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/  -2, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/  -1, -1, 0.5, 0, 1.0, 0, 2, 0.5, /**/  0, 0, 0.5, 0, 1.0, 0, 1, 0.5 /**/ ),                       
                        "&L"  => array( /*header*/ 2, 0.5, 0, 0.5,1,1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0.5,  0, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 1,  0.5, 0.5, 0, 2, 0, 0, 0.5, /**/   0.5, 1, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 0, 0.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/   0.5, 0, 0.5, 0, 2.5, 0, 1,   0, ),                       
                        "M"  => array( /*headr*/5.5, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  8.75, 0.5, 1, 1.5, 0, 0,   0, /**/  1.0, 10, 0.5, 2, 2.5, 0, 0, 0.5, /**/  2.5,  8, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 2.5,   2,0.5, 0, 2.5, 0, 0,   0, /**/ 4.0,  0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 4.0,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 5.5,  2,0.5, 0, 1.0, 0, 1,   0.5),                                                
                        "N"  => array( /*header*/11, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,     4.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 1,  5, 0.5, 2, 2.0, 0, 0, 0.5, /**/   2,  5, 0.5, 0, 3.0, 0, 0, 0.5, /**/ /*3,  4, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 5.5, 2, 0.5, 0, 2.5, 0, 0, 0.5, /**/  7.5, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 8.5, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 11, 1, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "P"  => array( /*headr*/4, 0.5, 0,   0,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,    20,   0, 1, 3.0, 0, 0, 0.0, /**/    0,  2.5, 0.5, 0, 2.5, 0, 0, 0.5, /**/      2,  0, 0.5, 4, 1.5, 0, 0, 0.5, /**/   2,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 4, 2, 0, 0, 1.0, 0, 1, 0),
                        "Q"  => array( /*header*/ 7, 0.5, 0, 0.5,2,2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  1,19, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3, 20, 0.5, 98, 3.0, 0, 0, 0.5, /**/  1,19,0.5, 0, 3.0, 0, 0, 0.5, /**/ 0,15, 0.5, 0, 3.0, 0, 0, 0, /**/ 0, 0, 0, 0, 1.0, 0, 1,   0),
                        "VR" => array( /*header*/ 5, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0,2.5, /*++*/ 5,0,0,0,0,0,0,0, /*data*/  2.5, 5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 3.75, 4, 0.7, 5, 2.5, 0, 0, 0.7, /**/ 5, 2.5, 0.7, 0, 3.0, 0, 0, 0.7, /**/ 4.5, 0.5, 0.7, 5, 2, 0, 0, 0.7, /**/ 3.25, 0.15, 0.7, 5, 1.5, 0, 0, 0.7, /**/ 2.5, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 1.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 4, 1.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 0, 1.0, 0, 1, 0.0),    
                        // /VR = normal VR but 1/2 line up when in combination with @L (inconditional_delta_y_after = offset 14 = 0.5)
                        //"/VR" => array( /*header*/ 5, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0.5,2.5, /*++*/ 5,0,0,0,0,0,0,0, /*data*/  2.5, 5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 3.75, 4, 0.7, 5, 2.5, 0, 0, 0.7, /**/ 5, 2.5, 0.7, 0, 3.0, 0, 0, 0.7, /**/ 4.5, 0.5, 0.7, 5, 2, 0, 0, 0.7, /**/ 3.25, 0.15, 0.7, 5, 1.5, 0, 0, 0.7, /**/ 2.5, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 1.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 4, 1.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 0, 1.0, 0, 1, 0.0),    
                        "AR" => array( /*header*/ 5, 0.5, 0, 0.5,   0,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  2.5, 0, 0.5, 1, 1.0, 0, 0, 0.7, /**/ 5, 2.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 5, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 0, 0.5, 0, 1.0, 0, 1, 0.0),    
                        "AR5" => array( /*header*/ 5, 0.5, 0, 0.5,   0,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5, 2.5, 0.7, 1, 1.0, 0, 0, 0.7, /**/  2.5, 5, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 0, 0.5, 0, 1.0, 0, 0, 0.0, /**/ 2.5, 0, 0.5, 1, 1.0, 0, 1, 0.7, ),    
                        "@R" => array( /*header*/ 5, 0.5, 0, 0.5,   0,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 0, 0.7, 0, 1.0, 0, 0, 0.7, /**/  -2, 2, 0.7, 0, 1.0, 0, 0, 0.5, /**/  -4, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/ -2, -2, 0.5, 0, 1.0, 0, 2, 0.5, /**/ 0, 0, 0.5, 0, 1.0, 0, 1, 0.7, /**/ ),    
                        "@R6" => array( /*header*/0, 0.5, 0, 0.5,   0,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 0, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2, 2, 0.7, 0, 1.0, 0, 0, 0.5, /**/  0, 4, 0.7, 0, 1.0, 0, 0, 0.5, /**/ -2, 2, 0.5, 0, 1.0, 0, 2, 0.5, /**/ 0, 0, 0.5, 0, 1.0, 0, 1, 0.7, /**/ ),    
                        "S"  => array( /*hear*/6.75, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,     4, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 1,  5, 0.5, 2, 2.0, 0, 0, 0.5, /**/  2,  5, 0.5, 0, 3.0, 0, 0, 0, /**/ 4,  4, 0.5, 0, 3.0, 0, 0, 0, /**/  6.75, 0, 0, 0, 1.0, 0, 1, 0 /**/  ),
                        "T"  => array( /*header*/ 0, 0.5, 0, 0,4,2.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 20,   0, 1, 3.0, 0, 0, 0, /**/ /* 0,  0.1,   0, 0, 1.0, 0, 2, 0, /*preceeding point = test*/0,  0,   0, 0, 1.0, 0, 1, 0, /**/ 0, 2.5, 0, 4, 1.0, 0, 0, 0.5, ), // define connection point
                        "&T" => array( /*header*/ 4, 0, 1.5, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4, 20, 0, 1, 1.0, 0, 1, 0, /**/ 4, 20, 0, 0, 1.0, 0, 1, 0 ),
                        "&T3/4" => array( /*header*/ 4, 0, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4, 15, 0, 1, 1.0, 0, 1, 0, /**/ 4, 15, 0, 0, 1.0, 0, 1, 0, /**/ 4, 15, 0, 4, 1.0, 0, 1, 0, ),
                        "ET" => array( /*header*/ 4, 0, 0, 0,   0,   0, 0, ""/**/,"","","","",0,0,2,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4, 25, 0, 1, 1.0, 0, 1, 0, /**/ 4, 25, 0, 0, 1.0, 0, 1, 0 ),
                        "ET3/4" => array( /*header*/ 4, 0, 0, 0,   0,   0, 0, ""/**/,"","","","",0,0,1.5,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4, 20, 0, 1, 1.0, 0, 1, 0, /**/ 4, 20, 0, 0, 1.0, 0, 1, 0 ),
                        "V"  => array( /*header*/ 6, 0.5, 0, 0.5,   1,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  1, 16, 0.5, 1, 1.0, 0, 0, 0.5, /**/  2, 18, 0.6, 2, 1.0, 0, 0, 0, /**/ 6,   20,  0, 98, 0, 0, 0, 0.5, /**/ 2, 18, 0.6, 0, 1.5, 0, 0, 0.5, /**/  1, 16, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 14, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0, 5, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1, 2, 0.5, 0, 2.5, 0, 0, 0.5, /**/  3, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/  3, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  5, 2, 0.5, 0, 1.0, 0, 1, 0.5 ), 
                        "W"  => array( /*header*/ 6, 0.5, 0, 0.5, 2, 2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/1.5, 8.5, 0.5, 1, 1.0, 0, 0, 0.5, /**/  2.25,9.25, 0.5, 2, 1.5, 0, 0, 0.5, /**/ 5, 10, 0.5, 98, 1.5, 0, 0, 0.5, /**/ 2.25,9.25, 0.5, 0, 1.5, 0, 0, 0.5, /**/1.5,8.5,0.5, 0, 2.5, 0, 0, 0.5,/**/0,6, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0.5,  2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  3,0, 0.5, 4, 1.5, 0, 0, 0.5, /**/  3,0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  6, 2, 0.5, 0, 1.0, 0, 1, 0.5 /**/ ),
                        "X"  => array( /*header*/ 5, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,   5, 0, 1, 1.5, 0, 0, 0.5, /**/4.25,7.5, 0.5, 2, 2.0, 0, 0, 0.5, /**/  5,  9,0.5, 0, 2.5, 0, 0, 0.5,/**/3.75,10,0.5,0,3.0, 0, 0, 0.5, /**/   1.75,   7.75, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0, 5, 0.5, 0, 3.0, 0, 0, 0, /**/   5, 0, 0, 0, 1.0, 0, 1, 0  ),
                        "Y"  => array( /*header*/ 10, 0.5, 0, 0, 2,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10, 0, 1, 3.0, 0, 0, 0, /**/   10, 0, 0, 0, 1.0, 0, 1, 0 /**/  ),
                        "Z"  => array( /*header*/ 9,   1,-1, 0, 2, 2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5, 10, 0, 1, 3.0, 0, 0, 0, /**/   5, -5, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 4.25, -9, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 5, 2.5, 0.5, 4, 1.0, 0, 0, 0.5, /**/ 5, 0, 0.5, 0, 1.0, 0, 1, 0.5,/**/ ),
                        // these (&&..) are dummy tokens which are only used to combine with other consonants
                        //"&&T" => array( /*header*/ 4, 0, 1.5, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4, 20, 0, 4, 1.0, 0, 1, 0,  ),
                        //"T15" => array( /*header*/ 4, 0, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  /*4, 15, 0, 1, 1.0, 0, 0, 0, /**/ 4, 15, 0, 4, 1.0, 0, 1, 0, /**/ /*4, 15, 0, 0, 1.0, 0, 1, 0,  ),
                        //"^1" => array( /*header*/ 5, 0, 0,   0, 1, 1, 1, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /**/ /*4, 15, 0, 1, 1.0, 0, 0, 0, /**/ 4, 15, 0, 4, 1.0, 0, 1, 0, /**/ /*4, 15, 0, 0, 1.0, 0, 1, 0, ),
                        // above tests with token combiner were'nt successfull: define [&T] + consonant manually (with baseline shift at offset 6)
                        // think the following line is obsolete (rests from token combiner tests?!?)
                        //"1B"  => array( /*header*/ 5, 0.5, 0,   0, 4, 1, 1, ""/**/,"","","","",0,0,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 20,   0, 1, 3.0, 0, 0, 0.5, /**/  0,  12, 0.5, 0, 2.5, 0, 0, 0.5, /**/   2.5,  10, 0.5, 4, 1.5, 0, 0, 0.5, /**/  2.5,  10, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 5,  12,0.5, 0, 1.0, 0, 1,   0),
                        //"^T+L"  => array( /*header*/ 5, 0.5, 0, 0.5, 4,0.75, 1, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  5, 0.5, 1, 1.5, 0, 0, 0.5, /**/2,  8.5, 0.5, 2, 2, 0, 0, 0.5, /**/   1.1, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 0, 7, 0.5, 0, 3.0, 0, 0, 0.5, /**/   0, 2, 0.5, 0, 2.5, 0, 0,   0, /**/ 2, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 2, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 3, 0, 0.5, 0, 1.0, 0, 1, 0.5),                       
                        //"^TN"  => array( /*header*/11, 0.5, 0, 0.5,   4,   0, 1.5, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,     4.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 1,  5, 0.5, 2, 2.0, 0, 0, 0.5, /**/   2,  5, 0.5, 0, 3.0, 0, 0, 0.5, /**/ /*3,  4, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 5.5, 2, 0.5, 0, 2.5, 0, 0, 0.5, /**/  7.5, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 8.5, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 11, 1, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        // SOLUTION: Create new function TokenShifter() similar to TokenCombiner() that just shifts base tokens and adds them to stenotokensmaster
                        // think the following line is obsolete (rests from token combiner tests?!?)
                        //"2B"  => array( /*header*/ 5, 0.5, 0,   0, 1, 1, 0, ""/**/,"","","","",0,1,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10,   0, 1, 3.0, 0, 0, 0.5, /**/  0,  2, 0.5, 0, 2.5, 0, 0, 0.5, /**/   2.5,  0, 0.5, 4, 1.5, 0, 0, 0.5, /**/  2.5,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 5,  2,0.5, 0, 1.0, 0, 1,   0),
                        
                        // token inserted as a second step (from tokenlist)
                        // shortings that must be specially defined (= cannot be represented by one or several tokens);
                        "&E" => array( /*hear*/3, 0.5, 0, 0,   0,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 3.0, 0, 0, 0, /**/  0, 1.5, 0.5, 0, 2.5, 0, 0, 0.5, /**/  2, 0, 0.5, 0, 1.2, 0, 2, 0.5, /**/  3, 0, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "&A" => array( /*header*/ 0, 0.5, 0, 0,   0,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 3.0, 0, 0, 0, /**/  0, 0, 0, 0, 1.0, 0, 1, 0 /**/  ),
                        "&U" => array( /*header*/ 5, 0.5, 0, 0,   0,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 3.0, 0, 0, 0, /**/  5, 0, 0, 0, 1.0, 0, 1, 0 /**/  ),
                        "&O" => array( /*hear*/6.75, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 1.5, 0, 0, 0.5, /**/  0.75, 3, 0.5, 0, 3.0, 0, 0, 0.5, /**/   6, 2.5, 0.5, 0, 1.5, 0, 0, 0.5, /**/   6.75, 0, 0.5, 0, 1.0, 0, 1, 0 /**/ ),
                        "&I" => array( /*header*/2, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 1, 0.5, 1, 1.0, 0, 0, 0.5, /**/  1, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/   2, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/   1, 0, 0.5, 0, 1.0, 0, 0, 0, /**/  0, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/  1, 2, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                      
                        "-E" => array( /*hear*/1.25, 0.5, 0, 0,   4,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,1,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 3.0, 0, 0, 0, /**/  ),
                        "-A" => array( /*header*/ 0, 0.5, 0, 0.5,   0,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 3.0, 0, 0, 0, /**/  0, 0, 0, 0, 1.0, 0, 1, 0 /**/  ),
                        "-U" => array( /*header*/ 5, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 3.0, 0, 0, 0, /**/  5, 0, 0, 0, 1.0, 0, 1, 0 /**/  ),
                        "-O" => array( /*hear*/6.75, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 1.5, 0, 0, 0.5, /**/  0.75, 3, 0.5, 0, 3.0, 0, 0, 0.5, /**/   6, 2.5, 0.5, 0, 1.5, 0, 0, 0.5, /**/   6.75, 0, 0.5, 0, 1.0, 0, 1, 0 /**/ ),
                        "-I" => array( /*header*/3, 0.5, 0, 0.5,   3,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 1, 0.5, 1, 1.0, 0, 0, 0.5, /**/  1, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/   2, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/   1, 0, 0.5, 0, 1.0, 0, 0, 0, /**/  0, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/  1, 2, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "/I" => array( /*hear*/1.25, 0.5, 0, 0.5,   1,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10, 0, 1, 1.0, 0, 0, 0 /**/  ), // "aufstrich-i" für kürzungen
                        
                        // dummy tokens at beginning of the word: 0n[ / 0d[ for normal and down / offset 7 <=> 1(or 3?) means: shifting point (offset 2 = vertical delta)
                        "0-" => array( /*header*/ 0,  0, 0,   0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 0, 0, 1, 1.0, 0, 0, 0, /**/ 0, 0, 0, 0, 1.0, 0, 1, 0.5 ),
                        "0N-" => array( /*header*/ 1,  0, 0,   0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 0, 0, 1, 1.0, 0, 0, 0, /**/ 0, 0, 0, 0, 1.0, 0, 1, 0.5 ),
                        "0D-" => array( /*header*/ 0,  0, -0.5,   0,   0,   0, 1, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 0, 0, 1, 1.0, 0, 0, 0, /**/ 0, 0, 0, 0, 1.0, 0, 1, 0.5 ),
                        "0U-" => array( /*header*/ 5,  0, 0 /*used for "war" but completely wrong*/, 0,   0,   0, 1, ""/**/,"","","","",0,0.5,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 0, 0, 1, 1.0, 0, 0, 0, /**/ 0, 0, 0, 0, 1.0, 0, 1, 0.5 ),
                       
                        "PSPACE" => array( /*header*/ 2,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 0, 0, 1, 1.0, 5, 0, 0, /**/ 2, 0, 0, 0, 1.0, 5, 2, 0, /**/ ),
                        "~~" => array( /*header*/ 12.5,  0, /*+0.5*/0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   0, 10, 0, 1, 1.0, 5, 0, 0.5, /**/   /**/ 2.5, 11, 0, 0, 1.0, 0, 0, 0.5, /**/ 5, 9, 0, 0, 1.0, 0, 0, 0.5, /**/  7.5, 11, 0, 0, 1.0, 0, 0, 0.5, /**/  10, 9, 0, 0, 1.0, 0, 0, 0.5,/**/  12.5, 10, 0, 0, 1.0, 0, 1, 0.5, ),
                        "." => array( /*header*/ 2,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/  0.5, 0, 0, 1, 1.0, 5, 0, 0, /**/ 1, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 1, 0, 0, 1.0, 0, 1, 0.5, /**/ 0, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 0, 0, 0, 1.0, 0, 1, 0.5, ),
                        "," => array( /*header*/ 3,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/ 2, -2, 0, 1, 1.0, 5, 0, 0, /**/ 3,  1, 0, 0, 1.0, 0, 1, 0.5, /**/  ),
                        ";" => array( /*header*/ 3,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/  2.5, 4.5, 0, 1, 1.0, 5, 0, 0, /**/ 3, 5, 0, 0, 1.0, 0, 1, 0.5, /**/ 2.5, 5.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 2, 5, 0, 0, 1.0, 0, 1, 0.5, /**/ 2.5, 4.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 3, 1, 0, 1, 1.0, 5, 0, 0, /**/ 2, -2, 0, 0, 1.0, 0, 1, 0.5, /**/  ),
                        ":" => array( /*header*/ 3,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/  0.5, 4.5, 0, 1, 1.0, 5, 0, 0, /**/ 1, 5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 5.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0, 5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 4.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 0, 0, 1, 1.0, 5, 0, 0, /**/ 1, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 1, 0, 0, 1.0, 0, 1, 0.5, /**/ 0, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 0, 0, 0, 1.0, 0, 1, 0.5,  ),
                        "!" => array( /*header*/ 2,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/  0.5, 20, 0, 1, 1.0, 5, 0, 0, /**/ 0.5, 5, 0, 1, 1.0, 0, 0, 0, /**/  0.5, 0, 0, 1, 1.0, 5, 0, 0, /**/ 1, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 1, 0, 0, 1.0, 0, 1, 0.5, /**/ 0, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 0, 0, 0, 1.0, 0, 1, 0.5, ),
                        "?" => array( /*header*/ 5,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   0, 15, 0, 0, 1.0, 5, 0, 0.5, /**/   1.25, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/   2.5, 20, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3.75, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5, 15, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5, 13, 0.5, 0, 1.0, 0, 0, 0.5, /**/   2.5, 10, 0.5, 0, 1.0, 0, 0, 0.5, /**/   2.5, 5, 0.5, 0, 1.0, 0, 0, 0, /**/    2.5, 0, 0, 1, 1.0, 5, 0, 0, /**/ 3, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 2.5, 1, 0, 0, 1.0, 0, 1, 0.5, /**/ 2, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 2.5, 0, 0, 0, 1.0, 0, 1, 0.5, ),
                        "-" => array( /*header*/ 5,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   0, 11, 0, 1, 1.0, 5, 0, 0.5, /**/   /**/ 5, 11, 0, 0, 1.0, 0, 0, 0.5, /**/ 0, 9, 0, 0, 1.0, 5, 0, 0.5, /**/   /**/ 5, 9, 0, 0, 1.0, 0, 1, 0.5, ),
                        "'" => array( /*header*/ 1,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   0, 14, 0, 1, 1.0, 5, 0, 0.5, /**/ 1, 15, 0, 0, 1.0, 0, 0, 0.5, /**/   /**/ 1, 18, 0, 0, 1.0, 0, 0, 0.5,  ),
                        "\"" => array( /*header*/ 3,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   0, 14, 0, 1, 1.0, 5, 0, 0.5, /**/ 1, 15, 0, 0, 1.0, 0, 0, 0.5, /**/   /**/ 1, 18, 0, 0, 1.0, 0, 0, 0.5, /**/ 2, 14, 0, 1, 1.0, 5, 0, 0.5, /**/ 3, 15, 0, 0, 1.0, 0, 0, 0.5, /**/ 3, 18, 0, 0, 1.0, 0, 0, 0.5,  ),
                        
                        // symbols []() can't be used for opening and closing brackets (used as separator tokens by bundler and for tokenlist)
                        "(" => array( /*header*/ 5,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   5, 21, 0, 1, 1.0, 5, 0, 0.5, /**/ 0, 11, 0, 0, 1.0, 0, 0, 0.5, /**/   /**/ 5, 1, 0, 0, 1.0, 0, 0, 0.5,  ),
                        ")" => array( /*header*/ 5,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   0, 21, 0, 1, 1.0, 5, 0, 0.5, /**/ 5, 11, 0, 0, 1.0, 0, 0, 0.5, /**/   /**/ 0, 1, 0, 0, 1.0, 0, 0, 0.5,  ),
                    
                        // define vowels and distance symbols
                        "A" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"no","narrow","yes",0,0, /*data*/ ),
                        "#0" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"no","none","no",0,0, /*data*/ ),
                        "#0S" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"no","none","yes",0,0, /*data*/ ),
                        "#N" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"no","narrow","no",0,0, /*data*/ ),
                        "#W" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"no","wide","no",0,0, /*data*/ ),
                        "#NS" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"no","narrow","yes",0,0, /*data*/ ),
                        "#WS" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"no","wide","yes",0,0, /*data*/ ),
                        "#NDS" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"down","narrow","yes",0,0, /*data*/ ),
                        "#WDS" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"down","wide","yes",0,0, /*data*/ ),
                        "#WD" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"down","wide","no",0,0, /*data*/ ),
                        "#WU" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"up","wide","no",0,0, /*data*/ ),
                        "#WUS" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"up","wide","yes",0,0, /*data*/ ),
                        "E" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"no","wide","no",0,0, /*data*/ ),
                        "I" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"up","narrow","no",0,0, /*data*/ ),
                        "O" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"down","narrow","no",0,0, /*data*/ ),
                        "U" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"down","narrow","yes",0,0, /*data*/ ),
                        "Ä" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"no","wide","yes",0,0, /*data*/ ),
                        "Ö" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"down","wide","no",0,0, /*data*/ ),
                        "Ü" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"down","wide","yes",0,0, /*data*/ ),
                        "EU" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"up","wide","yes",0,0, /*data*/ ),
                        "AU" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"up","narrow","yes",0,0, /*data*/ ),
                        "EI" => array( /*header*/ 0,  0, 0, 0,   0,   0, 0, "", /**/ "","","","",2,0,0,0, /*++*/ 0,0,0,"up","wide","no",0,0, /*data*/ ),

                        // normal characters (used for normal text displayed as handwritten characters with same method as stenotokens inside svg 
                        // In order to avoid confusions, normal characters are preceeded by # and followed by + (upper case) or - (lower case)
                        // In bundler-notation the are placed inside [] (brackets)
                        // This is used to distinguish them from stenotokens
                        //
                        // examples:
                        // "[#A+]" <=> normal (= handwritten) upper case A (A)
                        // "[#A-]  <=> normal (= handwritten) lower case A (a)
                        
                        // numbers
                        "0" => array( /*header*/ 7,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   3.5, 1, 0, 0, 1.0, 5, 0, 0.5, /**/   6, 2.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/   7, 10, 0.5, 0, 1.0, 0, 0, 0.5, /**/   6, 17.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3.5, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/   1, 17.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/   0, 10, 0.5, 0, 1.0, 0, 0, 0.5, /**/   1, 2.5, 0.5, 0, 1.0, 0, 0, 0, /**/   3.5, 1, 0, 1, 1.0, 0, 0, 0, /**/ 6, 2.5, 0, 0, 1.0, 5, 1, 0.5, /**/ /*2.5, 1, 0, 0, 1.0, 0, 1, 0.5, /**//* 2, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ /*2.5, 0, 0, 0, 1.0, 0, 1, 0.5,*/ ),
                        "1" => array( /*header*/ 7,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   0, 11, 0, 0, 1.0, 5, 0, 0, /**/   7, 19, 0, 0, 1.0, 0, 0, 0, /**/   7, 1, 0, 0, 1.0, 0, 0, 0, /**/  /* 3.75, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/  /* 5, 15, 0.5, 0, 1.0, 0, 0, 0.5, /**/    ),
                        "2" => array( /*header*/ 7,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   0, 14, 0, 0, 1.0, 5, 0, 0.5, /**/   1.25, 18, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3.5, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5.75, 18, 0.5, 0, 1.0, 0, 0, 0.5, /**/   7, 15, 0.5, 0, 1.0, 0, 0, 0, /**/   0, 1, 0, 0, 1.0, 0, 0, 0.5, /**/   7, 1, 0, 0, 1.0, 0, 0, 0, /**/   /*2.5, 5, 0.5, 0, 1.0, 0, 0, 0, /**/    /*2.5, 0, 0, 1, 1.0, 5, 0, 0, /**/  ),
                        "3" => array( /*header*/ 7,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   0, 14, 0, 0, 1.0, 5, 0, 0.5, /**/   1.25, 18, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3.5, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5.75, 18, 0.5, 0, 1.0, 0, 0, 0.5, /**/   7, 15, 0.5, 0, 1.0, 0, 0, 0, /**/   3.5, 11, 0, 0, 1.0, 0, 0, 0.5, /**/   7, 7, 0.5, 0, 1.0, 0, 0, 0.5, /**/   6, 2.5, 0.5, 0, 1.0, 0, 0, 0, /**/    3.5, 1, 0, 1, 1.0, 0, 0, 0, /**/ 0, 5, 0, 0, 1.0, 0, 1, 0, /**/ /*2.5, 1, 0, 0, 1.0, 0, 1, 0.5, /**/ /*2, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "4" => array( /*header*/ 7,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   3.5, 19, 0, 0, 1.0, 5, 0, 0, /**/   0, 11, 0, 0, 1.0, 0, 0, 0.5, /**/   7, 11, 0, 0, 1.0, 0, 0, 0, /**/   5.5, 19, 0, 0, 1.0, 5, 0, 0, /**/   5.5, 1, 0, 0, 1.0, 0, 0, 0, /**/   /*3.5, 11, 0, 0, 1.0, 0, 0, 0.5, /**/   /*7, 7, 0.5, 0, 1.0, 0, 0, 0.5, /**/     ), 
                        "5" => array( /*header*/ 7,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   7, 19, 0, 0, 1.0, 5, 0, 0, /**/   0, 19, 0, 0, 1.0, 0, 0, 0, /**/   0, 11, 0, 0, 1.0, 0, 0, 0.5, /**/   6, 10, 0.5, 0, 1.0, 0, 0, 0.5, /**/   7, 5, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3.5, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/   0, 4, 0.5, 0, 1.0, 0, 0, 0, /**/   /*2.5, 5, 0.5, 0, 1.0, 0, 0, 0, /**/     ),
                        "6" => array( /*header*/ 7,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   7, 19, 0, 0, 1.0, 5, 0, 0.5, /**/   1, 11, 0.5, 0, 1.0, 0, 0, 0.5, /**/   0, 5, 0.5, 0, 1.0, 0, 0, 0.5, /**/   1.5, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3.5, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/   6, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/   7, 5, 0.5, 0, 1.0, 0, 0, 0.5, /**/   /*6, 7, 0.5, 0, 1.0, 0, 0, 0.5, /**/    3.5, 10, 0.5, 1, 1.0, 0, 0, 0.5, /**/ 0, 5, 0.5, 0, 1.0, 0, 1, 0.5, /**/ /*2.5, 1, 0, 0, 1.0, 0, 1, 0.5, /**/ /*2, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "7" => array( /*header*/ 7,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   0, 19, 0, 0, 1.0, 5, 0, 0, /**/   7, 19, 0, 0, 1.0, 0, 0, 0, /**/   3, 1, 0, 0, 1.0, 0, 0, 0, /**/   3.5, 11, 0, 0, 1.0, 5, 0, 0, /**/   6.5, 11, 0, 0, 1.0, 0, 0, 0, /**/   /*5, 13, 0.5, 0, 1.0, 0, 0, 0.5, /**/    ),
                        "8" => array( /*header*/ 7,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   3.5, 11, 0, 0, 1.0, 5, 0, 0.5, /**/   7, 15, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3.5, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/   0, 15, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3.5, 11, 0.5, 0, 1.0, 0, 0, 0.5, /**/   7, 6, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3.5, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/   0, 6, 0.5, 0, 1.0, 0, 0, 0, /**/    3.5, 11, 0, 1, 1.0, 0, 0, 0, /**/ /*3, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "9" => array( /*header*/ 7,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   7, 14, 0, 0, 1.0, 5, 0, 0.5, /**/   3.5, 10, 0.5, 0, 1.0, 0, 0, 0.5, /**/   0, 15, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3.5, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/   7, 15, 0.5, 0, 1.0, 0, 0, 0.5, /**/   6, 6, 0.5, 0, 1.0, 0, 0, 0.5, /**/   /*3.5, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3.5, 1, 0.5, 0, 1.0, 0, 0, 0, /**/     ),
                        
                        //"" => array( /*header*/ 7,  0,       0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   x, y, 0, 1, 1.0, 5, 0, 0.5, /**/   x, y, t1, 0, 1.0, 0, 0, t2, /**/ ),
                        "#A+" => array( /*header*/ 7,  0,       0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   0, 0, 0, 1, 1.0, 5, 0, 0, /**/   3.5, 19, 0, 0, 1.0, 0, 0, 0, /**/ 7, 0, 0, 0, 1.0, 0, 0, 0, /**/ 1.75, 9.5, 0, 0, 1.0, 5, 0, 0, /**/ 5.25, 9.5, 0, 0, 1.0, 0, 1, 0, /**/ ),
                        "#A-" => array( /*header*/ 7,  0,       0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   5.25, 9, 0, 1, 1.0, 5, 0, 0.5, /**/   4.5, 9.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 0.5, 3.75, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 0.5, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 1.5, 0, 0.5, 0, 1.0, 0, 0, 0, /**/ 5.25, 9, 0, 0, 1.0, 0, 0, 0.5, /**/ 5.25, 1, 0.5, 0, 1.0, 0, 0, 0, /**/ 6, 0, 0, 0, 1.0, 0, 1, 0, /**/),
                        
                    );


// combiner table: defines which tokens can be combined with other ones
// is used by CreateCombinedTokens and TokenCombiner to create those tokens and add them to $steno_tokens_master
// the name of the resulting token will allways be: key + value (e.g. "T" + "@R" will be "T@R")
$combiner_table = array(
    //array("T15", "B"),  // test
    //array("^1", "B"),  // test
    array("D", "@R", 0, 0),
    array("ND", "@R", 0, 0),
    array("T", "@R", 0, 0),
    array("G", "@R", 0, 0),
    array("K", "@R", 0, 0),
    array("CH", "@R", 0, 0),
    array("NK", "@R", 0, 0),
    array("SCH", "@R", 0, 0),
    array("ST", "@R", 0, 0),
    array("L", "@L", 0, 0),
    array("B", "@L", 0, 0),
    array("G", "@L3", 0, 0),
    array("M", "@L", 0, 0),
    array("F", "@L", 0, 0),
    array("P", "@L", 0, 0),
    array("PF", "@L", 0, 0),
    array("V", "@L", 0, 0),
    array("SP", "@L", 0, 0),
    array("W", "@L", 0, 0),
    array("T", "@L3", 0, 0),
    array("NG", "@L3", 0, 0),
    array("D", "@L3", 0, 0),
    array("ND", "@L3", 0, 0),
    array("ST", "@L3", 0, 0),
    array("NK", "@L3", 0, 0),
    array("K", "@L3", 0, 0),
    array("Z", "@L3", 0, 0),
    array("SCH", "@L3", 0, 0),
    array("CH", "@L3", 0, 0),
    array("B", "@R6", 0, 0),
    array("SP", "@R6", 0, 0),
    array("F", "@R6", 0, 0),
    array("M", "@R6", 0, 0),
    array("P", "@R6", 0, 0),
    array("PF", "@R6", 0, 0),
    array("V", "@R6", 0, 0),
    array("W", "@R6", 0, 0),
    array("Z", "@R", 0, 0),
    array("Z", "@L3", 0, 0),
    array("DA", "@R", 0, 0),
    array("N", "@R6", 0, 0),
    array("N", "@L", 0, 0),
    array("VR", "@L", 0, 0.5),  // should be obsolete with following line => not necessary: implement deltay for tokencombiner
    //array("/VR", "@L"), // unnecessary, if tokencombiner offers possibility to add deltay like tokenshifter => implement that
    array("CK", "@L", 0, 0),
    array("L", "@R6", 0, 0),
    array("TT", "@R", 0, 0), // "Wettrennen" apparently written with that token combination - even if it's completely illogical! (same for "erfolgreich": "Rennen" y "reich" are separate words 
                             // that can clearly be recognized 
                             // as such and should therefore be written without combinations ttr and gr ... I'm sure some shorthand aficionado will explain me that this has its (own) logic ... 
                             // from a strictly formal point of view: it is NOT logical at all! (And I'd even say that there is a point, where all logics come to an end ... ;-)
);

$shifter_table = array(
    
    // "dem" at the beginning of words (e.g. demnach) needs higher position => use shifter for that
    array("DEM", "^DEM", 0, 5, 0, 0 ),      // ^DEM = dem 1/2 line up
    array("DEN", "^DEN", 0, 5, 0, 0 ),      // ^DEN = den 1/2 line up
    array("DEM", "^^DEM", 0, 10, 0, 0 ),    // ^DEN = den 1 line up
    array("DEN", "^^DEN", 0, 10, 0, 0 ),    // ^DEN = den 1 line up
    
    // special treatment: for -tes y -eites shift abbreviation "sein" (works like a charm ... :-), just had to fiddle arround a bit with the distances ... ;-)
    array("SEIN", "&EITES", 13, 10, 0, 1),  
    array("SEIN", "&TES", 2, 10, 0, 1),  
    
    // &T mit einstufigen Zeichen
    array("B", "&TB", 4, 10, 0, 1),
    array("NG", "&TNG", 4, 10, 0, 1),
    array("NS", "&TNS", 4, 10, 0, 1),
    array("C", "&TC", 4, 10, 0, 1),
    array("D", "&TD", 4, 10, 0, 1),
    array("G", "&TG", 4, 10, 0, 1),
    array("H", "&TH", 4, 10, 0, 1),
    array("J", "&TJ", 4, 10, 0, 1),
    array("L", "&TL", 4, 10, 0, 1),
    array("@L3", "&T@L3", 4, 16.5, 0, 1.5),
    array("ALL", "&TALL", 4, 10, 0, 1),
    array("M", "&TM", 4, 10, 0, 1),
    array("W", "&TW", 4, 10, 0, 1),
    array("X", "&TX", 4, 10, 0, 1),
    array("Y", "&TY", 4, 10, 0, 1),
    // &T mit halbstufigen Zeichen
    array("&E", "&T&E", 4, 15, 0, 1.5),
    array("SCHAFT", "&TSCHAFT", 4, 15, 0, 1.5),
    array("ION", "&TION", 4, 10, 0, 1),
    
    //array("", "&T", 4, 10, 0, 1.5), // I
    array("N", "&TN", 4, 15, 0, 1.5),
    array("VR", "&TVR", 4, 15, 0, 1.5),
    array("AR5", "&TAR5", 4, 15, 0, 1.5),
    array("S", "&TS", 4, 15, 0, 1.5),
    //array("", "&T", 4, 10, 0, 1.5), // tes
    array("&U", "&T&U", 4, 15, 0, 1.5),
    array("&O", "&T&O", 4, 15, 0, 1.5),
    array("&I", "&T&I", 4, 15, 0, 1.5),
    
    // &T mit zweistufigen Zeichen
    array("CH", "&TCH", 4, 20, 0, 1),
    array("SCH", "&T^SCH", 4, 20, 0, 1),
    array("^Z", "&T^Z", 4, 20, 0, 1),
    array("^CH", "&T^CH", 4, 20, 0, 1),
    array("^SCH", "&T^SCH", 4, 20, 0, 1),
    array("Z", "&TZ", 4, 20, 0, 1),
    array("F@L", "&TF@L", 4, 10, 0, 1),
    array("F", "&TF", 4, 10, 0, 1),
    array("HAFT", "&THAFT", 4, 10, 0, 1),
    array("K", "&TK", 4, 20, 0, 1),
    array("P", "&TP", 4, 20, 0, 1),
    array("Q", "&TQ", 4, 20, 0, 1),
    array("V", "&TV", 4, 20, 0, 1),
    array("NK", "&TK", 4, 20, 0, 1),
    array("PF", "&TPF", 4, 20, 0, 1),
    array("SP", "&TSP", 4, 20, 0, 1),
    array("ST", "&TST", 4, 10, 0, 1),
    
    // &T mit dreistufigen Zeichen
    array("SCHW", "&TSCHW", 4, 30, 0, 1),
    array("ZW", "&TZW", 4, 30, 0, 1),
    //array("FF", "&TFF", 4, 30, 0, 1),
    //array("PP", "&TPP", 4, 30, 0, 1),
    //array("CK", "&TCK", 4, 30, 0, 1),
    
    // ********************************
    // idem {EIT}
    // &EIT mit einstufigen Zeichen
    array("B", "&EITB", 18, 10, 0, 1),
    array("NG", "&EITNG", 18, 10, 0, 1),
    array("NS", "&EITNS", 18, 10, 0, 1),
    array("C", "&EITC", 18, 10, 0, 1),
    array("D", "&EITD", 18, 10, 0, 1),
    array("G", "&EITG", 18, 10, 0, 1),
    array("H", "&EITH", 18, 10, 0, 1),
    array("J", "&EITJ", 18, 10, 0, 1),
    array("L", "&EITL", 18, 10, 0, 1),
    array("@L3", "&EIT@L3", 18, 10, 0, 1),
    array("ALL", "&EITALL", 18, 10, 0, 1),
    array("M", "&EITM", 18, 10, 0, 1),
    array("W", "&EITW", 18, 10, 0, 1),
    array("X", "&EITX", 18, 10, 0, 1),
    array("Y", "&EITY", 18, 10, 0, 1),
    // &EIT mit halbstufigen Zeichen
    array("&E", "&EIT&E", 18, 15, 0, 1.5),
    array("SCHAFT", "&EITSCHAFT", 18, 15, 0, 1.5),
    
    //array("", "&T", 4, 10, 0, 1.5), // I
    array("N", "&EITN", 18, 15, 0, 1.5),
    array("VR", "&EITVR", 18, 15, 0, 1.5),
    array("S", "&EITS", 18, 15, 0, 1.5),
    //array("", "&T", 4, 10, 0, 1.5), // tes
    array("U", "&EITU", 18, 15, 0, 1.5),
    array("&O", "&EIT&O", 18, 15, 0, 1.5),
    // &EIT mit zweistufigen Zeichen
    array("CH", "&EITCH", 18, 20, 0, 1),
    array("SCH", "&EITSCH", 18, 20, 0, 2),
    array("Z", "&EITZ", 18, 20, 0, 1),
    array("^Z", "&EIT^Z", 4, 20, 0, 1),
    array("^CH", "&EIT^CH", 4, 20, 0, 1),
    array("^SCH", "&EIT^SCH", 4, 20, 0, 1),
    
    array("F", "&EITF", 18, 10, 0, 1),
    array("K", "&EITK", 18, 20, 0, 1),
    array("P", "&EITP", 18, 20, 0, 1),
    array("Q", "&EITQ", 18, 20, 0, 1),
    array("V", "&EITV", 18, 20, 0, 1),
    array("NK", "&EITK", 18, 20, 0, 1),
    array("PF", "&EITPF", 18, 20, 0, 1),
    array("SP", "&EITSP", 18, 20, 0, 1),
    array("ST", "&EITST", 18, 10, 0, 1),
    
    // &EIT mit dreistufigen Zeichen
    array("SCHW", "&EITSCHW", 18, 30, 0, 1),
    array("ZW", "&EITZW", 18, 30, 0, 1),
    //array("FF", "&TFF", 4, 30, 0, 1),
    //array("PP", "&TPP", 4, 30, 0, 1),
    //array("CK", "&TCK", 4, 30, 0, 1),
);

//////////////////////////////////////////// end of data definitions //////////////////////////////////////////
?>