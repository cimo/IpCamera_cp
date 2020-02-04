"use strict";

/* global ajax */

const captcha = new Captcha();

function Captcha() {
    // Vars
    const self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
    };
    
    self.action = function() {
        self.image();
        
        $(".captcha").find("img").on("click", "", function(event) {
            self.image();
        });
    };
    
    self.image = function() {
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
            function(xhr) {
                ajax.reply(xhr, "");

                if (xhr.response.captchaImage !== undefined)
                    $(".captcha").find("img").prop("src", "data:image/png;base64," + xhr.response.captchaImage);
            },
            null,
            null
        );
    };
    
    // Functions private
}