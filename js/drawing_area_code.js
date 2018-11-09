
// class TEDrawingArea 	
// constructor and properties
function TEDrawingArea(lowerLeft, totalLines, basePosition, lineHeight, scaleFactor) {
	
	// console.log(lowerLeft, totalLines, basePosition, lineHeight, scaleFactor);
	// class properties
	// borders
	this.leftX = lowerLeft.x;
	this.rightX = lowerLeft.x + (totalLines * lineHeight * scaleFactor);
	this.upperY = lowerLeft.y - (totalLines * lineHeight * scaleFactor);
	this.lowerY = lowerLeft.y;
	this.upperLeft = new Point(this.leftX, this.upperY);
	this.lowerRight = new Point(this.rightX, this.lowerY);
	
	// other properties
	this.totalLines = totalLines;
	this.basePosition = basePosition;
	this.lineHeight = lineHeight;
	this.scaleFactor = scaleFactor;

	// insert graphical elements
	this.borders = new TEBorders(this, '#000');
	this.dottedGrid = new TEDottedGrid(this, '#000');
	this.auxiliarySystemLines = new TEAuxiliarySystemLines(this, '#000');
	this.auxiliaryVerticalLines = new TEAuxiliaryVerticalLines(this, '#000');
	this.rotatingAxis = new TERotatingAxis(this, '#0f0');
	this.coordinateLabels = new TECoordinatesLabels(this);
	this.preceeding = new TEConnectionPointPreceeding(this, this.leftX, this.rotatingAxis.centerRotatingAxis.y);
	this.following =  new TEConnectionPointFollowing(this, this.rightX, this.rotatingAxis.centerRotatingAxis.y);
	
	// actual selected items
	this.itemSelected = this;		// main item selected (parent object), can be TERotatingAxis e.g.
	this.markedCircle = null;
	this.markedIndex = 0;			// 0 = preceeding connection point; 1,2,3 ... n = freehand circles; 99999 = following connection point
	
	// freehand path
	this.fhCircleSelected = null;
	this.fhCircleColor = null;
	this.fhToken = new Path();
	this.fhToken.strokeColor = '#000';
	this.fhCircleList = [];

	return this;
}
// class TEDrawingArea: methods
TEDrawingArea.prototype.calculateFreehandHandles = function() { // a = TEDrawingArea
	// console.log(this.fhToken);
	numberOfPoints = this.fhToken.segments.length;
	for (var i = 1; i < numberOfPoints-1; i++) { // dont calculate 1st and last
			var absHandles = getControlPoints( this.fhToken.segments[i-1].point, this.fhToken.segments[i].point, this.fhToken.segments[i+1].point, 0.5 );
			this.fhToken.segments[i].handleIn = absHandles[0] - this.fhToken.segments[i].point;
			this.fhToken.segments[i].handleOut = absHandles[1] - this.fhToken.segments[i].point;
	}
}
TEDrawingArea.prototype.isPartOfFreehand = function(test) {
	return this.whichCircle(test);
}
TEDrawingArea.prototype.whichCircle = function(circle) {
	var index = null, i = 0;
	for (i = 0; i<this.fhCircleList.length; i++) {
		if (this.fhCircleList[i] == circle) {
			index = i;
			//console.log("Match for circle: fhCircleList[" + i + "] = " + this.fhCircleList[i] + "<=?=>" + circle + "=> " + index);
			break;
		} //else console.log("search for circle: fhCircleList[" + i + "] = " + this.fhCircleList[i] + "<=?=>" + circle + "=> " + index);		    
	}
	return index;
}
TEDrawingArea.prototype.isInsideBorders = function( event ) {
	if ((this.leftX <= event.point.x) && (this.rightX >= event.point.x) && (this.lowerY >= event.point.y) && (this.upperY <= event.point.y)) return true;
	else return false;
}
TEDrawingArea.prototype.isPartOfFreehandOrRotatingAxis = function( item ) {
	if ((this.isPartOfFreehand(item) != null) || (item == this.rotatingAxis.controlCircle)) return true;
	else return false;
}
TEDrawingArea.prototype.isDragableCircle = function(item) {
	if ((this.isPartOfFreehandOrRotatingAxis(item)) || (item == this.preceeding ) || (item = this.following)) return true;
	else return false;
}
TEDrawingArea.prototype.markFreehandCircle = function(circle) {
	//this.unmarkFreehandCircle();
	this.markedCircle = circle;
	this.markedIndex = this.whichCircle(this.markedCircle);
	this.markedCircle.strokeColor = '#00f';
	this.markedCircle.strokeWidth = 2;
}
TEDrawingArea.prototype.unmarkFreehandCircle = function() {
	console.log("Unmark circle", this.markedCircle);
	if (this.markedCircle != null) {
		if (this.markedCircle.circle == undefined) this.markedCircle.strokeWidth = 0;		// freehand circle should be defined as an object also ...
		else this.markedCircle.unmarkCircle();		
	}
}
TEDrawingArea.prototype.handleMouseDown = function( event ) {
	//console.log("In onMouseDown");
	this.unmarkFreehandCircle();
	if ((event.item != null) && (this.isPartOfFreehandOrRotatingAxis(event.item))) { //(this.fhCircleSelected))) {
		this.fhCircleSelected = event.item;
		this.fhCircleColor = this.fhCircleSelected.fillColor;
		this.fhCircleSelected.fillColor = '#aaa';
		this.markFreehandCircle(this.fhCircleSelected);		
	} else {
		var path = new Path.Circle( {
				center: event.point,
				radius: 5,
				fillColor: '#f00'	
			});
		this.fhCircleList.push( path );
		//console.log(this.fhCircleList);
		this.fhCircleSelected = path;
		this.fhCircleColor = this.fhCircleSelected.fillColor;
		// add token data (relative to rotating axis)
		this.rotatingAxis.token.middle.push( new TERotatingAxisTokenPoint( event.point, 0.5, 0.5, "horizontal", this.rotatingAxis ));
		//console.log("Editor: ", this);
		// add bezier to freehand path
		this.fhToken.add( event.point );
		this.calculateFreehandHandles();
		this.markFreehandCircle(this.fhCircleSelected);	
	}
	this.preceeding.connect();
	this.following.connect();
}
TEDrawingArea.prototype.handleMouseUp = function( event ) {
	//console.log("In onMouseUp");
	if (this.fhCircleSelected != null) {
		this.fhCircleSelected.fillColor = this.fhCircleColor;
		this.fhCircleSelected = null;
		this.itemSelected = this;
	}
	//console.log(this);
}
TEDrawingArea.prototype.handleMouseDrag = function( event ) {
	//console.log("In onMouseDrag");
	if (editor.fhCircleSelected != null) {
		index = this.whichCircle( this.fhCircleSelected );
		this.fhCircleSelected.position = event.point; 
		this.fhToken.segments[index].point = this.fhCircleSelected.position;
		// update token data
		this.rotatingAxis.token.middle[index].absolute = event.point;
		this.rotatingAxis.token.middle[index].calculateRelativeCoordinates();
		this.itemSelected = this;
		
		this.calculateFreehandHandles();
		this.preceeding.connect(); // update connecting point also
		this.following.connect(); // update connecting point also
	}
}
TEDrawingArea.prototype.handleEvent = function( event ) {
	//console.log("TEDrawingArea.handleEvent()");
	//console.log(event.item);
	//if ((this.fhCircleSelected == null) && (event.item != null) && (this.isPartOfFreehandOrRotatingAxis(event.item))) {
	if ((this.fhCircleSelected == null) && (event.item != null) && (this.isDragableCircle(event.item))) {
		switch (event.item) {
			case this.rotatingAxis.controlCircle : this.itemSelected = this.rotatingAxis; break;
			case this.preceeding.circle : this.itemSelected = this.preceeding; break;
			case this.following.circle : this.itemSelected = this.following; break;
			default : this.itemSelected = this;
		}
		//this.itemSelected = (event.item == this.rotatingAxis.controlCircle) ? this.rotatingAxis : this;
		this.fhCircleSelected = event.item;	
	}
	
	if ((this.isInsideBorders(event)) || (event.type == "mouseup")) { 
		//console.log("Ok, it's my business");
		switch (event.type) {
			case "mousedown" :this.itemSelected.handleMouseDown(event); break;
			case "mouseup" : this.itemSelected.handleMouseUp(event); break;
			case "mousedrag" : this.itemSelected.handleMouseDrag(event); break;
		}
	} else {
		//console.log("Thx, but it's not my business");
	}
}
