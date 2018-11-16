 
// bezier functions
function getControlPoints( p0, p1, p2, t) {
    var d01=Math.sqrt(Math.pow(p0.x-p1.x,2)+Math.pow(p1.y-p0.y,2));
    var d12=Math.sqrt(Math.pow(p2.x-p1.x,2)+Math.pow(p2.y-p1.y,2));
    var fa=t*d01/(d01+d12);   // scaling factor for triangle Ta
    var fb=t*d12/(d01+d12);   // ditto for Tb, simplifies to fb=t-fa
    var p1x=p1.x-fa*(p2.x-p0.x);    // x2-x0 is the width of triangle T
    var p1y=p1.y-fa*(p2.y-p0.y);    // y2-y0 is the height of T
    var p2x=p1.x+fb*(p2.x-p0.x);
    var p2y=p1.y+fb*(p2.y-p0.y);  
    return [ new Point( p1x, p1y ), new Point( p2x, p2y ) ];
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
