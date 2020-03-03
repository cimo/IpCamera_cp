"use strict";

/* global helper, ajax, uploadChunk, popupEasy, materialDesign */

class ControlPanelMicroserviceSelenium {
    // Properties
    
    // Functions public
    constructor() {
        this.selectSended = false;
        this.selectId = -1;
    }
    
    action = () => {
        this._selectDesktop();
        
        this._selectMobile();
        
        uploadChunk.setUrlRequest = `${window.url.cpMicroserviceSeleniumUpload}?token=${window.session.token}&event=upload`;
        uploadChunk.setTagContainer = "#upload_chunk_microserviceSelenium_test_container";
        uploadChunk.setTagProgressBar = "#upload_chunk_microserviceSelenium_test_container .upload_chunk .mdc-linear-progress";
        uploadChunk.processFile(() => {
            $("#cp_microservice_selenium_select_result_table").find(".refresh").click();
        });
    }
    
    changeView = () => {
        if (helper.checkWidthType() === "mobile") {
            if (this.selectSended === true) {
                this.selectId = $("#cp_microservice_selenium_select_mobile").find("select option:selected").val();
                
                this.selectSended = false;
            }
            
            if (this.selectId >= 0) {
                $("#cp_microservice_selenium_select_result_table").find(".checkbox_column input[type='checkbox']").prop("checked", false);
                
                let id = $("#cp_microservice_selenium_select_result_table").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");
                
                $.each(id, (key, value) => {
                    if ($.trim($(value).text()) === String(this.selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (this.selectSended === true) {
                this.selectId = $.trim($("#cp_microservice_selenium_select_result_table").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());
                
                this.selectSended = false;
            }
            
            if (this.selectId > 0)
                $("#cp_microservice_selenium_select_mobile").find(`select option[value="${this.selectId}"]`).prop("selected", true);
        }
    }
    
    // Function private
    _selectDesktop = () => {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus = "show";
        tableAndPagination.create(window.url.cpMicroserviceSeleniumSelect, "#cp_microservice_selenium_select_result_table", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_microservice_selenium_select_result_table .refresh", (event) => {
            ajax.send(
                true,
                window.url.cpMicroserviceSeleniumSelect,
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
                    
                    $("#cp_microservice_selenium_select_result").html("");
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_microservice_selenium_select_result_table .delete_all", (event) => {
            popupEasy.create(
                window.text.index_5,
                window.textMicroserviceSelenium.label_1,
                () => {
                    ajax.send(
                        true,
                        window.url.cpMicroserviceSeleniumDelete,
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
                            
                            $.each($("#cp_microservice_selenium_select_result_table").find("table .id_column"), (key, value) => {
                                $(value).parents("tr").remove();
                            });
                            
                            $("#cp_microservice_selenium_select_result").html("");
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_microservice_selenium_select_result_table .cp_microservice_selenium_delete", (event) => {
            let id = $.trim($(event.currentTarget).parents("tr").find(".id_column").text());
            let name = $.trim($(event.currentTarget).parents("tr").find(".name_column").text());
            
            this._deleteElement(id, name);
        });
        
        $(document).on("click", "#cp_microservice_selenium_select_button_desktop", (event) => {
            let id = $.trim($(event.currentTarget).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());
            let name = $.trim($(event.currentTarget).parent().find(".checkbox_column input:checked").parents("tr").find(".name_column").text());
            
            ajax.send(
                true,
                window.url.cpMicroserviceSeleniumProfile,
                "post",
                {
                    'event': "result",
                    'id': id,
                    'name': name,
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                () => {
                    $("#cp_microservice_selenium_select_result").html("");
                },
                (xhr) => {
                    this._profile(xhr, `#${event.currentTarget.id}`);
                },
                null,
                null
            );
        });
        
        $(document).on("click", ".checkbox_column input[type='checkbox']", (event) => {
            $("#cp_microservice_selenium_select_result").html("");
        });
    }
    
    _selectMobile = () => {
        $(document).on("submit", "#form_cp_microservice_selenium_select_mobile", (event) => {
            event.preventDefault();
            
            let name = $("#form_microservice_selenium_select_id").find("option:selected").text();

            $("#form_microservice_selenium_select_name").val(name);
            
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
                    $("#cp_microservice_selenium_select_result").html("");
                },
                (xhr) => {
                    this._profile(xhr, `#${event.currentTarget.id}`);
                },
                null,
                null
            );
        });
        
        $(document).on("change", "#form_microservice_selenium_select_id", (event) => {
            $("#cp_microservice_selenium_select_result").html("");
        });
    }
    
    _profile = (xhr, tag) => {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            this.selectSended = true;
            
            $("#cp_microservice_selenium_select_result").html(xhr.response.render);
            
            materialDesign.refresh();
            
            let name = xhr.response.values.name;
            
            $(".selenium_icon").on("click", "", (event) => {
                let browser = $(event.target).attr("alt").split(".").slice(0, -1).join(".");
                
                ajax.send(
                    true,
                    window.url.cpMicroserviceSeleniumTest,
                    "post",
                    {
                        'event': browser,
                        'name': name,
                        'token': window.session.token
                    },
                    "json",
                    false,
                    true,
                    "application/x-www-form-urlencoded; charset=UTF-8",
                    () => {
                        $("#cp_microservice_selenium_test_result").html("");
                    },
                    (xhr) => {
                        ajax.reply(xhr, tag);
                        
                        $("#cp_microservice_selenium_test_result").html(xhr.response.result);
                    },
                    null,
                    null
                );
            });
            
            $("#cp_microservice_selenium_delete").on("click", "", (event) => {
               this._deleteElement(null, null);
            });
        }
    }
    
    _deleteElement = (id, name) => {
        popupEasy.create(
            window.text.index_5,
            window.textMicroserviceSelenium.label_1,
            () => {
                ajax.send(
                    true,
                    window.url.cpMicroserviceSeleniumDelete,
                    "post",
                    {
                        'event': "delete",
                        'id': id,
                        'name': name,
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
                            $.each($("#cp_microservice_selenium_select_result_table").find("table .id_column"), (key, value) => {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });
                            
                            $("#form_microservice_selenium_select_id").find(`option[value="${xhr.response.values.id}"]`).remove();
                            
                            $("#cp_microservice_selenium_select_result").html("");
                            
                            $("#cp_microservice_selenium_select_result_table").find(".refresh").click();
                        }
                    },
                    null,
                    null
                );
            }
        );
    }
}