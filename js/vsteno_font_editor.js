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
 
 
// bezier function
function getControlPoints( p0, p1, p2, t) {
    var d01=Math.sqrt(Math.pow(p0.x-p1.x,2)+Math.pow(p1.y-p0.y,2));
    var d12=Math.sqrt(Math.pow(p2.x-p1.x,2)+Math.pow(p2.y-p1.y,2));
    var fa=t*d01/(d01+d12);   // scaling factor for triangle Ta
    var fb=t*d12/(d01+d12);   // ditto for Tb, simplifies to fb=t-fa
    var p1x=p1.x-fa*(p2.x-p0.x);    // x2-x0 is the width of triangle T
    var p1y=p1.y-fa*(p2.y-p0.y);    // y2-y0 is the height of T
    var p2x=p1.x+fb*(p2.x-p0.x);
    var p2y=p1.y+fb*(p2.y-p0.y);  
    return [ new Point( p1x, p1y ), new Point( p2x, p2y ) ]
}

function calculateFreehandHandles(a) { // a = TEDrawingArea
	console.log(a.fhToken);
	numberOfPoints = a.fhToken.segments.length;
	for (var i = 1; i < numberOfPoints-1; i++) { // dont calculate 1st and last
			var absHandles = getControlPoints( a.fhToken.segments[i-1].point, a.fhToken.segments[i].point, a.fhToken.segments[i+1].point, 0.5 );
			a.fhToken.segments[i].handleIn = absHandles[0] - a.fhToken.segments[i].point;
			a.fhToken.segments[i].handleOut = absHandles[1] - a.fhToken.segments[i].point;
	}
}

function isPartOfFreehand(a, test) {
	return whichCircle(a, test);
}

function whichCircle(a, circle) {
	index = null;
	for (var i = 0; i<a.fhCircleList.length; i++) {
			if (a.fhCircleList[i] == circle) index = i;
	}
	return index;
}

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
	for (var i=1; i<lineHeight; i++) {
		dasharrayDotted.push( dasharrayDottedBase[0]);
		dasharrayDotted.push( dasharrayDottedBase[1]);
	}
	console.log(dasharrayDotted);
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
		this.allSystemLines.push( new Path.Line( [ upperLeft.x, y ], [ lowerRight.x, y ]));
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

// class TERotatingAxis
function TERotatingAxis(a, color) {
	var rotatingAxis = new Path.Line([a.centerRotatingAxis.x, a.lowerY], [a.centerRotatingAxis.x, a.upperY]);
	rotatingAxis.strokeColor = color;
	return rotatingAxis;
}

// class TEDrawingArea 	
function TEDrawingArea(lowerLeft, totalLines, basePosition, lineHeight, scaleFactor) {
	
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

	// rotating axis
	this.absBasePosition = this.lowerY - (this.basePosition * this.lineHeight * this.scaleFactor);
	this.centerRotatingAxis = new Point((this.rightX - this.leftX)/2+this.leftX, this.absBasePosition);
	console.log(this.rightX, this.leftX, this.centerRotatingAxis);
	this.inclinationRotatingAxis = 90; // default = 90° = vertical
	
	// insert graphical elements
	this.borders = new TEBorders(this, '#000');
	this.dottedGrid = new TEDottedGrid(this, '#000');
	this.auxiliarySystemLines = new TEAuxiliarySystemLines(this, '#000');
	this.auxiliaryVerticalLines = new TEAuxiliaryVerticalLines(this, '#000');
	this.rotatingAxis = new TERotatingAxis(this, '#0f0');
	
	// freehand path
	this.fhCircleSelected = null;
	this.fhCircleColor = null;
	this.fhToken = new Path();
	this.fhToken.strokeColor = '#000';
	this.fhCircleList = [];

	return this;
}

// event handlers
tool.onMouseDown = function( event ) {
	if ((event.item != null) && (isPartOfFreehand(editor, event.item) !== null)) {
		editor.fhCircleSelected = event.item;
		editor.fhCircleColor = editor.fhCircleSelected.fillColor;
		editor.fhCircleSelected.fillColor = '#aaa';
	} else {
		var path = new Path.Circle( {
			center: event.point,
			radius: 5,
			fillColor: '#f00'	
		});
		editor.fhCircleList.push( path );
		//console.log("CircleList", editor.fhCircleList);
		editor.fhCircleSelected = path;
		editor.fhCircleColor = editor.fhCircleSelected.fillColor;
		// add bezier to freehand path
		editor.fhToken.add( event.point );
		calculateFreehandHandles(editor);
	}
}

tool.onMouseUp = function( event ) {
	editor.fhCircleSelected.fillColor = editor.fhCircleColor;
	editor.fhCircleSelected = null;
}

tool.onMouseDrag = function( event ) {
	if (editor.fhCircleSelected != null) {
		index = whichCircle( editor, editor.fhCircleSelected );
		editor.fhCircleSelected.position += event.delta; //new Point(1,1); //event.delta;
		editor.fhToken.segments[index].point = editor.fhCircleSelected.position;
		calculateFreehandHandles(editor);
	}
}

function DrawCircles( path ) {
	numb_points = path.segments.length;
	for (var i=0; i<numb_points; i++) {
			var center = path.segments[i].point;
			var new_circle = new Path.Circle( center, 5 );
			new_circle.fillColor = '#00f';
	}
}

var editor = TEDrawingArea(new Point(100, 500), 4, 1, 10,10);

console.log(editor);
