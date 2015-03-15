"format es6";
console.log("module weight running");

var remoteData;
var url = location.origin.indexOf('localhost') >= 0 ? "dataCache/weight.json" : "/userdata/weight";	   
	
var drawDataTemp;
var drawFunc = (drawData) => {
    var yFreq = [];
    var thisYScale = drawData.yScale.domain(getYMinMax(remoteData));
    drawData.svg.append("g")
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
   	.attr('r', 2)
   	.attr('fill', 'brown');
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
			yScale: getYMinMax(remoteData)
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
