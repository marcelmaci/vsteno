
// class TEDrawingArea 	
function TEDrawingArea(parent, lowerLeft, totalLines, basePosition, lineHeight, scaleFactor) {
	// parent
	this.parent = parent;	// TECanvas
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
	this.coordinateLabels = new TECoordinatesLabels(this); // coordinateLabels depends on rotatingAxis!
	this.preceeding = new TEConnectionPointPreceeding(this, this.leftX+10, this.rotatingAxis.centerRotatingAxis.y);
	this.following =  new TEConnectionPointFollowing(this, this.rightX-10, this.rotatingAxis.centerRotatingAxis.y);
	this.knotLabel = new TEKnotLabel(this);
	
	// mouse events
	//this.mouseDown = false;
	this.mouseDownItem = null;
	this.handlingParent = null;
	
	// token that is edited
	this.actualToken = new TEEditableToken();
	
// actual selected itemsModifiableCircle
	this.markedIndex = 0;			// 0 = preceeding connection point; 1,2,3 ... n = freehand circles; 99999 = following connection point

	// freehand path
	this.fhCircleSelected = null;
	this.fhCircleColor = null;
	this.editableToken = new TEEditableToken(this);
	this.fhToken = new Path();
	//this.fhToken.fullySelected = true;
	this.fhToken.strokeColor = '#000';

	// initialize marked circle and index
	// following line throws an error => why?!?!?!?!?
    this.setMarkedCircle(this.preceeding);
}

