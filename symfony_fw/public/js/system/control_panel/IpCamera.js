"use strict";

/* global helper, ajax, popupEasy, materialDesign */

class IpCamera {
    // Properties
    
    // Functions public
    constructor() {
        this.selectSended = false;
        this.selectId = -1;
    }
    
    action = () => {
        this._selectDesktop();
        
        this._selectMobile();
        
        helper.wordTag("#ipCamera_userId", "#form_ipCamera_userId");
        
        $("#form_cp_ipCamera_create").on("submit", "", (event) => {
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
        
        this._file();
    }
    
    changeView = () => {
        if (helper.checkWidthType() === "mobile") {
            if (this.selectSended === true) {
                this.selectId = $("#cp_ipCamera_select_mobile").find("select option:selected").val();
                
                this.selectSended = false;
            }
            
            if (this.selectId >= 0) {
                $("#cp_ipCamera_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);
                
                let id = $("#cp_ipCamera_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");
                
                $.each(id, (key, value) => {
                    if ($.trim($(value).text()) === String(this.selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (this.selectSended === true) {
                this.selectId = $.trim($("#cp_ipCamera_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());
                
                this.selectSended = false;
            }

            if (this.selectId > 0)
                $("#cp_ipCamera_select_mobile").find(`select option[value="${this.selectId}"]`).prop("selected", true);
        }
    }
    
    videoContainer = () => {
        $(".video_container").find(".video").on("load", "", (event) => {
            $(".video_container").find(".video_loading").remove();
            $(".video_container").find(".video").show();
        });
    }
    
    commandContainer = () => {
        //...
    }
    
    // Function private
    _selectDesktop = () => {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus = "show";
        tableAndPagination.create(window.url.cpIpCameraSelect, "#cp_ipCamera_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_ipCamera_select_result_desktop .refresh", (event) => {
            ajax.send(
                true,
                window.url.cpIpCameraSelect,
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
        
        $(document).on("click", "#cp_ipCamera_select_result_desktop .delete_all", (event) => {
            popupEasy.create(
                window.text.index_5,
                window.textIpCamera.label_2,
                () => {
                    ajax.send(
                        true,
                        window.url.cpIpCameraDelete,
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
                            
                            $.each($("#cp_ipCamera_select_result_desktop").find("table .id_column"), (key, value) => {
                                $(value).parents("tr").remove();
                            });
                            
                            $("#cp_ipCamera_select_result").html("");
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_ipCamera_select_result_desktop .cp_ipCamera_delete", (event) => {
            let id = $.trim($(event.currentTarget).parents("tr").find(".id_column").text());
            
            this._deleteElement(id);
        });
        
        $(document).on("click", "#cp_ipCamera_select_button_desktop", (event) => {
            let id = $.trim($(event.currentTarget).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());
            
            ajax.send(
                true,
                window.url.cpIpCameraProfile,
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
                    $("#cp_ipCamera_select_result").html("");
                },
                (xhr) => {
                    this._profile(xhr, `#${event.currentTarget.id}`);
                },
                null,
                null
            );
        });
        
        $(document).on("click", ".checkbox_column input[type='checkbox']", (event) => {
            $("#cp_ipCamera_select_result").html("");
        });
    }
    
    _selectMobile = () => {
        $(document).on("submit", "#form_cp_ipCamera_select_mobile", (event) => {
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
                    $("#cp_ipCamera_select_result").html("");
                },
                (xhr) => {
                    this._profile(xhr, `#${event.currentTarget.id}`);
                },
                null,
                null
            );
        });
        
        $(document).on("change", "#form_ipCamera_select_id", (event) => {
            $("#cp_ipCamera_select_result").html("");
        });
    }
    
    _profile = (xhr, tag) => {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            this.selectSended = true;
            
            $("#cp_ipCamera_select_result").html(xhr.response.render);
            
            helper.wordTag("#ipCamera_userId", "#form_ipCamera_userId");
            
            materialDesign.refresh();
            
            $("#form_cp_ipCamera_profile").on("submit", "", (event) => {
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
                        
                        if (xhr.response.messages.success !== undefined) {
                            $("#cp_ipCamera_select_result").html("");
                            
                            $("#cp_ipCamera_select_result_desktop .refresh").click();
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#cp_ipCamera_delete").on("click", "", (event) => {
               this._deleteElement(null);
            });
        }
    }
    
    _deleteElement = (id) => {
        popupEasy.create(
            window.text.index_5,
            window.textIpCamera.label_1,
            () => {
                ajax.send(
                    true,
                    window.url.cpIpCameraDelete,
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
                            $.each($("#cp_ipCamera_select_result_desktop").find("table .id_column"), (key, value) => {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });
                            
                            $("#form_ipCamera_select_id").find(`option[value="${xhr.response.values.id}"]`).remove();
                            
                            $("#cp_ipCamera_select_result").html("");
                            
                            $("#cp_ipCamera_select_result_desktop").find(".refresh").click();
                        }
                    },
                    null,
                    null
                );
            }
        );
    }
    
    _file = () => {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus = "show";
        tableAndPagination.create(window.url.cpIpCameraFile, "#cp_ipCamera_file_result", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_ipCamera_file_result .refresh", (event) => {
            ajax.send(
                true,
                window.url.cpIpCameraFile,
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
        
        $(document).on("click", "#cp_ipCamera_file_result .delete_all", (event) => {
            popupEasy.create(
                window.text.index_5,
                window.textIpCamera.label_4,
                () => {
                    ajax.send(
                        true,
                        window.url.cpIpCameraFileDelete,
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
                            
                            $.each($("#cp_ipCamera_file_result").find("table .id_column"), (key, value) => {
                                $(value).parents("tr").remove();
                            });
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_ipCamera_file_result .cp_ipCamera_file_delete", (event) => {
            let id = $.trim($(event.currentTarget).parents("tr").find(".id_column").text());
            let deviceName = $.trim($(event.currentTarget).parents("tr").find(".deviceName_column").text());
            let fileName = $.trim($(event.currentTarget).parents("tr").find(".fileName_column").text());
            
            popupEasy.create(
                window.text.index_5,
                window.textIpCamera.label_3,
                () => {
                    ajax.send(
                        true,
                        window.url.cpIpCameraFileDelete,
                        "post",
                        {
                            'event': "delete",
                            'id': id,
                            'deviceName': deviceName,
                            'fileName': fileName,
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
                                $.each($("#cp_ipCamera_file_result").find("table .id_column"), (key, value) => {
                                    if (xhr.response.values.id === $.trim($(value).text()))
                                        $(value).parents("tr").remove();
                                });
                                
                                $("#cp_ipCamera_file_result").find(".refresh").click();
                            }
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_ipCamera_file_result .cp_ipCamera_file_download", (event) => {
            let deviceName = $.trim($(event.currentTarget).parents("tr").find(".deviceName_column").text());
            let fileName = $.trim($(event.currentTarget).parents("tr").find(".fileName_column").text());
            
            let html = `<form id="cp_ipCamera_file_download" action="${window.url.cpIpCameraFileDownload}" method="post">
                <input type="hidden" name="deviceName" value="${deviceName}">
                <input type="hidden" name="fileName" value="${fileName}">
                <input type="hidden" name="token" value="${window.session.token}">
            </form>`;
            
            $(html).appendTo("body").submit();
            $("#cp_ipCamera_file_download").remove();
        });
    }
}