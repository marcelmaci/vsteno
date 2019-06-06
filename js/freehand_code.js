
// definition header (new, i.e. different from SE1)
/*
offset: meaning
 0: "virtual" or "real" token
 1: width (not necessary)
 2: height (not necessary)
 3: additional width left
 4: additional width right
 5: conditional deltaY before (virtual token: value / real token: factor)
 6: conditional deltaY after (virtual token: value / real token: factor)
 7: inconditional deltaY before
 8: inconditional deltaY after
10: shadowed
*/
// class TEKnotVector
function TEKnotVector(distance, type) {
	//console.log("TEKnotVector.constructor");
	this.type = "orthogonal"; // make it fix for the moment (change it to type later)
	this.distance = distance;
	this.line = Path.Line(new Point(0,0), new Point(0,0));		// THIS LINE IS WRONG: CREATE LINE OBJECT THAT WILL NEVER BE DELETED => FIX IT LATER
	this.line.strokeColor = '#000';
	this.line.visible = false;
	//this.line = null;
}

// class TEKnotType
function TEKnotType() {
	this.entry = false;
	this.exit = false;
	this.pivot1 = false;
	this.pivot2 = false;
	// elements for compatibility with SE1
	this.lateEntry = false;
	this.earlyExit = false;
	this.combinationPoint = false;	// for token combiner
	this.connect = true;
	this.intermediateShadow = false;
}
/* doesn't work for imported tokens: export_variable only contains data, no methods! */
// solved by copying only data from JSKnotType to TEKnotType when loading token from actualFont
TEKnotType.prototype.getKnotTypesAsString = function() {
	var output = "SE1-Knottype:";
	output += (this.entry) ? " entry" : "";
	output += (this.exit) ? " exit" : "";
	output += (this.pivot1) ? " pivot1" : "";
	output += (this.pivot2) ? " pivot2" : "";
	output += (this.lateEntry) ? " lateEntry" : "";
	output += (this.earlyExit) ? " earlyExit" : "";
	output += (this.combinationPoint) ? " combinationPoint" : "";
	output += (this.connect) ? " connect" : "";
	output += (this.intermediateShadow) ? " intermediateShadow" : "";
	return output;
}
TEKnotType.prototype.setKnotType = function(type) {
	switch (type) {
		case "entry" : this.entry = true; break;
		case "exit" : this.exit = true; break;
		case "pivot1" : this.pivot1 = true; break;
		case "pivot2" : this.pivot2 = true; break;
		case "normal" : this.entry = false; this.exit = false; this.pivot1 = false; this.pivot2 = false; break;
		case "earlyExit" : this.earlyExit = true; break;
		case "lateEntry" : this.lateEntry = true; break;
		case "combinationPoint" : this.combinationPoint = true; break;
		case "connect" : this.connect = true; break;
		case "intermediateShadow" : this.intermediateShadow = true; break;
	}
	console.log(this);
}
TEKnotType.prototype.getKnotType = function(type) {
	switch (type) {
		case "entry" : return this.entry; break;
		case "exit" : return this.exit; break;
		case "pivot1" : return this.pivot1; break;
		case "pivot2" : return this.pivot2; break;
		//case "normal" :  break;
		case "earlyExit" : return this.earlyExit; break;
		case "lateEntry" : return this.lateEntry; break;
		case "combinationPoint" : return this.combinationPoint; break;
		case "connect" : return this.connect; break;
		case "intermediateShadow" : return this.intermediateShadow; break;
	}
}
TEKnotType.prototype.toggleKnotType = function(type) {
	switch (type) {
		case "entry" : this.entry = (this.entry) ? false : true; break;
		case "exit" : this.exit = (this.exit) ? false : true; break;
		case "pivot1" : this.pivot1 = (this.pivot1) ? false : true; break;
		case "pivot2" : this.pivot2 = (this.pivot2) ? false : true; break;
		case "earlyExit" : this.earlyExit = (this.earlyExit) ? false : true;break;
		case "lateEntry" : this.lateEntry = (this.lateEntry) ? false : true; break;
		case "combinationPoint" : this.combinationPoint = (this.combinationPoint) ? false : true; break;
		case "connect" : this.connect = (this.connect) ? false : true; break;
		case "intermediateShadow" : this.intermediateShadow = (this.intermediateShadow) ? false : true; break;
	}
	console.log(this);
}

