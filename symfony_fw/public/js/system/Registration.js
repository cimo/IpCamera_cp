"use strict";

/* global ajax */

const registration = new Registration();

function Registration() {
    // Vars
    let self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
    };
    
    self.action = function() {
        $("#form_user_registration").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    };
    
    // Function private
}