
// class TEVisuallyModifiableCircle
function TEVisuallyModifiableCircle(position, radius, color, selectColor, strokeColor ) {
	//console.log("TEVisuallyModifiableCircle.constructor");
	this.circle = new Path.Circle(position, radius);
	this.circle.fillColor = color;
	this.circle.strokeWidth = 0;
	this.circle.strokeColor = strokeColor;
	this.originalColor = color;
	this.selectColor = selectColor;
}
TEVisuallyModifiableCircle.prototype.mark = function() { // mark <=> set strokeWidth = 2 and strokeColor to a predefined value
	this.circle.strokeWidth = 2;
}
TEVisuallyModifiableCircle.prototype.unmark = function() { // unmark <=> show full circle again
	this.circle.strokeWidth = 0;
}
TEVisuallyModifiableCircle.prototype.select = function() { // select <=> modify fillColor of circle
	this.circle.fillColor = this.selectColor;
}
TEVisuallyModifiableCircle.prototype.unselect = function() { // unselect <=> restore original fillColor
	this.circle.fillColor = this.originalColor;
}
TEVisuallyModifiableCircle.prototype.handleMouseDown = function(event) {
	//console.log("TEVisuallyModifiableCircle.handleMouseDown");
	this.mark();
	this.select();
	this.circle.position = event.point;
}
TEVisuallyModifiableCircle.prototype.handleMouseDrag = function(event) {
	this.circle.position = event.point;
}
TEVisuallyModifiableCircle.prototype.handleMouseUp = function(event) {
	//console.log("TEVisuallyModifiableCircle.handleMouseUp()");
	this.unselect();	// unselect, but leave it marked
}
TEVisuallyModifiableCircle.prototype.isStatic = function() {
	return false;
}
TEVisuallyModifiableCircle.prototype.isDynamic = function() {
	return true;
}


// class TEConnectionPoint extends TEVisuallyModifiableCircle
function TEConnectionPoint(drawingArea, x, y ) {
	// call parent constructor
	TEVisuallyModifiableCircle.prototype.constructor.call(this, new Point(x, y), 5, '#000', '#aaa', '#00f');
	// handle own stuff
	this.parent = drawingArea;
	this.line = this.line = new Path.Line( this.position, this.position ); // initialize line as point inside circle
	this.line.strokeColor = '#000';
}
TEConnectionPoint.prototype = new TEVisuallyModifiableCircle(); 	// inherit
TEConnectionPoint.prototype.handleMouseDown = function(event) {
	if (this.circle == event.item) {
		//console.log("Set new markedCircle in parent", event.item);
		this.parent.setMarkedCircle(this);
	}
	TEVisuallyModifiableCircle.prototype.handleMouseUp.call(this, event); // call parent's method	
}
TEConnectionPoint.prototype.handleMouseUp = function(event) {
	//console.log("TEConnectionPoint.handleMouseUp()");
	if ((event.point.x >= this.parent.leftX) && (event.point.x < this.parent.rightX) && (event.point.y < this.parent.lowerY) && (event.point.y > this.parent.upperY)) {
		this.circle.position = event.point;
		this.parent.itemSelected = this.parent;
		this.parent.fhCircleSelected = null;
	} else {
		// just "release" connecting point (and leave point where it is)
		this.parent.itemSelected = this.parent;
		this.parent.fhCircleSelected = null;	
	}
	TEVisuallyModifiableCircle.prototype.handleMouseUp.call(this, event); // call parent's method
}
TEConnectionPoint.prototype.handleMouseDrag = function(event) {
	if ((event.point.x >= this.parent.leftX) && (event.point.x < this.parent.rightX) && (event.point.y < this.parent.lowerY) && (event.point.y > this.parent.upperY)) {
		this.circle.position = event.point;
	}
}
TEConnectionPoint.prototype.handleEvent = function(event) {
	//console.log("TEConnectionPoint.handleEvent()");
	switch (event.type) {
		case "mousedown" : this.handleMouseDown(event); break;
		case "mouseup" : this.handleMouseUp(event); break;
		case "mousedrag" : this.handleMouseDrag(event); break;
	}
}
TEConnectionPoint.prototype.markCircle = function() {
	//console.log("markCircle");
	this.unmarkCircle();
	this.circle.strokeColor = '#00f';
	this.circle.strokeWidth = 2;
}
TEConnectionPoint.prototype.unmarkCircle = function() {
	if (this.parent.markedCircle != null) {
		if (this.parent.markedCircle.circle == undefined) this.parent.markedCircle.strokeWidth = 0;	// freehand circles should be defined as objects also ...
		else this.parent.markedCircle.circle.strokeWidth = 0;
	}
}

