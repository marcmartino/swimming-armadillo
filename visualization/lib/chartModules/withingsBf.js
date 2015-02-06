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

	    /*drawData.legend
		.insert("text").attr("class", "fatChart")
		.attr("x", 20).attr("y",20)
		.text("fat chart");*/
};
  export default  {
    unit: "bpm",
    dateMinMaxFunc:  () => {
	
    },
    prom: new Promise(function(resolve, reject) {
  // do a thing, possibly async, then…

	$.ajax({
		type: "GET",
		url: url,
		success: (data) => {
		    //console.log("get success");
		   //  console.log(data);
		    remoteData = data;
		    resolve({
			chart: drawFunc,
			xScale: 10,
			yScale: 12
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


