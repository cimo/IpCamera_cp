/* global flashBag */

var popupEasy = new PopupEasy();

function PopupEasy() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.create = function(title, message, callbackOk, callbackCancel, loader) {
        var buttons = "";
        var clickOk = null;
        var clickCancel = null;
        
        if (callbackOk !== null) {
            buttons += "<a id=\"popup_easy_ok\" class=\"button_custom\" type=\"button\">" + window.text.ok + "</a>";
            
            clickOk = function() {
                callbackOk();
            };
        }
        
        if (callbackCancel !== null) {
            buttons += "<a id=\"popup_easy_cancel\" class=\"button_custom\" type=\"button\">" + window.text.abort + "</a>";
            
            clickCancel = function() {
                callbackCancel();
            };
        }
        
        if ($("#popup_easy").length > 0) {
            $("#popup_easy").find(".modal-title").html(title);
            $("#popup_easy").find(".modal-body").html(message);
            $("#popup_easy").find(".modal-footer").html(buttons);
        }
        
        if (loader === true) {
            $("#popup_easy").find(".modal-header").hide();
            $("#popup_easy").find(".modal-footer").hide();
        }
        else {
            $("#popup_easy").find(".modal-header").show();
            $("#popup_easy").find(".modal-footer").show();
        }
        
        $("#popup_easy_ok").on("click", "", clickOk);
        $("#popup_easy_cancel").on("click", "", clickCancel);
        
        flashBag.hide();
        
        $("#popup_easy").modal("show");
    };
    
    self.close = function() {
        $("#popup_easy").modal("hide");
    };
    
    self.recursive = function(title, obj, key) {
        self.create(
            title,
            obj[key],
            function(){
                self.close();

                if (key + 1 < obj.length)
                    self.recursive(title, obj, key + 1);
            },
            null
        );
    };
    
    // Functions private
}