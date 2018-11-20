
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
	this.knotsList = []; // type: TEVisuallyModifiableKnot
	this.vectorsLeft = [];
	this.vectorsRight = [];
	// paths
	this.middlePath = null;
	this.leftPath = null;
	this.rightPath = null;
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
*/
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
	//console.log(this
	this.parent.parent.tensionSliders.setValues(this.selectedKnot.tensions[0], this.selectedKnot.tensions[1]); // ok, this is a monkey jumping from one tree to another ..., but it works ... ;-)
}
TEEditableToken.prototype.handleMouseDown = function(event) {
	this.identifyAndSelectKnot(event.item);
	if (this.selectedKnot != null) {
		this.parent.parent.tensionSliders.link(this.selectedKnot);
		this.selectedKnot.handleMouseDown(event);
	}
}
TEEditableToken.prototype.handleMouseUp = function(event) {
	///*this.*/mouseDown = false;
	if (this.selectedKnot != null) this.selectedKnot.handleMouseUp(event); // catch error (selectedKnot can be null when clicking fast)
	this.selectedKnot = null;	// leave markedKnot
}
TEEditableToken.prototype.handleMouseDrag = function(event) {
	if (/*this.*/mouseDown) {
		if (this.selectedKnot != null) {
			this.selectedKnot.handleMouseDrag(event);
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
	//console.log("We're here");
	this.parent.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, this.index-1);
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
	console.log("new knot: index/length: ", index, this.knotsList.length);
	var index = this.index;
	var length = this.knotsList.length;
	var value = null;
	if (index == length) { console.log("exitKnot"); return colorExitKnot; }
	else if (index == 0) { console.log("entryKnot"); return colorEntryKnot; }
	else if (index == 1) { console.log("pivot1Knot"); return colorPivot1; }
	else if (index == length-1) {console.log("pivot2Knot"); return colorPivot2; }
	else { console.log("normalKnot"); return colorNormalKnot; }
}
TEEditableToken.prototype.getDeleteKnotTypeColor = function() {
	// knot will be deleted at this.index
	var index = this.index;
	var length = this.knotsList.length;
	var value = null;
	console.log("delete knot: index/length: ", index, this.knotsList.length);
	if (index == length) { console.log("exitKnot"); return colorExitKnot; }
	else if (index == 1) { console.log("entryKnot"); return colorEntryKnot; }
	else if (index == 2) { console.log("pivot1Knot"); return colorPivot1; }
	else if (index == length-1) {console.log("exitKnot"); return colorExitKnot; }
	else { console.log("normalKnot"); return colorNormalKnot; }
}
TEEditableToken.prototype.insertNewKnot = function(point) {
	//write a function that determines the color of the knot before inserting it!!!
	var test = this.getNewKnotTypeColor();
	console.log("index/color: ", this.index, test);
	
	var newKnot = new TEVisuallyModifiableKnot(point.x, point.y, 0.5, 0.5, 5, test, colorSelectedKnot, colorMarkedKnot);
	//console.log("splice at: ", this.index);
	this.knotsList.splice(this.index, 0, newKnot);
	// automatically define knot type with autodefine
	if (knotTypeAutoDefine) this.redefineKnotTypesAndSetColors();
	//this.knotsList[this.index+1].originalColor = this.knotsList[this.index+1].fillColor; // "color hack" ... should be implemented properly ...
	
	//this.index = this.knotsList.length;
	///*this.*/mouseDown = true;
	this.selectedKnot = newKnot;
	this.parent.parent.tensionSliders.link(this.selectedKnot);
	this.markedKnot = newKnot; // maybe superfluous
	this.parent.setMarkedCircle(newKnot);
	this.parent.handlingParent = this;
	//this.parent.rotatingAxis.relativeToken.pushNewRelativeKnot(point.x, point.y, "horizontal");
	this.parent.rotatingAxis.relativeToken.insertNewRelativeKnot(point.x, point.y, "horizontal", this.index);
	this.index += 1; // point to the newly inserted element
	//console.log("incremented index = ", this.index);
	// automatically define knot type with autodefine
	//if (knotTypeAutoDefine) this.defineKnotTypesAndSetColors();
}
TEEditableToken.prototype.deleteMarkedKnotFromArray = function() {
	// marked knot can be identified by index in editable token
	//console.log("marked knot: ", this.markedKnot, " index: ", this.index);
	// set new selected / marked knot before deleting the actual knot
	var end = this.knotsList.length;
	switch (this.index) {
		//case 1 : this.selectedKnot = this.knotsList[this.index]; this.markedKnot = this.selectedKnot; break;
		case end : this.selectedKnot = this.knotsList[end-2]; this.markedKnot = this.selectedKnot; break;
		default : this.selectedKnot = this.knotsList[this.index]; this.markedKnot = this.selectedKnot; break;
	}
	var color = this.getDeleteKnotTypeColor();
	this.selectedKnot.originalColor = color;
	this.selectedKnot.circle.fillColor = color;
	
	this.parent.setMarkedCircle(this.selectedKnot);
	//console.log("BEFORE: length: ", this.knotsList.length);
	
	//this.knotsList[this.index-1].circle = null; // delete control circle
	//this.knotsList[this.index-1].circle.visible = false; // make control circle invisible (should be deleted)
	this.knotsList[this.index-1].circle.remove(); // make control circle invisible (should be deleted)
	this.knotsList.splice(this.index-1, 1); // deletes 1 element at index and reindexes array
	
	this.parent.rotatingAxis.relativeToken.knotsList.splice(this.index-1,1); // do the same with relative token
	this.parent.fhToken.removeSegment(this.index-1); // do the same with path
	// automatically define knot type with autodefine
	if (knotTypeAutoDefine) this.redefineKnotTypesAndSetColors();
	
	this.parent.updateFreehandPath();
	//console.log("AFTER: length: ", this.knotsList.length);
	
}
