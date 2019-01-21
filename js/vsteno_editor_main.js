/* VSTENO TOKEN EDITOR (working title)
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
 *
 * ADDITIONAL DISCLAIMER
 * 
 * This Program uses the paper.js library which has been published under the MIT
 * license. The MIT-License is compatible with the GPL-License as long as the 
 * derived product (e.g. this program) and programs derived from it are published
 * under the GPL-license.
 * 
 * THE ORIGINAL LICENSE FOR PAPER.JS
 * 
 * Copyright (c) 2011 - 2016, Juerg Lehni & Jonathan Puckey
 * http://scratchdisk.com/ & http://jonathanpuckey.com/
 * All rights reserved.
 * 
 * The MIT License (MIT)
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE. 
 */
 

// global variables
var mainCanvas = new TECanvas(0,0,800,800);
var middlePathWithVariableWidth = [];	// test: array of paths (subdivided main middle path)
var showMiddlePathWithVariableWidth = false;

// version
//var versionSE = "SE2";	// default editor mode is SE2

// global event handlers and variables
var lastClick = null,
	doubleClickInterval = 500, // milliseconds
	doubleClick = false,
	mouseDown = false;

// global variables
var knotTypeAutoDefine = true,
	colorEntryKnot = '#00f',
	colorExitKnot = colorEntryKnot,
	colorPivot1 = '#f0f',
	colorPivot2 = colorPivot1,
	colorNormalKnot = '#f00',
	colorSelectedKnot = '#aaa',
	colorMarkedKnot = '#FFFC00';
	
// rotating axis
var colorParallelRotatingAxisUnselected = '#0f0',
	colorParallelRotatingAxisSelected = '#f00',
	colorMainRotatingAxisSelected = colorParallelRotatingAxisSelected,
	colorMainRotatingAxisUnselected = colorParallelRotatingAxisUnselected;
	
var tangentPrecision = 0.001,
	tangentFixPointMaxIterations = 200,
	tangentBetweenCurvesMaxIterations = 4;
	
var keyPressed = "";
var arrowUp = false,			// global variables for arrow keys
	arrowDown = false,
	arrowLeft = false,
	arrowRight = false,
	ctrlKey = false,
	altKey = false;
	
var selectedTension = "locked";		// locked = set all three tensions (left, right, middle) to same value; other values for selectedTension: left, middle, right (every tension is handled individually)
var selectedShape = "normal"		// normal = normal outer shape; shadowed = shadowed outer shape
var selectedShapeFill = false;		// true: fill Shape; false: don't fill (toggle with 'f')
var selectedShapeFillColor = '#000';
var connectPreceedingAndFollowingYes = false;	// true = connect, false = don't connect

// main classes
// class TECanvas (main container for complete drawing area)
function TECanvas(x, y, width, height) {
	// properties
	this.x = x;
	this.y = y;
	this.width = width;
	this.height = height;
	// objects
	// main editor
	this.editor = new TEDrawingArea(this, new Point(100, 450), 4, 1, 10, 10);
	// sliders
	this.tensionSliders = new TETwoGroupedTensionSliders(this, this.editor.rightX+10, this.editor.upperY, 80, this.editor.lowerY-this.editor.upperY);
	this.thicknessSliders = new TETwoGroupedThicknessSliders(this, this.editor.leftX, this.editor.lowerY+30, this.editor.rightX - this.editor.leftX, 70);
}
TECanvas.prototype.handleEvent = function(event) {
	//console.log("TECanvas.handleEvent()");
	if ((event.point.x >= this.x) && (event.point.x <= this.x+this.width) && (event.point.y >= this.y) && (event.point.y <= this.y+this.height)) {
		// instead of identifying object, call all event handlers
		this.editor.handleEvent(event);
		this.tensionSliders.handleEvent(event);
		this.thicknessSliders.handleEvent(event);
		//console.log("thicknessSliders: ", this.thicknessSliders);
	}
	//this.crossUpdateSliderAndFreehandCurve();
}

