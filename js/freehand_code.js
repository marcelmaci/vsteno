
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
	this.connectionPoint = false;
	this.connect2preceeding = true;
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
		this.knotsList[i].type.pivot1 = false;
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
TEEditableToken.prototype.copyTextFieldsToHeaderArray = function() {
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
TEEditableToken.prototype.copyHeaderArrayToTextFields = function() {
	//console.log("copy header array to text fields: header: ", this.header);
	var output = "<tr>\n"; // open first row
	for (var i=0; i<24; i++) {
			var id = "h" + Math.floor(i+1);
			var nr = (Math.floor(i)<10) ? "0"+Math.floor(i+1) : Math.floor(i+1);
			output += "<td>" + nr + "<input type=\"text\" id=\"" + id + "\" size=\"4\" value=\"" + this.header[i] + "\"></td>\n";
			if ((i+1)%8==0) output += "</tr><tr>"; // new row
	}
	output += "</tr>"; // close last row
	document.getElementById("headertable").innerHTML = output; 
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
