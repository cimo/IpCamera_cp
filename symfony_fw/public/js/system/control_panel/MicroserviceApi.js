"use strict";

/* global ajax */

const controlPanelMicroserviceApi = new ControlPanelMicroserviceApi();

function ControlPanelMicroserviceApi() {
    // Vars
    let self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
    };
    
    self.action = function() {
        $("#form_cp_microservice_api_create").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                new FormData(this),
                "json",
                false,
                false,
                false,
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $("#form_cp_microservice_api_profile").on("submit", "", function(event) {
            event.preventDefault();

            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                new FormData(this),
                "json",
                false,
                false,
                false,
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(".form_cp_api_select").find("select").on("change", "", function() {
            $("#cp_api_select_result").html("");
        });
    };
    
    // Function private
}