"use strict";

/* global loader */

class FlashBag {
    // Properties
    set setElement(value) {
        this.snackbarMdc = value;
    }
    
    // Functions public
    constructor() {
        this.snackbarMdc = null;
    }
    
    show = (message) => {
        if ($("#flashBag").attr("aria-hidden") !== "true" || $("#flashBag").hasClass("mdc-snackbar mdc-snackbar--active") === true)
            $("#flashBag").find(".mdc-snackbar__action-button").click();
        
        let snackbarDataObj = {
            message: message,
            actionText: window.text.index_7,
            actionHandler: () => {}
        };
        
        this.snackbarMdc.show(snackbarDataObj);
    }
    
    sessionActivity = () => {
        if ($("#flashBag").find(".content").length > 0 && window.session.userInform !== "") {
            loader.hide();
            
            this.show(window.session.userInform);
        }
    }
    
    // Functions private
}