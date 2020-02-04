"use strict";

/* global materialDesign */

const popupEasy = new PopupEasy();

function PopupEasy() {
    // Vars
    const self = this;
    
    let dialogMdc;
    
    // Properties
    
    // Functions public
    self.init = function() {
        dialogMdc = null;
    };
    
    self.create = function(title, message, callbackOk, callbackCancel) {
        $(".mdc-dialog").find(".mdc-dialog__header__title").html(title);
        $(".mdc-dialog").find(".mdc-dialog__body").html(message);
        $(".mdc-dialog").find(".mdc-dialog__footer__button--accept").text(window.text.index_6);
        $(".mdc-dialog").find(".mdc-dialog__footer__button--cancel").text(window.text.index_7);
        
        materialDesign.refresh();
        
        let clickOk = null;
        let clickCancel = null;
        
        if (callbackOk !== undefined) {
            clickOk = function() {
                callbackOk();
            };
            
            $(".mdc-dialog").find(".mdc-dialog__footer__button--accept").off("click").on("click", "", clickOk);
        }
        
        if (callbackCancel !== undefined) {
            clickCancel = function() {
                callbackCancel();
            };
            
            $(".mdc-dialog").find(".mdc-dialog__footer__button--cancel").off("click").on("click", "", clickCancel);
        }
        
        dialogMdc = materialDesign.getDialogMdc();
        dialogMdc.show();
    };
    
    self.close = function() {
        dialogMdc.close();
    };
    
    self.recursive = function(title, elements, key) {
        self.create(
            title,
            elements[key],
            function() {
                if (key + 1 < elements.length)
                    self.recursive(title, elements, key + 1);
            }
        );
    };
    
    // Functions private
}