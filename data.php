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
 
/////////////////////////////////////// data definitions /////////////////////////////////////////////////////

// define decapitalizer (changes all uppercase caracters to lower case => marks works starting with uppercase as "nouns" in order to show them in different color (not yet implemented))
// see function that uses mb_strtolower (no table)


// define helvetizer
$filter_table = array(
    // use this to filter out special characters that the program can't handle
    "»" => "",
    "«" => "",
    //"&quot;" => "",
    "&.*;-" => "", // filter out all escaped character from htmlspecialchars
    //"\uxC2" => "",
);

$helvetizer_table = array (
    "ß" => "ss",
    
);

// trickster is applied before decapitalizer ?!? (maybe that's not good ...)
// the trickster tries to avoid some automatical changes done by the parserchain (especially shortings by the shortener)
$trickster_table = array(
     "Variete" => "var[#N][&I][E]te", // should go into dictionary ?!
     "Uri" => "[0N-][U]r[#N][&I]", // should go into dictionary ?!
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
     "[Bb]eiz" => "B[EI]z",
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
    
);

// define lexical entries in dictionary
$dictionary_table = array (  
              
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
                       //"sozusagen" => "{so}zsagen",
                       //"soweit" => "{so}w{heit}",
                       //"solange" => "{so}lange",
                       //"worauf" => "{wor}{auf}",
                       //"wonach" => "{wo}nach",
                       //"wohin" => "{wo}hn",
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
                       "ins" => "I[NS]",
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
                           //"Uri" => "[0N-][U]r[#N][&I]", // should go into dictionary ?!
                            
                           //"\." => "", // vor the moment: filter out all point (otherwise detection of end of word won't work correctly)
                            //"\," => "", // vor the moment: filter out all point (otherwise detection of end of word won't work correctly)
                            "qu" => "q",                                                // makes it easier to handle => normally, should go to a separate, preceeding parser function, e.g. "simplifier"
                            "th" => "t",                                                // should be done in a separate part (e.g. "prenormalizer")
                            "ä([eaio])" => "ä[&A]$1",                                   // easier to do here, but should go to separate parser
                            "ö([aeiou])" => "ö[&O]$1",                                   // easier to do here, but should go to separate parser
                            "a([aeio])" => "a[&A]$1",                                   // easier to do here, but should go to separate parser
                            "o([aeiu])" => "[&O]$1",                                   // easier to do here, but should go to separate parser
                            "ü([aeiou])" => "[#WS][&U]$1",                                   // easier to do here, but should go to separate parser
                            "i[o|ö]nn?" => "{ION}",
                            "i([aou])" => "[&I]$1",                                   // easier to do here, but should go to separate parser
                            "e([ao])" => "[#W][&E]$1",                                   // easier to do here, but should go to separate parser
                            "ei([aeou])" => "[EI][&E]$1",                                   // easier to do here, but should go to separate parser
                            "eu([aeou]|lich)" => "[EU][&E]$1",                                   // easier to do here, but should go to separate parser
                            "ei([aeou]|lich)" => "[EI][&E]$1",                                   // easier to do here, but should go to separate parser
                            
                            "(.)schaft" => "\$1{SCHAFT}",
                            "usw" => "{USW}",
                            "usf" => "{USF}",
                            "über" => "{ÜBER}",
                            "unter" => "{UNTER}",
                            "^(dar|wor)?auf" => "$1{AUF}",
                            "wie?der" => "{WIDER}",
                            "([AEIOUÄÖÜaeiouäöü]+[bcdfghjklmnpqrstvwxyz\]]*)er$" => "$1{ER}",    // shorten -er => -r in bi- or multisyllabic words
                            "^unange" => "{UN}an{GE}",
                            "haft(e|en|es)?" => "{HAFT}$1",
                            "gegen" => "{GEGEN}",
                            "zum" => "Zum",
                            "geheim" => "{GE}heim",
                            "etz" => "ez",
                            "^woher" => "{WO}her",
                            "ndwo$" => "nd{WO}",
                            "trotzdem" => "trotz{DEM}", 
                            "(s|m)ehr" => "$1Er",
                            "damit" => "{DA}{MIT}",
                            "tret" => "trEt",
                            "^gebe" => "Gebe",
                            "^gern" => "Gern",
                            "^erd" => "[0N-]Erd",
                            "^angab" => "aNgab",
                            "heraus" => "her{AUS}",
                            "rosen" => "roseN",
                            "^zur$" => "[Z]ur",
                            "^uns(er.*)?$" => "u[NS]$1",
                            "davon" => "{DA}{VON}",
                            "^beid(e[n|r|s]?)" => "[B]eid$1",
                            "all(e[n|m|s]?)$" => "{ALL}$1",
                            "^all" => "{ALL}",
                            "mal(s)" => "MAL\$1",
                            "als" => "{ALS}",
                            "zurück" => "{ZURÜCK}",
                            "dazu" => "{DA}{ZU}",
                            "(\[^a-z\])all" => "{\$1ALL}",
                            "^auch$" => "{AUCH}",
                            "^auss" => "au[SS]",
                            "^aus" => "{AUS}",
                            "beinahe" => "{BEI}nahe",
                            "bein" => "[B][EI][N]",
                            "^darf" => "[D]arf",
                            "^dar" => "{DA@R}",
                            "dass?" => "{DASS}",
                            "^der" => "{DER}",
                            "^dem" => "{DEM}",
                            "^denn" => "de[NN]",
                            "denk" => "de[NK]",         // avoid shortening of "denker, denken" etc.
                            "^den" => "{DEN}",
                            "^des" => "{DES}",
                            "^dies" => "{DIS}",
                            "^die$" => "{DI}",
                            "durch" => "{DURCH}",
                            "fürch" => "Fürch",
                            "für" => "{FÜR}",
                            "gehabt" => "{GEHABT}",
                            "hab" => "{HAB}",
                            "hatt?" => "{HAT}",
                            "hint" => "{HINT}",
                            "^chin" => "[CH]in",       // avoid shortening "hin" in "chin"
                            "hin" => "{HIN}",
                            "^in\$" => "{IN}",
                            //"itinhab" => "it{IN}hab",
                            "^ist\$" => "{IST}",
                            "mitt" => "m1i2t3t4",           // convert mitt to something else
                            "mit" => "{M}",                   // perform mit substitution
                            "m1i2t3t4" => "mitt",           // convert mitt back
                            "^nicht" => "{NICHT}",
                            "sein" => "{SEIN}",
                            "^sie\$" => "{SI}",
                            "sind" => "{SIND}",
                            "solch" => "{SOLCH}",
                            "^und\$" => "{UND}",
                            "usw." => "{USW.}",
                            "vielleicht" => "{VILLEICHT}",
                            "von" => "{VON}",
                             "^war(st|en)?$" => "{WAR}$1",
                            "^wär(e?st|e|en|et|t)?$" => "{WÄR}$1",
                            "welch" => "{WELCH}",
                            "wenn" => "{WENN}",
                            "^werd" => "{WERD}",
                            "^wie$" => "{WI}",     // ! is wrong!
                            "wird" => "{WIRD}",
                            "zusammen" => "{ZUSAMMEN}",
                            "([^{])zu([^g]?)$" => "\$1{ZU}\$2",
                            "^(ein|auf|bei|an|vor)?zu" => "$1{ZU}",
                            "vor" => "{VOR}",
                            "fort" => "{FORT}",
                            "([hk]?[e|a]it)|([ie]?tät)" => "{HEIT}",
                            "bei" => "{BEI}",
                            "geh" => "Geh",
                            "^(herein|an|un|gegen|ab|auf)?ge" => "\$1{GE}",        
                            "^be(un|ur)" => "{BE}$1",
                            "^beu" => "b[EU]",              // if combination is not be(un|ur) consider eu as diphtong
                            "^(an)?be" => "\$1{BE}",                
                            "^anti" => "{ANTI}",
                            
                            "anten$" => "[A]nten",
                            "([^gz]?)[a|e]nt([^i])" => "\$1{ANT}\$2",
                            "^({?des}?)?inter" => "$1{INTER}",
                            "^(in|un)?[k|c]on([^n])" => "\$1{CON}\$2",
                            "^(ur|an|un|zu|selb|selbst)?ver" => "\$1{VER}",
                            "selb" => "{SELB}",
                            "^(un)?er" => "$1{ER}",
                            "^(un)?zer" => "\$1{ZER}",
                            "^rück" => "{RÜCK}",           // why doesn't it work?!?
                            "^({?ver}?)?un" => "\$1{UN}",      
                            "^({?ver}?)?ur" => "\$1{UR}",       
                            "^({?des}?)?in" => "\$1{IN}",
                            "eien" => "[EI][&E]{EN}",
                            "ien$" => "[&I]{EN}",
                            "en\$" => "{EN}",
                            "em\$" => "{EM}",
                            "(\[^i\])er\$" => "\$1{ER}",
                            "je" => "[J][E]",
                            "planet" => "[P@L][A][N][E][T]",
                            "([AEIOUaeiouäöü\]+[bcdfghjklmnpqrstvwxyz]*)et(e?|en?)" => "$1{ET}\$2", // only multisyllabic words
                            
                            "([^c])(haft)\$" => "\$1{HAFT}",
                            "(.)lich([^t])|(.)lich\$" => "\$1$3{LICH}\$2$4",
                            "(.)t[u|\ü]m" => "\$1{TUM}",           // umlaut ..
                            "{ANT}{LICH}" => "en{TLICH}",
                            "{ET}t" => "e[TT]",
                              
                        );

