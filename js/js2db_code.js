
// JSON / PHP XMLHTTPRequest export / import to / from database
var tempKey;

function writeDataToDB() {
	//var data = JSON.stringify(actualFont);
	//console.log("data: ", data, actualFont);
	//console.log("custom: ", custom);

	if (typeof actualFontSE1 != undefined) {
		// use this for SE1 export for the moment (no JSON) - only if actualFontSE1 exists!
		// prepare textarea data that will be presented in an intermedia step
		// as html form ant then exported via normal php/form/post-call
		// (using the php-code from SE1 without any modification (hopefully;-))
		// "Translation" from new SE2 to old SE1 occurs via actualFont: 
		// all data must be stored in actualFont first and is then converted
		// to SE1 notation (= text) afterwards.
	
		console.log(actualFont);
	
		var textArea = "#BeginSection(font)\n" + getBaseSectionSE1() + getCombinerSectionSE1() + getShifterSectionSE1() + "#EndSection(font)"; // + getCombinerSectionSE1() + getShifterSectionSE1(); // don't add shifter and combiner
		//console.log("textArea: ", textArea);
	
		// write result on the same page in div "textAreaOutput" for the moment
        
		//document.getElementById("textAreaOutput").innerHTML = "<form action='edit_font.php' method='post'><textarea id='font_as_text' name='font_as_text' rows='35' cols='120' spellcheck='false'>" + textArea + "</textarea><br><input type='submit' name='action' value='speichern'></form>";

		// use this code for the moment:
		// display text first in textarea (so that output can be verified and manually edited if necessary)
		// PROBLEM: when data is sent to server via this dynamically created form, the session gets lost => why?!?
		// WRONG: it's not the session that is lost (user is still logged-in and user data available - must be something else ...)
		// OK: fixed, problem was the session variables were reinitialized with an empty post variable ... see session.php
		
		document.getElementById("whole_page_content").innerHTML = "<h1>Speichern</h1><p><i>Untenstehend das Font, wie es direkt aus dem Grafikeditor exportiert wurde. Es besteht die Möglichkeit, es manuell nachzueditieren (falls gewünscht oder nötig, z.B. im Bereich Shifter oder Combiner). Um das Font definitiv in die Datenbank zu schreiben, klicken Sie auf 'speichern'.<i></p><form action='edit_font.php' method='post'><textarea id='font_as_text' name='font_as_text' rows='35' cols='110' spellcheck='false'>" + textArea + "</textarea><br><input type='submit' name='action' value='speichern'></form>";
		
		
		// use the following ode to send new model to data base:
		// - modify copy_font_to_session.php: instead of writing to session write directly to database
		// - once it works, the textarea step can probably be skipped
		
//		var request = new XMLHttpRequest();
//		request.open('POST', 'copy_font_to_session.php', /* async = */ false);
//
//		var formData = new FormData();
//		formData.append('font_as_text', textArea);
//    
//		request.send(formData);
		//console.log(request.response);
		
		
		
		
		
		
		//window.open("test");
	}
}

function arrayContainsFloatingPointWithTolerance(haystack, needle, tolerance) {
	var i = 0;
	var length = haystack.length;
	var hit = -1; 
	while ((i < length) && (hit < 0)) {
		var epsilon = Math.abs(haystack[i] - needle);
		if (epsilon < tolerance) {
			hit = i;
		}
		i++;
	}
	return hit;
}

function getRotatingAxisArrayForSE1(token) {
	var tokenDataList = token.tokenData;
	var rotatingAxisArray = [0,0,0]; // make sure the 3 fields for rotating axis in SE1 rev1 are 0 (in order to now if token has rotating axis or not)
	var tolerance = 0.01;
	var actualIndex = 0;
	for (var i=0; i<tokenDataList.length; i++) {
		var shiftX = tokenDataList[i].shiftX;
		if (!(shiftX < tolerance)) {
			if (arrayContainsFloatingPointWithTolerance(rotatingAxisArray, shiftX, tolerance) < 0) { // no entry is found
				rotatingAxisArray[actualIndex++] = tokenDataList[i].shiftX;  // array can be longer than 3, but SE1 rev1 only supports 3 rotating axis
			}	
		}
		if (tempKey == "SP") {
			    if (i==0) console.log("tokenDataList: ", tokenDataList);
				console.log("Key: ", tempKey, "data-i: ", i, "shiftX: ", shiftX, "rotatingAxisArray: ", rotatingAxisArray, "token: ", token)
		}
		
	} 
	//console.log("rotatingAxisArray: ", rotatingAxisArray);
	return rotatingAxisArray;
}

