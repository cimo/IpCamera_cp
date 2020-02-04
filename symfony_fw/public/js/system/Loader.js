"use strict";

/* global */

const loader = new Loader();

function Loader() {
    // Vars
    const self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
    };
    
    self.show = function() {
        $(".loader_back").show();
        $(".loader").show();
    };
    
    self.hide = function() {
        $(".loader").hide();
        $(".loader_back").hide();
    };
    
    // Functions private
}