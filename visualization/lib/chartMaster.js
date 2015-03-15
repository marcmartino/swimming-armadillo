function isNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

/*var dataset = JSON.parse(d3.select("#withingsData").text());
_.remove(dataset, function (datum) {
	return (datum["Fat mass (%)"] === null || datum['Lean mass (%)'] === null);
});*/

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
function fatChart(drawData) {
  console.log("fatChart func moved to withingsBF.js");
}
function leanChart(drawData) {
    drawData.svg.append("g").classed("leanChart chartGroup", true)
	.selectAll("rect")
   	.data(dataset)
   	.enter()
    .append("circle")
    .attr("cx", function (d, i) {
   		// return timestampData.scale(Date.parse(d.Date));
   		return drawData.timestampData.scale(Date.parseString(d.Date,'yyyy-MM-dd H:mm a'));
   	})
    .attr("cy", function (d, i) {
   		return drawData.bodyMassData.scale(d['Lean mass (%)'] / 100 || 0);
   	})
   	.attr('r', 2)
   	.attr('fill', 'orange');
   /*  drawData.legend
	.insert("text").classed("leanChart legendItem", true)
	.attr("x", 100).attr("y",20)
	.text("lean chart")
	.on( "click", function () {
	    console.log("cclicked lean");
	    d3.select(".leanChart" + ".chartGroup")
	    .style("visibility", "hidden");
	});
	*/
}
function redrawChart(svgData, xChartFuncs) {
    d3.select('#dThree').select("svg");//.remove();
   // console.log("redrawing");
    var drawData = {
	//timestampData: getTimestampData(),
//	bodyMassData: getBodyMassData(),
	svg: d3.select("#dThree svg")
	    //.append("svg") 
	    .attr("width", svgData.w)
	    .attr("height", svgData.h)
    };
    //drawData.legend = drawData.svg.append("g")
	//.attr("class", "svgLegend").attr("x", 10);
    drawData.xAxis = d3.svg.axis()
	//.scale(drawData.timestampData.scale)
	.orient("bottom")
        .ticks(parseInt(svgData.w / 75, 10));
    drawData.yAxis = d3.svg.axis()
    	//.scale(drawData.bodyMassData.scale)
    	.orient("left")
    	.ticks(5)
    	.tickFormat(d3.format("%"));





    //leanChart(drawData);
    //fatChart(drawData);
//    withingsBf.fun(drawData);
    ///console.log(xChartFuncs[0]);
    

    drawData.xScale = d3.time.scale()
	.domain(xChartFuncs[0].xScale)
	.range([0 + svgData.chartPadding, svgData.w - svgData.chartPadding]);

    drawData.yScale = d3.scale.linear()
	.range([0 + svgData.chartPadding, svgData.h - svgData.chartPadding]);

    drawData.xAxis.scale(drawData.xScale);
    drawData.yAxis.scale(xChartFuncs[0].yScale);
    xChartFuncs[0].chart(drawData);

drawData.svg.append("g")
	.attr("transform", "translate(0," + (svgData.h - svgData.chartPadding) + ")")
	.attr("class", "axis")
    .call(drawData.xAxis);
/*
drawData.svg.append("g")
    .attr("class", "axis")
    .attr("transform", "translate(" + svgData.chartPadding + ",0)")
    .call(drawData.yAxis);*/
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
    //.domain(dateArr)
			.range([0 + svgData.chartPadding, svgData.w - svgData.chartPadding]);

	return {min: min, max: max, scale: scale};
}

function resize() {
   // console.log('resizing');
    svgData.w = d3.min([1100, window.innerWidth]);
    //console.log(svgData);
    redrawChart(svgData);
}
d3.select(window).on('resize', resize);

import withingsBf from './chartModules/withingsBf';
import weight from './chartModules/weight';

var dataMods = [withingsBf, weight];

dataMods.forEach(function (dataMod) {
    //console.log("datamod promising");
    dataMod.prom.then(function (result) {
	
	console.log("promise success");
	  console.log(result);
	redrawChart(svgData, [result]);
    }, function (err) {
	console.log("promise err");
	console.log(err);
    });
});


