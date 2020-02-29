"use strict";

/* global helper, ajax, popupEasy, materialDesign */

class ControlPanelPayment {
    // Properties
    
    // Functions public
    constructor() {
        this.selectSended = false;
        this.selectId = -1;
    }
    
    action = () => {
        this.selectDesktop();
        
        this.selectMobile();
        
        $("#form_cp_payment_user_select").on("submit", "", (event) => {
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
                () => {
                    $("#cp_payment_select_result").html("");
                },
                (xhr) => {
                    $("#cp_payment_select_result_desktop").find(".refresh").click();
                    
                    $("#form_payment_select_id").find("option").not(":eq(0)").remove();
                    
                    $.each(xhr.response.values.paymentRows, (key, value) => {
                        $("#form_payment_select_id").append("<option value=\"" + value + "\">" + key + "</>");
                    });
                    
                    ajax.reply(xhr, `#${event.target.id}`);
                },
                null,
                null
            );
        });
        
        $("#form_payment_user_select_userId").on("change", "", (event) => {
            this.selectChangeClear();
        });
        
        let timeoutEvent = setTimeout(() => {
            clearTimeout(timeoutEvent);
            
            $(".button_accordion").eq(1).click();
        }, 100);
    }
    
    changeView = () => {
        if (helper.checkWidthType() === "mobile") {
            if (this.selectSended === true) {
                this.selectId = $("#cp_payment_select_mobile").find("select option:selected").val();
                
                this.selectSended = false;
            }
            
            if (this.selectId >= 0) {
                $("#cp_payment_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);
                
                let id = $("#cp_payment_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");
                
                $.each(id, (key, value) => {
                    if ($.trim($(value).text()) === String(this.selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (this.selectSended === true) {
                this.selectId = $.trim($("#cp_payment_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());
                
                this.selectSended = false;
            }
            
            if (this.selectId > 0)
                $("#cp_payment_select_mobile").find("select option[value='" + this.selectId + "']").prop("selected", true);
        }
    }
    
    // Function private
    selectDesktop = () => {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus = "show";
        tableAndPagination.create(window.url.cpPaymentSelect, "#cp_payment_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_payment_select_result_desktop .refresh", (event) => {
            ajax.send(
                true,
                window.url.cpPaymentSelect,
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
                    
                    if ($("#cp_payment_select_result_desktop").parents(".accordion").hasClass("accordion_active") === false)
                        $(".button_accordion").eq(1).click();
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_payment_select_result_desktop .delete_all", (event) => {
            popupEasy.create(
                window.text.index_5,
                window.textPayment.label_2,
                () => {
                    ajax.send(
                        true,
                        window.url.cpPaymentDelete,
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

                            $.each($("#cp_payment_select_result_desktop").find("table .id_column"), (key, value) => {
                                $(value).parents("tr").remove();
                            });
                            
                            $("#cp_payment_select_result").html("");
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_payment_select_result_desktop .cp_payment_delete", (event) => {
            let id = $.trim($(event.currentTarget).parents("tr").find(".id_column").text());
            
            this.deleteElement(id);
        });
        
        $(document).on("click", "#cp_payment_select_button_desktop", (event) => {
            let id = $.trim($(event.currentTarget).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());
            
            ajax.send(
                true,
                window.url.cpPaymentProfile,
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
                    $("#cp_payment_select_result").html("");
                },
                (xhr) => {
                    this.profile(xhr, `#${event.currentTarget.id}`);
                },
                null,
                null
            );
        });
    }
    
    selectMobile = () => {
        $(document).on("submit", "#form_cp_payment_select_mobile", (event) => {
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
                    $("#cp_payment_select_result").html("");
                },
                (xhr) => {
                    this.profile(xhr, `#${event.currentTarget.id}`);
                },
                null,
                null
            );
        });
    }
    
    profile = (xhr, tag) => {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            this.selectSended = true;
            
            $("#cp_payment_select_result").html(xhr.response.render);
            
            materialDesign.refresh();
            
            $("#cp_payment_delete").on("click", "", (event) => {
               this.deleteElement(null);
            });
        }
    }
    
    deleteElement = (id) => {
        popupEasy.create(
            window.text.index_5,
            window.textPayment.label_1,
            () => {
                ajax.send(
                    true,
                    window.url.cpPaymentDelete,
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
                            $.each($("#cp_payment_select_result_desktop").find("table .id_column"), (key, value) => {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });
                            
                            $("#form_payment_select_id").find("option[value='" + xhr.response.values.id + "']").remove();
                            
                            $("#cp_payment_select_result").html("");
                            
                            $("#cp_payment_select_result_desktop").find(".refresh").click();
                        }
                    },
                    null,
                    null
                );
            }
        );
    }
    
    selectChangeClear = () => {
        $("#cp_payment_select_result_desktop").find("tbody").html("");
        $("#form_payment_select_id").find("option").not(":eq(0)").remove();
        $("#cp_payment_select_result").html("");
    }
}