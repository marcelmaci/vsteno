// parallel_rotating_axis_code.js
// contains code to handle parallel rotating axis

// class definition
function TEParallelRotatingAxisGrouper(parent) {
	this.parent = parent;	// TERotatingAxis
	this.epsilon = 0.1; 	// tolerance: if abs(shiftX1 - shiftX2) < epsilon, only 1 axis is drawn 
							// (internally - i.e. for the knot - the EXACT value is calculated) 
	this.axisList = []; 	// array of TEParallelRotatingAxis
}
TEParallelRotatingAxisGrouper.prototype.addNewAxis = function(shiftX, type) {
	// tests if a new visual axis has to be inserted
	//console.log("Add new axis: ", shiftX);
	var result = this.getLowestEpsilonAndType(shiftX);
	if ((result[0] > this.epsilon) && (result[1] != type)) {		// add additional rotating axis when type is different (even if shiftX < epsilon)
		// insert new axis
		this.axisList.push(new TEParallelRotatingAxis(shiftX, type));
	}
}
TEParallelRotatingAxisGrouper.prototype.getLowestEpsilonAndType = function(shiftX) {
	var actualEpsilon = 99999999, newEpsilon, actualType;
	for (var i=0; i<this.axisList.length; i++) {
		newEpsilon = Math.abs(this.axisList[i].shiftX - shiftX);
		if (newEpsilon < actualEpsilon) {
			//actualEpsilon = (newEpsilon < actualEpsilon) ? newEpsilon : actualEpsilon;
			actualEpsilon = newEpsilon;
			actualType = this.axisList[i].type;
		}
	}
	return [actualEpsilon, actualType];
}
TEParallelRotatingAxisGrouper.prototype.drawAllAxis = function() {
	// get drawing area borders & scaling
	//console.log("TEParallelRotatingAxis.drawAllAxis()");
	var leftX = this.parent.parent.leftX,
		rightX = this.parent.parent.rightX,
		upperY = this.parent.parent.upperY,
		lowerY = this.parent.parent.lowerY,
		scaleF = this.parent.parent.scaleFactor;
	//console.log("leftX, upperY, rightX, lowerY, scaleF: ", leftX, upperY, rightX, lowerY, scaleF);
	// get coordinates of main rotating axis
	var ox = this.parent.centerRotatingAxis.x,		// origin
		oy = this.parent.centerRotatingAxis.y,
		cx = this.parent.controlCircle.circle.position.x,	// control circle
		cy = this.parent.controlCircle.circle.position.y;
	//console.log("ox, oy, cx, cy, this.parent.controlCircle: ", ox, oy, cx, cy, this.parent.controlCircle);	
	// calculate vector of main rotating axis
	var	dx = cx - ox,
		dy = cy - oy;
	//console.log("dx,dy: ", dx, dy);
	// declare variables for intersection calculation
	var r1x, r1y, r2x, r2y, l1x, l1y, l2x, l2y, m, c, lx, ly, rx, ry, angle, hypothenuse, shiftX, upscaledShiftX; 
	
	// calculate and draw
	for (var i=0; i<this.axisList.length; i++) {
		// calculate coordinates inside drawing area (clipping)
		shiftX = this.axisList[i].shiftX;
		switch (this.axisList[i].type) {
			case "horizontal" : break;
			case "orthogonal" : angle = Math.abs(Math.radians(this.parent.inclinationValue));	// inclination rotating axis
								hypothenuse = shiftX / Math.sin(angle);
								//console.log("proportional values: beta/h", angle, hypothenuse);
								// make rotating axis proportional
								shiftX = hypothenuse;
								break;
		}
		upscaledShiftX = shiftX * scaleF;
		
		// calculate points (y = m*x + c)
		// calculate m and c
		m = dy / Math.avoidDivisionBy0(dx);
		c = oy - (ox + upscaledShiftX) * m;
		// calculate right points
		// fix upperY
		r1y = upperY;
		r1x = (upperY - c) / m;
		// fix rightX
		r2x = rightX;
		r2y = m * rightX + c;
		// calculate left points
		// fix lowerY
		l1y = lowerY;
		l1x = (lowerY - c) / m;
		// fix leftX
		l2x = leftX;
		l2y = leftX * m + c;
		
		//console.log("l1x/y, l2x/y, r1x/y, r2x/y: ", l1x, l1y, l2x, l2y, r1x, r1y, r2x, r2y);
		
		// chose 2 points that fit drawing area (= clipping)
		// left point
		if (l1x < leftX) {
			lx = l2x;
			ly = l2y;
		} else {
			if (l1x > rightX) {
				lx = r2x;
				ly = r2y;
			} else {
				lx = l1x;
				ly = l1y;
			}
		}
		
		// right point
		if (r1x > rightX) {
			rx = r2x;
			ry = r2y;
		} else {
			if (r1x < leftX) {
				rx = l2x;
				ry = l2y;
			} else {	
				rx = r1x;
				ry = r1y;
			}
		}
		
		// calculate left point
		//iyl = (leftX + upscaledShiftX) / Math.avoidDivisionBy0(dx) * dy;
		//ixl = (iyl * dx) / dy - upscaledShiftX;
		
		// redraw line
		this.axisList[i].redrawLine(new Point(lx, ly), new Point(rx, ry));
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
		var type = this.parent.parent.editableToken.knotsList[i].parallelRotatingAxisType; 
		if (shiftX != 0) { // god knows if this comparison to 0 works in JS ... 
			this.addNewAxis(shiftX, type);
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
function TEParallelRotatingAxis(shiftX, type) {
	// shiftX: negative = left side; positive = right side from main rotating axis
	// for the moment no proportional axis are supported (i.e. shiftX is always the
	// same, independant from inclination)
	this.shiftX = shiftX;
	this.type = type;
	// define line
	this.line = Path.Line(new Point(0,0), new Point(0,0));
	this.line.strokeColor = '#0f0';
	this.line.dashArray = [1,1];
	this.line.strokeWidth = 1;
	this.line.visible = false;
}
TEParallelRotatingAxis.prototype.redrawLine = function(point1, point2) {
	//console.log("TEParalleRotatingAxis.redrawLine(): point1, point2: ", point1, point2);
	this.line.removeSegments();
	this.line = new Path.Line(point1, point2);
	this.line.strokeColor = '#0f0';
	this.line.dashArray = [5,5];
	this.line.strokeWidth = 1;
	this.line.visible = true;
}
