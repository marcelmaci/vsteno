/* VSTENO TOKEN EDITOR (working title)
 * (c) 2018 - Marcel Maci (m.maci@gmx.ch)
 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * ADDITIONAL DISCLAIMER
 * 
 * This Program uses the paper.js library which has been published under the MIT
 * license. The MIT-License is compatible with the GPL-License as long as the 
 * derived product (e.g. this program) and programs derived from it are published
 * under the GPL-license.
 * 
 * THE ORIGINAL LICENSE FOR PAPER.JS
 * 
 * Copyright (c) 2011 - 2016, Juerg Lehni & Jonathan Puckey
 * http://scratchdisk.com/ & http://jonathanpuckey.com/
 * All rights reserved.
 * 
 * The MIT License (MIT)
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE. 
 */
 
 
// bezier functions
function getControlPoints( p0, p1, p2, t1, t2) {
    var d01=Math.sqrt(Math.pow(p1.x-p0.x,2)+Math.pow(p1.y-p0.y,2));
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
	var dx1 = c1.x - p1.x,
		dy1 = c1.y - p1.y,
		dx2 = c2.x - c1.x,
		dy2 = c2.y - c1.y,
		dx3 = p2.x - c2.x,
		dy3 = p2.y - c2.y;
		//m1 = dx1 / dy1,
		//m2 = dx2 / dy2,
		//m3 = dx3 / dy3;
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
	// deltas
	var dtx = tx2 - tx1,
		dty = ty2 - ty1; // avoid division by 0 for bm!?
	// calculate bezier point (coordinates and m)
	var bx = tx1 + dtx * factor,
		by = ty1 + dty * factor,
		bm = dtx / Math.avoidDivisionBy0(dty);
	return [bx, by, bm];
}

function findTangentPointRelativeToFixPointBrute(fixPoint,p1,c1,p2,c2,epsilon) {
	// bruteforce method ...
	var iterations = 200;
	var deltaPercentage = 100 / iterations;
	var bestPercentage = undefined;
	var bestM = undefined;
	var angleTangent, angleFixPoint;
	var minEpsilon = 200;
	
	var bestBet = undefined;
	
	for (p = 0; p <= 100; p += deltaPercentage) {
		var actPoint = calculateBezierPoint(p1,c1,p2,c2,p);
	
		angleTangent = Math.atan(actPoint[2]);
		var tempM = (actPoint[0] - fixPoint.x) / (actPoint[1] - fixPoint.y);
		angleFixPoint = Math.atan(tempM);
		var deltaEpsilon = Math.abs(angleTangent - angleFixPoint);
		if (deltaEpsilon < minEpsilon) {
			
			minEpsilon = deltaEpsilon;
			bestBet = actPoint;
			bestM = tempM;
			bestPercentage = p;
			//console.log(p, ": angle tangent vs fixpoint / delta: ", angleTangent, angleFixPoint, deltaEpsilon);
			//console.log("set new minEpsilon = ", minEpsilon, " / actPoint = ", actPoint);
			
		}
	}
	if (minEpsilon < 0.9) {
		if (minEpsilon < 0.009) return bestBet;
		else if (bestPercentage <= 50) return [ p1.x, p1.y, bestM];
		else if (bestPercentage > 50) return [ p2.x, p2.y, bestM];
	} else return false;
}
function calculateEpsilonDeltaFixPointAndTangent(fixPoint,p1,c1,p2,c2,percentage) {
	var actPoint = calculateBezierPoint(p1,c1,p2,c2,percentage);
	var angleTangent = Math.atan(actPoint[2]);
	var actM = (actPoint[0] - fixPoint.x) / (actPoint[1] - fixPoint.y);
	var angleFixPoint = Math.atan(actM);
	var deltaEpsilon = Math.abs(angleTangent - angleFixPoint);
	return deltaEpsilon; 
}
function findTangentPointRelativeToFixPointIntervals(fixPoint,p1,c1,p2,c2,epsilon) {
	// interval method 
	var actLeftP = 0, testLeftP = 25, actMiddleP = 50, testRightP = 75, actRightP = 100;
	var i = 0, maxIterations = 8; // precision = 2 ^ maxIterations
	var minEpsilon = 200, bestBet = undefined, bestM = undefined, bestP = undefined;
	
	do {
		
		var middleEpsilon = calculateEpsilonDeltaFixPointAndTangent(fixPoint, p1,c1,p2,c2, actMiddleP); 
		var leftEpsilon = calculateEpsilonDeltaFixPointAndTangent(fixPoint, p1,c1,p2,c2, testLeftP);
		var rightEpsilon = calculateEpsilonDeltaFixPointAndTangent(fixPoint, p1,c1,p2,c2, testRightP);
		
		//console.log(i, ": percentages/epsilons left/middle/right: ", testLeftP, leftEpsilon, actMiddleP, middleEpsilon, testRightP, rightEpsilon);
		
		if ((leftEpsilon <= middleEpsilon) || (leftEpsilon < rightEpsilon)) {
				// chose left interval
				minEpsilon = leftEpsilon;
				bestBet = calculateBezierPoint(p1,c1,p2,c2,testLeftP);
				bestP = testLeftP;
				actRightP = actMiddleP;
				actMiddleP = testLeftP;
				testLeftP = (actMiddleP + actLeftP) / 2;
				testRightP = (actRightP + actMiddleP) / 2;
		} else if ((rightEpsilon <= middleEpsilon) || (rightEpsilon < leftEpsilon)) {
				// chose right interval
				minEpsilon = rightEpsilon;
				bestBet = calculateBezierPoint(p1,c1,p2,c2,testRightP);
				bestP = testRightP;
				actLeftP = actMiddleP;
				actMiddleP = testRightP;
				testLeftP = (actMiddleP + actLeftP) / 2;
				testRightP = (actRightP + actMiddleP) / 2;
		} else {
			
			console.log("this case actually shouldn't happen ... ;/)");
		
		}
		
		// console.log(i, ": angle tangent vs fixPoint: ", angleTangent, angleFixPoint);
		i++;
		
	} while (i < maxIterations);
	
	if (minEpsilon < 0.9) {
		if (minEpsilon < 0.009) return bestBet;
		else if (bestP<= 50) return [ p1.x, p1.y, 0];	// m = 0 is not the real inclination (but the value is not important)
		else if (bestP > 50) return [ p2.x, p2.y, 0];
	} else return false;

}

