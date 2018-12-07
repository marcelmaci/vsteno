// parallel_rotating_axis_code.js
// contains code to handle parallel rotating axis

// class definition
function TEParallelRotatingAxisGrouper(parent) {
	this.parent = parent;	// TERotatingAxis
	this.epsilon = 0.1; 	// tolerance: if abs(shiftX1 - shiftX2) < epsilon, only 1 axis is drawn 
							// (internally - i.e. for the knot - the EXACT value is calculated) 
	this.axisList = []; 	// array of TEParallelRotatingAxis
}
TEParallelRotatingAxisGrouper.prototype.addNewAxis = function(shiftX) {
	// tests if a new visual axis has to be inserted
	//console.log("Add new axis: ", shiftX);
	if (this.getLowestEpsilon(shiftX) > this.epsilon) {
		// insert new axis
		this.axisList.push(new TEParallelRotatingAxis(shiftX));
	}
}
TEParallelRotatingAxisGrouper.prototype.getLowestEpsilon = function(shiftX) {
	var actualEpsilon = 99999999, newEpsilon;
	for (var i=0; i<this.axisList.length; i++) {
		newEpsilon = Math.abs(this.axisList[i].shiftX - shiftX);
		actualEpsilon = (newEpsilon < actualEpsilon) ? newEpsilon : actualEpsilon;
	}
	return actualEpsilon;
}
TEParallelRotatingAxisGrouper.prototype.drawAllAxis = function() {
	// get drawing area borders & scaling
	console.log("TEParallelRotatingAxis.drawAllAxis()");
	var leftX = this.parent.parent.leftX,
		rightX = this.parent.parent.rightX,
		upperY = this.parent.parent.upperY,
		lowerY = this.parent.parent.lowerY,
		scaleF = this.parent.parent.scaleFactor;
	console.log("leftX, upperY, rightX, lowerY, scaleF: ", leftX, upperY, rightX, lowerY, scaleF);
	// get coordinates of main rotating axis
	var ox = this.parent.centerRotatingAxis.x,		// origin
		oy = this.parent.centerRotatingAxis.y,
		cx = this.parent.controlCircle.circle.position.x,	// control circle
		cy = this.parent.controlCircle.circle.position.y;
	console.log("ox, oy, cx, cy, this.parent.controlCircle: ", ox, oy, cx, cy, this.parent.controlCircle);	
	// calculate vector of main rotating axis
	var	dx = cx - ox,
		dy = cy - oy;
	console.log("dx,dy: ", dx, dy);
	// declare variables for intersection calculation
	var ixr, iyr, ixl, iyl, shiftX, upscaledShiftX; 
	
	// calculate and draw
	for (var i=0; i<this.axisList.length; i++) {
		// calculate coordinates inside drawing area (clipping)
		shiftX = this.axisList[i].shiftX;
		upscaledShiftX = shiftX * scaleF;
		
		// do it the simple way for test purposes
		
		ixl = ox + upscaledShiftX;
		iyl = oy;
		ixr = cx + upscaledShiftX;
		iyr = cy;
		
		/*
		// calculate right point
		iyr = (rightX + upscaledShiftX) / Math.avoidDivisionBy0(dx) * dy;
		ixr = (iyr * dx) / dy - upscaledShiftX;
		// calculate left point
		iyl = (leftX + upscaledShiftX) / Math.avoidDivisionBy0(dx) * dy;
		ixl = (iyl * dx) / dy - upscaledShiftX;
		*/
		// redraw line
		this.axisList[i].redrawLine(new Point(ixl, iyl), new Point(ixr, iyr));
	}
}
TEParallelRotatingAxisGrouper.prototype.emptyArray = function() {
	for (var i=0; i<this.axisList.length; i++) {
		this.axisList[i].line.removeSegments();
		this.axisList[i].line = undefined;  // horrible to abandon an array somewhere hoping that it will be cleaned by garbage collector ...
		this.shiftX = undefined;
	}	
	this.axisList = [];
} 
TEParallelRotatingAxisGrouper.prototype.updateRotatingAxisList = function() {
	var numberKnots = this.parent.relativeToken.knotsList.length;
	// start with an empty array for simplicity (change that later if performance is an issue)
	// now, how do we EMPTY an existing array ... as always in JavaScript there's a whole bunch of methods
	// (personally, I'd prefer to have just 1 GOOD method instead of several improvised ones as we always have in JS ...)
	// the methods are:
	// 1: a = [];								// works only if array isn't referenced anywhere else
	// 2: a.length = 0; 						// may not work in all implementations of JS
	// 3: a.splice(0, a.length):				// returns array with all removed items (but benchmarks say it's no performance issue ... aha ... )
	// 4: while (a.length > 0) { a.pop(); }		// of(f) course ... (definitely ugly, stupid and slow)
	// as I said above: all methods have their downsides and that's why I said I honestly prefert to have only 1 (but a good one) method ...
	// did I already mention that I hate JS ... ?
	// I'll go with method 1 for the moment ...
	
	// this.axisList = [];
	// ok, first problem: when array is delete, all Path.Line objects will remain on screen as zombies that can never be brought back to live (since "pointer" is lost ...)
	// write a method that removes all objects first
	this.emptyArray();
	
	for (var i=0; i<numberKnots; i++) {
		//console.log("updateRotatingAxisList: i: this.parent.parent.editableToken.knotsList[i]: ", i, this.parent.parent.editableToken.knotsList[i]); 
		var shiftX = this.parent.parent.editableToken.knotsList[i].shiftX; 
		if (shiftX != 0) { // god knows if this comparison to 0 works in JS ... 
			this.addNewAxis(shiftX);
		}
	}
}
TEParallelRotatingAxisGrouper.prototype.updateAll = function() {
	// this method is called by TERotatingAxis.eventHandler()
	//console.log("TEParallelRotatingAxisGrouper.updateAll()1: axisList: ", this.axisList);
	this.updateRotatingAxisList();
	//console.log("TEParallelRotatingAxisGrouper.updateAll()2: axisList: ", this.axisList);
	this.drawAllAxis();
	//console.log("TEParallelRotatingAxisGrouper.updateAll()3: axisList: ", this.axisList);
}

// class definition
function TEParallelRotatingAxis(shiftX) {
	// shiftX: negative = left side; positive = right side from main rotating axis
	// for the moment no proportional axis are supported (i.e. shiftX is always the
	// same, independant from inclination)
	this.shiftX = shiftX;
	// define line
	this.line = Path.Line(new Point(0,0), new Point(0,0));
	this.line.strokeColor = '#0f0';
	this.line.dashArray = [1,1];
	this.line.strokeWidth = 1;
	this.line.visible = false;
}
TEParallelRotatingAxis.prototype.redrawLine = function(point1, point2) {
	console.log("TEParalleRotatingAxis.redrawLine(): point1, point2: ", point1, point2);
	this.line.removeSegments();
	this.line = new Path.Line(point1, point2);
	this.line.strokeColor = '#0f0';
	this.line.dashArray = [1,1];
	this.line.strokeWidth = 1;
	this.line.visible = true;
}
