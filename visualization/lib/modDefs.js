"format es6";

import drawGenMod from "./chartGen";
var drawGen = drawGenMod.func,
modules = {};

modules.weight = function () {
    return drawGen({
	name: "weight",
	domClass: "weightPlot",
	dataUri: "/userdata/weight",
	curveFitting: true,
	pointColor: "green",
	invertedY: true,
	yFormat: function (d) {
	    return d/1000 + "kg";
	}
    });
};

modules.fatratio = function () {
    return drawGen({
	name: "fatratio",
	domClass: "weightPlot",
	dataUri: "/userdata/fatratio",
	curveFitting: true,
	pointColor: "pink",
	invertedY: true,
	filterFunc: function (datum) {
	    return (parseInt(datum['Units'], 10) > 3);
	},
    });
};

modules.unknown = function (name) {
    console.log("unspecifiedd module being run");
    return drawGen({
	name: name,
	domClass: name + "Plot",
	dataUri: "/userdata/" + name,
	curveFitting: true
    });
};

export default modules;
