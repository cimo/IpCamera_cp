/* global loader, helper */

var flashBag = new FlashBag();

function FlashBag() {
    // Vars
    var self = this;
    
    var element;
    
    // Properties
    self.setElement = function(value) {
        element = value;
    };
    
    // Functions public
    self.init = function() {
        element = null;
    };
    
    self.show = function(message) {
        var snackbarDataObj = {
            message: message,
            actionText: window.text.index_7,
            actionHandler: function() {}
        };
        
        element.show(snackbarDataObj);
        
        $("#flashBag").find(".mdc-snackbar__action-button").removeAttr("aria-hidden");
    };
    
    self.sessionActivity = function() {
        if ($("#flashBag").find(".content").length > 0 && window.session.userInform !== "") {
            loader.hide();
            
            self.show(window.session.userInform);
        }
    };
    
    // Functions private
}