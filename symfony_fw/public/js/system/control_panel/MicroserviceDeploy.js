"use strict";

/* global helper, ajax, popupEasy, materialDesign */

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
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus = "show";
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
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_microservice_deploy_select_result_desktop .delete_all", (event) => {
            popupEasy.create(
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

                            $.each($("#cp_microservice_deploy_select_result_desktop").find("table .id_column"), (key, value) => {
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
                window.url.cpMicroserviceDeployProfile,
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
                    this._profile(xhr, `#${event.currentTarget.id}`);
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
                helper.serializeJson($(event.currentTarget)),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                () => {
                    $("#cp_microservice_deploy_select_result").html("");
                },
                (xhr) => {
                    this._profile(xhr, `#${event.currentTarget.id}`);
                },
                null,
                null
            );
        });
        
        $(document).on("change", "#form_microservice_deploy_select_id", (event) => {
            $("#cp_microservice_deploy_select_result").html("");
        });
    }
    
    _profile = (xhr, tag) => {
        ajax.reply(xhr, tag);
        
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
                            
                            $("#cp_microservice_deploy_select_result_desktop .refresh").click();
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#cp_microservice_deploy_delete").on("click", "", (event) => {
               this._deleteElement(null);
            });
        }
    }
    
    _deleteElement = (id) => {
        popupEasy.create(
            window.text.index_5,
            window.textMicroserviceDeploy.label_1,
            () => {
                ajax.send(
                    true,
                    window.url.cpMicroserviceDeployDelete,
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
                            $.each($("#cp_microservice_deploy_select_result_desktop").find("table .id_column"), (key, value) => {
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
            
            popupEasy.create(
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