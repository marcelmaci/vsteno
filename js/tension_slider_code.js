// class TETensionSlider
function TETensionSlider(x, y, width, height, label) {
	// properties
	this.labelHeight = 20;
	this.sliderThickness = 6;
	this.sliderThicknessHalf = this.sliderThickness / 2;
	this.slidingHeight = height-this.labelHeight;
	this.slidingStartY = y+this.labelHeight;
	this.slidingEndY = y+height;
	// borders
	this.border = new Path.Rectangle(new Point(x, y+this.labelHeight), new Point(x+width, y+height));
	this.border.strokeColor = '#000';
	// label
	this.label = new PointText(new Point(x+width/2, y+12));
	this.label.style.fontSize = 12;
	this.label.justification = 'center';
	this.label.strokeColor = '#000';
	this.label.content = label;
	// slider
	this.sliderValue = 0.5;
	this.actualSliderPosition = this.slidingStartY+(this.slidingHeight*this.sliderValue);
	this.sliderRectangle = new Path.Rectangle(new Point(x+1, this.actualSliderPosition-this.sliderThicknessHalf), new Point(x+width-1, this.actualSliderPosition+this.sliderThicknessHalf));
	this.sliderRectangle.fillColor = '#000';
	
	this.sliderRectangle.strokeColor = '#000';
	
}
