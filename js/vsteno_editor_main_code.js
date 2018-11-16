
// main

var editor = new TEDrawingArea(new Point(100, 500), 4, 1, 10, 10);
	
// global event handlers
tool.onMouseDown = function(event) {
	editor.handleEvent(event);
}
tool.onMouseDrag = function(event) {
	editor.handleEvent(event);
}
tool.onMouseUp = function(event) {
	editor.handleEvent(event);
}
