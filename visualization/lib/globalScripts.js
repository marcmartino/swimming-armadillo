function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

var chartSettings = {};

chartSettings.startTime = getParameterByName("start");
chartSettings.endTime =   getParameterByName("end");

var debug = function (style) {
    var options = {
	notice: (function () {
	    $("body").prepend("<div id='notice'>");
	    var noticeDiv = $("#notice");

	    return function (info) {
		//console.log(info);
		noticeDiv.text(info);
	    };
	}()),
	none: function () {}
    };
    return options[style];
},
debugObj = debug("notice");
