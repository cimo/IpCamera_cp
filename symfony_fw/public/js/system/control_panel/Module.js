"use strict";

/* global helper, ajax, popupEasy, materialDesign */

class ControlPanelModule {
    // Properties
    
    // Functions public
    constructor() {
        this.selectSended = false;
        this.selectId = -1;
    }
    
    action = () => {
        this.selectDesktop();
        
        this.selectMobile();
        
        this.rankInColumn();
        
        $("#form_cp_module_create").on("submit", "", (event) => {
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
                },
                null,
                null
            );
        });
    }
    
    changeView = () => {
        if (helper.checkWidthType() === "mobile") {
            if (this.selectSended === true) {
                this.selectId = $("#cp_module_select_mobile").find("select option:selected").val();

                this.selectSended = false;
            }

            if (this.selectId >= 0) {
                $("#cp_module_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                let id = $("#cp_module_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(id, (key, value) => {
                    if ($.trim($(value).text()) === String(this.selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (this.selectSended === true) {
                this.selectId = $.trim($("#cp_module_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());

                this.selectSended = false;
            }

            if (this.selectId >= 0)
                $("#cp_module_select_mobile").find(`select option[value="${this.selectId}"]`).prop("selected", true);
        }
        
        this.rankInColumn();
    }
    
    // Function private
    selectDesktop = () => {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus = "show";
        tableAndPagination.create(window.url.cpModuleSelect, "#cp_module_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_module_select_result_desktop .refresh", (event) => {
            ajax.send(
                true,
                window.url.cpModuleSelect,
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
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_module_select_result_desktop .delete_all", (event) => {
            popupEasy.create(
                window.text.index_5,
                window.textModule.label_2,
                () => {
                    ajax.send(
                        true,
                        window.url.cpModuleDelete,
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

                            $.each($("#cp_module_select_result_desktop").find("table .id_column"), (key, value) => {
                                let id = $.trim($(value).parents("tr").find(".id_column").text());
                                
                                if (id > 2)
                                    $(value).parents("tr").remove();
                            });
                            
                            $("#cp_module_select_result").html("");
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_module_select_result_desktop .cp_module_delete", (event) => {
            let id = $.trim($(event.currentTarget).parents("tr").find(".id_column").text());
            
            this.deleteElement(id);
        });
        
        $(document).on("click", "#cp_module_select_button_desktop", (event) => {
            let id = $.trim($(event.currentTarget).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                window.url.cpModuleProfile,
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
                    $("#cp_module_select_result").html("");
                },
                (xhr) => {
                    this.profile(xhr, `#${event.currentTarget.id}`);
                },
                null,
                null
            );
        });
        
        $(document).on("click", ".checkbox_column input[type='checkbox']", (event) => {
            $("#cp_module_select_result").html("");
        });
    }
    
    selectMobile = () => {
        $(document).on("submit", "#form_cp_module_select_mobile", (event) => {
            event.preventDefault();
            
            ajax.send(
                true,
                $(event.currentTarget).prop("action"),
                $(event.currentTarget).prop("method"),
                helper.serializeJson($(event.currentTarget)),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                () => {
                    $("#cp_module_select_result").html("");
                },
                (xhr) => {
                    this.profile(xhr, `#${event.currentTarget.id}`);
                },
                null,
                null
            );
        });
        
        $(document).on("change", "#form_module_select_id", (event) => {
            $("#cp_module_select_result").html("");
        });
    }
    
    profile = (xhr, tag) => {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            this.selectSended = true;
            
            $("#cp_module_select_result").html(xhr.response.render);
            
            this.rankInColumn();
            
            materialDesign.refresh();

            $("#form_cp_module_profile").on("submit", "", (event) => {
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
                        
                        if (xhr.response.messages.success !== undefined) {
                            $("#cp_module_select_result").html("");
                            
                            $("#cp_module_select_result_desktop .refresh").click();
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#cp_module_delete").on("click", "", (event) => {
               this.deleteElement(null);
            });
        }
    }
    
    rankInColumn = () => {
        helper.sortableElement("#module_rankColumnSort", "#form_module_rankColumnSort");
        
        $("#form_module_position").off("change").on("change", "", (event) => {
            ajax.send(
                true,
                window.url.cpModuleProfileSort,
                "post",
                {
                    'event': "refresh",
                    'position': $(event.target).val(),
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                (xhr) => {
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.values.moduleSortListHtml !== undefined) {
                        $("#module_rankColumnSort").find(".sort_result").html(xhr.response.values.moduleSortListHtml);

                        helper.sortableElement("#module_rankColumnSort", "#form_module_rankColumnSort");
                    }
                },
                null,
                null
            );
        });
    }
    
    deleteElement = (id) => {
        popupEasy.create(
            window.text.index_5,
            window.textModule.label_1,
            () => {
                ajax.send(
                    true,
                    window.url.cpModuleDelete,
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
                        
                        if (xhr.response.messages.success !== undefined) {
                            $.each($("#cp_module_select_result_desktop").find("table .id_column"), (key, value) => {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });
                            
                            $("#form_module_select_id").find(`option[value="${xhr.response.values.id}"]`).remove();

                            $("#cp_module_select_result").html("");
                            
                            $("#cp_module_select_result_desktop").find(".refresh").click();
                        }
                    },
                    null,
                    null
                );
            }
        );
    }
}