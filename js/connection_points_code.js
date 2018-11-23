
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
	this.originalColor = this.circle.fillColor;
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
	//console.log("unlink sliders:", this.parent.parent.tensionSliders);
	this.parent.parent.tensionSliders.hideVerticalSliders();
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
/////// the following functions are experimental
/////// test new method for tangent calculation between to bezier curves
TEConnectionPointPreceeding.prototype.findTangentPointsBetweenCurves2And6 = function(epsilon) {
	// method to find the two points (both are variable!)
	// - choose a fix point on curve 1 (e.g at 50%) and calculate tangent relative to this point
	// - use calculate tangent point on curve 2 as new fix point and calculate tangent point on curve 1 relative to
	//   this point
	// - use calculated fix point on curve 1 as new fix point etc.
	// - continue calculation until m of preceedingly calculated tangent is < epsilon (tolerance)
	// - return array with following values: [tx1, ty1, tx2, ty2] (= two tangent points on both curves)
	var curve1LeftPercentage = 0.001,
		curve1MiddlePercentage = 50,
		curve1RightPercentage = 99.999,
		curve2LeftPercentage = 0.001,
		curve2MiddlePercentage = 50,
		curve2RightPercentage = 99.999;
	// set values for curve 1
	var curve1P1 = this.parent.editableToken.knotsList[1].circle.position,
		curve1C1 = curve1P1 + this.parent.fhToken.segments[1].handleOut,     // control points are RELATIVE coordinates
		curve1P2 = this.parent.editableToken.knotsList[2].circle.position,
		curve1C2 = curve1P2 + this.parent.fhToken.segments[2].handleIn;	
	// set values for curve 2
	var curve2P1 = this.parent.editableToken.knotsList[5].circle.position,
		curve2C1 = curve2P1 + this.parent.fhToken.segments[5].handleOut,     // control points are RELATIVE coordinates
		curve2P2 = this.parent.editableToken.knotsList[6].circle.position,
		curve2C2 = curve2P2 + this.parent.fhToken.segments[6].handleIn;	
	
	// start with fixpoints at 50% on both curves
	//var curve1ActualFixPoint = calculateBezierPoint(curve1P1, curve1C1, curve1P2, curve1C2, curve1MiddlePercentage); 
	//var curve2ActualFixPoint = calculateBezierPoint(curve2P1, curve2C1, curve2P2, curve2C2, curve2MiddlePercentage); 
	curve1ActualFixPoint = [curve1P1.x, curve1P1.y, 0];
	curve2ActualFixPoint = [curve2P2.x, curve2P2.y, 0];
	
	var numberIterations = 0;
	var curve2NewTangentPoint = undefined;
	var curve1NewTangentPoint = undefined;
	var curve1Distance = undefined;
	var curve2Distance = undefined;
	
	do {
		// calculate tangent point on curve2
		curve2NewTangentPoint = findTangentPointRelativeToFixPoint(new Point(curve1ActualFixPoint[0], curve1ActualFixPoint[1]), curve2P1, curve2C1, curve2P2, curve2C2, 0.00000001 ); // use global function
		curve2Distance = new Point(curve2NewTangentPoint[0], curve2NewTangentPoint[1]).getDistance(new Point(curve2ActualFixPoint[0], curve2ActualFixPoint[1]));
		
		// use tangent point on curve2 as new fix point and calculate tangent for curve1
		curve1NewTangentPoint = findTangentPointRelativeToFixPoint(new Point(curve2NewTangentPoint[0], curve2NewTangentPoint[1]), curve1P1, curve1C1, curve1P2, curve1C2, 0.00000001 );
		curve1Distance = new Point(curve1NewTangentPoint[0], curve1NewTangentPoint[1]).getDistance(new Point(curve1ActualFixPoint[0], curve1ActualFixPoint[1]));
				
		console.log("ActualFixPoints: ", curve1ActualFixPoint, curve2ActualFixPoint);
		console.log("NewFixPoints: ", curve1NewTangentPoint, curve2NewTangentPoint);
		
		curve1ActualFixPoint = curve1NewTangentPoint;
		curve2ActualFixPoint = curve2NewTangentPoint;
		
		// calculate epsilon
		actualEpsilon = curve1Distance + curve2Distance;
		//console.log("Iteration ", numberIterations, "Distances: ", curve1Distance, curve2Distance, "Epsilon: ", actualEpsilon);
		
		numberIterations++;
		
	} while (/*(actualEpsilon > epsilon) &&*/ (numberIterations < 10));
	
	// show result
	tangent.removeSegments();
	tangent.add(new Point(curve1ActualFixPoint[0], curve1ActualFixPoint[1]), new Point(curve2ActualFixPoint[0], curve2ActualFixPoint[1]));
	tangent.strokeColor = '#000';
	return true;
}
/////// test new method for tangent calculation with preceeding point
/////// test 2nd bezier segment for the moment
/////// (should be applied to following point and several segments later)
TEConnectionPointPreceeding.prototype.findTangentPointRelativeToConnectionPoint = function(epsilon) {
	var p1 = this.parent.editableToken.knotsList[1].circle.position,
		c1 = p1 + this.parent.fhToken.segments[1].handleOut,     // control points are RELATIVE coordinates
		p2 = this.parent.editableToken.knotsList[2].circle.position,
		c2 = p2 + this.parent.fhToken.segments[2].handleIn;	
	var cx = this.circle.position.x,
		cy = this.circle.position.y;
	return findTangentPointRelativeToFixPoint(new Point(cx, cy), p1, c1, p2, c2, epsilon);
	
	// known issues and caveats with following algorithm:
	// 1) when connecting point is exactly orthogonal to bezier curve, the function will return orthogonal point 
	//    => screenshot_tangentpoint_bug1a.jpb / ... bug1b.jpg
	// 2) when connecting point is "inside rectangle" the function will return the opposite tangent point (left
	//    or right => screenshot.tangentpoint_bug2a.jpg / ... bug2b.jpg
	// 3) vertical tangents are possible (screenshot_tangentpoint_bug3a), but horizontal tangents are NOT
	//    => screenshot_tangenpoint_bug3b.jpg / .. bug3c.jpg
	// there might also be erroneous results if tensions are 0 (in general 0 is a difficult number ... ;-)
	//
	// implications for VSTENO:
	// 1) and 2) maybe not really a problem?!
	// 3) unfortunately, there are many shorthand tokens that connect horizontally ... Keep this bug in mind and see
	// what happens.
	// POSSIBLE SOLUTION: define m as dy / dx (instead of dx / dy) => this will probably allow horizontal connections
	// (and produce false results for vertical connections which would have less impact on VSTENO
	// => keep it like that for the moment, change it later if necessary!
	//
	// FURTHER INVESTIGATIONS
	// "bugs" 1-3 seem to be related to precision: increasing precision (to epsilon = 0.00000005 instead of 0.1) 
	// and iterations (500 instead of 10) improves results significantly ("bug" 1 almost impossible to reproduce,
	// "bug" 3: nearly horizontal connections possible
	// => increase precision to the max (will have an impact on speed, of course ...)
	// => define a tolerance for horizontal connections when connecting tokens (and "round" values in drawing routine
	// later)
	//
	// DESCRIPTION OF ALGORITHM
	// define the 3 points:
	// - the middle one separates the bezier curve (or the actual segment of it) into two halves
	// - left and right points define start and end of the two segments (halves)
	// the points are defined as percentages (= relative location) on the bezier curve
	// epsilon stands for the precision: delta of straight lines going from connection point
	// to calculated tangent point should be < epsilon (numerical aproximation) 
/*	this.leftPercentage = 0.001;			// 0% <=> leftPoint
	this.rightPercentage = 99.999;		// 100% <=> rightPoint
	this.middlePercentage = 50;		// 50% <=> middlePoint
	var leftPoint = undefined,		// declare point variables
		rightPoint = undefined,
		middlePoint = undefined; 
	// for the moment use fix segment (2nd segment <=> indexes 1 and 2)
	var p1 = this.parent.editableToken.knotsList[1].circle.position,
		c1 = p1 + this.parent.fhToken.segments[1].handleOut,     // control points are RELATIVE coordinates
		p2 = this.parent.editableToken.knotsList[2].circle.position,
		c2 = p2 + this.parent.fhToken.segments[2].handleIn;	
	var avoidInfinityLoop = 0;
	do {
		//console.log("Starting loop number "+avoidInfinityLoop+"........................................");
		leftPoint = calculateBezierPoint(p1, c1, p2, c2, this.leftPercentage);
		middlePoint = calculateBezierPoint(p1, c1, p2, c2, this.middlePercentage);
		rightPoint = calculateBezierPoint(p1, c1, p2, c2, this.rightPercentage);
		// the xPoint[] arrays now contain the point and m (= inclination) of the bezier tangent
		// calculate m for straight line from connecting point to tangent point
		var cx = this.circle.position.x,
			cy = this.circle.position.y,
			dx = middlePoint[0] - cx,
			dy = middlePoint[1] - cy,
			cm = dx / dy;
		/*console.log("Percentages: ", this.leftPercentage, this.middlePercentage, this.rightPercentage);
		console.log("leftPoint = ("+leftPoint[0]+","+leftPoint[1]+") with m=", leftPoint[2]);
		console.log("middlePoint = ("+middlePoint[0]+","+middlePoint[1]+") with m=", middlePoint[2]);
		console.log("rightPoint = ("+rightPoint[0]+","+rightPoint[1]+") with m=", rightPoint[2]);
		console.log("connectionPoint = ("+cx+","+cy+") with m=", cm);
		*/
		// find out in which interval (left or right) the tangent point is
		// leftInterval <=> (leftM < connectionM < middleM) or (leftM > connectionM > middleM)
		// in other words: m must be BETWEEN the two other values
		// and same for right interval 
/*		var whichInterval = undefined;
		if (((leftPoint[2] < cm) && (cm < middlePoint[2])) || ((leftPoint[2] > cm)  && (cm > middlePoint[2]))) whichInterval = "left";
		else if (((rightPoint[2] < cm) && (cm < middlePoint[2])) || ((rightPoint[2] > cm)  && (cm > middlePoint[2]))) whichInterval = "right";
		else whichInterval = "noidea"; // not sure about that one ... //whichInterval = "sorry, dude, something seems to be wrong ...";
		//console.log("whichInterval: ", whichInterval);
		// calculate actual epsilon
		var actualEpsilon = Math.abs(Math.abs(cm) - Math.abs(middlePoint[2]));
		//console.log("actualEpsilon = ", actualEpsilon);
		// set new points to test
		switch (whichInterval) {
			case "left" : this.rightPercentage = this.middlePercentage; this.middlePercentage = (this.leftPercentage + this.rightPercentage) / 2;
						  break;
			case "right": this.leftPercentage = this.middlePercentage; this.middlePercentage = (this.leftPercentage + this.rightPercentage) / 2; 
					      break;
			case "noidea" : 
							//console.log("compare left: "+leftPoint[2].toFixed(2)+" <?> "+cm.toFixed(2)+" <?> "+middlePoint[2].toFixed(2));
							//console.log("compare right: "+middlePoint[2].toFixed(2)+" <?> "+cm.toFixed(2)+" <?> "+rightPoint[2].toFixed(2));
							this.middlePercentage = (this.middlePercentage + this.rightPercentage) / 2; // shift middle point instead
			
			
							break;
			default : avoidInfinity = 1000000000; break;
		}
		avoidInfinityLoop++;
	} while ((actualEpsilon > epsilon) && (avoidInfinityLoop < 500)); // do max 10 loops
	if (actualEpsilon <= epsilon) {
		//console.log("Point found: ", middlePoint);
		return middlePoint;
	} else { 
		console.log("No point found.");
		return false;
	}
*/
}
/////////////////////// end of experimental function
TEConnectionPointPreceeding.prototype.connect = function() {
	if (this.parent.editableToken.knotsList.length > 2) {
		var p1 = this.parent.editableToken.knotsList[1].circle.position,
			c1 = p1 + this.parent.fhToken.segments[1].handleOut,     // control points are RELATIVE coordinates
			p2 = this.parent.editableToken.knotsList[2].circle.position,
			c2 = p2 + this.parent.fhToken.segments[2].handleIn;
		var result = calculateBezierPoint(p1, c1, p2, c2, 50);
			
		//var bezierPoint = new Point(result[0], result[1]);
		//console.log("bezierPoint = ", bezierPoint);
		var result2 = this.findTangentPointRelativeToConnectionPoint(0.0000000001);
		
		if (result2 != false) {
			this.line.removeSegments();
			this.line.add( this.circle.position, new Point(result2[0], result2[1]));
			this.line.visible = true;
		} else this.line.visible = false;
		
	/*	if (this.circle.position.x > result2[0]) {
			console.log("error: ", result2);
		}*/
		//this.line.segments[0].point = this.circle.position;
		//this.line.segments[1].point = [result[0], result[1]];
		//console.log(this.line.segments[1]);
		
		//var result2 = this.findTangentPointRelativeToConnectionPoint(0.1);
		//this.line.segments[1].point = this.parent.editableToken.knotsList[0].circle.position;
	}
	// for test purposes: calculate tangents between to bezier segments (choose segments 2 and 6 from freehand curve)
	if (this.parent.editableToken.knotsList.length > 6) {
		var result3 = this.findTangentPointsBetweenCurves2And6(0.1);
	
	}
	
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
	if (this.parent.editableToken.knotsList.length > 0) {
		this.line.segments[0].point = this.circle.position;
		this.line.segments[1].point = this.parent.editableToken.knotsList[0].circle.position;
	}
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
	/*
	this.tensions = new PointText(new Point(100, 120));
	this.tensions.justification = "center";
	this.tensions.fillcolor = '#000';
	this.tensions.content = 'empty tensions';
	this.tensions.visible = false;
	*/
}
TEKnotLabel.prototype.updateLabel = function() { // TEVisuallyModifiableKnot
	//this.parent.markedCircle;
	var drawingAreaObject = this.parent.getTEDrawingAreaObject(this.parent.markedCircle);
	//console.log("markedCircle: ", this.parent.markedCircle, " drawingAreaObject: ", drawingAreaObject, " parent.editableToken: ", this.parent.editableToken);
	if (drawingAreaObject == this.parent.editableToken) {
		var valuesXY = this.parent.markedCircle.circle.position,
			valuesT = this.parent.markedCircle.tensions
			rescaledX = (-(this.parent.rotatingAxis.centerRotatingAxis.x - valuesXY.x) / this.parent.scaleFactor).toFixed(1),
			rescaledY = ((this.parent.rotatingAxis.centerRotatingAxis.y - valuesXY.y) / this.parent.scaleFactor).toFixed(1);
			
		this.coordinates.position = valuesXY + [0,12];	
		this.coordinates.content = "[" + rescaledX + "," + rescaledY + "]";
		//this.tensions.content = "T(" + valuesT[0] + "," + valuesT[1] + ")";
		//this.tensions.position = valuesXY + [0,12];
		//console.log("coordinates: ", this.coordinates.content, " tensions: ", this.tensions.content);
		this.coordinates.visible = true;
		//this.tensions.visible = true;
	} else {
		//this.tensions.visible = false;
		this.coordinates.visible = false;
	}
	//console.log("Show label");
}
