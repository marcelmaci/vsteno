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
 
 
// bezier functions
function getControlPoints( p0, p1, p2, t) {
    var d01=Math.sqrt(Math.pow(p0.x-p1.x,2)+Math.pow(p1.y-p0.y,2));
    var d12=Math.sqrt(Math.pow(p2.x-p1.x,2)+Math.pow(p2.y-p1.y,2));
    var fa=t*d01/(d01+d12);   // scaling factor for triangle Ta
    var fb=t*d12/(d01+d12);   // ditto for Tb, simplifies to fb=t-fa
    var p1x=p1.x-fa*(p2.x-p0.x);    // x2-x0 is the width of triangle T
    var p1y=p1.y-fa*(p2.y-p0.y);    // y2-y0 is the height of T
    var p2x=p1.x+fb*(p2.x-p0.x);
    var p2y=p1.y+fb*(p2.y-p0.y);  
    return [ new Point( p1x, p1y ), new Point( p2x, p2y ) ];
}

// trigonometric functions
// degrees to radians.
Math.radians = function(degrees) {
  return degrees * Math.PI / 180;
};
 
// radians to degrees.
Math.degrees = function(radians) {
  return radians * 180 / Math.PI;
};

// classes 
// class TEBorders (TokenEditBorders)
function TEBorders(a, color) { // a = TEDrawingArea
	this.borders = new Path.Rectangle(a.upperLeft, a.lowerRight);
	this.borders.strokeColor = color;
	this.borders.strokeWidth = 0.5;
	return this.borders;
}

// class TEDottedGrid
function TEDottedGrid(a, color) {
	// draw grid as dotted lines
	this.allDottedLines = [];
	var deltaX = a.scaleFactor,
		index = 0
		actX = 1;
		dasharrayDottedBase = [1, a.scaleFactor-1];
		dasharrayDotted = [0, a.scaleFactor]
	// build dasharray
	for (var i=1; i<a.lineHeight; i++) {
		dasharrayDotted.push( dasharrayDottedBase[0]);
		dasharrayDotted.push( dasharrayDottedBase[1]);
	}
	//console.log(dasharrayDotted);
	for (var x=a.leftX+deltaX; x<a.rightX; x+=deltaX) {
		if (actX%a.lineHeight !== 0) {
			this.allDottedLines.push( new Path.Line( [ x, a.lowerY ], [ x, a.upperY ]));
			this.allDottedLines[index].strokeColor = color;
			this.allDottedLines[index].dashArray = dasharrayDotted;
			index++;
		}
		actX++;
	}
	return this.allDottedLines;
}

// class TEAuxiliarySystemLines
function TEAuxiliarySystemLines(a, color) { // a = TEDrawingArea
    this.allSystemLines = []; // array of lines
    
    var index = 0,
		baseIndex = a.totalLines - a.basePosition - 1,
		dasharrayStrong = 0,
		dasharrayDotted = [1,1],
		absLineHeight = a.lineHeight * a.scaleFactor;
	
    for (var y=a.upperLeft.y+absLineHeight; y<a.lowerRight.y; y+=absLineHeight) {
		this.allSystemLines.push( new Path.Line( [ a.upperLeft.x, y ], [ a.lowerRight.x, y ]));
		this.allSystemLines[index].strokeColor = color;
		this.allSystemLines[index].dashArray = (baseIndex == index) ? dasharrayStrong : dasharrayDotted;
		index++;
	}
	return this.allSystemLines;
}

// class TEAuxiliaryVerticalLines
function TEAuxiliaryVerticalLines(a, color) {
	this.allVerticalLines = [];
	var index = 0,
		absDeltaX = a.lineHeight*a.scaleFactor;
	for (var x=a.leftX+absDeltaX; x<a.rightX; x+=absDeltaX) {
		this.allVerticalLines.push( new Path.Line([x, a.upperY],[x, a.lowerY]));
		this.allVerticalLines[index].strokeColor = color;
		this.allVerticalLines[index].dashArray = [1,1];
		index++;
	}
	return this.allVerticalLines;
}