function findTangentPointRelativeToFixPoint(fixPoint,p1,c1,p2,c2,epsilon) {
	//return findTangentPointRelativeToFixPointOld(fixPoint,p1,c1,p2,c2,epsilon);
	//return findTangentPointRelativeToFixPointBrute(fixPoint,p1,c1,p2,c2,epsilon);
	return findTangentPointRelativeToFixPointIntervals(fixPoint,p1,c1,p2,c2,epsilon);

}

function findTangentPointRelativeToFixPointOld(fixPoint, p1, c1, p2, c2, epsilon) {
	// DESCRIPTION OF ALGORITHM
	// define the 3 points:
	// - the middle one separates the bezier curve (or the actual segment of it) into two halves
	// - left and right points define start and end of the two segments (halves)
	// the points are defined as percentages (= relative location) on the bezier curve
	// epsilon stands for the precision: delta of straight lines going from connection point
	// to calculated tangent point should be < epsilon (numerical aproximation) 
	
	var leftPercentage = 0.001;			// 0% <=> leftPoint
	var rightPercentage = 99.999;		// 100% <=> rightPoint
	var middlePercentage = 50;		// 50% <=> middlePoint
	var leftPoint = undefined,		// declare point variables
		rightPoint = undefined,
		middlePoint = undefined; 
	
	var avoidInfinityLoop = 0;
	var whichInterval = "start";
	var actualEpsilon = 100;
	leftPoint = calculateBezierPoint(p1, c1, p2, c2, leftPercentage);
	rightPoint = calculateBezierPoint(p1, c1, p2, c2, rightPercentage);
	var cx = fixPoint.x,
		cy = fixPoint.y;
	var cm = undefined,			// declare all variales outside loop = speed optimization?!
		angleLeft = undefined,
		angleRight = undefined,
		angleMiddle = undefined,
		angleConnectionPoint = undefined,
		deltaAngleLeft = undefined,
		deltaAngleMiddle = undefined,
		deltaAngleRight = undefined; 
	var actualPercentageEpsilon = undefined,
	    lastPercentageEpsilon = 100;
		
	do {
		middlePoint = calculateBezierPoint(p1, c1, p2, c2, middlePercentage);
	
		// calculate m for straight line from connecting point to tangent point
		cm = (middlePoint[0]-cx) / Math.avoidDivisionBy0(middlePoint[1]-cy);
		
		// work with rad angles (easier, but slower)	
		angleLeft = Math.atan(leftPoint[2]); 
		angleMiddle = Math.atan(middlePoint[2]); 
		angleRight = Math.atan(rightPoint[2]); 
		angleConnectionPoint = Math.atan(cm); 
					
		// find out in which interval (left or right) the tangent point is
		deltaAngleLeft = Math.abs(angleConnectionPoint - angleLeft);
		deltaAngleRight = /*Math.abs(angleRight - angleConnectionPoint); */ Math.abs(angleConnectionPoint - angleRight);
//		console.log("delta: left: ", deltaAngleLeft, "right: ", deltaAngleRight);
		//console.log("calculateTangent: leftPoint: ", leftPoint, " middlePoint: ", middlePoint, " rightPoint: ", rightPoint);
		//console.log("leftAngle: ", angleLeft, "middleAngle: ", angleMiddle, " rightAngle: ", angleRight);
		if (deltaAngleLeft <= deltaAngleRight) { 
			whichInterval = "left"; 
			//actualEpsilon =  middlePercentage - leftPercentage; // problem: a point is always found! (angle is not taken into consideration)
			deltaAngleMiddle = Math.abs(angleMiddle - angleConnectionPoint);
			
			//actualEpsilon = Math.abs(angleMiddle - angleLeft);
			actualEpsilon = Math.min(deltaAngleLeft, deltaAngleMiddle); //(deltaAngleLeft > deltaAngleMiddle) ? deltaAngleLeft : deltaAngleMiddle;
		} else if (deltaAngleRight < deltaAngleLeft) { 
			whichInterval = "right"; 
			deltaAngleMiddle = Math.abs(angleConnectionPoint - angleMiddle);
			//actualEpsilon = rightPercentage - middlePercentage; //deltaAngleRight; 
			//actualEpsilon = Math.abs(angleRight - angleMiddle);
			actualEpsilon = Math.min(deltaAngleRight, deltaAngleMiddle); // (deltaAngleRight > deltaAngleMiddle) ? deltaAngleRight : deltaAngleMiddle;
		} else { 
			console.log("noidea"); 
			whichInterval = "noidea";
		}
		console.log("left/middle/rightPercentage: ", leftPercentage, middlePercentage, rightPercentage);
		console.log("deltaAngleLeft: ", deltaAngleLeft, " deltaAngleRight: ", deltaAngleRight, " interval decision: ", whichInterval);
		
		//console.log("Deltas: left: ", deltaAngleLeft, "middle: ", deltaAngleMiddle, "right: ", deltaAngleRight);

		switch (whichInterval) {
			case "left" : rightPercentage = middlePercentage; middlePercentage = (leftPercentage + rightPercentage) / 2;
						  rightPoint = middlePoint;
						  break;
			case "right": leftPercentage = middlePercentage; middlePercentage = (leftPercentage + rightPercentage) / 2; 
					      leftPoint = middlePoint;
					      break;
			case "noidea" : //console.log("noidea");
							//console.log("compare left: "+leftPoint[2].toFixed(2)+" <?> "+cm.toFixed(2)+" <?> "+middlePoint[2].toFixed(2));
							//console.log("compare right: "+middlePoint[2].toFixed(2)+" <?> "+cm.toFixed(2)+" <?> "+rightPoint[2].toFixed(2));
							middlePercentage = (middlePercentage + rightPercentage) / 2; // shift middle point instead	
							break;
			default : avoidInfinity = 1000000000; break;
		}
		avoidInfinityLoop++;
		actualPercentageEpsilon = rightPercentage - leftPercentage;
		
	} while (/*(actualEpsilon > epsilon)*/ /*(actualPercentageEpsilon > 0.1) &&*/ (avoidInfinityLoop < tangentFixPointMaxIterations)); 
	if ((/*actualEpsilon <= epsilon*/actualPercentageEpsilon <=0.1) && (actualEpsilon < 0.1)) {
		//console.log("Point found: ", middlePoint, "Epsilon: ", actualEpsilon);
		return middlePoint;
	} else { 
		
		console.log("No point found: Epsilon:", actualEpsilon, avoidInfinityLoop);
		//return false;
		return middlePoint;
		//if (actualEpsilon < 0.5) return middlePoint;
		//else return false;
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

Math.avoidDivisionBy0 = function(value) {
	// mathematically horrible ..., but it does the trick ...
	if (value == 0) return 0.000000000000000001;
	else return value;
}

function fract(n) { 
	return Number(String(n).split('.')[1] || 0); 
	// I hate JS ... (ceterum censeo:)
	// BTW: I was recommended not to add personal comments in the source code, so I decided to add some more and will do so in 
	// the future ... ;-)
	// And no, I won't change my mind: JS is a miserable programming language ... Not only is it poorly designed and full
	// of absolutely strange things (type coercion as one of the worst) that you don't find in any other language and that 
	// can cost you hours of debugging, but in addition it is the ONLY language that you can use to run code in a webbrowser 
	// (which means that you have absolutely no choice).
	// I also write these lines in defense for PHP (which is the other language VSTENO is written in): Many people say PHP 
	// is horrible ... Well, that might be, but at least if you don't like it, you can choose from a whole bunch of other
	// server-side languages.
	// That's why JS in my opinion fully and entirely deserves the verdict of Cato the Elder ... ;-)
}

function humanReadableEditor(data) {
	switch (typeof data) {
		case "number" : return Math.floor(data * 100) / 100; break;
		case "string" : return "\"" + data + "\""; break;
		case "undefined" : return ""; break;
		case "null" : return 0; break;
	}
}
