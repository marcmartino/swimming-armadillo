"format es6";
var url = location.origin.indexOf('localhost') >= 0 ? "dataCache/fatratio.json" : "/userdata/fatratio";

var generatedMod = drawGen.func({
    name: "fatratio",
    domClass: "weightPlot",
    dataUri: url,
    curveFitting: true,
    pointColor: "pink",
    invertedY: true
});
export default generatedMod;
