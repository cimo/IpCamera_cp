"use strict";

/* global helper, ajax, materialDesign, popupEasy */

class ControlPanelMicroserviceDeploy {
    // Properties
    
    // Functions public
    constructor() {
        this.selectSended = false;
        this.selectId = -1;
    }
    
    action = () => {
        this._selectDesktop();
        
        this._selectMobile();
        
        $("#form_cp_microservice_deploy_render").on("submit", "", (event) => {
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
                    
                    if (xhr.response.values !== undefined) {
                        $("#cp_microservice_deploy_render_result").html(xhr.response.values.renderHtml);
                        $("#cp_microservice_deploy_ssh_connection_result").html("");
                        
                        materialDesign.refresh();
                        
                        this._execute();
                    }
                },
                null,
                null
            );
        });
        
        $("#form_cp_microservice_deploy_create").on("submit", "", (event) => {
            event.preventDefault();
            
            ajax.send(
                true,
                $(event.target).prop("action"),
                $(event.target).prop("method"),
                new FormData(event.target),
                "json",
                false,
                false,
                false,
                null,
                (xhr) => {
                    ajax.reply(xhr, `#${event.target.id}`);
                },
                null,
                null
            );
        });
        
        $("#form_microservice_deploy_select_id").on("change", "", (event) => {
            $("#cp_microservice_deploy_render_result").html("");
            $("#cp_microservice_deploy_ssh_connection_result").html("");
        });
    }
    
    changeView = () => {
        if (helper.checkWidthType() === "mobile") {
            if (this.selectSended === true) {
                this.selectId = $("#cp_microservice_deploy_select_mobile").find("select option:selected").val();

                this.selectSended = false;
            }

            if (this.selectId >= 0) {
                $("#cp_microservice_deploy_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                let id = $("#cp_microservice_deploy_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(id, (key, value) => {
                    if ($.trim($(value).text()) === String(this.selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (this.selectSended === true) {
                this.selectId = $.trim($("#cp_microservice_deploy_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());

                this.selectSended = false;
            }

            if (this.selectId >= 0)
                $("#cp_microservice_deploy_select_mobile").find(`select option[value="${this.selectId}"]`).prop("selected", true);
        }
    }
    
    // Function private
    _selectDesktop = () => {
        let tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonStatus = "show";
        tableAndPagination.create(window.url.cpMicroserviceDeploySelect, "#cp_microservice_deploy_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_microservice_deploy_select_result_desktop .refresh", (event) => {
            ajax.send(
                true,
                window.url.cpMicroserviceDeploySelect,
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
                    
                    $("#cp_microservice_deploy_select_result").html("");
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_microservice_deploy_select_result_desktop .delete_all", (event) => {
            popupEasy.show(
                window.text.index_5,
                window.textMicroserviceDeploy.label_2,
                () => {
                    ajax.send(
                        true,
                        window.url.cpMicroserviceDeployDelete,
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
                            
                            let ids = $("#cp_microservice_deploy_select_result_desktop").find("table .id_column");
                            
                            $.each(ids, (key, value) => {
                                $(value).parents("tr").remove();
                            });
                            
                            $("#cp_microservice_deploy_select_result").html("");
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_microservice_deploy_select_result_desktop .cp_module_delete", (event) => {
            let id = $.trim($(event.currentTarget).parents("tr").find(".id_column").text());
            
            this._deleteElement(id);
        });
        
        $(document).on("click", "#cp_microservice_deploy_select_button_desktop", (event) => {
            let id = $.trim($(event.currentTarget).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                window.url.cpMicroserviceDeploySelect,
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
                    $("#cp_microservice_deploy_select_result").html("");
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
            $("#cp_microservice_deploy_select_result").html("");
        });
    }
    
    _selectMobile = () => {
        $(document).on("submit", "#form_cp_microservice_deploy_select_mobile", (event) => {
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
                    $("#cp_microservice_deploy_select_result").html("");
                },
                (xhr) => {
                    ajax.reply(xhr, `#${event.currentTarget.id}`);
                    
                    this._profile(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("change", "#form_microservice_deploy_select_id", (event) => {
            $("#cp_microservice_deploy_select_result").html("");
        });
    }
    
    _profile = (xhr) => {
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            this.selectSended = true;
            
            $("#cp_microservice_deploy_select_result").html(xhr.response.render);
            
            materialDesign.refresh();
            
            $("#form_cp_microservice_deploy_profile").on("submit", "", (event) => {
                event.preventDefault();
                
                ajax.send(
                    true,
                    $(event.target).prop("action"),
                    $(event.target).prop("method"),
                    new FormData(event.target),
                    "json",
                    false,
                    false,
                    false,
                    null,
                    (xhr) => {
                        ajax.reply(xhr, `#${event.target.id}`);
                        
                        if (xhr.response.messages.success !== undefined) {
                            $("#cp_microservice_deploy_select_result").html("");
                            
                            $("#cp_microservice_deploy_select_result_desktop").find(".refresh").click();
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#cp_microservice_deploy_delete").on("click", "", (event) => {
               this._deleteElement();
            });
            
            $(".button_password").on("click", "", (event) => {
                let target = $(event.target).parent().hasClass("mdc-button") === true ? $(event.target).parent() : $(event.target);
                
                let inputName = $(target).prev().find("input[type='password']").prop("name");
                
                popupEasy.show(
                    window.text.index_5,
                    window.textMicroserviceDeploy.label_4,
                    () => {
                        ajax.send(
                            true,
                            window.url.cpMicroserviceDeployClearPassword,
                            "post",
                            {
                                'event': "clear",
                                'inputName': inputName,
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
                                    $(target).prev().find("input[type='password']").val("");
                                    $(target).prev().find("input[type='password']").attr("placeholder", "");
                                    $(target).prev().find(".mdc-floating-label").removeClass("mdc-floating-label--float-above");
                                }
                            },
                            null,
                            null
                        );
                    }
                );
            });
        }
    }
    
    _deleteElement = (id) => {
        let idValue = id === undefined ? null : id;
        
        popupEasy.show(
            window.text.index_5,
            window.textMicroserviceDeploy.label_1,
            () => {
                ajax.send(
                    true,
                    window.url.cpMicroserviceDeployDelete,
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
                            let ids = $("#cp_microservice_deploy_select_result_desktop").find("table .id_column");
                            
                            $.each(ids, (key, value) => {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });
                            
                            $("#form_microservice_deploy_select_id").find(`option[value="${xhr.response.values.id}"]`).remove();
                            
                            $("#cp_microservice_deploy_select_result").html("");
                            
                            $("#cp_microservice_deploy_select_result_desktop").find(".refresh").click();
                        }
                    },
                    null,
                    null
                );
            }
        );
    }
    
    _execute = () => {
        $(".git_execute").on("click", "", (event) => {
            let id = $("#form_microservice_deploy_select_id").val();
            let action = $(event.target).attr("data-action");
            let branchName = $("#cp_microservice_deploy_render_result").find("input[name='branchName']");
            
            popupEasy.show(
                window.text.index_5,
                window.textMicroserviceDeploy.label_3,
                () => {
                    ajax.send(
                        true,
                        window.url.cpMicroserviceDeployExecute,
                        "post",
                        {
                            'event': "execute",
                            'id': id,
                            'action': action,
                            'branchName': branchName.val(),
                            'token': window.session.token
                        },
                        "json",
                        false,
                        true,
                        "application/x-www-form-urlencoded; charset=UTF-8",
                        () => {
                            if (action !== "pull") {
                                branchName.val("");
                                branchName.focus();
                            }
                            
                            $("#cp_microservice_deploy_ssh_connection_result").html("");
                        },
                        (xhr) => {
                            ajax.reply(xhr, "");
                            
                            if (xhr.response.values !== undefined)
                                $("#cp_microservice_deploy_ssh_connection_result").html(xhr.response.values.sshConnection);
                        },
                        null,
                        null
                    );
                }
            );
        });
    }
}