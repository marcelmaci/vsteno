
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
	this.editor = new TEDrawingArea(new Point(100, 500), 4, 1, 10, 10);
	// sliders
	this.tensionSlider1 = new TETensionSlider(600, this.editor.upperY, 20, this.editor.lowerY-this.editor.upperY, "T1");
	this.tensionSlider1 = new TETensionSlider(630, this.editor.upperY, 20, this.editor.lowerY-this.editor.upperY, "T2");
}
TECanvas.prototype.handleEvent = function(event) {
	this.editor.handleEvent(event);
}

// main
//var editor = new TEDrawingArea(new Point(100, 500), 4, 1, 10, 10);
var mainCanvas = new TECanvas(0,0,800,600);
	
// global event handlers
var lastClick = null;
var doubleClickInterval = 500; // milliseconds
var doubleClick = false;
tool.onMouseDown = function(event) {
	var newClick = (new Date).getTime();
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
}
