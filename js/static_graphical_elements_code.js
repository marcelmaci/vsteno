
// classes 
// class TEBorders (TokenEditBorders)
function TEBorders(a, color) { // a = TEDrawingArea
	this.borders = new Path.Rectangle(a.upperLeft, a.lowerRight);
	this.borders.strokeColor = color;
	this.borders.strokeWidth = 0.5;
	return this.borders;
}
TEBorders.prototype.isStatic = function() {
	return true;
}
TEBorders.prototype.isDynamic = function() {
	return false;
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
TEDottedGrid.prototype.isStatic = function() {
	return true;
}
TEDottedGrid.prototype.isDynamic = function() {
	return false;
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
TEAuxiliarySystemLines.prototype.isStatic = function() {
	return true;
}
TEAuxiliarySystemLines.prototype.isDynamic = function() {
	return false;
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
TEAuxiliaryVerticalLines.prototype.isStatic = function() {
	return true;
}
TEAuxiliaryVerticalLines.prototype.isDynamic = function() {
	return false;
}

// class TECoordinatesLabels
function TECoordinatesLabels(parent) {
	this.parent = parent;
	this.allLabels = [];
	//console.log(this.parent);
	var posX = this.parent.rotatingAxis.centerRotatingAxis.x - ((this.parent.totalLines / 2) * this.parent.lineHeight * this.parent.scaleFactor),
		labelX = - (this.parent.totalLines / 2) * this.parent.lineHeight;
		
	for (var i = 0; i <= this.parent.totalLines; i++) {
		var text = new PointText(new Point(posX, this.parent.lowerY + 20));
		//console.log("posxy: ", posX, this.parent.lowerY+20);
		text.justification = 'center';
		text.fillColor = '#000';
		text.content = labelX;
		this.allLabels.push(text);
		//console.log("PointText: ", text);
		
		labelX += this.parent.lineHeight;	
		//console.log(this.parent.lineHeight);
		posX += this.parent.lineHeight * this.parent.scaleFactor;
	}
	
	//console.log(text.style.fontSize);
	var posY = this.parent.upperY + text.style.fontSize / 2,
		labelY = (this.parent.totalLines - this.parent.basePosition) * this.parent.lineHeight;
		
	for (var i = 0; i <= this.parent.totalLines; i++) {
		var text = new PointText(new Point(this.parent.leftX-10, posY));
		//console.log("posxy: ", posX, this.parent.lowerY+20);
		text.justification = 'right';
		text.fillColor = '#000';
		text.content = labelY;
		this.allLabels.push(text);
		//console.log("PointText: ", text);
		
		labelY -= this.parent.lineHeight;	
		//console.log(this.parent.lineHeight);
		posY += this.parent.lineHeight * this.parent.scaleFactor;
	}
	
}
TECoordinatesLabels.prototype.isStatic = function() {
	return true;
}
TECoordinatesLabels.prototype.isDynamic = function() {
	return false;
}
