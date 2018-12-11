// parallel_rotating_axis_code.js
// contains code to handle parallel rotating axis

// class definition
function TEParallelRotatingAxisGrouper(parent) {
	this.parent = parent;	// TERotatingAxis
	this.epsilon = 0.1; 	// tolerance: if abs(shiftX1 - shiftX2) < epsilon, only 1 axis is drawn 
							// (internally - i.e. for the knot - the EXACT value is calculated) 
	this.axisList = []; 	// array of TEParallelRotatingAxis
	// new variables for manual insertion
	this.selectedAxis = 0; // select main rotating axis by default
	this.mainSelected = true;
	this.newAxisList = [new TEParallelRotatingAxis(0, "main")];	// add "dummy" main axis
}
// new method for manual insertion
TEParallelRotatingAxisGrouper.prototype.addParallelAxis = function() {
	// add axis from left to right (in order to select them with CTRL-arrow left/right)
	var defaultValue = 0;
	var shiftX = Number(prompt("Enter x-Delta for parallel rotating axis:\n(negative = left side; positive = right side)", defaultValue));
	if ((shiftX != defaultValue) && (!isNaN(shiftX))) {
		var i = 0, length = this.newAxisList.length, type = "orthogonal";
		for (i = 0; i < length; i++) {
			if ((i == 0) && (shiftX < this.newAxisList[i].shiftX)) break;
			if (shiftX > this.newAxisList[i]) break;
			//console.log("i: shiftX/newAxis.shiftX: ", i, shiftX, this.newAxisList[i].shiftX);
		}
		//console.log("TEParallelRotatingAxisGrouper.addParallelAxis(): i/shiftX/type: ", i, shiftX, type);
		var newParallelAxis = new TEParallelRotatingAxis(shiftX, type);
		newParallelAxis.line.strokeColor = '#00f';
		this.newAxisList.splice(i, 0, newParallelAxis);
		this.selectedAxis = i;
		this.mainSelected = false;
		this.parent.parent.rotatingAxis.unselect();
	}
	//console.log("AFTER: newAxisList: ", this.newAxisList);
	this.updateAll();
}
TEParallelRotatingAxisGrouper.prototype.deleteParallelAxis = function() {
	//console.log("TEParallelRotatingAxis.deleteParallelAxis()");
	if (this.newAxisList[this.selectedAxis].type != "main") {		// main axis cannot be deleted
		//console.log("Before: ", this.newAxisList);
		this.newAxisList[this.selectedAxis].line.removeSegments(); // remove line before deleting object
		this.newAxisList[this.selectedAxis].line = null;
		this.newAxisList.splice(this.selectedAxis,1);
		//console.log("After: ", this.newAxisList);
		this.selectedAxis -= 1;
		if (this.selectedAxis < 0) this.selectedAxis = this.newAxisList.length; 
		if (this.newAxisList[this.selectedAxis].type == "main") {
			this.mainSelected = true;
			this.parent.parent.rotatingAxis.select();
		}
		this.updateAll();
	}
}
TEParallelRotatingAxisGrouper.prototype.selectFollowingAxis = function() {
	var length = this.newAxisList.length;
	//console.log("Select following: length: ", length);
	if (length > 0) {
		if (!this.mainSelected) {
			//console.log("main not selected: this.selectedAxis: ", this.selectedAxis);
			this.selectedAxis = (this.selectedAxis + 1) % length;	// wrap around
			//console.log("newAxisList(this.selectedAxis).type: ", this.newAxisList[this.selectedAxis].type);
			if (this.newAxisList[this.selectedAxis].type == "main") {
				this.mainSelected = true;
				//console.log("select main: this.mainSelected: ", this.mainSelected);
				this.parent.parent.rotatingAxis.select();
			}
		} else {
			//console.log("main selected");
			//this.selectedAxis = Math.floor(this.selectedAxis);
			this.selectedAxis = (this.selectedAxis+1) % length;	// wrap around		
			//console.log("BEFORE: main rotating: selected: ", this.parent.parent.rotatingAxis.selected);
			this.mainSelected = false;
			this.parent.parent.rotatingAxis.unselect();
			//console.log("AFTER: main rotating: selected: ", this.parent.parent.rotatingAxis.selected);
		
		}
		//console.log("this.selectedAxis: ", this.selectedAxis);
		//console.log("axisList: ", this.newAxisList);
	}
	this.updateAll();
}
TEParallelRotatingAxisGrouper.prototype.selectPreceedingAxis = function() {
	var length = this.newAxisList.length;
	if (length > 0) {
		if (!this.mainSelected) {		
			if (this.selectedAxis == 0) this.selectedAxis = length; // wrap around
			this.selectedAxis = this.selectedAxis - 1;
			if (this.newAxisList[this.selectedAxis].type == "main") {
				this.mainSelected = true;
				//console.log("select main: this.mainSelected: ", this.mainSelected);
				this.parent.parent.rotatingAxis.select();
			}
		} else {
			if (this.selectedAxis == 0) this.selectedAxis = length; // wrap around
			this.selectedAxis = this.selectedAxis - 1;
			this.mainSelected = false;
			this.parent.parent.rotatingAxis.unselect();
		}		
		//console.log("this.selectedAxis: ", this.selectedAxis);
	}
	this.updateAll();
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
		
		// redraw line
		this.axisList[i].redrawLine(new Point(lx, ly), new Point(rx, ry));
	}
}
/// experimental
TEParallelRotatingAxisGrouper.prototype.experimentalDrawAllAxis = function() {
	// get drawing area borders & scaling
	//console.log("TEParallelRotatingAxis.experimentalDrawAllAxis()");
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
	for (var i=0; i<this.newAxisList.length; i++) {
		if (this.newAxisList[i].type != "main") {
			// calculate coordinates inside drawing area (clipping)
			shiftX = this.newAxisList[i].shiftX;
			switch (this.newAxisList[i].type) {
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
		
			// color
			var color = (this.selectedAxis == i) ? colorParallelRotatingAxisSelected : colorParallelRotatingAxisUnselected; 
		
			// redraw line
			//console.log("redraw: ", lx, ly, rx, ry, this.selectedAxis, color);
			this.newAxisList[i].redrawLine(new Point(lx, ly), new Point(rx, ry), color);
		}
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
/* 
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
*/
TEParallelRotatingAxisGrouper.prototype.updateAll = function() {
	this.experimentalDrawAllAxis();
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
TEParallelRotatingAxis.prototype.redrawLine = function(point1, point2, color) {
	color = (typeof color == undefined) ? colorParallelRotatingAxisUnselected : color;
	//console.log("TEParalleRotatingAxis.redrawLine(): point1, point2: ", point1, point2);
	this.line.removeSegments();
	this.line = new Path.Line(point1, point2);
	this.line.strokeColor = color;
	this.line.dashArray = [5,5];
	this.line.strokeWidth = 1;
	this.line.visible = true;
}
