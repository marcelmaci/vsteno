
// main

var editor = new TEDrawingArea(new Point(100, 500), 4, 1, 10, 10);
	
// global event handlers
var lastClick = null;
var doubleClickInterval = 500; // milliseconds
var doubleClick = false;
tool.onMouseDown = function(event) {
	var newClick = (new Date).getTime();
	//console.log("lastclick: ", lastClick, " newClick: ", newClick, " delta: ", newClick-lastClick);
	if ((newClick-lastClick) < doubleClickInterval) doubleClick = true;
	else doubleClick = false;
	
	editor.handleEvent(event);
	lastClick = newClick;
}
tool.onMouseDrag = function(event) {
	editor.handleEvent(event);
}
tool.onMouseUp = function(event) {
	editor.handleEvent(event);
}
