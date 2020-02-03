"use strict";

/* global helper, ajax, popupEasy, materialDesign */

const controlPanelModule = new ControlPanelModule();

function ControlPanelModule() {
    // Vars
    let self = this;
    
    let selectSended;
    let selectId;
    
    // Properties
    
    // Functions public
    self.init = function() {
        selectSended = false;
        selectId = -1;
    };
    
    self.action = function() {
        selectDesktop();
        
        selectMobile();
        
        rankInColumn();
        
        $("#form_cp_module_create").on("submit", "", function(event) {
            event.preventDefault();
            
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
                },
                null,
                null
            );
        });
    };
    
    self.changeView = function() {
        if (helper.checkWidthType() === "mobile") {
            if (selectSended === true) {
                selectId = $("#cp_module_select_mobile").find("select option:selected").val();

                selectSended = false;
            }

            if (selectId >= 0) {
                $("#cp_module_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                let id = $("#cp_module_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(id, function(key, value) {
                    if ($.trim($(value).text()) === String(selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectSended === true) {
                selectId = $.trim($("#cp_module_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());

                selectSended = false;
            }

            if (selectId >= 0)
                $("#cp_module_select_mobile").find("select option[value='" + selectId + "']").prop("selected", true);
        }
        
        rankInColumn();
    };
    
    // Function private
    function selectDesktop() {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.create(window.url.cpModuleSelect, "#cp_module_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_module_select_result_desktop .refresh", function() {
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
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    tableAndPagination.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_module_select_result_desktop .delete_all", function() {
            popupEasy.create(
                window.text.index_5,
                window.textModule.label_2,
                function() {
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
                        function(xhr) {
                            ajax.reply(xhr, "");

                            $.each($("#cp_module_select_result_desktop").find("table .id_column"), function(key, value) {
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
        
        $(document).on("click", "#cp_module_select_result_desktop .cp_module_delete", function() {
            let id = $.trim($(this).parents("tr").find(".id_column").text());
            
            deleteElement(id);
        });
        
        $(document).on("click", "#cp_module_select_button_desktop", function(event) {
            let id = $.trim($(this).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

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
                function() {
                    $("#cp_module_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(document).on("click", ".checkbox_column input[type='checkbox']", function() {
            $("#cp_module_select_result").html("");
        });
    }
    
    function selectMobile() {
        $(document).on("submit", "#form_cp_module_select_mobile", function(event) {
            event.preventDefault();

            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                helper.serializeJson($(this)),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                function() {
                    $("#cp_module_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(document).on("change", "#form_module_select_id", function() {
            $("#cp_module_select_result").html("");
        });
    }
    
    function profile(xhr, tag) {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            selectSended = true;
            
            $("#cp_module_select_result").html(xhr.response.render);
            
            rankInColumn();
            
            materialDesign.refresh();

            $("#form_cp_module_profile").on("submit", "", function(event) {
                event.preventDefault();

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
                        
                        if (xhr.response.messages.success !== undefined) {
                            $("#cp_module_select_result").html("");
                            
                            $("#cp_module_select_result_desktop .refresh").click();
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#cp_module_delete").on("click", "", function() {
               deleteElement(null);
            });
        }
    }
    
    function rankInColumn() {
        helper.sortableElement("#module_rankColumnSort", "#form_module_rankColumnSort");
        
        $("#form_module_position").off("change").on("change", "", function() {
            ajax.send(
                true,
                window.url.cpModuleProfileSort,
                "post",
                {
                    'event': "refresh",
                    'position': $(this).val(),
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                function(xhr) {
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
    
    function deleteElement(id) {
        popupEasy.create(
            window.text.index_5,
            window.textModule.label_1,
            function() {
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
                    function(xhr) {
                        ajax.reply(xhr, "");
                        
                        if (xhr.response.messages.success !== undefined) {
                            $.each($("#cp_module_select_result_desktop").find("table .id_column"), function(key, value) {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });
                            
                            $("#form_module_select_id").find("option[value='" + xhr.response.values.id + "']").remove();

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