// class TEConnectionPointPreceeding extends TEConnectionPoint
function TEConnectionPointPreceeding(drawingArea, x, y) {
	TEConnectionPoint.call( this, drawingArea, x, y );
}
TEConnectionPointPreceeding.prototype = new TEConnectionPoint(); //new TEConnectionPoint(TEConnectionPoint.prototype);
TEConnectionPointPreceeding.prototype.connect = function() {
/*	if (this.parent.fhCircleList.length != 0) {
		//console.log(this.parent.fhCircleList[0].position);
		var entryPoint = this.parent.fhCircleList[0];
		this.line.segments[0].point = this.circle.position;
		this.line.segments[1].point = entryPoint.position;
		//console.log(this.line);
	}*/
}
/*
TEConnectionPointPreceeding.prototype.handleEvent = function(event) { // overload parent method
	console.log("TEConnectionPointPreceeding.handleEvent()", event.type);
	switch (event.type) {
		case "mousedown" : this.handleMouseDown(event); break;
		case "mouseup" : this.handleMouseUp(event); break;
		case "mousedrag" : console.log("mousedrag"); this.handleMouseDrag(event); break;
	}
} 
*/
TEConnectionPointPreceeding.prototype.handleMouseDown = function(event) { // overload parent method
	//console.log("TEConnectionPointPreceeding");
	TEConnectionPoint.prototype.handleMouseDown.call(this, event); // call parent's method
	this.connect(); // overload
}
TEConnectionPointPreceeding.prototype.handleMouseUp = function(event) { // overload parent method
	//console.log("TEConnectionPointPreceeding.handleMouseUp()");
	TEConnectionPoint.prototype.handleMouseUp.call(this, event); // call parent's method
	this.connect(); // overload
}
TEConnectionPointPreceeding.prototype.handleMouseDrag = function(event) { // overload parent method
	//console.log("TEConnectionPointPreceeding.handleMouseDrag()");
	TEConnectionPoint.prototype.handleMouseDrag.call(this, event); // call parent's method
	this.connect(); // overload
}
TEConnectionPointPreceeding.prototype.markCircle = function() {
	TEConnectionPoint.prototype.markCircle.call(this); // call parent's method
	this.parent.markedCircle = this;
	this.parent.markedIndex = 0;
}
TEConnectionPointPreceeding.prototype.identify = function(item) {
	//console.log("TEConnectionPointPreceeding.identify() - circle vs item: ", this.circle, item);
	if (item == this.circle) return this;
	else return false;
}

// class TEConnectionPointFollowing extends TEConnectionPoint
function TEConnectionPointFollowing(drawingArea, x, y) {
	TEConnectionPoint.call( this, drawingArea, x, y );
}
TEConnectionPointFollowing.prototype = new TEConnectionPoint(); //new TEConnectionPoint(TEConnectionPoint.prototype);
TEConnectionPointFollowing.prototype.connect = function() {
/*	if (this.parent.fhCircleList.length != 0) {
		//console.log(this.parent.fhCircleList[0].position);
		var exitPoint = this.parent.fhCircleList[this.parent.fhCircleList.length-1];
		this.line.segments[0].point = this.circle.position;
		this.line.segments[1].point = exitPoint.position;
		//console.log(this.line);
	}*/
}
TEConnectionPointFollowing.prototype.handleMouseDown = function(event) { // overload parent method
	TEConnectionPoint.prototype.handleMouseDown.call(this, event); // call parent's method
	this.connect(); // overload
}
TEConnectionPointFollowing.prototype.handleMouseUp = function(event) { // overload parent method
	TEConnectionPoint.prototype.handleMouseUp.call(this, event); // call parent's method
	this.connect(); // overload
}
TEConnectionPointFollowing.prototype.handleMouseDrag = function(event) { // overload parent method
	TEConnectionPoint.prototype.handleMouseDrag.call(this, event); // call parent's method
	this.connect(); // overload
}
TEConnectionPointFollowing.prototype.markCircle = function() {
	TEConnectionPoint.prototype.markCircle.call(this); // call parent's method
	this.parent.markedCircle = this;
	this.parent.markedIndex = 99999;
}
TEConnectionPointFollowing.prototype.identify = function(item) {
	//console.log("TEConnectionPointFollowing.identify()");
	if (item == this.circle) return this;
	else return false;
}


// class TEPointLabel
function TEKnotLabel(drawingArea) {
	this.parent = drawingArea;
	// coordinates
	this.coordinates = new PointText(new Point(100, 100));
	this.coordinates.justification = "center";
	this.coordinates.fillcolor = '#000';
	this.coordinates.content = 'empty coordinates';
	this.coordinates.visible = false;
	
	// tensions
	this.tensions = new PointText(new Point(100, 120));
	this.tensions.justification = "center";
	this.tensions.fillcolor = '#000';
	this.tensions.content = 'empty tensions';
	this.tensions.visible = false;
	
		//console.log("posxy: ", posX, this.parent.lowerY+20);
		//text.justification = 'right';
		//text.fillColor = '#000';
}
TEKnotLabel.prototype.updateLabel = function() { // TEVisuallyModifiableKnot
	//this.parent.markedCircle;
	var drawingAreaObject = this.parent.getTEDrawingAreaObject(this.parent.markedCircle);
	//console.log("markedCircle: ", this.parent.markedCircle, " drawingAreaObject: ", drawingAreaObject, " parent.editableToken: ", this.parent.editableToken);
	if (drawingAreaObject == this.parent.editableToken) {
		var valuesXY = this.parent.markedCircle.circle.position,
			valuesT = this.parent.markedCircle.tensions
			rescaledX = ((this.parent.rotatingAxis.centerRotatingAxis.x - valuesXY.x) / this.parent.scaleFactor).toFixed(1),
			rescaledY = (-(this.parent.rotatingAxis.centerRotatingAxis.y - valuesXY.y) / this.parent.scaleFactor).toFixed(1);
			
		this.coordinates.position = valuesXY - [0,12];	
		this.coordinates.content = "P(" + rescaledX + "," + rescaledY + ")";
		this.tensions.content = "T(" + valuesT[0] + "," + valuesT[1] + ")";
		this.tensions.position = valuesXY + [0,12];
		//console.log("coordinates: ", this.coordinates.content, " tensions: ", this.tensions.content);
		this.coordinates.visible = true;
		this.tensions.visible = true;
	} else {
		this.tensions.visible = false;
		this.coordinates.visible = false;
	}
	//console.log("Show label");
}
