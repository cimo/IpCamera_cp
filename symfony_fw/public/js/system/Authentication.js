"use strict";

/* global ajax, captcha */

class Authentication {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        $("#form_authentication").on("submit", "", (event) => {
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
                    
                    if (xhr.response.messages !== undefined) {
                        if (xhr.response.values !== undefined && xhr.response.values.captchaReload === true)
                            captcha.image();
                    }
                    else
                        window.location.href = xhr.response.values.url;
                },
                null,
                null
            );
        });
    }
    
    // Function private
}