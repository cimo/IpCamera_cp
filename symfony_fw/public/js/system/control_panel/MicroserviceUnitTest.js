/* global utility, ajax, popupEasy, materialDesign */

var controlPanelMicroserviceUnitTest = new ControlPanelMicroserviceUnitTest();

function ControlPanelMicroserviceUnitTest() {
    // Vars
    var self = this;
    
    var selectSended = false;
    var selectId = -1;
    
    // Properties
    
    // Functions public
    self.init = function() {
        selectDesktop();
        
        selectMobile();
        
        $("#form_cp_microservice_unit_test_create").on("submit", "", function(event) {
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
        if (utility.checkWidthType() === "mobile") {
            if (selectSended === true) {
                selectId = $("#cp_microservice_unit_test_select_mobile").find("select option:selected").val();

                selectSended = false;
            }

            if (selectId >= 0) {
                $("#cp_microservice_unit_test_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                var id = $("#cp_microservice_unit_test_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(id, function(key, value) {
                    if ($.trim($(value).text()) === String(selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectSended === true) {
                selectId = $.trim($("#cp_microservice_unit_test_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());

                selectSended = false;
            }

            if (selectId > 0)
                $("#cp_microservice_unit_test_select_mobile").find("select option[value='" + selectId + "']").prop("selected", true);
        }
    };
    
    // Function private
    function selectDesktop() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.create(window.url.cpMicroserviceUnitTestSelect, "#cp_microservice_unit_test_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_microservice_unit_test_select_result_desktop .refresh", function() {
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
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    tableAndPagination.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_microservice_unit_test_select_result_desktop .delete_all", function() {
            popupEasy.create(
                window.text.index_5,
                window.textMicroserviceUnitTest.label_2,
                function() {
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
                        function(xhr) {
                            ajax.reply(xhr, "");

                            $.each($("#cp_microservice_unit_test_select_result_desktop").find("table .id_column"), function(key, value) {
                                var id = $.trim($(value).parents("tr").find(".id_column").text());
                                
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
        
        $(document).on("click", "#cp_microservice_unit_test_select_result_desktop .cp_microservice_unit_test_delete", function() {
            var id = $.trim($(this).parents("tr").find(".id_column").text());
            
            deleteElement(id);
        });
        
        $(document).on("click", "#cp_microservice_unit_test_select_button_desktop", function(event) {
            var id = $.trim($(this).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                window.url.cpMicroserviceUnitTestProfile,
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
                    $("#cp_microservice_unit_test_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(document).on("click", ".checkbox_column input[type='checkbox']", function() {
            $("#cp_microservice_unit_test_select_result").html("");
        });
    }
    
    function selectMobile() {
        $(document).on("submit", "#form_cp_microservice_unit_test_select_mobile", function(event) {
            event.preventDefault();

            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                utility.serializeJson($(this)),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                function() {
                    $("#cp_microservice_unit_test_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(document).on("change", "#form_microservice_unit_test_select_id", function() {
            $("#cp_microservice_unit_test_select_result").html("");
        });
    }
    
    function profile(xhr, tag) {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            selectSended = true;
            
            $("#cp_microservice_unit_test_select_result").html(xhr.response.render);
            
            materialDesign.refresh();

            $("#form_microservice_unit_test_level").on("keyup", "", function() {
                $(this).val($(this).val().toUpperCase());
            });

            $("#form_cp_microservice_unit_test_profile").on("submit", "", function(event) {
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
                            $("#cp_microservice_unit_test_select_result").html("");
                            
                            $("#cp_microservice_unit_test_select_result_desktop .refresh").click();
                        }
                    },
                    null,
                    null
                );
            });

            $("#cp_microservice_unit_test_delete").on("click", "", function() {
               deleteElement(null);
            });
        }
    }
    
    function deleteElement(id) {
        popupEasy.create(
            window.text.index_5,
            window.textMicroserviceUnitTest.label_1,
            function() {
                ajax.send(
                    true,
                    window.url.cpMicroserviceUnitTestDelete,
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
                            $.each($("#cp_microservice_unit_test_select_result_desktop").find("table .id_column"), function(key, value) {
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