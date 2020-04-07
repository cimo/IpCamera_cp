"use strict";

/* global materialDesign */

class PopupEasy {
    // Properties
    set setElement(value) {
        this.dialogMdc = value;
    }
    
    // Functions public
    constructor() {
        this.dialogMdc = null;
    }
    
    show = (title, message, callbackOk, callbackCancel) => {
        $(".mdc-dialog").find(".mdc-dialog__header__title").html(title);
        $(".mdc-dialog").find(".mdc-dialog__body").html(message);
        $(".mdc-dialog").find(".mdc-dialog__footer__button--accept").text(window.text.index_6);
        $(".mdc-dialog").find(".mdc-dialog__footer__button--cancel").text(window.text.index_7);
        
        materialDesign.refresh();

        if (callbackOk !== undefined)
            $(".mdc-dialog").find(".mdc-dialog__footer__button--accept").off("click").on("click", "", callbackOk);
        
        if (callbackCancel !== undefined)
            $(".mdc-dialog").find(".mdc-dialog__footer__button--cancel").off("click").on("click", "", callbackCancel);
        
        this.dialogMdc.show();
    }
    
    close = () => {
        this.dialogMdc.close();
    }
    
    recursive = (title, elements, key) => {
        this.show(
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