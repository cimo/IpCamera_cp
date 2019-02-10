/* global ajax, popupEasy, materialDesign, tableAndPagination */

var controlPanelSettingLinePush = new ControlPanelSettingLinePush();

function ControlPanelSettingLinePush() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.create(window.url.cpSettingLinePushRender, "#cp_setting_line_push_user_result", false);
        tableAndPagination.search();
        tableAndPagination.pagination();
        
        $("#form_cp_setting_line_push_render").on("submit", "", function(event) {
            event.preventDefault();
            
            if ($(".tableAndPagination .mdc-text-field__input").is(":focus") === true)
                return false;
            
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
                    
                    if (xhr.response.values.wordTagListHtml !== undefined)
                        $("#form_cp_setting_line_push_render").find(".wordTag_container").html(xhr.response.values.wordTagListHtml);
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_line_push_render").on("click", ".button_reset", function(event) {
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
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.messages.success !== undefined) {
                        resetField();
                        
                        tableAndPagination.populate(xhr);

                        materialDesign.refresh();
                    }
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_line_push_render .wordTag_container").on("click", ".edit", function(event) {
            if ($(event.target).hasClass("delete") === true)
                return;
            
            var id = $.trim($(this).find(".mdc-chip__text").attr("data-id"));
            
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
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.values.wordTagListHtml !== undefined) {
                        $("#form_settingLinePush_name").val(xhr.response.values.entity[0]);
                        $("#form_settingLinePush_userIdPrimary").val(xhr.response.values.entity[1]);
                        $("#form_settingLinePush_accessToken").val(xhr.response.values.entity[2]);
                        $("#form_settingLinePush_active").val(xhr.response.values.entity[3] === true ? 1 : 0);
                        
                        $("#form_cp_setting_line_push_render").find(".wordTag_container").html(xhr.response.values.wordTagListHtml);
                        
                        tableAndPagination.populate(xhr);
                        
                        materialDesign.refresh();
                    }
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_line_push_render .wordTag_container").on("click", ".delete", function(event) {
            if ($(event.target).hasClass("edit") === true)
                return;
            
            var id = $.trim($(this).parent().find(".mdc-chip__text").attr("data-id"));
            
            popupEasy.create(
                window.text.index_5,
                window.textSettingLinePush.label_1,
                function() {
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
                        function(xhr) {
                            ajax.reply(xhr, "");
                            
                            if (xhr.response.values.wordTagListHtml !== undefined) {
                                $("#form_cp_setting_line_push_render").find(".wordTag_container").html(xhr.response.values.wordTagListHtml);
                                
                                resetField();
                                
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
    function resetField() {
        $("#form_settingLinePush_name").val("");
        $("#form_settingLinePush_name").parent().find("label").removeClass("mdc-floating-label--float-above");

        $("#form_settingLinePush_userIdPrimary").val("");
        $("#form_settingLinePush_userIdPrimary").parent().find("label").removeClass("mdc-floating-label--float-above");

        $("#form_settingLinePush_accessToken").val("");
        $("#form_settingLinePush_accessToken").parent().find("label").removeClass("mdc-floating-label--float-above");
        
        $("#form_settingLinePush_active").val("");
        $("#form_settingLinePush_active").parent().find("label").removeClass("mdc-floating-label--float-above");
    }
}