// define normalizer (handles ortographical irregularities and corrects them)
$normalizer_table = array(
                            "a([ah])([flmnrst])" => "a\$2",
                            "o([oh])([flmnrst])" => "o\$2",
                            "ieh(\[tmn\])" => "i\$1",
                            "e([eh])([flmnrst])" => "e\$2",
                            "äh([nrl])" => "ä$1",
                            "öh" => "ö",
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
                            
);

// defines all available stenotokens and if they are longer than 1 character, bundles them into []
$bundler_table = array( 
                    //"([bcdfgklmnprstxyz])([lr])" => "[\$1\$2]",
                    "schl" => "[SCHL]",
                    "schm" => "[SCHM]",
                    "schw" => "[SCHW]",
                    "schr" => "[SCHR]",
                    "schl" => "[SCHL]",
                    
                    "sch" => "[SCH]",
                    "mpfr" => "[MPFR]",
                    "mpfl" => "[MPFL]",
                    "mpf" => "[MPF]",
                    "str" => "[STR]",
                    "nkr" => "[NKR]",
                    "nkl" => "[CHR]",
                    "stl" => "[STL]",
                    "spl" => "[SPL]",
                    "spr" => "[SPR]",
                    "nkl" => "[NKL]",
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
                    "ns" => "[NS]",
                    
                    "bl" => "[BL]",
                    "cl" => "[CL]",
                    "dl" => "[DL]",
                    "fl" => "[FL]",
                    "ngl" => "[NGL]",
                    "ng" => "[NG]",     // before gl, e.e. englisch => e[NG]lisch => en[NGL]isch => e[NG@L3]isch
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
                    "tt" => "[TT]",
                    "ch" => "[CH]", // (*^\])])
                    "nd" => "[ND]",
                    
                    "nk" => "[NK]",
                    "pf" => "[PF]",
                    "sp" => "[SP]",
                    "zw" => "[ZW]",
                    "au" => "[AU]",
                    "äu" => "[EU]",
                    "eu" => "[EU]",
                    "ei" => "[EI]",   
                    // "qu" => "[QU]",              // not necessary if qu => qu
                        
);


// transcriptor
$transcriptor_table = array(   

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
                    "\[TL\]" => "[T@L3]", // disabled for test
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
                    "\[VR\]" => "[V@R6]",
                    "\[PR\]" => "[P@R6]",
                    "\[PFR\]" => "[PF@R6]",
                    "\[WR\]" => "[WR@R6]",
                    "\[ZL\]" => "[Z@L3]",
                    "\[RL\]" => "[VR@L]",
                    "\[GL\]" => "[G@L]",
                    "\[SR\]" => "[S][AR]",
                    "\[DL\]" => "[D@L3]",
                    "\[STL\]" => "[ST@L3]",
                    "\[SL\]" => "s[@L]",        // only a quick fix: a special token for sl should be defined (e.g S1 with line at the end, which is combined with @L to form [S1@L]
                    
                    "\{ANT\}" => "[0N-][#N]{ANT}",
                    "{HEIT}{ET}" => "{HEIT}[&E][ET3/4]",
                    "{ET}(e|{EN}|{EM})" => "{ET}[&E]\$1",
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
                    
                    "{HEIT}e" => "{HEIT}[&E][-E]",
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
                    "\[&T\]e" => "[&T][&E]E",
                    "\[&T\]a" => "[&T][&E]A",
                    "\[&T\]o" => "[&T][&E]O",
                    "\[&T\]u" => "[&T][&E]U",
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
                    "a\$" => "[A][&A]",
                    "e\$" => "[-E]",
                    "i\$" => "[#N][&I]",
                    "o\$" => "[#N][&O]",
                    "u\$" => "[#NS][&U]",
                    "ä\$" => "[#WS][&A]",
                    "ö\$" => "[#W][&O]",
                    "ü\$" => "[#WS][&U]",
                    "\[EU\]\$" => "[EU][&E]",
                    "\[AU\]\$" => "[AU][&E]",
                    
                    // initial vowels
                    "^a" => "[0N-][A]",
                    "^e" => "[0N-][E]",
                    //"^i\[CH" => "[0N-][I][CH",
                    "^i\[(S?CH|ZW?)" => "[0N-][I][\$1",
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
                   
                    // single vowels
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
                    "\[VR\]\[VR\]" => "[VR+AR]",
                    "^{HEIT}" => "[0N-]{HEIT}",
                    
                    "IU" => "[&I]U", 
                    "IE" => "[&I]E",
                    "IA" => "[&I]A",
                    "IO[^N]" => "[&I]O",
                    "II" => "[&I]I",
                    "OU" => "[&O]U",
                    "\[EU\]E" => "[EU][&E]",
                    "E\[-E\]" => "[#N][-E]",
                    "\[E\]$" => "[-E]",
                    "E{EN}" => "E[&E]{EN}",
                    
                   // "\[(CH|SCH)\]{EN}" => "[$1][EN2]",
                    
                    // test with special stenotoken for SCH and others in higher position
                    "(I|\[EI\]|\[AU\]|\[EU\])\[SCH\]" => "$1[^SCH]",
                    "(I|\[EI\]|\[AU\]|\[EU\])\[SCHW\]" => "$1[^SCHW]",
                    "(I|\[EI\]|\[AU\]|\[EU\])\[SCHM\]" => "$1[^SCHM]",
                    "(I|\[EI\]|\[AU\]|\[EU\])\[ZW\]" => "$1[^ZW]",
                    "(I|\[EI\]|\[AU\]|\[EU\])Z" => "$1[^Z]",
                    "(I|\[EI\]|\[AU\]|\[EU\])\[CH\]" => "$1[^CH]",
                    
          
                        );

// substituter table: replaces shortings by steno tokens that can be handled directly by steno engine
$substituter_table = array(
                            "{DASS?}" => "[0N-][#NS][S]",
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
                            "{VER}" => "[0N-][EN][AR]",
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
                            "{HIN}" => "[H][N]",
                            "{HINT}" => "[H][N][&T]",
                            "\[&T\]{EN}$" => "[&T][&E][EN]",
                            "\[&T\]{EM}$" => "[&T][&E][EM]",
                            "\[&T\]\[#N\]\[VR\]$" => "[&T][VR]",
                            
                            "{-ER}" => "[VR]",
                            "{USW}" => "[A][&U][SS]",
                            "{USF}" => "[A][&U][SS][FORT]",
                            "{GEHABT}" => "[G][&T]",
                            "{HAB}T" => "[HAB][&T]",
                            "{HAB}" => "[HAB]",
);

