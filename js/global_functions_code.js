 
// bezier functions
function getControlPoints( p0, p1, p2, t1, t2) {
    var d01=Math.sqrt(Math.pow(p0.x-p1.x,2)+Math.pow(p1.y-p0.y,2));
    var d12=Math.sqrt(Math.pow(p2.x-p1.x,2)+Math.pow(p2.y-p1.y,2));
    var fa=t1*d01/(d01+d12);   // scaling factor for triangle Ta
    var fb=t2*d12/(d01+d12);   // ditto for Tb, simplifies to fb=t-fa
    var p1x=p1.x-fa*(p2.x-p0.x);    // x2-x0 is the width of triangle T
    var p1y=p1.y-fa*(p2.y-p0.y);    // y2-y0 is the height of T
    var p2x=p1.x+fb*(p2.x-p0.x);
    var p2y=p1.y+fb*(p2.y-p0.y);  
    return [ new Point( p1x, p1y ), new Point( p2x, p2y ) ];
}

function calculateBezierPoint(p1, c1, p2, c2, percent) {
	// considers length of complete bezier curve as 100%
	// calculates point situated at percent percent of the curve
	// returns coordinates of point and m of tangent
	// calculate 3 outer lines 
	//console.log("-------------------------------------------------------------------------");
	//console.log("calculateBezierPoint(("+ p1.x +","+p1.y+"), ("+c1.x+","+c1.y+"), ("+p2.x+","+p2.y+"), ("+c2.x+","+c2.y+"), "+percent+"%)");
	var dx1 = c1.x - p1.x,
		dy1 = c1.y - p1.y,
		dx2 = c2.x - c1.x,
		dy2 = c2.y - c1.y,
		dx3 = p2.x - c2.x,
		dy3 = p2.y - c2.y;
		//m1 = dx1 / dy1,
		//m2 = dx2 / dy2,
		//m3 = dx3 / dy3;
	/*outerLines.removeSegments();
	outerLines.add(p1, c1, c2, p2);
	outerLines.strokeColor = '#f00';
	*/
	// calculate 2 inner lines
	// coordinates
	var factor = 1 / 100 * percent;
	//console.log("factor: ", factor);
	var ix1 = p1.x + dx1 * factor,
		iy1 = p1.y + dy1 * factor,
		ix2 = c1.x + dx2 * factor,
		iy2 = c1.y + dy2 * factor,
		ix3 = c2.x + dx3 * factor,
		iy3 = c2.y + dy3 * factor;
	//console.log("inner lines (ix123, iy123): (("+ix1+","+iy1+"), ("+ix2+","+iy2+"), ("+ix3+","+iy3+")");
	/*innerLines.removeSegments(); 
	innerLines.add(new Point(ix1, iy1), new Point(ix2, iy2), new Point(ix3, iy3));
	innerLines.strokeColor = '#00f';
	*/
	// deltas
	var dix1 = ix2 - ix1,
		diy1 = iy2 - iy1,
		dix2 = ix3 - ix2,
		diy2 = iy3 - iy2;
	// calculate last inner line that touches bezier curve
	// coordinates
	var tx1 = ix1 + dix1 * factor,
		ty1 = iy1 + diy1 * factor,
		tx2 = ix2 + dix2 * factor,
		ty2 = iy2 + diy2 * factor;
	//console.log("tangent line (tx12, ty12): (("+tx1+","+ty1+"), ("+tx2+","+ty2+")");
	/*tangent.removeSegments();
	tangent.add(new Point(tx1, ty1), new Point(tx2, ty2));
	tangent.strokeColor = '#000';
	*/
	// deltas
	var dtx = tx2 - tx1,
		dty = ty2 - ty1; // avoid division by 0 for bm!?
	// calculate bezier point (coordinates and m)
	//console.log("Tangent data: tx1, ty1, tx2, ty2: ", tx1, ty1, tx2, ty2);
	var bx = tx1 + dtx * factor,
		by = ty1 + dty * factor,
		bm = dtx / dty;
	//console.log("bezierPoint (bx, by, m): (("+bx+","+by+","+bm+")");
	// return values as array
	//bm = isNaN(bm) ? 9999999999999999 : bm;	// sanitize NaN resulting from division by zero above
	return [bx, by, bm];
}

