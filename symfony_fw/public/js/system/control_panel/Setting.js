"use strict";

/* global ajax, helper, materialDesign, popupEasy */

class ControlPanelSetting {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        this._languageManage();
        
        helper.wordTag("#setting_roleUserId", "#form_setting_roleUserId");
        
        $("#form_setting_payPalCurrencyCode").on("keyup", "", (event) => {
            $(event.target).val($(event.target).val().toUpperCase());
        });
        
        $("#form_cp_setting_save").on("submit", "", (event) => {
            event.preventDefault();
            
            let propNameLanguageManageCode = $("#form_setting_languageManageCode").prop("name");
            $("#form_setting_languageManageCode").removeAttr("name");
            let propNameLanguageManageDate = $("#form_setting_languageManageDate").prop("name");
            $("#form_setting_languageManageDate").removeAttr("name");
            let propNameLanguageManageActive = $("#form_setting_languageManageActive").prop("name");
            $("#form_setting_languageManageActive").removeAttr("name");
            
            $("#setting_language_manage_delete").removeClass("button_icon_inline");
            $("#setting_language_manage_close").click();
            
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
    _languageManage = () => {
        let index = $("#form_setting_language").prop("selectedIndex");
        let code = $("#form_setting_language").val();
        let eventAjax = "";
        
        if (code === window.setting.language)
            $("#setting_language_manage_delete").hide();
        else
            $("#setting_language_manage_delete").show();
        
        $("#form_setting_language").on("change", "", (event) => {
            index = $(event.target).prop("selectedIndex");
            code = $(event.target).val();
            
            if (code === window.setting.language)
                $("#setting_language_manage_delete").hide();
            else
                $("#setting_language_manage_delete").show();
            
            $("#setting_language_manage_close").click();
        });
        
        $("#setting_language_manage_modify").on("click", "", (event) => {
            eventAjax = "modifyLanguage";
            
            $("#setting_language_manage_container").show();
            
            let textSplit = $("#form_setting_language").find(":selected").text().split("|");
            let activeLabel = textSplit[2].trim() === window.textSetting.label_3 ? 1 : 0;
            
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
        
        $("#setting_language_manage_create").on("click", "", (event) => {
            eventAjax = "createLanguage";
            
            $("#setting_language_manage_container").show();
            
            $("#form_setting_languageManageCode").prop("disabled", false);
            $("#form_setting_languageManageCode").val("");
            $("#form_setting_languageManageDate").val("");
            $("#form_setting_languageManageActive").val("");
        });
        
        $("#setting_language_manage_confirm").on("click", "", (event) => {
            let code = $("#form_setting_languageManageCode").val();
            let date = $("#form_setting_languageManageDate").val();
            let active = $("#form_setting_languageManageActive").val();
            
            let currentCode = $("#language_text_container").find(".mdc-list-item[aria-disabled='true'] img").prop("class");
            
            if (code === currentCode) {
                popupEasy.show(
                    window.text.index_5,
                    window.textSetting.label_1,
                    () => {
                        this.confirm(eventAjax, code, date, active);
                    }
                );
            }
            else
                this.confirm(eventAjax, code, date, active);
        });
        
        $("#setting_language_manage_delete").on("click", "", (event) => {
            popupEasy.show(
                window.text.index_5,
                window.textSetting.label_2,
                () => {
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
                        (xhr) => {
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
        
        $("#setting_language_manage_close").on("click", "", (event) => {
            eventAjax = "";
            
            $("#form_setting_languageManageCode").val("");
            $("#form_setting_languageManageDate").val("");
            $("#form_setting_languageManageActive").val("");
            
            $("#setting_language_manage_container").hide();
        });
    }
    
    confirm = (eventAjax, code, date, active) => {
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
            (xhr) => {
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
                        let activeLabel = active === "1" ? window.textSetting.label_3 : window.textSetting.label_4;

                        $("#form_setting_language").find("option:contains(" + code + ")").text(code + " | " + date + " | " + activeLabel);

                        let element = null;

                        let elements = $("#language_text_container").find(".mdc-menu__items.mdc-list .mdc-list-item");

                        $.each(elements, (key, value) => {
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