// class TECoordinatesLabels
function TECoordinatesLabels(parent) {
	this.parent = parent;
	this.allLabels = [];
	//console.log(this.parent);
	var posX = this.parent.rotatingAxis.centerRotatingAxis.x - ((this.parent.totalLines / 2) * this.parent.lineHeight * this.parent.scaleFactor),
		labelX = - (this.parent.totalLines / 2) * this.parent.lineHeight;
		
	for (var i = 0; i <= this.parent.totalLines; i++) {
		var text = new PointText(new Point(posX, this.parent.lowerY + 20));
		//console.log("posxy: ", posX, this.parent.lowerY+20);
		text.justification = 'center';
		text.fillColor = '#000';
		text.content = labelX;
		this.allLabels.push(text);
		//console.log("PointText: ", text);
		
		labelX += this.parent.lineHeight;	
		//console.log(this.parent.lineHeight);
		posX += this.parent.lineHeight * this.parent.scaleFactor;
	}
	
	console.log(text.style.fontSize);
	var posY = this.parent.upperY + text.style.fontSize / 2,
		labelY = (this.parent.totalLines - this.parent.basePosition) * this.parent.lineHeight;
		
	for (var i = 0; i <= this.parent.totalLines; i++) {
		var text = new PointText(new Point(this.parent.leftX-10, posY));
		//console.log("posxy: ", posX, this.parent.lowerY+20);
		text.justification = 'right';
		text.fillColor = '#000';
		text.content = labelY;
		this.allLabels.push(text);
		//console.log("PointText: ", text);
		
		labelY -= this.parent.lineHeight;	
		//console.log(this.parent.lineHeight);
		posY += this.parent.lineHeight * this.parent.scaleFactor;
	}
	
}


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