// base definitions for all tokens  x, y, t, d1, th, 0, d2, t2, /**/  
// IMPORTANT: when defining abbreviations you cannot use the same key in array twice! E.g: if you define token m and then try to define abbreviation "mit" => m it wont work! Workaround: define "mit" => "m/"
$steno_tokens_master = array(  

                        // rescription rules
                        // shortings - IMPORTANT: shortings must be returned with a "/" at the end, otherwise there is a key conflict in the array (2 x same key) !!! 
                        // shortings that can be represented as a single steno-token (=> now integrated above as normal rules with multiple search_strings)
                        "IST" => array( /*header*/5,   0.5,0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0.5, 9.5, 0.5, 1, 1.0, 0, 0, 0.5, /**/  1, 10, 0.5, 0, 1.0, 0, 0, 0.5, /**/  0.5, 10.5, 0.5, 0, 1.5, 0, 0, 0.5, /**/  0, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0.5, 9.5, 0.5, 0, 3.0, 0, 1, 0.5 ),
                        "WAS"  => array( /*header*/ 5, 0.5, 0, 0.5, 2, 1, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/1.5, 8.5, 0.5, 1, 1.0, 0, 0, 0.5, /**/  2.25,9.25, 0.5, 2, 1.5, 0, 0, 0.5, /**/ 5, 10, 0.5, 2, 1.5, 0, 0, 0.5, /**/ 2.25,9.25, 0.5, 2, 1.5, 0, 0, 0.5, /**/1.5,8.5,0.5, 0, 2.5, 0, 0, 0.5,/**/0,6, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0.5,  2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2.5,0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  4.5, 2, 0.5, 0, 1.0, 0, 1, 0.5 /**/ ),
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
                        "ANT"  => array( /*header*/25, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4,     4.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 8,  5, 0.5, 2.5, 2.0, 0, 0, 0.5, /**/  11,  4, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 18, 1, 0.5, 0, 2.5, 0, 0, 0.5, /**/  22, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 25, 0, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "RÜCK" => array( /*header*/ 5, 0.5, 0, 0.5,   0,   1, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  2.5, 0, 0.5, 1, 1.0, 0, 0, 0.7, /**/ 5, 2.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 5, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 0, 0.5, 0, 1.0, 0, 1, 0.0),    
                        "UR" => array( /*header*/ 5, 0.5, 0, 0,   0,   1, 0, ""/**/,"","","","",1,0,0,2.5, /*++*/ 5,0,0,0,0,0,0,0, /*data*/    -2, 10, 0, 1, 3.0, 0, 0, 0.5, /**/  3, 5, 0.5, 0, 1.0, 0, 0, 0.7, /**/5, 2.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 1.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 0, 1.0, 0, 1, 0.0),    
                        "ANTI"  => array( /*header*/25, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4,     4.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 8,  5, 0.5, 2.5, 2.0, 0, 0, 0.5, /**/  11,  4, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 18, 1, 0.5, 0, 2.5, 0, 0, 0.5, /**/   22, 0, 0.5, 0, 1.5, 0, 0, 0.5, /**/ 22.5, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 23.5, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/  24.5, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/  23.5, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/  22.5, 1, 0.5, 0, 1.0, 0, 1, 0.5,      ),
                        "ZURÜCK" => array( /*header*/ 5, 0.5, 0, 0.5,   2,   1, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   -2, -4, 0.5, 1, 1.0, 0, 0, 0.5, /**/  1.5, -3, 0.5, 0, 1.0, 0, 0, 0.5, /**/  4, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/  5, 2.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 5, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 0, 0.5, 0, 1.0, 0, 0, 0.0, /**/ 3.5, 0, 0.5, 1, 1.0, 0, 1, 0.7, /**/),    
                        "INTER" => array( /*header*/ 5, 0.5, 0, 0.5,   2,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   -2, -4, 0.5, 1, 1.0, 0, 0, 0.5, /**/  1.5, -3, 0.5, 0, 1.0, 0, 0, 0.5, /**/  4, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/  5, 2.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 5, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 0, 0.5, 0, 1.0, 0, 0, 0.0, /**/ 3.5, 0, 0.5, 1, 1.0, 0, 1, 0.7, /**/),    
                        "ION"  => array( /*header*/25, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0.5,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4,     4.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 8,  5, 0.5, 2.5, 2.0, 0, 0, 0.5, /**/  11,  4, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 18, 1, 0.5, 0, 2.5, 0, 0, 0.5, /**/  22, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 25, 0, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "HAFT"  => array( /*header*/ 4, 0.5, 0,   0,   4,   1, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10, 0.5, 1, 1.5, 0, 0, 0.5, /**/  3,  17, 0.5, 2, 2.0, 0, 0, 0.5, /**/  1.8, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 18, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 2, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  4, 2, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "SCHAFT" => array( /*hear*/20, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 1.5, 0, 0, 0.5, /**/  0.75, 3, 0.5, 0, 3.0, 0, 0, 0.5, /**/   19.25, 2.5, 0.5, 0, 1.5, 0, 0, 0.5, /**/   20, 0, 0.5, 0, 1.0, 0, 1, 0 /**/ ),
                        "TUM"  => array( /*headr*/10.5, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",1,1,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5,  8.75, 0.5, 1, 1.5, 0, 0,   0, /**/  6, 10, 0.5, 2, 2.5, 0, 0, 0.5, /**/  7.5,  8, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 7.5,   2,0.5, 0, 2.5, 0, 0,   0, /**/ 9.0,  0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 9.0,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 9.5,  2,0.5, 0, 1.0, 0, 1,   0.5),                                                
                        "TALL"  => array( /*header*/ 3, 0.5, 0, 0.5,0.75,0.75, 0, ""/**/,"","","","",1,0,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5,  15, 0.5, 1, 1.5, 0, 0, 0.5, /**/7,  18.5, 0.5, 2, 2, 0, 0, 0.5, /**/   6.1, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 5, 17, 0.5, 0, 3.0, 0, 0, 0.5, /**/   5, 12, 0.5, 0, 2.5, 0, 0,   0, /**/ 7, 10, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 8, 12, 0.5, 0, 1.0, 0, 1, 0.5),                       
                        "TLICH"  => array( /*headr*/4.5, 0.5, 0, 0.5,   1,   1, 0, "","","","","",0,0,1,0, /*++*/ 1,0,0,0,0,0,0,0, /*data*/  5,  18.75, 0.5, 1, 1.5, 0, 0.5, 0, /**/6, 20, 0.5, 2, 2.5, 0, 0, 0.5, /**/   7.5,  18, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 7.5, 12, 0.5, 0, 3.0, 0, 0, 0.5, /**/  6.0, 10, 0.5, 0, 2.5, 0, 99, 0.5, /**/  5, 11.5, 0.5, 0, 1.5, 0, 2, 0.5, /**/  7.5, 15, 0.5, 0, 1.0, 0, 1, 0.5 /**/     ),
                        "TIG"  => array( /*heade*/7.5, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5,  18.75, 0.5, 1, 1.5, 0, 0,   0, /**/6, 20, 0.5, 2, 2.5, 0, 0, 0.5, /**/  7.5,  18, 0.5, 0, 3.0, 0, 0, 0, /**/ 7.5, 10,   0, 0, 1.0, 0, 1,   0, /**/ 7.5, 12.5, 0, 4, 1.0, 0, 0, 0,),                        
                        "TUNG" => array( /*headr*/8, 0.5, 0, 0.5,   2,   1, 0, ""/**/,"","","","",1,0,1,0, /*++*/ 1,0,0,0,0,0,0,0, /*data*/   7, 18, 0.5, 1, 1.3, 0, 0, 0.5, /**/   8, 19.5, 0.5, 0, 1.3, 0, 0, 0.5, /**/   9, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  8, 19.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/  7, 18, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 7, 11.5, 0.5, 0, 2.5, 0, 0, 0.5, /**/  6, 10, 0.5, 0, 1.5, 0, 99, 0.5, /**/  5, 12, 0.5, 0, 1.5, 0, 2, 0.5, /**/  7, 14, 0.5, 0, 1.0, 0, 1, 0.5 /**/     ),                        
                        "AUF" => array( /*header*/ 8, 0.5, 0, 0,   2,   2, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 3.0, 0, 0, 0.5, /**/   1.5, 2, 0.5, 0, 2.0, 0, 0, 0.5, /**/   4, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/   6.5, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/   8, 5, 0.5, 0, 1.0, 0, 1, 0.5,   ),    
                        "AUS" => array( /*hear*/20, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 0, 0, 1, 1.0, 0, 0, 0.5, /**/   3, 3, 0.5, 0, 1.0, 0, 0, 0.5, /**/  10, 5, 0.5, 0, 1.5, 0, 0, 0.5, /**/  17, 3, 0.5, 0, 2.5, 0, 0, 0.5, /**/  19, 1, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 20, 0, 0.5, 0, 1.0, 0, 1, 0 ),
                        "-AUS" => array( /*hear*/20, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/    10, 5, 0.5, 1, 1.5, 0, 0, 0.5, /**/  17, 3, 0.5, 0, 2.5, 0, 0, 0.5, /**/  19, 1, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 20, 0, 0.5, 0, 1.0, 0, 1, 0 ),                        
                        "BEI" => array( /*header*/18, 0.5, 0, 0,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 3.0, 0, 0, 0.5, /**/   3, 2, 0.5, 0, 2.0, 0, 0, 0.5, /**/   9, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/   15, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/   18, 5, 0.5, 0, 1.0, 0, 1, 0.5,   ),    
                        "DURCH" => array( /*header*/10, 0.5, 0, 0,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  7, 10, 0, 1, 1.0, 0, 0, 0.2, /**/   0, 5, 0.2, 0, 1.0, 0, 0, 0, /**/   8, 0, 0, 0, 1.0, 0, 1, 0, /**/   ),    
                        "SICH"  => array( /*hear*/11.75, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5, 0, 1, 1.0, 0, 0, 0.5, /**/      2,    9.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 3,  10, 0.5, 2.5, 1.0, 0, 0, 0.5, /**/  5,  9.5, 0.5, 0, 1.0, 0, 0, 0, /**/ 8, 5, 0, 0, 1.0, 0, 1, 0, /**/   ),
                        "SOLCH" => array( /*header*/ 15,   1,-1, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/    0, 0, 0, 1, 1.0, 0, 0, 0.5, /**/  2.5, 4, 0.5, 0, 1.0, 0, 0, 0.5, /**/  4, 5, 0.5, 0, 1.0, 0, 0, 0.5, /**/  5.5, 4, 0, 0, 1.0, 0, 0, 0.5, /**/  9, 0, 0.5, 0, 1.0, 0, 0, 0, /*ch=>*/   /**/ 10, 3.5, 0, 1, 1.3, 0, 0, 0.5, /**/   12.5, 5, 0.7, 2, 2.5, 0, 0, 0.8, /**/  15, 2, 0.8, 0, 3.0, 0, 0, 0.5, /**/   15, -13, 0.5, 0, 2.5, 0, 0, 0.5, /**/   13, -15, 0.5, 0, 2, 0, 99, 0.5, /**/   11.5, -14, 0.5, 0, 1.5, 0, 0, 0.5, /**/   10, -12, 0.5, 0, 1.0, 0, 2, 0.5, /**/   13, -7, 0.5, 0, 1.0, 0, 1, 0.5, /**/ 15, -2.5, 0, 4, 1.0, 0, 0, 0.5,/**/  ),
                        "WIRD" => array( /*header*/ 5, 0.5, 0, 0.5,   2,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 0, 0.5, 1, 1.0, 0, 0, 0.5, /**/  3.5, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/  5, 5, 0.5, 0, 1.0, 0, 0, 0.5, /**/  5, 7.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 10, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 7.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 0, 1.0, 0, 0, 0.0, /**/ 5, 5, 0.5, 1, 1.0, 0, 1, 0.7, /**/),    
                        "SELB"  => array( /*hear*/ 8, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",1,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0.5, 0, 0.5, 1, 1.5, 0, 0, 0.5, /**/   1, 0.5, 0.5, 0, 1.5, 0, 0, 0.5, /**/   0.5, 1, 0.5, 0, 1.5, 0, 0, 0.5, /**/   0, 0.5, 0.5, 0, 1.5, 0, 0, 0.5, /**/   0.5, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/  4, 0, 0.5, 0, 1.0, 0, 2, 0.5, /**/   7, 2, 0.5, 0, 1.0, 0, 1, 0.5, /**/   ),
                        "WO"  => array( /*header*/ 7, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/1.5, 8.5, 0.5, 1, 1.0, 0, 0, 0.5, /**/  2.25,9.25, 0.5, 2, 1.5, 0, 0, 0.5, /**/ 5, 10, 0.5, 2, 1.5, 0, 0, 0.5, /**/ 2.25,9.25, 0.5, 2, 1.5, 0, 0, 0.5, /**/1.5,8.5,0.5, 0, 2.5, 0, 0, 0.5,/**/0,6, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1,  3.25, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2.5,1.5, 0.5, 4, 1.5, 0, 0, 0.5, /**/  6,1.5, 0.5, 0, 1.5, 0, 0, 0.5, /**/  6.5, 0, 0.5, 0, 1.0, 0, 1, 0 /**/ ),
                        "VILLEICHT"  => array( /*header*/ 5, 0.5, 0, 0.5,0.75,0.75, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  5, 0.5, 1, 1.5, 0, 0, 0.5, /**/2,  8.5, 0.5, 2, 2, 0, 0, 0.5, /**/   1.1, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 0, 7, 0.5, 0, 3.0, 0, 0, 0.5, /**/   0, 2, 0.5, 0, 2.5, 0, 0,   0,  /**/ 2, 0, 0.5, 0, 1.5, 0, 0, 0.5, /**/ 5, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 7, 12, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 5, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 2, 20, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 0, 17, 0.5, 0, 1.0, 0, 1, 0.5, ),                       
                        "HEIT" => array(/*heade*/18, 0, 1.5, 0.5,   0,   0, 0, ""/*,keit,ität,tät*//**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  18, 20, 0, 1, 1.0, 0, 0, 0, /**/ 18, 20, 0, 0, 1.0, 0, 1, 0 ),
                        "FORT"  => array( /*header*/ 8, 0.5, 0,   0, 1, 1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5, 10, 0.5, 1, 1.5, 0, 0, 0.5, /**/  8,  17, 0.5, 2, 2.0, 0, 0, 0.5, /**/  6.8, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  5, 18, 0.5, 0, 3.0, 0, 0, 0.5, /**/  5, 0, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 5, -5, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 4.25, -9, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 5, 2.5, 0.5, 4, 1.0, 0, 0, 0.5, /**/ 5, 0, 0.5, 0, 1.0, 0, 1, 0.5,/**/ ),

                        "|"  => array( /*header*/ 2, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,1,0, /*data*/ 0, 0, 0.5, 5, 1.0, 5, 0, 0, /**/  0, 0, 0, 5, 1.0, 5, 1, 0, /**/ ),
                            
                        // token rules
                        // rules with 3 characters
                        "SCHM" => array(/*header*/6,   1,-1, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  2, 9, 0.5, 1, 1.0, 0, 0, 0.5, /**/  4,10, 0.5, 0, 1.5, 0, 0, 0.5, /**/  6, 9, 0.5, 0, 2.5, 0, 0, 0.5, /**/   5, 5, 0.5, 0, 3.0, 0, 0, 0.5, /**/  3, 2.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 3, 1.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/   /**/  5, 0.5, 0.5, 0, 3.0, 0, 0, 0, /**/   5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "SCHW" => array(/*header*/7,   1,-1, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/ 5, 17, 0.5, 1, 1.0, 0, 0, 0.5, /**/  7, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/  9, 20, 0.5, 0, 1.5, 0, 0, 0.5, /**/  7, 19, 0.5, 0, 2.5, 0, 0, 0.5, /**/  5, 17, 0.5, 0, 3.0, 0, 0, 0.5, /**/  5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "MPF" => array( /*header*/6, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  9, 0.5, 1, 1.5, 0, 0,   0, /**/  1.5, 10, 0.5, 2, 2.5, 0, 0, 0.5, /**/  3,  8, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 3,   2,0.5, 0, 2.5, 0, 0,   0, /**/ 4.5,  0, 0.5, 0, 1.5, 0, 0, 0.5, /**/ 5.75,  2,0.5, 0, 1.0, 0, 0,   0.5, /**/  4.5, 3.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 1.5, 3.1, 0.5, 0, 1.0, 0, 0, 0.8, /**/ 0.75, 2.6, 0.8, 0, 1.0, 0, 0, 0.5, /*+++*/ 1.5, 2.0, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 4.5, 2, 0.5, 0, 1.0, 0, 2, 0.5, /**/ 5.75, 2, 0.5, 0, 1.0, 0, 1, 0.5 ),                                                
                        "SCH" => array( /*header*/9,   1,-1, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5, 7, 0.5, 1, 1.0, 0, 0, 0.5, /**/  7, 9, 0.5, 0, 1.0, 0, 0, 0.5, /**/  9, 10, 0.5, 0, 1.5, 0, 0, 0.5, /**/  7, 9, 0.5, 0, 2.5, 0, 0, 0.5, /**/  5, 7, 0.5, 0, 3.0, 0, 0, 0.5, /**/  5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0 /*3*/, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/5, 2.5, 0, 4, 1.0, 0, 0, 0.5,/**/  ),
                        
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
                        "MM" => array( /*header*/11, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/ 0,  8.75, 0.5, 1, 1.0, 0, 0,   0,  /**/ 1.75, 10, 0.5, 0, 1.2, 0, 0, 0, /**/  6, 10, 0.5, 2, 2.0, 0, 0, 0.5, /**/  8,  8.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 8,   2,0.5, 0, 2.5, 0, 0,   0, /**/ 9.5,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 11,  2,0.5, 0, 1.0, 0, 1,   0.5),                                                
                        "NN" => array( /*header*/17, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/ 0,     9, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 2,  10, 0.5, 2.5, 2.0, 0, 0, 0.5, /**/  5,  8, 0.5, 0, 3.0, 0, 0, 0.5, /**//* 7,  6.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 11, 2, 0.5, 0, 2.5, 0, 0, 0.5, /**/  14, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 17, 2, 1, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "PP" => array( /*headr*/2.5, 0.5, 0, 0.5,   2, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,    30,   0, 1, 3.0, 0, 0, 0.0, /**/    0,  3, 0.5, 0, 2.5, 0, 0, 0.5, /**/      1.25,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 2.5, 2, 0, 0, 1.0, 0, 1, 0),
                        "RR" => array( /*header*/5, 0.5, 1, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/ 1, 7.75, 0.5, 1, 1.0, 0, 0, 0.5, /**/  5, 10, 0.7, 0, 3.0, 0, 0, 0.8, /**/  10, 5, 0.8, 0, 3.0, 0, 0, 0.7, /**/  5, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/  0, 5, 0.5, 0, 1.0, 0, 0, 0.5, /**/  1, 7.75, 0.5, 0, 1.0, 0, 0, 0.5, /**/  5, 10, 0.5, 0, 1.0, 0, 1, 0.5, /**/  ),    
                        "SS" => array( /*header*/14, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,   9, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 3,  10, 0.5, 2.5, 2.0, 0, 0, 0.5, /**/  7.5,  8, 0, 0, 3.0, 0, 0, 0, /**/ 9.5,  6, 0, 0, 3.0, 0, 0, 0, /**/ 14, 0, 0, 0, 1.0, 0, 1, 0 /**/  ),
                        "TT" => array( /*header*/ 0, 0.5, 0, 0,   5,   3, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 30,   0, 1, 3.0, 0, 0, 0, /**/  0,  0,   0, 0, 1.0, 0, 1, 0),
                        "CH" => array( /*header*/ 5,   1,-1, 0.5, 2, 2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 8.5, 0.5, 1, 1.3, 0, 0, 0.5, /**/   2.5, 10, 0.7, 2, 2.5, 0, 0, 0.8, /**/  5, 7, 0.8, 0, 3.0, 0, 0, 0.5, /**/   5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 99, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 0, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/ 5, 2.5, 0, 4, 1.0, 0, 0, 0.5,/**/  ),
                        "ND" => array( /*headr*/2.5, 0.5, 0, 0.5,   1, 3, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  5, 0.5, 1, 1.5, 0, 0, 0.5, /**/2.5,  8.25, 0.5, 2, 2, 0, 0, 0.5, /**/   1.1, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 0, 7, 0.5, 0, 3.0, 0, 0, 0, /**/   0, 0, 0.0, 0, 1.0, 0, 0,   0 ),                       
                        "NG" => array( /*headr*/3, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 1,0,0,0,0,0,0,0, /*data*/   2, 8, 0.5, 1, 1.3, 0, 0, 0.5, /**/   3, 9.5, 0.5, 0, 1.3, 0, 0, 0.5, /**/   4, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/  3, 9.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2, 8, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 2, 1.5, 0.5, 0, 2.5, 0, 0, 0.5, /**/  1, 0, 0.5, 0, 1.5, 0, 99, 0.5, /**/  0, 2, 0.5, 0, 1.5, 0, 2, 0.5, /**/  2, 4, 0.5, 0, 1.0, 0, 1, 0.5, /**/ 2, 4, 0, 4, 1.0, 0, 0, 0.5,    ),                        
                        "NK" => array( /*header*/ 6, 0.5, 0,   0,   1,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/1,0,0,0,0,0,0,0, /*data*/  3, 10, 0.5, 1, 1.5, 0, 0, 0.5, /**/  6,  17, 0.5, 2, 2.0, 0, 0, 0.5, /**/  4.8, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  3, 18, 0.5, 0, 3.0, 0, 0, 0.5, /**/  3, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1.5, 0, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 2, 0.5, 0, 1.5, 0, 99, 0.5, /**/ 0, 2, 0.5, 0, 1.5, 0, 2, 0.5, /**/  4, 5, 0.5, 0, 1.0, 0, 1, 0.5, /**/  4, 5.5, 0, 4, 1.0, 0, 0, 0.5,/**/),
                        "NS" => array( /*head*/3.75, 0.5, 0,   0,   1,   1, 0, ""/**/ ,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0.75, 5, 0.5, 1, 1.5, 0, 0, 0.5, /**/  3.75,  8.5, 0.5, 2, 2.0, 0, 0, 0.5, /**/  2.65, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/  1.75, 9, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1.75, 1, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0.75, 0, 0.5, 0, 2.5, 0, 99, 0.5, /**/  0, 2.25, 0.5, 0, 1.5, 0, 2, 0.5, /**/  1.75, 3, 0.5, 0, 1.0, 0, 1, 0.5 /**/ ),
                        "PF" => array( /*header*/ 8, 0.5, 0, 0.5,   1,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 5.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/  4,  9.5, 0.5, 2, 2.0, 0, 0, 0.5, /**/ 7,  13, 0.8, 2, 2.0, 0, 0, 0.9, /**/ 8,  16, 0.9, 2, 2.0, 0, 0, 0.7, /**/  1.8, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 18, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/  2, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  4, 2, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "SP" => array( /*header*/ 8, 0.5, 0, 0.5,0.75,0.75, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 18, 0.5, 1, 1.5, 0, 0, 0.5, /**/2, 20, 0.5, 2, 2, 0, 0, 0.5, /**/   4, 18, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 4, 15, 0.5, 0, 3, 0, 0, 0.5, /**/  4, 5, 0.5, 0, 2.5, 0, 0, 0.5, /**/  4, 3, 0.5, 0, 2, 0, 0, 0.5, /**/ 6, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 6, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 8, 2, 0.5, 0, 1.0, 0, 1, 0),
                        "ST" => array( /*header*/ 3, 0.5, 0,   0,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10, 0.5, 1, 1.5, 0, 0, 0.5, /**/  3,  17, 0.5, 2, 2.0, 0, 0, 0.5, /**/  1.8, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 18, 0.5, 0, 3.0, 0, 0, 0, /**/  0, 0, 0, 0, 1.0, 0, 1, 0, /**/ 0, 2.5, 0, 4, 1.0, 0, 1, 0 /**/ ),
                        "ZW" => array( /*header*/ 5,   1,-1, 0,   1,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5, 20, 0, 1, 3.0, 0, 0, 0, /**/   5, -8, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 0, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5, 0, 0.5, 0, 1.0, 0, 1, 0.5, /**/  ),
                        "EN" => array( /*header*/ 5, 0,  0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,1,0,0,0,0,0,0, /*data*/ /* 1, 0, 0, 1, 1.0, 0, 0, 0, /**/ 5, 0, 0, 1, 1.0, 0, 0, 0, /**/ 5, 0, 0, 0, 1.0, 0, 1, 0 ),
                        "EM" => array( /*header*/18,   5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,1,0,0,0,0,0,0, /*data*/  /*1, 0,  0, 1, 1.0, 0, 0,  0,*/ /**/ /*13, 0,  0, 2, 1.0, 0, 0,  0,*/ /**/ 13, 0,  0.5, 1, 1.0, 0, 1,  0 /**/),
                        "VR+AR" => array( /*header*/ 5, 0.5, 0, 0.5,   0,   1, 0, ""/**/,"","","","",0,0,0.5,2.5, /*++*/ 5,0,0,0,0,0,0,0, /*data*/  2.5, 5, 0.5, 1, 3.0, 0, 0, 0.5, /**/ 5, 2.5, 0.7, 0, 3.0, 0, 0, 0.7, /**/  2.5, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 1.0, 0, 0, 0.5,  /**/ 2.5, 5, 0.5, 0, 1.0, 0, 0, 0.0, /*AR*/ 2.5, 5, 0.5, 0, 1.0, 0, 0, 0.7, /**/ 5, 7.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 10, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 7.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 0, 1.0, 0, 1, 0.0, ),    
                        // special en and em endings with to points (necessary for correct calculation after tokens with pivot points, e.g. "chen"
                   //     "EN2" => array( /*header*/ 5, 0,  0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,1,0,0,0,0,0,0, /*data*/ -4, 0, 0.5, 1, 1.0, 0, 0, 0, /**/ 5, 0, 0, 1, 1.0, 0, 0, 0, /**/ 5, 0, 0, 0, 1.0, 0, 1, 0 ),
                       
                        // rules with 1 character
                        "B"  => array( /*header*/ 5, 0.5, 0,   0, 1, 1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10,   0, 1, 3.0, 0, 0, 0.5, /**/  0,  2, 0.5, 0, 2.5, 0, 0, 0.5, /**/   2.5,  0, 0.5, 4, 1.5, 0, 0, 0.5, /**/  2.5,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 5,  2,0.5, 0, 1.0, 0, 1,   0),
                        "C"  => array( /*header*/ 3, 0.5, 0, 0.5, 1, 1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  1,9.5, 0.5, 0, 1.0, 0, 0, 0.5, /**/  3, 10, 0.5, 0, 3.0, 0, 0, 0.5, /**/     1,9.5, 0.5, 0, 3.0, 0, 0, 0.5, /**/    0,7.5, 0.5, 0, 3.0, 0, 0,   0, /**/ 0,  0,  0, 0, 1.0, 0, 1,   0),
                        "D"  => array( /*header*/ 0, 0.5, 0,   0, 2 ,3, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10,   0, 1, 3.0, 0, 0,   0, /**/  0,  0,   0, 0, 1.0, 0, 1,  0, /**/ 0, 2.5, 0, 4, 1.0, 0, 0, 0.5, ), 
                        "F"  => array( /*header*/ 4, 0.5, 0,   0, 1, 1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10, 0.5, 1, 1.5, 0, 0, 0.5, /**/  3,  17, 0.5, 2, 2.0, 0, 0, 0.5, /**/  1.8, 20, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 18, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  2, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 2, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  4, 2, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "G"  => array( /*heade*/2.5, 0.5, 0, 0.5,   2,   3, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  8.75, 0.5, 1, 1.5, 0, 0,   0, /**/1, 10, 0.5, 2, 2.5, 0, 0, 0.5, /**/  2.5,  8, 0.5, 0, 3.0, 0, 0, 0, /**/ 2.5, 0,   0, 0, 1.0, 0, 1,   0, /**/ 2.5, 2.5, 0, 4, 1.0, 0, 0, 0,),                        
                        "H"  => array( /*headr*/4.5, 0.5, 0, 0.5,   2,   2, 0, "","","","","",0,0,0,0, /*++*/ 1,0,0,0,0,0,0,0, /*data*/  0,  8.75, 0.5, 1, 1.5, 0, 0.5, 0, /**/1, 10, 0.5, 2, 2.5, 0, 0, 0.5, /**/   2.5,  8, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1.0, 0, 0.5, 0, 2.5, 0, 99, 0.5, /**/  0, 1.5, 0.5, 0, 1.5, 0, 2, 0.5, /**/  2.5, 5, 0.5, 0, 1.0, 0, 1, 0.5 /**/     ),
                        "J"  => array( /*header*/ 3, 0.5, 0,   0,2,2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  3,  10, 0, 1, 3.0, 0, 0, 0.5, /**/ 3, 2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1.5, 0, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 1.5, 0.5, 0, 1.5, 0, 2, 0.5, /**/  3, 5, 0.5, 0, 1.0, 0, 1, 0.5 /**/     ),
                        "K"  => array( /*headr*/2.5, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 18.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/1.25, 20, 0.5, 2, 2, 0, 0, 0.5, /**/   2.5, 17, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 2.5, 15, 0.5, 0, 3, 0, 0, 0, /**/ 2.5, 0, 0, 0, 1.0, 0, 1, 0.0, /**/ 2.5, 2.5, 0, 4, 1.0, 0, 0, 0.5,),
                        "L"  => array( /*header*/ 3, 0.5, 0, 0.5,0.75,0.75, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  5, 0.5, 1, 1.5, 0, 0, 0.5, /**/2,  8.5, 0.5, 2, 2, 0, 0, 0.5, /**/   1.1, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 0, 7, 0.5, 0, 3.0, 0, 0, 0.5, /**/   0, 2, 0.5, 0, 2.5, 0, 0,   0, /**/ 2, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 2, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 3, 0, 0.5, 0, 1.0, 0, 1, 0.5),                       
                        "@L" => array( /*header*/ 0, 0, 0, 0.5, 0, 0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/    0, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/  1, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/  0, 2, 0.5, 0, 1.0, 0, 0, 0.5, /**/  -1, 1, 0.5, 0, 1.0, 0, 2, 0.5, /**/  0, 0, 0.5, 0, 1.0, 0, 1, 0.5 /**/ ),                       
                        "@L3" => array( /*header*/ 0, 0, 0, 0.5, 0, 0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/    0, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/  -1, 1, 0.5, 0, 1.0, 0, 0, 0.5, /**/  -2, 0, 0.5, 0, 1.0, 0, 0, 0.5, /**/  -1, -1, 0.5, 0, 1.0, 0, 2, 0.5, /**/  0, 0, 0.5, 0, 1.0, 0, 1, 0.5 /**/ ),                       
                        "M"  => array( /*headr*/5.5, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  8.75, 0.5, 1, 1.5, 0, 0,   0, /**/  1.0, 10, 0.5, 2, 2.5, 0, 0, 0.5, /**/  2.5,  8, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 2.5,   2,0.5, 0, 2.5, 0, 0,   0, /**/ 4.0,  0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 4.0,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 5.5,  2,0.5, 0, 1.0, 0, 1,   0.5),                                                
                        "N"  => array( /*header*/11, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,     4.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 1,  5, 0.5, 2, 2.0, 0, 0, 0.5, /**/   2,  5, 0.5, 0, 3.0, 0, 0, 0.5, /**/ /*3,  4, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 5.5, 2, 0.5, 0, 2.5, 0, 0, 0.5, /**/  7.5, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 8.5, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 11, 1, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        "P"  => array( /*headr*/4, 0.5, 0,   0,   2,   2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,    20,   0, 1, 3.0, 0, 0, 0.0, /**/    0,  2.5, 0.5, 0, 2.5, 0, 0, 0.5, /**/      2,  0, 0.5, 4, 1.5, 0, 0, 0.5, /**/   2,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 4, 2, 0, 0, 1.0, 0, 1, 0),
                        "Q"  => array( /*header*/ 3, 0.5, 0, 0.5,0.75,0.75, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  1,19, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3, 20, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1,19,0.5, 0, 3.0, 0, 0, 0.5, /**/ 0,15, 0.5, 0, 3.0, 0, 0, 0, /**/ 0, 0, 0, 0, 1.0, 0, 1,   0),
                        "VR" => array( /*header*/ 5, 0.5, 0, 0.5,   2,   2, 0, ""/**/,"","","","",0,0,0,2.5, /*++*/ 5,0,0,0,0,0,0,0, /*data*/  2.5, 5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 3.75, 4, 0.7, 5, 2.5, 0, 0, 0.7, /**/ 5, 2.5, 0.7, 0, 3.0, 0, 0, 0.7, /**/ 4.5, 0.5, 0.7, 5, 2, 0, 0, 0.7, /**/ 3.25, 0.15, 0.7, 5, 1.5, 0, 0, 0.7, /**/ 2.5, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 1.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 4, 1.0, 0, 0, 0.5, /**/ 2.5, 5, 0.5, 0, 1.0, 0, 1, 0.0),    
                        "AR" => array( /*header*/ 5, 0.5, 0, 0.5,   0,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  2.5, 0, 0.5, 1, 1.0, 0, 0, 0.7, /**/ 5, 2.5, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2.5, 5, 0.7, 0, 3.0, 0, 0, 0.5, /**/  0, 2.5, 0.7, 0, 3.0, 0, 0, 0.5, /**/ 2.5, 0, 0.5, 0, 1.0, 0, 1, 0.0),    
                        "@R" => array( /*header*/ 5, 0.5, 0, 0.5,   0,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 0, 0.7, 0, 1.0, 0, 0, 0.7, /**/  -2, 2, 0.7, 0, 1.0, 0, 0, 0.5, /**/  -4, 0, 0.7, 0, 1.0, 0, 0, 0.5, /**/ -2, -2, 0.5, 0, 1.0, 0, 2, 0.5, /**/ 0, 0, 0.5, 0, 1.0, 0, 1, 0.7, /**/ ),    
                        "@R6" => array( /*header*/0, 0.5, 0, 0.5,   0,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/   0, 0, 0.7, 0, 1.0, 0, 0, 0.7, /**/  2, 2, 0.7, 0, 1.0, 0, 0, 0.5, /**/  0, 4, 0.7, 0, 1.0, 0, 0, 0.5, /**/ -2, 2, 0.5, 0, 1.0, 0, 2, 0.5, /**/ 0, 0, 0.5, 0, 1.0, 0, 1, 0.7, /**/ ),    
                        "S"  => array( /*hear*/6.75, 0.5, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,     4, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 1,  5, 0.5, 2, 2.0, 0, 0, 0.5, /**/  2,  5, 0.5, 0, 3.0, 0, 0, 0, /**/ 4,  4, 0.5, 0, 3.0, 0, 0, 0, /**/  6.75, 0, 0, 0, 1.0, 0, 1, 0 /**/  ),
                        "T"  => array( /*header*/ 0, 0.5, 0, 0,4,2.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 20,   0, 1, 3.0, 0, 0, 0, /**/ /* 0,  0.1,   0, 0, 1.0, 0, 2, 0, /*preceeding point = test*/0,  0,   0, 0, 1.0, 0, 1, 0, /**/ 0, 2.5, 0, 4, 1.0, 0, 0, 0.5, ), // define connection point
                        "&T" => array( /*header*/ 4, 0, 1.5, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4, 20, 0, 1, 1.0, 0, 1, 0, /**/ 4, 20, 0, 0, 1.0, 0, 1, 0 ),
                        "&T3/4" => array( /*header*/ 4, 0, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4, 15, 0, 1, 1.0, 0, 1, 0, /**/ 4, 15, 0, 0, 1.0, 0, 1, 0, /**/ 4, 15, 0, 4, 1.0, 0, 1, 0, ),
                        "ET" => array( /*header*/ 4, 0, 0, 0,   0,   0, 0, ""/**/,"","","","",0,0,2,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4, 25, 0, 1, 1.0, 0, 1, 0, /**/ 4, 25, 0, 0, 1.0, 0, 1, 0 ),
                        "ET3/4" => array( /*header*/ 4, 0, 0, 0,   0,   0, 0, ""/**/,"","","","",0,0,1.5,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4, 20, 0, 1, 1.0, 0, 1, 0, /**/ 4, 20, 0, 0, 1.0, 0, 1, 0 ),
                        "V"  => array( /*header*/ 6, 0.5, 0, 0.5,   1,   1, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  1, 16, 0.5, 1, 1.0, 0, 0, 0.5, /**/  2, 18, 0.6, 2, 1.0, 0, 0, 0, /**/ 6,   20,  0, 0, 0, 0, 0, 0.5, /**/ 2, 18, 0.6, 0, 1.5, 0, 0, 0.5, /**/  1, 16, 0.5, 0, 2.5, 0, 0, 0.5, /**/  0, 14, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0, 5, 0.5, 0, 3.0, 0, 0, 0.5, /**/  1, 2, 0.5, 0, 2.5, 0, 0, 0.5, /**/  3, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/  3, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  5, 2, 0.5, 0, 1.0, 0, 1, 0.5 ), 
                        "W"  => array( /*header*/ 6, 0.5, 0, 0.5, 2, 2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/1.5, 8.5, 0.5, 1, 1.0, 0, 0, 0.5, /**/  2.25,9.25, 0.5, 2, 1.5, 0, 0, 0.5, /**/ 5, 10, 0.5, 0, 1.5, 0, 0, 0.5, /**/ 2.25,9.25, 0.5, 0, 1.5, 0, 0, 0.5, /**/1.5,8.5,0.5, 0, 2.5, 0, 0, 0.5,/**/0,6, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0.5,  2, 0.5, 0, 3.0, 0, 0, 0.5, /**/  3,0, 0.5, 4, 1.5, 0, 0, 0.5, /**/  3,0, 0.5, 0, 1.5, 0, 2, 0.5, /**/  6, 2, 0.5, 0, 1.0, 0, 1, 0.5 /**/ ),
                        "X"  => array( /*header*/ 5, 0.5, 0, 0.5, 0.5, 0.5, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,   5, 0, 1, 1.5, 0, 0, 0.5, /**/4.25,7.5, 0.5, 2, 2.0, 0, 0, 0.5, /**/  5,  9,0.5, 0, 2.5, 0, 0, 0.5,/**/3.75,10,0.5,0,3.0, 0, 0, 0.5, /**/   1.75,   7.75, 0.5, 0, 3.0, 0, 0, 0.5, /**/  0, 5, 0.5, 0, 3.0, 0, 0, 0, /**/   5, 0, 0, 0, 1.0, 0, 1, 0  ),
                        "Y"  => array( /*header*/ 10, 0.5, 0, 0, 2,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10, 0, 1, 3.0, 0, 0, 0, /**/   10, 0, 0, 0, 1.0, 0, 1, 0 /**/  ),
                        "Z"  => array( /*header*/ 5,   1,-1, 0, 2, 2, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  5, 10, 0, 1, 3.0, 0, 0, 0, /**/   5, -5, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 4.25, -9, 0.5, 0, 2.5, 0, 0, 0.5, /**/   3, -10, 0.5, 0, 2, 0, 0, 0.5, /**/   1.5, -9, 0.5, 0, 1.5, 0, 99, 0.5, /**/   0, -7, 0.5, 0, 1.0, 0, 0, 0.5, /**/ 5, 2.5, 0.5, 4, 1.0, 0, 0, 0.5, /**/ 5, 0, 0.5, 0, 1.0, 0, 1, 0.5,/**/ ),
                        // these (&&..) are dummy tokens which are only used to combine with other consonants
                        //"&&T" => array( /*header*/ 4, 0, 1.5, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  4, 20, 0, 4, 1.0, 0, 1, 0,  ),
                        //"T15" => array( /*header*/ 4, 0, 0, 0.5,   0,   0, 0, ""/**/,"","","","",0,0,1,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  /*4, 15, 0, 1, 1.0, 0, 0, 0, /**/ 4, 15, 0, 4, 1.0, 0, 1, 0, /**/ /*4, 15, 0, 0, 1.0, 0, 1, 0,  ),
                        //"^1" => array( /*header*/ 5, 0, 0,   0, 1, 1, 1, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /**/ /*4, 15, 0, 1, 1.0, 0, 0, 0, /**/ 4, 15, 0, 4, 1.0, 0, 1, 0, /**/ /*4, 15, 0, 0, 1.0, 0, 1, 0, ),
                        // above tests with token combiner were'nt successfull: define [&T] + consonant manually (with baseline shift at offset 6)
                        //"&TB"  => array( /*header*/ 5, 0.5, 0,   0, 4, 1, 1, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 10,   0, 1, 3.0, 0, 0, 0.5, /**/  0,  2, 0.5, 0, 2.5, 0, 0, 0.5, /**/   2.5,  0, 0.5, 4, 1.5, 0, 0, 0.5, /**/  2.5,  0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 5,  2,0.5, 0, 1.0, 0, 1,   0),
                        //"^T+L"  => array( /*header*/ 5, 0.5, 0, 0.5, 4,0.75, 1, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,  5, 0.5, 1, 1.5, 0, 0, 0.5, /**/2,  8.5, 0.5, 2, 2, 0, 0, 0.5, /**/   1.1, 10, 0.5, 0, 2.5, 0, 0, 0.5, /**/ 0, 7, 0.5, 0, 3.0, 0, 0, 0.5, /**/   0, 2, 0.5, 0, 2.5, 0, 0,   0, /**/ 2, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 2, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 3, 0, 0.5, 0, 1.0, 0, 1, 0.5),                       
                        //"^TN"  => array( /*header*/11, 0.5, 0, 0.5,   4,   0, 1.5, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0,     4.5, 0.5, 1, 1.5, 0, 0, 0.5, /**/ 1,  5, 0.5, 2, 2.0, 0, 0, 0.5, /**/   2,  5, 0.5, 0, 3.0, 0, 0, 0.5, /**/ /*3,  4, 0.5, 0, 3.0, 0, 0, 0.5, /**/ 5.5, 2, 0.5, 0, 2.5, 0, 0, 0.5, /**/  7.5, 0, 0.5, 4, 1.5, 0, 0, 0.5, /**/ 8.5, 0, 0.5, 0, 1.5, 0, 2, 0.5, /**/ 11, 1, 0.5, 0, 1.0, 0, 1, 0.5 /**/  ),
                        // SOLUTION: Create new function TokenShifter() similar to TokenCombiner() that just shifts base tokens and adds them to stenotokensmaster
                        
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
                        "0D-" => array( /*header*/ 20,  0, -0.5,   0,   0,   0, 1, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  20, 0, 0, 1, 1.0, 0, 0, 0, /**/ 20, 0, 0, 0, 1.0, 0, 1, 0.5 ),
                        "0U-" => array( /*header*/ 5,  0, /*+0.5*/0, 0,   0,   0, 1, ""/**/,"","","","",0,0.5,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 0, 0, 1, 1.0, 0, 0, 0, /**/ 0, 0, 0, 0, 1.0, 0, 1, 0.5 ),
                       
                        "PSPACE" => array( /*header*/ 2,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,0,0,0,0,0,0, /*data*/  0, 0, 0, 1, 1.0, 5, 0, 0, /**/ 2, 0, 0, 0, 1.0, 5, 2, 0, /**/ ),
                        "." => array( /*header*/ 2,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/  0.5, 0, 0, 1, 1.0, 5, 0, 0, /**/ 1, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 1, 0, 0, 1.0, 0, 1, 0.5, /**/ 0, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 0, 0, 0, 1.0, 0, 1, 0.5, ),
                        "," => array( /*header*/ 3,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/ 2, -2, 0, 1, 1.0, 5, 0, 0, /**/ 3,  1, 0, 0, 1.0, 0, 1, 0.5, /**/  ),
                        ";" => array( /*header*/ 3,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/  2.5, 4.5, 0, 1, 1.0, 5, 0, 0, /**/ 3, 5, 0, 0, 1.0, 0, 1, 0.5, /**/ 2.5, 5.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 2, 5, 0, 0, 1.0, 0, 1, 0.5, /**/ 2.5, 4.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 3, 1, 0, 1, 1.0, 5, 0, 0, /**/ 2, -2, 0, 0, 1.0, 0, 1, 0.5, /**/  ),
                        ":" => array( /*header*/ 3,  0, /*+0.5*/0, 0,   0,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/  0.5, 4.5, 0, 1, 1.0, 5, 0, 0, /**/ 1, 5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 5.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0, 5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 4.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 0, 0, 1, 1.0, 5, 0, 0, /**/ 1, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 1, 0, 0, 1.0, 0, 1, 0.5, /**/ 0, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 0, 0, 0, 1.0, 0, 1, 0.5,  ),
                        "!" => array( /*header*/ 2,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/  0.5, 20, 0, 1, 1.0, 5, 0, 0, /**/ 0.5, 5, 0, 1, 1.0, 0, 0, 0, /**/  0.5, 0, 0, 1, 1.0, 5, 0, 0, /**/ 1, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 1, 0, 0, 1.0, 0, 1, 0.5, /**/ 0, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 0.5, 0, 0, 0, 1.0, 0, 1, 0.5, ),
                        "?" => array( /*header*/ 5,  0, /*+0.5*/0, 0,   4,   0, 0, ""/**/,"","","","",0,0,0,0, /*++*/ 0,0,1,0,0,0,0,0, /*data*/   0, 15, 0, 0, 1.0, 5, 0, 0.5, /**/   1.25, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/   2.5, 20, 0.5, 0, 1.0, 0, 0, 0.5, /**/   3.75, 19, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5, 15, 0.5, 0, 1.0, 0, 0, 0.5, /**/   5, 13, 0.5, 0, 1.0, 0, 0, 0.5, /**/   2.5, 10, 0.5, 0, 1.0, 0, 0, 0.5, /**/   2.5, 5, 0.5, 0, 1.0, 0, 0, 0, /**/    2.5, 0, 0, 1, 1.0, 5, 0, 0, /**/ 3, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 2.5, 1, 0, 0, 1.0, 0, 1, 0.5, /**/ 2, 0.5, 0, 0, 1.0, 0, 1, 0.5, /**/ 2.5, 0, 0, 0, 1.0, 0, 1, 0.5, ),
                        
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

                    );


// combiner table: defines which tokens can be combined with other ones
// is used by CreateCombinedTokens and TokenCombiner to create those tokens and add them to $steno_tokens_master
// the name of the resulting token will allways be: key + value (e.g. "T" + "@R" will be "T@R")
$combiner_table = array(
    
    //array("T15", "B"),  // test
    //array("^1", "B"),  // test
    array("D", "@R"),
    array("T", "@R"),
    array("G", "@R"),
    array("K", "@R"),
    array("CH", "@R"),
    array("NK", "@R"),
    array("SCH", "@R"),
    array("ST", "@R"),
    array("L", "@L"),
    array("B", "@L"),
    array("G", "@L"),
    array("M", "@L"),
    array("F", "@L"),
    array("P", "@L"),
    array("PF", "@L"),
    array("V", "@L"),
    array("SP", "@L"),
    array("W", "@L"),
    array("T", "@L3"),
    array("NG", "@L3"),
    array("D", "@L3"),
    array("ST", "@L3"),
    array("NK", "@L3"),
    array("K", "@L3"),
    array("Z", "@L3"),
    array("SCH", "@L3"),
    array("CH", "@L3"),
    array("B", "@R6"),
    array("SP", "@R6"),
    array("F", "@R6"),
    array("M", "@R6"),
    array("P", "@R6"),
    array("PF", "@R6"),
    array("V", "@R6"),
    array("W", "@R6"),
    array("Z", "@R"),
    array("Z", "@L3"),
    array("DA", "@R"),
    array("N", "@R6"),
    array("N", "@L"),
    array("VR", "@L"),
    array("CK", "@L"),
    array("L", "@R6"),
    
    
    
    
);

//////////////////////////////////////////// end of data definitions //////////////////////////////////////////
?>