/* global utility, loader, flashBag */

var ajax = new Ajax();

function Ajax() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.send = function(loaderEnabled, messageHide, url, method, data, dataType, cache, callbackBefore, callbackSuccess, callbackError, callbackComplete) {
        if (loaderEnabled === true)
            loader.show();
        
        if (messageHide === true && window.session.userActivity === "")
            flashBag.hide();
        
        $.ajax({
            'url': url,
            'method': method,
            'data': data,
            'dataType': dataType,
            'cache': cache,
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
                    self.send(loaderEnabled, messageHide, url, method, data, dataType, cache, callbackBefore, callbackSuccess, callbackError, callbackComplete);
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
        
        if ($("#menu_root_navbar").hasClass("in") === true)
            $("#menu_root_nav_button").click();
        
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
                        if (typeof(value[0]) === "string" && $.isEmptyObject(value) === false && key !== "_token") {
                            var object = null;
                            
                            if ($(tag).length > 0)
                                object = $(tag).find("*[name*='"+ key + "']")[0];
                            
                            if (object !== undefined) {
                                $(object).parents(".form-group").addClass("has-error");

                                var icon = "";
                                var label = " - ";

                                if ($(object).parents(".form-group").find(".input-group-addon").length > 0)
                                    icon = $(object).parents(".form-group").find(".input-group-addon").html();
                                
                                if ($(object).parents(".form-group").find("label").html() !== undefined)
                                    label = $(object).parents(".form-group").find("label").html()
                                else if ($($(object).parents(".form-group").find("*[name*='"+ key + "']")).attr("placeholder") !== undefined)
                                    label = $($(object).parents(".form-group").find("*[name*='"+ key + "']")).attr("placeholder");
                                
                                list += "<li>" + icon + " <b>" + label + "</b>: " + value[0] + "</li>";
                            }
                        }
                    });
                }
                else
                    list += "<li>" + xhr.response.errors + "</li>";

                list += "</ul>";

                reply += list;
            }
            
            if (xhr.response.session !== undefined && xhr.response.session.userActivity !== undefined)
                window.session.userActivity = xhr.response.session.userActivity;
        }
        
        if (reply !== "")
            $("#flashBag").find(".content").html(reply);
        
        flashBag.sessionActivity();
        
        utility.linkPreventDefault();
    };
    
    // Functions private
}