function calculateDRFieldForRevision1(knotType, calcType, raNumber) {
    var output = 0;
    switch (knotType.connect) {
		case true : break; // output += 0; // default
		case false : output += 5; break;
	}
	switch (calcType) {
		case "horizontal" : break; // output += 0; // default
		case "orthogonal" : output += 16; break; // set bit 4
		case "proportional" : output += 32; break; // set bit 5
	}
	switch (raNumber) {
		case 0 : break; // output += 0; // main axis (default)
		default : var shiftedNumber = raNumber << 6; // shift value 6 bits to left (use bits 7-8 for value)
				  output += shiftedNumber;
				break;
	}
	//console.log("drfield (revision1): ", output);
	
	return output;
}
/*
function determineRotatingAxisNumber(rotatingAxisList, findShiftX) {
	var number = -1;
	var tolerance = 0.01;
	for (var i=0; i<3; i++) {
		var shiftX = rotatingAxisList[i];
		if (Math.abs(shiftX - findShiftX) < tolerance) {
			number = i;
		}
	}
	//console.log("findShiftX: ", findShiftX, "corresponds to: ", rotatingAxisList[number], "number=", number);
	
	return number;
}
*/

function getCombinerSectionSE1() {
	return document.getElementById("combinerHTML").value;
}

function getShifterSectionSE1() {
	return document.getElementById("shifterHTML").value;
}

