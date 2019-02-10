
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
	//console.log("positionAfterBackgroundElements: ", this.positionAfterBackgroundElements);
	
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
TEDrawingArea.prototype.showSelectedKnotSE1Type = function() {
	if ((actualFont.version != undefined) && (actualFont.version == "SE1")) {
		//console.log("selectedKnot: ", this.editableToken.selectedKnot, "Index: ", this.editableToken.index);
		if (this.editableToken.selectedKnot != undefined) { // check if at least 1 knot exists
			// using method of TEKnotType (this is possible now that only data is copied from JSKnotType to TEKnotType and methods are preserved)
			var output = this.editableToken.selectedKnot.type.getKnotTypesAsString();
	//		console.log("this.editableToken.selectedKnot.type: ", this.editableToken.selectedKnot.type);
			
			/*
			var output = "SE1-Knottype:";
			with (this.editableToken.selectedKnot.type) {
				output += (entry) ? " entry" : "";
				output += (exit) ? " exit" : "";
				output += (pivot1) ? " pivot1" : "";
				output += (pivot2) ? " pivot2" : "";
				output += (lateEntry) ? " lateEntry" : "";
				output += (earlyExit) ? " earlyExit" : "";
				output += (combinationPoint) ? " combinationPoint" : "";
				output += (connect) ? " connect" : "";
				output += (intermediateShadow) ? " intermediateShadow" : "";
			}
			*/
			// impossible to call type method because type is copied from export_variable (php) and this variable only contains data, no code!
			// use code above instead
			//var type = this.editableToken.knotsList[this.editableToken.index-1].type;
			//console.log('check type...', type, typeof type); //, type.getKnotTypesAsString();
			//document.getElementById('se1_knottype').innerHTML = type.getKnotTypesAsString();
			document.getElementById('se1_knottype').innerHTML = output;
		}
	}
}
TEDrawingArea.prototype.moveRelativeSelectedKnot = function(x,y) {
	if (this.editableToken.selectedKnot != null) {
		this.editableToken.selectedKnot.moveRelative(x,y);
		this.updateFreehandPath();
	}
}
TEDrawingArea.prototype.updateFreehandPath = function() {
	if (this.editableToken.knotsList.length > 0) {
		this.copyKnotsToFreehandPath();
		this.calculateLeftRightVectors();
		this.calculateOuterShape();
		this.calculateFreehandHandles();
	}
	// draw path with variable width
	this.drawMiddlePathWithVariableWidth();
	// test: update se1-knottype (for se1)
	//if ((actualFont.version != undefined) && (actualFont.version == "SE1")) {
		//this.showSelectedKnotSE1Type();
	//} 
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
	//console.log("this.editableToken: ", this.editableToken);
	// now create new array width subdivided paths
	for (i=0; i<this.fhToken.segments.length-1; i++) {
		// copy part of the path
		middlePathWithVariableWidth[i] = new Path(this.fhToken.segments[i], this.fhToken.segments[i+1]);
		middlePathWithVariableWidth[i].strokeColor = '#000';
		// calculate width
		// sum up left and right distance and scale, use selected shape
		var actualShape = this.getSelectedShapeIndex();
		var width = (this.editableToken.leftVectors[actualShape][i].distance + this.editableToken.rightVectors[actualShape][i].distance) * this.scaleFactor; 
		//console.log("middle path: set width: ", width);
		if ((this.editableToken.knotsList[i+1] != undefined) && (this.editableToken.knotsList[i+1].type.connect == false)) {
			 //console.log("i+1= ", i+1, "=> don't connect");
			 width = 0;
			 //console.log("middle path: set width(correction): ", width);
			 //console.log("this.editableToken.knotsList[i+1].type: ", this.editableToken.knotsList[i+1].type);
		}
		//console.log("this.editableToken:i:width: ", this.editableToken, i, width);
		middlePathWithVariableWidth[i].strokeWidth = width;			// variable width: just assign growing i at this point for demonstration
		middlePathWithVariableWidth[i].visible = showMiddlePathWithVariableWidthBoolean;			// set visibility
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
	showMiddlePathWithVariableWidthBoolean = false;
	for (var i=0; i<middlePathWithVariableWidth.length; i++) {
		middlePathWithVariableWidth[i].visible = false;
	}
}
TEDrawingArea.prototype.showMiddlePathWithVariableWidth = function() {
	//console.log("show middle path: boolean: ", showMiddlePathWithVariableWidthBoolean);
	showMiddlePathWithVariableWidthBoolean = true;
	for (var i=0; i<middlePathWithVariableWidth.length; i++) {
		middlePathWithVariableWidth[i].visible = true;
		//console.log("i: ", i, "middlePathWithVariableWidth[i]: ", middlePathWithVariableWidth[i]); 
	}	
}
TEDrawingArea.prototype.isInsideBorders = function( event ) {
	if ((this.leftX <= event.point.x) && (this.rightX >= event.point.x) && (this.lowerY >= event.point.y) && (this.upperY <= event.point.y)) return true;
	else return false;
}
TEDrawingArea.prototype.toggleVisibilityMiddlePathWithVariableWidth = function() {
	//console.log("toggle visibility");
	switch (showMiddlePathWithVariableWidthBoolean) {
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
TEDrawingArea.prototype.loadAndInitializeTokenData = function(tokenKey) {
	var token = actualFont.tokenList[tokenKey];
	console.log("actualfont: ", actualFont);
	//console.log("loadAndInitializeTokenData(): token: ", token);
	this.editableToken.deleteAllKnotData();
	// delete main object
	this.editableToken = null;
	// create new object
	this.editableToken = new TEEditableToken(this);
	// copy data
	this.editableToken.header = token.header.slice(); // ?
	//console.log("tokenData: ", token, token.tokenData.length);
	//console.log("load token: token:", token);
	
	knotTypeAutoDefine = false; // disable autodefine for knots
	for (var i=0; i<token.tokenData.length; i++) {
		// insert knots and stuff
		//console.log("tokenData: i: ", i, token.tokenData[i]);
		var x, y;
		
		if (token.tokenData[i].calcType == "horizontal") {
			x = (token.tokenData[i].vector1 * this.scaleFactor) + this.rotatingAxis.centerRotatingAxis.x;
			y =	this.rotatingAxis.centerRotatingAxis.y - (token.tokenData[i].vector2 * this.scaleFactor);
		} else { // swap vectors for proportional and orthogonal knots
			x = (token.tokenData[i].vector2 * this.scaleFactor) + this.rotatingAxis.centerRotatingAxis.x;
			y =	this.rotatingAxis.centerRotatingAxis.y - (token.tokenData[i].vector1 * this.scaleFactor);
		}
		
		this.fhToken.insert(this.editableToken.index, new Point(x,y))
		this.editableToken.insertNewKnotFromActualFont(new Point(x, y), token.tokenData[i].knotType);
		
		// set tensions
		this.editableToken.knotsList[i].tensions = token.tokenData[i].tensions.slice(); // copy entire array(6) use slice!!! // direct reference or copy (what is better?)
		
		this.editableToken.knotsList.type = new TEKnotType();
		
		//console.log("this.editableToken.knotsList[i].type: ", this.editableToken.knotsList[i].type);
		//console.log("token.tokenData[i].knotType: ", token.tokenData[i].knotType);
		
		// ok, the following line is completely wrong!
		// by copying knotType from token.tokenData.knotType (which comes from actualFont), we are replacing TEKnotType by JSKnotType (from PHP export, i.e. $export_variable)
		// since $export_variable only contains data (= no methods), all editor functions are lost! 
		//this.editableToken.knotsList[i].type = token.tokenData[i].knotType;	// direct reference or copy (what is better?!)
		// SOLUTION: keep TEKnotType and copy only the properties from JSKnotType to TEKnotType!
		this.editableToken.knotsList[i].type.entry = token.tokenData[i].knotType.entry;
		this.editableToken.knotsList[i].type.exit = token.tokenData[i].knotType.exit;
		this.editableToken.knotsList[i].type.pivot1 = token.tokenData[i].knotType.pivot1;
		this.editableToken.knotsList[i].type.pivot2 = token.tokenData[i].knotType.pivot2;
		this.editableToken.knotsList[i].type.earlyExit = token.tokenData[i].knotType.earlyExit;
		this.editableToken.knotsList[i].type.lateEntry = token.tokenData[i].knotType.lateEntry;
		this.editableToken.knotsList[i].type.combinationPoint = token.tokenData[i].knotType.combinationPoint;
		this.editableToken.knotsList[i].type.connect = token.tokenData[i].knotType.connect;
		this.editableToken.knotsList[i].type.intermediateShadow = token.tokenData[i].knotType.intermediateShadow;
		
		
		//mainCanvas.editor.editableToken.setKnotType("proportional");
		this.editableToken.setKnotType(token.tokenData[i].calcType); // do this in editor data?!? => sets knot type correctly but changes coordinates!
		// here's the dilemma: if knot type is not set, shapes are wrong (coordinates are correct)
		// if knot tpye is set, shapes are correct but coordinates are correct
	
		// **************************** fix that **********************************
		/*
		console.log("hithere1");
		this.editableToken.knotsList[i].changeKnotShapeAccordingToType(token.tokenData[i].calcType); // use these methods to change shape
		this.editableToken.knotsList[i].linkToRelativeKnot.type = token.tokenData[i].calcType;
		console.log("hithere2");
		*/
		
		/*
		this.editableToken.knotsList[i].linkToRelativeKnot.type = token.tokenData[i].calcType;
		//mainCanvas.editor.editableToken.knotsList[i].linkToRelativeKnot.rd1 = token.tokenData[i].vector1;		// has been inserted via insertNewKnot()
		//mainCanvas.editor.editableToken.knotsList[i].linkToRelativeKnot.rd2 = token.tokenData[i].vector2;		// has been inserted via insertNewKnot()
		// if calcType is proportional or orthogonal vectors have to be swapped (vector1 = vector2 and viceversa)
	*//*
		//console.log("token.tokenData[i]: ", token.tokenData[i]);
		if ((token.tokenData[i].calcType == "orthogonal") || (token.tokenData[i].calcType == "proportional")) { // swap vectors in relative knot
			var tempRD = this.editableToken.knotsList[i].linkToRelativeKnot.rd1;
			this.editableToken.knotsList[i].linkToRelativeKnot.rd1 = this.editableToken.knotsList[i].linkToRelativeKnot.rd2;
			this.editableToken.knotsList[i].linkToRelativeKnot.rd2 = tempRD;
			//this.editableToken.knotsList[i].parallelRotatingAxisType = token.tokenData[i].calcType; // set correct rotatingAxisType (use "orthogonal" for the moment)
		}
	*/
		if ((token.tokenData[i].calcType == "orthogonal") || (token.tokenData[i].calcType == "proportional")) { // subtract shiftX in editableToken for correct vectors
			console.log("Subtract shiftX");
			this.editableToken.knotsList[i].linkToRelativeKnot.rd2 -= token.tokenData[i].shiftX;
			// parallelRotatingAxisType will be defined in loadAndInitializeEditorData
			//this.editableToken.knotsList[i].parallelRotatingAxisType = "horizontal"; //token.tokenData[i].calcType; // set correct rotatingAxisType (use "horizontal" for the moment)
		}
		
		console.log("i: ", i, "token.tokenData: ", token.tokenData, "set shiftX/Y: ", token.tokenData[i].shiftX, token.tokenData[i].shiftY);
		this.editableToken.knotsList[i].shiftX = token.tokenData[i].shiftX;
		this.editableToken.knotsList[i].shiftY = token.tokenData[i].shiftY;
		// shiftX/Y must be inserted manually in linkToParallelRotatingAxis too
		console.log("linkToParallelRotatingAxis: ", this.editableToken.knotsList[i].linkToParallelRotatingAxis);
		//this.editableToken.knotsList[i].linkToParallelRotatingAxis.shiftX = token.tokenData[i].shiftX;
		//this.editableToken.knotsList[i].linkToParallelRotatingAxis.shiftY = token.tokenData[i].shiftY;
		
		//mainCanvas.editor.editableToken.knotsList[i].tensions = token.tokenData[i].tensions; // done above
		
		// copy thicknesses
		this.editableToken.leftVectors[0][i].distance = token.tokenData[i].thickness.standard.left				
		this.editableToken.rightVectors[0][i].distance = token.tokenData[i].thickness.standard.right;
		this.editableToken.leftVectors[1][i].distance = token.tokenData[i].thickness.shadowed.left;
		this.editableToken.rightVectors[1][i].distance = token.tokenData[i].thickness.shadowed.right;		
	}
	// load editor data => can't be called from here!
	//this.loadAndInitializeEditorData(token.editorData);
	
	//console.log("fhToken: ", mainCanvas.editor.fhToken);
	this.updateFreehandPath();
	this.parent.thicknessSliders.updateLabels(); // well, this is getting very messy ... call this updateFunction to set visibility of OuterShape at the same time ...
	
	// update header fields in HTML
	//console.log("header: ", mainCanvas.editor.editableToken.header);
	this.editableToken.copyHeaderArrayToTextFields();
	this.showSelectedKnotSE1Type();
	
	
	console.log("mainCanvas.editor: ", mainCanvas.editor);
	//console.log("parallelRotatingAxisType: ", mainCanvas.editor.editableToken.knotsList[8].parallelRotatingAxisType);
}
TEDrawingArea.prototype.loadAndInitializeEditorData = function(tokenKey) {
	var editorData = actualFont.editorData[tokenKey];
	// set standard parameters for editor in order to insert data
	console.log("initialize editor data: token.editorData", editorData, this.rotatingAxis);
	// set rotatingAxis to horizontal (makes it easier: relativ coordinates can be used as absolute coordinates)
	//this.rotatingAxis.controlCircle.circle.position.x = this.rotatingAxis.centerRotatingAxis.x;
	//this.rotatingAxis.controlCircle.circle.position.y = this.upperY; 
	
	
//this.rotatingAxis.setRotatingAxisManually(new Point(this.rotatingAxis.centerRotatingAxis.x, this.upperY));
	
	
	//console.log("test1: ",this.rotatingAxis);
	//var tmp = new TEParallelRotatingAxisGrouper(this.rotatingAxis); 
	//this.rotatingAxis.parallelRotatingAxis = tmp; //new TEParallelRotatingAxisGrouper(this.rotatingAxis); // install main axis
	//console.log("test2: ",tmp);
	
	// copy parallel rotating axis
	//console.log("editor: ", editor);
	
	// create parallel rotating axis grouper
	this.rotatingAxis.parallelRotatingAxis = new TEParallelRotatingAxisGrouper(this.rotatingAxis);
	for (var i=0; i<editorData.rotatingAxisList.length; i++) {
			console.log("add axis: ", editorData.rotatingAxisList[i]);
			this.rotatingAxis.parallelRotatingAxis.addParallelAxisWithoutDialog(editorData.rotatingAxisList[i]);
			//tmp.addParallelAxisWithoutDialog(editor.rotatingAxisList[i]);
			//console.log("i:tmp: ", i, tmp);
	}
	//console.log("test3: ",tmp);
	console.log("drawingArea: ", this);
	this.rotatingAxis.parallelRotatingAxis.updateAll(); // update all rotating axis (including main)
	//console.log("loadAndInitializeEditorData()");

	console.log("this.rotatingAxis: ", this.rotatingAxis);
	// now that knots and parallelRotatingAxis are installed, knot types must be set and knots linked to parallelRotingAxis
	var tokenData = actualFont.tokenList[tokenKey].tokenData;
	console.log("tokenData: ", tokenData);
	for (var i=0;i<tokenData.length; i++) {
		//this.editableToken.knotsList[i].parallelRotatingAxisType = "test";
		var testX = tokenData[i].shiftX;
		var tempL = this.rotatingAxis.parallelRotatingAxis.getLinkToParallelRotatingAxisViaShiftX(testX); 
		//console.log("i: ", i, "editableToken.knotsList[i].parallelRotatingAxisType BEFORE: ", editableToken.knotsList[i].parallelRotatingAxisType);
		//var test = this.editableToken.knotsList[i].parallelRotatingAxisType; // = "test";
		//this.editableToken.setKnotType(token.tokenData[i].calcType);
		console.log("this.editableToken.knotsList", this.editableToken.knotsList);
		console.log("tokenData: ", tokenData);
		//var tempType = (tokenData[i].calcType == "horizontal") ? "horizontal" : "orthogonal";
		var tempType = "horizontal"; // use only horizontal type for the moment!
		this.editableToken.knotsList[i].parallelRotatingAxisType = tempType;
		this.editableToken.knotsList[i].linkToParallelRotatingAxis = tempL;
		
		//console.log("editableToken.knotsList[i].parallelRotatingAxisType AFTER: ", editableToken.knotsList[i].parallelRotatingAxisType);
		//console.log("knot(i): ", i, "linkToParallelRotatingAxis: ", tempL);
		console.log("i: ", i, "this: ", this, "testX: ", testX, "tempL: ", tempL);
	}
	//console.log("END EDITOR DATA: parallelRotatingAxisType: ", mainCanvas.editor.editableToken.knotsList[8].parallelRotatingAxisType);
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