// class TEDrawingArea: methods
TEDrawingArea.prototype.setMarkedCircle = function(circle) { // type TEVisuallyModifiableCircle
	if (this.markedCircle != null) {
		this.markedCircle.unmark();
	}
	this.markedCircle = circle;
	this.markedCircle.mark();
	// set index
	if (this.markedCircle.identify() == false) {
		// marked circle is preceeding or following (because identify returns false?!)
		switch (this.markedCircle.circle) {
			case this.preceeding.circle : this.editableToken.index = 0; break;
			case this.following.circle : this.editableToken.index = this.editableToken.knotsList.length+1; break;
			// default is not needed: if this.markedCircle is part of editableToken, index is set automatically
			// via the identify method (which is called in the if statement)
		}
	} //else this
	//console.log("index set to: ", this.editableToken.index);
}
TEDrawingArea.prototype.calculateFreehandHandles = function() {
	numberOfPoints = this.fhToken.segments.length;
	//console.log("calculateFreehandHandles");
	// do first knot separately (add "virtual" 3rd knot at beginning which is identical with 1st knot)
	if (numberOfPoints > 1) { // only if there are at least 2 knots
		//console.log("calculate 1st");
		var t1 = this.editableToken.knotsList[0].tensions[0];
		var t2 = this.editableToken.knotsList[0].tensions[1];
		var absHandles = getControlPoints(this.fhToken.segments[0].point, this.fhToken.segments[0], this.fhToken.segments[1], t1, t2);
		this.fhToken.segments[0].handleIn = absHandles[0] - this.fhToken.segments[0].point;
		this.fhToken.segments[0].handleOut = absHandles[1] - this.fhToken.segments[0].point;
	}
	for (var i = 1; i < numberOfPoints-1; i++) { // dont calculate 1st and last
			var t1 = this.editableToken.knotsList[i].tensions[0];
			var t2 = this.editableToken.knotsList[i].tensions[1];
			var absHandles = getControlPoints( this.fhToken.segments[i-1].point, this.fhToken.segments[i].point, this.fhToken.segments[i+1].point, t1, t2 );
			this.fhToken.segments[i].handleIn = absHandles[0] - this.fhToken.segments[i].point;
			this.fhToken.segments[i].handleOut = absHandles[1] - this.fhToken.segments[i].point;
	}
	// do last knot separately (add "virtual" 3rd knot at end which is identical with last knot)
	if (numberOfPoints > 1) { // only if there are at least 2 knots
		//console.log("calculate last");
		var last = this.editableToken.knotsList.length-1;
		var t1 = this.editableToken.knotsList[last].tensions[0];
		var t2 = this.editableToken.knotsList[last].tensions[1];
		var absHandles = getControlPoints(this.fhToken.segments[last-1].point, this.fhToken.segments[last], this.fhToken.segments[last], t1, t2);
		this.fhToken.segments[last].handleIn = absHandles[0] - this.fhToken.segments[last].point;
		this.fhToken.segments[last].handleOut = absHandles[1] - this.fhToken.segments[last].point;
	}
}
TEDrawingArea.prototype.copyKnotsToFreehandPath = function() {
	for (var i=0; i<this.editableToken.knotsList.length; i++) {
			this.fhToken.segments[i].point = this.editableToken.knotsList[i].circle.position;
	}
}
TEDrawingArea.prototype.calculateLeftRightVectors = function() {
	// start with left vectors
	var length = this.editableToken.knotsList.length;
	for (var i=1; i<length-1; i++) {
		var tempPosition = this.editableToken.knotsList[i].circle.position;
		this.editableToken.leftVectors[i].line.removeSegments();
		// calculate vector and coordinates
		// get point and relative control point
		var p1 = this.editableToken.knotsList[i].circle.position;
		var rc1 = this.fhToken.segments[i].handleOut;
		// define vector
		var vectorX = rc1.x;
		var vectorY = rc1.y; 
		// turn by 90 degrees <=> swap x, y and negate x
		var tempX = vectorX;
		vectorX = vectorY;
		vectorY = tempX;
		vectorY = -vectorY;
		// normalize vector
		var vectorLength = Math.sqrt(vectorX * vectorX + vectorY * vectorY);	// squareLength
		vectorX = vectorX / Math.avoidDivisionBy0(vectorLength);
		vectorY = vectorY / Math.avoidDivisionBy0(vectorLength);
		// calculate new coordinates for left shape
		// vector endpoint
		var newLength = 20; // 10 pixels
		var endPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// vector startpoint (outside circle)
		newLength = 6;
		var startPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// draw left vector
		this.editableToken.leftVectors[i].line.add( startPoint, endPoint); //[newX,newY]);
		this.editableToken.leftVectors[i].line.strokeColor = '#000';
		this.editableToken.leftVectors[i].line.visible = true;
		// calculate new coordinates for right shape
		// flip vector by 180 degrees <=> negate x and y
		vectorY = -vectorY;
		vectorX = -vectorX;
		// vector endpoint
		newLength = 20; // 10 pixels
		endPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// vector startpoint (outside circle)
		newLength = 6;
		startPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// draw right vector
		this.editableToken.rightVectors[i].line.removeSegments();
		this.editableToken.rightVectors[i].line.add( startPoint, endPoint); //[newX,newY]);
		this.editableToken.rightVectors[i].line.strokeColor = '#000';
		this.editableToken.rightVectors[i].line.visible = true;
	}
}
TEDrawingArea.prototype.updateFreehandPath = function() {
	this.copyKnotsToFreehandPath();
	this.calculateLeftRightVectors();
	this.calculateFreehandHandles();
}
TEDrawingArea.prototype.isInsideBorders = function( event ) {
	if ((this.leftX <= event.point.x) && (this.rightX >= event.point.x) && (this.lowerY >= event.point.y) && (this.upperY <= event.point.y)) return true;
	else return false;
}
TEDrawingArea.prototype.handleMouseDown = function( event ) {
	///*this.*/mouseDown = true;
	this.mouseItem = event.item;
	this.handlingParent = this.getTEDrawingAreaObject(event.item);	
	if ((event.item != null) && (this.handlingParent != null)) {
		this.handlingParent.handleEvent(event);
		//if (doubleClick) console.log("This was a DOUBLECLICK;-)");
	} else {
		// at this point (since TEEditableToken.identify() has been called beforehand) index can be used to insert 
		// new knot at a specific point (i.e. after marked knot)
		//console.log("Insert new at: ", this.editableToken.index);
		
		this.fhToken.insert(this.editableToken.index, event.point) // path doesn't have slice method - use insert method instead (same functionality)
		//this.fhToken.add( event.point ); // add point at the end for the moment ...
		this.editableToken.insertNewKnot(event.point);
		//this.editableToken.index += 1; // point to the newly inserted element
		
		//var length = this.rotatingAxis.relativeToken.knotsList.length;
		//this.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, length);		
	}
	this.connectPreceedingAndFollowing();
	//this.preceeding.connect();
/*	this.following.connect();
*/
}
TEDrawingArea.prototype.handleMouseUp = function( event ) {
	if (this.handlingParent != null) {
		this.handlingParent.handleEvent(event);
	}
	///*this.*/mouseDown = false;
	this.mouseDownItem = null;
	this.handlingParent = null;
}
TEDrawingArea.prototype.handleMouseDrag = function( event ) {
	if (this.handlingParent != null) {
		this.handlingParent.handleEvent(event);	
		//var length = this.rotatingAxis.relativeToken.knotsList.length;
		//this.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, length);
		
	}
	this.connectPreceedingAndFollowing();
	
		//this.preceeding.connect(); // update connecting point also
/*		this.following.connect(); // update connecting point also
*/
}
TEDrawingArea.prototype.getTEDrawingAreaObject = function(item) {
	var value = this.preceeding.identify(item);
	if (!value) {
		value = this.following.identify(item);
		if (!value) {
			value = this.editableToken.identify(item);
			if (!value) {
				value = this.rotatingAxis.identify(item);
				if (!value) {
					value = null;
				}
			}
		}
	}
	return value;
}
TEDrawingArea.prototype.isDynamic = function(item) {
	
}
TEDrawingArea.prototype.isStatic = function(item) {
}

