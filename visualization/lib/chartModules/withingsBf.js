"format es6";
console.log("module fatmass running");
console.log("attempting to use the gen module");

var generatedMod = drawGen.func({
    name: "withingsBf",
    domClass: "fatmasssPlot",
    dataUri: location.origin.indexOf('localhost') >= 0 ? "dataCache/data.json" : "/userdata/fatmasssweight",
    curveFitting: true,
});
console.log(generatedMod);
export default generatedMod;
