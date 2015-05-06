"format es6";
console.log("module fatmass running");

var generatedMod = drawGen.func({
    name: "withingsBf",
    domClass: "fatmasssPlot",
    dataUri: location.origin.indexOf('localhost') >= 0 ? "dataCache/data.json" : "/userdata/fatmassweight",
    curveFitting: true,
    
    curveColor: "black",
    pointColor: "blue",
});
export default generatedMod;
