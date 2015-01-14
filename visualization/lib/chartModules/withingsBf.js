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
  export default  {
    poo: "poo",
    fun: (s) => {"hello, " + s}
  }
