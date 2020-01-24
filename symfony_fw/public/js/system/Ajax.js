/* global helper, loader, flashBag */

var ajax = new Ajax();

function Ajax() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
    };
    
    self.send = function(loaderEnabled, url, method, data, dataType, cache, processData, contentType, callbackBefore, callbackSuccess, callbackError, callbackComplete) {
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
            beforeSend: function() {
                if (callbackBefore !== null)
                    callbackBefore();
            },
            success: function(xhr) {
                if (xhr.userInform !== undefined && xhr.userInform !== "") {
                    window.session.userInform = xhr.userInform;
                    
                    self.reply(xhr.userInform, "");
                    
                    if (loaderEnabled === true)
                        loader.hide();
                    
                    return;
                }
                
                if (callbackSuccess !== null)
                    callbackSuccess(xhr);
                
                if (loaderEnabled === true)
                    loader.hide();
            },
            error: function(xhr, status) {
                if (loaderEnabled === true)
                    loader.hide();
                
                if (xhr.status === 408 || status === "timeout")
                    self.send(loaderEnabled, url, method, data, dataType, cache, processData, contentType, callbackBefore, callbackSuccess, callbackError, callbackComplete);
                else {
                    if (callbackError !== null)
                        callbackError(xhr);
                }
            },
            complete: function() {
                if (callbackComplete !== null)
                    callbackComplete();
            }
        });
    };
    
    self.reply = function(xhr, tagError) {
        helper.linkPreventDefault();
        
        var reply = "";
        
        if ($(tagError).length > 0) {
            $(tagError).find("*[required='required']").parent().removeClass("mdc-text-field--invalid mdc-text-field--focused");
            $(tagError).find("*[required='required']").parents(".form_row").find(".mdc-text-field-helper-text").text("");
        }
        
        if ($.isEmptyObject(xhr.response) === true)
            reply = window.text.index_8;
        
        if (xhr.response === undefined)
            reply = xhr;
        else {
            if (xhr.response.messages !== undefined) {
                if (xhr.response.messages.error !== undefined)
                    reply = xhr.response.messages.error;
                else if (xhr.response.messages.info !== undefined)
                    reply = xhr.response.messages.info;
                else if (xhr.response.messages.success !== undefined)
                    reply = xhr.response.messages.success;
            }

            if (xhr.response.errors !== undefined && typeof(xhr.response.errors) !== "string") {
                var errors = xhr.response.errors;

                $.each(errors, function(key, value) {
                    if (typeof(value[0]) === "string" && $.isEmptyObject(value) === false && key !== "_token" && key !== "token") {
                        var input = null;

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
        
        if (reply !== "")
            flashBag.show(reply);
    };
    
    // Functions private
}