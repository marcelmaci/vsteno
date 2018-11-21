 
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
	outerLines.removeSegments();
	outerLines.add(p1, c1, c2, p2);
	outerLines.strokeColor = '#f00';
	
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
	innerLines.removeSegments(); 
	innerLines.add(new Point(ix1, iy1), new Point(ix2, iy2), new Point(ix3, iy3));
	innerLines.strokeColor = '#00f';
	
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
	tangent.removeSegments();
	tangent.add(new Point(tx1, ty1), new Point(tx2, ty2));
	tangent.strokeColor = '#000';
	
	// deltas
	var dtx = tx2 - tx1,
		dty = ty2 - ty1;
	// calculate bezier point (coordinates and m)
	//console.log("Tangent data: tx1, ty1, tx2, ty2: ", tx1, ty1, tx2, ty2);
	var bx = tx1 + dtx * factor,
		by = ty1 + dty * factor,
		bm = dtx / dty;
	//console.log("bezierPoint (bx, by, m): (("+bx+","+by+","+bm+")");
	// return values as array
	return [bx, by, bm];
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
