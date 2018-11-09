
// class TERotatingAxisTokenPoint
function TERotatingAxisTokenPoint( position, t1, t2, type, rotatingAxis ) {
	this.parent = rotatingAxis;
	this.absolute = position;			// type point
	this.tensions = [t1, t2];
	this.type = type;					// orthogonal or horizontal
	this.calculateRelativeCoordinates();
}
TERotatingAxisTokenPoint.prototype.calculateHorizontalIntersectionX = function() {
	var dx = this.parent.centerRotatingAxis.x - this.parent.controlCircle.position.x,
		dy = this.parent.centerRotatingAxis.y - this.parent.controlCircle.position.y;
	//console.log("dx/dy: ", dx, dy);
	//console.log("RotatingAxis: ", this.parent.centerRotatingAxis.x, this.parent.centerRotatingAxis);
	//console.log("absolute: ", this.absolute.x, this.absolute.y);
	if (dx == 0) horX = this.parent.centerRotatingAxis.x;		// avoid division by 0
	else {
		var m = dy / dx,
			horX = (this.absolute.y - this.parent.centerRotatingAxis.y) / m + this.parent.centerRotatingAxis.x;
	}
	//console.log("horX: ", horX);
	return horX;
}
TERotatingAxisTokenPoint.prototype.calculateHorizontalIntersectionRelativeX = function() {
	var relX = this.calculateHorizontalIntersectionX() - this.absolute.x;
	return relX;
}
TERotatingAxisTokenPoint.prototype.calculateRelativeCoordinates = function() {
	switch (this.type) {
		case "ortogonal" : break;
		case "horizontal" : 
				relX = this.absolute.x - this.calculateHorizontalIntersectionRelativeX() - this.absolute.x;
				//console.log("relX / abs.x: ", relX, this.absolute.x);
				downScaledX = relX / this.parent.parent.scaleFactor;
				downScaledY = -(this.absolute.y - this.parent.centerRotatingAxis.y) / this.parent.parent.scaleFactor;
				this.relative = [downScaledX, downScaledY];
				//console.log("Inserted: ", this.relative);
			break;
	}
}
// class TERotatingAxisTokenData
function TERotatingAxisTokenData() {
	this.middle = [];		// array of TERotatingAxisTokenPoint
	this.left = [];
	this.right = [];
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
	this.controlCircle = new Path.Circle( new Point(this.centerRotatingAxis.x, this.parent.upperY), 5);
	this.controlCircle.fillColor = color;
	
	// token data
	this.token = new TERotatingAxisTokenData();
}
TERotatingAxis.prototype.recalculateFreehandPoints = function() {
	var numberPoints = this.token.middle.length,
		dy = this.controlCircle.position.y - this.centerRotatingAxis.y,
		dx = this.controlCircle.position.x - this.centerRotatingAxis.x;
	if (dx == 0) {
		// calculate horizontal
	} else {
		var m = dy / dx;
		for (var i=0; i<numberPoints; i++) {
			var horX = this.token.middle[i].calculateHorizontalIntersectionX();
			
			this.token.middle[i].absolute.x = horX + (this.token.middle[i].relative[0] * this.parent.scaleFactor);
			this.token.middle[i].absolute.y = this.centerRotatingAxis.y - (this.token.middle[i].relative[1] * this.parent.scaleFactor);
			// copy values to freehand path
			// circles
			this.parent.fhCircleList[i].position.x = this.token.middle[i].absolute.x;
			this.parent.fhCircleList[i].position.y = this.token.middle[i].absolute.y;
			// segments
			this.parent.fhToken.segments[i].point.x = this.token.middle[i].absolute.x;
			this.parent.fhToken.segments[i].point.y = this.token.middle[i].absolute.y;
			//console.log("Position: ", this.token.middle[i].absolute);
			//console.log("this.parent: ", this.parent);
			this.parent.calculateFreehandHandles(); // recalculate bezier curve
		}
	}
	this.parent.preceeding.connect(); // update connecting point also
	this.parent.following.connect(); // update connecting point also
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
TERotatingAxis.prototype.isRotatingAxisControlCircle = function( item ) {
	if (item == this.controlCircle) return true;
	else return false;
}
TERotatingAxis.prototype.handleMouseDown = function(event) {
	//console.log("rotatingAxis.mousedown");	
	this.tempColor = this.controlCircle.fillColor;
	this.controlCircle.fillColor = "#aaa";
	this.controlCircle.position = event.point;
}
TERotatingAxis.prototype.handleMouseUp = function(event) {
	//console.log("rotatingAxis.mouseup");
	if ((event.point.x >= this.parent.leftX) && (event.point.x < this.parent.rightX) && (event.point.y < this.centerRotatingAxis.y) && (event.point.y > this.parent.upperY)) {
	
		var startAndEndPoints = this.getStraightLineStartAndEndPoints(event);
		if ((event.point.x <= this.parent.leftX) || (event.point.x > this.parent.rightX) || (event.point.y >= this.centerRotatingAxis.y) || (event.point.y < this.parent.upperY)) {
			circleCenter = new Point( startAndEndPoints[1] );
		} else circleCenter = event.point;
		//console.log("circleCenter: ", circleCenter);

		this.line.segments[0].point = startAndEndPoints[0];
		this.line.segments[1].point = startAndEndPoints[1];	

		//console.log(this.tempColor);
		this.controlCircle.fillColor = this.tempColor; // bug: why can tempColor be == 0 ?!
		if (this.controlCircle.fillColor == null) this.controlCircle.fillColor = "#ff0"; // workaround for the moment ... mark circle as yellow when the error occurs
		this.controlCircle.position = circleCenter; //event.point;
		this.parent.itemSelected = this.parent;
		this.parent.fhCircleSelected = null;
		// adjust token points
		this.recalculateFreehandPoints();
		//console.log(this.parent);
	} else {
		// just "release" controlCircle (and leave rotatingAxis and freehand path as it is)
		this.controlCircle.fillColor = this.tempColor; // bug: why can tempColor be == 0 ?!
		if (this.controlCircle.fillColor == null) this.controlCircle.fillColor = "#ff0"; // workaround for the moment ... mark circle as yellow when the error occurs
		this.parent.itemSelected = this.parent;
		this.parent.fhCircleSelected = null;	
	}
}
TERotatingAxis.prototype.handleMouseDrag = function(event) {
	//console.log("rotatingAxis.mousedrag");
	//console.log(this);
	if ((event.point.x >= this.parent.leftX) && (event.point.x < this.parent.rightX) && (event.point.y < this.centerRotatingAxis.y) && (event.point.y > this.parent.upperY)) {
		var startAndEndPoints = this.getStraightLineStartAndEndPoints(event);
		this.line.segments[0].point = startAndEndPoints[0];
		this.line.segments[1].point = startAndEndPoints[1];	
		this.controlCircle.position = event.point;
		// adjust token points
		this.recalculateFreehandPoints();
		var angleRad = Math.atan((startAndEndPoints[1][1] - startAndEndPoints[0][1]) / (startAndEndPoints[1][0] - startAndEndPoints[0][0]));
		var angleDeg = Math.degrees(angleRad);
		// copy values
		this.inclinationValue = angleDeg;
		this.inclinationLabel.content = Math.abs(angleDeg.toFixed(0)) + "°"; // show only positive values
	}
}
TERotatingAxis.prototype.handleEvent = function( event ) {
	//console.log("rotatingAxis.handleEvent()");
	switch (event.type) {
		case "mousedown" : this.handleMouseDown(event); break;
		case "mouseup" : this.handleMouseUp(event); break;
		case "mousedrag" : this.handleMouseDrag(event); break;
	}
	this.controlCircle.position = event.point;
	return;
}

