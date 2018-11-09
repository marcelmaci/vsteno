
// class TEConnectionPoint
function TEConnectionPoint(drawingArea, x, y ) {
	this.parent = drawingArea;
	this.tempColor = '#000';
	//var position = new Point(x, y);
	this.circle = new Path.Circle(new Point(x, y), 5);
	this.circle.fillColor = '#000';
	this.line = this.line = new Path.Line( this.position, this.position ); // initialize line as point inside circle
	this.line.strokeColor = '#000';
}
TEConnectionPoint.prototype.handleMouseDown = function(event) {
	//console.log("TEConnectionPoint.handleMouseDown");	
	this.tempColor = this.circle.fillColor;
	this.circle.fillColor = "#aaa";
	this.circle.position = event.point;
	this.markCircle();
}
TEConnectionPoint.prototype.handleMouseUp = function(event) {
	//console.log("rotatingAxis.mouseup");
	if ((event.point.x >= this.parent.leftX) && (event.point.x < this.parent.rightX) && (event.point.y < this.parent.lowerY) && (event.point.y > this.parent.upperY)) {
		this.circle.position = event.point;
		this.circle.fillColor = this.tempColor;
		this.parent.itemSelected = this.parent;
		this.parent.fhCircleSelected = null;
	} else {
		// just "release" connecting point (and leave point as it is)
		this.circle.fillColor = this.tempColor; 
		//if (this.circle.fillColor == null) this.controlCircle.fillColor = "#ff0"; // workaround for the moment ... mark circle as yellow when the error occurs
		this.parent.itemSelected = this.parent;
		this.parent.fhCircleSelected = null;	
	}
}
TEConnectionPoint.prototype.handleMouseDrag = function(event) {
	//console.log("rotatingAxis.mousedrag");
	//console.log(this);
	if ((event.point.x >= this.parent.leftX) && (event.point.x < this.parent.rightX) && (event.point.y < this.parent.lowerY) && (event.point.y > this.parent.upperY)) {
		this.circle.position = event.point;
	}
}
TEConnectionPoint.prototype.handleEvent = function( event ) {
	//console.log("rotatingAxis.handleEvent()");
	switch (event.type) {
		case "mousedown" : this.handleMouseDown(event); break;
		case "mouseup" : this.handleMouseUp(event); break;
		case "mousedrag" : this.handleMouseDrag(event); break;
	}
	//this.controlCircle.position = event.point;
	//return;
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
	if (this.parent.fhCircleList.length != 0) {
		//console.log(this.parent.fhCircleList[0].position);
		var entryPoint = this.parent.fhCircleList[0];
		this.line.segments[0].point = this.circle.position;
		this.line.segments[1].point = entryPoint.position;
		//console.log(this.line);
	}
}
TEConnectionPointPreceeding.prototype.handleMouseDown = function(event) { // overload parent method
	//console.log("TEConnectionPointPreceeding");
	TEConnectionPoint.prototype.handleMouseDown.call(this, event); // call parent's method
	this.connect(); // overload
}
TEConnectionPointPreceeding.prototype.handleMouseUp = function(event) { // overload parent method
	//console.log("TEConnectionPointPreceeding");
	TEConnectionPoint.prototype.handleMouseUp.call(this, event); // call parent's method
	this.connect(); // overload
}
TEConnectionPointPreceeding.prototype.handleMouseDrag = function(event) { // overload parent method
	//console.log("TEConnectionPointPreceeding");
	TEConnectionPoint.prototype.handleMouseDrag.call(this, event); // call parent's method
	this.connect(); // overload
}
TEConnectionPointPreceeding.prototype.markCircle = function() {
	TEConnectionPoint.prototype.markCircle.call(this); // call parent's method
	this.parent.markedCircle = this;
	this.parent.markedIndex = 0;
}

// class TEConnectionPointFollowing extends TEConnectionPoint
function TEConnectionPointFollowing(drawingArea, x, y) {
	TEConnectionPoint.call( this, drawingArea, x, y );
}
TEConnectionPointFollowing.prototype = new TEConnectionPoint(); //new TEConnectionPoint(TEConnectionPoint.prototype);
TEConnectionPointFollowing.prototype.connect = function() {
	if (this.parent.fhCircleList.length != 0) {
		//console.log(this.parent.fhCircleList[0].position);
		var exitPoint = this.parent.fhCircleList[this.parent.fhCircleList.length-1];
		this.line.segments[0].point = this.circle.position;
		this.line.segments[1].point = exitPoint.position;
		//console.log(this.line);
	}
}
TEConnectionPointFollowing.prototype.handleMouseDown = function(event) { // overload parent method
	//console.log("TEConnectionPointPreceeding");
	TEConnectionPoint.prototype.handleMouseDown.call(this, event); // call parent's method
	this.connect(); // overload
}
TEConnectionPointFollowing.prototype.handleMouseUp = function(event) { // overload parent method
	//console.log("TEConnectionPointPreceeding");
	TEConnectionPoint.prototype.handleMouseUp.call(this, event); // call parent's method
	this.connect(); // overload
}
TEConnectionPointFollowing.prototype.handleMouseDrag = function(event) { // overload parent method
	//console.log("TEConnectionPointPreceeding");
	TEConnectionPoint.prototype.handleMouseDrag.call(this, event); // call parent's method
	this.connect(); // overload
}
TEConnectionPointFollowing.prototype.markCircle = function() {
	TEConnectionPoint.prototype.markCircle.call(this); // call parent's method
	this.parent.markedCircle = this;
	this.parent.markedIndex = 99999;
}
