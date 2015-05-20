"format es6";
console.log("module drivedistance running");

var url = "/userdata/drivedistance";

console.log("attempting to use the gen module");

var generatedMod = drawGen.func({
    name: "drivedistance",
    domClass: "drivedistancePlot",
    dataUri: url,
    curveFitting: true,
    pointColor: "blue",
    curveColor: "blue",
    invertedY: true,
    yFormat: function (d) {
        return (d * 0.000621371).toFixed(1) + "mi";
    }
});
export default generatedMod;
