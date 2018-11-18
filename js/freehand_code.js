

// class TEVisuallyModifiableKnot extends TEVisuallyModfiableCircle
function TEVisuallyModifiableKnot(x, y, t1, t2, radius, color, selectedColor, markedColor) {
    this.tensions = [t1, t2];
	TEVisuallyModifiableCircle.prototype.constructor.call(this, new Point(x, y), radius, color, selectedColor, markedColor);
}
TEVisuallyModifiableKnot.prototype = new TEVisuallyModifiableCircle(); 	// inherit
TEVisuallyModifiableKnot.prototype.identify = function(item) {
	if (this.circle == item) return this;
	else return null;
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
	this.mouseDown = false;
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
}
TEEditableToken.prototype.deleteMarkedKnotFromArray = function() {
	// marked knot can be identified by index in editable token
	//console.log("marked knot: ", this.markedKnot, " index: ", this.index);
	// set new selected / marked knot before deleting the actual knot
	end = this.knotsList.length;
	switch (this.index) {
		case 1 : this.selectedKnot = this.knotsList[this.index]; this.markedKnot = this.selectedKnot; break;
		case end : this.selectedKnot = this.knotsList[end-2]; this.markedKnot = this.selectedKnot; break;
		default : this.selectedKnot = this.knotsList[this.index]; this.markedKnot = this.selectedKnot; break;
	}
	this.parent.setMarkedCircle(this.selectedKnot);
	//console.log("BEFORE: length: ", this.knotsList.length);
	
	//this.knotsList[this.index-1].circle = null; // delete control circle
	//this.knotsList[this.index-1].circle.visible = false; // make control circle invisible (should be deleted)
	this.knotsList[this.index-1].circle.remove(); // make control circle invisible (should be deleted)
	this.knotsList.splice(this.index-1, 1); // deletes 1 element at index and reindexes array
	this.parent.rotatingAxis.relativeToken.knotsList.splice(this.index-1,1); // do the same with relative token
	this.parent.fhToken.removeSegment(this.index-1); // do the same with path
	this.parent.updateFreehandPath();
	//console.log("AFTER: length: ", this.knotsList.length);
	
}
TEEditableToken.prototype.handleMouseDown = function(event) {
	this.mouseDown = true;
	this.identifyAndSelectKnot(event.item);
	if (this.selectedKnot != null) {
		this.selectedKnot.handleMouseDown(event);
	}
}
TEEditableToken.prototype.handleMouseUp = function(event) {
	this.mouseDown = false;
	if (this.selectedKnot != null) this.selectedKnot.handleMouseUp(event); // catch error (selectedKnot can be null when clicking fast)
	this.selectedKnot = null;	// leave markedKnot
}
TEEditableToken.prototype.handleMouseDrag = function(event) {
	if (this.mouseDown) {
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

TEEditableToken.prototype.insertNewKnot = function(point) {
	var newKnot = new TEVisuallyModifiableKnot(point.x, point.y, 0.5, 0.5, 5, '#f00', '#aaa', '#00f');
	//console.log("splice at: ", this.index);
	this.knotsList.splice(this.index, 0, newKnot);
	//this.index = this.knotsList.length;
	this.mouseDown = true;
	this.selectedKnot = newKnot;
	this.markedKnot = newKnot; // maybe superfluous
	this.parent.setMarkedCircle(newKnot);
	this.parent.handlingParent = this;
	//this.parent.rotatingAxis.relativeToken.pushNewRelativeKnot(point.x, point.y, "horizontal");
	this.parent.rotatingAxis.relativeToken.insertNewRelativeKnot(point.x, point.y, "horizontal", this.index);
	this.index += 1; // point to the newly inserted element
	//console.log("incremented index = ", this.index);
}
