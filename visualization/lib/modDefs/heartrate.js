"format es6";
console.log("module heartrate running");

var url = "/userdata/heartrate";

console.log("attempting to use the gen module");

var generatedMod = drawGen.func({
    name: "heartrate",
    domClass: "heartratePlot",
    dataUri: url,
    curveFitting: true,
    pointColor: "green",
});
export default generatedMod;
