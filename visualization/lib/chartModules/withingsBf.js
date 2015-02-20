"format es6";
console.log("module poo running");
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
var url = location.origin.indexOf('localhost') >= 0 ? "dataCache/data.json" : "/userdata";	   
	
var drawDataTemp;
var drawFunc = (drawData) => {
    console.log("drawFuncEx");
    console.log(drawData);
    console.warn(drawData);
   // console.log(getXMinMax(remoteData));
   // console.log([drawData.chartPadding, drawData.w - drawData.chartPadding]);

    

    drawData.svg.append("g")
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
	    return 30;
   	    return drawData.yAxis(d['Fat mass (%)'] / 100 || 0);
   	})
   	.attr('r', 2)
   	.attr('fill', 'brown');

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
		    remoteData = data;
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
}*/


