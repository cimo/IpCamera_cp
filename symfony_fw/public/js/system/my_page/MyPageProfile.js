"use strict";

/* global helper, ajax */

class MyPageProfile {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        $("#form_myPage_profile").on("submit", "", (event) => {
            event.preventDefault();
            
            ajax.send(
                true,
                $(event.target).prop("action"),
                $(event.target).prop("method"),
                new FormData(event.target),
                "json",
                false,
                false,
                false,
                null,
                (xhr) => {
                    ajax.reply(xhr, `#${event.target.id}`);
                },
                null,
                null
            );
        });

        $("#form_myPage_profile_password").on("submit", "", (event) => {
            event.preventDefault();
            
            ajax.send(
                true,
                $(event.target).prop("action"),
                $(event.target).prop("method"),
                $(event.target).serialize(),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                (xhr) => {
                    ajax.reply(xhr, `#${event.target.id}`);
                },
                null,
                null
            );
        });
        
        $("#form_myPage_profile_credit").on("submit", "", (event) => {
            event.preventDefault();
            
            ajax.send(
                true,
                $(event.target).prop("action"),
                $(event.target).prop("method"),
                $(event.target).serialize(),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                (xhr) => {
                    ajax.reply(xhr, `#${event.target.id}`);
                    
                    if (xhr.response.messages.success !== undefined) {
                        let credit = $("#form_myPage_profile_credit").find("input[name='credit']").val();
                        $("#form_myPage_profile_credit_paypal").find("input[name='quantity']").val(credit);
                        
                        $("#form_myPage_profile_credit_paypal").submit();
                    }
                },
                null,
                null
            );
        });
    }
    
    // Function private
}