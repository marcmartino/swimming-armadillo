"format es6";
console.log("mod gen running");

var drawGen = function (settings) {
    
    var url = settings.dataUri,
    remoteData,
    chartOutlierPred;
    var drawDataTemp;
    var drawFunc = (drawData) => {
	var yFreq = [];
	var thisYScale = drawData.yScale.domain(getYMinMax(remoteData));
	
	drawData.svg.append("g")
	    .attr("class", settings.domClass)
	    .selectAll("rect")
	    .data(remoteData)
   	    .enter()
	    .append("circle")
	    .attr("cx", function (d, i) {
		return drawData.xScale(Date.parseString(d.Date, 'yyyy-MM-dd H:mm a'));
	    })
	    .attr("cy", function (d, i) {
		var fatVal = d['Units'] || 0;
		var intFat = parseInt(fatVal, 10);
		
		yFreq[intFat] = yFreq[intFat] ? yFreq[intFat] + 1 : 1;
   		return thisYScale(d['Units']  || 0);
   	    })
   	    .attr('r', 3) 
   	    .attr('fill', 'brown');
	
	$("#legend ." + settings.name).text("body fat")
	    .off("click")
	    .on("click", (e) => {
		$("g." + settings.domClass).toggle();
	    });


	if (settings.curveFitting) {
	    var lineFunction = d3.svg.line()
		.x((d) => {return drawData.xScale(new Date(d.Date));})
		.y((d) => {return drawData.yScale(d.Units);})
		.interpolate('basis');
	
	    var grouped = remoteData.reduce(groupByDate,{});
	    var pluckedGroups = _.reduce(grouped, arrayToObj, []); 
	    drawData.svg.select("g." + settings.domClass)
		.append("path")
		.attr("d", lineFunction(pluckedGroups))
		.attr("stroke", "gray")
		.attr("stroke-width", 4)
		.attr("fill", "none");
	}
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
	
	var predFunc = (!!customPred ? customPred : (function (dataPoint) {
	    // console.log(dataPoint, this.devMax, this.devMin);
	    return (!(dataPoint < this.devMax && dataPoint > this.devMin));
	}));
	return predFunc.bind(outlierStats);
    });
    function groupByDate(prev, curr, index, arr) {
	var currUnits = parseInt(curr.Units, 10);
	
	if (!chartOutlierPred || !chartOutlierPred(currUnits)) {
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
	    console.warn(currUnits + "is an outlier " );
//	    console.log(chartOutlierPred);
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
    var fatAccessor  = settings.dataAccessor || ((el) => {
	return el['Units'];
    });
    function getYMinMax (data) {   
	return [d3.min(data, fatAccessor), d3.max(data, fatAccessor)];
    };
    //export default  {
    return {
	unit: "bpm",
	prom: new Promise(function(resolve, reject) {
	    
	    $.ajax({
		type: "GET",
		url: url,
		success: (data) => {
		    remoteData = typeof data == 'object' ? data : JSON.parse(data);
		    
		    resolve({
			chart: drawFunc,
			xScale: getXMinMax(remoteData),
			yScale: getYMinMax(remoteData),
			name: settings.name,
		    });

		    if (settings.curveFitting) {
			chartOutlierPred = isOutlier(remoteData, fatAccessor);
		    }
		    
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
		if (remoteData) {
		    drawFunc(drawData);
		} else {
		    drawDataTemp = drawData;
		}
	    };
	}())
    }
};
function alternateOutlierFunction(dataPoint) {
			
    var range = this.max - this.min,
    limit = range * (50/100);
    return (dataPoint < this.max - limit || dataPoint < this.min + limit);
}
//};

export default { func: drawGen };
