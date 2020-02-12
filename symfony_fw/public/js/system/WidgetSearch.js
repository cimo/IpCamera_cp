"use strict";

/* global helper */

class WidgetSearch {
    // Properties
    
    // Functions public
    constructor() {
        this.widgetSearchButtonOpen = null;
        this.widgetSearchButtonClose = null;
        this.widgetSearchButtonInput = null;
        this.topAppBarSectionStart = null;
    }
    
    create = () => {
        this.widgetSearchButtonOpen = $(".widget_search").find(".button_open");
        this.widgetSearchButtonClose = $(".widget_search").find(".button_close");
        this.widgetSearchButtonInput = $(".widget_search").find("input[name='form_search[words]']");
        this.topAppBarSectionStart = $(".mdc-top-app-bar__section--align-start");

        this.widgetSearchButtonOpen.on("click", "", (event) => {
            if ($(event.target).hasClass("animate") === false) {
                $(event.target).addClass("animate");
                
                this.widgetSearchButtonClose.show();
                this.widgetSearchButtonInput.show();
                
                this.topAppBarSectionStart.hide();
                $(".menu_root_container").hide();
            }
        });

        this.widgetSearchButtonClose.on("click", "", (event) => {
            if (this.widgetSearchButtonOpen.hasClass("animate") === true) {
                $(event.target).hide();
                
                this.widgetSearchButtonOpen.removeClass("animate");
                this.widgetSearchButtonInput.val("");
                this.widgetSearchButtonInput.hide();
                
                this.topAppBarSectionStart.css("display", "inline-flex");
                $(".menu_root_container").show();
            }
        });
    }
    
    changeView = () => {
        if (helper.checkWidthType() === "desktop")
            this.topAppBarSectionStart.css("display", "inline-flex");
        else {
            if (this.widgetSearchButtonOpen.hasClass("animate") === true)
                this.topAppBarSectionStart.hide();
        }
    }

    // Functions private
}