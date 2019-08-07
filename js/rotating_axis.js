/* VSTENO TOKEN EDITOR (working title)
 * (c) 2018 - Marcel Maci (m.maci@gmx.ch)
 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * ADDITIONAL DISCLAIMER
 * 
 * This Program uses the paper.js library which has been published under the MIT
 * license. The MIT-License is compatible with the GPL-License as long as the 
 * derived product (e.g. this program) and programs derived from it are published
 * under the GPL-license.
 * 
 * THE ORIGINAL LICENSE FOR PAPER.JS
 * 
 * Copyright (c) 2011 - 2016, Juerg Lehni & Jonathan Puckey
 * http://scratchdisk.com/ & http://jonathanpuckey.com/
 * All rights reserved.
 * 
 * The MIT License (MIT)
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE. 
 */
 
 
//class TERotatingAxisRelativeKnot
function TERotatingAxisRelativeKnot(x, y, type, link) {
	this.linkToVisuallyModifiableKnot = link;
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
TERotatingAxisRelativeToken.prototype.insertNewRelativeKnot = function(newKnot) {
	//console.log("TERotatingAxis.insertNewRelativeKnot()");
	//var relative = this.parent.getRelativeCoordinates(x, y, type);
	//console.log("newKnot (BEFORE) = ", newKnot);
	
	var relative = this.parent.getRelativeCoordinates(newKnot);
	//console.log("newKnot: relative = ", relative);
	
	//this.knotsList.push(new TERotatingAxisRelativeKnot(relative[0],relative[1], type));
	var type = "horizontal"; // make it fix for the moment
	var index = mainCanvas.editor.editableToken.index;
	var newRelativeKnot = new TERotatingAxisRelativeKnot(relative[0],relative[1], type, null);
	// link the two knots
	newKnot.linkToRelativeKnot = newRelativeKnot;
	newRelativeKnot.linkToVisuallyModifiableKnot = newKnot;
	//console.log("Link knots: ", newKnot, newRelativeKnot);
	this.knotsList.splice(index, 0, newRelativeKnot);
}
TERotatingAxisRelativeToken.prototype.updateRelativeCoordinates = function(tempSelectedKnot) {
	//console.log("update coordinates ...");
	var x = tempSelectedKnot.circle.position.x,
		y = tempSelectedKnot.circle.position.y,
		index = mainCanvas.editor.editableToken.index-1; // holy cow ... ugly, risky ... not good!
	//console.log("index: ", index);
	if (this.knotsList[index] != undefined) {
		//console.log("type: ", this.knotsList[index].type);
		switch /*(tempSelectedKnot.type) {*/ (this.knotsList[index].type) { // replace by: tempSelectedKnot.type?!?!
			case "horizontal" : var relative = this.parent.getRelativeCoordinates(tempSelectedKnot);
								this.knotsList[index].rd1 = relative[0];
								this.knotsList[index].rd2 = relative[1];
								break;
			case "orthogonal" : var relative = this.parent.getRelativeCoordinates(tempSelectedKnot);
								this.knotsList[index].rd1 = relative[0];
								this.knotsList[index].rd2 = relative[1];
								break;
			case "proportional" : var relative = this.parent.getRelativeCoordinates(tempSelectedKnot);
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
	this.shiftX = 0; // add this property for compatibility with parallel rotating axis
	this.type = "orthogonal"; // idem
	
	this.selected = true; // start with main rotating axis selected
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
	this.line.dasharray = [5,5];
	
	this.controlCircle = new TEVisuallyModifiableCircle(new Point(this.centerRotatingAxis.x,this.parent.upperY), 10, color, '#0a0', '#00f' ); // Path.Circle( new Point(this.centerRotatingAxis.x, this.parent.upperY), 5);
	
	// token data (relative coordinates)
	this.relativeToken = new TERotatingAxisRelativeToken(this);	
	
	// parallel rotating axis
	this.parallelRotatingAxis = new TEParallelRotatingAxisGrouper(this);
	//console.log("parallelRotatingAxis: ", this.parallelRotatingAxis);
	//this.parallelRotatingAxis.updateAll();
}
TERotatingAxis.prototype.select = function() {
	this.selected = true;
	this.updateColor();
}
TERotatingAxis.prototype.unselect = function() {
	this.selected = false;
	this.updateColor();
} 
TERotatingAxis.prototype.getStraightLineStartAndEndPoints = function(point) {
	var dx = point.x - this.centerRotatingAxis.x,
		dy = point.y - this.centerRotatingAxis.y;
	
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
	var temp1 = 0, temp2 = 0, horX = 0, newX = 0, newY = 0;
	for (var i=0; i<this.relativeToken.knotsList.length; i++) {
		temp1 = this.relativeToken.knotsList[i].rd1 * this.parent.scaleFactor;
		temp2 = this.centerRotatingAxis.y - (this.relativeToken.knotsList[i].rd2 * this.parent.scaleFactor);
		horX = this.calculateHorizontalIntersectionX( temp2, "horizontal");
		newX = horX + temp1;
		newY = temp2;
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
	//this.updateVisibleKnots();
	this.parent.updateFreehandPath();
	this.parent.connectPreceedingAndFollowing();
	this.parallelRotatingAxis.updateAll();
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
TERotatingAxis.prototype.updateColor = function() {
	switch (this.selected) {
		case true : this.line.strokeColor = colorMainRotatingAxisSelected; 
					this.line.dashArray = [15,5]; 
					this.controlCircle.circle.strokeColor = colorMainRotatingAxisSelected;
					this.controlCircle.circle.strokeWidth = 2;
					break;
		case false : this.line.strokeColor = colorMainRotatingAxisUnselected; 
					 this.line.dashArray = null; 
					 this.controlCircle.circle.strokeColor = colorMainRotatingAxisUnselected; 
	 		 		 this.controlCircle.circle.strokeWidth = 2;
					 break;
	}
	//console.log("RotatingAxis: ", this);
}
TERotatingAxis.prototype.handleMouseDrag = function(event) {
	if ((event.point.x >= this.parent.leftX) && (event.point.x < this.parent.rightX) && (event.point.y < this.centerRotatingAxis.y) && (event.point.y > this.parent.upperY)) {
		var point = event.point;
		var startAndEndPoints = this.getStraightLineStartAndEndPoints(point);
		this.line.segments[0].point = startAndEndPoints[0];
		this.line.segments[1].point = startAndEndPoints[1];	
		this.updateColor();
		this.controlCircle.circle.position = event.point;
		// adjust token points
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
TERotatingAxis.prototype.setRotatingAxisManually = function(point) {
	// basically same code as handleMouseDrag, but with custom x,y instead of event.point
	var startAndEndPoints = this.getStraightLineStartAndEndPoints(point);
	this.line.segments[0].point = startAndEndPoints[0];
	this.line.segments[1].point = startAndEndPoints[1];	
	this.updateColor();
	this.controlCircle.circle.position = point;
	// adjust token points
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
	var rdx = cx-ox,
		rdy = cy-oy;
	
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
	return [ix,iy];
}
TERotatingAxis.prototype.getRelativeCoordinates = function(visuallyModifiableKnot, type) {
	// try to work with standard parameter type in the following method (by the way: I hate JS ... (ceterum censeo:))
	type = typeof type !== 'undefined' ? type : "horizontal";
	if (visuallyModifiableKnot.linkToRelativeKnot !== null) type = visuallyModifiableKnot.linkToRelativeKnot.type;
	var x = visuallyModifiableKnot.circle.position.x,
		y = visuallyModifiableKnot.circle.position.y; 
	//console.log("TERotatingAxis.getRelativeCoordinates(): x, y, type: ", x, y, type);
	
	var relative = null;
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
				var shiftX = visuallyModifiableKnot.shiftX;
				
				var intersection = this.calculateOrthogonalIntersectionWithRotatingAxis(x,y);
				// calculate distance origin to intersection
				delta1X = intersection[0] - this.centerRotatingAxis.x;
				//console.log("delta1X: ", delta1X);
				delta1Y = intersection[1] - this.centerRotatingAxis.y;
				//console.log("delta1Y: ", delta1Y);
				distance1 = Math.sqrt((delta1X*delta1X) + (delta1Y*delta1Y));
				//console.log("length vector 1: ", distance1);
				// calculate distance intersection to knot
				delta2X = intersection[0] - x;
				delta2Y = intersection[1] - y;
				distance2 = Math.sqrt(delta2X*delta2X + delta2Y*delta2Y);
				//console.log("delta2X/Y: distance2: ", delta2X, delta2Y, distance2);
				// scale them down
				//console.log("y/intersection(1)/delta1X/delta1Y/distance1/scaleFactor: ", y/intersection[1], delta1X, delta1Y, distance1, this.parent.scaleFactor);
				// downScaledDistance1 = distance1 / this.parent.scaleFactor; // do that later so that parallel rotating axis distance can be calculated
				downScaledDistance2 = distance2 / this.parent.scaleFactor;
				// calculate intersection with parallel rotating axis (i.e. shiftX != 0, works also for shiftX == 0)
				// calculate intersection point
				var px = x + (delta2X / Math.avoidDivisionBy0(downScaledDistance2)) * (downScaledDistance2 - Math.abs(shiftX));
				var py = y + (delta2Y / Math.avoidDivisionBy0(downScaledDistance2)) * (downScaledDistance2 - Math.abs(shiftX));
				var deltaPX = px - this.centerRotatingAxis.x - (shiftX * this.parent.scaleFactor);
				var deltaPY = py - this.centerRotatingAxis.y;
				//console.log("deltaPX/Y: ", deltaPX, deltaPY);
				var distanceParallel = Math.sqrt((deltaPX*deltaPX)+(deltaPY*deltaPY));
				distance1 = distanceParallel;
				downScaledDistance1 = distance1 / this.parent.scaleFactor;
				//console.log("P(x,y): parallelDistance: ", px, py, distanceParallel, downScaledDistance1);
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
				// get shiftX value for parallel rotating axis
				//console.log("visuallyModifiableKnot: ", visuallyModifiableKnot);
				
				//var shiftX = visuallyModifiableKnot.shiftX;
				//var shiftX = visuallyModifiableKnot.linkToParallelRotatingAxis.shiftX; // new: take shiftX from linked axis
				//console.log("downScaledDistance2 (alias rd2): ", downScaledDistance2);
				//console.log("shiftX to calculate relative coordinates: ", shiftX);
				// define return value
				relative = [downScaledDistance1, downScaledDistance2 - shiftX];
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
	//console.log("relative (inside function) = ", relative
	return relative;
}
TERotatingAxis.prototype.getAbsoluteCoordinates = function(relativeTokenKnot) {
	var absCoordinates, temp1, temp2, horX, newX, newY;
	var rd1 = relativeTokenKnot.rd1,
		rd2 = relativeTokenKnot.rd2,
		type = relativeTokenKnot.type,
		parallelRotatingAxisType = relativeTokenKnot.linkToVisuallyModifiableKnot.parallelRotatingAxisType;
		//parallelRotatingAxisType = relativeTokenKnot.linkToVisuallyModifiableKnot.linkToParallelRotatingAxis.type; // new: take type from linked axis
	//console.log("TERotatingAxis.getAbsoluteCoordinates(relativeToken): rd1/2, type1/2: ", rd1, rd2, type, parallelRotatingAxisType);
		
	switch (type) {
		case "horizontal" : 
		
				temp1 = rd1 * this.parent.scaleFactor;
				temp2 = this.centerRotatingAxis.y - (rd2 * this.parent.scaleFactor);
				horX = this.calculateHorizontalIntersectionX( temp2, "horizontal");
				newX = horX + temp1;
				newY = temp2;
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
				// vector 2
					v2dx = v2dx / v2Length;
					v2dy = v2dy / v2Length;
				// calculate new point on rotating axis vector
				var rnx = rdx * rd1 * this.parent.scaleFactor,
					rny = rdy * rd1 * this.parent.scaleFactor;
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
				//console.log("absoluteCoordinates: BEFORE: rd1/rd2: ", rd1, rd2);
				//console.log("shiftX: ");
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
				// vector 2
					v2dx = v2dx / v2Length;
					v2dy = v2dy / v2Length;
				// proportional => adapt length of rd1
				var	radAngle = Math.radians(this.inclinationValue),
					sinAngle = Math.abs(Math.sin(radAngle)),
					rd1Proportional = rd1 / sinAngle;
				var rd2Proportional;
				switch (parallelRotatingAxisType) {
					case "horizontal" : rd2Proportional = rd2 * sinAngle; break;
					case "orthogonal" : rd2Proportional = rd2; break;  // keep same distance indepent from inclination
					case "main" : rd2Proportional = rd2; break; // treat main like orthogonal
				}
				// calculate new point on rotating axis vector
				var rnx = rdx * rd1Proportional * this.parent.scaleFactor,
					rny = rdy * rd1Proportional * this.parent.scaleFactor;
				// calculate new vector length // test rd2Proportional
				var v2nx = v2dx * rd2Proportional * this.parent.scaleFactor, // change direction
					v2ny = v2dy * rd2Proportional * this.parent.scaleFactor;
				// get shiftX for parallel rotating axis
				//var edTok = mainCanvas.editor.editableToken;		// brutally ugly ... change that in a free minute!
				var shiftX = relativeTokenKnot.linkToVisuallyModifiableKnot.shiftX;
				//var shiftX = relativeTokenKnot.linkToVisuallyModifiableKnot.linkToParallelRotatingAxis.shiftX; // new: take shiftX from linked axis
				
				//console.log("shiftX from knotsList: ", shiftX);
				var upscaledShiftX = shiftX * this.parent.scaleFactor;
				// calculate final absolute point (vector 1 + vector 2) + ox/oy
				switch (parallelRotatingAxisType) { 
					case "horizontal" : break;
					case "orthogonal" : var angle = Math.radians(Math.abs(this.inclinationValue));
										var hypothenuse = upscaledShiftX / Math.sin(angle);
										upscaledShiftX = hypothenuse; break;
				}
				var absx = rnx + v2nx + ox + upscaledShiftX,
					absy = rny + v2ny + oy;
				//console.log("absoluteCoordinates: AFTER: rd1/rd2: ", rd1Proportional, rd2);
				
				absCoordinates = [absx, absy];
				
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
			var absCoordinates = this.getAbsoluteCoordinates(this.relativeToken.knotsList[i]);
			// copy values to editable token
			this.parent.editableToken.knotsList[i].x = absCoordinates[0];
			this.parent.editableToken.knotsList[i].y = absCoordinates[1];	
			//console.log("newx,y: ", newX, newY);
			this.parent.editableToken.knotsList[i].circle.position = [absCoordinates[0], absCoordinates[1]];
		}
	}
}