// enable right clicks
/* doesn't work: unfortunately the oncontextmenu-method is called after the tool.nomouse-methods ...
// which means that you only will know that a right click was made when it is released 
// (and that doesn't seem very helpful ... ;-)
// try to use the button-property of paper.js-event: it's not documented, but wenn inspecting
// the event object in the debugger, the property is clearly there ... !? 
// work with global variables for the buttons
// By the way: there's also information about ALT- and CTRL-Key (could be used in combination
// with mouse events)
// window.oncontextmenu: still needed in order to avoid pop-up menu!
*/
// doesn't work: button property is not set
window.oncontextmenu = function(event) {
	//console.log("rightclick: ", event);
	return false; // avoid popping up of context menu
}
// eventHandler for global functions (load, save, delete, save to database)
document.onClick = function() {
	console.log("onclick: ", document.activeElement.id);
	switch (document.activeElement.id) {
		case "addnew" : addNewTokenToPullDownSelection(document.getElementById("token").value); break;
		case "load" : //if (actualFont.editorData != null) actualFont.loadTokenAndEditorData(document.getElementById("tokenpulldown").value); 
					  //else loadTokenAndEditorData(document.getElementById("tokenpulldown").value);
					  // replace method definitely by global function due to php import
					  loadTokenAndEditorData(document.getElementById("tokenpulldown").value);
					  break;
		case "save" : // call add function first, in case text field is not empty
					  // in that case: add token (name), select it and save it directly to this (eventually new) token (name)
					  addNewTokenToPullDownSelection(document.getElementById("token").value); 
					  //actualFont.saveTokenAndEditorData(document.getElementById("tokenpulldown").value); 
					  // replace method definitely by global function due to php import
					  saveTokenAndEditorData(document.getElementById("tokenpulldown").value);
					  break;
		case "delete" : //actualFont.deleteTokenFromPullDownSelection(document.getElementById("tokenpulldown").value); 
						// replace method definitely by function due to php import
						deleteTokenFromPullDownSelection(document.getElementById("tokenpulldown").value);
					  
						break;
		case "savetodatabase" : console.log("toDatabase triggered..."); console.log("selection: ", document.getElementById("tokenpulldown").value); 
							writeDataToDB();
							break;
		default : console.log("nothing triggered"); break;
	}
}
document.onkeydown = checkSpecialKeys; 
function checkSpecialKeys(e) {
	if (document.activeElement.id == "") {		// separate keyboard events: drawingArea vs input text fields
	
	e = e || window.event;
	//console.log("e: ", e);
	if (e.ctrlKey) ctrlKey = true;
    else ctrlKey = false;
	if (e.altKey) altKey = true;
	else altKey = false;
   
    if (ctrlKey) {
		if (e.keyCode == '38') {
			arrowUp = true; // up arrow
			//console.log("arrowUP");
		} else if (e.keyCode == '40') {
			arrowDown = true; // down arrow
			//console.log("arrowDown");
		} else if (e.keyCode == '37') {
			arrowLeft = true; // left arrow
			mainCanvas.editor.rotatingAxis.parallelRotatingAxis.selectPreceedingAxis();
			// for following line: see comment in freehand => setKnotType()
			
		} else if (e.keyCode == '39') {
			arrowRight = true; // right arrow
			mainCanvas.editor.rotatingAxis.parallelRotatingAxis.selectFollowingAxis();
			//console.log("arrowRight");
		} else if (e.key == "1") {
			//console.log("set entry knot");
			mainCanvas.editor.editableToken.knotsList[mainCanvas.editor.editableToken.index-1].toggleKnotType("entry");
		} else if (e.key == "2") {
			console.log("set normal knot");
			mainCanvas.editor.editableToken.knotsList[mainCanvas.editor.editableToken.index-1].setKnotType("normal"); 	// can be used to "reset" knot type
		} else if (e.key == "3") {
			//console.log("i=", mainCanvas.editor.editableToken.index-1);
			//console.log("set exit knot");
			mainCanvas.editor.editableToken.knotsList[mainCanvas.editor.editableToken.index-1].toggleKnotType("exit");
		} else if (e.key == "4") {
			console.log("set pivot1 knot");
			mainCanvas.editor.editableToken.knotsList[mainCanvas.editor.editableToken.index-1].toggleKnotType("pivot1");
		} else if (e.key == "5") {
			//console.log("set connPoint value");
			mainCanvas.editor.editableToken.knotsList[mainCanvas.editor.editableToken.index-1].toggleKnotType("combinationPoint");
		} else if (e.key == "6") {
			//console.log("set pivot2 knot");
			mainCanvas.editor.editableToken.knotsList[mainCanvas.editor.editableToken.index-1].toggleKnotType("pivot2");
		} else if (e.key == "7") {
			//console.log("set lateEntry knot");
			mainCanvas.editor.editableToken.knotsList[mainCanvas.editor.editableToken.index-1].toggleKnotType("lateEntry");
		} else if (e.key == "8") {
			//console.log("set connect");
			mainCanvas.editor.editableToken.knotsList[mainCanvas.editor.editableToken.index-1].toggleKnotType("connect");
		} else if (e.key == "9") {
			//console.log("set earlyExit knot");
			mainCanvas.editor.editableToken.knotsList[mainCanvas.editor.editableToken.index-1].toggleKnotType("earlyExit");
		}  else if (e.key == "0") {
			// use this for intermediata shadow points
			mainCanvas.editor.editableToken.knotsList[mainCanvas.editor.editableToken.index-1].toggleKnotType("intermediateShadow");
			
			//console.log("show knot status: ");
			//console.log(mainCanvas.editor.editableToken.knotsList[mainCanvas.editor.editableToken.index].type);
			//console.log(mainCanvas.editor.editableToken);
		}		
	} else if (altKey) {
		if (e.keyCode == '38') {
			arrowUp = true; // up arrow
			mainCanvas.editor.moveRelativeSelectedKnot(0,-1);
			//console.log("arrowUP");
		} else if (e.keyCode == '40') {
			arrowDown = true; // down arrow
			mainCanvas.editor.moveRelativeSelectedKnot(0,1);
			//console.log("arrowDown");
		} else if (e.keyCode == '37') {
			arrowLeft = true; // left arrow
			mainCanvas.editor.moveRelativeSelectedKnot(-1,0);
			return false; // returning false prevents execution of predefined browser functionality (e.g. "go back" for alt + left arroy) => should be used for other commands also! => fix that later
			// for following line: see comment in freehand => setKnotType()
			//console.log("arrowLeft");
		} else if (e.keyCode == '39') {
			arrowRight = true; // right arrow
			mainCanvas.editor.moveRelativeSelectedKnot(1,0);
		}
	} else {
		if (e.keyCode == '38') {
			arrowUp = true; // up arrow
			//console.log("arrowUP");
		} else if (e.keyCode == '40') {
			arrowDown = true; // down arrow
			//console.log("arrowDown");
		} else if (e.keyCode == '37') {
			arrowLeft = true; // left arrow
			mainCanvas.editor.editableToken.selectPreceedingKnot();
			// for following line: see comment in freehand => setKnotType()
			mainCanvas.editor.editableToken.selectedKnot = mainCanvas.editor.editableToken.markedKnot;
			//console.log("arrowLeft");
		} else if (e.keyCode == '39') {
			arrowRight = true; // right arrow
			mainCanvas.editor.editableToken.selectFollowingKnot();
			// for following line: see comment in freehand => setKnotType()
			mainCanvas.editor.editableToken.selectedKnot = mainCanvas.editor.editableToken.markedKnot;
			//console.log("arrowRight");
		} else if (e.keyCode == '32') {
			// space bar
			mainCanvas.editor.cleanDrawingArea();
		} 
/*		else if (e.key == "w") {
			console.log("Try this hack ..."); 
			console.log("actualFont: ", actualFont); 
			console.log("actualFontSE1: ", actualFontSE1); 
			actualFont = actualFontSE1; // problem: all prototype functions get lost ... try to save and copy them (not necessary any more: methods rewritten as global functions, actualFont now only contains data)
			console.log(actualFont);
			console.log("combiner: ", document.getElementById("combinerHTML").value);
			console.log("shifter: ", document.getElementById("shifterHTML").value);
			
			//console.log("combiner: ", actualCombiner);
			//console.log("shifter: ", actualShifter);
			createPullDownSelectionFromActualFont();
		} 
*/
		
		else if (e.key == "q") {
			//console.log("toggle middle path visibility");
			mainCanvas.editor.toggleVisibilityMiddlePathWithVariableWidth();	
		}
	}    
	//console.log("e.keyCode/e.ctrlKey: ", e.keyCode, e.ctrlKey);
	}
}
document.onkeyup = function resetSpecialKeys() {
	arrowUp = false;
	arrowDown = false;
	arrowLeft = false;
	arrowRight = false;
	mainCanvas.editor.showSelectedKnotSE1Type();
}
// work with keyboard events instead
tool.onKeyDown = function(event) {	
	//console.log("active element", document.activeElement.id);
	if (document.activeElement.id == "") {		// separate keyboard events: drawingArea vs input text fields
	
	keyPressed = event.key;
	if (selectedTension != "locked") {
		switch (keyPressed) {	
			case "m" : selectedTension = "middle"; mainCanvas.tensionSliders.setNewLabels(); mainCanvas.tensionSliders.updateValues(); break;
			case "l" : selectedTension = "left"; mainCanvas.tensionSliders.setNewLabels(); mainCanvas.tensionSliders.updateValues(); break;
			case "r" : selectedTension = "right"; mainCanvas.tensionSliders.setNewLabels(); mainCanvas.tensionSliders.updateValues(); break;
		}
	}
	switch (keyPressed) {	
		// use 't' to toggle between locked and unlocked tensions
		case "t" : selectedTension = (selectedTension == "locked") ? "middle" : "locked"; mainCanvas.tensionSliders.setNewLabels(); mainCanvas.tensionSliders.updateValues(); break;
		//case "y" : writeDataToDB(); break;
		case "s" : selectedShape = (selectedShape == "normal") ? "shadowed" : "normal"; mainCanvas.thicknessSliders.updateLabels(); break;
		case "f" : selectedShapeFill = (selectedShapeFill == false) ? true : false; mainCanvas.thicknessSliders.setOuterShapesVisibility(); break; // toggle fill and update (method setOuterShapesVisibility should be transferred to more general object, e.g. TEDrawingArea)
		case "o" : mainCanvas.editor.editableToken.setKnotType("orthogonal"); break;
		case "h" : mainCanvas.editor.editableToken.setKnotType("horizontal"); break;
		case "p" : mainCanvas.editor.editableToken.setKnotType("proportional"); break;
		//case "c" : mainCanvas.editor.editableToken.toggleParallelRotatingAxisType(); break;
		case "c" : ; connectPreceedingAndFollowingYes = (connectPreceedingAndFollowingYes) ? false : true; 
					 mainCanvas.editor.preceeding.line.visible = connectPreceedingAndFollowingYes;
					 mainCanvas.editor.following.line.visible = connectPreceedingAndFollowingYes;
					 mainCanvas.editor.connectPreceedingAndFollowing();
					break;
		case "i" : mainCanvas.editor.editableToken.copyTextFieldsToHeaderArray();
		//case "q" : console.log("test"); break;
//		case "q" : mainCanvas.editor.toggleVisibilityMiddlePathWithVariableWidth(); break;	// test
		
		/*console.log("input: ", document.getElementById("h1").value,
					document.getElementById("h2").value,
					document.getElementById("h3").value,
					document.getElementById("h4").value,
					document.getElementById("h5").value,
					document.activeElement.id
					); 
					
					document.getElementById("h7").blur(); */
					
	//				break;
		case "+" : mainCanvas.editor.rotatingAxis.parallelRotatingAxis.addParallelAxis(); break;
		case "-" : mainCanvas.editor.rotatingAxis.parallelRotatingAxis.deleteParallelAxis(); break;
/*		case "w" : console.log("Try this hack ..."); 
			console.log("actualFont: ", actualFont); 
			console.log("actualFontSE1: ", actualFontSE1); 
			//var tempPrototypes = actualFont.prototype;
			//var oldActualFont = actualFont;
			actualFont = actualFontSE1; // problem: all prototype functions get lost ... try to save and copy them
			//actualFont.prototype = Object.clone(oldActualFont.prototype);
			//actualFont.prototype.loadAndInitializeEditorData = oldActualFont.prototype.loadAndInitializeEditorData;
			//actualFont.prototype = tempPrototypes; // doesn't work ... the problem is that actualFont contains loadFont-method ... if prototype is deleted no font can be loaded ... good thing that oop always keeps data and methods together, right? well, in that case, it's a rather annoying effect: since data has to be transferred from php to js, php creates a new datastructure which doesn't contain the JS methods ... would be nice to copy only the data this time ...
			//actualFont.prototype = new ShorthandFont(); // try to re-inherit prototypes from ShorthandFont (doesn't seem to work neither)
			console.log(actualFont);
			createPullDownSelectionFromActualFont();
			break; // try this hack ...
*/
	}
	//console.log("Keycode(charCode): ",keyPressed.charCodeAt(0));
	//console.log("KeyEvent: ", event);
	
	}
}
tool.onKeyUp = function(event) {
	//console.log("KeyEvent: ", event);
	keyPressed = "";
	mainCanvas.editor.showSelectedKnotSE1Type();
}

tool.onMouseDown = function(event) {
	
	var newClick = (new Date).getTime();
	mouseDown = true;
	//console.log("mousedown: event: ", event);
	//console.log("lastclick: ", lastClick, " newClick: ", newClick, " delta: ", newClick-lastClick);
	if ((newClick-lastClick) < doubleClickInterval) doubleClick = true;
	else doubleClick = false;
	mainCanvas.handleEvent(event);
	lastClick = newClick;
}
tool.onMouseDrag = function(event) {
	mainCanvas.handleEvent(event);
}
tool.onMouseUp = function(event) {
	mainCanvas.handleEvent(event);
	mouseDown = false;
    mainCanvas.editor.rotatingAxis.controlCircle.unselect(); 
	mainCanvas.editor.showSelectedKnotSE1Type();
}

// load font automatically => not clear which code is executed: this one (in head) or the patched one (in body)?!
window.onload = function() {
	//console.log("versionSE: ", versionSE, "actualFontSE1: ", actualFontSE1);
	if (actualFontSE1 != undefined) {		// if editor is used in SE1 mode, load font (= "patched" variable actualFontSE1) automatically 
		actualFont = actualFontSE1;
		createPullDownSelectionFromActualFont();
	}
}

