"use strict";

/* global helper */

class WidgetSearch {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        $(".widget_search").find(".button_open").on("click", "", (event) => {
            if ($(event.target).hasClass("animate") === false) {
                $(event.target).addClass("animate");
                
                $(".widget_search").find(".button_close").show();
                $(".widget_search").find("input[name='form_search[words]']").show();
                
                $(".mdc-top-app-bar__section--align-start").hide();
                $(".menu_root_container").hide();
            }
        });

        $(".widget_search").find(".button_close").on("click", "", (event) => {
            if ($(".widget_search").find(".button_open").hasClass("animate") === true) {
                $(event.target).hide();
                
                $(".widget_search").find(".button_open").removeClass("animate");
                $(".widget_search").find("input[name='form_search[words]']").val("");
                $(".widget_search").find("input[name='form_search[words]']").hide();
                
                $(".mdc-top-app-bar__section--align-start").css("display", "inline-flex");
                $(".menu_root_container").show();
            }
        });
    }
    
    changeView = () => {
        if (helper.checkWidthType() === "desktop")
            $(".mdc-top-app-bar__section--align-start").css("display", "inline-flex");
        else {
            if ($(".widget_search").find(".button_open").hasClass("animate") === true)
                $(".mdc-top-app-bar__section--align-start").hide();
        }
    }

    // Functions private
}