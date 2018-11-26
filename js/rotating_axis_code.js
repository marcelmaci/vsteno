

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
function TERotatingAxisOuterKnot(distance, x, y) {
	this.position = new Point(x,y);
	this.distance();
}

function TERotatingAxisRelativeKnot(x, y, type) {
	// TERotatingAxisRelativeKnot doesn't include tensions (these are stored in TEVisuallyModifiableCircle)
	this.type = type;			// orthogonal or horizontal
	this.rd1 = x;				// relative data 1: x (for horizontal coordinates) - relative length following rotating axis (for orthogonal coordinates)
	this.rd2 = y;				// relative data 2: y (for horizontal coordinates) - relative distance orthogonal to rotating axis
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
	if (this.knotsList[index] != undefined) {
		var relative = this.parent.getRelativeCoordinates(x, y, this.knotsList[index].type);
		this.knotsList[index].rd1 = relative[0];
		this.knotsList[index].rd2 = relative[1];
	}
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
	this.controlCircle = new TEVisuallyModifiableCircle(new Point(this.centerRotatingAxis.x,this.parent.upperY), 5, color, '#0a0', '#00f' ); // Path.Circle( new Point(this.centerRotatingAxis.x, this.parent.upperY), 5);
	
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
	this.updateVisibleKnots();
	this.parent.updateFreehandPath();
}
TERotatingAxis.prototype.handleMouseDown = function(event) {
	//console.log("rotatingAxis.mousedown");	
	this.controlCircle.select();
	this.controlCircle.position = event.point;
}
TERotatingAxis.prototype.handleMouseUp = function(event) {
	//this.controlCircle.position = event.point;
	this.controlCircle.unselect();
}
TERotatingAxis.prototype.handleMouseDrag = function(event) {
	if ((event.point.x >= this.parent.leftX) && (event.point.x < this.parent.rightX) && (event.point.y < this.centerRotatingAxis.y) && (event.point.y > this.parent.upperY)) {
		var startAndEndPoints = this.getStraightLineStartAndEndPoints(event);
		this.line.segments[0].point = startAndEndPoints[0];
		this.line.segments[1].point = startAndEndPoints[1];	
		this.controlCircle.circle.position = event.point;
		// adjust token points
		this.recalculateFreehandPoints();
		var angleRad = Math.atan((startAndEndPoints[1][1] - startAndEndPoints[0][1]) / (startAndEndPoints[1][0] - startAndEndPoints[0][0]));
		var angleDeg = Math.degrees(angleRad);
		// copy values
		this.inclinationValue = angleDeg;
		this.inclinationLabel.content = Math.abs(angleDeg.toFixed(0)) + "°"; // show only positive values
	}
}
TERotatingAxis.prototype.calculateHorizontalIntersectionX = function(/* x, */ y, type) {
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
TERotatingAxis.prototype.getRelativeCoordinates = function(x, y, type) {
	var relative = null;
	switch (type) {
		case "ortogonal" : break;
		case "horizontal" : 
				relX = -this.calculateHorizontalIntersectionRelativeX(x, y, type);
				//console.log("relX / abs.x: ", relX, this.absolute.x);
				downScaledX = relX / this.parent.scaleFactor;
				downScaledY = -(y - this.centerRotatingAxis.y) / this.parent.scaleFactor;
				relative = [downScaledX, downScaledY];
				//console.log("Inserted: ", this.relative);
			break;
	}
	return relative;
}
TERotatingAxis.prototype.calculateHorizontalIntersectionRelativeX = function(x, y, type) {
	var relX = this.calculateHorizontalIntersectionX(/*x,*/ y, type) - x;
	return relX;
}
TERotatingAxis.prototype.recalculateFreehandPoints = function() {
	
	var numberPoints = this.parent.editableToken.knotsList.length,
		dy = this.controlCircle.circle.position.y - this.centerRotatingAxis.y,
		dx = this.controlCircle.circle.position.x - this.centerRotatingAxis.x;
	if (dx == 0) {
		// calculate horizontal
	} else {
		var m = dy / dx;
		for (var i=0; i<numberPoints; i++) {
			var horX = this.calculateHorizontalIntersectionX(this.parent.editableToken.knotsList[i].x, this.parent.editableToken.knotsList[i].y, "horizontal" );
			
			var tempx = this.parent.editableToken.knotsList[i];
			var tempy = this.parent.editableToken.knotsList[i];
			
			var relative = this.getRelativeCoordinates( tempx, tempy, "horizontal");
			
			this.parent.editableToken.knotsList[i].x = horX + (relative[0] * this.parent.scaleFactor);
			this.parent.editableToken.knotsList[i].y = this.centerRotatingAxis.y - (relative[1] * this.parent.scaleFactor);
		}

	}
	//this.parent.connectPreceedingAndFollowing();
	
	this.parent.preceeding.connect(); // update connecting point also
	this.parent.following.connect(); // update connecting point also
}
