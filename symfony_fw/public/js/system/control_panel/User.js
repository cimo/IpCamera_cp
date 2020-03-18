"use strict";

/* global helper, ajax, materialDesign, popupEasy, widgetDatePicker */

class ControlPanelUser {
    // Properties
    
    // Functions public
    constructor() {
        this.selectSended = false;
        this.selectId = -1;
    }
    
    action = () => {
        this._selectDesktop();
        
        this._selectMobile();
        
        helper.wordTag("#user_roleUserId", "#form_user_roleUserId");
        
        $("#form_cp_user_create").on("submit", "", (event) => {
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
                    ajax.reply(xhr, `#${event.target.id}`);
                },
                null,
                null
            );
        });
    }
    
    changeView = () => {
        if (helper.checkWidthType() === "mobile") {
            if (this.selectSended === true) {
                this.selectId = $("#cp_user_select_mobile").find("select option:selected").val();

                this.selectSended = false;
            }

            if (this.selectId >= 0) {
                $("#cp_user_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                let ids = $("#cp_user_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(ids, (key, value) => {
                    if ($.trim($(value).text()) === String(this.selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (this.selectSended === true) {
                this.selectId = $.trim($("#cp_user_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());

                this.selectSended = false;
            }

            if (this.selectId > 0)
                $("#cp_user_select_mobile").find(`select option[value="${this.selectId}"]`).prop("selected", true);
        }
    }
    
    // Function private
    _selectDesktop = () => {
        let tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonStatus = "show";
        tableAndPagination.create(window.url.cpUserSelect, "#cp_user_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_user_select_result_desktop .refresh", (event) => {
            ajax.send(
                true,
                window.url.cpUserSelect,
                "post",
                {
                    'event': "refresh",
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                (xhr) => {
                    ajax.reply(xhr, "");
                    
                    tableAndPagination.populate(xhr);
                    
                    $("#cp_user_select_result").html("");
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_user_select_result_desktop .delete_all", (event) => {
            popupEasy.show(
                window.text.index_5,
                window.textUser.label_2,
                () => {
                    ajax.send(
                        true,
                        window.url.cpUserDelete,
                        "post",
                        {
                            'event': "deleteAll",
                            'token': window.session.token
                        },
                        "json",
                        false,
                        true,
                        "application/x-www-form-urlencoded; charset=UTF-8",
                        null,
                        (xhr) => {
                            ajax.reply(xhr, "");
                            
                            let ids = $("#cp_user_select_result_desktop").find("table .id_column");
                            
                            $.each(ids, (key, value) => {
                                $(value).parents("tr").remove();
                            });
                            
                            $("#cp_user_select_result").html("");
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_user_select_result_desktop .cp_user_delete", (event) => {
            let id = $.trim($(event.currentTarget).parents("tr").find(".id_column").text());
            
            this._deleteElement(id);
        });
        
        $(document).on("click", "#cp_user_select_button_desktop", (event) => {
            let id = $.trim($(event.currentTarget).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                window.url.cpUserSelect,
                "post",
                {
                    'event': "result",
                    'id': id,
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                () => {
                    $("#cp_user_select_result").html("");
                },
                (xhr) => {
                    ajax.reply(xhr, `#${event.currentTarget.id}`);
                    
                    this._profile(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", ".checkbox_column input[type='checkbox']", (event) => {
            $("#cp_user_select_result").html("");
        });
    }
    
    _selectMobile = () => {
        $(document).on("submit", "#form_cp_user_select_mobile", (event) => {
            event.preventDefault();
            
            ajax.send(
                true,
                $(event.currentTarget).prop("action"),
                $(event.currentTarget).prop("method"),
                $(event.currentTarget).serialize(),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                () => {
                    $("#cp_user_select_result").html("");
                },
                (xhr) => {
                    ajax.reply(xhr, `#${event.currentTarget.id}`);
                    
                    this._profile(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("change", "#form_user_select_id", (event) => {
            $("#cp_user_select_result").html("");
        });
    }
    
    _profile = (xhr) => {
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            this.selectSended = true;
            
            $("#cp_user_select_result").html(xhr.response.render);

            helper.wordTag("#user_roleUserId", "#form_user_roleUserId");
            
            widgetDatePicker.setInputFill = ".widget_datePicker_input";
            widgetDatePicker.create();
            
            materialDesign.refresh();

            $("#form_cp_user_profile").on("submit", "", (event) => {
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
                        ajax.reply(xhr, `#${event.target.id}`);
                        
                        if (xhr.response.messages.success !== undefined) {
                            $("#cp_user_select_result").html("");
                            
                            $("#cp_user_select_result_desktop").find(".refresh").click();
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#attemptLogin_reset").find("button").on("click", "", (event) => {
                event.preventDefault();

                ajax.send(
                    true,
                    window.url.cpUserAttemptLoginReset,
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
                        
                        if (xhr.response.messages.success !== undefined)
                            $(".attemptLogin_reset_text").text(0);
                    },
                    null,
                    null
                );
            });
            
            $("#cp_user_delete").on("click", "", (event) => {
               this._deleteElement();
            });
        }
    }
    
    _deleteElement = (id) => {
        let idValue = id === undefined ? null : id;
        
        popupEasy.show(
            window.text.index_5,
            window.textUser.label_1,
            () => {
                ajax.send(
                    true,
                    window.url.cpUserDelete,
                    "post",
                    {
                        'event': "delete",
                        'id': idValue,
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
                            let ids = $("#cp_user_select_result_desktop").find("table .id_column");
                            
                            $.each(ids, (key, value) => {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });

                            $("#form_user_select_id").find(`option[value="${xhr.response.values.id}"]`).remove();

                            $("#cp_user_select_result").html("");
                            
                            $("#cp_user_select_result_desktop").find(".refresh").click();
                        }
                    },
                    null,
                    null
                );
            }
        );
    }
}