// Version 1.0.0

/* global utility, ajax, popupEasy, download */

var ipCamera = new IpCamera();

function IpCamera() {
    // Vars
    var self = this;
    
    var cameraNumber = window.session.cameraNumber;
    
    var detectionStatus = "";
    
    var videoAreaEnabled = false;
    
    var swipeMoveValue = -1;
    
    var takePictureEnable = true;
    
    // Properties
    
    // Functions public
    self.init = function() {
        self.status();
    };
    
    self.status = function() {
        var lastSelectOption = parseInt($("#form_camera_selection_cameraNumber").find("option").last().val());

        $("#form_camera_selection").on("submit", "", function(event) {
            event.preventDefault();

            var form = $(this);

            if (parseInt($("#form_camera_selection_cameraNumber").val()) === 0) {
                popupEasy.create(
                    window.text.warning,
                    "You would like create a new camera settings?",
                    function() {
                        popupEasy.close();

                        createNew = true;

                        statusSend(form, lastSelectOption, true);
                    },
                    function() {
                        popupEasy.close();
                    }
                );
            }
            else
                statusSend(form, lastSelectOption, false);
        });

        if (cameraNumber > 0)
            $("#form_camera_selection_cameraNumber").val(cameraNumber);
        else
            $("#form_camera_selection_cameraNumber").val(-1);
        
        $("#form_camera_selection").submit();
    };
    
    self.changeView = function() {
        if (utility.checkWidth() === "desktop") {
            $("#camera_video_area").removeClass("touch_disable");
            $("#camera_video_area").hide();
            
            $("#control_container").show();
        }
        else {
            if (utility.getIsMobile() === false) {
                $("#camera_control_swipe_switch").parents(".bootstrap-switch").hide();
                $(".panel-heading").find(".camera_control_picture").hide();
            }
            else
                $("#control_container").hide();
            
            if (videoAreaEnabled === true) {
                $("#camera_video_area").show();
                $("#camera_video_area").addClass("touch_disable");
            }
        }
    };
    
    // Functions private
    function statusSend(form, lastSelectOption, createNew) {
        cameraNumber = $("#form_camera_selection_cameraNumber").val();
        
        ajax.send(
            true,
            true,
            form.prop("action"),
            form.prop("method"),
            JSON.stringify(form.serializeArray()),
            "json",
            false,
            function() {
                $("#camera_video_result").html("");
                $("#camera_control_result").html("");
                $("#camera_apparatusProfile_result").html("");
                $("#camera_file_result").html("");
            },
            function(xhr) {
                /*if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                    ajax.reply(xhr, "");
                    
                    return;
                }*/
                
                if (createNew === true) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    cameraNumber = lastSelectOption + 1;
                    
                    $("<option>", {
                        'value': cameraNumber,
                        'text': "Camera " + cameraNumber
                    }).appendTo($("#form_camera_selection_cameraNumber"));
                    
                    $("#form_camera_selection_cameraNumber").val(cameraNumber);
                }
                else {
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.values !== undefined && xhr.response.values.motionDetectionStatus !== undefined)
                        labelStatus(xhr.response.values.motionDetectionStatus);
                    else
                        labelStatus();
                }

                $("#camera_video_result").load(window.url.root + "/Resources/views/render/video.php", function() {
                    video();
                });

                $("#camera_control_result").load(window.url.root + "/Resources/views/render/control.php", function() {
                    control();
                });

                $("#camera_apparatusProfile_result").load(window.url.root + "/Resources/views/render/apparatusProfile.php", function() {
                    apparatusProfile();
                });

                $("#camera_file_result").load(window.url.root + "/Resources/views/render/file.php", function() {
                    file();
                });
                
                $("#camera_userProfile_result").load(window.url.root + "/Resources/views/render/userProfile.php", function() {
                    userProfile();
                });
                
                $("#camera_userManagement_result").load(window.url.root + "/Resources/views/render/userManagement.php", function() {
                    userManagement();
                });
                
                $("#camera_setting_result").load(window.url.root + "/Resources/views/render/setting.php", function() {
                    setting();
                });
            },
            null,
            null
        );
    }
    
    function video() {
        $("#camera_video").on("error", function() {
            utility.imageRefresh("#camera_video", 2);
        });
    }
    
    function control() {
        $("#camera_control_swipe_switch").bootstrapSwitch("state", false);
        
        if (utility.getIsMobile() === false)
            $("#camera_control_swipe_switch").parents(".bootstrap-switch").hide();
        
        move("#camera_control_move_up", new Array(0, 1));
        move("#camera_control_move_right_up", new Array(6, 7, 0, 1));
        move("#camera_control_move_right", new Array(6, 7));
        move("#camera_control_move_right_down", new Array(6, 7, 2, 3));
        move("#camera_control_move_down", new Array(2, 3));
        move("#camera_control_move_left_down", new Array(4, 5, 2, 3));
        move("#camera_control_move_left", new Array(4, 5));
        move("#camera_control_move_left_up", new Array(4, 5, 0, 1));
        
        $("#camera_control_swipe_switch").on("switchChange.bootstrapSwitch", "", function(event, state) {
            if (state === true && utility.getIsMobile() === true) {
                videoAreaEnabled = true;
                
                $("#camera_video_area").show();
                $("#camera_video_area").addClass("touch_disable");
                
                $("#camera_video_area").swipe({
                    left: function() {
                        swipeMoveStart(6);
                        swipeMoveValue = 7;
                    },
                    right: function() {
                        swipeMoveStart(4);
                        swipeMoveValue = 5;
                    },
                    up: function() {
                        swipeMoveStart(2);
                        swipeMoveValue = 3;
                    },
                    down: function() {
                        swipeMoveStart(0);
                        swipeMoveValue = 1;
                    }
                });

                swipeMoveEnd();
            }
            else {
                videoAreaEnabled = false;
                
                $("#camera_video_area").removeClass("touch_disable");
                $("#camera_video_area").hide();
            }
        });
        
        $(".camera_control_picture").on("click", "", function() {
            if (takePictureEnable === true) {
                ajax.send(
                    true,
                    false,
                    window.url.root + "/Requests/IpCameraRequest.php?controller=controlAction",
                    "post",
                    JSON.stringify({
                        'event': "picture",
                        'token': window.session.token
                    }),
                    "json",
                    false,
                    null,
                    function(xhr) {
                        /*if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                            ajax.reply(xhr, "");

                            return;
                        }*/
                        
                        takePictureEnable = false;

                        ajax.reply(xhr, "");

                        setTimeout(
                            function() {
                                takePictureEnable = true;
                            },
                        1000);
                    },
                    null,
                    null
                );
            }
        });
    }
    
    function apparatusProfile() {
        $("#form_camera_apparatusProfile_motionDetectionStatus").bootstrapSwitch();
        
        $("#form_camera_apparatusProfile_motionDetectionStatus").on("switchChange.bootstrapSwitch", "", function(event, state) {
            switchStatus(state);
        });
        
        switchStatus(detectionStatus);
        
        $("#form_camera_apparatusProfile").on("submit", "", function(event) {
            event.preventDefault();

            ajax.send(
                true,
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                JSON.stringify($(this).serializeArray()),
                "json",
                false,
                null,
                function(xhr) {
                    /*if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                        ajax.reply(xhr, "");

                        return;
                    }*/
                    
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    labelStatus();
                },
                null,
                null
            );
        });
        
        $("#camera_deletion").on("click", "", function() {
            popupEasy.create(
                window.text.warning,
                "Really delete this camera?",
                function() {
                    popupEasy.close();

                    ajax.send(
                        true,
                        true,
                        window.url.root + "/Requests/IpCameraRequest.php?controller=apparatusProfileDeleteAction",
                        "post",
                        JSON.stringify({
                            'token': window.session.token
                        }),
                        "json",
                        false,
                        null,
                        function(xhr) {
                            /*if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                                ajax.reply(xhr, "");

                                return;
                            }*/

                            ajax.reply(xhr, "");
                            
                            $("#form_camera_selection_cameraNumber").find("option[value=" + cameraNumber + "]").remove();
                            $("#form_camera_selection_cameraNumber").val(-1);
                            
                            $("#camera_video_result").html("");
                            $("#camera_control_result").html("");
                            $("#camera_apparatusProfile_result").html("");
                            $("#camera_file_result").html("");
                        },
                        null,
                        null
                    );
                },
                function() {
                    popupEasy.close();
                }
            );
        });
    }
    
    function file() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.init(window.url.root + "/Requests/IpCameraRequest.php", "#camera_file_table", true);
        tableAndPagination.search(true);
        tableAndPagination.pagination(true);
        tableAndPagination.sort(true);
        
        $(document).on("click", "#camera_file_table .refresh", function() {
            ajax.send(
                true,
                false,
                window.url.root + "/Requests/IpCameraRequest.php?controller=fileAction",
                "post",
                JSON.stringify({
                    'event': "refresh",
                    'token': window.session.token
                }),
                "json",
                false,
                null,
                function(xhr) {
                    /*if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                        ajax.reply(xhr, "");

                        return;
                    }*/
                    
                    ajax.reply(xhr, "");
                    
                    tableAndPagination.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#camera_file_table .delete_all", function() {
            popupEasy.create(
                window.text.warning,
                "Really delete all files?",
                function() {
                    popupEasy.close();
                    
                    ajax.send(
                        true,
                        false,
                        window.url.root + "/Requests/IpCameraRequest.php?controller=fileAction",
                        "post",
                        JSON.stringify({
                            'event': "deleteAll",
                            'token': window.session.token
                        }),
                        "json",
                        false,
                        null,
                        function(xhr) {
                            /*if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                                ajax.reply(xhr, "");

                                return;
                            }*/

                            ajax.reply(xhr, "");
                            
                            tableAndPagination.populate(xhr);
                        },
                        null,
                        null
                    );
                },
                function() {
                    popupEasy.close();
                }
            );
        });
        
        $(document).on("click", "#camera_file_table .camera_file_download", function() {
            var path = window.path.documentRoot + "/motion/camera_" + cameraNumber;
            var name = $(this).parents("tr").find(".name_column").text();
            
            download.send(path, name);
        });
        
        $(document).on("click", "#camera_file_table .camera_file_delete", function() {
            var name = $.trim($(this).parents("tr").find(".name_column").text());
            
            popupEasy.create(
                window.text.warning,
                "Really delete this file?",
                function() {
                    popupEasy.close();
                    
                    ajax.send(
                        true,
                        false,
                        window.url.root + "/Requests/IpCameraRequest.php?controller=fileAction",
                        "post",
                        JSON.stringify({
                            'event': "delete",
                            'name': name,
                            'token': window.session.token
                        }),
                        "json",
                        false,
                        null,
                        function(xhr) {
                            /*if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                                ajax.reply(xhr, "");

                                return;
                            }*/

                            ajax.reply(xhr, "");
                            
                            tableAndPagination.populate(xhr);
                        },
                        null,
                        null
                    );
                },
                function() {
                    popupEasy.close();
                }
            );
        });
    }
    
    function userProfile() {
        utility.accordion();
        
        $("#form_userProfile_password").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                JSON.stringify($(this).serializeArray()),
                "json",
                false,
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    }
    
    function userManagement() {
        $("#form_cp_user_selection").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                JSON.stringify($(this).serializeArray()),
                "json",
                false,
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    }
    
    function setting() {
        $("#form_camera_setting").on("submit", "", function(event) {
            event.preventDefault();

            ajax.send(
                true,
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                JSON.stringify($(this).serializeArray()),
                "json",
                false,
                null,
                function(xhr) {
                    /*if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                        ajax.reply(xhr, "");

                        return;
                    }*/
                    
                    ajax.reply(xhr, "");
                },
                null,
                null
            );
        });
    }
    
    function labelStatus(value) {
        if (value === undefined)
            detectionStatus = $("#form_camera_apparatusProfile_motionDetectionStatus").val();
        else
            detectionStatus = value;
        
        if (detectionStatus === "start")
            $("#camera_detection_status").text("Active.");
        else
            $("#camera_detection_status").text("Not active.");
    }
    
    function switchStatus(value) {
        if (value === true || value === "start") {
            if ($("#form_camera_apparatusProfile_motionDetectionStatus").val() === "" || $("#form_camera_apparatusProfile_motionDetectionStatus").val() === "pause")
                $("#form_camera_apparatusProfile_motionDetectionStatus").click();
            
            $("#form_camera_apparatusProfile_motionDetectionStatus").prop("value", "start");
            $("#form_camera_apparatusProfile_motionDetectionStatus").parents(".bootstrap-switch-wrapper").next().prop("value", "start");
        }
        else {
            if ($("#form_camera_apparatusProfile_motionDetectionStatus").val() === "start")
                $("#form_camera_apparatusProfile_motionDetectionStatus").click();
            
            $("#form_camera_apparatusProfile_motionDetectionStatus").prop("value", "pause");
            $("#form_camera_apparatusProfile_motionDetectionStatus").parents(".bootstrap-switch-wrapper").next().prop("value", "pause");
        }
    }
    
    function move(tag, elements) {
        if (elements.length === 2) {
            $(tag).on("mousedown", "", function(event) {
                event.preventDefault();
                
                utility.postIframe(
                    window.url.cameraControl + "&command=" + elements[0],
                    "post",
                    {
                        'command': elements[0]
                    }
                );
            });
            
            $(tag).on("mouseup", "", function(event) {
                event.preventDefault();
                
                utility.postIframe(
                    window.url.cameraControl + "&command=" + elements[1],
                    "post",
                    {
                        'command': elements[1]
                    }
                );
            });
        }
        else if (elements.length === 4) {
            $(tag).on("mousedown", "", function(event) {
                event.preventDefault();
                
                utility.postIframe(
                    window.url.cameraControl + "&command=" + elements[0],
                    "post",
                    {
                        'command': elements[0]
                    }
                );
        
                utility.postIframe(
                    window.url.cameraControl + "&command=" + elements[2],
                    "post",
                    {
                        'command': elements[2]
                    }
                );
            });
            
            $(tag).on("mouseup", "", function(event) {
                event.preventDefault();
                
                utility.postIframe(
                    window.url.cameraControl + "&command=" + elements[1],
                    "post",
                    {
                        'command': elements[1]
                    }
                );
                
                utility.postIframe(
                    window.url.cameraControl + "&command=" + elements[3],
                    "post",
                    {
                        'command': elements[3]
                    }
                );
            });
        }
    }
    
    function swipeMoveStart(value) {
        utility.postIframe(
            window.url.cameraControl + "&command=" + value,
            "post",
            {
                'command': value
            }
        );
    }
    
    function swipeMoveEnd() {
        $("#camera_video_area").on("touchend touchcancel", "", function(event) {
            if (swipeMoveValue !== -1) {
                utility.postIframe(
                    window.url.cameraControl + "&command=" + swipeMoveValue,
                    "post",
                    {
                        'command': swipeMoveValue
                    }
                );
                
                swipeMoveValue = -1;
            }
        });
    }
}