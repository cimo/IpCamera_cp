"use strict";

/* global helper */

const widgetSearch = new WidgetSearch();

function WidgetSearch() {
    // Vars
    let self = this;
    
    let widgetSearchButtonOpen;
    let widgetSearchButtonClose;
    let widgetSearchButtonInput;
    let topAppBarSectionStart;
    
    // Properties
    
    // Functions public
    self.init = function() {
        widgetSearchButtonOpen = null;
        widgetSearchButtonClose = null;
        widgetSearchButtonInput = null;
        topAppBarSectionStart = null;
    };
    
    self.create = function() {
        widgetSearchButtonOpen = $(".widget_search").find(".button_open");
        widgetSearchButtonClose = $(".widget_search").find(".button_close");
        widgetSearchButtonInput = $(".widget_search").find("input[name='form_search[words]']");
        topAppBarSectionStart = $(".mdc-top-app-bar__section--align-start");

        $(widgetSearchButtonOpen).on("click", "", function(event) {
            let target = event.target;

            if ($(target).hasClass("animate") === false) {
                $(target).addClass("animate");
                $(widgetSearchButtonClose).show();
                $(widgetSearchButtonInput).show();
                
                $(topAppBarSectionStart[0]).hide();
                $(".menu_root_container").hide();
            }
        });

        $(widgetSearchButtonClose).on("click", "", function(event) {
            let target = event.target;

            if ($(widgetSearchButtonOpen).hasClass("animate") === true) {
                $(target).hide();
                $(widgetSearchButtonOpen).removeClass("animate");
                widgetSearchButtonInput.val("");
                $(widgetSearchButtonInput).hide();
                
                $(topAppBarSectionStart[0]).css("display", "inline-flex");
                $(".menu_root_container").show();
            }
        });
    };
    
    self.changeView = function() {
        if (helper.checkWidthType() === "desktop")
            $(topAppBarSectionStart[0]).css("display", "inline-flex");
        else {
            if ($(widgetSearchButtonOpen).hasClass("animate") === true)
                $(topAppBarSectionStart[0]).hide();
        }
    };

    // Functions private
}