function getBaseSectionSE1() {	
	// this function "translate" SE2 data into SE1 notation
	
	var output = "\t#BeginSubSection(base)\n";
	
	// loop through list of tokens
	// "TT" => {  /*h*/ 0,  0.5,  0,  0,  5,  3,  0,  "", /**/ "",  "",  "",  "",  0,  0,  0,  0, /**/ 0,  0,  0,  0,  0,  0,  0,  0, /*d*/ 0,  30,  0,  1,  3,  0,  0,  0, /**/ 0,  0,  0,  0,  1,  0,  1,  0, /**/ 0,  2.5,  0,  4,  1,  0,  0,  0.5 }
	for (key in actualFont.tokenList) {
			tempKey = key; // for debugging
		//if (key == "0") console.log("begin export: ", key);
			
		if ((actualFont.tokenList[key].tokenType != "shifted") &&  (actualFont.tokenList[key].tokenType != "combined")) {		// export only base tokens, define base tokens negatively: != shifted && != combined (reason: tokenType might be undefined, since save function doesn't set this variable for the moment)
			
			//console.log("key: ", key);
			output += "\t\t\"" + key + "\" => {";
			
			// determine rotating axis before going through header
			// to do so, it is necessary to go once through the whole data array of the token 
			if (tempKey == "SP") {
			    console.log("token given to getRotatingAxisArrayForSE1: ", actualFont.tokenList[key])
			    console.log("actualFont: ", actualFont);
			}
			var rotatingAxisArray = getRotatingAxisArrayForSE1(actualFont.tokenList[key]); 
			
			/*
			if (key == "SP") {
				console.log("key: ", key, "rotatingAxisArray: ", rotatingAxisArray);
			}
			*/
			if (rotatingAxisArray.length > 3) {
				console.log("Error: more than 3 rotating axis (not possible in SE1, additional axis will be ignored)"); 
			}
			
			// write values to header
			actualFont.tokenList[key].header[7] = rotatingAxisArray[0];	// 1st rotating axis
			actualFont.tokenList[key].header[8] = rotatingAxisArray[1]; // 2nd rotating axis
			actualFont.tokenList[key].header[9] = rotatingAxisArray[2]; // 3rd rotating axis
			
			// add header
			output += " /*header*/ ";
			for (var i=0; i<24; i++) {
				switch (actualFont.tokenList[key].header[i]) {
					case "undefined" : output += "0, "; break;
					case "" : output += "\"\", "; break;
					case "no" : output += "\"no\", "; break;
					case "yes" : output += "\"yes\", "; break;
					case "wide" : output += "\"wide\", "; break;
					case "narrow" : output += "\"narrow\", "; break;
					case "up" : output += "\"up\", "; break;
					case "down" : output += "\"down\", "; break;
					default: 	if (i==3) {
									if ((actualFont.tokenList[key].tokenData[0] != undefined) && (actualFont.tokenList[key].tokenData[0] != null)) {
									output += humanReadableEditor(actualFont.tokenList[key].tokenData[0].tensions[2]) + ", ";		// incoming tension (offset 2) of first knot has to be written to header (offset 3) in SE1 ...
									} else output += "0, ";		// if there's no 1st knot, write 0
								} else output += actualFont.tokenList[key].header[i] + ", "; 
							
							break;
				
					// add comma and space between elements (no comma after last)
					//if (i != 23) output += ", ";	// comma is needed: array continues
				}
			}
			
			// add data
			output += "/*data*/ ";
		    var length = actualFont.tokenList[key].tokenData.length;
		    //console.log("length = ",length);
		    for (var i=0; i<length; i++) {
				
				// add tuplet with 8 entries 
				if (i != 0) output += " /**/ ";
				var d1 = calculateD1(actualFont.tokenList[key].tokenData[i].knotType);
				var d2 = calculateD2(actualFont.tokenList[key].tokenData[i].knotType);
				//var dr = calculateDR(actualFont.tokenList[key].tokenData[i].knotType);
				//var axisNumber = determineRotatingAxisNumber(rotatingAxisArray, actualFont.tokenList[key].tokenData[i].shiftX);
				var axisNumber = arrayContainsFloatingPointWithTolerance(rotatingAxisArray, actualFont.tokenList[key].tokenData[i].shiftX, 0.01);
				//axisNumber = (axisNumber < 0) ? 0 : axisNumber+1; // this line is correct, but the following is easier: -1 means no axis found => value 0 in dr field; values 0-2 mean: axis number 1-3 in dr field => conclusion: add 1 to axisNumber in any case
				axisNumber += 1;
				if (tempKey == "SP") {
					console.log("key: ", tempKey, "i: ", i, "axisNumber: ", axisNumber);
				}
				var dr = calculateDRFieldForRevision1(actualFont.tokenList[key].tokenData[i].knotType, actualFont.tokenList[key].tokenData[i].calcType, axisNumber);
				
				
				var actualShiftX = rotatingAxisArray[axisNumber-1]; 
				
				switch (actualFont.tokenList[key].tokenData[i].calcType) {
					case "horizontal" : // default in SE1
										output += humanReadableEditor(actualFont.tokenList[key].tokenData[i].vector1) + ", ";		// offset 0: x
										output += humanReadableEditor(actualFont.tokenList[key].tokenData[i].vector2) + ", ";		// offset 1: y
										break;
				    case "orthogonal" : // values x and y (vectors) have to be inverted!!!
										// in addition, SE1 stores coordinates as absolute values, so actualShiftX has to be added to rd2 value (= vector2)!
										output += humanReadableEditor(actualFont.tokenList[key].tokenData[i].vector2 + actualShiftX) + ", ";		// offset 0: x
										output += humanReadableEditor(actualFont.tokenList[key].tokenData[i].vector1) + ", ";		// offset 1: y
										break;
					case "proportional" : // values x and y (vectors) have to be inverted!!!
										  // in addition, SE1 stores coordinates as absolute values, so actualShiftX has to be added to rd2 value (= vector2)!
										output += humanReadableEditor(actualFont.tokenList[key].tokenData[i].vector2 + actualShiftX) + ", ";		// offset 0: x
										output += humanReadableEditor(actualFont.tokenList[key].tokenData[i].vector1) + ", ";		// offset 1: y
										break;
				}
				
				
				output += humanReadableEditor(actualFont.tokenList[key].tokenData[i].tensions[3]) + ", ";	// offset 2: t1 (use middle outgoing tension of SE2 = offset 3!)
				output += d1 + ", ";		// offset 3: d1 (more complex issue: some points have to be copied first ...)
				output += humanReadableEditor(calculateSE1Thickness(actualFont.tokenList[key].tokenData[i].thickness.shadowed.left, actualFont.tokenList[key].tokenData[i].thickness.shadowed.right)) + ", ";		// offset 4: thickness (use shadowed)
				output += dr + ", ";		// offset 5: dr field
				output += d2 + ", ";		// offset 6: d2 (see d1)
				if ((actualFont.tokenList[key].tokenData[i+1] != undefined) && (actualFont.tokenList[key].tokenData[i+1] != null)) {
						output += humanReadableEditor(actualFont.tokenList[key].tokenData[i+1].tensions[2]);	// offset 7: t2 (use incoming (= offset 2) middle tension of following knot in SE2)
				} else output += "0";	// if no following knot exists, write 0
	
				// add comma if necessary
				if (i != length-1) output += ", ";
				
			}
		    
	
			// close token definition and go to next line
			output += " }\n";
		} else {
			//console.log("not exported: ", key, actualFont.tokenList[key].tokenType);
		}
	}
	
	// close subsection
	output += "\t#EndSubSection(base)\n";
	
	return output;
}