// class TEVisuallyModifiableKnotTEVisuallyModifiableKnot extends TEVisuallyModfiableCircle
function TEVisuallyModifiableKnot(x, y, t1, t2, radius, color, selectedColor, markedColor, link) {
    this.linkToRelativeKnot = link;
    this.type = new TEKnotType();
    
    // the following 3 properties will become obsolete
    this.shiftX = 0.0;	// shifting values for additional rotating axis
	this.shiftY = 0.0;  // now, if you believe that this is a constructor that will set shifX/Y to number 0, forget it! ShiftX/Y are reported as NaN ... (I hate JS ...) 
						// ok, got it: shiftX = 0 leads to NaN, shiftX = 0.0 leads to 0 ... (did I mention that I hate JS ... ?!)
    this.parallelRotatingAxisType = "horizontal"; // horizontal: shiftX is horizontal; orthogonal: shiftX is orthogonal (= compensation for inclination angle)
    // they are replaced by a link to a parallel rotation axis
    this.linkToParallelRotatingAxis = null;	// will be set by setKnotType
    
    
    this.tensions = [t1, t2, t1, t2, t1, t2];	// tensions must be controlled individually for left, middle and right path/outer shape (set them all to the same value to start)
	TEVisuallyModifiableCircle.prototype.constructor.call(this, new Point(x, y), radius, color, selectedColor, markedColor);
}
TEVisuallyModifiableKnot.prototype = new TEVisuallyModifiableCircle(); 	// inherit
TEVisuallyModifiableKnot.prototype.setKnotType = function(type) {
	this.type.setKnotType(type);
}
TEVisuallyModifiableKnot.prototype.getKnotType = function(type) {	// returns true if knot is type
	return this.type.getKnotType(type);
}
TEVisuallyModifiableKnot.prototype.toggleKnotType = function(type) {
	this.type.toggleKnotType(type);
}
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
	this.header = [];	// array for header elements
	for (var i=0;i<24;i++) this.header[i] = 0;
	
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
	//console.log("actual type: ", actualType);
	this.selectedKnot.parallelRotatingAxisType = (actualType == "horizontal") ? "orthogonal" : "horizontal";
	//console.log("new type: ", this.selectedKnot.parallelRotatingAxisType);
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
	// more ... ?! => seems to work
	
	// new: knot is linked to parallel rotating axis
	//this.markedKnot.linkToParallelRotatingAxis = this.parent.rotatingAxis.parallelRotatingAxis.getLinkToParallelRotatingAxis();
	
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
		case "proportional" : 
							// new: knot is linked to parallel rotating axis
							// for test: just link the knot for the moment
							this.markedKnot.linkToParallelRotatingAxis = this.parent.rotatingAxis.parallelRotatingAxis.getLinkToParallelRotatingAxis();
							//console.log("Type rotating axis: ", this.markedKnot.linkToParallelRotatingAxis.type);
							// try the easy solution first: use old shiftX and type properties and copy values instead of linking
							this.markedKnot.shiftX = this.markedKnot.linkToParallelRotatingAxis.shiftX;
							this.markedKnot.parallelRotatingAxisType = this.markedKnot.linkToParallelRotatingAxis.type;
							//console.log("shiftX/type: ", this.markedKnot.shiftX, this.markedKnot.parallelRotatingAxisType);
							
							this.markedKnot.changeKnotToProportional();
							var x = this.selectedKnot.circle.position.x,
								y = this.selectedKnot.circle.position.y;
							var relative = this.parent.rotatingAxis.getRelativeCoordinates(this.selectedKnot, "proportional");
							// console.log("setKnotType(proportional): relative[]: ", relative);
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
		this.knotsList[i].type.pivot2 = false;
		this.knotsList[i].circle.fillColor = colorNormalKnot;
		// set thicknesses to 1
		//this.leftVectors[0][i].distance = 1;
		//this.rightVectors[0][i].distance = 1;
	}
	// set new types
	if ((this.knotsList != null) && (this.knotsList != undefined) && (this.knotsList.length>0)) {
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
TEEditableToken.prototype.insertNewKnotFromActualFont = function(point, type) {
	// same code as insertNewKnot, but with type as parameter to set color
	var newColor;
	if (type.entry) newColor = colorEntryKnot;
	else if (type.exit) newColor = colorExitKnot;
	else if (type.pivot1) newColor = colorPivot1;
	else if (type.pivot2) newColor = colorPivot2;
	else if (type.earlyExit) newColor = colorNormalKnot;		// possibility to define other colors
	else if (type.lateEntry) newColor = colorNormalKnot;
	else if (type.combinationPoint) newColor = colorNormalKnot;
	else if (type.intermediateShadow) newColor = colorNormalKnot;
	else newColor = colorNormalKnot;
	
	
	
	// code from insertNewKnot() => make this a new function that can be called from both functions (avoid duplicate code) => fix that later
	//console.log("TEEditableToken.insertNewKnot(): ", point, this.index);
	// get color of new knot before inserting it
//	var newColor = this.getNewKnotTypeColor();
	// insert knot
	var newKnot = new TEVisuallyModifiableKnot(point.x, point.y, 0.5, 0.5, 5, newColor, colorSelectedKnot, colorMarkedKnot, null);
	//console.log("newKnot: ", newKnot);
	this.knotsList.splice(this.index, 0, newKnot);
	//var newLength = this.knotsList.length;
	// insert knot vectors for outer shape
	//var distance = ((this.index == 0) || (this.index == newLength-1)) ? 0 : 1; 	// 0 = no pencil thickness, 1 = maximum thickness
	// define vectors for normal shape
	var distance = 1;
	var leftVector = new TEKnotVector(distance, "orthogonal");
	var rightVector = new TEKnotVector(distance, "orthogonal");
	this.leftVectors[0].splice(this.index,0, leftVector);
	this.rightVectors[0].splice(this.index,0, rightVector);
	// define vectors for shadowed shape
	distance = 2;
	leftVector = new TEKnotVector(distance, "orthogonal");
	rightVector = new TEKnotVector(distance, "orthogonal");
	this.leftVectors[1].splice(this.index,0, leftVector);
	this.rightVectors[1].splice(this.index,0, rightVector);
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
	// define vectors for normal shape
	var distance = 1;
	var leftVector = new TEKnotVector(distance, "orthogonal");
	var rightVector = new TEKnotVector(distance, "orthogonal");
	this.leftVectors[0].splice(this.index,0, leftVector);
	this.rightVectors[0].splice(this.index,0, rightVector);
	// define vectors for shadowed shape
	distance = 2;
	leftVector = new TEKnotVector(distance, "orthogonal");
	rightVector = new TEKnotVector(distance, "orthogonal");
	this.leftVectors[1].splice(this.index,0, leftVector);
	this.rightVectors[1].splice(this.index,0, rightVector);
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
TEEditableToken.prototype.deleteAllKnotData = function() {
	// delete all data of editableToken (i.e. dependent objects) as clean as possible (... and here we are again with the JS-problem: 
	// no explicit method for deleting data ... no "destructor" ... since we're talking about destructors: ceterum censeo ...)
	// and no: don't (even) try to explain me that JS is so cool that you actually don't have to destroy your objects (and that
	// the fabulous garbage collector of JS is intelligent enough to do that for you ... This is definitely not true: at this point
	// there are paper.js objects (lines, circles, polygons ...) on the canvas that need to be deleted before a new token can be loaded 
	// in ... at least they won't go away like "magic" ... Just in case I didn't mention it before: I hate JS!
	
	//console.log("Data to delete: before:", this);
	// parent
	this.parent = drawingArea;		// delete parent right away and use mainCanvas later (see below for fhToken)
	this.parent = null;
	//console.log("Data to delete: 1:", this);
	
	// token data
	//this.header.length = 0; // setting header.length = 0 is fatal ... data referenced by another variable (e.g. TokenDefinition.header will also get deleted
	this.header = null;		  // => setting only this.header to null leaves that data intact and "frees" variable this.header so that it can point to a new header
	//console.log("Data to delete: 2:", this);	// again: JS is just an ugly and unprecise language!
	
	for (var i=0; i<this.knotsList.length; i++) {
		this.knotsList[i].circle.remove();
	}
	this.knotsList.length = 0;
	this.knotsList = null; 	// type: TEVisuallyModifiableKnot
	//console.log("Data to delete: 3:", this);
	
	for (var shape=0; shape<2; shape++) {
		for (var i=0; i<this.leftVectors[0].length; i++) {
			//console.log("shape:",shape,"i:",i);
			this.leftVectors[shape][i].line.remove();
			this.rightVectors[shape][i].line.remove();
		}
	}
	
	for (var i=0; i<2; i++) {			// make 2-dimensional array for vectors (TEKnotVector)
		this.leftVectors[i].length = 0;
		this.leftVectors[i] = null;
		this.rightVectors[i].length = 0;
		this.rightVectors[i] = null;
	}
	this.leftVectors.length = 0;
	this.leftVectors = null;
	this.rightVectors.length = 0;
	this.rightVectors = null;
	
	//console.log("Data to delete: 4:", this);
	
	
	// paths
	//this.middlePath.remove();	// don't know if this variable is used?! seems to be null?! => seems to be unused duplicate of fhToken in TEDrawingArea
	//this.middlePath = null; 
	//console.log("outerShape: ", this.outerShape);
				
	this.outerShape[0].remove();
	//this.outerShape = null;		
	this.outerShape[1].remove();
	//this.outerShape = null;
	//console.log("Data to delete: 6:", this);
	
	this.outerShape.length = 0;
	this.outerShape = null;		
	//console.log("Data to delete: 7:", this);
	
	// delete middlepath
	//for (var i=0; i<mainCanvas.editor.fhToken.segments.length; i++);
	mainCanvas.editor.fhToken.remove();
	mainCanvas.editor.fhToken = new Path();
	mainCanvas.editor.fhToken.strokeColor = '#000';
	
	// delete label
	mainCanvas.editor.knotLabel.coordinates.remove();
	//mainCanvas.editor.knotLabel = null;
	
	// delete rotating axis
	//mainCanvas.editor.rotatingAxis.parallelRotatingAxis.newAxisList = 0;
	//mainCanvas.editor.rotatingAxis.parallelRotatingAxis.emptyArray();
	//mainCanvas.editor.rotatingAxis.parallelRotatingAxis = null;
	mainCanvas.editor.rotatingAxis.parallelRotatingAxis.deleteAllParallelAxis();
	
	this.selectedKnot = null;
	this.markedKnot = null;

	// index (is updated whenever identify-method is called)
	this.index = 0;

	//console.log("Data to delete: after:", this);
}
TEEditableToken.prototype.copyTextFieldsToHeaderArrayStandard = function() {
	//console.log("copy header: ");
	var output = "";
	for (var i=0; i<24; i++) {
			var id = "h" + Math.floor(i+1);
			var tmp = document.getElementById(id);
			if (tmp != null) this.header[i] = tmp.value;
			var string = (this.header[i] == null) ? "null" : this.header[i];
			output += "[" + string + "]";
	}
	//console.log("values: ", output);
}

TEEditableToken.prototype.copyTextFieldsToHeaderArraySE1 = function() {
	// copies the human readable user inputs to editableToken.header
	console.log("copyTextFieldsToHeaderArraySE1");
	
	//console.log("generate new header for SE1 from human readable form");
	// special variables
	
	var firstTension = (this.knotsList[0] != undefined) ? this.knotsList[0].tensions[3] : 0;
	var HTMLValue = document.getElementById('tokentypepulldown').value, 
		tokenType = 0;
	// console.log("tokenTypeHTML: ", HTMLValue);
	switch (HTMLValue) {
		case "normal" : tokenType = 0; break;
		case "shadowed" : tokenType = 1; break;
		case "virtual" : tokenType = 2; break;
		// 0 normal, 1 shadowed, 2 virtual
	}
	HTLMValue = document.getElementById('whichexit').value;
	console.log("whichexit HTMLValue: ", HTMLValue);
	var exitToUse = (HTMLValue == "normal") ? 0 : 1;
		HTMLValue = document.getElementById ('relative_or_absolute').value;
	console.log("relative_or_absolute: ", HTMLValue);
	var coordType = (HTMLValue == "relative") ? 0 : 1;
	// offsets 19-21
	var higherPosition = "", shadowed = "", distance = "";
	switch (document.getElementById('higherpositionpulldown').value) {
		case "higher" : higherPosition = "up"; break;
		case "same_line" : higherPosition = "no"; break;
		case "down" : higherPosition = "down"; break;
	}
	switch (document.getElementById('shadowingpulldown').value) {
		case "shadowed" : shadowed = "yes"; break;
		case "not_shadowed" : shadowed = "no"; break;
	}
	switch (document.getElementById('distancepulldown').value) {
		case "narrow" : distance = "narrow"; break;
		case "wide" : distance = "wide"; break;
	}	
	// connect
	var connectToPreceeding = 0;
	switch (document.getElementById('connect').value) {
		case "yes" : connectToPreceeding = 0; console.log("copyTextFieldsToHeaderArraySE1: connectToPreceeding = " + connectToPreceeding + " HTML-element: value(connect): " + document.getElementById('whichExit').value);  break;
		case "no" : connectToPreceeding = 1; console.log("copyTextFieldsToHeaderArraySE1: connectToPreceeding = " + connectToPreceeding + " HTML-element: value(connect): " + document.getElementById('whichExit').value); break;
	}
	var tokenGroup = document.getElementById('group').value;
		
	
	// write values to header array
	this.header[0] = Number(document.getElementById('width_middle').value);
	this.header[1] = Number(document.getElementById('conddeltaybefore').value);
	this.header[2] = Number(document.getElementById('conddeltayafter').value);
	
	
	this.header[3] = firstTension; // comes directly from editor (knot) in SE2
	this.header[4] = Number(document.getElementById('width_before').value);
	this.header[5] = Number(document.getElementById('width_after').value);
	this.header[6] = Number(document.getElementById('offset6').value);
	
	this.header[7] = "";	// 7-11: unused
	this.header[8] = "";
	this.header[9] = "";
	this.header[10] = "";
	this.header[11] = "";
	this.header[12] = tokenType;
	this.header[13] = Number(document.getElementById ('inconddeltaybefore').value);
	this.header[14] = Number(document.getElementById ('inconddeltayafter').value);
	this.header[15] = Number(document.getElementById ('altx').value);;
	this.header[16] = Number(document.getElementById ('alty').value);;
	
	
	this.header[17] = exitToUse;
	this.header[18] = coordType;
	this.header[19] = higherPosition;
	this.header[20] = distance;
	this.header[21] = shadowed;
	this.header[22] = connectToPreceeding;
	this.header[23] = tokenGroup;
	//console.log("editableToken: ", this);
	//console.log("new font: ", actualFont);

}
TEEditableToken.prototype.copyTextFieldsToHeaderArray = function() {
	//console.log("copy header: ");
	//this.copyTextFieldsToHeaderArrayStandard();
	
	if ((actualFont.version != undefined) && (actualFont.version == "SE1")) {
		this.copyTextFieldsToHeaderArraySE1();			
	} else {
		this.copyTextFieldsToHeaderArrayStandard();
	}
	
}
TEEditableToken.prototype.copyHeaderArrayToTextFieldsStandard = function() {
	//console.log("copy header array to text fields: header: ", this.header);
	var output = "<tr>\n"; // open first row
	for (var i=0; i<24; i++) {
			var id = "h" + Math.floor(i+1);
			var nr = (Math.floor(i)<9) ? "0"+Math.floor(i+1) : Math.floor(i+1); // ok, peace and love: this works now! :-)
			output += "<td>" + nr + "<input type=\"text\" id=\"" + id + "\" size=\"4\" value=\"" + this.header[i] + "\"></td>\n";
			if ((i+1)%8==0) output += "</tr><tr>"; // new row
	}
	output += "</tr>"; // close last row
	document.getElementById("headertable").innerHTML = output; 

}
TEEditableToken.prototype.copyHeaderArrayToTextFieldsSE1 = function() {
	//console.log("copy header array to text fields (SE1): header: ", this.header);
	var output = "<tr>\n"; // open first row
	
	/* standard header
	for (var i=0; i<24; i++) {
			var id = "h" + Math.floor(i+1);
			var nr = (Math.floor(i)<9) ? "0"+Math.floor(i+1) : Math.floor(i+1); // ok, peace and love: this works now! :-)
			output += "<td>" + nr + "<input type=\"text\" id=\"" + id + "\" size=\"4\" value=\"" + this.header[i] + "\"></td>\n";
			if ((i+1)%8==0) output += "</tr><tr>"; // new row
	}
	*/
	
	// special header for SE1
	// prepare data variables
	// token type (offset 12)
	var TTS = ["", "", ""];		// TTS = token type selection
	switch (this.header[12]) {
		case 0 : TTS[0] = " selected"; break;
		case 1 : TTS[1] = " selected"; break;
		case 2 : TTS[2] = " selected"; break; 
	}
	// width (offsets 4, 0, 5)
	var WB = this.header[4],		// width before
		WM = this.header[0],		// .. middle (called 'token' in the text)
		WA = this.header[5];		// .. after
	// following
	// vertical up (offset 19)
	var VS = ["", "", "", ""]; 		// VS = vertical selection
	switch (this.header[19]) {
		case "up" : VS[0] = " selected"; break;
		case "no" : VS[1] = " selected"; break;
		case "down" : VS[2] = " selected"; break;
		default : VS[3] = " selected"; break;
	}
	// shadow (offset 21)
	var SS = ["","",""]; 			// SS = shadow selection
	switch (this.header[21]) {
		case "yes" : SS[0] = " selected"; break;
		case "no" : SS[1] = " selected"; break;
		default : SS[2] = " selected"; break;
	}
	// distance (offset 20)
	var DS = ["", "", ""];			// DS = distance selection
	switch (this.header[20]) {
		case "narrow" : DS[0] = " selected"; break;	// treat "narrow" and "none" as "narrow"
		case "none" : DS[0] = " selected"; break;
		case "wide" : DS[1] = " selected"; break;
		default : DS[2] = " selected"; break;
	}
	// deltaY
	// conditional (offsets 1, 2)
	var CDYB = this.header[1], CDYA = this.header[2]; 	// Conditional Delta Y Before / After
	// inconditional (offsets 13, 14)
	var IDYB = this.header[13], IDYA = this.header[14]; 	// Inconditional Delta Y Before / After
	// alternative exit coordinates (offsets 15, 16)
	var AX = this.header[15], AY = this.header[16];		// AX = x86 register name haha ... :-) ... or alternative x, alternative y
	// which exit knot? (offset 17)
	var ES = ["", ""];				// ES = another x86 register ... ;-) ... or Exit Selection
	switch (this.header[17]) {
		case 0 : ES[0] = " checked"; break;
		case 1 : ES[1] = " checked"; break;
	}
	// y coordinates absolute or relative?
	var CS = ["", ""];				// CS = this definitely a reunion of all the old x86 cpu architecture ... ;-) ... Coordinates Selector
	switch (this.header[18]) {
		case 0 : CS[0] = " checked"; break;
		case 1 : CS[1] = " checked"; break;
	}
	// connected token? (offset 22)
	var CTS = ["", ""];				// Connected Token Selector
	switch (this.header[22]) {
		case 0 : CTS[0] = " checked"; break;
		case 1 : CTS[1] = " checked"; break;
	}
	// offset 6 (still don't know if this is obsolete, I think that 1 token still uses it ... so keep it for the sake of backwards compatibility)
	var O6 = this.header[6];
	
	// prepare HTML
	// don't know why this part is implemented dynamically here, but the it is included as raw-html format from editor_raw_html_code.html in export_se1data_to_editor.php ?!
	// so any changes here won't be visible in editor!!!
	// ok, got it ... : raw-html code is used to show an empty headertable when VPAINT loads, this section then dynamically updates the headertable when a token is loaded
	// would be better to create headertable dynamically from beginning (with empty values as long as no token is loaded), but leave it like that for the moment
	// (until it is changed, both files - js and html - must be modified manually!)
	output += "<td>\n"; 	// open first table cell
	output += "type: <select id='tokentypepulldown'><option value='normal'" + TTS[0] + ">normal</option><option value='shadowed'" + TTS[1] + ">shadowed</option><option value='virtual'" + TTS[2] + ">virtual</option></select><br>\n";
	output += "width: before <input id='width_before' type='text' size='4' value='" + WB + "'> token <input id='width_middle' type='text' size='4' value='" + WM + "'> after <input id='width_after' type='text' size='4' value='" + WA + "'><br>\n";
	output += "following: <select id='higherpositionpulldown'><option value='higher'" + VS[0] + ">higher</option><option value='same_line'" + VS[1] + ">same line</option><option value='lower'" + VS[2] + ">lower</option><option value='none'" + VS[3] + ">---</option></select>";
	output += "<select id='shadowingpulldown'><option value='shadowed'" + SS[0] + ">shadowed</option><option value='not_shadowed'" + SS[1] + ">normal</option><option value='shadow_none'" + SS[2] + ">---</option></select>";
	output += "<select id='distancepulldown'><option value='narrow'" + DS[0] + ">narrow</option><option value='wide'" + DS[1] + ">wide</option><option value='none'" + DS[2] + ">---</option></select><br>\n";
	output += "delta-Y: if higher: before <input id='conddeltaybefore' type='text' size='4' value='" + CDYB + "'> after <input id='conddeltayafter' type='text' size='4' value='" + CDYA + "'><br>\n";
	output += "         inconditional: before <input id='inconddeltaybefore' type='text' size='4' value='" + IDYB + "'> after <input id='inconddeltayafter' type='text' size='4' value='" + IDYB + "'><br>\n";
	output += "2nd: x <input id='altx' type='text' size='4' value='" + AX + "'> y <input id='alty' type='text' size='4' value='" + AY + "'> <input type='radio' name='relative_or_absolute' id='relative_or_absolute' value='relative'" + CS[0] + "> relative <input type='radio' name='relative_or_absolute' id='relative_or_absolute' value='absolute'" + CS[1] + "> absolute<br>\n";
	output += "use: <input type='radio' name='whichexit' id='whichexit' value='normal'" + ES[0] + "> normal <input type='radio' name='whichexit' id='whichexit' value='alternative'" + ES[1] + "> alternative <br>\n";
	output += "connect: <input type='radio' name='connect' id='connect' value='yes'" + CTS[0] + "> yes <input type='radio' name='connect' id='connect' value='no'" + CTS[1] + "> no <br>\n";
	output += "group: <input type='text' id='group' size='4' value='" + this.header[23] + "'>\n";
	output += "offset 6: <input type='text' id='offset6' size='4' value='" + O6 + "'><br>\n";
	output += "</td>\n</tr>\n"; // close table cell and last row
	
	//console.log(output);
	document.getElementById("headertable").innerHTML = output; 
}
TEEditableToken.prototype.copyHeaderArrayToTextFields = function() {
	//console.log("copy header array to text fields (main method)", actualFont);
	if ((actualFont.version != undefined) && (actualFont.version == "SE1")) {
		this.copyHeaderArrayToTextFieldsSE1();
	} else {
		this.copyHeaderArrayToTextFieldsStandard();
	}
}
TEEditableToken.prototype.deleteMarkedKnotFromArray = function() {
	// marked knot can be identified by index in editable token
	// set new selected / marked knot before deleting the actual knot
	// this function is still buggy ... e.g. rests of vectors remain on canvas and thickness sliders aren't unlinked => FIX IT LATER
	var end = this.knotsList.length;
	switch (this.index) {
		case 1 : this.selectedKnot = this.knotsList[0]; this.markedKnot = this.selectedKnot; break;
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
	this.knotsList[this.index-1].circle.remove(); // remove control circle
	this.knotsList.splice(this.index-1, 1); // deletes 1 element at index and reindexes array
	for (var i=0; i<2; i++) {
		// delete both vectors (for normal and shadowed shape)
		this.leftVectors[i][this.index-1].line.removeSegments();
		this.leftVectors[i].splice(this.index-1, 1);
		this.rightVectors[i][this.index-1].line.removeSegments();
		this.rightVectors[i].splice(this.index-1, 1);
	}
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
