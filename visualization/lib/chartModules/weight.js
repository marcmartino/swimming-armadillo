"format es6";
console.log("module weight running");

var url = location.origin.indexOf('localhost') >= 0 ? "dataCache/weight.json" : "/userdata/weight";	   

console.log("attempting to use the gen module");

var generatedMod = drawGen.func({
    name: "weight",
    domClass: "weightPlot",
    dataUri: url,
    curveFitting: true,
    pointColor: "green",
    invertedY: true,
    yFormat: function (d) {
	console.log("y format running");
	console.log(d);
	return d/1000 + "kg";
    }
});
export default generatedMod;
