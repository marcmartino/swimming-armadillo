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
	    var fatVal = d['Fat mass (%)'] || 0;
	    var intFat = parseInt(fatVal, 10);

	    //console.log(thisYScale(d['Units']  || 0));
	    //console.log(fatVal);
	    yFreq[intFat] = yFreq[intFat] ? yFreq[intFat] + 1 : 1;
   	    return thisYScale(d['Fat mass (%)']  || 0);
   	})
   	.attr('r', 3) 
   	.attr('fill', 'brown');

    //console.log(yFreq);
	    /*drawData.legend
		.insert("text").attr("class", "fatChart")
		.attr("x", 20).attr("y",20)
		.text("fat chart");*/
};
function getXMinMax (data) {
    var dateAccessor = (el) => {
	return Date.parseString(el.Date,'yyyy-MM-dd H:mm a');
	return (new Date(el.Date));
    };
    return [d3.min(data, dateAccessor), d3.max(data, dateAccessor)];
}
function getYMinMax (data) {
    var fatAccessor  = (el) => {
	return el['Fat mass (%)'];
    };
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
			yScale: getYMinMax(remoteData)
		    });
		    
		    
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


