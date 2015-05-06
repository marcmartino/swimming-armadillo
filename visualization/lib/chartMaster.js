function isNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}
var fetchModName = getParameterByName('measure');

var svgData = {
    h: 300,
    w: d3.min([1100, window.innerWidth]),
    chartPadding: 50,
    
};
function redrawChart(svgData, xChartFuncs) {
    d3.select('#dThree').select("svg");
    var drawData = {
	svg: d3.select("#dThree svg")
	    .attr("width", svgData.w)
	    .attr("height", svgData.h)
    };

    drawData.xAxis = d3.svg.axis()
	.orient("bottom")
        .ticks(parseInt(svgData.w / 75, 10));
    drawData.yAxis = d3.svg.axis()
    	.orient("left")
    	.ticks(5)
    	.tickFormat(d3.format("%"));
    drawData.xScale = d3.time.scale()
	.domain(xChartFuncs[0].xScale)
	.range([0 + svgData.chartPadding, svgData.w - svgData.chartPadding]);

    drawData.yScale = d3.scale.linear()
	.range([0 + svgData.chartPadding, svgData.h - svgData.chartPadding]);

    drawData.legend = $("#legend"); //drawData.svg.append("g.legend");
    createLegendGroups(drawData.legend, xChartFuncs[0].name);

    drawData.xAxis.scale(drawData.xScale);
    drawData.yAxis.scale(drawData.yScale);
    xChartFuncs[0].chart(drawData);

    drawData.svg.select("g.xAxis").remove();
    drawData.svg.append("g")
	.attr("transform", "translate(0," + (svgData.h - svgData.chartPadding) + ")")
	.attr("class", "xAxis")
    .call(drawData.xAxis);

    drawData.svg.select("g.yAxis").remove();
    drawData.svg.append("g")
	.attr("transform", "translate(" + svgData.chartPadding + ", 0)")
	.attr("class", "yAxis")
    .call(drawData.yAxis);
}

function createLegendGroups(legend, chartName) {
    legend.append("<div class='" + chartName + "'></div>");
}

function getTimestampData() {
	var getTimestampFromJson = function (datum) {
	    return Date.parseString(datum,'yyyy-MM-dd H:mm a');
	};

	var dateArr = _.map(_.pluck(dataset, "Date"), getTimestampFromJson),
           min = d3.min(dateArr),
                max =d3.max(dateArr),
		rangePadding = (max - min) * 0.01,
		scale = d3.time.scale()
			.domain([new Date(min - rangePadding), max])
			.range([0 + svgData.chartPadding, svgData.w - svgData.chartPadding]);

	return {min: min, max: max, scale: scale};
}

function resize() {
    svgData.w = d3.min([1100, window.innerWidth]);
    redrawChart(svgData);
}

function convertGetParams(imports) {
    var measures = getParameterByName("measure");

    return measures.split(",").map(function (measureName) {
	console.log(measureName);
	console.log(imports[measureName]);
	return imports[measureName];
    });
}

d3.select(window).on('resize', resize);

//var withingsBf, weight;

chartSettings.startTime = getParameterByName("start");
chartSettings.endTime =   getParameterByName("end");

import withingsBf from './chartModules/withingsBf';
import weight from './chartModules/weight';

var dataMods = convertGetParams({weight: weight, withingsBf: withingsBf});

dataMods.forEach(function (dataMod) {
    if (dataMod) {
	dataMod.prom.then(function (result) {
	    
	    console.log("promise success");
	    console.log(result);
	    redrawChart(svgData, [result]);
	}, function (err) {
	    console.log("promise err");
	    console.log(err);
	});
    }
});
