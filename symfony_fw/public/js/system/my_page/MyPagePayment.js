"use strict";

/* global helper, ajax, popupEasy, materialDesign */

const myPagePayment = new MyPagePayment();

function MyPagePayment() {
    // Vars
    const self = this;
    
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
    };
    
    self.changeView = function() {
        if (helper.checkWidthType() === "mobile") {
            if (selectSended === true) {
                selectId = $("#myPage_payment_select_mobile").find("select option:selected").val();

                selectSended = false;
            }

            if (selectId >= 0) {
                $("#myPage_payment_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                let id = $("#myPage_payment_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(id, function(key, value) {
                    if ($.trim($(value).text()) === String(selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectSended === true) {
                selectId = $.trim($("#myPage_payment_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());

                selectSended = false;
            }

            if (selectId > 0)
                $("#myPage_payment_select_mobile").find("select option[value='" + selectId + "']").prop("selected", true);
        }
    };
    
    // Function private
    function selectDesktop() {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.create(window.url.myPagePaymentSelect, "#myPage_payment_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#myPage_payment_select_result_desktop .refresh", function() {
            ajax.send(
                true,
                window.url.myPagePaymentSelect,
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
        
        $(document).on("click", "#myPage_payment_select_result_desktop .delete_all", function() {
            popupEasy.create(
                window.text.index_5,
                window.textPayment.label_2,
                function() {
                    ajax.send(
                        true,
                        window.url.myPagePaymentDelete,
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

                            $.each($("#myPage_payment_select_result_desktop").find("table .id_column"), function(key, value) {
                                $(value).parents("tr").remove();
                            });
                            
                            $("#myPage_payment_select_result").html("");
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#myPage_payment_select_result_desktop .myPage_payment_delete", function() {
            let id = $.trim($(this).parents("tr").find(".id_column").text());
            
            deleteElement(id);
        });
        
        $(document).on("click", "#myPage_payment_select_button_desktop", function(event) {
            let id = $.trim($(this).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                window.url.myPagePaymentProfile,
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
                    $("#myPage_payment_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    }
    
    function selectMobile() {
        $(document).on("submit", "#form_myPage_payment_select_mobile", function(event) {
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
                    $("#myPage_payment_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    }
    
    function profile(xhr, tag) {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            selectSended = true;
            
            $("#myPage_payment_select_result").html(xhr.response.render);
            
            materialDesign.refresh();
            
            $("#myPage_payment_delete").on("click", "", function() {
               deleteElement(null);
            });
        }
    }
    
    function deleteElement(id) {
        popupEasy.create(
            window.text.index_5,
            window.textPayment.label_1,
            function() {
                ajax.send(
                    true,
                    window.url.myPagePaymentDelete,
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
                            $.each($("#myPage_payment_select_result_desktop").find("table .id_column"), function(key, value) {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });

                            $("#form_payment_select_id").find("option[value='" + xhr.response.values.id + "']").remove();

                            $("#myPage_payment_select_result").html("");
                            
                            $("#myPage_payment_select_result_desktop").find(".refresh").click();
                        }
                    },
                    null,
                    null
                );
            }
        );
    }
}