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
function getControlPoints( p0, p1, p2, t1, t2) {
    var d01=Math.sqrt(Math.pow(p0.x-p1.x,2)+Math.pow(p1.y-p0.y,2));
    var d12=Math.sqrt(Math.pow(p2.x-p1.x,2)+Math.pow(p2.y-p1.y,2));
    var fa=t1*d01/(d01+d12);   // scaling factor for triangle Ta
    var fb=t2*d12/(d01+d12);   // ditto for Tb, simplifies to fb=t-fa
    var p1x=p1.x-fa*(p2.x-p0.x);    // x2-x0 is the width of triangle T
    var p1y=p1.y-fa*(p2.y-p0.y);    // y2-y0 is the height of T
    var p2x=p1.x+fb*(p2.x-p0.x);
    var p2y=p1.y+fb*(p2.y-p0.y);  
    return [ new Point( p1x, p1y ), new Point( p2x, p2y ) ];
}

function calculateBezierPoint(p1, c1, p2, c2, percent) {
	// considers length of complete bezier curve as 100%
	// calculates point situated at percent percent of the curve
	// returns coordinates of point and m of tangent
	// calculate 3 outer lines 
	//console.log("-------------------------------------------------------------------------");
	//console.log("calculateBezierPoint(("+ p1.x +","+p1.y+"), ("+c1.x+","+c1.y+"), ("+p2.x+","+p2.y+"), ("+c2.x+","+c2.y+"), "+percent+"%)");
	var dx1 = c1.x - p1.x,
		dy1 = c1.y - p1.y,
		dx2 = c2.x - c1.x,
		dy2 = c2.y - c1.y,
		dx3 = p2.x - c2.x,
		dy3 = p2.y - c2.y;
		//m1 = dx1 / dy1,
		//m2 = dx2 / dy2,
		//m3 = dx3 / dy3;
	/*outerLines.removeSegments();
	outerLines.add(p1, c1, c2, p2);
	outerLines.strokeColor = '#f00';
	*/
	// calculate 2 inner lines
	// coordinates
	var factor = 1 / 100 * percent;
	//console.log("factor: ", factor);
	var ix1 = p1.x + dx1 * factor,
		iy1 = p1.y + dy1 * factor,
		ix2 = c1.x + dx2 * factor,
		iy2 = c1.y + dy2 * factor,
		ix3 = c2.x + dx3 * factor,
		iy3 = c2.y + dy3 * factor;
	//console.log("inner lines (ix123, iy123): (("+ix1+","+iy1+"), ("+ix2+","+iy2+"), ("+ix3+","+iy3+")");
	/*innerLines.removeSegments(); 
	innerLines.add(new Point(ix1, iy1), new Point(ix2, iy2), new Point(ix3, iy3));
	innerLines.strokeColor = '#00f';
	*/
	// deltas
	var dix1 = ix2 - ix1,
		diy1 = iy2 - iy1,
		dix2 = ix3 - ix2,
		diy2 = iy3 - iy2;
	// calculate last inner line that touches bezier curve
	// coordinates
	var tx1 = ix1 + dix1 * factor,
		ty1 = iy1 + diy1 * factor,
		tx2 = ix2 + dix2 * factor,
		ty2 = iy2 + diy2 * factor;
	//console.log("tangent line (tx12, ty12): (("+tx1+","+ty1+"), ("+tx2+","+ty2+")");
	/*tangent.removeSegments();
	tangent.add(new Point(tx1, ty1), new Point(tx2, ty2));
	tangent.strokeColor = '#000';
	*/
	// deltas
	var dtx = tx2 - tx1,
		dty = ty2 - ty1; // avoid division by 0 for bm!?
	// calculate bezier point (coordinates and m)
	//console.log("Tangent data: tx1, ty1, tx2, ty2: ", tx1, ty1, tx2, ty2);
	var bx = tx1 + dtx * factor,
		by = ty1 + dty * factor,
		bm = dtx / Math.avoidDivisionBy0(dty);
	//console.log("bezierPoint (bx, by, m): (("+bx+","+by+","+bm+")");
	// return values as array
	//bm = isNaN(bm) ? 9999999999999999 : bm;	// sanitize NaN resulting from division by zero above
	return [bx, by, bm];
}