// class TEDrawingArea 	
// constructor and properties
function TEDrawingArea(lowerLeft, totalLines, basePosition, lineHeight, scaleFactor) {
	
	// console.log(lowerLeft, totalLines, basePosition, lineHeight, scaleFactor);
	// class properties
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
	this.rotatingAxis = new TERotatingAxis(this, '#0f0');
	this.coordinateLabels = new TECoordinatesLabels(this);
	
	// actual selected item
	this.itemSelected = this;		// can be TERotatingAxis e.g.
	
	// freehand path
	this.fhCircleSelected = null;
	this.fhCircleColor = null;
	this.fhToken = new Path();
	this.fhToken.strokeColor = '#000';
	this.fhCircleList = [];

	return this;
}
// class TEDrawingArea: methods
TEDrawingArea.prototype.calculateFreehandHandles = function() { // a = TEDrawingArea
	// console.log(this.fhToken);
	numberOfPoints = this.fhToken.segments.length;
	for (var i = 1; i < numberOfPoints-1; i++) { // dont calculate 1st and last
			var absHandles = getControlPoints( this.fhToken.segments[i-1].point, this.fhToken.segments[i].point, this.fhToken.segments[i+1].point, 0.5 );
			this.fhToken.segments[i].handleIn = absHandles[0] - this.fhToken.segments[i].point;
			this.fhToken.segments[i].handleOut = absHandles[1] - this.fhToken.segments[i].point;
	}
}
TEDrawingArea.prototype.isPartOfFreehand = function(test) {
	return this.whichCircle(test);
}
TEDrawingArea.prototype.whichCircle = function(circle) {
	var index = null, i = 0;
	for (i = 0; i<this.fhCircleList.length; i++) {
		if (this.fhCircleList[i] == circle) {
			index = i;
			//console.log("Match for circle: fhCircleList[" + i + "] = " + this.fhCircleList[i] + "<=?=>" + circle + "=> " + index);
			break;
		} //else console.log("search for circle: fhCircleList[" + i + "] = " + this.fhCircleList[i] + "<=?=>" + circle + "=> " + index);		    
	}
	return index;
}
TEDrawingArea.prototype.isInsideBorders = function( event ) {
	if ((this.leftX <= event.point.x) && (this.rightX >= event.point.x) && (this.lowerY >= event.point.y) && (this.upperY <= event.point.y)) return true;
	else return false;
}
TEDrawingArea.prototype.isPartOfFreehandOrRotatingAxis = function( item ) {
	if ((this.isPartOfFreehand(item) != null) || (item == this.rotatingAxis.controlCircle)) return true;
	else return false;
}
TEDrawingArea.prototype.handleMouseDown = function( event ) {
	//console.log("In onMouseDown");
	//console.log(event.item,this.isPartOfFreehandOrRotatingAxis(event.item));
	if ((event.item != null) && (this.isPartOfFreehandOrRotatingAxis(event.item))) { //(this.fhCircleSelected))) {
		//this.itemSelected = (event.item == this.rotatingAxis.controlCircle) ? this.rotatingAxis : this;
		//this.fhCircleSelected = event.item;
		this.fhCircleSelected = event.item;
		this.fhCircleColor = this.fhCircleSelected.fillColor;
		this.fhCircleSelected.fillColor = '#aaa';			
	} else {
		var path = new Path.Circle( {
				center: event.point,
				radius: 5,
				fillColor: '#f00'	
			});
		this.fhCircleList.push( path );
		//console.log(this.fhCircleList);
		this.fhCircleSelected = path;
		this.fhCircleColor = this.fhCircleSelected.fillColor;
		// add token data (relative to rotating axis)
		this.rotatingAxis.token.middle.push( new TERotatingAxisTokenPoint( event.point, 0.5, 0.5, "horizontal", this.rotatingAxis ));
		//console.log("Editor: ", this);
		// add bezier to freehand path
		this.fhToken.add( event.point );
		this.calculateFreehandHandles();	
	}
}
TEDrawingArea.prototype.handleMouseUp = function( event ) {
	//console.log("In onMouseUp");
	if (this.fhCircleSelected != null) {
		this.fhCircleSelected.fillColor = this.fhCircleColor;
		this.fhCircleSelected = null;
		this.itemSelected = this;
	}
	//console.log(this);
}
TEDrawingArea.prototype.handleMouseDrag = function( event ) {
	//console.log("In onMouseDrag");
	if (editor.fhCircleSelected != null) {
		index = this.whichCircle( this.fhCircleSelected );
		this.fhCircleSelected.position = event.point; 
		this.fhToken.segments[index].point = this.fhCircleSelected.position;
		// update token data
		this.rotatingAxis.token.middle[index].absolute = event.point;
		this.rotatingAxis.token.middle[index].calculateRelativeCoordinates();
		this.itemSelected = this;
		
		this.calculateFreehandHandles();
	}
}
TEDrawingArea.prototype.handleEvent = function( event ) {
	//console.log("TEDrawingArea.handleEvent()");
	//console.log(event.item);
	if ((this.fhCircleSelected == null) && (event.item != null) && (this.isPartOfFreehandOrRotatingAxis(event.item))) {
		this.itemSelected = (event.item == this.rotatingAxis.controlCircle) ? this.rotatingAxis : this;
		this.fhCircleSelected = event.item;	
	}
	
	if ((this.isInsideBorders(event)) || (event.type == "mouseup")) { 
		//console.log("Ok, it's my business");
		switch (event.type) {
			case "mousedown" :this.itemSelected.handleMouseDown(event); break;
			case "mouseup" : this.itemSelected.handleMouseUp(event); break;
			case "mousedrag" : this.itemSelected.handleMouseDrag(event); break;
		}
	} else {
		//console.log("Thx, but it's not my business");
	}
}

// main
var editor = new TEDrawingArea(new Point(100, 500), 4, 1, 10, 10);

// global event handlers
tool.onMouseDown = function( event ) {
	editor.handleEvent(event);
}
tool.onMouseDrag = function( event ) {
	editor.handleEvent( event );
}
tool.onMouseUp = function( event ) {
	editor.handleEvent( event );
}

//console.log(editor);
