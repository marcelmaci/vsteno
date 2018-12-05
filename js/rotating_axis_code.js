

/* 
class TERotatingAxisRelativeKnot
TERotatingAxisRelativeKnot is comparable to TEVisuallyModifiableKnot
The only difference is that TERotatingAxisRelativeKnot holds relative
data, i.e. (vetor) data to get from the absolute coordinates (in 
TEVisuallyModifiableKnot) to relative coordinates with respect to 
the rotating axis (and viceversa). The conversion (calculation) of
the coordinates is done by TERotatingAxis methods
TERotatingAxisRelativeKnot is invisible (only internal data)
*/
// class TERotatingAxisOuterKnot
/*
function TERotatingAxisOuterKnot(distance, x, y) {
	this.position = new Point(x,y);
	this.distance();
}
*/

function TERotatingAxisRelativeKnot(x, y, type) {
	// TERotatingAxisRelativeKnot doesn't include tensions (these are stored in TEVisuallyModifiableCircle)
	this.type = type;			// orthogonal or horizontal
	this.rd1 = x;				// relative data 1: x (for horizontal coordinates) - vector1: distance following rotating axis (for orthogonal coordinates)
	this.rd2 = y;				// relative data 2: y (for horizontal coordinates) - vector2: distance orthogonal to rotating axis
}
TERotatingAxisRelativeKnot.prototype.setType = function(type) {
	this.type = type;
}
TERotatingAxisRelativeKnot.prototype.getType = function() {
	return this.type;
}

// class TERotatingAxisRelativeToken
function TERotatingAxisRelativeToken(rotatingAxis) {
	this.parent = rotatingAxis;
	this.knotsList = []; 	// array of TERotatingAxisRelativeKnot
} 
/*
TERotatingAxisRelativeToken.prototype.pushNewRelativeKnot = function(x, y, type) {
	//console.log("TERotatingAxis.pushNewRelativeKnot()");
	var relative = this.parent.getRelativeCoordinates(x, y, type);
	//console.log("relative = ", relative);
	this.knotsList.push(new TERotatingAxisRelativeKnot(relative[0],relative[1], type));
}
*/
TERotatingAxisRelativeToken.prototype.insertNewRelativeKnot = function(x, y, type, index) {
	//console.log("TERotatingAxis.pushNewRelativeKnot()");
	var relative = this.parent.getRelativeCoordinates(x, y, type);
	//console.log("relative = ", relative);
	//this.knotsList.push(new TERotatingAxisRelativeKnot(relative[0],relative[1], type));
	this.knotsList.splice(index, 0, new TERotatingAxisRelativeKnot(relative[0],relative[1], type));
}
TERotatingAxisRelativeToken.prototype.updateRelativeCoordinates = function(x, y, index) {
	//console.log("update coordinates ...");
	if (this.knotsList[index] != undefined) {
		//console.log("type: ", this.knotsList[index].type);
		switch (this.knotsList[index].type) {
			case "horizontal" : var relative = this.parent.getRelativeCoordinates(x, y, this.knotsList[index].type);
								this.knotsList[index].rd1 = relative[0];
								this.knotsList[index].rd2 = relative[1];
								break;
			case "orthogonal" : var relative = this.parent.getRelativeCoordinates(x, y, /*"horizontal"*/ this.knotsList[index].type);
								this.knotsList[index].rd1 = relative[0];
								this.knotsList[index].rd2 = relative[1];
								break;
			case "proportional" : var relative = this.parent.getRelativeCoordinates(x, y, /*"horizontal"*/ this.knotsList[index].type);
								this.knotsList[index].rd1 = relative[0];
								this.knotsList[index].rd2 = relative[1];
								break;
		}
	}
	//console.log("Updated (new) Values: ", relative);
}

