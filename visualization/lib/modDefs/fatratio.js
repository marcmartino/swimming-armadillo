"format es6";
var url = location.origin.indexOf('localhost') >= 0 ? "dataCache/fatratio.json" : "/userdata/fatratio";

var generatedMod = drawGen.func({
    name: "fatratio",
    domClass: "weightPlot",
    dataUri: url,
    curveFitting: true,
    pointColor: "pink",
    curveColor: "pink",
    invertedY: true,
    filterFunc: function (datum) {
	return (parseInt(datum['Units'], 10) > 3);
    },
});
export default generatedMod;
