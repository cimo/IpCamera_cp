/* global utility, loader, flashBag */

var ajax = new Ajax();

function Ajax() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.send = function(url, method, data, loaderEnabled, callbackBefore, callbackSuccess, callbackError, callbackComplete) {
        if (loaderEnabled === true)
            loader.show();
        
        if (window.session.activity === "")
            flashBag.hide();
        
        $.ajax({
            'url': url,
            'method': method,
            'data': data,
            'dataType': "json",
            'cache': false,
            beforeSend: function() {
                if (callbackBefore !== null)
                    callbackBefore();
            },
            success: function(xhr) {
                if (callbackSuccess !== null)
                    callbackSuccess(xhr);
                
                if (loaderEnabled === true)
                    loader.hide();
                
                flashBag.show();
            },
            error: function(xhr, status) {
                if (loaderEnabled === true)
                    loader.hide();
                
                flashBag.hide();
                
                if (xhr.status === 408 || status === "timeout")
                    self.send(url, method, data, callbackBefore, callbackSuccess, callbackError, callbackComplete);
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
    
    self.reply = function(xhr, tag) {
        var reply = "";
        
        if ($(tag).length > 0)
            $(tag).find("*[required='required']").parents(".form-group").removeClass("has-error");

        if ($.isEmptyObject(xhr.response) === true) {
            $("#flashBag").prop({'class': "alert alert-danger"});
            
            reply = "<p>" + window.text.ajaxConnectionError + "</p>";
        }
        
        if (xhr.response === undefined) {
            $("#flashBag").prop({'class': "alert alert-danger"});

            reply = xhr;
        }
        else {
            if (xhr.response.messages !== undefined) {
                if (xhr.response.messages.error !== undefined) {
                    $("#flashBag").prop({'class': "alert alert-danger"});

                    reply = "<p>" + xhr.response.messages.error + "</p>";
                }
                else if (xhr.response.messages.success !== undefined) {
                    $("#flashBag").prop({'class': "alert alert-success"});

                    reply = "<p>" + xhr.response.messages.success + "</p>";
                }
            }

            if (xhr.response.errors !== undefined) {
                $("#flashBag").prop({'class': "alert alert-danger"});

                var list = "<ul>";

                if (typeof(xhr.response.errors) !== "string") {
                    var errors = xhr.response.errors;

                    $.each(errors, function(key, value) {
                        if (typeof(value[0]) === "string" && $.isEmptyObject(value) === false && key !== "token") {
                            var object = $(tag).find("*[name*='"+ key + "']")[0];

                            $(object).parents(".form-group").addClass("has-error");
                            
                            var icon = "";
                            
                            if ($(object).parents(".form-group").find(".input-group-addon").length > 0)
                                icon = $(object).parents(".form-group").find(".input-group-addon").html();
                            
                            list += "<li>" + icon + " <b>" + $(object).parents(".form-group").find("label").html() + "</b>: " + value[0] + "</li>";
                        }
                    });
                }
                else
                    list += "<li>" + xhr.response.errors + "</li>";

                list += "</ul>";

                reply += list;
            }
            
            if (xhr.response.session !== undefined && xhr.response.session.activity !== undefined)
                window.session.activity = xhr.response.session.activity;
        }
        
        if (reply !== "")
            $("#flashBag").find(".content").html(reply);
        
        flashBag.sessionActivity();
        
        utility.linkPreventDefault();
    };
    
    // Functions private
}