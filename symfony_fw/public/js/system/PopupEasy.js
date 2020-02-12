"use strict";

/* global materialDesign */

class PopupEasy {
    // Properties
    
    // Functions public
    constructor() {
        this.dialogMdc = null;
    }
    
    create = (title, message, callbackOk, callbackCancel) => {
        $(".mdc-dialog").find(".mdc-dialog__header__title").html(title);
        $(".mdc-dialog").find(".mdc-dialog__body").html(message);
        $(".mdc-dialog").find(".mdc-dialog__footer__button--accept").text(window.text.index_6);
        $(".mdc-dialog").find(".mdc-dialog__footer__button--cancel").text(window.text.index_7);
        
        materialDesign.refresh();
        
        let clickOk = null;
        let clickCancel = null;
        
        if (callbackOk !== undefined) {
            clickOk = () => {
                callbackOk();
            };
            
            $(".mdc-dialog").find(".mdc-dialog__footer__button--accept").off("click").on("click", "", clickOk);
        }
        
        if (callbackCancel !== undefined) {
            clickCancel = () => {
                callbackCancel();
            };
            
            $(".mdc-dialog").find(".mdc-dialog__footer__button--cancel").off("click").on("click", "", clickCancel);
        }
        
        this.dialogMdc = materialDesign.getDialogMdc;
        this.dialogMdc.show();
    }
    
    close = () => {
        this.dialogMdc.close();
    }
    
    recursive = (title, elements, key) => {
        this.create(
            title,
            elements[key],
            () => {
                if (key + 1 < elements.length)
                    this.recursive(title, elements, key + 1);
            }
        );
    }
    
    // Functions private
}