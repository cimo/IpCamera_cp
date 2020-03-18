"use strict";

/* global ajax */

class Captcha {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        this.actionImage();
        
        $(".captcha").find("img").on("click", "", (event) => {
            this.image();
        });
    }
    
    actionImage = () => {
        ajax.send(
            false,
            window.url.rootRender,
            "post",
            {
                'event': "captchaImage"
            },
            "json",
            false,
            true,
            "application/x-www-form-urlencoded; charset=UTF-8",
            null,
            (xhr) => {
                ajax.reply(xhr, "");

                if (xhr.response.captchaImage !== undefined)
                    $(".captcha").find("img").prop("src", `data:image/png;base64,${xhr.response.captchaImage}`);
            },
            null,
            null
        );
    }
    
    // Functions private
}