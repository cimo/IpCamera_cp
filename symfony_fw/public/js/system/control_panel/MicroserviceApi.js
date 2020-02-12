"use strict";

/* global ajax */

class ControlPanelMicroserviceApi {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        $("#form_cp_microservice_api_create").on("submit", "", (event) => {
            event.preventDefault();
            
            ajax.send(
                true,
                $(event.target).prop("action"),
                $(event.target).prop("method"),
                new FormData(this),
                "json",
                false,
                false,
                false,
                null,
                (xhr) => {
                    ajax.reply(xhr, `#${event.currentTarget.id}`);
                },
                null,
                null
            );
        });
        
        $("#form_cp_microservice_api_profile").on("submit", "", (event) => {
            event.preventDefault();

            ajax.send(
                true,
                $(event.target).prop("action"),
                $(event.target).prop("method"),
                new FormData(this),
                "json",
                false,
                false,
                false,
                null,
                (xhr) => {
                    ajax.reply(xhr, `#${event.currentTarget.id}`);
                },
                null,
                null
            );
        });
        
        $(".form_cp_api_select").find("select").on("change", "", (event) => {
            $("#cp_api_select_result").html("");
        });
    }
    
    // Function private
}