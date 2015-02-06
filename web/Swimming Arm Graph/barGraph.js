var dataset = JSON.parse(d3.select("#withingsData").text());

var h = 200,
	w = 400,
	barPadding = 1;
	svg = d3.select("#dThree")
    .append("svg")
    .attr("width", w)
    .attr("height", h);

svg.selectAll("rect")
   	.data(dataset)
   	.enter()
    .append("rect")
    .attr("x", function (d, i) {
   		return i * (w / dataset.length);
   	})
    .attr("y", function (d, i) {
   		return (h - d['Weight (lb)']);
   	})
    .attr("width", function (d, i) {
   		return w / dataset.length - barPadding;
   	})
    .attr("height", function (d, i) {
   		console.log(d['Weight (lb)']);
   		return (d['Weight (lb)']);
   	});