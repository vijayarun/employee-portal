/**
 * @author A Vijay <vijay.a@technoduce.com>
 */

/**
 * 
 * @type Array
 */
window.onBootCall = [];

/**
 *
 * @param func
 */
function runOnLoad( func ) {
    window.onBootCall.push(func);
}

/**
 * 
 * @returns {undefined}
 */
function exeOnLoad(){
    for(var x = 0; x < onBootCall.length; x++){
        (onBootCall[x]).apply(arguments);
    }
    onBootCall = [];
}