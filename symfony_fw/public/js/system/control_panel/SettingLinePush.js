"use strict";

/* global ajax, popupEasy, materialDesign, tableAndPagination */

class ControlPanelSettingLinePush {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.create(window.url.cpSettingLinePushRender, "#cp_setting_line_push_user_result", false);
        tableAndPagination.search();
        tableAndPagination.pagination();
        
        $("#cp_setting_line_push_user_result").find(".tableAndPagination").hide();
        
        $("#form_cp_setting_line_push_render").on("submit", "", (event) => {
            event.preventDefault();
            
            if ($(".tableAndPagination .mdc-text-field__input").is(":focus") === true)
                return false;
            
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
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    if (xhr.response.values.wordTagListHtml !== undefined)
                        $("#form_cp_setting_line_push_render").find(".wordTag_container").html(xhr.response.values.wordTagListHtml);
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_line_push_render").on("click", ".button_reset", (event) => {
            ajax.send(
                true,
                window.url.cpSettingLinePushReset,
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
                        
                        tableAndPagination.populate(xhr);

                        materialDesign.refresh();
                    }
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_line_push_render .wordTag_container").on("click", ".edit", (event) => {
            if ($(event.currentTarget).hasClass("delete") === true)
                return;
            
            let id = $.trim($(event.currentTarget).find(".mdc-chip__text").attr("data-id"));
            
            ajax.send(
                true,
                window.url.cpSettingLinePushRender,
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
                        $("#form_settingLinePush_name").val(xhr.response.values.entity[0]);
                        $("#form_settingLinePush_userIdPrimary").val(xhr.response.values.entity[1]);
                        $("#form_settingLinePush_accessToken").val(xhr.response.values.entity[2]);
                        $("#form_settingLinePush_active").val(xhr.response.values.entity[3] === true ? 1 : 0);
                        
                        $("#cp_setting_line_push_user_result").find(".tableAndPagination").show();
                        
                        $("#form_cp_setting_line_push_render").find(".wordTag_container").html(xhr.response.values.wordTagListHtml);
                        
                        tableAndPagination.populate(xhr);
                        
                        materialDesign.refresh();
                    }
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_line_push_render .wordTag_container").on("click", ".delete", (event) => {
            if ($(event.currentTarget).hasClass("edit") === true)
                return;
            
            let id = $.trim($(event.currentTarget).parent().find(".mdc-chip__text").attr("data-id"));
            
            popupEasy.create(
                window.text.index_5,
                window.textSettingLinePush.label_1,
                () => {
                    ajax.send(
                        true,
                        window.url.cpSettingLinePushDelete,
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
                                $("#form_cp_setting_line_push_render").find(".wordTag_container").html(xhr.response.values.wordTagListHtml);
                                
                                this.resetField();
                                
                                tableAndPagination.populate(xhr);
                                
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
        $("#form_settingLinePush_name").val("");
        $("#form_settingLinePush_name").parent().find("label").removeClass("mdc-floating-label--float-above");

        $("#form_settingLinePush_userIdPrimary").val("");
        $("#form_settingLinePush_userIdPrimary").parent().find("label").removeClass("mdc-floating-label--float-above");

        $("#form_settingLinePush_accessToken").val("");
        $("#form_settingLinePush_accessToken").parent().find("label").removeClass("mdc-floating-label--float-above");
        
        $("#form_settingLinePush_active").val("");
        $("#form_settingLinePush_active").parent().find("label").removeClass("mdc-floating-label--float-above");
        
        $("#cp_setting_line_push_user_result").find(".tableAndPagination").hide();
    }
}