// class TERotatingAxis
function TERotatingAxis(drawingArea, color) {
	this.parent = drawingArea;
	this.absBasePosition = this.parent.lowerY - (this.parent.basePosition * this.parent.lineHeight * this.parent.scaleFactor);
	this.centerRotatingAxis = new Point((this.parent.rightX - this.parent.leftX)/2+this.parent.leftX, this.absBasePosition);
	this.inclinationValue = 90; // default value = 90° (vertical)
	this.inclinationLabel = new PointText({
							point: [this.parent.rightX-33, this.parent.lowerY-2],
							content: '90°',
							fillColor: '#0f0',
							//fontFamily: 'Courier New',
							fontWeight: 'bold',
							fontSize: 20 
						});		
	//this.m = null; 
	this.tempColor = '#0f0'; // try to avoid tempColor == null bug by setting variable from the beginning ...
	this.line = new Path.Line([this.centerRotatingAxis.x, this.parent.lowerY], [this.centerRotatingAxis.x, this.parent.upperY]);
	this.line.strokeColor = color;
	this.controlCircle = new TEVisuallyModifiableCircle(new Point(this.centerRotatingAxis.x,this.parent.upperY), 10, color, '#0a0', '#00f' ); // Path.Circle( new Point(this.centerRotatingAxis.x, this.parent.upperY), 5);
	
	// token data (relative coordinates)
	this.relativeToken = new TERotatingAxisRelativeToken(this);	
}
TERotatingAxis.prototype.getStraightLineStartAndEndPoints = function(event) {
	var dx = event.point.x - this.centerRotatingAxis.x,
		dy = event.point.y - this.centerRotatingAxis.y;
	
	if (dx == 0) return [[this.centerRotatingAxis.x,this.parent.lowerY],[this.centerRotatingAxis.x, this.parent.upperY]];	// avoid division by 0
	else {
		this.m = dy / dx;
		var	startY = this.parent.lowerY,
			startX = (startY - this.centerRotatingAxis.y) / this.m + this.centerRotatingAxis.x,
			endY = this.parent.upperY,
			endX = (endY - this.centerRotatingAxis.y) / this.m + this.centerRotatingAxis.x;
			
		if (endX > this.parent.rightX) {
			//console.log("apply end-mod");
			endX = this.parent.rightX;
			endY = (endX - this.centerRotatingAxis.x) * this.m + this.centerRotatingAxis.y;
		} else if (endX < this.parent.leftX) {
			//console.log("apply end-mod");
			endX = this.parent.leftX;
			endY = (endX - this.centerRotatingAxis.x) * this.m + this.centerRotatingAxis.y;
		}
	
		if (startX < this.parent.leftX) {
			//console.log("apply start-mod");
			startX = this.parent.leftX;
			startY = (startX - this.centerRotatingAxis.x) * this.m + this.centerRotatingAxis.y;
		} else if (startX > this.parent.rightX) {
			//console.log("apply start-mod");
			startX = this.parent.rightX;
			startY = (startX - this.centerRotatingAxis.x) * this.m + this.centerRotatingAxis.y;
		}
		return [[startX, startY],[endX, endY]];
	}
}
TERotatingAxis.prototype.updateVisibleKnots = function() {
	//console.log("TERotatingAxis.updateVisibleKnots");
	//if (editableToken.knotsList.length > 0) {
		var temp1 = 0, temp2 = 0, horX = 0, newX = 0, newY = 0;
		for (var i=0; i<this.relativeToken.knotsList.length; i++) {
			temp1 = this.relativeToken.knotsList[i].rd1 * this.parent.scaleFactor;
			temp2 = this.centerRotatingAxis.y - (this.relativeToken.knotsList[i].rd2 * this.parent.scaleFactor);
			horX = this.calculateHorizontalIntersectionX( temp2, "horizontal");
			newX = horX + temp1;
			newY = /*this.centerRotatingAxis.y -*/ temp2;
			//console.log("rel(x,y):", temp1, temp2, "Intersection: ", horX); //, "abs(x,y):", absx,absy);
			this.parent.editableToken.knotsList[i].circle.position = [newX, newY];
		}
	//}
}	
TERotatingAxis.prototype.identify = function(item) {
	//console.log("TERotatingAxis.identify()", item, this.controlCircle);
	if (item == this.controlCircle.circle) return this;
	else return false;
}
TERotatingAxis.prototype.handleEvent = function(event) {
	//console.log("TERotatingAxis.handleEvent()");
	switch (event.type) {
		case "mousedown" : this.handleMouseDown(event); break;
		case "mouseup" : this.handleMouseUp(event); break;
		case "mousedrag" : this.handleMouseDrag(event); break;
	}
	// update visible knots
	//this.updateVisibleKnots();
	this.parent.updateFreehandPath();
	this.parent.connectPreceedingAndFollowing();
}
TERotatingAxis.prototype.handleMouseDown = function(event) {
	//console.log("rotatingAxis.mousedown");	
	this.controlCircle.select();
	this.controlCircle.position = event.point;
	//this.recalculateFreehandPoints();
}
TERotatingAxis.prototype.handleMouseUp = function(event) {
	//this.controlCircle.position = event.point;
	this.controlCircle.unselect(); // works only inside drawing area ... probably superfluous if placed in TECanvas MouseUp-handler
}
TERotatingAxis.prototype.handleMouseDrag = function(event) {
	if ((event.point.x >= this.parent.leftX) && (event.point.x < this.parent.rightX) && (event.point.y < this.centerRotatingAxis.y) && (event.point.y > this.parent.upperY)) {
		var startAndEndPoints = this.getStraightLineStartAndEndPoints(event);
		this.line.segments[0].point = startAndEndPoints[0];
		this.line.segments[1].point = startAndEndPoints[1];	
		this.controlCircle.circle.position = event.point;
		// adjust token points
		//this.recalculateFreehandPoints(); // since I disabled this, the code works flawlessy ... but NO IDEA WHY ... ! :-) :-) :-)
		var angleRad = Math.atan((startAndEndPoints[1][1] - startAndEndPoints[0][1]) / (startAndEndPoints[1][0] - startAndEndPoints[0][0]));
		var angleDeg = Math.degrees(angleRad);
		// copy values
		this.inclinationValue = angleDeg;
		this.inclinationLabel.content = Math.abs(angleDeg.toFixed(0)) + "°"; // show only positive values
		// update
		
		//this.updateVisibleKnots();
		this.recalculateFreehandPoints();
		this.parent.updateFreehandPath();
		this.parent.connectPreceedingAndFollowing();
	}
}
TERotatingAxis.prototype.calculateHorizontalIntersectionX = function(y, type) {
	var dx = this.centerRotatingAxis.x - this.controlCircle.circle.position.x,
		dy = this.centerRotatingAxis.y - this.controlCircle.circle.position.y;
	if (dx == 0) horX = this.centerRotatingAxis.x;		// avoid division by 0
	else {
		var m = dy / dx,
			horX = (y - this.centerRotatingAxis.y) / m + this.centerRotatingAxis.x;
	}
	//console.log("horX: ", horX);
	return horX;
}
TERotatingAxis.prototype.calculateOrthogonalIntersectionWithRotatingAxis = function(kx, ky) {
	// given: a) rotating axis (from origin to center control point)
	//		  b) knot at actual position
	// calculate: intersection (x,y) with rotating axis
	// coordinates are absolute (i.e. inside canvas)
	var ix, iy, m1, m2, c1, c2, kdx, kdy;
	// set origin
	var ox = this.centerRotatingAxis.x,
		oy = this.centerRotatingAxis.y;
	// set control point coordinates
	var cx = this.controlCircle.circle.position.x,
		cy = this.controlCircle.circle.position.y;
	// calculate deltas of rotating axis
	//console.log("hi there: ", this.controlCircle.circle.position, this.centerRotatingAxis);
	var rdx = cx-ox,
		rdy = cy-oy;
	//console.log(rdx, rdy, this.centerRotatingAxis.x);
	
	if (rdx == 0) {
			//console.log("is vertical");
			// avoid division by zero
			// this case is trivial: iy = y, ix = ox = this.centerRotatingAxis.x
			ix = ox;
			iy = ky;
	} else {
		// calculate deltas for straight line that passes through knot
		// must be orthogonal to rotating axis => swap rdx, rdy and negate rdx
		kdx = rdy;
		kdy = -rdx;
		
		// straight lines are defined like so:
		// g1: y = x * (rdy/rdx) + c1, with m1 = rdy/rdx and c1 = oy - ox*(rdy/rdx)
		// g2: y = x * (-rdx/rdy) + c2, with m2 = -rdx/rdy = kdx/kdy and c2 = y - (x*m2)
	
		// calculate intersection ix, iy
		// ix = (c2-c1) / (m1-m2)
		// iy = ix * m1 + c1
		m1 = rdy/Math.avoidDivisionBy0(rdx);
		m2 = kdy/Math.avoidDivisionBy0(kdx);
		c1 = oy-ox*m1;
		c2 = ky-kx*m2;
		ix = (c2-c1) / Math.avoidDivisionBy0(m1-m2);
		iy = (ix*m1) + c1;
		
	}	
	/*console.log("Results: ");
	console.log("m1=rdy/rdx: ", m1, "=", rdy, "/", rdx, "m2=kdx/kdy: ", m2, "=", kdx, "/", kdy);
	console.log("c1=oy-ox*m1: ", c1, "=", oy, "-", ox, "*", m1);
	console.log("c2=kx-ky*m2: ", c2, "=", kx, "-", ky, "*", m2);
	
	console.log("g1: oy=ox*m1+c1 ", oy, "=", ox, "*", m1, "+", c1);
	console.log("g2: y=x*m2+c2 ", ky, "=", kx, "*", m2, "+", c2);
	console.log("Intersection: ", ix, iy);
	*/
	return [ix,iy];
}
TERotatingAxis.prototype.getRelativeCoordinates = function(x, y, type) {
	var relative = null;
	//console.log("TERotatingAxis.getRelativeCoordinates: ", x, y, type);
	var intersection, downScaledX, downScaledY, delta1X, delta1Y, delta2X, delta2Y, distance1, distance2, downScaledDistance1, downScaledDistance2;
	switch (type) {
		case "orthogonal" : 
				var intersection = this.calculateOrthogonalIntersectionWithRotatingAxis(x,y);
				// calculate distance origin to intersection
				delta1X = intersection[0] - this.centerRotatingAxis.x;
				//console.log("delta1x: ", delta1X);
				delta1Y = intersection[1] - this.centerRotatingAxis.y;
				//console.log("delta1y: ", delta1Y);
				distance1 = Math.sqrt((delta1X*delta1X) + (delta1Y*delta1Y));
				//console.log("length vector 1: ", distance1);
				// calculate distance intersection to knot
				delta2X = intersection[0] - x;
				delta2Y = intersection[1] - y;
				distance2 = Math.sqrt(delta2X*delta2X + delta2Y*delta2Y);
				// scale the down
				downScaledDistance1 = distance1 / this.parent.scaleFactor;
				downScaledDistance2 = distance2 / this.parent.scaleFactor;
				// add direction for distance (positive or negative)
				if (x<intersection[0]) downScaledDistance2 = -downScaledDistance2; // + = left side, - = right side of rotating axis
				if (y>this.centerRotatingAxis.y) downScaledDistance1 = -downScaledDistance1; // - = below baseline / + = above baseline
				// define return value
				relative = [downScaledDistance1, downScaledDistance2];
				//console.log("calculate orthogonal:", relative);
		
		break;
		case "proportional" : 
				var intersection = this.calculateOrthogonalIntersectionWithRotatingAxis(x,y);
				// calculate distance origin to intersection
				delta1X = intersection[0] - this.centerRotatingAxis.x;
				//console.log("delta1x: ", delta1X);
				delta1Y = intersection[1] - this.centerRotatingAxis.y;
				//console.log("delta1y: ", delta1Y);
				distance1 = Math.sqrt((delta1X*delta1X) + (delta1Y*delta1Y));
				//console.log("length vector 1: ", distance1);
				// calculate distance intersection to knot
				delta2X = intersection[0] - x;
				delta2Y = intersection[1] - y;
				distance2 = Math.sqrt(delta2X*delta2X + delta2Y*delta2Y);
				// scale them down
				//console.log("y/intersection(1)/delta1X/delta1Y/distance1/scaleFactor: ", y/intersection[1], delta1X, delta1Y, distance1, this.parent.scaleFactor);
				downScaledDistance1 = distance1 / this.parent.scaleFactor;
				downScaledDistance2 = distance2 / this.parent.scaleFactor;
				// add direction for distance (positive or negative)
				if (x<intersection[0]) downScaledDistance2 = -downScaledDistance2; // + = left side, - = right side of rotating axis
				if (y>this.centerRotatingAxis.y) downScaledDistance1 = -downScaledDistance1; // - = below baseline / + = above baseline
				// the only difference between orthogonal and proportional is the relative length of distance1
				// distance1 depends on the angle of rotating axis
				var radAngle = Math.radians(this.inclinationValue);
				var sinAngle = Math.abs(Math.sin(radAngle));
				var verticalDistance = downScaledDistance1 * sinAngle; // same distance at 90 degree (vertical)
				//console.log("deg/rad/sin/vert/distance1: ", this.inclinationValue, radAngle, sinAngle, verticalDistance, downScaledDistance1);
				downScaledDistance1 = verticalDistance;
				// define return value
				relative = [downScaledDistance1, downScaledDistance2];
				//console.log("proportional vector:", relative);
		
		break;
		case "horizontal" : 
				relX = -this.calculateHorizontalIntersectionRelativeX(x, y, type);
				//console.log("relX:", relX, "From: ", x, y, type);
				downScaledX = relX / this.parent.scaleFactor;
				downScaledY = -(y - this.centerRotatingAxis.y) / this.parent.scaleFactor;
				relative = [downScaledX, downScaledY];
				//console.log("calculate horizontal: ", relative);
			break;
	}
	return relative;
}
TERotatingAxis.prototype.getAbsoluteCoordinates = function(rd1, rd2, type) {
	var absCoordinates, temp1, temp2, horX, newX, newY;
	switch (type) {
		case "horizontal" : 
		
				temp1 = rd1 * this.parent.scaleFactor;
				temp2 = this.centerRotatingAxis.y - (rd2 * this.parent.scaleFactor);
				horX = this.calculateHorizontalIntersectionX( temp2, "horizontal");
				newX = horX + temp1;
				newY = /*this.centerRotatingAxis.y -*/ temp2;
				//console.log("rel(x,y):", temp1, temp2, "Intersection: ", horX); //, "abs(x,y):", absx,absy);
				//console.log("new(x,y):", newX, newY);
				//this.parent.editableToken.knotsList[i].circle.position = [newX, newY];
				
				
				/*
				
				relX = -this.calculateHorizontalIntersectionRelativeX(rd1, rd2, type);
				console.log("relX:", relX, "From: ", x, y, type);
				downScaledX = relX / this.parent.scaleFactor;
				downScaledY = -(y - this.centerRotatingAxis.y) / this.parent.scaleFactor;
				absCoordinates = [downScaledX, downScaledY];*/
				absCoordinates = [newX, newY];
				//console.log("calculate absolute horizontal: ", absCoordinates);
			break;
		case "orthogonal" : //absCoordinates = [300,300];
		
				// set origin
				var ox = this.centerRotatingAxis.x,
					oy = this.centerRotatingAxis.y;
				// set control point coordinates
				var cx = this.controlCircle.circle.position.x,
					cy = this.controlCircle.circle.position.y;
				// calculate deltas of rotating axis (vector 1)
				var rdx = cx-ox,
					rdy = cy-oy;
				// define vector 2 (orthogonal)
				var v2dx = -rdy,
					v2dy = rdx;
				// calculate length
				var rLength = Math.sqrt((rdx*rdx) + (rdy*rdy));
				var v2Length = Math.sqrt((v2dx*v2dx) + (v2dy * v2dy));
				// standardize deltas
					rdx = rdx / rLength;
					rdy = rdy / rLength;
					v2dx = v2dx / v2Length;
					v2dy = v2dy / v2Length;
				// calculate new point on rotating axis vector
				var rnx = rdx * rd1 * this.parent.scaleFactor,
					rny = rdy * rd1 * this.parent.scaleFactor;
				// define vector 2 (orthogonal)
				//var v2dx = -rdy,
				//	v2dy = rdx;
				// calculate new vector length
				var v2nx = v2dx * rd2 * this.parent.scaleFactor, // change direction
					v2ny = v2dy * rd2 * this.parent.scaleFactor;
				// calculate final absolute point (vector 1 + vector 2) + ox/oy
				var absx = rnx + v2nx + ox,
					absy = rny + v2ny + oy;
				
				absCoordinates = [absx, absy]
				//console.log("calculate orthogonal:", absCoordinates);
		
			break;
			case "proportional" : //absCoordinates = [300,300];
				//console.log("rd1/rd2: ", rd1, rd2);
				// set origin
				var ox = this.centerRotatingAxis.x,
					oy = this.centerRotatingAxis.y;
				// set control point coordinates
				var cx = this.controlCircle.circle.position.x,
					cy = this.controlCircle.circle.position.y;
				// calculate deltas of rotating axis (vector 1)
				var rdx = cx-ox,
					rdy = cy-oy;
				// define vector 2 (orthogonal)
				var v2dx = -rdy,
					v2dy = rdx;
				// calculate length
				var rLength = Math.sqrt((rdx*rdx) + (rdy*rdy));
				var v2Length = Math.sqrt((v2dx*v2dx) + (v2dy * v2dy));
				// standardize deltas
					rdx = rdx / rLength;
					rdy = rdy / rLength;
					v2dx = v2dx / v2Length;
					v2dy = v2dy / v2Length;
				// proportional => adapt length of rd1
				var	radAngle = Math.radians(this.inclinationValue),
					sinAngle = Math.abs(Math.sin(radAngle)),
					rd1Proportional = rd1 / sinAngle;
				// calculate new point on rotating axis vector
				var rnx = rdx * rd1Proportional * this.parent.scaleFactor,
					rny = rdy * rd1Proportional * this.parent.scaleFactor;
				// define vector 2 (orthogonal)
				//var v2dx = -rdy,
				//	v2dy = rdx;
				// calculate new vector length
				var v2nx = v2dx * rd2 * this.parent.scaleFactor, // change direction
					v2ny = v2dy * rd2 * this.parent.scaleFactor;
				// calculate final absolute point (vector 1 + vector 2) + ox/oy
				var absx = rnx + v2nx + ox,
					absy = rny + v2ny + oy;
				
				absCoordinates = [absx, absy]
				//console.log("calculate orthogonal:", absCoordinates);
		
			break;
	}
	return absCoordinates;
}
TERotatingAxis.prototype.calculateHorizontalIntersectionRelativeX = function(x, y, type) {
	var relX = this.calculateHorizontalIntersectionX(/*x,*/ y, type) - x;
	return relX;
}
TERotatingAxis.prototype.recalculateFreehandPoints = function() {
	//console.log("TERotatingAxis.recalculateFreehandPoints");
	var newX, newY;
	var numberPoints = this.parent.editableToken.knotsList.length,
		dy = this.controlCircle.circle.position.y - this.centerRotatingAxis.y,
		dx = this.controlCircle.circle.position.x - this.centerRotatingAxis.x;
	if (dx == 0) {
		// calculate horizontal
	} else {
		var m = dy / dx;
		for (var i=0; i<numberPoints; i++) {
			// read relative values
			var rd1 = this.relativeToken.knotsList[i].rd1,
				rd2 = this.relativeToken.knotsList[i].rd2,
				type = this.relativeToken.knotsList[i].type;
			// calculate absolute coordinates
			//console.log("getAbsoluteCoordinates: i: (rd1, rd2, type) ", i, ":", rd1, rd2, type);
			var absCoordinates = this.getAbsoluteCoordinates(rd1, rd2, type);
			//console.log("absCoordinates: ", absCoordinates);
			// copy values to editable token
			this.parent.editableToken.knotsList[i].x = absCoordinates[0];
			this.parent.editableToken.knotsList[i].y = absCoordinates[1];
				
			//console.log("newx,y: ", newX, newY);
			this.parent.editableToken.knotsList[i].circle.position = [absCoordinates[0], absCoordinates[1]];
			/*
			var horX = this.calculateHorizontalIntersectionX(this.parent.editableToken.knotsList[i].x, this.parent.editableToken.knotsList[i].y, "horizontal" );
			
			var tempx = this.parent.editableToken.knotsList[i];
			var tempy = this.parent.editableToken.knotsList[i];
			
			var relative = this.getRelativeCoordinates( tempx, tempy, "horizontal");
			
			this.parent.editableToken.knotsList[i].x = horX + (relative[0] * this.parent.scaleFactor);
			this.parent.editableToken.knotsList[i].y = this.centerRotatingAxis.y - (relative[1] * this.parent.scaleFactor);
			*/
		}
	}
	//this.parent.connectPreceedingAndFollowing();
	//this.parent.updateFreehandPath();
	//this.parent.connectPreceedingAndFollowing();
	
	//this.parent.preceeding.connect(); // update connecting point also
	//this.parent.following.connect(); // update connecting point also
}
