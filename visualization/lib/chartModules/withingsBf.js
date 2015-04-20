"format es6";
console.log("module fatmass running");
//export default = "poo";
/*export {
    unit: "bpm",
    viz: function (drawData) {
	console.log(drawData);
	console.log('viz function running');
    }
};*/

//export default {poo:"poo"};
var remoteData;
var url = location.origin.indexOf('localhost') >= 0 ? "dataCache/data.json" : "/userdata/fatmassweight";
var bfMin = undefined;	   
	
var drawDataTemp;
var drawFunc = (drawData) => {
    console.log("fatmass drawFuncEx");
    //console.warn(drawData);
    var yFreq = [];
    var thisYScale = drawData.yScale.domain(getYMinMax(remoteData));
    //drawData.svg.append("g").attr("class", "fattyTest");

    drawData.svg.append("g")
	.attr("class", "fatmassPlot")
	.selectAll("rect")
	.data(remoteData)
   	.enter()
	.append("circle")
	.attr("cx", function (d, i) {
	    //drawData.xAxis.scale(
	   
	   // console.log(drawData.xAxis.scale());
	    //console.log(drawData.xAxis.scale(new Date()));
	    return drawData.xScale(Date.parseString(d.Date, 'yyyy-MM-dd H:mm a'));
	    
	    //return drawData.xAxis.scale(Date.parseString(d.Date,'yyyy-MM-dd H:mm a'));
	})
	.attr("cy", function (d, i) {
	    var fatVal = d['Units'] || 0;
	    var intFat = parseInt(fatVal, 10);

	    //console.log(thisYScale(d['Units']  || 0));
	    //console.log(fatVal);
	    yFreq[intFat] = yFreq[intFat] ? yFreq[intFat] + 1 : 1;
   	    return thisYScale(d['Units']  || 0);
   	})
   	.attr('r', 3) 
   	.attr('fill', 'brown');

    $("#legend .withingsBf").text("body fat")
	.off("click")
	.on("click", (e) => {
	    //console.log("poo");
	    $("g.fatmassPlot").toggle();
	});
    //console.log(yFreq);
	    /*drawData.legend
		.insert("text").attr("class", "fatChart")
		.attr("x", 20).attr("y",20)
		.text("fat chart");*/
    var lineFunction = d3.svg.line()
	.x((d) => {return drawData.xScale(new Date(d.Date));})
	.y((d) => {return drawData.yScale(d.Units);})
	.interpolate('basis');
    
    var grouped = remoteData.reduce(groupByDate,{});
    var pluckedGroups = _.reduce(grouped, arrayToObj, []); 
    drawData.svg.select("g.fatmassPlot")
	.append("path")
	.attr("d", lineFunction(pluckedGroups))
        .attr("stroke", "gray")
        .attr("stroke-width", 4)
        .attr("fill", "none");
};
var isOutlier = (function (dataSet, accessor, customPred) {
    var outlierStats = {
	mean: d3.mean(dataSet, accessor),
	median: d3.median(dataSet, accessor),
	sd: d3.deviation(dataSet, accessor),
	max: parseInt(d3.max(dataSet, accessor), 10),
	min: parseInt(d3.min(dataSet, accessor), 10),
    };
    outlierStats.devMax = outlierStats.mean + outlierStats.sd * 2;
    outlierStats.devMin = outlierStats.mean - outlierStats.sd * 2;

    console.log(outlierStats);
    // console.log({sd, mean, min, max, median});
    // console.log(d3.extent(dataSet, accessor));
    var predFunc = (!!customPred ? customPred : (function (dataPoint) {
	console.log("customPred was not supplied");
	return (!(dataPoint < this.devMax && dataPoint > this.devMin));
    }));
    return predFunc.bind(outlierStats);
});
function groupByDate(prev, curr, index, arr) {
    var currUnits = parseInt(curr.Units, 10);
//    if (currUnits >= parseInt(bfMin, 10) + 50) {
    if (!isOutlier(currUnits)) {
	//console.log([curr.Units, bfMin]);

	console.log(currUnits + " is not an outlier");
	var itemDate = Date.parseString(curr.Date, 'yyyy-MM-dd h:mm a');
	itemDate = itemDate.setDate(parseInt(itemDate.getDate() / 2, 10) * 2);
	itemDate = (new Date(itemDate)).setHours(12,0,0,0);
	var prevItem = prev[itemDate];
	
	if (prevItem) {
	    prev[itemDate].Units = ((prevItem.Units * prevItem.count) +currUnits) / (prevItem.count + 1);
	    prev[itemDate].count++;
	} else {
	    prev[itemDate] = {Units: parseInt(curr.Units, 10), count: 1};
	}
    } else {
	console.warn(currUnits + "is an outlier");
    }
    return prev;
}
function arrayToObj(prev, curr, index, arr) {
    prev.push({Units: curr.Units, Date: parseInt(index,10)});
    return prev;
}
function getXMinMax (data) {
    var dateAccessor = (el) => {
	return Date.parseString(el.Date,'yyyy-MM-dd H:mm a');
    };
    return [d3.min(data, dateAccessor), d3.max(data, dateAccessor)];
}
var fatAccessor  = (el) => {
   return el['Units'];
};
function getYMinMax (data) {   
    return [d3.min(data, fatAccessor), d3.max(data, fatAccessor)];
};
  export default  {
    unit: "bpm",
    prom: new Promise(function(resolve, reject) {
  // do a thing, possibly async, then…

	$.ajax({
		type: "GET",
		url: url,
		success: (data) => {
		    //console.log("get success");
		   // console.log(data);
		    remoteData = typeof data == 'object' ? data : JSON.parse(data);
//console.log(getYMinMax(remoteData));
		    
		    resolve({
			chart: drawFunc,
			xScale: getXMinMax(remoteData),
			yScale: getYMinMax(remoteData),
			name: "withingsBf"
		    });
		    isOutlier = isOutlier(remoteData, fatAccessor, (function(dataPoint) {
			
			
			var range = this.max - this.min,
			    limit = range * (50/100);
			console.log({limit, dataPoint, maxLimit: this.max - limit, minLimit: this.min + limit});
			return (dataPoint < this.max - limit || dataPoint < this.min + limit);
		    }));
		    
		    /*if (drawDataTemp) {
			drawFunc(drawDataTemp);
			drawDataTemp = undefined;
		    }*/
		},
		error: (d) => {
		    console.log("ajax errored");
		    console.log(d);
		    reject(Error(d));
		}
	    });
}),
    fun: (function () {
	
	return function(drawData) {
	    console.log("about tto draw");
	    console.log(remoteData);

	    if (remoteData) {
		drawFunc(drawData);
	    } else {
		console.log("defering draw call");
		drawDataTemp = drawData;
	    }
	   // return 'poo';
	};
    }())
  }

/*function getBodyMassData() {
	var min = _.min([_.min(dataset, "Units")["Units"], _.min(dataset, "Lean mass (%)")["Lean mass (%)"]]) / 100,
		max = _.max([_.max(dataset, "Units")["Units"], _.max(dataset, "Lean mass (%)")["Lean mass (%)"]]) / 100,
		rangePadding = (max - min) * 0.25;
		scale = d3.scale.linear()

			//added range padding,but also maxing by zero to make sure no negitive percentage appears on axis
			.domain([_.min([max + rangePadding,100]), _.max([min - rangePadding,0])])
			.range([0 + svgData.chartPadding, svgData.h - svgData.chartPadding]);

	console.log("body mass max: ");
	console.log(max);
	return {min: min, max: max, scale: scale};
}*/


