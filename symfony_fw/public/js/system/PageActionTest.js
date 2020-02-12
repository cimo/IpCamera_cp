"use strict";

/* global */

const deferred = (callback) => {
    if (window.$ !== undefined)
        callback();
    else {
        let timeoutEvent = setTimeout(() => {
            clearTimeout(timeoutEvent);
            
            deferred(callback);
        }, 50);
    }
};

deferred(() => {
});