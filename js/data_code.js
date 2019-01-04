
// contains data operations for vsteno-editor (import / export etc.)

var tokenPullDownSelection = [];
var actualFont = new ShorthandFont();

function filterOutEmptySpaces(string) {
	var newString = string;
	do {
		string = newString;
		newString = string.replace(/\s+/, '');
	} while (newString != string);
	return newString;
}

// general functions
function addNewTokenToPullDownSelection(token) {
	token = filterOutEmptySpaces(token); // filter out empty spaces 
	if ((tokenPullDownSelection.indexOf(token) == -1)  && (token != "")) {	// element doesn't exist => add
		tokenPullDownSelection.push(token);
		tokenPullDownSelection.sort(); // sort array alphabetically
		updatePullDownSelection(token);
	}
	// set textfield to empty
	document.getElementById("token").value = "";
}
function updatePullDownSelection(token) {			// preselect token in list
	var optionList = "<option value=\"select\">-select-</option>\n";
	var preselection = tokenPullDownSelection.indexOf(token); // returns -1 if array doesn't contain token
	for (var i=0;i<tokenPullDownSelection.length; i++) {
		if (i == preselection) optionList += "<option value=\"" + tokenPullDownSelection[i] + "\" selected>" + tokenPullDownSelection[i] + "</option>\n";
		else optionList += "<option value=\"" + tokenPullDownSelection[i] + "\">" + tokenPullDownSelection[i] + "</option>\n"; 
	}
	var element = document.getElementById("tokenpulldown");
	element.innerHTML = optionList;	
}

// classes 
// class ShorthandFont
function ShorthandFont() {
	this.tokenList = []; // array 
	this.editorData = []; // array
}
ShorthandFont.prototype.saveTokenAndEditorData = function(token) {		// saves actual token to this.tokenList["token"]
	if ((token != "select") && (token != "empty")) {
		this.deleteTokenAndEditorData(token);
		this.tokenList[token] = new TokenDefinition();			// data will be copied directly via constructor that call goAndGrabThatTokenData()-method
		this.editorData[token] = new EditorParameters();		// same for editor data
	}
	
	//console.log("ShorthandFont: ", this);
	//console.log("EditableToken: ", mainCanvas.editor.editableToken);
}
ShorthandFont.prototype.deleteTokenFromPullDownSelection = function(token) {
	this.deleteTokenData(token);
	var index = tokenPullDownSelection.indexOf(token);
	if (index > -1) {	// element does exist => delete it
		tokenPullDownSelection.splice(index, 1);
		updatePullDownSelection();
	}
}
ShorthandFont.prototype.deleteTokenAndEditorData = function(token) {
	this.deleteTokenData(token);
	this.deleteEditorData(token);
}
ShorthandFont.prototype.deleteTokenData = function(token) {
	//console.log("deleteTokenData");
	this.tokenList[token] = null;
}
ShorthandFont.prototype.deleteEditorData = function(token) {
	//console.log("deleteEditorData");
	this.editorData[token] = null;	
}
ShorthandFont.prototype.loadTokenAndEditorData = function(token) {
	mainCanvas.editor.loadAndInitializeEditorData(actualFont.editorData[token]);
	mainCanvas.editor.loadAndInitializeTokenData(actualFont.tokenList[token]);
}

// database data types
// class TokenDefinition
function TokenDefinition() {
	this.header = null;
	this.tokenData = [];
	this.goAndGrabThatTokenData();
}
TokenDefinition.prototype.goAndGrabThatTokenData = function() {
	mainCanvas.editor.editableToken.copyTextFieldsToHeaderArray();
	this.header = mainCanvas.editor.editableToken.header.slice(); 
	// well, guess what ... slice() is vital here ... otherwise JS will make this.header point to one and the same object 
	// (and operations destined for this token will affect other objects also ... ceterum censeo ;-))
	// to resume: slice() <=> copy by value
	
	//console.log("goAndGrabThatTokenData: header: ", this.header);
	
	this.getTokenDefinition();
}
TokenDefinition.prototype.getTokenDefinition = function() {
	for (var i=0; i<mainCanvas.editor.editableToken.knotsList.length; i++) {
		this.tokenData.push(new DBKnotData(i));	
	}
}

// class EditorParameters
function EditorParameters() {
	this.rotatingAxisList = [];
	this.goAndCollectThatEditorData();
}
EditorParameters.prototype.goAndCollectThatEditorData = function() {
	for (var i=0; i<mainCanvas.editor.rotatingAxis.parallelRotatingAxis.newAxisList.length; i++) {
		this.rotatingAxisList.push(mainCanvas.editor.rotatingAxis.parallelRotatingAxis.newAxisList[i].shiftX);
	}
}

// class DBTokenData
function DBKnotData(index) {
	this.knotType = null;
	this.calcType = null; 	// horizontal, orthogonal, proportional
	this.vector1 = null;
	this.vector2 = null;
	this.shiftX = null;
	this.shiftY = null;
	this.tensions = null;
	this.thickness = [];
	// call function to define variables
	return this.readKnotData(index);
}
DBKnotData.prototype.readKnotData = function(index) {
	this.knotType = mainCanvas.editor.editableToken.knotsList[index].type;
	this.calcType = mainCanvas.editor.editableToken.knotsList[index].linkToRelativeKnot.type;
	this.vector1 = mainCanvas.editor.editableToken.knotsList[index].linkToRelativeKnot.rd1;
	this.vector2 = mainCanvas.editor.editableToken.knotsList[index].linkToRelativeKnot.rd2;
	this.shiftX = mainCanvas.editor.editableToken.knotsList[index].shiftX;
	this.shiftY = mainCanvas.editor.editableToken.knotsList[index].shiftY;
	this.tensions = mainCanvas.editor.editableToken.knotsList[index].tensions;
	this.thickness["standard"] = []; 	// I'm pretty sure there's another syntax for this in JS, but as I said: ceterum censeo ... ;-)
	this.thickness["shadowed"] = [];
	this.thickness["standard"]["left"] = mainCanvas.editor.editableToken.leftVectors[0][index].distance;		// make data more readable with associative array
	this.thickness["standard"]["right"] = mainCanvas.editor.editableToken.rightVectors[0][index].distance;	    // hugh ... copying array element by element ... this 'll be slow ... (but who cares ... ;-)
	this.thickness["shadowed"]["left"] = mainCanvas.editor.editableToken.leftVectors[1][index].distance;
	this.thickness["shadowed"]["right"] = mainCanvas.editor.editableToken.rightVectors[1][index].distance;
	return this;
}