function findTangentPointRelativeToFixPoint(fixPoint, p1, c1, p2, c2, epsilon) {
	//epsilon = 0.001; // change it temporarily
	// DESCRIPTION OF ALGORITHM
	// define the 3 points:
	// - the middle one separates the bezier curve (or the actual segment of it) into two halves
	// - left and right points define start and end of the two segments (halves)
	// the points are defined as percentages (= relative location) on the bezier curve
	// epsilon stands for the precision: delta of straight lines going from connection point
	// to calculated tangent point should be < epsilon (numerical aproximation) 
	//console.log("epsilon: ",epsilon, "tangentFixPointMaxIteration: ", tangentFixPointMaxIteration);
	var leftPercentage = 0.001;			// 0% <=> leftPoint
	var rightPercentage = 99.999;		// 100% <=> rightPoint
	var middlePercentage = 50;		// 50% <=> middlePoint
	var leftPoint = undefined,		// declare point variables
		rightPoint = undefined,
		middlePoint = undefined; 
	
	var avoidInfinityLoop = 0;
	var whichInterval = "start";
	var actualEpsilon = 100;
	leftPoint = calculateBezierPoint(p1, c1, p2, c2, leftPercentage);
	rightPoint = calculateBezierPoint(p1, c1, p2, c2, rightPercentage);
	var cx = fixPoint.x,
		cy = fixPoint.y;
	var cm = undefined,			// declare all variales outside loop = speed optimization?!
		angleLeft = undefined,
		angleRight = undefined,
		angleMiddle = undefined,
		angleConnectionPoint = undefined,
		deltaAngleLeft = undefined,
		deltaAngleMiddle = undefined,
		deltaAngleRight = undefined; 
	var actualPercentageEpsilon = undefined,
	    lastPercentageEpsilon = 100;
		
	do {
		//console.log("Starting loop number "+avoidInfinityLoop+"........................................");
		middlePoint = calculateBezierPoint(p1, c1, p2, c2, middlePercentage);
	
		// calculate m for straight line from connecting point to tangent point
		/* var dx = middlePoint[0] - cx, dy = middlePoint[1] - cy, cm = dx / dy; */
		cm = (middlePoint[0]-cx) / Math.avoidDivisionBy0(middlePoint[1]-cy);
		
		// work with rad angles (easier, but slower)	
		angleLeft = Math.atan(leftPoint[2]); 
		angleMiddle = Math.atan(middlePoint[2]); 
		angleRight = Math.atan(rightPoint[2]); 
		angleConnectionPoint = Math.atan(cm); 
					
		// find out in which interval (left or right) the tangent point is
		deltaAngleLeft = Math.abs(angleConnectionPoint - angleLeft);
		deltaAngleRight = /*Math.abs(angleRight - angleConnectionPoint); */ Math.abs(angleConnectionPoint - angleRight);
//		console.log("delta: left: ", deltaAngleLeft, "right: ", deltaAngleRight);
		if (deltaAngleLeft <= deltaAngleRight) { 
			whichInterval = "left"; 
			//actualEpsilon =  middlePercentage - leftPercentage; // problem: a point is always found! (angle is not taken into consideration)
			deltaAngleMiddle = Math.abs(angleMiddle - angleConnectionPoint);
			
			//actualEpsilon = Math.abs(angleMiddle - angleLeft);
			actualEpsilon = Math.min(deltaAngleLeft, deltaAngleMiddle); //(deltaAngleLeft > deltaAngleMiddle) ? deltaAngleLeft : deltaAngleMiddle;
		} else if (deltaAngleRight < deltaAngleLeft) { 
			whichInterval = "right"; 
			deltaAngleMiddle = Math.abs(angleConnectionPoint - angleMiddle);
			//actualEpsilon = rightPercentage - middlePercentage; //deltaAngleRight; 
			//actualEpsilon = Math.abs(angleRight - angleMiddle);
			actualEpsilon = Math.min(deltaAngleRight, deltaAngleMiddle); // (deltaAngleRight > deltaAngleMiddle) ? deltaAngleRight : deltaAngleMiddle;
		} else { 
			console.log("noidea"); 
			whichInterval = "noidea";
		}
		
		//console.log("Deltas: left: ", deltaAngleLeft, "middle: ", deltaAngleMiddle, "right: ", deltaAngleRight);

		switch (whichInterval) {
			case "left" : rightPercentage = middlePercentage; middlePercentage = (leftPercentage + rightPercentage) / 2;
						  rightPoint = middlePoint;
						  break;
			case "right": leftPercentage = middlePercentage; middlePercentage = (leftPercentage + rightPercentage) / 2; 
					      leftPoint = middlePoint;
					      break;
			case "noidea" : //console.log("noidea");
							//console.log("compare left: "+leftPoint[2].toFixed(2)+" <?> "+cm.toFixed(2)+" <?> "+middlePoint[2].toFixed(2));
							//console.log("compare right: "+middlePoint[2].toFixed(2)+" <?> "+cm.toFixed(2)+" <?> "+rightPoint[2].toFixed(2));
							middlePercentage = (middlePercentage + rightPercentage) / 2; // shift middle point instead	
							break;
			default : avoidInfinity = 1000000000; break;
		}
		avoidInfinityLoop++;
		actualPercentageEpsilon = rightPercentage - leftPercentage;
		
	} while (/*(actualEpsilon > epsilon)*/ /*(actualPercentageEpsilon > 0.1) &&*/ (avoidInfinityLoop < tangentFixPointMaxIterations)); 
	if ((/*actualEpsilon <= epsilon*/actualPercentageEpsilon <=0.1) && (actualEpsilon < 0.1)) {
		//console.log("Point found: ", middlePoint, "Epsilon: ", actualEpsilon);
		return middlePoint;
	} else { 
		//console.log("No point found: Epsilon:", actualEpsilon, avoidInfinityLoop);
		return false;
		//if (actualEpsilon < 0.5) return middlePoint;
		//else return false;
	}
}

// trigonometric functions
// degrees to radians
Math.radians = function(degrees) {
  return degrees * Math.PI / 180;
};
 
// radians to degrees
Math.degrees = function(radians) {
  return radians * 180 / Math.PI;
};

Math.avoidDivisionBy0 = function(value) {
	// mathematically horrible ..., but it does the trick ...
	if (value == 0) return 0.000000000000000001;
	else return value;
}
// fixing the JS typeof operator ... (again: very weak and neary useless concept in JS, in my opinion...)
function toType(obj) {
    if(obj && obj.constructor && obj.constructor.name) {
        return obj.constructor.name;
    }
    return Object.prototype.toString.call(obj).slice(8, -1).toLowerCase();
}

