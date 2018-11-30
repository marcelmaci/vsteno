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
	console.log("Update slider values: ", this.linkedKnot);
	if (this.linkedKnot != null) {
		var temp = this.linkedKnot.getTensions();
		var t1 = temp[0],
			t2 = temp[1];
		this.setValues(t1,t2);
		this.showVerticalSliders(); // not sure if this is necessary ...
		console.log("linkedKnot after Update: ", this.linkedKnot);
	
	}
}
TETwoGroupedTensionSliders.prototype.setNewLabels = function() {
	var labels = this.getLabelStrings();
	console.log("setNewLabels: ", labels);
	
	this.tensionSlider1.setNewLabel(labels[0]);
	this.tensionSlider2.setNewLabel(labels[1]);
}
TETwoGroupedTensionSliders.prototype.getLabelStrings = function() {
	console.log("selectedTension: ", selectedTension);
	
	var label1, label2;
	switch (selectedTension) { // use global variable
		case "middle" : label1 = "M1"; label2 = "M2"; break;
		case "left" : label1 = "L1"; label2 = "L2"; break;
		case "right" : label1 = "R1"; label2 = "R2"; break;
		case "locked" : label1 = "A1"; label2 = "A2"; break;
	}
	return [label1, label2];
}
