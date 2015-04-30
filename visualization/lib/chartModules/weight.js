"format es6";
console.log("module weight running");

//var remoteData;
var url = location.origin.indexOf('localhost') >= 0 ? "dataCache/weight.json" : "/userdata/weight";	   

console.log("attempting to use the gen module");

var generatedMod = drawGen.func({
    name: "weight",
    domClass: "weightPlot",
    dataUri: url,
    curveFitting: true,
});
console.log(generatedMod);
export default generatedMod;

/*	
var drawDataTemp;
var drawFunc = (drawData) => {
    console.log("weight draw func executing");
    var yFreq = [];
    var thisYScale = drawData.yScale.domain(getYMinMax(remoteData));
    drawData.svg.append("g")
	.attr("class", "weightPlot")
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
   	.attr('fill', 'green');

    //console.log("about to append shit into legend");
    //console.log(d3.select("g.legend .weight"));
    $("#legend .weight").text("weight yeah!")
	.off("click")
	.on("click", (e) => {
	    console.log("poo");
	    $("g.weightPlot").toggle();
	});
    //console.log(drawData.legend.selectOne("g.legend .weight"));
   // console.log(yFreq);
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
	return el['Units'];
    };
    console.log([d3.min(data, fatAccessor), d3.max(data, fatAccessor)]);
    return [d3.min(data, fatAccessor), d3.max(data, fatAccessor)];

};
  export default  {
    unit: "bpm",
    prom: new Promise(function(resolve, reject) {

	$.ajax({
		type: "GET",
		url: url,
		success: (data) => {
		    remoteData = data;
		    resolve({
			chart: drawFunc,
			xScale: getXMinMax(remoteData),
			yScale: getYMinMax(remoteData),
			name: "weight",
		    });
		    
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
	    console.log("about tto draw weight");
	    //console.log(remoteData);

	    if (remoteData) {
		drawFunc(drawData);
	    } else {
		console.log("defering draw weight call");
		drawDataTemp = drawData;
	    }
	};
    }())
  }
*/
