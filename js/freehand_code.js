

// class TEVisuallyModifiableKnot extends TEVisuallyModfiableCircle
function TEVisuallyModifiableKnot(x, y, t1, t2, radius, color, selectedColor, markedColor) {
    this.t1 = t1;
    this.t2 = t2;
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
}
TEEditableToken.prototype.identify = function(item) {
	//console.log("TEEditableToken: item: ", item);
	var value = null;
	for (var i=0; i<this.knotsList.length; i++) {
		//console.log("TEEditableToken(i): ", i, this.knotsList[i]);
		if (item == this.knotsList[i].circle) {
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
TEEditableToken.prototype.handleMouseDown = function(event) {
	this.mouseDown = true;
	this.identifyAndSelectKnot(event.item);
	if (this.selectedKnot != null) {
		this.selectedKnot.handleMouseDown(event);
	}
}
TEEditableToken.prototype.handleMouseUp = function(event) {
	this.mouseDown = false;
	this.selectedKnot.handleMouseUp(event);
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
	//this.identifyAndSelectKnot(event.item);
	//console.log("TEEditabletoken.handleEvent - item/activeKnot.circle: ", event.item, this.activeKnot.circle);
	switch (event.type) {
		case "mousedown" : this.handleMouseDown(event); break;
		case "mouseup" : this.handleMouseUp(event); break;
		case "mousedrag" : this.handleMouseDrag(event); break;
	}
}
TEEditableToken.prototype.insertNewKnot = function(point) {
	var newKnot = new TEVisuallyModifiableKnot(point.x, point.y, 0.5, 0.5, 5, '#f00', '#aaa', '#00f');
	this.knotsList.push(newKnot);
	this.mouseDown = true;
	this.selectedKnot = newKnot;
	this.markedKnot = newKnot; // maybe superfluous
	this.parent.setMarkedCircle(newKnot);
	this.parent.handlingParent = this;
}
