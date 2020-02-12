"use strict";

/* global loader */

class FlashBag {
    // Properties
    set setElement(value) {
        this.element = value;
    }
    
    // Functions public
    constructor() {
        this.element = null;
    }
    
    show = (message) => {
        let snackbarDataObj = {
            message: message,
            actionText: window.text.index_7,
            actionHandler: () => {}
        };
        
        this.element.show(snackbarDataObj);
        
        $("#flashBag").find(".mdc-snackbar__action-button").removeAttr("aria-hidden");
    }
    
    sessionActivity = () => {
        if ($("#flashBag").find(".content").length > 0 && window.session.userInform !== "") {
            loader.hide();
            
            this.show(window.session.userInform);
        }
    }
    
    // Functions private
}