
// global variables
var mainCanvas = new TECanvas(0,0,800,800);

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
	ctrlKey = false;
	
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
		case "load" : actualFont.loadTokenAndEditorData(document.getElementById("tokenpulldown").value); break;
		case "save" : actualFont.saveTokenAndEditorData(document.getElementById("tokenpulldown").value); break;
		case "delete" : actualFont.deleteTokenFromPullDownSelection(document.getElementById("tokenpulldown").value); break;
		case "todatabase" : console.log("toDatabase triggered..."); console.log("selection: ", document.getElementById("tokenpulldown").value); break;
		default : console.log("nothing triggered"); break;
	}
}
document.onkeydown = checkSpecialKeys; 
function checkSpecialKeys(e) {
	if (document.activeElement.id == "") {		// separate keyboard events: drawingArea vs input text fields
	
	e = e || window.event;
	if (e.ctrlKey) ctrlKey = true;
    else ctrlKey = false;
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
		
		/*console.log("input: ", document.getElementById("h1").value,
					document.getElementById("h2").value,
					document.getElementById("h3").value,
					document.getElementById("h4").value,
					document.getElementById("h5").value,
					document.activeElement.id
					); 
					
					document.getElementById("h7").blur(); */
					
					break;
		case "+" : mainCanvas.editor.rotatingAxis.parallelRotatingAxis.addParallelAxis(); break;
		case "-" : mainCanvas.editor.rotatingAxis.parallelRotatingAxis.deleteParallelAxis(); break;
	}
	//console.log("Keycode(charCode): ",keyPressed.charCodeAt(0));
	//console.log("KeyEvent: ", event);
	
	}
}
tool.onKeyUp = function(event) {
	//console.log("KeyEvent: ", event);
	keyPressed = "";
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
}

