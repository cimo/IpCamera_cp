"use strict";

/* global helper, ajax, materialDesign, popupEasy */

class ControlPanelMicroserviceUnitTest {
    // Properties
    
    // Functions public
    constructor() {
        this.selectSended = false;
        this.selectId = -1;
    }
    
    action = () => {
        this._selectDesktop();
        
        this._selectMobile();
        
        $("#form_cp_microservice_unit_test_create").on("submit", "", (event) => {
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
                this.selectId = $("#cp_microservice_unit_test_select_mobile").find("select option:selected").val();

                this.selectSended = false;
            }

            if (this.selectId >= 0) {
                $("#cp_microservice_unit_test_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                let id = $("#cp_microservice_unit_test_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(id, (key, value) => {
                    if ($.trim($(value).text()) === String(this.selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (this.selectSended === true) {
                this.selectId = $.trim($("#cp_microservice_unit_test_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());

                this.selectSended = false;
            }

            if (this.selectId > 0)
                $("#cp_microservice_unit_test_select_mobile").find("select option[value='" + this.selectId + "']").prop("selected", true);
        }
    }
    
    // Function private
    _selectDesktop = () => {
        let tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonStatus = "show";
        tableAndPagination.create(window.url.cpMicroserviceUnitTestSelect, "#cp_microservice_unit_test_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_microservice_unit_test_select_result_desktop .refresh", (event) => {
            ajax.send(
                true,
                window.url.cpMicroserviceUnitTestSelect,
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
                    
                    $("#cp_microservice_unit_test_select_result").html("");
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_microservice_unit_test_select_result_desktop .delete_all", (event) => {
            popupEasy.show(
                window.text.index_5,
                window.textMicroserviceUnitTest.label_2,
                () => {
                    ajax.send(
                        true,
                        window.url.cpMicroserviceUnitTestDelete,
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
                            
                            let ids = $("#cp_microservice_unit_test_select_result_desktop").find("table .id_column");
                            
                            $.each(ids, (key, value) => {
                                let id = $.trim($(value).parents("tr").find(".id_column").text());
                                
                                if (id > 4)
                                    $(value).parents("tr").remove();
                            });
                            
                            $("#cp_microservice_unit_test_select_result").html("");
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_microservice_unit_test_select_result_desktop .cp_microservice_unit_test_delete", (event) => {
            let id = $.trim($(event.currentTarget).parents("tr").find(".id_column").text());
            
            this._deleteElement(id);
        });
        
        $(document).on("click", "#cp_microservice_unit_test_select_button_desktop", (event) => {
            let id = $.trim($(event.currentTarget).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                window.url.cpMicroserviceUnitTestSelect,
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
                    $("#cp_microservice_unit_test_select_result").html("");
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
            $("#cp_microservice_unit_test_select_result").html("");
        });
    }
    
    _selectMobile = () => {
        $(document).on("submit", "#form_cp_microservice_unit_test_select_mobile", (event) => {
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
                    $("#cp_microservice_unit_test_select_result").html("");
                },
                (xhr) => {
                    ajax.reply(xhr, `#${event.currentTarget.id}`);
                    
                    this._profile(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("change", "#form_microservice_unit_test_select_id", (event) => {
            $("#cp_microservice_unit_test_select_result").html("");
        });
    }
    
    _profile = (xhr) => {
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            this.selectSended = true;
            
            $("#cp_microservice_unit_test_select_result").html(xhr.response.render);
            
            materialDesign.refresh();

            $("#form_microservice_unit_test_level").on("keyup", "", (event) => {
                $(event.target).val($(event.target).val().toUpperCase());
            });

            $("#form_cp_microservice_unit_test_profile").on("submit", "", (event) => {
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
                            $("#cp_microservice_unit_test_select_result").html("");
                            
                            $("#cp_microservice_unit_test_select_result_desktop").find(".refresh").click();
                        }
                    },
                    null,
                    null
                );
            });

            $("#cp_microservice_unit_test_delete").on("click", "", (event) => {
               this._deleteElement();
            });
        }
    }
    
    _deleteElement = (id) => {
        let idValue = id === undefined ? null : id;
        
        popupEasy.show(
            window.text.index_5,
            window.textMicroserviceUnitTest.label_1,
            () => {
                ajax.send(
                    true,
                    window.url.cpMicroserviceUnitTestDelete,
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
                            let ids = $("#cp_microservice_unit_test_select_result_desktop").find("table .id_column");
                            
                            $.each(ids, (key, value) => {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });
                            
                            $("#form_microservice_unit_test_select_id").find("option[value='" + xhr.response.values.id + "']").remove();
                            
                            $("#cp_microservice_unit_test_select_result").html("");
                            
                            $("#cp_microservice_unit_test_select_result_desktop").find(".refresh").click();
                        }
                    },
                    null,
                    null
                );
            }
        );
    }
}