function calculateD1(knotTypeObject) {		// parameter: TEKnotType
// to simplify things respect SE1 philosophy: only 1 (!) type per point can be set (well, that's not entirely true: since there are 2 fields (d1 and d2)
// some combinations (e.g. entry and exit point at same knot) are possible; in addition, the drawValue (connect in SE2) can be combined with any of those
// types in the same knot (what a mess:-)) ... again: to keep things simple (and with a maximum of compatibility): implement different functions for these 
// datafields (d1, d2, dr))
// => his means that if the same coordinates should hold different types of knots, then SEVERAL knots have to be defined!
// if you want to export tokens from SE1 to SE2 this separation must be done manually! (Note: this will be problematic since - graphically - it is impossible
// to add more than 1 point at the same coordinates with the mouse => maybe a workaround will be needed (..) for the moment leave it like that)
// the following docuementation comes directly from the SE1 php code
// conditional pivot point is obsolete (and won't be implemented)
// note also that first tension (= tension before first knot) is stored in the header in SE1 and can't be edited (header is copied without modification)

// d1: entry data field: 0 = regular point / 1 = entry point / 2 = pivot point / 4 = connecting point (for combined tokens created "on the fly")
//                       5 = "intermediate shadow point" (this point will only be used if the token is shadowed, otherwise it wont be inserted into splines),
//                       3 = conditional pivot point: if token is in normal position this point will be ignored/considered as a normal point (= value 0)
//                      98 = late entry point (= if token is first token in tokenlist then don't draw points before late entry point; consider this point as entry point)	
// d2: exit data field: 0 = regular point / 1 = exit point / 2 = pivot point / 99 = early exit point (= this point is the last one inserted into splines if token is the last one in tokenlist)
//                      3 = conditional pivot point: if token is in normal position this point will be ignored/considered as a normal point (= value 0)
// dr: data field for drawing function: 0 = normal (i.e. connect points) / 5 = don't connect to this point from preceeding point

	with (knotTypeObject) {
		if (entry) return 1;			// d1
		else if (pivot1) return 2; 		// d1
		else if (lateEntry) return 98;	// d1
		else if (combinationPoint) return 4; // d1
		else if (intermediateShadow) return 5; // d1
		else return 0; // corresponds to "regular point"
	} 
}

function calculateD2(knotTypeObject) {
	with (knotTypeObject) {
		if (exit) return 1; 		// d2
		else if (pivot2) return 2;	// d2
		else if (earlyExit) return 99; // d2
		else return 0; // corresponds to "regular point"
	}
}

function calculateDR(knotTypeObject) {
	with (knotTypeObject) {
		if (connect) return 0;
		else return 5;			// 5 = don't connect
	}
}

function calculateSE1Thickness(left, right) {		// shadowed thicknesses left and right
	var se1Thickness = left + right;
	if (se1Thickness < 1) se1Thickness = 1; 		// SE1 can't have thicknesses < 1 (..) => this is an backwards incompatibility from SE2 to SE1 (i.e. not all tokens designed by SE2 can be represented in SE1);
	return se1Thickness;
}
