"use strict";

/* global helper, ajax, materialDesign, popupEasy */

class MyPagePayment {
    // Properties
    
    // Functions public
    constructor() {
        this.selectSended = false;
        this.selectId = -1;
    }
    
    action = () => {
        this._selectDesktop();
        
        this._selectMobile();
    }
    
    changeView = () => {
        if (helper.checkWidthType() === "mobile") {
            if (this.selectSended === true) {
                this.selectId = $("#myPage_payment_select_mobile").find("select option:selected").val();

                this.selectSended = false;
            }

            if (this.selectId >= 0) {
                $("#myPage_payment_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                let ids = $("#myPage_payment_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(ids, (key, value) => {
                    if ($.trim($(value).text()) === String(this.selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (this.selectSended === true) {
                this.selectId = $.trim($("#myPage_payment_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());

                this.selectSended = false;
            }

            if (this.selectId > 0)
                $("#myPage_payment_select_mobile").find("select option[value='" + this.selectId + "']").prop("selected", true);
        }
    }
    
    // Function private
    _selectDesktop = () => {
        let tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonStatus = "show";
        tableAndPagination.create(window.url.myPagePaymentSelect, "#myPage_payment_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#myPage_payment_select_result_desktop .refresh", (event) => {
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
                (xhr) => {
                    ajax.reply(xhr, "");
                    
                    tableAndPagination.populate(xhr);
                    
                    $("#myPage_payment_select_result").html("");
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#myPage_payment_select_result_desktop .delete_all", (event) => {
            popupEasy.show(
                window.text.index_5,
                window.textPayment.label_2,
                () => {
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
                        (xhr) => {
                            ajax.reply(xhr, "");
                            
                            let ids = $("#myPage_payment_select_result_desktop").find("table .id_column");
                            
                            $.each(ids, (key, value) => {
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
        
        $(document).on("click", "#myPage_payment_select_result_desktop .myPage_payment_delete", (event) => {
            let id = $.trim($(event.currentTarget).parents("tr").find(".id_column").text());
            
            this._deleteElement(id);
        });
        
        $(document).on("click", "#myPage_payment_select_button_desktop", (event) => {
            let id = $.trim($(event.currentTarget).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                window.url.myPagePaymentSelect,
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
                    $("#myPage_payment_select_result").html("");
                },
                (xhr) => {
                    ajax.reply(xhr, `#${event.currentTarget.id}`);
                    
                    this._profile(xhr);
                },
                null,
                null
            );
        });
    }
    
    _selectMobile = () => {
        $(document).on("submit", "#form_myPage_payment_select_mobile", (event) => {
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
                    $("#myPage_payment_select_result").html("");
                },
                (xhr) => {
                    ajax.reply(xhr, `#${event.currentTarget.id}`);
                    
                    this._profile(xhr);
                },
                null,
                null
            );
        });
    }
    
    _profile = (xhr) => {
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            this.selectSended = true;
            
            $("#myPage_payment_select_result").html(xhr.response.render);
            
            materialDesign.refresh();
            
            $("#myPage_payment_delete").on("click", "", (event) => {
               this._deleteElement();
            });
        }
    }
    
    _deleteElement = (id) => {
        let idValue = id === undefined ? null : id;
        
        popupEasy.show(
            window.text.index_5,
            window.textPayment.label_1,
            () => {
                ajax.send(
                    true,
                    window.url.myPagePaymentDelete,
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
                            let ids = $("#myPage_payment_select_result_desktop").find("table .id_column");
                            
                            $.each(ids, (key, value) => {
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