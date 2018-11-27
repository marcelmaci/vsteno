
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
TEEditableToken.prototype.getRelativeToken = function() {
	console.log("this.index: ", this.index);
	return this.parent.rotatingAxis.relativeToken.knotsList[this.index-1];
}
TEEditableToken.prototype.setKnotType = function(type) {
	var relativeToken = this.getRelativeToken();
	relativeToken.setType(type);
	//console.log("settype");
	switch (type) {
		case "orthogonal" : this.selectedKnot.changeCircleToRectangle(); 
							var x = this.selectedKnot.circle.position.x,
								y = this.selectedKnot.circle.position.y;
							this.parent.rotatingAxis.calculateOrthogonalIntersectionWithRotatingAxis(x, y);
							break;
		case "horizontal" : this.selectedKnot.changeRectangleToCircle(); break;
	}
}
TEEditableToken.prototype.handleMouseDown = function(event) {
	this.identifyAndSelectKnot(event.item);
	if (this.selectedKnot != null) {
	  	console.log("keypressed+mouse: ", keyPressed, event.point, this.selectedKnot);
		// placed here from bottom - not sure if this is correct!?!
		this.parent.parent.tensionSliders.link(this.selectedKnot);
		this.selectedKnot.handleMouseDown(event);
		this.parent.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, this.index-1);

		switch (keyPressed) {
			case "o" : this.setKnotType("orthogonal"); break;
			case "h" : this.setKnotType("horizontal"); break;
		}
		console.log("Afterwards: ", keyPressed, event.point, this.selectedKnot);
		
		//this.parent.parent.tensionSliders.link(this.selectedKnot);
		//this.selectedKnot.handleMouseDown(event);
		//this.parent.rotatingAxis.relativeToken.updateRelativeCoordinates(event.point.x, event.point.y, this.index-1);
	}
}
TEEditableToken.prototype.handleMouseUp = function(event) {
	///*this.*/mouseDown = false;
	if (this.selectedKnot != null) {
		//console.log("change rectangle to circle");
		//console.log("MouseUp: rightclick: ", rightClick);
		if (keyPressed == "o") {
		//	var relativeToken = this.getRelativeToken();
	//		relativeToken.setType("horizontal");	
	//		this.selectedKnot.changeRectangleToCircle();
		}
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
	console.log("TEEditableToken.handleEvent");
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
