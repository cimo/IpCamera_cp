/* global ajax, helper, popupEasy, materialDesign */

var controlPanelSetting = new ControlPanelSetting();

function ControlPanelSetting() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
    };
    
    self.action = function() {
        languageManage();
        
        helper.wordTag("#setting_roleUserId", "#form_setting_roleUserId");
        
        $("#form_setting_payPalCurrencyCode").on("keyup", "", function() {
            $(this).val($(this).val().toUpperCase());
        });
        
        $("#form_cp_setting_save").on("submit", "", function(event) {
            event.preventDefault();
            
            var propNameLanguageManageCode = $("#form_setting_languageManageCode").prop("name");
            $("#form_setting_languageManageCode").removeAttr("name");
            var propNameLanguageManageDate = $("#form_setting_languageManageDate").prop("name");
            $("#form_setting_languageManageDate").removeAttr("name");
            var propNameLanguageManageActive = $("#form_setting_languageManageActive").prop("name");
            $("#form_setting_languageManageActive").removeAttr("name");
            
            $("#setting_language_manage_delete").removeClass("button_icon_inline");
            $("#setting_language_manage_close").click();
            
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
                    
                    $("#form_setting_languageManageCode").prop("name", propNameLanguageManageCode);
                    $("#form_setting_languageManageDate").prop("name", propNameLanguageManageDate);
                    $("#form_setting_languageManageActive").prop("name", propNameLanguageManageActive);
                },
                null,
                null
            );
        });
    };
    
    // Function private
    function languageManage() {
        var index = $("#form_setting_language").prop("selectedIndex");
        var code = $("#form_setting_language").val();
        var eventAjax = "";
        
        if (code === window.setting.language)
            $("#setting_language_manage_delete").hide();
        else
            $("#setting_language_manage_delete").show();
        
        $("#form_setting_language").on("change", "", function() {
            index = $(this).prop("selectedIndex");
            code = $(this).val();
            
            if (code === window.setting.language)
                $("#setting_language_manage_delete").hide();
            else
                $("#setting_language_manage_delete").show();
            
            $("#setting_language_manage_close").click();
        });
        
        $("#setting_language_manage_modify").on("click", "", function() {
            eventAjax = "modifyLanguage";
            
            $("#setting_language_manage_container").show();
            
            var textSplit = $("#form_setting_language").find(":selected").text().split("|");
            var activeLabel = textSplit[2].trim() === window.textSetting.label_3 ? 1 : 0;
            
            $("#form_setting_languageManageCode").prop("disabled", true);
            $("#form_setting_languageManageCode").val(code);
            $("#form_setting_languageManageDate").val(textSplit[1].trim());
            $("#form_setting_languageManageActive").val(activeLabel);
            
            if (code === window.setting.language)
                $("#form_setting_languageManageActive").parents(".form_row").hide();
            else
                $("#form_setting_languageManageActive").parents(".form_row").show();
            
            materialDesign.refresh();
        });
        
        $("#setting_language_manage_create").on("click", "", function() {
            eventAjax = "createLanguage";
            
            $("#setting_language_manage_container").show();
            
            $("#form_setting_languageManageCode").prop("disabled", false);
            $("#form_setting_languageManageCode").val("");
            $("#form_setting_languageManageDate").val("");
            $("#form_setting_languageManageActive").val("");
        });
        
        $("#setting_language_manage_confirm").on("click", "", function() {
            var code = $("#form_setting_languageManageCode").val();
            var date = $("#form_setting_languageManageDate").val();
            var active = $("#form_setting_languageManageActive").val();
            
            var currentCode = $("#language_text_container").find(".mdc-list-item[aria-disabled='true'] img").prop("class");
            
            if (code === currentCode) {
                popupEasy.create(
                    window.text.index_5,
                    window.textSetting.label_1,
                    function() {
                        confirm(eventAjax, code, date, active);
                    }
                );
            }
            else
                confirm(eventAjax, code, date, active);
        });
        
        $("#setting_language_manage_delete").on("click", "", function() {
            popupEasy.create(
                window.text.index_5,
                window.textSetting.label_2,
                function() {
                    ajax.send(
                        true,
                        window.url.cpSettingLanguageManage,
                        "post",
                        {
                            'event': "deleteLanguage",
                            'code': code,
                            'token': window.session.token
                        },
                        "json",
                        false,
                        true,
                        "application/x-www-form-urlencoded; charset=UTF-8",
                        null,
                        function(xhr) {
                            ajax.reply(xhr, "");
                            
                            if (xhr.response.messages.success !== undefined) {
                                $("#setting_language_manage_close").click();
                                
                                $("#form_setting_language").find("option").eq(index).remove();
                                $("#language_text_container").find(".mdc-list-item").eq(index - 1).remove();
                            }
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $("#setting_language_manage_close").on("click", "", function() {
            eventAjax = "";
            
            $("#form_setting_languageManageCode").val("");
            $("#form_setting_languageManageDate").val("");
            $("#form_setting_languageManageActive").val("");
            
            $("#setting_language_manage_container").hide();
        });
    }
    
    function confirm(eventAjax, code, date, active) {
        ajax.send(
            true,
            window.url.cpSettingLanguageManage,
            "post",
            {
                'event': eventAjax,
                'code': code,
                'date': date,
                'active': active,
                'token': window.session.token
            },
            "json",
            false,
            true,
            "application/x-www-form-urlencoded; charset=UTF-8",
            null,
            function(xhr) {
                ajax.reply(xhr, "");
                
                if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined)
                    window.location.href = xhr.response.values.url;
                else if (xhr.response.messages.success !== undefined) {
                    if ($("#form_setting_language").find("option:contains(" + code + ")").length === 0) {
                        $("#form_setting_language").append("<option value=\"" + code + "\">" + code + " | " + date + " | " + active + "</option>");

                        if (active === "1") {
                            $("#language_text_container").find(".mdc-menu__items.mdc-list").append(
                                "<li class=\"mdc-list-item\" role=\"menuitem\">\n\
                                    <span>\n\
                                        <img class=\"" + code + "\" src=\"" + window.url.root + "/images/templates/" + window.setting.template + "/lang/" + code + ".png\" alt=\"" + code + ".png\"/>\n\
                                    " + code + "\n\
                                    </span>\n\
                                </li>"
                            );
                        }
                    }
                    else {
                        var activeLabel = active === "1" ? window.textSetting.label_3 : window.textSetting.label_4;

                        $("#form_setting_language").find("option:contains(" + code + ")").text(code + " | " + date + " | " + activeLabel);

                        var element = null;

                        var elements = $("#language_text_container").find(".mdc-menu__items.mdc-list .mdc-list-item");

                        $.each(elements, function(key, value) {
                            if ($(value).find("img").hasClass(code) === true) {
                                element = $(value);

                                return false;
                            }
                        });

                        if (active === "1") {
                            if (element === null) {
                                $("#language_text_container").find(".mdc-menu__items.mdc-list").append(
                                    "<li class=\"mdc-list-item\" role=\"menuitem\">\n\
                                        <span>\n\
                                            <img class=\"" + code + "\" src=\"" + window.url.root + "/images/templates/" + window.setting.template + "/lang/" + code + ".png\" alt=\"" + code + ".png\"/>\n\
                                            " + code + "\n\
                                        </span>\n\
                                    </li>"
                                );
                            }
                        }
                        else if (active === "0") {
                            if (element !== null)
                                element.remove();
                        }
                    }

                    $("#setting_language_manage_close").click();
                }
            },
            null,
            null
        );
    }
}