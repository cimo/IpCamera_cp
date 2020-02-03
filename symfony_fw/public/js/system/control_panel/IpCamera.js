"use strict";

/* global helper, ajax, popupEasy, materialDesign */

const ipCamera = new ipCamera();

function ipCamera() {
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
        
        helper.wordTag("#ipCamera_userId", "#form_ipCamera_userId");
        
        $("#form_cp_ipCamera_create").on("submit", "", function(event) {
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
        
        file();
    };
    
    self.changeView = function() {
        if (helper.checkWidthType() === "mobile") {
            if (selectSended === true) {
                selectId = $("#cp_ipCamera_select_mobile").find("select option:selected").val();
                
                selectSended = false;
            }
            
            if (selectId >= 0) {
                $("#cp_ipCamera_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);
                
                let id = $("#cp_ipCamera_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");
                
                $.each(id, function(key, value) {
                    if ($.trim($(value).text()) === String(selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectSended === true) {
                selectId = $.trim($("#cp_ipCamera_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());
                
                selectSended = false;
            }

            if (selectId > 0)
                $("#cp_ipCamera_select_mobile").find("select option[value='" + selectId + "']").prop("selected", true);
        }
    };
    
    self.videoContainer = function() {
        $(".video_container").find(".video").on("load", "", function() {
            $(".video_container").find(".video_loading").remove();
            $(".video_container").find(".video").show();
        });
    };
    
    self.commandContainer = function() {
        
    };
    
    // Function private
    function selectDesktop() {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.create(window.url.cpIpCameraSelect, "#cp_ipCamera_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_ipCamera_select_result_desktop .refresh", function() {
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
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    tableAndPagination.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_ipCamera_select_result_desktop .delete_all", function() {
            popupEasy.create(
                window.text.index_5,
                window.textIpCamera.label_2,
                function() {
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
                        function(xhr) {
                            ajax.reply(xhr, "");
                            
                            $.each($("#cp_ipCamera_select_result_desktop").find("table .id_column"), function(key, value) {
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
        
        $(document).on("click", "#cp_ipCamera_select_result_desktop .cp_ipCamera_delete", function() {
            let id = $.trim($(this).parents("tr").find(".id_column").text());
            
            deleteElement(id);
        });
        
        $(document).on("click", "#cp_ipCamera_select_button_desktop", function(event) {
            let id = $.trim($(this).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());
            
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
                function() {
                    $("#cp_ipCamera_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(document).on("click", ".checkbox_column input[type='checkbox']", function() {
            $("#cp_ipCamera_select_result").html("");
        });
    }
    
    function selectMobile() {
        $(document).on("submit", "#form_cp_ipCamera_select_mobile", function(event) {
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
                    $("#cp_ipCamera_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(document).on("change", "#form_ipCamera_select_id", function() {
            $("#cp_ipCamera_select_result").html("");
        });
    }
    
    function profile(xhr, tag) {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            selectSended = true;
            
            $("#cp_ipCamera_select_result").html(xhr.response.render);
            
            helper.wordTag("#ipCamera_userId", "#form_ipCamera_userId");
            
            materialDesign.refresh();
            
            $("#form_cp_ipCamera_profile").on("submit", "", function(event) {
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
                            $("#cp_ipCamera_select_result").html("");
                            
                            $("#cp_ipCamera_select_result_desktop .refresh").click();
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#cp_ipCamera_delete").on("click", "", function() {
               deleteElement(null);
            });
        }
    }
    
    function deleteElement(id) {
        popupEasy.create(
            window.text.index_5,
            window.textIpCamera.label_1,
            function() {
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
                    function(xhr) {
                        ajax.reply(xhr, "");
                        
                        if (xhr.response.messages.success !== undefined) {
                            $.each($("#cp_ipCamera_select_result_desktop").find("table .id_column"), function(key, value) {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });
                            
                            $("#form_ipCamera_select_id").find("option[value='" + xhr.response.values.id + "']").remove();
                            
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
    
    function file() {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.create(window.url.cpIpCameraFile, "#cp_ipCamera_file_result", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_ipCamera_file_result .refresh", function() {
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
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    tableAndPagination.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_ipCamera_file_result .delete_all", function() {
            popupEasy.create(
                window.text.index_5,
                window.textIpCamera.label_4,
                function() {
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
                        function(xhr) {
                            ajax.reply(xhr, "");
                            
                            $.each($("#cp_ipCamera_file_result").find("table .id_column"), function(key, value) {
                                $(value).parents("tr").remove();
                            });
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_ipCamera_file_result .cp_ipCamera_file_delete", function() {
            let id = $.trim($(this).parents("tr").find(".id_column").text());
            let deviceName = $.trim($(this).parents("tr").find(".deviceName_column").text());
            let fileName = $.trim($(this).parents("tr").find(".fileName_column").text());
            
            popupEasy.create(
                window.text.index_5,
                window.textIpCamera.label_3,
                function() {
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
                        function(xhr) {
                            ajax.reply(xhr, "");
                            
                            if (xhr.response.messages.success !== undefined) {
                                $.each($("#cp_ipCamera_file_result").find("table .id_column"), function(key, value) {
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
        
        $(document).on("click", "#cp_ipCamera_file_result .cp_ipCamera_file_download", function() {
            let deviceName = $.trim($(this).parents("tr").find(".deviceName_column").text());
            let fileName = $.trim($(this).parents("tr").find(".fileName_column").text());
            
            $(document).ready(function(){
                let html = "<form id=\"cp_ipCamera_file_download\" action=\"" + window.url.cpIpCameraFileDownload + "\" method=\"post\">\n\
                    <input type=\"hidden\" name=\"deviceName\" value=\"" + deviceName + "\">\n\
                    <input type=\"hidden\" name=\"fileName\" value=\"" + fileName + "\">\n\
                    <input type=\"hidden\" name=\"token\" value=\"" + window.session.token + "\">\n\
                </form>";
                
                $(html).appendTo("body").submit();
                $("#cp_ipCamera_file_download").remove();
            });
        });
    }
}