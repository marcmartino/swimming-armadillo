"format es6";
console.log("es6?");
/*function isNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

var dataset = JSON.parse(d3.select("#withingsData").text());
_.remove(dataset, function (datum) {
	return (datum["Fat mass (%)"] === null || datum['Lean mass (%)'] === null);
});

var svgData = {
    h: 300,
    w: d3.min([1100, window.innerWidth]),
    chartPadding: 50,
    
};
function fatChart(drawData) {
   drawData.svg.append("g")
	.selectAll("rect")
   	.data(dataset)
   	.enter()
    .append("circle")
    .attr("cx", function (d, i) {
   	return drawData.timestampData.scale(Date.parseString(d.Date,'yyyy-MM-dd H:mm a'));
   })
    .attr("cy", function (d, i) {
   		return drawData.bodyMassData.scale(d['Fat mass (%)'] / 100 || 0);
   	})
   	.attr('r', 2)
   	.attr('fill', 'brown');

    drawData.legend
	.insert("text").attr("class", "fatChart")
	.attr("x", 20).attr("y",20)
	.text("fat chart");
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
   	.attr('fill', 'orange');/*
   	.on("mouseover", function (d) {
   		d3.select(this)
   			.transition()
        	.attr("fill", "blue")
        	.attr("r", 5);
   	})
   	.on("mouseout", function (d) {
   		d3.select(this)
   			.transition()
        	.attr("fill", "orange")
        	.attr("r", 2);
   	});*/
    /* drawData.legend
	.insert("text").classed("leanChart legendItem", true)
	.attr("x", 100).attr("y",20)
	.text("lean chart")
	.on( "click", function () {
	    console.log("cclicked lean");
	    d3.select(".leanChart" + ".chartGroup")
	    .style("visibility", "hidden");
	});
}
function redrawChart(svgData) {
    d3.select('#dThree').select("svg").remove();
   // console.log("redrawing");
    var drawData = {
	timestampData: getTimestampData(),
	bodyMassData: getBodyMassData(),
	svg: d3.select("#dThree")
	    .append("svg")
	    .attr("width", svgData.w)
	    .attr("height", svgData.h)
    };
    drawData.legend = drawData.svg.append("g")
    .attr("class", "svgLegend").attr("x", 10);
    drawData.xAxis = d3.svg.axis()
	.scale(drawData.timestampData.scale)
	.orient("bottom")
        .ticks(parseInt(svgData.w / 75, 10));
    drawData.yAxis = d3.svg.axis()
    	.scale(drawData.bodyMassData.scale)
    	.orient("left")
    	.ticks(5)
    	.tickFormat(d3.format("%"));





    leanChart(drawData);
    fatChart(drawData);

drawData.svg.append("g")
	.attr("transform", "translate(0," + (svgData.h - svgData.chartPadding) + ")")
	.attr("class", "axis")
    .call(drawData.xAxis);

drawData.svg.append("g")
    .attr("class", "axis")
    .attr("transform", "translate(" + svgData.chartPadding + ",0)")
    .call(drawData.yAxis);
}

function getBodyMassData() {
	var min = _.min([_.min(dataset, "Fat mass (%)")["Fat mass (%)"], _.min(dataset, "Lean mass (%)")["Lean mass (%)"]]) / 100,
		max = _.max([_.max(dataset, "Fat mass (%)")["Fat mass (%)"], _.max(dataset, "Lean mass (%)")["Lean mass (%)"]]) / 100,
		rangePadding = (max - min) * 0.25;
		scale = d3.scale.linear()

			//added range padding,but also maxing by zero to make sure no negitive percentage appears on axis
			.domain([_.min([max + rangePadding,100]), _.max([min - rangePadding,0])])
			.range([0 + svgData.chartPadding, svgData.h - svgData.chartPadding]);

	console.log("body mass max: ");
	console.log(max);
	return {min: min, max: max, scale: scale};
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
redrawChart(svgData);
function isNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

var dataset = JSON.parse(d3.select("#withingsData").text());
_.remove(dataset, function (datum) {
	return (datum["Fat mass (%)"] === null || datum['Lean mass (%)'] === null);
});

var svgData = {
    h: 300,
    w: d3.min([1100, window.innerWidth]),
    chartPadding: 50,
    
};
function fatChart(drawData) {
   drawData.svg.append("g")
	.selectAll("rect")
   	.data(dataset)
   	.enter()
    .append("circle")
    .attr("cx", function (d, i) {
   	return drawData.timestampData.scale(Date.parseString(d.Date,'yyyy-MM-dd H:mm a'));
   })
    .attr("cy", function (d, i) {
   		return drawData.bodyMassData.scale(d['Fat mass (%)'] / 100 || 0);
   	})
   	.attr('r', 2)
   	.attr('fill', 'brown');

    drawData.legend
	.insert("text").attr("class", "fatChart")
	.attr("x", 20).attr("y",20)
	.text("fat chart");
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
   	.attr('fill', 'orange');/*
   	.on("mouseover", function (d) {
   		d3.select(this)
   			.transition()
        	.attr("fill", "blue")
        	.attr("r", 5);
   	})
   	.on("mouseout", function (d) {
   		d3.select(this)
   			.transition()
        	.attr("fill", "orange")
        	.attr("r", 2);
   	});*/
    /* drawData.legend
	.insert("text").classed("leanChart legendItem", true)
	.attr("x", 100).attr("y",20)
	.text("lean chart")
	.on( "click", function () {
	    console.log("cclicked lean");
	    d3.select(".leanChart" + ".chartGroup")
	    .style("visibility", "hidden");
	});
}
function redrawChart(svgData) {
    d3.select('#dThree').select("svg").remove();
   // console.log("redrawing");
    var drawData = {
	timestampData: getTimestampData(),
	bodyMassData: getBodyMassData(),
	svg: d3.select("#dThree")
	    .append("svg")
	    .attr("width", svgData.w)
	    .attr("height", svgData.h)
    };
    drawData.legend = drawData.svg.append("g")
    .attr("class", "svgLegend").attr("x", 10);
    drawData.xAxis = d3.svg.axis()
	.scale(drawData.timestampData.scale)
	.orient("bottom")
        .ticks(parseInt(svgData.w / 75, 10));
    drawData.yAxis = d3.svg.axis()
    	.scale(drawData.bodyMassData.scale)
    	.orient("left")
    	.ticks(5)
    	.tickFormat(d3.format("%"));





    leanChart(drawData);
    fatChart(drawData);

drawData.svg.append("g")
	.attr("transform", "translate(0," + (svgData.h - svgData.chartPadding) + ")")
	.attr("class", "axis")
    .call(drawData.xAxis);

drawData.svg.append("g")
    .attr("class", "axis")
    .attr("transform", "translate(" + svgData.chartPadding + ",0)")
    .call(drawData.yAxis);
}

function getBodyMassData() {
	var min = _.min([_.min(dataset, "Fat mass (%)")["Fat mass (%)"], _.min(dataset, "Lean mass (%)")["Lean mass (%)"]]) / 100,
		max = _.max([_.max(dataset, "Fat mass (%)")["Fat mass (%)"], _.max(dataset, "Lean mass (%)")["Lean mass (%)"]]) / 100,
		rangePadding = (max - min) * 0.25;
		scale = d3.scale.linear()

			//added range padding,but also maxing by zero to make sure no negitive percentage appears on axis
			.domain([_.min([max + rangePadding,100]), _.max([min - rangePadding,0])])
			.range([0 + svgData.chartPadding, svgData.h - svgData.chartPadding]);

	console.log("body mass max: ");
	console.log(max);
	return {min: min, max: max, scale: scale};
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
redrawChart(svgData);
*/
///var withingsBf = System.load('lib/chartModules/withingsBf');
console.log('about to load poo');
System.import( 'lib/chartModules/withingsBf')
    .then(function (withingsBf) {
	console.log("poo loaded");
	console.log(withingsBf.default.poo);
	console.log(withingsBf.default.fun);
	//console.log(withingsBf.poo);
})
//import withingsBf from './chartModules/withingsBf';
//console.log(withingsBf);
//console.log("poo");