// classes 
// class TEBorders (TokenEditBorders)
function TEBorders(a, color) { // a = TEDrawingArea
	this.borders = new Path.Rectangle(a.upperLeft, a.lowerRight);
	this.borders.strokeColor = color;
	this.borders.strokeWidth = 0.5;
	return this.borders;
}
TEBorders.prototype.isStatic = function() {
	return true;
}
TEBorders.prototype.isDynamic = function() {
	return false;
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
TEDottedGrid.prototype.isStatic = function() {
	return true;
}
TEDottedGrid.prototype.isDynamic = function() {
	return false;
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
TEAuxiliarySystemLines.prototype.isStatic = function() {
	return true;
}
TEAuxiliarySystemLines.prototype.isDynamic = function() {
	return false;
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
TEAuxiliaryVerticalLines.prototype.isStatic = function() {
	return true;
}
TEAuxiliaryVerticalLines.prototype.isDynamic = function() {
	return false;
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
	
	//console.log(text.style.fontSize);
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
TECoordinatesLabels.prototype.isStatic = function() {
	return true;
}
TECoordinatesLabels.prototype.isDynamic = function() {
	return false;
}

// class TEKnotVector
function TEKnotVector(distance, type) {
	this.type = "orthogonal"; // make it fix for the moment (change it to type later)
	this.distance = distance;
	this.line = Path.Line(new Point(0,0), new Point(100,100));
	this.line.strokeColor = '#000';
	this.line.visible = false;
}

// class TEKnotType
function TEKnotType() {
	this.entry = false;
	this.exit = false;
	this.pivot1 = false;
	this.pivot2 = false;
}

// class TEVisuallyModifiableKnot extends TEVisuallyModfiableCircle
function TEVisuallyModifiableKnot(x, y, t1, t2, radius, color, selectedColor, markedColor) {
    this.type = new TEKnotType();
    this.tensions = [t1, t2];
	TEVisuallyModifiableCircle.prototype.constructor.call(this, new Point(x, y), radius, color, selectedColor, markedColor);
}
TEVisuallyModifiableKnot.prototype = new TEVisuallyModifiableCircle(); 	// inherit
TEVisuallyModifiableKnot.prototype.identify = function(item) {
	if (this.circle == item) return this;
	else return null;
}
TEVisuallyModifiableKnot.prototype.setTensions = function(t1, t2) {
	this.tensions = [t1, t2];
}

// class TEEditableToken
function TEEditableToken(drawingArea) {
	// parent
	this.parent = drawingArea;
	// token data
	this.knotsList = []; 	// type: TEVisuallyModifiableKnot
	this.leftVectors = []; 	// type: TEKnotVector
	this.rightVectors = [];
	// paths
	this.middlePath = null; 			// for the moment: fhToken in TEDrawingArea
	this.outerShape = new Path();		// closed path: starting point - leftPath - endPoint - rightPath - starting point
	
	// mouse events
	//this.mouseDown = false;
	this.selectedKnot = null;
	this.markedKnot = null;
	// index (is updated whenever identify-method is called)
	// maybe not a good idea ... index can be undefined or contain obsolete values
	// => use updateIndex for the moment to solve this problem
	this.index = 0;
}
/* // not sure if this is necessary after all ...
TEEditableToken.prototype.updateIndex = function() {
	// uses this.markedKnot to update index
	// returns index
	if (this.markedKnot != null) this.markedKnot.identify(this.markedKnot.circle);
	return index;
}
*/
TEEditableToken.prototype.identify = function(item) {
	//console.log("TEEditableToken: item: ", item);
	var value = null;
	for (var i=0; i<this.knotsList.length; i++) {
		//console.log("TEEditableToken(i): ", i, this.knotsList[i]);
		if ((item == this.knotsList[i].circle) || (item == this.knotsList[i])) { // item can be TEVisuallyModifiableCircle or TEVisuallyModifiableKnot ?!
			this.index = i+1;
			value = this;
			break;
		}
	}
	//console.log("TEEditableToken.identify(): ", value);
	return value;
}
TEEditableToken.prototype.identifyAndSelectKnot = function(item) {
	var value = null;
	for (var i=0; i<this.knotsList.length; i++) {
		if (this.knotsList[i].identify(item) != null) {
			value = this.knotsList[i];
			break;
		}
	}
	//console.log("ActiveKnot = ", value);
	this.selectedKnot = value;
	this.markedKnot = value; // maybe pleonastic (should be handled by parent => see following line)
	this.parent.setMarkedCircle(this.markedKnot);
	// update sliders
	//console.log(this
	this.parent.parent.tensionSliders.setValues(this.selectedKnot.tensions[0], this.selectedKnot.tensions[1]); // ok, this is a monkey jumping from one tree to another ..., but it works ... ;-)
}
TEEditableToken.prototype.handleMouseDown = function(event) {
	this.identifyAndSelectKnot(event.item);
	if (this.selectedKnot != null) {
		this.parent.parent.tensionSliders.link(this.selectedKnot);
		this.selectedKnot.handleMouseDown(event);
		this.parent.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, this.index-1);
	}
}
TEEditableToken.prototype.handleMouseUp = function(event) {
	///*this.*/mouseDown = false;
	if (this.selectedKnot != null) {
		this.selectedKnot.handleMouseUp(event); // catch error (selectedKnot can be null when clicking fast)
		this.parent.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, this.index-1);
	} this.selectedKnot = null;	// leave markedKnot
}
TEEditableToken.prototype.handleMouseDrag = function(event) {
	if (/*this.*/mouseDown) {
		if (this.selectedKnot != null) {
			this.selectedKnot.handleMouseDrag(event);
			// update of relative coordinates not necessary (will be called by handleMouseUp-event)
			//this.parent.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, this.index-1);
		}
	}
}
TEEditableToken.prototype.handleEvent = function(event) {
	switch (event.type) {
		case "mousedown" : if (doubleClick) {
								//this.handleMouseDown(event);
								//console.log("delete this point: ", event.item);
								this.deleteMarkedKnotFromArray();
						   } else this.handleMouseDown(event); 
						   break;
		case "mouseup" : this.handleMouseUp(event); break;
		case "mousedrag" : this.handleMouseDrag(event); break;
	}
	//this.parent.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, this.index-1);
}
TEEditableToken.prototype.redefineKnotTypesAndSetColors = function() {
	// reset all knot types
	for (var i=0; i<this.knotsList.length; i++) {
		this.knotsList[i].type.entry = false;
		this.knotsList[i].type.exit = false;
		this.knotsList[i].type.pivot1 = false;
		this.knotsList[i].type.pivot1 = false;
		this.knotsList[i].circle.fillColor = colorNormalKnot;
	}
	// set new types
	this.knotsList[0].type.entry = true;
	this.knotsList[this.knotsList.length-1].type.exit = true;
	indexP1 = (this.knotsList.length > 2) ? 1 : 0;
	indexP2 = (this.knotsList.length > 2) ? this.knotsList.length-2: this.knotsList.length-1;
	this.knotsList[indexP1].type.pivot1 = true;
	this.knotsList[indexP2].type.pivot2 = true;
	// set colors
	this.knotsList[indexP1].circle.fillColor = colorPivot1;
	this.knotsList[indexP2].circle.fillColor = colorPivot2;
	this.knotsList[0].circle.fillColor = colorEntryKnot;	// if pivot color has been set before, it will be overwritten
	this.knotsList[this.knotsList.length-1].circle.fillColor = colorExitKnot;
}
TEEditableToken.prototype.getNewKnotTypeColor = function() {
	// knot will be inserted after this.index
	//console.log("new knot: index/length: ", index, this.knotsList.length);
	var index = this.index;
	var length = this.knotsList.length;
	var value = null;
	if (index == length) { /*console.log("exitKnot");*/ return colorExitKnot; }
	else if (index == 0) { /*console.log("entryKnot");*/ return colorEntryKnot; }
	else if (index == 1) { /*console.log("pivot1Knot");*/ return colorPivot1; }
	else if (index == length-1) { /*console.log("pivot2Knot");*/ return colorPivot2; }
	else { /*console.log("normalKnot");*/ return colorNormalKnot; }
}
TEEditableToken.prototype.getDeleteKnotTypeColor = function() {
	// knot will be deleted at this.index
	var index = this.index;
	var length = this.knotsList.length;
	var value = null;
	//console.log("delete knot: index/length: ", index, this.knotsList.length);
	if (index == length) { /*console.log("exitKnot");*/ return colorExitKnot; }
	else if (index == 1) { /*console.log("entryKnot");*/ return colorEntryKnot; }
	else if (index == 2) { /*console.log("pivot1Knot");*/ return colorPivot1; }
	else if (index == length-1) { /*console.log("exitKnot");*/ return colorExitKnot; }
	else { /*console.log("normalKnot");*/ return colorNormalKnot; }
}
TEEditableToken.prototype.insertNewKnot = function(point) {
	// get color of new knot before inserting it
	var newColor = this.getNewKnotTypeColor();
	// insert knot
	var newKnot = new TEVisuallyModifiableKnot(point.x, point.y, 0.5, 0.5, 5, newColor, colorSelectedKnot, colorMarkedKnot);
	this.knotsList.splice(this.index, 0, newKnot);
	// insert vectors for outer shape
	var distance = (this.index == 0) ? 0 : 1; 	// 0 = no pencil thickness, 1 = maximum thickness
	var leftVector = new TEKnotVector(distance, "orthogonal");
	var rightVector = new TEKnotVector(distance, "orthogonal");
	this.leftVectors.splice(this.index,0, leftVector);
	this.rightVectors.splice(this.index,0, rightVector);
	// automatically define knot type if autodefine is set
	if (knotTypeAutoDefine) this.redefineKnotTypesAndSetColors();
	// select new knot as actual knot
	this.selectedKnot = newKnot;
	// link tension slider to new knot
	this.parent.parent.tensionSliders.link(this.selectedKnot);
	// set marked knot and handling parent
	this.markedKnot = newKnot; // maybe superfluous
	this.parent.setMarkedCircle(newKnot);
	this.parent.handlingParent = this;
	// insert relative knot in rotating axis relativeToken
	this.parent.rotatingAxis.relativeToken.insertNewRelativeKnot(point.x, point.y, "horizontal", this.index);
	// make index point to new knot
	this.index += 1; // point to the newly inserted element
	// update connections from preceeding and following connection point
	this.connectPreceedingAndFollowing();
}
TEEditableToken.prototype.deleteMarkedKnotFromArray = function() {
	// marked knot can be identified by index in editable token
	// set new selected / marked knot before deleting the actual knot
	var end = this.knotsList.length;
	switch (this.index) {
		case end : this.selectedKnot = this.knotsList[end-2]; this.markedKnot = this.selectedKnot; break;
		default : this.selectedKnot = this.knotsList[this.index]; this.markedKnot = this.selectedKnot; break;
	}
	// get color of knot that will be deleted, assure colors are set correctly and mark knot
	var color = this.getDeleteKnotTypeColor();
	this.selectedKnot.originalColor = color;
	this.selectedKnot.circle.fillColor = color;
	this.parent.setMarkedCircle(this.selectedKnot);
	// remove: circle, knot, lines and vectors
	// bug: there's something wrong with the lines (they remain on drawing area as zombies ... ;-)
	this.knotsList[this.index-1].circle.remove(); // make control circle invisible (should be deleted)
	this.knotsList.splice(this.index-1, 1); // deletes 1 element at index and reindexes array
	this.leftVectors[this.index-1].line.removeSegments();
	this.leftVectors.splice(this.index-1, 1);
	this.rightVectors[this.index-1].line.removeSegments();
	this.rightVectors.splice(this.index-1, 1);
	// remove also relative knot in relative token (rotating axis)
	this.parent.rotatingAxis.relativeToken.knotsList.splice(this.index-1,1); // do the same with relative token
	this.parent.fhToken.removeSegment(this.index-1); // do the same with path
	// automatically define knot type with autodefine
	if (knotTypeAutoDefine) this.redefineKnotTypesAndSetColors();
	// update
	this.parent.updateFreehandPath();
	this.connectPreceedingAndFollowing();
}
TEEditableToken.prototype.connectPreceedingAndFollowing = function() {
	this.parent.connectPreceedingAndFollowing();	
}


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
/*
TEVisuallyModifiableCircle.prototype.handleEvent = function(event) {
switch (event.type) {
		case "mousedown" : this.handleMouseDown(event); break;
		case "mouseup" : this.handleMouseUp(event); break;
		case "mousedrag" : this.handleMouseDrag(event); break;
	}	
}*/
TEVisuallyModifiableCircle.prototype.handleMouseDown = function(event) {
	//console.log("TEVisuallyModifiableCircle.handleMouseDown");
	this.mark();
	this.select();
	this.circle.position = event.point;
}
TEVisuallyModifiableCircle.prototype.handleMouseDrag = function(event) {
	//console.log("mousedrag");
	this.circle.position = event.point;
}
TEVisuallyModifiableCircle.prototype.handleMouseUp = function(event) {
	//console.log("TEVisuallyModifiableCircle.handleMouseUp()");
	this.circle.position = event.point;
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
	var curve1ActualFixPoint = calculateBezierPoint(curve1P1, curve1C1, curve1P2, curve1C2, curve1MiddlePercentage); 
	var curve2ActualFixPoint = calculateBezierPoint(curve2P1, curve2C1, curve2P2, curve2C2, curve2MiddlePercentage); 
	//curve1ActualFixPoint = [curve1P1.x, curve1P1.y, 0];
	//curve2ActualFixPoint = [curve2P2.x, curve2P2.y, 0];
	
	var numberIterations = 0;
	var curve2NewTangentPoint = undefined;
	var curve1NewTangentPoint = undefined;
	var curve1Distance = undefined;
	var curve2Distance = undefined;
	
	do {
		// calculate tangent point on curve2
		curve2NewTangentPoint = findTangentPointRelativeToFixPoint(new Point(curve1ActualFixPoint[0], curve1ActualFixPoint[1]), curve2P1, curve2C1, curve2P2, curve2C2, 0.0001 ); // use global function
		//curve2Distance = new Point(curve2NewTangentPoint[0], curve2NewTangentPoint[1]).getDistance(new Point(curve2ActualFixPoint[0], curve2ActualFixPoint[1]));
		
		// use tangent point on curve2 as new fix point and calculate tangent for curve1
		curve1NewTangentPoint = findTangentPointRelativeToFixPoint(new Point(curve2NewTangentPoint[0], curve2NewTangentPoint[1]), curve1P1, curve1C1, curve1P2, curve1C2, 0.0001 );
		//curve1Distance = new Point(curve1NewTangentPoint[0], curve1NewTangentPoint[1]).getDistance(new Point(curve1ActualFixPoint[0], curve1ActualFixPoint[1]));
				
		//console.log("ActualFixPoints: ", curve1ActualFixPoint, curve2ActualFixPoint);
		//console.log("NewFixPoints: ", curve1NewTangentPoint, curve2NewTangentPoint);
		
		curve1ActualFixPoint = curve1NewTangentPoint; // (curve1NewTangentPoint != false) ? curve1NewTangentPoint : curve1ActualFixPoint;
		curve2ActualFixPoint = curve2NewTangentPoint; //(curve2NewTangentPoint != false) ? curve2NewTangentPoint : curve2ActualFixPoint;
		
		// calculate epsilon
		//actualEpsilon = curve1Distance + curve2Distance;
		//console.log("Iteration ", numberIterations, "Distances: ", curve1Distance, curve2Distance, "Epsilon: ", actualEpsilon);
		
		numberIterations++;
		
	} while (/*(actualEpsilon > epsilon) &&*/ (numberIterations < tangentBetweenCurvesMaxIterations));
	
	// show result
	tangent.removeSegments();
	tangent.add(new Point(curve1ActualFixPoint[0], curve1ActualFixPoint[1]), new Point(curve2ActualFixPoint[0], curve2ActualFixPoint[1]));
	tangent.strokeColor = '#000';
	return true;
}
/////// test new method for tangent calculation with preceeding point
/////// test 2nd bezier segment for the moment
/////// (should be applied to following point and several segments later)
TEConnectionPointPreceeding.prototype.findTangentPointRelativeToConnectionPoint = function(p1, c1, p2, c2, epsilon) {
	//console.log("findTangentPointRelativeToConnectionPoint - epsilon: ", epsilon);
	/*var p1 = this.parent.editableToken.knotsList[1].circle.position,
		c1 = p1 + this.parent.fhToken.segments[1].handleOut,     // control points are RELATIVE coordinates
		p2 = this.parent.editableToken.knotsList[2].circle.position,
		c2 = p2 + this.parent.fhToken.segments[2].handleIn;	
	*/
	var cx = this.circle.position.x,
		cy = this.circle.position.y;
	//console.log("Hi there1.");
	return findTangentPointRelativeToFixPoint(new Point(cx, cy), p1, c1, p2, c2, epsilon);
}
/////////////////////// end of experimental function
TEConnectionPointPreceeding.prototype.connect = function() {
	
	/* // disable this code for the moment
	if (this.parent.editableToken.knotsList.length > 1) {
		var p1 = this.parent.editableToken.knotsList[0].circle.position,
			c1 = p1 + this.parent.fhToken.segments[0].handleOut,     // control points are RELATIVE coordinates
			p2 = this.parent.editableToken.knotsList[1].circle.position,
			c2 = p2 + this.parent.fhToken.segments[1].handleIn;
		//var result = calculateBezierPoint(p1, c1, p2, c2, 50);
			
		//var bezierPoint = new Point(result[0], result[1]);
		//console.log("tangentPrecision = ", tangentPrecision);
		var result2 = this.findTangentPointRelativeToConnectionPoint(p1, c1, p2, c1, tangentPrecision);
		//console.log("result2: ",result2);
		
		if (result2 != false) {
			this.line.removeSegments();
			this.line.add( this.circle.position, new Point(result2[0], result2[1]));
			this.line.visible = true;
		} else this.line.visible = false;
	}
	*/
	
	// for test purposes: calculate tangents between to bezier segments (choose segments 2 and 6 from freehand curve)
	/* // disable this code for the moment
	if (this.parent.editableToken.knotsList.length > 6) {
		var result3 = this.findTangentPointsBetweenCurves2And6(tangentPrecision);
	
	}
	*/
	// use this code for the moment: connect to first point of freehand path
	if (this.parent.editableToken.knotsList.length > 0) {
		this.line.segments[0].point = this.circle.position;
		this.line.segments[1].point = this.parent.editableToken.knotsList[0].circle.position;
	}
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
	var length = this.parent.editableToken.knotsList.length;
	if (length > 0) {
		this.line.segments[0].point = this.circle.position;
		this.line.segments[1].point = this.parent.editableToken.knotsList[length-1].circle.position;
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
	this.rotatingAxis = new TERotatingAxis(this, '#0f0');
	this.coordinateLabels = new TECoordinatesLabels(this); // coordinateLabels depends on rotatingAxis!
	this.preceeding = new TEConnectionPointPreceeding(this, this.leftX+10, this.rotatingAxis.centerRotatingAxis.y);
	this.following =  new TEConnectionPointFollowing(this, this.rightX-10, this.rotatingAxis.centerRotatingAxis.y);
	this.knotLabel = new TEKnotLabel(this);
	
	// mouse events
	//this.mouseDown = false;
	this.mouseDownItem = null;
	this.handlingParent = null;
	
	// token that is edited
	this.actualToken = new TEEditableToken();
	
// actual selected itemsModifiableCircle
	this.markedIndex = 0;			// 0 = preceeding connection point; 1,2,3 ... n = freehand circles; 99999 = following connection point

	// freehand path
	this.fhCircleSelected = null;
	this.fhCircleColor = null;
	this.editableToken = new TEEditableToken(this);
	this.fhToken = new Path();
	//this.fhToken.fullySelected = true;
	this.fhToken.strokeColor = '#000';

	// initialize marked circle and index
	// following line throws an error => why?!?!?!?!?
    this.setMarkedCircle(this.preceeding);
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
	} //else this
	//console.log("index set to: ", this.editableToken.index);
}
TEDrawingArea.prototype.calculateFreehandHandles = function() {
	numberOfPoints = this.fhToken.segments.length;
	//console.log("calculateFreehandHandles");
	// do first knot separately (add "virtual" 3rd knot at beginning which is identical with 1st knot)
	if (numberOfPoints > 1) { // only if there are at least 2 knots
		//console.log("calculate 1st");
		var t1 = this.editableToken.knotsList[0].tensions[0];
		var t2 = this.editableToken.knotsList[0].tensions[1];
		var absHandles = getControlPoints(this.fhToken.segments[0].point, this.fhToken.segments[0], this.fhToken.segments[1], t1, t2);
		this.fhToken.segments[0].handleIn = absHandles[0] - this.fhToken.segments[0].point;
		this.fhToken.segments[0].handleOut = absHandles[1] - this.fhToken.segments[0].point;
	}
	for (var i = 1; i < numberOfPoints-1; i++) { // dont calculate 1st and last
			var t1 = this.editableToken.knotsList[i].tensions[0];
			var t2 = this.editableToken.knotsList[i].tensions[1];
			var absHandles = getControlPoints( this.fhToken.segments[i-1].point, this.fhToken.segments[i].point, this.fhToken.segments[i+1].point, t1, t2 );
			this.fhToken.segments[i].handleIn = absHandles[0] - this.fhToken.segments[i].point;
			this.fhToken.segments[i].handleOut = absHandles[1] - this.fhToken.segments[i].point;
	}
	// do last knot separately (add "virtual" 3rd knot at end which is identical with last knot)
	if (numberOfPoints > 1) { // only if there are at least 2 knots
		//console.log("calculate last");
		var last = this.editableToken.knotsList.length-1;
		var t1 = this.editableToken.knotsList[last].tensions[0];
		var t2 = this.editableToken.knotsList[last].tensions[1];
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
TEDrawingArea.prototype.calculateLeftRightVectors = function() {
	// start with left vectors
	var length = this.editableToken.knotsList.length;
	for (var i=1; i<length-1; i++) {
		var tempPosition = this.editableToken.knotsList[i].circle.position;
		this.editableToken.leftVectors[i].line.removeSegments();
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
		var newLength = 20; // 10 pixels
		var endPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// vector startpoint (outside circle)
		newLength = 6;
		var startPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// draw left vector
		this.editableToken.leftVectors[i].line.add( startPoint, endPoint); //[newX,newY]);
		this.editableToken.leftVectors[i].line.strokeColor = '#000';
		this.editableToken.leftVectors[i].line.visible = true;
		// calculate new coordinates for right shape
		// flip vector by 180 degrees <=> negate x and y
		vectorY = -vectorY;
		vectorX = -vectorX;
		// vector endpoint
		newLength = 20; // 10 pixels
		endPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// vector startpoint (outside circle)
		newLength = 6;
		startPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// draw right vector
		this.editableToken.rightVectors[i].line.removeSegments();
		this.editableToken.rightVectors[i].line.add( startPoint, endPoint); //[newX,newY]);
		this.editableToken.rightVectors[i].line.strokeColor = '#000';
		this.editableToken.rightVectors[i].line.visible = true;
	}
}
TEDrawingArea.prototype.calculateOuterShapeHandles = function() {
	var length = this.editableToken.knotsList.length;
	// set control points of entry knot to (0,0)
	this.editableToken.outerShape.segments[0].handleIn = 0;
	this.editableToken.outerShape.segments[0].handleOut = 0;
	//console.log("Starting point: ", this.editableToken.outerShape.segments[0].point);
	// recalculate handles of left shape
	var p0, p1, p2, t1, t2, controlPoints, rc1, rc2;
	for (var i=1; i<length-1; i++) {
		// get 3 points for control point calculation (preceeding, actual, following)
		p0 = this.editableToken.outerShape.segments[i-1].point;
		p1 = this.editableToken.leftVectors[i].line.segments[1].point;
		p2 = this.editableToken.outerShape.segments[i+1].point;
		// get tensions
		t1 = this.editableToken.knotsList[i].tensions[0];
		t2 = this.editableToken.knotsList[i].tensions[1];
		// calculate control points
		controlPoints = getControlPoints(p0, p1, p2, t1, t2);
		//console.log("getControlPoints: ", p0, p1, p2, t1, t2);
		// values are absolute => make them relative and copy them to segment
		rc1 = controlPoints[0]-p1;
		rc2 = controlPoints[1]-p1;
		this.editableToken.outerShape.segments[i].handleIn = rc1;
		this.editableToken.outerShape.segments[i].handleOut = rc2;
		//console.log("object: ",i,  this.editableToken.leftVectors[i].line.segments[1].point);
	}
	
	// set control points of exit knot to (0,0)
	this.editableToken.outerShape.segments[length-1].handleIn = 0;
	this.editableToken.outerShape.segments[length-1].handleOut = 0;
	
	// calculate right shape control points (backwards)
	//console.log("End point: ", this.editableToken.outerShape.segments[length-1].point);
	var continueIndex = length;
	for (var i=length-2; i>0; i--) {
		// get 3 points for control point calculation (preceeding, actual, following)
		p0 = this.editableToken.outerShape.segments[continueIndex-1].point;  
		p1 = this.editableToken.outerShape.segments[continueIndex].point;
		// modulo = wrap around at the end (lenght of outerShape = 2*length - 2, because start/end points are only inserted 1x)
		p2 = this.editableToken.outerShape.segments[(continueIndex+1)%(2*length-2)].point;
		// get tensions
		t1 = this.editableToken.knotsList[i].tensions[1];	// tensions have to be inversed 
		t2 = this.editableToken.knotsList[i].tensions[0];	// due to backwards calculation
		// calculate control points
		controlPoints = getControlPoints(p0, p1, p2, t1, t2);
		//console.log("getControlPoints: ", continueIndex, p0, p1, p2, t1, t2);
		// values are absolute => make them relative and copy them to segment
		rc1 = controlPoints[0]-p1;
		rc2 = controlPoints[1]-p1;
		this.editableToken.outerShape.segments[continueIndex].handleIn = rc1;
		this.editableToken.outerShape.segments[continueIndex].handleOut = rc2;		
		continueIndex++;
	}
	// starting point control point values have already been written
}
TEDrawingArea.prototype.calculateOuterShape = function() {
	var length = this.editableToken.knotsList.length;
	this.editableToken.outerShape.removeSegments();
	// add first segment = entry point
	this.editableToken.outerShape.add(this.editableToken.knotsList[0].circle.position);
	// add points of left shape
	var tempPoint, handleIn, handleOut;
	for (var i=1; i<length-1; i++) {
		tempPoint = this.editableToken.leftVectors[i].line.segments[1].point;
		handleIn = this.fhToken.segments[i].handleIn;	// not correct
		handleOut = this.fhToken.segments[i].handleOut;	// not correct
		this.editableToken.outerShape.add(new Segment(tempPoint, handleIn, handleOut));
	//console.log("object: ",i,  this.editableToken.leftVectors[i].line.segments[1].point);
	}
	
	// add end point
	this.editableToken.outerShape.add(this.editableToken.knotsList[length-1].circle.position);
	// add right shape backwards
	var tempPoint, handleIn, handleOut;
	for (var i=length-2; i>0; i--) {
		tempPoint = this.editableToken.rightVectors[i].line.segments[1].point;
		// inverse handleIn / handleOut (since elements are inserted backwards)
		handleOut = this.fhToken.segments[i].handleIn; // not correct
		handleIn = this.fhToken.segments[i].handleOut; // not correct
		this.editableToken.outerShape.add(new Segment(tempPoint, handleIn, handleOut));			
	}
	// no need to add starting point again => just close path
	this.editableToken.outerShape.closePath();

	// set color
	this.editableToken.outerShape.strokeColor = '#000';

	// it's not correct to copy the control points (handles) from the middle path,
	// outer paths are different (only tensions are equal)!
	// Therefore: take the TENSIONS of the middle path and recalculate the handles
	// Do this in 2 steps for the moment (in order to be able to compare differences,
	// later this calculation can be integrated inside the for-loops above
	this.calculateOuterShapeHandles();
}
TEDrawingArea.prototype.updateFreehandPath = function() {
	this.copyKnotsToFreehandPath();
	this.calculateLeftRightVectors();
	this.calculateOuterShape();
	this.calculateFreehandHandles();
}
TEDrawingArea.prototype.isInsideBorders = function( event ) {
	if ((this.leftX <= event.point.x) && (this.rightX >= event.point.x) && (this.lowerY >= event.point.y) && (this.upperY <= event.point.y)) return true;
	else return false;
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
		//this.fhToken.add( event.point ); // add point at the end for the moment ...
		this.editableToken.insertNewKnot(event.point);
		//this.editableToken.index += 1; // point to the newly inserted element
		
		//var length = this.rotatingAxis.relativeToken.knotsList.length;
		//this.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, length);		
	}
	this.connectPreceedingAndFollowing();
	//this.preceeding.connect();
/*	this.following.connect();
*/
}
TEDrawingArea.prototype.handleMouseUp = function( event ) {
	if (this.handlingParent != null) {
		this.handlingParent.handleEvent(event);
	}
	///*this.*/mouseDown = false;
	this.mouseDownItem = null;
	this.handlingParent = null;
}
TEDrawingArea.prototype.handleMouseDrag = function( event ) {
	if (this.handlingParent != null) {
		this.handlingParent.handleEvent(event);	
		//var length = this.rotatingAxis.relativeToken.knotsList.length;
		//this.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, length);
		
	}
	this.connectPreceedingAndFollowing();
	
		//this.preceeding.connect(); // update connecting point also
/*		this.following.connect(); // update connecting point also
*/
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
TEDrawingArea.prototype.isDynamic = function(item) {
	
}
TEDrawingArea.prototype.isStatic = function(item) {
}

TEDrawingArea.prototype.handleEvent = function(event) {
	//console.log("TEDrawingArea.handleEvent()", event.item);
	if ((event.point.x >= this.leftX) && (event.point.x <= this.rightX) && (event.point.y >= this.upperY) && (event.point.y <= this.lowerY)) {	
		switch (event.type) {
			case "mousedown" : this.handleMouseDown(event); break;
			case "mouseup" : this.handleMouseUp(event); break;
			case "mousedrag" : this.handleMouseDrag(event); break;
		}
		//var index = this.rotatingAxis.relativeToken.index;
		//this.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, index);
		this.updateFreehandPath();
		this.knotLabel.updateLabel();
/*	
	if ((event.item != null) || (this.mouseItem != null)) {
		//console.log("GetTDrawingAreaObjet: ", this.getTEDrawingAreaObject(event.item));
		
		if (event.type == "mousedown") { 
			console.log("Handling parent: ", this.handlingParent);
			//console.log("mousedown => set variables");
			this.mouseDown = true;
			this.mouseItem = event.item;
			this.handlingParent = this.getTEDrawingAreaObject(event.item);
			//console.log("event.item: ", event.item);
			//console.log("Handling parent: ", this.handlingParent);
		} else if (event.type == "mouseup") {
			console.log("Handling parent: ", this.handlingParent);
			if (this.handlingParent != null) {
				this.handlingParent.handleEvent(event);
			}
			//console.log("mouseup => set variables");
			this.mouseDown = false;
			this.mouseDownItem = null;
			this.handlingParent = null;
		} 
		//console.log("Handling parent: ", this.handlingParent);
		//console.log("TEDrawingArea.mouseDown: ", this.mouseDown);
		//if (this.mouseDown) {	
			//console.log("Handle this event: ", this.mouseDownItem, "toType: ", toType(this.mouseDownItem));
			if (this.handlingParent != null) {
				//console.log("Handling parent: ", this.handlingParent);
				this.handlingParent.handleEvent(event);
			}
			
			//this.mouseDownItem.handleEvent(event);
			
			/*
			if ((this.fhCircleSelected == null) && (event.item != null) && (this.isDragableCircle(event.item))) {
				switch (event.item) {
					case this.rotatingAxis.controlCircle : this.itemSelected = this.rotatingAxis; break;
					case this.preceeding.circle : this.itemSelected = this.preceeding; break;
					case this.following.circle : this.itemSelected = this.following; break;
					default : this.itemSelected = this;
				}
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
			}*/
		//} else {
			//console.log("Don't react to mouse events if mouseDown == false");
		//}
/*	} else {
		if ((event.item != null) || (event.item.isStatic())) { // hoping that JS evaluates or expressions sequentially ... otherwise the second expression might throw and error ...
			console.log("Insert new point");
		
			this.editableToken.insertNewKnot(event.point);
		}
	}
*/
	}
}
TEDrawingArea.prototype.connectPreceedingAndFollowing = function() {
	this.preceeding.connect();
	this.following.connect();	
}


// main classes
// class TECanvas (main container for complete drawing area)
function TECanvas(x, y, width, height) {
	// properties
	this.x = x;
	this.y = y;
	this.width = width;
	this.height = height;
	// objects
	// main editor
	this.editor = new TEDrawingArea(this, new Point(100, 500), 4, 1, 10, 10);
	// sliders
	this.tensionSliders = new TETwoGroupedTensionSliders(this, this.editor.rightX+10, this.editor.upperY, 80, this.editor.lowerY-this.editor.upperY);
}
TECanvas.prototype.handleEvent = function(event) {
	//console.log("TECanvas.handleEvent()");
	if ((event.point.x >= this.x) && (event.point.x <= this.x+this.width) && (event.point.y >= this.y) && (event.point.y <= this.y+this.height)) {
		// instead of identifying object, call all event handlers
		this.editor.handleEvent(event);
		this.tensionSliders.handleEvent(event);
	}
	//this.crossUpdateSliderAndFreehandCurve();
}
//TECanvas.prototype.crossUpdateSliderAndFreehandCurve() {
	
//}

// main
// auxiliary lines to test bezier curves
var outerLines = new Path();
var innerLines = new Path();
var tangent = new Path();

//var editor = new TEDrawingArea(new Point(100, 500), 4, 1, 10, 10);
var mainCanvas = new TECanvas(0,0,800,600);
	
// global event handlers and variables
var lastClick = null,
	doubleClickInterval = 500, // milliseconds
	doubleClick = false,
	mouseDown = false;

// global variables
var knotTypeAutoDefine = true,
	colorEntryKnot = '#00f',
	colorExitKnot = colorEntryKnot,
	colorPivot1 = '#f0f',
	colorPivot2 = colorPivot1,
	colorNormalKnot = '#f00',
	colorSelectedKnot = '#aaa',
	colorMarkedKnot = '#FFFC00';
	
var tangentPrecision = 0.001,
	tangentFixPointMaxIterations = 200,
	tangentBetweenCurvesMaxIterations = 4;

tool.onMouseDown = function(event) {
	var newClick = (new Date).getTime();
	mouseDown = true;
	//console.log("lastclick: ", lastClick, " newClick: ", newClick, " delta: ", newClick-lastClick);
	if ((newClick-lastClick) < doubleClickInterval) doubleClick = true;
	else doubleClick = false;
	
	mainCanvas.handleEvent(event);
	lastClick = newClick;
}
tool.onMouseDrag = function(event) {
	mainCanvas.handleEvent(event);
}
tool.onMouseUp = function(event) {
	mainCanvas.handleEvent(event);
	mouseDown = false;
}
// class TEMovingVerticalSlider
function TEMovingVerticalSlider(from, to) {
	//var labelPosition = new Point(this.leftX+this.sliderThickness*2-0.5, event.point.y);
	this.label = new PointText(from-[0,8]);
	this.label.justification = "center";
	this.label.fillcolor = '#000';
	this.label.content = 'empty';
	this.label.visible = false;
	this.label.style.fontSize = 8;
	
	this.rectangle = new Path.Rectangle(from, to);
	this.rectangle.fillColor = '#000';
	this.rectangle.strokeColor = '#000';
	this.rectangle.visible = false;
	//this.rectangle.topLeft = from;
}
TEMovingVerticalSlider.prototype.identify = function(item) {
	//console.log("TEMovingVerticalSlider.identify()");
	if (item == this.rectangle) return this;
	else return false;
}
TEMovingVerticalSlider.prototype.hide = function() {
	this.rectangle.visible = false;
	this.label.visible = false;
	
}
TEMovingVerticalSlider.prototype.show = function() {
	this.rectangle.visible = true;
	this.label.visible = true;
}

// class TETensionSlider
function TETensionSlider(x, y, width, height, label) {
	// limitations
	this.leftX = x;
	this.rightX = x+width;
	this.upperY = y;
	this.lowerY = y+height;
	// properties
	this.labelHeight = 20;
	this.sliderThickness = 6;
	this.sliderThicknessHalf = this.sliderThickness / 2;
	this.slidingHeight = height-this.labelHeight;
	this.slidingStartY = y+this.labelHeight;
	this.slidingEndY = y+height;
	// borders
	this.border = new Path.Rectangle(new Point(this.leftX, this.upperY+this.labelHeight), new Point(this.rightX, this.lowerY));
	this.border.strokeColor = '#000';
	this.border.strokeWidth = 0.5;
	
	// labels
	// title
	this.title = new PointText(new Point(x+width/2, y+12));
	this.title.style.fontSize = 12;
	this.title.justification = 'center';
	this.title.strokeColor = '#000';
	this.title.strokeWidth = 0.5;
	this.title.content = label;
	// slider
	this.sliderValue = 0.5;
	this.actualSliderPosition = this.slidingStartY+(this.slidingHeight*this.sliderValue);
	this.verticalSlider = new TEMovingVerticalSlider(new Point(x+1, this.actualSliderPosition-this.sliderThicknessHalf), new Point(x+width-1, this.actualSliderPosition+this.sliderThicknessHalf));
	//this.setValue(0.5);
	
	// auxiliary lines
	this.auxiliaryLines = [];
	for (var t=0.1; t<=0.9; t+=0.1) {
		var tempY = y+height-(t*(height-this.labelHeight));
		var newLine = new Path.Line(new Point(x,tempY), new Point(x+width,tempY));
		newLine.dashArray = [2,2];
		newLine.strokeColor = '#000';
		newLine.strokeWidth = 0.5;
		this.auxiliaryLines.push(newLine);
	}
}
TETensionSlider.prototype.handleEvent = function(event) {
	//console.log("TETensionSlider.handleEvent()");
	if ((event.point.x >= this.leftX) && (event.point.x <= this.rightX) && (event.point.y >= this.slidingStartY) && (event.point.y <= this.slidingEndY)) {
		if ((this.verticalSlider.identify(event.item) != false) || (mouseDown)) {
			//console.log("Slider has received a mouse event");
			// somehow the value for the new position has to be adapted ... seems as if position of a rectangle has reference point at the CENTER of a retangle ?!?
			// strangely topLeft property cannot be used ... ?!?!
			newPosition = new Point(this.leftX+this.sliderThickness*2-0.5, event.point.y);
			this.sliderValue = (1 / this.slidingHeight) * (this.slidingEndY-event.point.y);
			//console.log("Slider value: ", this.sliderValue);
			//console.log("newTopLeft: ", newTopLeft);
			this.verticalSlider.rectangle.position = newPosition;
			this.verticalSlider.label.content = this.sliderValue.toFixed(2);
			this.verticalSlider.label.position = newPosition-[0,8];
			this.verticalSlider.label.visible = true;
		}
	}
}
TETensionSlider.prototype.setValue = function(tension) {
	this.sliderValue = tension;
	var tempX = this.verticalSlider.rectangle.position.x,
		tempY = this.slidingEndY-(this.slidingHeight*this.sliderValue);
	this.verticalSlider.rectangle.position = new Point(tempX, tempY); 
	this.verticalSlider.label.content = tension.toFixed(2);
	this.verticalSlider.label.position = new Point(tempX, tempY-8);
}


function TETwoGroupedTensionSliders(parent, x1, y1, width, height) {
	// parent and links
	this.parent = parent; 	// TECanvas
	this.linkedKnot = null; // TEVisuallyModifiableKnot, i.e. direct link to knot with corresponding tensions (that are modified by slider)
	
	// coordinates
	this.leftX = x1;
	this.rightX = x1+width;
	this.upperY = y1;
	this.lowerY = y1+height;
	
	// distances
	this.onePart = (width) / 7;
	this.sliderWidth = this.onePart * 2;
	
	// sliders
	this.tensionSlider1 = new TETensionSlider(x1+this.onePart*2, y1, this.sliderWidth, height, "T1");
	this.tensionSlider2 = new TETensionSlider(x1+this.sliderWidth+this.onePart*3, y1, this.sliderWidth, height, "T2");
	
	// labels
	this.valueLabels = new Array();
	
	// set labels
	fontSize = 10;
	for (var t=0; t<=1; t+=0.1) {
		//console.log("t=", t);
		var tempY = y1+height-(t*(height-this.tensionSlider1.labelHeight))+fontSize/2;
		var newValueLabel = new PointText(new Point(x1+this.onePart, tempY));
		newValueLabel.content = t.toFixed(1);
		newValueLabel.justification = 'center';
		newValueLabel.style.fontSize = fontSize;
		this.valueLabels.push(newValueLabel);
	}
	//this.tensionSlider1.verticalSlider.rectangle.visible = false; 	// start with vertical sliders hidden
	//this.tensionSlider2.verticalSlider.rectangle.visible = false; 	// start with vertical sliders hidden

}
TETwoGroupedTensionSliders.prototype.handleEvent = function(event) {
	//console.log("TETwoGroupedTensionSliders.handleEvent()");
	if (this.linkedKnot != null) {
		if ((event.point.x >= this.leftX) && (event.point.x <= this.rightX) && (event.point.y >= this.upperY) && (event.point.y <= this.lowerY)) {
			this.tensionSlider1.handleEvent(event);
			this.tensionSlider2.handleEvent(event);
		}
		// update tension in editableToken
		//var index = this.parent.editor.editableToken.index;
		//var modifiableKnot = this.parent.editor.editableToken.knotsList[index-1];
		var t1 = this.tensionSlider1.sliderValue;
		var t2 = this.tensionSlider2.sliderValue;
	
		//console.log(modifiableKnot, index);
		//modifiableKnot.setTension(0.4, 0.4);
		//modifiableKnot.tensions = [t1, t2];
		//if (this.parent.editor.editableToken.selectedKnot != null) {
		//	console.log("update tensions....");
		//	this.parent.editor.editableToken.selectedKnot.setTensions(t1,t2);
		//}
		if (this.linkedKnot != null) {
			//console.log("knot: ", this.linkedKnot, " tensions: ", t1, t2);
			//this.linkedKnot.tensions = [t1, t2];
			this.linkedKnot.setTensions(t1,t2);
			//console.log("update: ", this.parent);
			this.parent.editor.updateFreehandPath();
		}
		//this.parent.editor.editableToken.knotsList[this.parent.editor.editableToken.index].setTensions(t1, t2);
	}
}
TETwoGroupedTensionSliders.prototype.setValues = function(t1, t2) {
	this.tensionSlider1.setValue(t1);
	this.tensionSlider2.setValue(t2);
}
TETwoGroupedTensionSliders.prototype.showVerticalSliders = function() {
	this.tensionSlider1.verticalSlider.show();
	this.tensionSlider2.verticalSlider.show();
}
TETwoGroupedTensionSliders.prototype.hideVerticalSliders = function() {
	this.tensionSlider1.verticalSlider.hide();
	this.tensionSlider2.verticalSlider.hide();
}
TETwoGroupedTensionSliders.prototype.link = function(knot) {
	this.linkedKnot = knot;
	var t1 = this.linkedKnot.tensions[0]
		t2 = this.linkedKnot.tensions[1];
	this.setValues(t1,t2);
	this.showVerticalSliders();
}
TETwoGroupedTensionSliders.prototype.unlink = function() {
	this.linkedKnot = null;
	this.hideVerticalSliders();
}
