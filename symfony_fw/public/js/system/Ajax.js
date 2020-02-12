"use strict";

/* global helper, loader, flashBag */

class Ajax {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    send = (loaderEnabled, url, method, data, dataType, cache, processData, contentType, callbackBefore, callbackSuccess, callbackError, callbackComplete) => {
        if (loaderEnabled === true)
            loader.show();
        
        $.ajax({
            'url': url,
            'method': method,
            'data': data,
            'dataType': dataType,
            'cache': cache,
            'processData': processData,
            'contentType': contentType,
            beforeSend: () => {
                if (callbackBefore !== null)
                    callbackBefore();
            },
            success: (xhr) => {
                if (xhr.userInform !== undefined && xhr.userInform !== "") {
                    window.session.userInform = xhr.userInform;
                    
                    this.reply(xhr.userInform, "");
                    
                    if (loaderEnabled === true)
                        loader.hide();
                    
                    return;
                }
                
                if (callbackSuccess !== null)
                    callbackSuccess(xhr);
                
                if (loaderEnabled === true)
                    loader.hide();
            },
            error: (xhr, status) => {
                if (loaderEnabled === true)
                    loader.hide();
                
                if (xhr.status === 408 || status === "timeout")
                    this.send(loaderEnabled, url, method, data, dataType, cache, processData, contentType, callbackBefore, callbackSuccess, callbackError, callbackComplete);
                else {
                    if (callbackError !== null)
                        callbackError(xhr);
                }
            },
            complete: () => {
                if (callbackComplete !== null)
                    callbackComplete();
            }
        });
    }
    
    reply = (xhr, tagError) => {
        helper.linkPreventDefault();
        
        let result = "";
        
        if ($(tagError).length > 0) {
            $(tagError).find("*[required='required']").parent().removeClass("mdc-text-field--invalid mdc-text-field--focused");
            $(tagError).find("*[required='required']").parents(".form_row").find(".mdc-text-field-helper-text").text("");
        }
        
        if ($.isEmptyObject(xhr.response) === true)
            result = window.text.index_8;
        
        if (xhr.response === undefined)
            result = xhr;
        else {
            if (xhr.response.messages !== undefined) {
                if (xhr.response.messages.error !== undefined)
                    result = xhr.response.messages.error;
                else if (xhr.response.messages.info !== undefined)
                    result = xhr.response.messages.info;
                else if (xhr.response.messages.success !== undefined)
                    result = xhr.response.messages.success;
            }

            if (xhr.response.errors !== undefined && typeof(xhr.response.errors) !== "string") {
                let errors = xhr.response.errors;

                $.each(errors, (key, value) => {
                    if (typeof(value[0]) === "string" && $.isEmptyObject(value) === false && key !== "_token" && key !== "token") {
                        let input = null;

                        if ($(tagError).length > 0)
                            input = $(tagError).find("*[name*='["+ key + "]']")[0];

                        if (input !== undefined) {
                            $(input).parent().addClass("mdc-text-field--invalid mdc-text-field--focused");
                            $(input).parents(".form_row").find(".mdc-text-field-helper-text").text(value[0]);
                        }
                    }
                });
            }
            
            if (xhr.response.session !== undefined && xhr.response.session.userInform !== undefined)
                window.session.userInform = xhr.response.session.userInform;
        }
        
        if (result !== "")
            flashBag.show(result);
    }
    
    // Functions private
}