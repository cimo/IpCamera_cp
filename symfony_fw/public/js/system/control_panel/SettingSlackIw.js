"use strict";

/* global ajax, popupEasy, materialDesign */

class ControlPanelSettingSlackIw {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        $("#form_cp_setting_slack_iw_render").on("submit", "", (event) => {
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
                    ajax.reply(xhr, `#${event.currentTarget.id}`);
                    
                    if (xhr.response.values.wordTagListHtml !== undefined)
                        $("#form_cp_setting_slack_iw_render").find(".wordTag_container").html(xhr.response.values.wordTagListHtml);
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_slack_iw_render").on("click", ".button_reset", (event) => {
            ajax.send(
                true,
                window.url.cpSettingSlackIwReset,
                "post",
                {
                    'event': "reset",
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
                        this.resetField();

                        materialDesign.refresh();
                    }
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_slack_iw_render .wordTag_container").on("click", ".edit", (event) => {
            if ($(event.currentTarget).hasClass("delete") === true)
                return;
            
            let id = $.trim($(event.currentTarget).find(".mdc-chip__text").attr("data-id"));
            
            ajax.send(
                true,
                window.url.cpSettingSlackIwRender,
                "post",
                {
                    'event': "profile",
                    'id': id,
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                (xhr) => {
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.values.wordTagListHtml !== undefined) {
                        $("#form_settingSlackIw_name").val(xhr.response.values.entity[0]);
                        $("#form_settingSlackIw_hook").val(xhr.response.values.entity[1]);
                        $("#form_settingSlackIw_channel").val(xhr.response.values.entity[2]);
                        $("#form_settingSlackIw_active").val(xhr.response.values.entity[3] === true ? 1 : 0);
                        
                        $("#form_cp_setting_slack_iw_render").find(".wordTag_container").html(xhr.response.values.wordTagListHtml);
                        
                        materialDesign.refresh();
                    }
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_slack_iw_render .wordTag_container").on("click", ".delete", (event) => {
            if ($(event.currentTarget).hasClass("edit") === true)
                return;
            
            let id = $.trim($(event.currentTarget).parent().find(".mdc-chip__text").attr("data-id"));
            
            popupEasy.create(
                window.text.index_5,
                window.textSettingSlackIw.label_1,
                () => {
                    ajax.send(
                        true,
                        window.url.cpSettingSlackIwDelete,
                        "post",
                        {
                            'event': "delete",
                            'id': id,
                            'token': window.session.token
                        },
                        "json",
                        false,
                        true,
                        "application/x-www-form-urlencoded; charset=UTF-8",
                        null,
                        (xhr) => {
                            ajax.reply(xhr, "");

                            if (xhr.response.values.wordTagListHtml !== undefined) {
                                $("#form_cp_setting_slack_iw_render").find(".wordTag_container").html(xhr.response.values.wordTagListHtml);
                                
                                this.resetField();
                                
                                materialDesign.refresh();
                            }
                        },
                        null,
                        null
                    );
                }
            );
        });
    };
    
    // Function private
    resetField = () => {
        $("#form_settingSlackIw_name").val("");
        $("#form_settingSlackIw_name").parent().find("label").removeClass("mdc-floating-label--float-above");

        $("#form_settingSlackIw_hook").val("");
        $("#form_settingSlackIw_hook").parent().find("label").removeClass("mdc-floating-label--float-above");

        $("#form_settingSlackIw_channel").val("");
        $("#form_settingSlackIw_channel").parent().find("label").removeClass("mdc-floating-label--float-above");
        
        $("#form_settingSlackIw_active").val("");
        $("#form_settingSlackIw_active").parent().find("label").removeClass("mdc-floating-label--float-above");
    }
}