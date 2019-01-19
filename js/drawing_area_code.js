
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
	this.rotatingAxis = new TERotatingAxis(this, '#0f0'); // colorMainRotatingAxisSelected);
	this.coordinateLabels = new TECoordinatesLabels(this); // coordinateLabels depends on rotatingAxis!
	
	// all background objects created => save children index in order to insert shapes here later!
	this.positionAfterBackgroundElements = project.activeLayer.children.length;
	console.log("positionAfterBackgroundElements: ", this.positionAfterBackgroundElements);
	
	this.preceeding = new TEConnectionPointPreceeding(this, this.leftX+10, this.rotatingAxis.centerRotatingAxis.y);
	this.following =  new TEConnectionPointFollowing(this, this.rightX-10, this.rotatingAxis.centerRotatingAxis.y);
	this.knotLabel = new TEKnotLabel(this);
	
	// mouse events
	//this.mouseDown = false;
	this.mouseDownItem = null;
	this.handlingParent = null;
	
	// token that is edited
	this.actualToken = new TEEditableToken(); // what's that?! Did I use that somewhere ... ?
	
	// actual selected itemsModifiableCircle
	this.markedIndex = 0;			// 0 = preceeding connection point; 1,2,3 ... n = freehand circles; 99999 = following connection point

	// freehand path
	this.fhCircleSelected = null;
	this.fhCircleColor = null;
	this.editableToken = new TEEditableToken(this);
	this.fhToken = new Path();
	this.fhToken.strokeColor = '#000';

	// initialize marked circle and index
	// following line throws an error => why?!?!?!?!?
    this.setMarkedCircle(this.preceeding);
    //this.rotatingAxis.parallelRotatingAxis.updateAll();
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
	}
}
TEDrawingArea.prototype.calculateFreehandHandles = function() {
	numberOfPoints = this.fhToken.segments.length;
	//console.log("calculateFreehandHandles");
	// do first knot separately (add "virtual" 3rd knot at beginning which is identical with 1st knot)
	if (numberOfPoints > 1) { // only if there are at least 2 knots
		//console.log("calculate 1st");
		var t1 = this.editableToken.knotsList[0].tensions[2];
		var t2 = this.editableToken.knotsList[0].tensions[3];
		var absHandles = getControlPoints(this.fhToken.segments[0].point, this.fhToken.segments[0], this.fhToken.segments[1], t1, t2);
		this.fhToken.segments[0].handleIn = absHandles[0] - this.fhToken.segments[0].point;
		this.fhToken.segments[0].handleOut = absHandles[1] - this.fhToken.segments[0].point;
	}
	for (var i = 1; i < numberOfPoints-1; i++) { // dont calculate 1st and last
			var t1 = this.editableToken.knotsList[i].tensions[2];
			var t2 = this.editableToken.knotsList[i].tensions[3];
			var absHandles = getControlPoints( this.fhToken.segments[i-1].point, this.fhToken.segments[i].point, this.fhToken.segments[i+1].point, t1, t2 );
			this.fhToken.segments[i].handleIn = absHandles[0] - this.fhToken.segments[i].point;
			this.fhToken.segments[i].handleOut = absHandles[1] - this.fhToken.segments[i].point;
	}
	// do last knot separately (add "virtual" 3rd knot at end which is identical with last knot)
	if (numberOfPoints > 1) { // only if there are at least 2 knots
		//console.log("calculate last");
		var last = this.editableToken.knotsList.length-1;
		var t1 = this.editableToken.knotsList[last].tensions[2];
		var t2 = this.editableToken.knotsList[last].tensions[3];
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
TEDrawingArea.prototype.getSelectedShapeIndex = function() {
	switch (selectedShape) {
		case "normal" : return 0; break;
		case "shadowed" : return 1; break;
	}
}
TEDrawingArea.prototype.calculateLeftRightVectors = function() {
	// define outer shape index
	var actualShape = this.getSelectedShapeIndex();
	// start with left vectors
	var length = this.editableToken.knotsList.length;
	for (var i=1; i<length-1; i++) {
		var tempPosition = this.editableToken.knotsList[i].circle.position;
		this.editableToken.leftVectors[actualShape][i].line.removeSegments();
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
		var newLength = this.editableToken.leftVectors[actualShape][i].distance * this.scaleFactor; //10; // 10 pixels
		var endPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// vector startpoint (outside circle)
		newLength = 6;
		var startPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// draw left vector
		this.editableToken.leftVectors[actualShape][i].line.add( startPoint, endPoint); //[newX,newY]);
		this.editableToken.leftVectors[actualShape][i].line.strokeColor = '#000';
		this.editableToken.leftVectors[actualShape][i].line.visible = true;
		// calculate new coordinates for right shape
		// flip vector by 180 degrees <=> negate x and y
		vectorY = -vectorY;
		vectorX = -vectorX;
		// vector endpoint
		newLength = this.editableToken.rightVectors[actualShape][i].distance * this.scaleFactor; //10; // 10 pixels
		endPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// vector startpoint (outside circle)
		newLength = 6;
		startPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// draw right vector
		this.editableToken.rightVectors[actualShape][i].line.removeSegments();
		this.editableToken.rightVectors[actualShape][i].line.add( startPoint, endPoint); //[newX,newY]);
		this.editableToken.rightVectors[actualShape][i].line.strokeColor = '#000';
		this.editableToken.rightVectors[actualShape][i].line.visible = true;
	}
}
TEDrawingArea.prototype.calculateOuterShapeHandles = function() {
	// define outer shape index
	var actualShape = this.getSelectedShapeIndex();
	// define length
	var length = this.editableToken.knotsList.length;
	// set control points of entry knot to (0,0)
	this.editableToken.outerShape[actualShape].segments[0].handleIn = 0;
	this.editableToken.outerShape[actualShape].segments[0].handleOut = 0;
	//console.log("Starting point: ", this.editableToken.outerShape.segments[0].point);
	// recalculate handles of left shape
	var p0, p1, p2, t1, t2, controlPoints, rc1, rc2;
	for (var i=1; i<length-1; i++) {
		// get 3 points for control point calculation (preceeding, actual, following)
		p0 = this.editableToken.outerShape[actualShape].segments[i-1].point;
		p1 = this.editableToken.leftVectors[actualShape][i].line.segments[1].point;
		p2 = this.editableToken.outerShape[actualShape].segments[i+1].point;
		// get tensions
		t1 = this.editableToken.knotsList[i].tensions[0];
		t2 = this.editableToken.knotsList[i].tensions[1];
		// calculate control points
		controlPoints = getControlPoints(p0, p1, p2, t1, t2);
		//console.log("getControlPoints: ", p0, p1, p2, t1, t2);
		// values are absolute => make them relative and copy them to segment
		rc1 = controlPoints[0]-p1;
		rc2 = controlPoints[1]-p1;
		this.editableToken.outerShape[actualShape].segments[i].handleIn = rc1;
		this.editableToken.outerShape[actualShape].segments[i].handleOut = rc2;
		//console.log("object: ",i,  this.editableToken.leftVectors[i].line.segments[1].point);
	}
	
	// set control points of exit knot to (0,0)
	this.editableToken.outerShape[actualShape].segments[length-1].handleIn = 0;
	this.editableToken.outerShape[actualShape].segments[length-1].handleOut = 0;
	
	// calculate right shape control points (backwards)
	//console.log("End point: ", this.editableToken.outerShape.segments[length-1].point);
	var continueIndex = length;
	for (var i=length-2; i>0; i--) {
		// get 3 points for control point calculation (preceeding, actual, following)
		p0 = this.editableToken.outerShape[actualShape].segments[continueIndex-1].point;  
		p1 = this.editableToken.outerShape[actualShape].segments[continueIndex].point;
		// modulo = wrap around at the end (lenght of outerShape = 2*length - 2, because start/end points are only inserted 1x)
		p2 = this.editableToken.outerShape[actualShape].segments[(continueIndex+1)%(2*length-2)].point;
		// get tensions
		t1 = this.editableToken.knotsList[i].tensions[5];	// tensions have to be inversed 
		t2 = this.editableToken.knotsList[i].tensions[4];	// due to backwards calculation
		// calculate control points
		controlPoints = getControlPoints(p0, p1, p2, t1, t2);
		//console.log("getControlPoints: ", continueIndex, p0, p1, p2, t1, t2);
		// values are absolute => make them relative and copy them to segment
		rc1 = controlPoints[0]-p1;
		rc2 = controlPoints[1]-p1;
		this.editableToken.outerShape[actualShape].segments[continueIndex].handleIn = rc1;
		this.editableToken.outerShape[actualShape].segments[continueIndex].handleOut = rc2;		
		continueIndex++;
	}
	// starting point control point values have already been written
}
TEDrawingArea.prototype.calculateOuterShape = function() {
	// define outer shape index
	var actualShape = this.getSelectedShapeIndex();
	// define length
	var length = this.editableToken.knotsList.length;
	this.editableToken.outerShape[actualShape].removeSegments();
	// add first segment = entry point
	this.editableToken.outerShape[actualShape].add(this.editableToken.knotsList[0].circle.position);
	// add points of left shape
	var tempPoint, handleIn, handleOut;
	for (var i=1; i<length-1; i++) {
		tempPoint = this.editableToken.leftVectors[actualShape][i].line.segments[1].point;
		handleIn = this.fhToken.segments[i].handleIn;	// not correct
		handleOut = this.fhToken.segments[i].handleOut;	// not correct
		this.editableToken.outerShape[actualShape].add(new Segment(tempPoint, handleIn, handleOut));
	//console.log("object: ",i,  this.editableToken.leftVectors[i].line.segments[1].point);
	}
	
	// add end point
	this.editableToken.outerShape[actualShape].add(this.editableToken.knotsList[length-1].circle.position);
	// add right shape backwards
	var tempPoint, handleIn, handleOut;
	for (var i=length-2; i>0; i--) {
		tempPoint = this.editableToken.rightVectors[actualShape][i].line.segments[1].point;
		// inverse handleIn / handleOut (since elements are inserted backwards)
		handleOut = this.fhToken.segments[i].handleIn; // not correct
		handleIn = this.fhToken.segments[i].handleOut; // not correct
		this.editableToken.outerShape[actualShape].add(new Segment(tempPoint, handleIn, handleOut));			
	}
	// no need to add starting point again => just close path
	this.editableToken.outerShape[actualShape].closePath();

	// set color
	this.editableToken.outerShape[actualShape].strokeColor = '#000';

	// it's not correct to copy the control points (handles) from the middle path,
	// outer paths are different (only tensions are equal)!
	// Therefore: take the TENSIONS of the middle path and recalculate the handles
	// Do this in 2 steps for the moment (in order to be able to compare differences,
	// later this calculation can be integrated inside the for-loops above
	this.calculateOuterShapeHandles();
}
TEDrawingArea.prototype.updateFreehandPath = function() {
	if (this.editableToken.knotsList.length > 0) {
		this.copyKnotsToFreehandPath();
		this.calculateLeftRightVectors();
		this.calculateOuterShape();
		this.calculateFreehandHandles();
	}
	// test: path with variable width
	this.drawMiddlePathWithVariableWidth();;
}
TEDrawingArea.prototype.drawMiddlePathWithVariableWidth = function() {
	// Implementation for SE2 takes a lot of time, so maybe make the editor
	// somehow compatible with SE1. In order to achieve that it must be possible
	// to variable the width along the middle path. Paper.js unfortunately doesn't
	// offer this function out of the box, so try the following workaround:
	// Take the given path (consisting of x elements/points), divide it into
	// x-1 subpaths (storing them in an array9 and assign a variable width for each of those 
	// subpaths.
	
	// first of all delete subdivided path (if it has been drawn before)
	for (var i=0; i<middlePathWithVariableWidth.length; i++) {
		middlePathWithVariableWidth[i].remove();	// delete subpath (erase from canvas)
	}
	middlePathWithVariableWidth.length = 0; // delete array
	console.log("this.editableToken: ", this.editableToken);
	// now create new array width subdivided paths
	for (i=0; i<this.fhToken.segments.length-1; i++) {
		// copy part of the path
		middlePathWithVariableWidth[i] = new Path(this.fhToken.segments[i], this.fhToken.segments[i+1]);
		middlePathWithVariableWidth[i].strokeColor = '#000';
		// calculate width
		// sum up left and right distance and scale, use selected shape
		var actualShape = this.getSelectedShapeIndex();
		var width = (this.editableToken.leftVectors[actualShape][i].distance + this.editableToken.rightVectors[actualShape][i].distance) * this.scaleFactor; 
		
		//console.log("this.editableToken:i:width: ", this.editableToken, i, width);
		middlePathWithVariableWidth[i].strokeWidth = width;			// variable width: just assign growing i at this point for demonstration
		middlePathWithVariableWidth[i].visible = showMiddlePathWithVariableWidth;			// set visibility
		project.activeLayer.insertChild(this.positionAfterBackgroundElements, middlePathWithVariableWidth[i]); // position middlePath after static background elements
		
		// code: disable one segment (nr. 2 in the example)
		/*
		if (i==2) {
			console.log("i = ", i);
			middlePathWithVariableWidth[i].strokeWidth = 0; // works for not connecting
			middlePathWithVariableWidth[i].visible = false; // disable one segment (for not connecting points) -- doesn't work?!
		}
		*/
	}
	
}
TEDrawingArea.prototype.hideMiddlePathWithVariableWidth = function() {
	//console.log("hide middle path");
	showMiddlePathWithVariableWidth = false;
	for (var i=0; i<middlePathWithVariableWidth.length; i++) {
		middlePathWithVariableWidth[i].visible = false;
	}
}
TEDrawingArea.prototype.showMiddlePathWithVariableWidth = function() {
	//console.log("show middle path");
	showMiddlePathWithVariableWidth = true;
	for (var i=0; i<middlePathWithVariableWidth.length; i++) {
		middlePathWithVariableWidth[i].visible = true;
	}	
}
TEDrawingArea.prototype.isInsideBorders = function( event ) {
	if ((this.leftX <= event.point.x) && (this.rightX >= event.point.x) && (this.lowerY >= event.point.y) && (this.upperY <= event.point.y)) return true;
	else return false;
}
TEDrawingArea.prototype.toggleVisibilityMiddlePathWithVariableWidth = function() {
	//console.log("toggle visibility");
	switch (showMiddlePathWithVariableWidth) {
		case true : this.hideMiddlePathWithVariableWidth(); break;
		case false : this.showMiddlePathWithVariableWidth(); break;
	}
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
		//console.log("insertNewKnot: ", event.point);
		this.editableToken.insertNewKnot(event.point);
	}
	this.connectPreceedingAndFollowing();
	// link thickness sliders
	this.parent.thicknessSliders.linkEditableToken(this.editableToken);
}
TEDrawingArea.prototype.handleMouseUp = function( event ) {
	//console.log("HandlingParent: ", this.handlingParent);
	if (this.handlingParent != null) {
		this.handlingParent.handleEvent(event);
	}
	this.mouseDownItem = null;
	this.handlingParent = null;
}
TEDrawingArea.prototype.handleMouseDrag = function( event ) {
	if (this.handlingParent != null) {
		this.handlingParent.handleEvent(event);	
	}
	this.connectPreceedingAndFollowing();
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
/* some remains from ancient times (Atlantis?:)
TEDrawingArea.prototype.isDynamic = function(item) {	
}
TEDrawingArea.prototype.isStatic = function(item) {
}
*/
TEDrawingArea.prototype.handleEvent = function(event) {
	//console.log("TEDrawingArea.handleEvent()", event);
	if ((event.point.x >= this.leftX) && (event.point.x <= this.rightX) && (event.point.y >= this.upperY) && (event.point.y <= this.lowerY)) {	
		switch (event.type) {
			case "mousedown" : this.handleMouseDown(event); break;
			case "mouseup" : this.handleMouseUp(event); break;
			case "mousedrag" : this.handleMouseDrag(event); break;
		}
		//console.log("TEDrawingArea.handleEvent1: selected/marked: ", this.parent.editor.editableToken.selectedKnot, this.parent.editor.editableToken.markedKnot);
		this.updateFreehandPath();
		this.knotLabel.updateLabel();
	}
}
TEDrawingArea.prototype.connectPreceedingAndFollowing = function() {
	this.preceeding.connect();
	this.following.connect();	
}
TEDrawingArea.prototype.loadAndInitializeTokenData = function(token) {
	console.log("actualfont: ", actualFont);
	//console.log("loadAndInitializeTokenData(): token: ", token);
	mainCanvas.editor.editableToken.deleteAllKnotData();
	// delete main object
	this.editableToken = null;
	// create new object
	this.editableToken = new TEEditableToken(this);
	// copy data
	this.editableToken.header = token.header.slice(); // ?
	//console.log("tokenData: ", token, token.tokenData.length);
	console.log("load token: token:", token);
		
	for (var i=0; i<token.tokenData.length; i++) {
		// insert knots and stuff
		//console.log("tokenData: i: ", i, token.tokenData[i]);
		var x = (token.tokenData[i].vector1 * this.scaleFactor) + this.rotatingAxis.centerRotatingAxis.x,
			y =	this.rotatingAxis.centerRotatingAxis.y - (token.tokenData[i].vector2 * this.scaleFactor);
		
		mainCanvas.editor.fhToken.insert(this.editableToken.index, new Point(x,y))
		mainCanvas.editor.editableToken.insertNewKnot(new Point( x, y));
		
		// set tensions
		mainCanvas.editor.editableToken.knotsList[i].tensions = token.tokenData[i].tensions.slice(); // copy entire array(6) use slice!!! // direct reference or copy (what is better?)
		
		 
		mainCanvas.editor.editableToken.knotsList[i].type = token.tokenData[i].knotType;	// direct reference or copy (what is better?!)
		mainCanvas.editor.editableToken.knotsList[i].linkToRelativeKnot.type = token.tokenData[i].calcType;
		//mainCanvas.editor.editableToken.knotsList[i].linkToRelativeKnot.rd1 = token.tokenData[i].vector1;		// has been inserted via insertNewKnot()
		//mainCanvas.editor.editableToken.knotsList[i].linkToRelativeKnot.rd2 = token.tokenData[i].vector2;		// has been inserted via insertNewKnot()
		mainCanvas.editor.editableToken.knotsList[i].shiftX = token.tokenData[i].shiftX;
		mainCanvas.editor.editableToken.knotsList[i].shiftY = token.tokenData[i].shiftY;
		//mainCanvas.editor.editableToken.knotsList[i].tensions = token.tokenData[i].tensions; // done above
		
		// copy thicknesses
		mainCanvas.editor.editableToken.leftVectors[0][i].distance = token.tokenData[i].thickness.standard.left				
		mainCanvas.editor.editableToken.rightVectors[0][i].distance = token.tokenData[i].thickness.standard.right;
		mainCanvas.editor.editableToken.leftVectors[1][i].distance = token.tokenData[i].thickness.shadowed.left;
		mainCanvas.editor.editableToken.rightVectors[1][i].distance = token.tokenData[i].thickness.shadowed.right;		
	}
	//console.log("fhToken: ", mainCanvas.editor.fhToken);
	mainCanvas.editor.updateFreehandPath();
	mainCanvas.thicknessSliders.updateLabels(); // well, this is getting very messy ... call this updateFunction to set visibility of OuterShape at the same time ...
	
	// update header fields in HTML
	//console.log("header: ", mainCanvas.editor.editableToken.header);
	mainCanvas.editor.editableToken.copyHeaderArrayToTextFields();
	
	
	
	console.log("mainCanvas.editor: ", mainCanvas.editor);
}
TEDrawingArea.prototype.loadAndInitializeEditorData = function(editor) {
	// set standard parameters for editor in order to insert data
	// set rotatingAxis to horizontal (makes it easier: relativ coordinates can be used as absolute coordinates)
	//this.rotatingAxis.controlCircle.circle.position.x = this.rotatingAxis.centerRotatingAxis.x;
	//this.rotatingAxis.controlCircle.circle.position.y = this.upperY; 
	this.rotatingAxis.setRotatingAxisManually(new Point(this.rotatingAxis.centerRotatingAxis.x, this.upperY));
	//console.log("test1: ",this.rotatingAxis);
	//var tmp = new TEParallelRotatingAxisGrouper(this.rotatingAxis); 
	//this.rotatingAxis.parallelRotatingAxis = tmp; //new TEParallelRotatingAxisGrouper(this.rotatingAxis); // install main axis
	//console.log("test2: ",tmp);
	
	// copy parallel rotating axis
	//console.log("editor: ", editor);
	for (var i=0; i<editor.rotatingAxisList.length; i++) {
			console.log("add axis: ", editor.rotatingAxisList[i]);
			this.rotatingAxis.parallelRotatingAxis.addParallelAxisWithoutDialog(editor.rotatingAxisList[i]);
			//tmp.addParallelAxisWithoutDialog(editor.rotatingAxisList[i]);
			//console.log("i:tmp: ", i, tmp);
	}
	//console.log("test3: ",tmp);
	console.log("drawingArea: ", this);
	this.rotatingAxis.parallelRotatingAxis.updateAll(); // update all rotating axis (including main)
	//console.log("loadAndInitializeEditorData()");
}
TEDrawingArea.prototype.cleanDrawingArea = function() {
	// clean drawing area and start editing a new token
	this.editableToken.deleteAllKnotData();
	// delete main object
	this.editableToken = null;
	// create new object
	this.editableToken = new TEEditableToken(this);
	
	/* nice try but didn't work ... couldn't get the deleteMarkedKnotFromArray() method to delete last knot => maybe there's a bug there ... !?
	for (var i=this.editableToken.knotsList.length-1; i>=0; i--) {
		console.log("editableToken: ", i, this.editableToken.knotsList);
		this.editableToken.index = this.editableToken.knotsList.length; // point index to length, so that deleteMarkedKnotFromArray will delete last element and set selected/markedKnot accordingly
		console.log("index: ", this.editableToken.index);
		if (this.editableToken.index == 1) this.editableToken.index = 0; // set index to 0 if it is the last (first of array) element to delete
		this.editableToken.deleteMarkedKnotFromArray();
	}
	this.editableToken.index = 0;
	*/
}
