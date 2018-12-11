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
	var dx1 = c1.x - p1.x,
		dy1 = c1.y - p1.y,
		dx2 = c2.x - c1.x,
		dy2 = c2.y - c1.y,
		dx3 = p2.x - c2.x,
		dy3 = p2.y - c2.y;
		//m1 = dx1 / dy1,
		//m2 = dx2 / dy2,
		//m3 = dx3 / dy3;
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
	// deltas
	var dtx = tx2 - tx1,
		dty = ty2 - ty1; // avoid division by 0 for bm!?
	// calculate bezier point (coordinates and m)
	var bx = tx1 + dtx * factor,
		by = ty1 + dty * factor,
		bm = dtx / Math.avoidDivisionBy0(dty);
	return [bx, by, bm];
}

function findTangentPointRelativeToFixPoint(fixPoint, p1, c1, p2, c2, epsilon) {
	// DESCRIPTION OF ALGORITHM
	// define the 3 points:
	// - the middle one separates the bezier curve (or the actual segment of it) into two halves
	// - left and right points define start and end of the two segments (halves)
	// the points are defined as percentages (= relative location) on the bezier curve
	// epsilon stands for the precision: delta of straight lines going from connection point
	// to calculated tangent point should be < epsilon (numerical aproximation) 
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
		middlePoint = calculateBezierPoint(p1, c1, p2, c2, middlePercentage);
	
		// calculate m for straight line from connecting point to tangent point
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

function fract(n) { 
	return Number(String(n).split('.')[1] || 0); 
	// I hate JS ... (ceterum censeo:)
	// BTW: I was recommended not to add personal comments in the source code, so I decided to add some more and will do so in 
	// the future ... ;-)
	// And no, I won't change my mind: JS is a miserable programming language ... Not only is it poorly designed and full
	// of absolutely strange things (type coercion as one of the worst) that you don't find in any other language and that 
	// can cost you hours of debugging, but in addition it is the ONLY language that you can use to run code in a webbrowser 
	// (which means that you have absolutely no choice).
	// I also write these lines in defense for PHP (which is the other language VSTENO is written in): Many people say PHP 
	// is horrible ... Well, that might be, but at least if you don't like it, you can choose from a whole bunch of other
	// server-side languages.
	// That's why JS in my opinion fully and entirely deserves the verdict of Cato the Elder ... ;-)
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
	//console.log("TEKnotVector.constructor");
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

// class TEVisuallyModifiableKnotTEVisuallyModifiableKnot extends TEVisuallyModfiableCircle
function TEVisuallyModifiableKnot(x, y, t1, t2, radius, color, selectedColor, markedColor, link) {
    this.linkToRelativeKnot = link;
    this.type = new TEKnotType();
    this.shiftX = 0.0;	// shifting values for additional rotating axis
	this.shiftY = 0.0;  // now, if you believe that this is a constructor that will set shifX/Y to number 0, forget it! ShiftX/Y are reported as NaN ... (I hate JS ...) 
						// ok, got it: shiftX = 0 leads to NaN, shiftX = 0.0 leads to 0 ... (did I mention that I hate JS ... ?!)
    this.parallelRotatingAxisType = "horizontal"; // horizontal: shiftX is horizontal; orthogonal: shiftX is orthogonal (= compensation for inclination angle)
    this.tensions = [t1, t2, t1, t2, t1, t2];	// tensions must be controlled individually for left, middle and right path/outer shape (set them all to the same value to start)
	TEVisuallyModifiableCircle.prototype.constructor.call(this, new Point(x, y), radius, color, selectedColor, markedColor);
}
TEVisuallyModifiableKnot.prototype = new TEVisuallyModifiableCircle(); 	// inherit
TEVisuallyModifiableKnot.prototype.identify = function(item) {
	if (this.circle == item) return this;
	else return null;
}
TEVisuallyModifiableKnot.prototype.setTensions = function(t1, t2) {
	switch (selectedTension) { // for test purposes
		case "middle" : this.tensions[2] = t1; this.tensions[3] = t2; break;
		case "left" : this.tensions[0] = t1; this.tensions[1] = t2; break;
		case "right" : this.tensions[4] = t1; this.tensions[5] = t2; break;	
		case "locked" : this.tensions = [t1, t2, t1, t2, t1, t2]; break; // set all tension to the same value
	}
}
TEVisuallyModifiableKnot.prototype.getTensions = function() {
	var result;
	switch (selectedTension) { // for test purposes
		case "middle" : result = [this.tensions[2], this.tensions[3]]; break;
		case "left" : result = [this.tensions[0], this.tensions[1]]; break;
		case "right" : result = [this.tensions[4], this.tensions[5]]; break;	
		case "locked" : result = [this.tensions[2], this.tensions[3]]; break; // return middle tension
	}	
	return result;
}

// class TEEditableToken
function TEEditableToken(drawingArea) {
	// parent
	this.parent = drawingArea;
	// token data
	this.knotsList = []; 	// type: TEVisuallyModifiableKnot
	this.leftVectors = new Array(2);  	// type: TEKnotVector
	this.rightVectors = new Array(2);
	for (var i=0; i<2; i++) {			// make 2-dimensional array for vectors (TEKnotVector)
		this.leftVectors[i] = [];
		this.rightVectors[i] = [];
	}
	
	// paths
	this.middlePath = null; 			// for the moment: fhToken in TEDrawingArea
	this.outerShape = new Array(2);		// closed path: starting point - leftPath - endPoint - rightPath - starting point
	this.outerShape[0] = new Path();	// reserve 2 pathes (as array): 1 = normal, 2 = shadowed	
	this.outerShape[1] = new Path(); 	
	
	// mouse events
	this.selectedKnot = null;
	this.markedKnot = null;

	// index (is updated whenever identify-method is called)
	this.index = 0;
}
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
	this.parent.parent.tensionSliders.link(this.selectedKnot);
}
TEEditableToken.prototype.selectFollowingKnot = function() {
	//console.log("Select following knot");
	var lastKnot = this.knotsList.length;
	if (this.index >= lastKnot) return;
	else {
		this.index += 1;
		this.selectedKnot = this.knotsList[this.index-1];
		this.markedKnot = this.selectedKnot;
		this.parent.setMarkedCircle(this.markedKnot);
		this.parent.parent.tensionSliders.link(this.selectedKnot);
		this.parent.parent.thicknessSliders.linkEditableToken(this);
	}
}
TEEditableToken.prototype.selectPreceedingKnot = function() {
	//console.log("Select preceeding knot");
	// save edited tension values first!!!! => is done automatically by linking
	if (this.index <= 1) return;
	else {
		this.index -= 1;
		this.selectedKnot = this.knotsList[this.index-1];
		this.markedKnot = this.selectedKnot;
		this.parent.setMarkedCircle(this.markedKnot);
		this.parent.parent.tensionSliders.link(this.selectedKnot);
		this.parent.parent.thicknessSliders.linkEditableToken(this);
	}
}
TEEditableToken.prototype.getRelativeTokenKnot = function() {
	//console.log("this.index: ", this.index);
	return this.parent.rotatingAxis.relativeToken.knotsList[this.index-1];
}
TEEditableToken.prototype.setParallelRotatingAxis = function() {
	var defaultValue = this.knotsList[this.index-1].shiftX;
	var shiftX = prompt("Enter x-Delta for parallel rotating axis:\n(negative = left side; positive = right side)", defaultValue);
	this.knotsList[this.index-1].shiftX = Number(shiftX);
	var temp = this.parent.rotatingAxis.getRelativeCoordinates(this.selectedKnot);
	this.selectedKnot.linkToRelativeKnot.rd1 = temp[0];
	this.selectedKnot.linkToRelativeKnot.rd2 = temp[1];
}
TEEditableToken.prototype.toggleParallelRotatingAxisType = function() {
	var actualType = this.selectedKnot.parallelRotatingAxisType;
	console.log("actual type: ", actualType);
	this.selectedKnot.parallelRotatingAxisType = (actualType == "horizontal") ? "orthogonal" : "horizontal";
	console.log("new type: ", this.selectedKnot.parallelRotatingAxisType);
}
TEEditableToken.prototype.setKnotType = function(type) {
	var relativeTokenKnot = this.getRelativeTokenKnot();
	relativeTokenKnot.setType(type);
	//console.log("setKnotType: ", type, relativeTokenKnot, this.selectedKnot, this.markedKnot);
	// ok, the following line is something between a bugfix and a workaround.
	// the problem is as follows: event handlers were first designed to change
	// type of a knot when a key ('o', 'h') was pressed and a left mouseclick
	// ocurred simultaneously. In this case, this.selectedKnot was set to the
	// actual knot by mouseDown event-handler. 
	// with the introduction of proportional knots, the way knots are selected
	// was changed: knots could be selected by mouse or by left/right arrow keys.
	// Since keys have their own event handles (i.e. they don't pass through
	// mouseDown event), the actual knot is marked (but not selected).
	// This leads to the fact, that no event handler is called for the actual
	// knot (because event handler is selectedKnot.eventHandler). As a consequence
	// new relative coordinates are not updated ... and the calculation of relative
	// coordinates goes wrong when type is changed ... 
	// I think, that with the introduction of keyboard commands, the selectedKnot 
	// variable has become somewhat obsolete (a part from the event handler)
	// Therefore I think it is safe to set it to markedKnot (but keep an eye
	// on that ... in case unpleasant behavours occurs later ...)
	// MAYBE A CLEAN SOLUTION IS TO SET selectedKnot TO markedKnot DIRECTLY 
	// IN THE KEYBOARD EVENT HANDLER (IN MAIN PROGRAM) => try that!
	// Result: doesn't work because if actual knot is selected by default
	// (e.g. after insertion with mouse), no keyboard action ocurrs ...
	// OTHER SOLUTION: do not deselect knot in mouseUp event-handler any 
	// more ... ?!
	switch (type) {
		case "orthogonal" : this.markedKnot.changeCircleToRectangle(); 
							var x = this.selectedKnot.circle.position.x,
								y = this.selectedKnot.circle.position.y;
							//this.parent.rotatingAxis.calculateOrthogonalIntersectionWithRotatingAxis(x, y);
							//var relative = this.parent.rotatingAxis.getRelativeCoordinates(x,y, type);
							var relative = this.parent.rotatingAxis.getRelativeCoordinates(this.selectedKnot, "orthogonal");
							relativeTokenKnot.rd1 = relative[0];
							relativeTokenKnot.rd2 = relative[1];
							break;
		case "horizontal" : this.markedKnot.changeRectangleToCircle(); 
							var x = this.selectedKnot.circle.position.x,
								y = this.selectedKnot.circle.position.y;
							//this.parent.rotatingAxis.calculateOrthogonalIntersectionWithRotatingAxis(x, y);
							var relative = this.parent.rotatingAxis.getRelativeCoordinates(this.selectedKnot, "horizontal");
							relativeTokenKnot.rd1 = relative[0];
							relativeTokenKnot.rd2 = relative[1];
							break;
		case "proportional" : this.markedKnot.changeKnotToProportional();
							var x = this.selectedKnot.circle.position.x,
								y = this.selectedKnot.circle.position.y;
							var relative = this.parent.rotatingAxis.getRelativeCoordinates(this.selectedKnot, "proportional");
							relativeTokenKnot.rd1 = relative[0];
							relativeTokenKnot.rd2 = relative[1];
							break;
	}
}
TEEditableToken.prototype.handleMouseDown = function(event) {
	//console.log("TEEditableToken.handleMouseDown()");
	this.identifyAndSelectKnot(event.item);
	if (this.selectedKnot != null) {
		this.parent.parent.tensionSliders.link(this.selectedKnot);
		this.selectedKnot.handleMouseDown(event);
		this.parent.rotatingAxis.relativeToken.updateRelativeCoordinates(this.selectedKnot);
		this.parent.parent.thicknessSliders.linkEditableToken(this);
	}
}
TEEditableToken.prototype.handleMouseUp = function(event) {
	if (this.selectedKnot != null) {
		this.selectedKnot.handleMouseUp(event); // catch error (selectedKnot can be null when clicking fast)
	} 
	// for following line: see comment in freehand => setKnotType()	
	// do not deselect knot any more ...   
}
TEEditableToken.prototype.handleMouseDrag = function(event) {
	if (mouseDown) {
		if (this.selectedKnot != null) {
			this.selectedKnot.handleMouseDrag(event);
			// update of relative coordinates not necessary (will be called by handleMouseUp-event)
			this.parent.rotatingAxis.relativeToken.updateRelativeCoordinates(this.selectedKnot);
		}
	}
}
TEEditableToken.prototype.handleEvent = function(event) {
	//console.log("TEEditableToken.handleEvent");
	switch (event.type) {
		case "mousedown" : if (keyPressed == "d") { 
								this.deleteMarkedKnotFromArray();
						   } else this.handleMouseDown(event); 
						   break;
		case "mouseup" : this.handleMouseUp(event); break;
		case "mousedrag" : this.handleMouseDrag(event); break;
	}
}
TEEditableToken.prototype.redefineKnotTypesAndSetColors = function() {
	// reset all knot types
	for (var i=0; i<this.knotsList.length; i++) {
		this.knotsList[i].type.entry = false;
		this.knotsList[i].type.exit = false;
		this.knotsList[i].type.pivot1 = false;
		this.knotsList[i].type.pivot1 = false;
		this.knotsList[i].circle.fillColor = colorNormalKnot;
		// set thicknesses to 1
		//this.leftVectors[0][i].distance = 1;
		//this.rightVectors[0][i].distance = 1;
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
	// correct thicknesses of entry and exit knot (set them to 0)
	//this.leftVectors[0][0].distance = 0;
	//this.rightVectors[0][0].distance = 0;
	//this.leftVectors[0][this.leftVectors[0].length-1].distance = 0;
	//this.rightVectors[0][this.rightVectors[0].length-1].distance = 0;
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
	//console.log("TEEditableToken.insertNewKnot(): ", point, this.index);
	// get color of new knot before inserting it
	var newColor = this.getNewKnotTypeColor();
	// insert knot
	var newKnot = new TEVisuallyModifiableKnot(point.x, point.y, 0.5, 0.5, 5, newColor, colorSelectedKnot, colorMarkedKnot, null);
	//console.log("newKnot: ", newKnot);
	this.knotsList.splice(this.index, 0, newKnot);
	//var newLength = this.knotsList.length;
	// insert knot vectors for outer shape
	//var distance = ((this.index == 0) || (this.index == newLength-1)) ? 0 : 1; 	// 0 = no pencil thickness, 1 = maximum thickness
	var distance = 1;
	var leftVector = new TEKnotVector(distance, "orthogonal");
	var rightVector = new TEKnotVector(distance, "orthogonal");
	this.leftVectors[0].splice(this.index,0, leftVector);
	this.rightVectors[0].splice(this.index,0, rightVector);
	//console.log("new leftVector: ", leftVector);
	//console.log("array leftVectors: ", this.leftVectors[this.index]);
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
	//this.parent.rotatingAxis.relativeToken.insertNewRelativeKnot(point.x, point.y, "horizontal", this.index);
	this.parent.rotatingAxis.relativeToken.insertNewRelativeKnot(newKnot);
	
	// make index point to new knot
	this.index += 1; // point to the newly inserted element
	// update connections from preceeding and following connection point
	this.connectPreceedingAndFollowing();
	//console.log("insertNewKnot: selected/marked:", this.selectedKnot, this.markedKnot);
	
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
	this.leftVectors[0][this.index-1].line.removeSegments();
	this.leftVectors[0].splice(this.index-1, 1);
	this.rightVectors[0][this.index-1].line.removeSegments();
	this.rightVectors[0].splice(this.index-1, 1);
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
}
TERotatingAxis.prototype.select = function() {
	this.selected = true;
	this.updateColor();
}
TERotatingAxis.prototype.unselect = function() {
	this.selected = false;
	this.updateColor();
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
		var startAndEndPoints = this.getStraightLineStartAndEndPoints(event);
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
				// get shiftX value for parallel rotating axis
				//console.log("visuallyModifiableKnot: ", visuallyModifiableKnot);
				
				var shiftX = visuallyModifiableKnot.shiftX;
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

// class TEVisuallyModifiableCircle
function TEVisuallyModifiableCircle(position, radius, color, selectColor, strokeColor ) {
	//console.log("TEVisuallyModifiableCircle.constructor");
	this.circle = new Path.Circle(position, radius);
	//this.center = position;
	this.radius = radius;
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
	this.circle.position = event.point;
	this.mark();
	this.select();
	//this.circle.position = event.point;
}
TEVisuallyModifiableCircle.prototype.handleMouseDrag = function(event) {
	//console.log("mousedrag");
	this.circle.position = event.point;
}
TEVisuallyModifiableCircle.prototype.handleMouseUp = function(event) {
	//console.log("TEVisuallyModifiableCircle.handleMouseUp()");
	//this.circle.position = event.point;
	this.unselect();	// unselect, but leave it marked
}
TEVisuallyModifiableCircle.prototype.isStatic = function() {
	return false;
}
TEVisuallyModifiableCircle.prototype.isDynamic = function() {
	return true;
}
TEVisuallyModifiableCircle.prototype.changeCircleToRectangle = function() {
	// changes circle to rectangle
	// function needed by TEVisuallyModifiableKnot (which inherits from TEVisuallyModifiableCircle)
	// implement it here, so that other classes can use the function as well if they need to
	// the property "circle" will keep the same name for the moment
	// (can be changed in the whole code later)
	
	var center = this.circle.position,
		leftX = center.x - this.radius,
		topY = center.y - this.radius,
		rightX = center.x + this.radius,
		bottomY = center.y + this.radius,
		strokeColor = this.circle.strokeColor,
		strokeWidth = this.circle.strokeWidth,
		fillColor = this.circle.fillColor;
		
	// delete circle path completely
	this.center = center; // store center for rectangle (so that the circle can be restored later) - USE THIS ONLY FOR THIS PURPOSE!!!
	this.circle.removeSegments();
	
	// create a new rectangle object with same properties
	this.circle = new Path.Rectangle(new Point(leftX, topY), new Point(rightX, bottomY));
	this.circle.strokeColor = strokeColor;
	this.circle.strokeWidth = strokeWidth;
	this.circle.fillColor = fillColor;
}
TEVisuallyModifiableCircle.prototype.changeRectangleToCircle = function() {
	// changes rectangle to circle
	// same comments as for changeCircleToRectangle
	var strokeColor = this.circle.strokeColor,
		strokeWidth = this.circle.strokeWidth,
		fillColor = this.circle.fillColor;
	var center = this.circle.position; // get position of rectangle => use it for circle
		
	// delete circle path completely
	this.circle.removeSegments();
	
	// create a new rectangle object with same properties
	this.circle = new Path.Circle(center, this.radius);
	this.circle.strokeColor = strokeColor;
	this.circle.strokeWidth = strokeWidth;
	this.circle.fillColor = fillColor;
}
TEVisuallyModifiableCircle.prototype.changeKnotToProportional = function() {
	// changes shape to polygon
	var strokeColor = this.circle.strokeColor,
		strokeWidth = this.circle.strokeWidth,
		fillColor = this.circle.fillColor;
	var center = this.circle.position; // get position of rectangle => use it for circle
	
	this.circle.removeSegments();
	
	var sides = 3, points = 6; 
	var radius = 6;
	//this.circle = new Path.RegularPolygon(center, sides, radius);
	this.circle = new Path.Star(center, points, radius, radius-2);
	
	this.circle.fillColor = fillColor;
	this.circle.strokeColor = strokeColor;
	this.circle.strokeWidth = strokeWidth;
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
	this.rotatingAxis = new TERotatingAxis(this, '#0f0'); // colorMainRotatingAxisSelected);
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
	}
}
TEDrawingArea.prototype.calculateFreehandHandles = function() {
	numberOfPoints = this.fhToken.segments.length;
	//console.log("calculateFreehandHandles");
	// do first knot separately (add "virtual" 3rd knot at beginning which is identical with 1st knot)
	if (numberOfPoints > 1) { // only if there are at least 2 knots
		//console.log("calculate 1st");
		var t1 = this.editableToken.knotsList[0].tensions[2];
		var t2 = this.editableToken.knotsList[0].tensions[3];
		var absHandles = getControlPoints(this.fhToken.segments[0].point, this.fhToken.segments[0], this.fhToken.segments[1], t1, t2);
		this.fhToken.segments[0].handleIn = absHandles[0] - this.fhToken.segments[0].point;
		this.fhToken.segments[0].handleOut = absHandles[1] - this.fhToken.segments[0].point;
	}
	for (var i = 1; i < numberOfPoints-1; i++) { // dont calculate 1st and last
			var t1 = this.editableToken.knotsList[i].tensions[2];
			var t2 = this.editableToken.knotsList[i].tensions[3];
			var absHandles = getControlPoints( this.fhToken.segments[i-1].point, this.fhToken.segments[i].point, this.fhToken.segments[i+1].point, t1, t2 );
			this.fhToken.segments[i].handleIn = absHandles[0] - this.fhToken.segments[i].point;
			this.fhToken.segments[i].handleOut = absHandles[1] - this.fhToken.segments[i].point;
	}
	// do last knot separately (add "virtual" 3rd knot at end which is identical with last knot)
	if (numberOfPoints > 1) { // only if there are at least 2 knots
		//console.log("calculate last");
		var last = this.editableToken.knotsList.length-1;
		var t1 = this.editableToken.knotsList[last].tensions[2];
		var t2 = this.editableToken.knotsList[last].tensions[3];
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
		this.editableToken.leftVectors[0][i].line.removeSegments();
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
		var newLength = this.editableToken.leftVectors[0][i].distance * this.scaleFactor; //10; // 10 pixels
		var endPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// vector startpoint (outside circle)
		newLength = 6;
		var startPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// draw left vector
		this.editableToken.leftVectors[0][i].line.add( startPoint, endPoint); //[newX,newY]);
		this.editableToken.leftVectors[0][i].line.strokeColor = '#000';
		this.editableToken.leftVectors[0][i].line.visible = true;
		// calculate new coordinates for right shape
		// flip vector by 180 degrees <=> negate x and y
		vectorY = -vectorY;
		vectorX = -vectorX;
		// vector endpoint
		newLength = this.editableToken.rightVectors[0][i].distance * this.scaleFactor; //10; // 10 pixels
		endPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// vector startpoint (outside circle)
		newLength = 6;
		startPoint = tempPosition + [vectorX * newLength, vectorY * newLength];
		// draw right vector
		this.editableToken.rightVectors[0][i].line.removeSegments();
		this.editableToken.rightVectors[0][i].line.add( startPoint, endPoint); //[newX,newY]);
		this.editableToken.rightVectors[0][i].line.strokeColor = '#000';
		this.editableToken.rightVectors[0][i].line.visible = true;
	}
}
TEDrawingArea.prototype.calculateOuterShapeHandles = function() {
	var length = this.editableToken.knotsList.length;
	// set control points of entry knot to (0,0)
	this.editableToken.outerShape[0].segments[0].handleIn = 0;
	this.editableToken.outerShape[0].segments[0].handleOut = 0;
	//console.log("Starting point: ", this.editableToken.outerShape.segments[0].point);
	// recalculate handles of left shape
	var p0, p1, p2, t1, t2, controlPoints, rc1, rc2;
	for (var i=1; i<length-1; i++) {
		// get 3 points for control point calculation (preceeding, actual, following)
		p0 = this.editableToken.outerShape[0].segments[i-1].point;
		p1 = this.editableToken.leftVectors[0][i].line.segments[1].point;
		p2 = this.editableToken.outerShape[0].segments[i+1].point;
		// get tensions
		t1 = this.editableToken.knotsList[i].tensions[0];
		t2 = this.editableToken.knotsList[i].tensions[1];
		// calculate control points
		controlPoints = getControlPoints(p0, p1, p2, t1, t2);
		//console.log("getControlPoints: ", p0, p1, p2, t1, t2);
		// values are absolute => make them relative and copy them to segment
		rc1 = controlPoints[0]-p1;
		rc2 = controlPoints[1]-p1;
		this.editableToken.outerShape[0].segments[i].handleIn = rc1;
		this.editableToken.outerShape[0].segments[i].handleOut = rc2;
		//console.log("object: ",i,  this.editableToken.leftVectors[i].line.segments[1].point);
	}
	
	// set control points of exit knot to (0,0)
	this.editableToken.outerShape[0].segments[length-1].handleIn = 0;
	this.editableToken.outerShape[0].segments[length-1].handleOut = 0;
	
	// calculate right shape control points (backwards)
	//console.log("End point: ", this.editableToken.outerShape.segments[length-1].point);
	var continueIndex = length;
	for (var i=length-2; i>0; i--) {
		// get 3 points for control point calculation (preceeding, actual, following)
		p0 = this.editableToken.outerShape[0].segments[continueIndex-1].point;  
		p1 = this.editableToken.outerShape[0].segments[continueIndex].point;
		// modulo = wrap around at the end (lenght of outerShape = 2*length - 2, because start/end points are only inserted 1x)
		p2 = this.editableToken.outerShape[0].segments[(continueIndex+1)%(2*length-2)].point;
		// get tensions
		t1 = this.editableToken.knotsList[i].tensions[5];	// tensions have to be inversed 
		t2 = this.editableToken.knotsList[i].tensions[4];	// due to backwards calculation
		// calculate control points
		controlPoints = getControlPoints(p0, p1, p2, t1, t2);
		//console.log("getControlPoints: ", continueIndex, p0, p1, p2, t1, t2);
		// values are absolute => make them relative and copy them to segment
		rc1 = controlPoints[0]-p1;
		rc2 = controlPoints[1]-p1;
		this.editableToken.outerShape[0].segments[continueIndex].handleIn = rc1;
		this.editableToken.outerShape[0].segments[continueIndex].handleOut = rc2;		
		continueIndex++;
	}
	// starting point control point values have already been written
}
TEDrawingArea.prototype.calculateOuterShape = function() {
	var length = this.editableToken.knotsList.length;
	this.editableToken.outerShape[0].removeSegments();
	// add first segment = entry point
	this.editableToken.outerShape[0].add(this.editableToken.knotsList[0].circle.position);
	// add points of left shape
	var tempPoint, handleIn, handleOut;
	for (var i=1; i<length-1; i++) {
		tempPoint = this.editableToken.leftVectors[0][i].line.segments[1].point;
		handleIn = this.fhToken.segments[i].handleIn;	// not correct
		handleOut = this.fhToken.segments[i].handleOut;	// not correct
		this.editableToken.outerShape[0].add(new Segment(tempPoint, handleIn, handleOut));
	//console.log("object: ",i,  this.editableToken.leftVectors[i].line.segments[1].point);
	}
	
	// add end point
	this.editableToken.outerShape[0].add(this.editableToken.knotsList[length-1].circle.position);
	// add right shape backwards
	var tempPoint, handleIn, handleOut;
	for (var i=length-2; i>0; i--) {
		tempPoint = this.editableToken.rightVectors[0][i].line.segments[1].point;
		// inverse handleIn / handleOut (since elements are inserted backwards)
		handleOut = this.fhToken.segments[i].handleIn; // not correct
		handleIn = this.fhToken.segments[i].handleOut; // not correct
		this.editableToken.outerShape[0].add(new Segment(tempPoint, handleIn, handleOut));			
	}
	// no need to add starting point again => just close path
	this.editableToken.outerShape[0].closePath();

	// set color
	this.editableToken.outerShape[0].strokeColor = '#000';

	// it's not correct to copy the control points (handles) from the middle path,
	// outer paths are different (only tensions are equal)!
	// Therefore: take the TENSIONS of the middle path and recalculate the handles
	// Do this in 2 steps for the moment (in order to be able to compare differences,
	// later this calculation can be integrated inside the for-loops above
	this.calculateOuterShapeHandles();
}
TEDrawingArea.prototype.updateFreehandPath = function() {
	if (this.editableToken.knotsList.length > 0) {
		this.copyKnotsToFreehandPath();
		this.calculateLeftRightVectors();
		this.calculateOuterShape();
		this.calculateFreehandHandles();
	}
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
		//console.log("insertNewKnot: ", event.point);
		this.editableToken.insertNewKnot(event.point);
	}
	this.connectPreceedingAndFollowing();
	// link thickness sliders
	this.parent.thicknessSliders.linkEditableToken(this.editableToken);
}
TEDrawingArea.prototype.handleMouseUp = function( event ) {
	//console.log("HandlingParent: ", this.handlingParent);
	if (this.handlingParent != null) {
		this.handlingParent.handleEvent(event);
	}
	this.mouseDownItem = null;
	this.handlingParent = null;
}
TEDrawingArea.prototype.handleMouseDrag = function( event ) {
	if (this.handlingParent != null) {
		this.handlingParent.handleEvent(event);	
	}
	this.connectPreceedingAndFollowing();
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
/* some remains from ancient times (Atlantis?:)
TEDrawingArea.prototype.isDynamic = function(item) {	
}
TEDrawingArea.prototype.isStatic = function(item) {
}
*/
TEDrawingArea.prototype.handleEvent = function(event) {
	//console.log("TEDrawingArea.handleEvent()", event);
	if ((event.point.x >= this.leftX) && (event.point.x <= this.rightX) && (event.point.y >= this.upperY) && (event.point.y <= this.lowerY)) {	
		switch (event.type) {
			case "mousedown" : this.handleMouseDown(event); break;
			case "mouseup" : this.handleMouseUp(event); break;
			case "mousedrag" : this.handleMouseDrag(event); break;
		}
		//console.log("TEDrawingArea.handleEvent1: selected/marked: ", this.parent.editor.editableToken.selectedKnot, this.parent.editor.editableToken.markedKnot);
		this.updateFreehandPath();
		this.knotLabel.updateLabel();
	}
}
TEDrawingArea.prototype.connectPreceedingAndFollowing = function() {
	this.preceeding.connect();
	this.following.connect();	
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
TETensionSlider.prototype.setNewLabel = function(label) {
	// title
	this.title.style.fontSize = 12;
	this.title.justification = 'center';
	this.title.strokeColor = '#000';
	this.title.strokeWidth = 0.5;
	this.title.content = label;
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
/*TETensionSlider.prototype.getLabelChar = function() {
	var char;
	switch (selectedTension) { // use global variable
		case "middle" : char = "M"; break;
		case "left" : char = "L"; break;
		case "right" : char = "R"; break;
		case "locked" : char = "A"; break;
	}
	return char;
}
*/

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
	// to simplify: just set labels to "A1/2" (instead of calling parent class method)
	this.tensionSlider1 = new TETensionSlider(x1+this.onePart*2, y1, this.sliderWidth, height, "A1");
	this.tensionSlider2 = new TETensionSlider(x1+this.sliderWidth+this.onePart*3, y1, this.sliderWidth, height, "A2");
	
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
	//this.updateValues();
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
	var temp = this.linkedKnot.getTensions();
	var t1 = temp[0],
		t2 = temp[1];
	this.setValues(t1,t2);
	this.showVerticalSliders();
}
TETwoGroupedTensionSliders.prototype.unlink = function() {
	this.linkedKnot = null;
	this.hideVerticalSliders();
}
TETwoGroupedTensionSliders.prototype.updateValues = function() {
	//console.log("Update slider values: ", this.linkedKnot);
	if (this.linkedKnot != null) {
		var temp = this.linkedKnot.getTensions();
		var t1 = temp[0],
			t2 = temp[1];
		this.setValues(t1,t2);
		this.showVerticalSliders(); // not sure if this is necessary ...
		//console.log("linkedKnot after Update: ", this.linkedKnot);
	
	}
}
TETwoGroupedTensionSliders.prototype.setNewLabels = function() {
	var labels = this.getLabelStrings();
	//console.log("setNewLabels: ", labels);
	
	this.tensionSlider1.setNewLabel(labels[0]);
	this.tensionSlider2.setNewLabel(labels[1]);
}
TETwoGroupedTensionSliders.prototype.getLabelStrings = function() {
	//console.log("selectedTension: ", selectedTension);
	
	var label1, label2;
	switch (selectedTension) { // use global variable
		case "middle" : label1 = "M1"; label2 = "M2"; break;
		case "left" : label1 = "L1"; label2 = "L2"; break;
		case "right" : label1 = "R1"; label2 = "R2"; break;
		case "locked" : label1 = "A1"; label2 = "A2"; break;
	}
	return [label1, label2];
}

// class TEMovingHorizontalSlider
function TEMovingHorizontalSlider(from, to) {
	//console.log("TEMovingHorizontalSlider()");
	this.label = new PointText( from + [10,2] );
	this.label.justification = "center";
	this.label.fillcolor = '#000';
	this.label.content = '1';
	this.label.visible = false;
	this.label.style.fontSize = 8;
	
	this.rectangle = new Path.Rectangle(from, to);
	this.rectangle.fillColor = '#000';
	this.rectangle.strokeColor = '#000';
	this.rectangle.visible = false; // false
	//this.rectangle.topLeft = from;
}
TEMovingHorizontalSlider.prototype.identify = function(item) {
	//console.log("TEMovingHorizontalSlider.identify()");
	if (item == this.rectangle) return this;
	else return false;
}
TEMovingHorizontalSlider.prototype.hide = function() {
	//console.log("TEMovingHorizontalSlider.hide()");	
	this.rectangle.visible = false;
	this.label.visible = false;
}
TEMovingHorizontalSlider.prototype.show = function() {
	//console.log("TEMovingHorizontalSlider.show()");
	this.actualSliderPosition = this.slidingStartX+(this.slidingWidth/2*this.sliderValue);
	this.rectangle.visible = true;
	this.label.visible = true;
}

// class TEThicknessSlider
function TEThicknessSlider(x, y, width, height, label) {
	this.linkedVector;   // TEEditableToken.leftVectors[] / rightVectors[]
	// limitations
	this.leftX = x;
	this.rightX = x+width;
	this.upperY = y;
	this.lowerY = y+height;
	// original values
	this.width = width;
	this.height = height;
	// properties
	this.labelWidth = 20;
	this.sliderThickness = 6;
	this.sliderThicknessHalf = this.sliderThickness / 2;
	this.slidingWidth = width-this.labelWidth;
	this.slidingStartX = x+this.labelWidth;
	this.slidingEndX = x+width;
	// borders
	this.border = new Path.Rectangle(new Point(this.leftX+this.labelWidth, this.upperY), new Point(this.rightX, this.lowerY));
	this.border.strokeColor = '#000';
	this.border.strokeWidth = 0.5;
	
	// labels
	// title
	var fontSize = 12;
	this.title = new PointText(new Point(x, y+(height+fontSize)/2)-1);
	this.title.style.fontSize = fontSize;
	this.title.justification = 'left';
	this.title.strokeColor = '#000';
	this.title.strokeWidth = 0.5;
	this.title.content = label;
	// slider
	this.sliderValue = 1;
	this.actualSliderPosition = this.slidingStartX+(this.slidingWidth/2*this.sliderValue);
	//this.horizontalSlider = new TEMovingHorizontalSlider(new Point(this.actualSliderPosition-this.sliderThickness/2, this.upperY+1), new Point(this.actualSliderPosition+this.sliderThickness/2, this.lowerY-1), this.getLabelPosition());
	this.horizontalSlider = new TEMovingHorizontalSlider(new Point(this.actualSliderPosition-this.sliderThickness/2, this.upperY+1), new Point(this.actualSliderPosition+this.sliderThickness/2, this.lowerY-1));
	
	//this.setValue(1);
	
	// auxiliary lines
	this.auxiliaryLines = [];
	for (var t=0.2; t<1.9; t+=0.2) {
		var tempX = (this.slidingWidth / 2) * t + this.leftX + this.labelWidth;
		var newLine = new Path.Line(new Point(tempX,this.upperY+1), new Point(tempX,this.lowerY-1));
		if (Math.abs(t-1) < 0.01) newLine.dashArray = [];
		else newLine.dashArray = [2,2];
		newLine.strokeColor = '#000';
		newLine.strokeWidth = 0.5;
		this.auxiliaryLines.push(newLine);
	}
}
TEThicknessSlider.prototype.getLabelDelta = function() {
	delta = (this.sliderValue > 0.1) ? [14,-2] : [-14,-2] ;
	return delta;
}
TEThicknessSlider.prototype.copySliderValueToVector = function() {
	if (this.linkedVector != null) this.linkedVector.distance = this.sliderValue;
}
/*
TEThicknessSlider.prototype.setNewLabel = function(label) {
	// title
	this.title.style.fontSize = 12;
	this.title.justification = 'center';
	this.title.strokeColor = '#000';
	this.title.strokeWidth = 0.5;
	this.title.content = label;
}
*/
TEThicknessSlider.prototype.handleEvent = function(event) {
	//console.log("TEThicknessSlider.handleEvent()", event);
	//console.log("is inside rectangle? ", event.point, this.slidingStartX, this.slidingEndX, this.upperY, this.lowerY);
	
	if ((event.point.x >= this.slidingStartX) && (event.point.x <= this.slidingEndX) && (event.point.y >= this.upperY) && (event.point.y <= this.lowerY)) {
		//console.log("is inside rectangle: ", event.point, this.slidingStartX, this.slidingEndX, this.upperY, this.lowerY);
		if ((this.horizontalSlider.identify(event.item) != false) || (mouseDown)) {
			//console.log("thicknessSlider has received a mouse event");
			// somehow the value for the new position has to be adapted ... seems as if position of a rectangle has reference point at the CENTER of a retangle ?!?
			// strangely topLeft property cannot be used ... ?!?!
			newPosition = new Point(event.point.x, this.upperY + this.height/2);
			this.sliderValue = (2 / this.slidingWidth) * (event.point.x-this.slidingStartX);
			//console.log("Slider value: ", this.sliderValue);
			//console.log("newPosition: ", newPosition);
			this.horizontalSlider.rectangle.position = newPosition;
			this.horizontalSlider.label.content = this.sliderValue.toFixed(2);
			deltaPosition = (this.sliderValue > 0.1) ? deltaPosition = [14,-2] : [-14,-2] ;
			this.horizontalSlider.label.position = newPosition-deltaPosition;
			//this.horizontalSlider.label.visible = true;
		}
	}
	this.copySliderValueToVector();
	
}
TEThicknessSlider.prototype.getValue = function() {
	return this.sliderValue;
}
TEThicknessSlider.prototype.showLinked = function() {
	//console.log("linkedVector: ", this.linkedVector);
}
TEThicknessSlider.prototype.linkVector = function(vector) {
	this.linkedVector = vector;
	this.setValue(vector.distance);
	this.horizontalSlider.show();
}
TEThicknessSlider.prototype.unlinkVector = function() {
	this.linkedVector = null;
	this.horizontalSlider.hide();
}
TEThicknessSlider.prototype.setValue = function(thickness) {
	//console.log("TEThicknessSlider.setValue(): ", thickness);
	this.sliderValue = thickness;
	var tempX = (this.slidingWidth / 2) * thickness + this.slidingStartX;
		tempY = (this.upperY+this.lowerY) / 2;
	//console.log("coordinates(x,y): ", tempX, tempY);
	this.horizontalSlider.rectangle.position = new Point(tempX, tempY); 
	this.horizontalSlider.label.content = thickness.toFixed(2);
	this.actualSliderPosition = tempX; // ?
	var labelDelta = this.getLabelDelta();
	this.horizontalSlider.label.position = new Point(tempX-labelDelta[0], tempY-labelDelta[1]);
	
}
/*
TEThicknessSlider.prototype.showHorizontalSlider = function() {
	this.horizontalSlider.show();
}
TEThicknessSlider.prototype.hideHorizontalSlider = function() {
	this.horizontalSlider.hide();
}
*/

/*TETensionSlider.prototype.getLabelChar = function() {
	var char;
	switch (selectedTension) { // use global variable
		case "middle" : char = "M"; break;
		case "left" : char = "L"; break;
		case "right" : char = "R"; break;
		case "locked" : char = "A"; break;
	}
	return char;
}
*/


//////////////// class TETwoGroupedThicknessSliders /////////////////////////////////////////////////////////////////////
function TETwoGroupedThicknessSliders(parent, x1, y1, width, height) {
	// parent and links
	this.parent = parent; 	// TECanvas
	this.linkedEditableToken = null;
	
	// coordinates
	this.leftX = x1;
	this.rightX = x1+width;
	this.upperY = y1;
	this.lowerY = y1+height;
	
	// distances
	this.onePart = (height) / 7;
	this.sliderHeight = this.onePart * 2;
	
	// sliders
	this.thicknessSlider1 = new TEThicknessSlider(x1, y1, width, this.sliderHeight, "L1"); // + this.getSelectedShape());
	this.thicknessSlider2 = new TEThicknessSlider(x1, y1+this.onePart*3, width, this.sliderHeight, "R1"); // + this.getSelectedShape());
	
	// labels
	this.valueLabels = new Array();
	
	// set labels
	fontSize = 10;
	for (var t=0; t<=2.01; t+=0.2) {
		var tempX = (this.thicknessSlider1.slidingWidth / 2) * t + this.leftX + this.thicknessSlider1.labelWidth;
		//console.log("t / tempX: ", t, tempX);
		var newValueLabel = new PointText(new Point(tempX, y1+height-(this.onePart-fontSize/2)));
		if ((t<0.05) || (t>1.95) || ((t>0.99) && (t<1.01))) newValueLabel.content = t.toFixed(0); // I hate JS ...
		else newValueLabel.content =  "." + fract(t.toFixed(1));   //t.toFixed(1);
		newValueLabel.justification = 'center';
		newValueLabel.style.fontSize = fontSize;
		this.valueLabels.push(newValueLabel);
	}
	//this.tensionSlider1.verticalSlider.rectangle.visible = false; 	// start with sliders hidden
	//this.tensionSlider2.verticalSlider.rectangle.visible = false; 	// start with sliders hidden
}
TETwoGroupedThicknessSliders.prototype.getSelectedShape = function() {
	switch (selectedShape) {
		case "normal" : return "1"; break;
		case "shadowed" : return "2"; break;
	}
}
TETwoGroupedThicknessSliders.prototype.handleEvent = function(event) {
/*		switch (event.type) {
			case "mousedown" : this.handleMouseDown(event); break;
			case "mouseup" : this.handleMouseUp(event); break;
			case "mousedrag" : this.handleMouseDrag(event); break;
		}
*/	
		//console.log("TETwoGroupedThicknessSliders.handleEvent()");
		this.thicknessSlider1.handleEvent(event);
		this.thicknessSlider2.handleEvent(event);
		//console.log("Slider1: ");
		//this.thicknessSlider1.showLinked();
		//console.log("Slider2: ");
		//this.thicknessSlider2.showLinked();
		//console.log("Write new values: ", this.thicknessSlider1.getValue(), this.thicknessSlider2.getValue());
		//this.showHorizontalSliders();
		//console.log("set visible: ",this.thicknessSlider1.horizontalSlider.rectangle.visible);
		this.thicknessSlider1.horizontalSlider.rectangle.visible=true;
		this.thicknessSlider2.horizontalSlider.rectangle.visible=true;
		
}
/*
TETwoGroupedThicknessSliders.prototype.handleMouseDown = function(event) {
}
TETwoGroupedThicknessSliders.prototype.handleMouseUp = function(event) {
}
TETwoGroupedThicknessSliders.prototype.handleMouseDrag = function(event) {
}*/

//////////////////// following methods have not been tested!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
TETwoGroupedThicknessSliders.prototype.setValues = function(t1, t2) {
	//console.log("TETwoGroupedThicknessSliders.setValues(): ",t1,t2);
	this.thicknessSlider1.setValue(t1);
	this.thicknessSlider2.setValue(t2);
}
TETwoGroupedThicknessSliders.prototype.showHorizontalSliders = function() {
	//console.log("TETwoGroupedThicknessSliders.showHorizontalSliders()", this.thicknessSlider1.horizontalSlider.rectangle.visible);
	this.thicknessSlider1.horizontalSlider.show(); //horizontalSlider.show();
	this.thicknessSlider2.horizontalSlider.show();
	//console.log("visible = ", this.thicknessSlider1.horizontalSlider.rectangle.visible);
	//console.log("fillColor = ", this.thicknessSlider1.horizontalSlider.rectangle.fillColor);
	//console.log("strokeColor = ", this.thicknessSlider1.horizontalSlider.rectangle.strokeColor);
	
}
TETwoGroupedThicknessSliders.prototype.hideHorizontalSliders = function() {
	this.thicknessSlider1.horizontalSlider.hide();
	this.thicknessSlider2.horizontalSlider.hide();
}
TETwoGroupedThicknessSliders.prototype.updateValues = function() {
	//console.log("Update slider values: ", this.linkedKnot);
	if (this.linkedEditableToken != null) {
		//var temp = this.linkedKnot.getTensions();
		//var t1 = temp[0],
			//t2 = temp[1];
		this.setValues(t1,t2);
		this.showHorizontalSliders(); // not sure if this is necessary ...
		//console.log("linkedKnot after Update: ", this.linkedKnot);
	}
}
TETwoGroupedThicknessSliders.prototype.linkEditableToken = function(token) {
	//console.log("TETwoGroupedThicknessSliders.linkEditableToken()", token);
	//console.log("Visible? ", this.thicknessSlider1.horizontalSlider.rectangle.visible);
	this.linkedEditableToken = token;
	var index = token.index-1;
	//console.log("Link vector: ", index, token, token.leftVectors[index]);
	this.thicknessSlider1.linkVector(token.leftVectors[0][index]);
	this.thicknessSlider2.linkVector(token.rightVectors[0][index]);
	//console.log("Hi there:", token.leftVectors[index]);
	this.setValues(token.leftVectors[0][index].distance, token.rightVectors[0][index].distance);	
	this.showHorizontalSliders();
	//console.log("Set values: ", token.leftVectors[index].distance, token.rightVectors[index].distance);
	//this.setValues(token.leftVectors[index].distance, token.rightVectors[index].distance);	
}
TETwoGroupedThicknessSliders.prototype.unlinkEditableToken = function() {
	this.linkedEditableToken = null;
	this.hideHorizontalSliders(); 
}
TETwoGroupedThicknessSliders.prototype.updateLabels = function() {
	//console.log("TETwoGroupedThicknessSliders.updateLabels(): ", selectedShape);
	var temp = this.getSelectedShape();
	//console.log("temp = ", temp, this.thicknessSlider1);
	this.thicknessSlider1.title.content = "L" + temp;
	this.thicknessSlider2.title.content = "R" + temp;
}
/*
TETwoGroupedThicknessSliders.prototype.setNewLabels = function() {
	var labels = this.getLabelStrings();
	//console.log("setNewLabels: ", labels);
	
	this.tensionSlider1.setNewLabel(labels[0]);
	this.tensionSlider2.setNewLabel(labels[1]);
}
TETwoGroupedThicknessSliders.prototype.getLabelStrings = function() {
	//console.log("selectedTension: ", selectedTension);
	
	var label1, label2;
	switch (selectedTension) { // use global variable
		case "middle" : label1 = "M1"; label2 = "M2"; break;
		case "left" : label1 = "L1"; label2 = "L2"; break;
		case "right" : label1 = "R1"; label2 = "R2"; break;
		case "locked" : label1 = "A1"; label2 = "A2"; break;
	}
	return [label1, label2];
}
*/
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

// global variables
var mainCanvas = new TECanvas(0,0,800,800);
	
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
	
// rotating axis
var colorParallelRotatingAxisUnselected = '#0f0',
	colorParallelRotatingAxisSelected = '#f00',
	colorMainRotatingAxisSelected = colorParallelRotatingAxisSelected,
	colorMainRotatingAxisUnselected = colorParallelRotatingAxisUnselected;
	
var tangentPrecision = 0.001,
	tangentFixPointMaxIterations = 200,
	tangentBetweenCurvesMaxIterations = 4;
	
var keyPressed = "";
var arrowUp = false,			// global variables for arrow keys
	arrowDown = false,
	arrowLeft = false,
	arrowRight = false,
	ctrlKey = false;
	
var selectedTension = "locked";		// locked = set all three tensions (left, right, middle) to same value; other values for selectedTension: left, middle, right (every tension is handled individually)
var selectedShape = "normal"		// normal = normal outer shape; shadowed = shadowed outer shape
 
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
	this.editor = new TEDrawingArea(this, new Point(100, 450), 4, 1, 10, 10);
	// sliders
	this.tensionSliders = new TETwoGroupedTensionSliders(this, this.editor.rightX+10, this.editor.upperY, 80, this.editor.lowerY-this.editor.upperY);
	this.thicknessSliders = new TETwoGroupedThicknessSliders(this, this.editor.leftX, this.editor.lowerY+30, this.editor.rightX - this.editor.leftX, 70);
}
TECanvas.prototype.handleEvent = function(event) {
	//console.log("TECanvas.handleEvent()");
	if ((event.point.x >= this.x) && (event.point.x <= this.x+this.width) && (event.point.y >= this.y) && (event.point.y <= this.y+this.height)) {
		// instead of identifying object, call all event handlers
		this.editor.handleEvent(event);
		this.tensionSliders.handleEvent(event);
		this.thicknessSliders.handleEvent(event);
		//console.log("thicknessSliders: ", this.thicknessSliders);
	}
	//this.crossUpdateSliderAndFreehandCurve();
}

// enable right clicks
/* doesn't work: unfortunately the oncontextmenu-method is called after the tool.nomouse-methods ...
// which means that you only will know that a right click was made when it is released 
// (and that doesn't seem very helpful ... ;-)
// try to use the button-property of paper.js-event: it's not documented, but wenn inspecting
// the event object in the debugger, the property is clearly there ... !? 
// work with global variables for the buttons
// By the way: there's also information about ALT- and CTRL-Key (could be used in combination
// with mouse events)
// window.oncontextmenu: still needed in order to avoid pop-up menu!
*/
// doesn't work: button property is not set
window.oncontextmenu = function(event) {
	//console.log("rightclick: ", event);
	return false; // avoid popping up of context menu
}
document.onkeydown = checkSpecialKeys; 
function checkSpecialKeys(e) {
	e = e || window.event;
	if (e.ctrlKey) ctrlKey = true;
    else ctrlKey = false;
    if (ctrlKey) {
		if (e.keyCode == '38') {
			arrowUp = true; // up arrow
			//console.log("arrowUP");
		} else if (e.keyCode == '40') {
			arrowDown = true; // down arrow
			//console.log("arrowDown");
		} else if (e.keyCode == '37') {
			arrowLeft = true; // left arrow
			mainCanvas.editor.rotatingAxis.parallelRotatingAxis.selectPreceedingAxis();
			// for following line: see comment in freehand => setKnotType()
			
		} else if (e.keyCode == '39') {
			arrowRight = true; // right arrow
			mainCanvas.editor.rotatingAxis.parallelRotatingAxis.selectFollowingAxis();
			//console.log("arrowRight");
		}	
	} else {
		if (e.keyCode == '38') {
			arrowUp = true; // up arrow
			//console.log("arrowUP");
		} else if (e.keyCode == '40') {
			arrowDown = true; // down arrow
			//console.log("arrowDown");
		} else if (e.keyCode == '37') {
			arrowLeft = true; // left arrow
			mainCanvas.editor.editableToken.selectPreceedingKnot();
			// for following line: see comment in freehand => setKnotType()
			mainCanvas.editor.editableToken.selectedKnot = mainCanvas.editor.editableToken.markedKnot;
			//console.log("arrowLeft");
		} else if (e.keyCode == '39') {
			arrowRight = true; // right arrow
			mainCanvas.editor.editableToken.selectFollowingKnot();
			// for following line: see comment in freehand => setKnotType()
			mainCanvas.editor.editableToken.selectedKnot = mainCanvas.editor.editableToken.markedKnot;
			//console.log("arrowRight");
		}
	}    
	//console.log("e.keyCode/e.ctrlKey: ", e.keyCode, e.ctrlKey);
}
document.onkeyup = function resetSpecialKeys() {
	arrowUp = false;
	arrowDown = false;
	arrowLeft = false;
	arrowRight = false;
}

// work with keyboard events instead
tool.onKeyDown = function(event) {
	keyPressed = event.key;
	if (selectedTension != "locked") {
		switch (keyPressed) {	
			case "m" : selectedTension = "middle"; mainCanvas.tensionSliders.setNewLabels(); mainCanvas.tensionSliders.updateValues(); break;
			case "l" : selectedTension = "left"; mainCanvas.tensionSliders.setNewLabels(); mainCanvas.tensionSliders.updateValues(); break;
			case "r" : selectedTension = "right"; mainCanvas.tensionSliders.setNewLabels(); mainCanvas.tensionSliders.updateValues(); break;
		}
	}
	switch (keyPressed) {	
		// use 't' to toggle between locked and unlocked tensions
		case "t" : selectedTension = (selectedTension == "locked") ? "middle" : "locked"; mainCanvas.tensionSliders.setNewLabels(); mainCanvas.tensionSliders.updateValues(); break;
		case "s" : selectedShape = (selectedShape == "normal") ? "shadowed" : "normal"; mainCanvas.thicknessSliders.updateLabels(); break;
		case "o" : mainCanvas.editor.editableToken.setKnotType("orthogonal"); break;
		case "h" : mainCanvas.editor.editableToken.setKnotType("horizontal"); break;
		case "p" : mainCanvas.editor.editableToken.setKnotType("proportional"); break;
		case "c" : mainCanvas.editor.editableToken.toggleParallelRotatingAxisType(); break;
		case "+" : mainCanvas.editor.rotatingAxis.parallelRotatingAxis.addParallelAxis(); break;
		case "-" : console.log("-"); mainCanvas.editor.rotatingAxis.parallelRotatingAxis.deleteParallelAxis(); break;
	}
	//console.log("Keycode(charCode): ",keyPressed.charCodeAt(0));
	//console.log("KeyEvent: ", event);
}
tool.onKeyUp = function(event) {
	//console.log("KeyEvent: ", event);
	keyPressed = "";
}

tool.onMouseDown = function(event) {
	var newClick = (new Date).getTime();
	mouseDown = true;
	//console.log("mousedown: event: ", event);
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
    mainCanvas.editor.rotatingAxis.controlCircle.unselect(); 
}
