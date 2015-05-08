"format es6";
console.log("module drivetime running");

var url = "/userdata/drivetime";

console.log("attempting to use the gen module");

var generatedMod = drawGen.func({
    name: "drivetime",
    domClass: "drivetimePlot",
    dataUri: url,
    curveFitting: true,
    pointColor: "red",
    curveColor: "red"
});
export default generatedMod;