TEDrawingArea.prototype.handleEvent = function(event) {
	//console.log("TEDrawingArea.handleEvent()", event.item);
	if ((event.point.x >= this.leftX) && (event.point.x <= this.rightX) && (event.point.y >= this.upperY) && (event.point.y <= this.lowerY)) {	
		switch (event.type) {
			case "mousedown" : this.handleMouseDown(event); break;
			case "mouseup" : this.handleMouseUp(event); break;
			case "mousedrag" : this.handleMouseDrag(event); break;
		}
		//var index = this.rotatingAxis.relativeToken.index;
		//this.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, index);
		this.updateFreehandPath();
		this.knotLabel.updateLabel();
/*	
	if ((event.item != null) || (this.mouseItem != null)) {
		//console.log("GetTDrawingAreaObjet: ", this.getTEDrawingAreaObject(event.item));
		
		if (event.type == "mousedown") { 
			console.log("Handling parent: ", this.handlingParent);
			//console.log("mousedown => set variables");
			this.mouseDown = true;
			this.mouseItem = event.item;
			this.handlingParent = this.getTEDrawingAreaObject(event.item);
			//console.log("event.item: ", event.item);
			//console.log("Handling parent: ", this.handlingParent);
		} else if (event.type == "mouseup") {
			console.log("Handling parent: ", this.handlingParent);
			if (this.handlingParent != null) {
				this.handlingParent.handleEvent(event);
			}
			//console.log("mouseup => set variables");
			this.mouseDown = false;
			this.mouseDownItem = null;
			this.handlingParent = null;
		} 
		//console.log("Handling parent: ", this.handlingParent);
		//console.log("TEDrawingArea.mouseDown: ", this.mouseDown);
		//if (this.mouseDown) {	
			//console.log("Handle this event: ", this.mouseDownItem, "toType: ", toType(this.mouseDownItem));
			if (this.handlingParent != null) {
				//console.log("Handling parent: ", this.handlingParent);
				this.handlingParent.handleEvent(event);
			}
			
			//this.mouseDownItem.handleEvent(event);
			
			/*
			if ((this.fhCircleSelected == null) && (event.item != null) && (this.isDragableCircle(event.item))) {
				switch (event.item) {
					case this.rotatingAxis.controlCircle : this.itemSelected = this.rotatingAxis; break;
					case this.preceeding.circle : this.itemSelected = this.preceeding; break;
					case this.following.circle : this.itemSelected = this.following; break;
					default : this.itemSelected = this;
				}
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
			}*/
		//} else {
			//console.log("Don't react to mouse events if mouseDown == false");
		//}
/*	} else {
		if ((event.item != null) || (event.item.isStatic())) { // hoping that JS evaluates or expressions sequentially ... otherwise the second expression might throw and error ...
			console.log("Insert new point");
		
			this.editableToken.insertNewKnot(event.point);
		}
	}
*/
	}
}
TEDrawingArea.prototype.connectPreceedingAndFollowing = function() {
	this.preceeding.connect();
	this.following.connect();	
}

