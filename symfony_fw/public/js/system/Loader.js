"use strict";

/* global */

class Loader {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    show = () => {
        $(".loader_back").show();
        $(".loader").show();
    }
    
    hide = () => {
        $(".loader").hide();
        $(".loader_back").hide();
    }
    
    // Functions private
}