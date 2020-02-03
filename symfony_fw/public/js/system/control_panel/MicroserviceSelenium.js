"use strict";

/* global helper, ajax, uploadChunk, popupEasy, materialDesign */

const controlPanelMicroserviceSelenium = new ControlPanelMicroserviceSelenium();

function ControlPanelMicroserviceSelenium() {
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
        
        uploadChunk.setUrlRequest(window.url.cpMicroserviceSeleniumUpload + "?token=" + window.session.token + "&event=upload");
        uploadChunk.setTagContainer("#upload_chunk_microserviceSelenium_test_container");
        uploadChunk.setTagProgressBar("#upload_chunk_microserviceSelenium_test_container .upload_chunk .mdc-linear-progress");
        uploadChunk.processFile(function() {
            $("#cp_microservice_selenium_select_result_table").find(".refresh").click();
        });
    };
    
    self.changeView = function() {
        if (helper.checkWidthType() === "mobile") {
            if (selectSended === true) {
                selectId = $("#cp_microservice_selenium_select_mobile").find("select option:selected").val();
                
                selectSended = false;
            }
            
            if (selectId >= 0) {
                $("#cp_microservice_selenium_select_result_table").find(".checkbox_column input[type='checkbox']").prop("checked", false);
                
                let id = $("#cp_microservice_selenium_select_result_table").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");
                
                $.each(id, function(key, value) {
                    if ($.trim($(value).text()) === String(selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectSended === true) {
                selectId = $.trim($("#cp_microservice_selenium_select_result_table").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());
                
                selectSended = false;
            }
            
            if (selectId > 0)
                $("#cp_microservice_selenium_select_mobile").find("select option[value='" + selectId + "']").prop("selected", true);
        }
    };
    
    // Function private
    function selectDesktop() {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.create(window.url.cpMicroserviceSeleniumSelect, "#cp_microservice_selenium_select_result_table", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_microservice_selenium_select_result_table .refresh", function() {
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
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    tableAndPagination.populate(xhr);
                    
                    $("#cp_microservice_selenium_select_result").html("");
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_microservice_selenium_select_result_table .delete_all", function() {
            popupEasy.create(
                window.text.index_5,
                window.textMicroserviceSelenium.label_1,
                function() {
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
                        function(xhr) {
                            ajax.reply(xhr, "");
                            
                            $.each($("#cp_microservice_selenium_select_result_table").find("table .id_column"), function(key, value) {
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
        
        $(document).on("click", "#cp_microservice_selenium_select_result_table .cp_microservice_selenium_delete", function() {
            let id = $.trim($(this).parents("tr").find(".id_column").text());
            let name = $.trim($(this).parents("tr").find(".name_column").text());
            
            deleteElement(id, name);
        });
        
        $(document).on("click", "#cp_microservice_selenium_select_button_desktop", function(event) {
            let id = $.trim($(this).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());
            let name = $.trim($(this).parent().find(".checkbox_column input:checked").parents("tr").find(".name_column").text());
            
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
                function() {
                    $("#cp_microservice_selenium_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(document).on("click", ".checkbox_column input[type='checkbox']", function() {
            $("#cp_microservice_selenium_select_result").html("");
        });
    }
    
    function selectMobile() {
        $(document).on("submit", "#form_cp_microservice_selenium_select_mobile", function(event) {
            event.preventDefault();
            
            let name = $("#form_microservice_selenium_select_id").find("option:selected").text();

            $("#form_microservice_selenium_select_name").val(name);
            
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
                    $("#cp_microservice_selenium_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(document).on("change", "#form_microservice_selenium_select_id", function() {
            $("#cp_microservice_selenium_select_result").html("");
        });
    }
    
    function profile(xhr, tag) {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            selectSended = true;
            
            $("#cp_microservice_selenium_select_result").html(xhr.response.render);
            
            materialDesign.refresh();
            
            let name = xhr.response.values.name;
            
            $(".selenium_icon").on("click", "", function(event) {
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
                    function() {
                        $("#cp_microservice_selenium_test_result").html("");
                    },
                    function(xhr) {
                        ajax.reply(xhr, tag);
                        
                        $("#cp_microservice_selenium_test_result").html(xhr.response.result);
                    },
                    null,
                    null
                );
            });
            
            $("#cp_microservice_selenium_delete").on("click", "", function() {
               deleteElement(null, null);
            });
        }
    }
    
    function deleteElement(id, name) {
        popupEasy.create(
            window.text.index_5,
            window.textMicroserviceSelenium.label_1,
            function() {
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
                    function(xhr) {
                        ajax.reply(xhr, "");
                        
                        if (xhr.response.messages.success !== undefined) {
                            $.each($("#cp_microservice_selenium_select_result_table").find("table .id_column"), function(key, value) {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });
                            
                            $("#form_microservice_selenium_select_id").find("option[value='" + xhr.response.values.id + "']").remove();
                            
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