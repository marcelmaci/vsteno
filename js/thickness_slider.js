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
	if ((vector != undefined) && (vector != null)) {
		this.linkedVector = vector;
		this.setValue(vector.distance);
		this.horizontalSlider.show();
	}
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
TETwoGroupedThicknessSliders.prototype.getSelectedShapeAsString = function() {
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
		
		//this.thicknessSlider1.horizontalSlider.rectangle.visible=true;
		//this.thicknessSlider2.horizontalSlider.rectangle.visible=true;
		
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
	//console.log("TETwoGroupedThicknessSliders.showHorizontalSliders()", this.linkedVector, this.linkedVector.knotsList.length);
	
	if ((this.linkedVector != null) && (this.linkedVector != undefined) && (this.linkedVector.knotsList.length>0)) {
		this.thicknessSlider1.horizontalSlider.show(); //horizontalSlider.show();
		this.thicknessSlider2.horizontalSlider.show();
		//console.log("visible = ", this.thicknessSlider1.horizontalSlider.rectangle.visible);
		//console.log("fillColor = ", this.thicknessSlider1.horizontalSlider.rectangle.fillColor);
		//console.log("strokeColor = ", this.thicknessSlider1.horizontalSlider.rectangle.strokeColor);
	}
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
	if ((token != null) && (token != undefined) && (token.knotsList.length>0)) {
		this.linkedEditableToken = token;
		var index = token.index-1;
		//console.log("Link vector: ", index, token, token.leftVectors[index]);
		var actualShape = this.parent.editor.getSelectedShapeIndex();
		
		//console.log(index, token);
		if (token.leftVectors[actualShape][index] != undefined) { // this is only a quick fix ... there's something wrong with that
			this.thicknessSlider1.linkVector(token.leftVectors[actualShape][index]);
			this.thicknessSlider2.linkVector(token.rightVectors[actualShape][index]);
			//console.log("Hi there:", token.leftVectors[index]);
			this.setValues(token.leftVectors[actualShape][index].distance, token.rightVectors[actualShape][index].distance);	
		}
		this.showHorizontalSliders();
		//console.log("Set values: ", token.leftVectors[index].distance, token.rightVectors[index].distance);
		//this.setValues(token.leftVectors[index].distance, token.rightVectors[index].distance);	
	}
}
TETwoGroupedThicknessSliders.prototype.unlinkEditableToken = function() {
	this.linkedEditableToken = null;
	this.hideHorizontalSliders(); 
}
TETwoGroupedThicknessSliders.prototype.setOuterShapesVisibility = function() {
	//console.log("TETwoGroupedThicknessSliders.setOuterShapesVisibility(): this.parent.editor.editableToken.outerShape[]: ", this.parent.editor.editableToken.outerShape);
	//console.log("TETwoGroupedThicknessSliders.setOuterShapesVisibility(): this.parent.editor.editableToken.leftVectors[0]: ", this.parent.editor.editableToken.leftVectors[0]);
	//console.log("TETwoGroupedThicknessSliders.setOuterShapesVisibility(): this.parent.editor.editableToken.rightVectors[0]: ", this.parent.editor.editableToken.rightVectors[0]);
	
	switch (selectedShape) {
		case "normal" : if (selectedShapeFill) this.parent.editor.editableToken.outerShape[0].fillColor = selectedShapeFillColor;
						else this.parent.editor.editableToken.outerShape[0].fillColor = null;
						this.parent.editor.editableToken.outerShape[0].visible = true; 
						this.parent.editor.editableToken.outerShape[1].visible = false;
						// additionally, vectors must be set to invisible / invisible
						for (var i=0; i<this.parent.editor.editableToken.knotsList.length; i++) {
							this.parent.editor.editableToken.leftVectors[0][i].line.visible = true; 
							this.parent.editor.editableToken.rightVectors[0][i].line.visible = true;
							this.parent.editor.editableToken.leftVectors[1][i].line.visible = false; 
							this.parent.editor.editableToken.rightVectors[1][i].line.visible = false;
						}
						break;
		case "shadowed" : if (selectedShapeFill) this.parent.editor.editableToken.outerShape[1].fillColor = selectedShapeFillColor; 
						  else this.parent.editor.editableToken.outerShape[1].fillColor = null;
						  this.parent.editor.editableToken.outerShape[0].visible = false; 
						  this.parent.editor.editableToken.outerShape[1].visible = true;
						  for (var i=0; i<this.parent.editor.editableToken.knotsList.length; i++) {
							  this.parent.editor.editableToken.leftVectors[0][i].line.visible = false; 
							  this.parent.editor.editableToken.rightVectors[0][i].line.visible = false;
							  this.parent.editor.editableToken.leftVectors[1][i].line.visible = true; 
							  this.parent.editor.editableToken.rightVectors[1][i].line.visible = true;
						   }
						   break;
	}
	this.parent.editor.updateFreehandPath();
}
TETwoGroupedThicknessSliders.prototype.updateLabels = function() {
	//console.log("TETwoGroupedThicknessSliders.updateLabels(): ", selectedShape);
	var temp = this.getSelectedShapeAsString();
	//console.log("temp = ", temp, this.thicknessSlider1);
	this.thicknessSlider1.title.content = "L" + temp;
	this.thicknessSlider2.title.content = "R" + temp;
	// update visibility of outer shape at the same time (I know: not the orthodox place to do that, but practical ... ;-)
	this.setOuterShapesVisibility();
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
