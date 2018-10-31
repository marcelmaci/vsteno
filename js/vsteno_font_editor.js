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

// class TERotatingAxis
function TERotatingAxis(drawingArea, color) {
	this.parent = drawingArea;
	this.absBasePosition = this.parent.lowerY - (this.parent.basePosition * this.parent.lineHeight * this.parent.scaleFactor);
	this.centerRotatingAxis = new Point((this.parent.rightX - this.parent.leftX)/2+this.parent.leftX, this.absBasePosition);
	this.inclinationRotatingAxis = 90; // default = 90° = vertical
	this.tempColor = null;
	this.line = new Path.Line([this.centerRotatingAxis.x, this.parent.lowerY], [this.centerRotatingAxis.x, this.parent.upperY]);
	this.line.strokeColor = color;
	this.controlCircle = new Path.Circle( new Point(this.centerRotatingAxis.x, this.parent.upperY), 5);
	this.controlCircle.fillColor = color;
}
TERotatingAxis.prototype.isRotatingAxisControlCircle = function( item ) {
	if (item == this.controlCircle) return true;
	else return false;
}
TERotatingAxis.prototype.handleMouseDown = function(event) {
	console.log("rotatingAxis.mousedown");	
	this.tempColor = this.controlCircle.fillColor;
	this.controlCircle.fillColor = "#aaa";
	this.controlCircle.position = event.point;
	console.log(this.rotatingAxis);
}
TERotatingAxis.prototype.handleMouseUp = function(event) {
	console.log("rotatingAxis.mouseup");
	this.line.segments[0].point = [this.centerRotatingAxis.x, this.centerRotatingAxis.y];
	this.line.segments[1].point = event.point;
	
	this.controlCircle.fillColor = this.tempColor;
	this.controlCircle.position = event.point;
	this.parent.itemSelected = this.parent;
	this.parent.fhCircleSelected = null;
}
TERotatingAxis.prototype.handleMouseDrag = function(event) {
	console.log("rotatingAxis.mousedrag");
	console.log(this);
	this.line.segments[0].point = [this.centerRotatingAxis.x, this.centerRotatingAxis.y];
	this.line.segments[1].point = event.point;
	this.controlCircle.position = event.point;
}
TERotatingAxis.prototype.handleEvent = function( event ) {
	console.log("rotatingAxis.handleEvent()");
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
	
	console.log(lowerLeft, totalLines, basePosition, lineHeight, scaleFactor);
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
	console.log(this.fhToken);
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
	index = null;
	for (var i = 0; i<this.fhCircleList.length; i++) {
			if (this.fhCircleList[i] == circle) index = i;
			console.log("search for circle: fhCircleList[" + i + "] = " + this.fhCircleList[i] + "<=?=>" + circle + "=> " + index);
	}
	return index;
}
TEDrawingArea.prototype.isInsideBorders = function( event ) {
	if ((this.leftX <= event.point.x) && (this.rightX >= event.point.x) && (this.lowerY >= event.point.y) && (this.upperY <= event.point.y)) return true;
	else return false;
}
TEDrawingArea.prototype.isPartOfFreehandOrRotatingAxis = function( item ) {
	if ((this.isPartOfFreehand(item)) || (item == this.rotatingAxis.controlCircle)) return true;
	else return false;
}
TEDrawingArea.prototype.handleMouseDown = function( event ) {
	console.log("In onMouseDown");
	//console.log(event.item,this.isPartOfFreehandOrRotatingAxis(this.fhCircleSelected));
	if ((event.item != null) && (this.isPartOfFreehandOrRotatingAxis(this.fhCircleSelected))) {
		//this.itemSelected = (event.item == this.rotatingAxis.controlCircle) ? this.rotatingAxis : this;
		//this.fhCircleSelected = event.item;	
		this.fhCircleColor = this.fhCircleSelected.fillColor;
		this.fhCircleSelected.fillColor = '#aaa';			
	} else {
		var path = new Path.Circle( {
				center: event.point,
				radius: 5,
				fillColor: '#f00'	
			});
			console.log(this.fhCircleList);
			
		this.fhCircleList.push( path );
		console.log("CircleList", this.fhCircleList);
		this.fhCircleSelected = path;
		this.fhCircleColor = this.fhCircleSelected.fillColor;
		// add bezier to freehand path
		this.fhToken.add( event.point );
		this.calculateFreehandHandles();	
	}
}
TEDrawingArea.prototype.handleMouseUp = function( event ) {
	console.log("In onMouseUp");
	if (this.fhCircleSelected != null) {
		this.fhCircleSelected.fillColor = this.fhCircleColor;
		this.fhCircleSelected = null;
		this.itemSelected = this;
	}
}
TEDrawingArea.prototype.handleMouseDrag = function( event ) {
	console.log("In onMouseDrag");
	if (editor.fhCircleSelected != null) {
		index = this.whichCircle( this.fhCircleSelected );
		this.fhCircleSelected.position = event.point; //new Point(1,1); //event.delta;
		this.fhToken.segments[index].point = this.fhCircleSelected.position;
		this.calculateFreehandHandles();
	}
}
TEDrawingArea.prototype.handleEvent = function( event ) {
	console.log("TEDrawingArea.handleEvent()");
	console.log(event.item);
	if ((this.fhCircleSelected == null) && (event.item != null) && (this.isPartOfFreehandOrRotatingAxis(event.item))) {
		this.itemSelected = (event.item == this.rotatingAxis.controlCircle) ? this.rotatingAxis : this;
		this.fhCircleSelected = event.item;	
	}
	
	if ((this.isInsideBorders(event)) || (event.type == "mouseup")) { 
		console.log("Ok, it's my business");
		switch (event.type) {
			case "mousedown" :this.itemSelected.handleMouseDown(event); break;
			case "mouseup" : this.itemSelected.handleMouseUp(event); break;
			case "mousedrag" : this.itemSelected.handleMouseDrag(event); break;
		}
	} else {
		console.log("Thx, but it's not my business");
	}
}

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

console.log(editor);
