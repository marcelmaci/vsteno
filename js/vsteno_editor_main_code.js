
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
	this.editor = new TEDrawingArea(this, new Point(100, 500), 4, 1, 10, 10);
	// sliders
	this.tensionSliders = new TETwoGroupedTensionSliders(this, this.editor.rightX+10, this.editor.upperY, 80, this.editor.lowerY-this.editor.upperY);
}
TECanvas.prototype.handleEvent = function(event) {
	//console.log("TECanvas.handleEvent()");
	if ((event.point.x >= this.x) && (event.point.x <= this.x+this.width) && (event.point.y >= this.y) && (event.point.y <= this.y+this.height)) {
		// instead of identifying object, call all event handlers
		this.editor.handleEvent(event);
		this.tensionSliders.handleEvent(event);
	}
	//this.crossUpdateSliderAndFreehandCurve();
}
//TECanvas.prototype.crossUpdateSliderAndFreehandCurve() {
	
//}

// main
//var editor = new TEDrawingArea(new Point(100, 500), 4, 1, 10, 10);
var mainCanvas = new TECanvas(0,0,800,600);
	
// global event handlers and variables
var lastClick = null,
	doubleClickInterval = 500, // milliseconds
	doubleClick = false,
	mouseDown = false;

tool.onMouseDown = function(event) {
	var newClick = (new Date).getTime();
	mouseDown = true;
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
}
