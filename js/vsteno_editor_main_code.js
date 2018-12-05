
// global variables
// auxiliary lines to test bezier curves
var outerLines = new Path();
var innerLines = new Path();
var tangent = new Path();

//var editor = new TEDrawingArea(new Point(100, 500), 4, 1, 10, 10);
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
	
var tangentPrecision = 0.001,
	tangentFixPointMaxIterations = 200,
	tangentBetweenCurvesMaxIterations = 4;
	
var keyPressed = "";
var arrowUp = false,			// global variables for arrow keys
	arrowDown = false,
	arrowLeft = false,
	arrowRight = false;
	
var selectedTension = "locked";		// locked = set all three tensions (left, right, middle) to same value; other values for selectedTension: left, middle, right (every tension is handled individually)
var selectedShape = "normal"		// normal = normal outer shape; shadowed = shadowed outer shape
 
//var thicknessSlider = new TEThicknessSlider(100, 550, 400, 20, "L");
//var thicknessSliders = new TETwoGroupedThicknessSliders(null, 100, 500, 400, 70);

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
	//this.editor = new TEDrawingArea(this, new Point(100, 500), 4, 1, 10, 10);
	this.editor = new TEDrawingArea(this, new Point(100, 450), 4, 1, 10, 10);
	// sliders
	this.tensionSliders = new TETwoGroupedTensionSliders(this, this.editor.rightX+10, this.editor.upperY, 80, this.editor.lowerY-this.editor.upperY);
	this.thicknessSliders = new TETwoGroupedThicknessSliders(this, this.editor.leftX, this.editor.lowerY+30, this.editor.rightX - this.editor.leftX, 70);
	//console.log("TwoGroupedSliders: BEFORE:", this.thicknessSliders);

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
//TECanvas.prototype.crossUpdateSliderAndFreehandCurve() {
	
//}

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
document.onkeydown = checkSpecialKeys; 
function checkSpecialKeys(e) {
	e = e || window.event;
	if (e.keyCode == '38') {
        arrowUp = true; // up arrow
        //console.log("arrowUP");
    }
    else if (e.keyCode == '40') {
        arrowDown = true; // down arrow
		//console.log("arrowDown");
    }
    else if (e.keyCode == '37') {
       arrowLeft = true; // left arrow
	   mainCanvas.editor.editableToken.selectPreceedingKnot();
	   // for following line: see comment in freehand => setKnotType()
	   mainCanvas.editor.editableToken.selectedKnot = mainCanvas.editor.editableToken.markedKnot;
	   //console.log("arrowLeft");
    }
    else if (e.keyCode == '39') {
       arrowRight = true; // right arrow
	   mainCanvas.editor.editableToken.selectFollowingKnot();
	   // for following line: see comment in freehand => setKnotType()
	   mainCanvas.editor.editableToken.selectedKnot = mainCanvas.editor.editableToken.markedKnot;
	   //console.log("arrowRight");
    }
}
document.onkeyup = function resetSpecialKeys() {
	arrowUp = false;
	arrowDown = false;
	arrowLeft = false;
	arrowRight = false;
}


// test
/*
function makeVisible() {
	console.log("make visible");
	console.log("TwoGroupedSliders: BEFORE:", mainCanvas.thicknessSliders);
	mainCanvas.thicknessSliders.showHorizontalSliders();
	console.log("TwoGroupedSliders: AFTER:", mainCanvas.thicknessSliders);
}
function makeInvisible() {
	console.log("make invisible");
	console.log("TwoGroupedSliders: BEFORE:", mainCanvas.thicknessSliders);
	mainCanvas.thicknessSliders.hideHorizontalSliders();
	console.log("TwoGroupedSliders: AFTER:", mainCanvas.thicknessSliders);
}
*/

// work with keyboard events instead
tool.onKeyDown = function(event) {
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
		//case "v" : makeVisible(); break; // show thickness slider (test)
		//case "i" : makeInvisible(); break; // hide thickness slider (test)
		case "o" : mainCanvas.editor.editableToken.setKnotType("orthogonal"); break;
		case "h" : mainCanvas.editor.editableToken.setKnotType("horizontal"); break;
		case "p" : mainCanvas.editor.editableToken.setKnotType("proportional"); break;
		
	
	}
	//console.log("Keycode: ",keyPressed.charCodeAt(0));
	//console.log("KeyEvent: ", event);
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
