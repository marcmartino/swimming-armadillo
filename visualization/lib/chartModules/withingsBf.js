"format es6";
console.log("module fatmass running");

console.log("trying to import");
System.import("lib/chartModuleGen").then(function (m) {
    console.log("loaaded");
    console.log(m);
});
