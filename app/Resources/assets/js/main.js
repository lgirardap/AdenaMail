const $ = global.$ = global.jQuery = require('jquery');
require('bootstrap-sass');
const EventBus = global.EventBus = require('./event-bus');

require('./form-fields');
require('./form-validation');

let contexts = {
    //AppBundle: require.context("src/AppBundle/Resources/assets/js", true, /\.js$/),
};
if(window.fileName){
    let bundleName = window.fileName.split(':')[0];
    let path = window.fileName.split(':')[1];
    path = path ? path+"/" : "";
    let fileName = window.fileName.split(':')[2];

    try{
        contexts[bundleName]("./"+path+fileName+".js");
    }catch(e){
        console.log(e);
    }
}