function findTangentPointRelativeToFixPoint(fixPoint, p1, c1, p2, c2, epsilon) {
	// known issues and caveats with following algorithm:
	// 1) when connecting point is exactly orthogonal to bezier curve, the function will return orthogonal point 
	//    => screenshot_tangentpoint_bug1a.jpb / ... bug1b.jpg
	// 2) when connecting point is "inside rectangle" the function will return the opposite tangent point (left
	//    or right => screenshot.tangentpoint_bug2a.jpg / ... bug2b.jpg
	// 3) vertical tangents are possible (screenshot_tangentpoint_bug3a), but horizontal tangents are NOT
	//    => screenshot_tangenpoint_bug3b.jpg / .. bug3c.jpg
	// there might also be erroneous results if tensions are 0 (in general 0 is a difficult number ... ;-)
	//
	// implications for VSTENO:
	// 1) and 2) maybe not really a problem?!
	// 3) unfortunately, there are many shorthand tokens that connect horizontally ... Keep this bug in mind and see
	// what happens.
	// POSSIBLE SOLUTION: define m as dy / dx (instead of dx / dy) => this will probably allow horizontal connections
	// (and produce false results for vertical connections which would have less impact on VSTENO
	// => keep it like that for the moment, change it later if necessary!
	//
	// FURTHER INVESTIGATIONS
	// "bugs" 1-3 seem to be related to precision: increasing precision (to epsilon = 0.00000005 instead of 0.1) 
	// and iterations (500 instead of 10) improves results significantly ("bug" 1 almost impossible to reproduce,
	// "bug" 3: nearly horizontal connections possible
	// => increase precision to the max (will have an impact on speed, of course ...)
	// => define a tolerance for horizontal connections when connecting tokens (and "round" values in drawing routine
	// later)
	//
	// DESCRIPTION OF ALGORITHM
	// define the 3 points:
	// - the middle one separates the bezier curve (or the actual segment of it) into two halves
	// - left and right points define start and end of the two segments (halves)
	// the points are defined as percentages (= relative location) on the bezier curve
	// epsilon stands for the precision: delta of straight lines going from connection point
	// to calculated tangent point should be < epsilon (numerical aproximation) 
	this.leftPercentage = 0.001;			// 0% <=> leftPoint
	this.rightPercentage = 99.999;		// 100% <=> rightPoint
	this.middlePercentage = 50;		// 50% <=> middlePoint
	var leftPoint = undefined,		// declare point variables
		rightPoint = undefined,
		middlePoint = undefined; 
	// for the moment use fix segment (2nd segment <=> indexes 1 and 2)
	/*
	var p1 = this.parent.editableToken.knotsList[1].circle.position,
		c1 = p1 + this.parent.fhToken.segments[1].handleOut,     // control points are RELATIVE coordinates
		p2 = this.parent.editableToken.knotsList[2].circle.position,
		c2 = p2 + this.parent.fhToken.segments[2].handleIn;	
	*/
	var avoidInfinityLoop = 0;
	var whichInterval = "start";
	var actualEpsilon = 100;
	do {
		//console.log("Starting loop number "+avoidInfinityLoop+"........................................");
		leftPoint = calculateBezierPoint(p1, c1, p2, c2, this.leftPercentage);
		middlePoint = calculateBezierPoint(p1, c1, p2, c2, this.middlePercentage);
		rightPoint = calculateBezierPoint(p1, c1, p2, c2, this.rightPercentage);
		// the xPoint[] arrays now contain the point and m (= inclination) of the bezier tangent
		// calculate m for straight line from connecting point to tangent point
		var cx = fixPoint.x,
			cy = fixPoint.y,
			dx = middlePoint[0] - cx,
			dy = middlePoint[1] - cy,
			cm = dx / dy;
		// work with angles (easier)
		
		var angleLeft = Math.atan(leftPoint[2]); 
		//console.log(angleLeft.toFixed(2));
		var angleMiddle = Math.atan(middlePoint[2]); 
		var angleRight = Math.atan(rightPoint[2]); 
		var angleConnetionPoint = Math.atan(cm); 
		var degLeft = Math.degrees(angleLeft);
		var degMiddle = Math.degrees(angleMiddle);
		var degRight = Math.degrees(angleRight);
		var degCP = Math.degrees(angleConnetionPoint);
		
		//console.log(angleMiddle.toFixed(2));
		//console.log(angleRight.toFixed(2));
		//console.log(angleConnetionPoint.toFixed(2));
		

/*		console.log("---------------------- loop "+avoidInfinityLoop+" -------------------------------------------")
		console.log("Epsilon: ", actualEpsilon.toFixed(2),"Decision: ", whichInterval, "Percentages: ", this.leftPercentage, this.middlePercentage, this.rightPercentage);
/*		console.log("leftPoint = ("+leftPoint[0].toFixed(2)+","+leftPoint[1].toFixed(2)+") with m=", leftPoint[2].toFixed(2));
		console.log("connectionPoint = ("+cx.toFixed(2)+","+cy.toFixed(2)+") with m=", cm.toFixed(2));
*///		console.log("middlePoint = ("+middlePoint[0].toFixed(2)+","+middlePoint[1].toFixed(2)+") with m=", middlePoint[2].toFixed(2));
/*		console.log("-------------------------------------------------------------------------------------");
		console.log("middlePoint = ("+middlePoint[0].toFixed(2)+","+middlePoint[1].toFixed(2)+") with m=", middlePoint[2].toFixed(2));
		console.log("connectionPoint = ("+cx.toFixed(2)+","+cy.toFixed(2)+") with m=", cm.toFixed(2));
		console.log("rightPoint = ("+rightPoint[0].toFixed(2)+","+rightPoint[1].toFixed(2)+") with m=", rightPoint[2].toFixed(2));
*/
/*		console.log("Angles: left: ", angleLeft.toFixed(2), "middle: ", angleMiddle.toFixed(2), "right: ", angleRight.toFixed(2), " | connection point: ", angleConnetionPoint.toFixed(2));
		console.log("Degrees: left: ", degLeft.toFixed(2), "middle: ", degMiddle.toFixed(2), "right: ", degRight.toFixed(2), " | connection point: ", degCP.toFixed(2));
		
							
		// find out in which interval (left or right) the tangent point is
		// leftInterval <=> (leftM < connectionM < middleM) or (leftM > connectionM > middleM)
		// in other words: m must be BETWEEN the two other values
		// and same for right interval 
		
		/*
		if (((leftPoint[2] < cm) && (cm < middlePoint[2])) || ((leftPoint[2] > cm)  && (cm > middlePoint[2]))) whichInterval = "left";
		else if (((rightPoint[2] < cm) && (cm < middlePoint[2])) || ((rightPoint[2] > cm)  && (cm > middlePoint[2]))) whichInterval = "right";
		else whichInterval = "noidea"; // not sure about that one ... //whichInterval = "sorry, dude, something seems to be wrong ...";
		*/
		
		// base decision about interval on angles
	 /*
		if (((angleLeft < angleConnetionPoint) && (angleConnetionPoint < angleMiddle))
			|| ((angleLeft > angleConnetionPoint) && (angleConnetionPoint > angleMiddle))) whichInterval = "left";
		else if (((angleRight < angleConnetionPoint) && (angleConnetionPoint < angleMiddle))
			|| ((angleRight > angleConnetionPoint) && (angleConnetionPoint > angleMiddle))) whichInterval = "right";
		else {
				// this is the former "noidea" section
				// try one thing: invert connection point value and do the above comparison again!
/*			angleMiddle = -angleMiddle;
			if (((angleLeft < angleConnetionPoint) && (angleConnetionPoint < angleMiddle))
				|| ((angleLeft > angleConnetionPoint) && (angleConnetionPoint > angleMiddle))) whichInterval = "left";
			else if (((angleRight < angleConnetionPoint) && (angleConnetionPoint < angleMiddle))
				|| ((angleRight > angleConnetionPoint) && (angleConnetionPoint > angleMiddle))) whichInterval = "right";
			else whichInterval = "noidea";	
*/
/*			whichInterval = "noidea";
		}
*/
		var deltaAngleLeft = Math.abs(angleConnetionPoint - angleLeft);
		var deltaAngleRight = Math.abs(angleConnetionPoint - angleRight);
//		console.log("delta: left: ", deltaAngleLeft, "right: ", deltaAngleRight);
		if (deltaAngleLeft <= deltaAngleRight) { 
			whichInterval = "left"; 
			actualEpsilon =  middlePercentage - leftPercentage; 
		} else if (deltaAngleRight < deltaAngleLeft) { 
			whichInterval = "right"; 
			actualEpsilon = rightPercentage - middlePercentage; //deltaAngleRight; 
		} else { 
			console.log("noidea"); 
			whichInterval = "noidea";
		}
		
		
		
		// base decision about interval on angles
		//if ((angleLeft < angleConnetionPoint) && (angleConnetionPoint < angleMiddle)) whichInterval = "left";
		//else if ((angleMiddle < angleConnetionPoint) && (angleConnetionPoint < angleRight)) whichInterval = "right";
		//else whichInterval = "noidea";
		 
		//console.log("whichInterval: ", whichInterval);
		// calculate actual epsilon
		
		//actualEpsilon = Math.abs(Math.abs(cm) - Math.abs(middlePoint[2]));
		
		//console.log("actualEpsilon = ", actualEpsilon);
		// set new points to test
		
		switch (whichInterval) {
			case "left" : this.rightPercentage = this.middlePercentage; this.middlePercentage = (this.leftPercentage + this.rightPercentage) / 2;
						  break;
			case "right": this.leftPercentage = this.middlePercentage; this.middlePercentage = (this.leftPercentage + this.rightPercentage) / 2; 
					      break;
			case "noidea" : //console.log("noidea");
							//console.log("compare left: "+leftPoint[2].toFixed(2)+" <?> "+cm.toFixed(2)+" <?> "+middlePoint[2].toFixed(2));
							//console.log("compare right: "+middlePoint[2].toFixed(2)+" <?> "+cm.toFixed(2)+" <?> "+rightPoint[2].toFixed(2));
							this.middlePercentage = (this.middlePercentage + this.rightPercentage) / 2; // shift middle point instead
			
			
							break;
			default : avoidInfinity = 1000000000; break;
		}
		avoidInfinityLoop++;
	} while ((actualEpsilon > epsilon) && (avoidInfinityLoop < 1000)); // do max 10 loops
	if (actualEpsilon <= epsilon) {
		//console.log("Point found: ", middlePoint);
		return middlePoint;
	} else { 
		//console.log("No point found: ", actualEpsilon, avoidInfinityLoop);
		return false;
		//return middlePoint;
	}
}

// trigonometric functions
// degrees to radians
Math.radians = function(degrees) {
  return degrees * Math.PI / 180;
};
 
// radians to degrees
Math.degrees = function(radians) {
  return radians * 180 / Math.PI;
};

// fixing the JS typeof operator ... (again: very weak and neary useless concept in JS, in my opinion...)
function toType(obj) {
    if(obj && obj.constructor && obj.constructor.name) {
        return obj.constructor.name;
    }
    return Object.prototype.toString.call(obj).slice(8, -1).toLowerCase();
}
