// Version 1.0.0

/* global utility, ajax, popupEasy, download */

var ipCamera = new IpCamera();

function IpCamera() {
    // Vars
    var self = this;
    
    var apparatusNumber = window.session.apparatusNumber;
    
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
        var lastSelectOption = parseInt($("#form_apparatus_selection_number").find("option").last().val());

        $("#form_apparatus_selection").on("submit", "", function(event) {
            event.preventDefault();

            var form = $(this);

            if (parseInt($("#form_apparatus_selection_number").val()) === 0) {
                popupEasy.create(
                    window.text.warning,
                    "You would like create a new camera?",
                    function() {
                        popupEasy.close();
                        
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

        if (apparatusNumber > 0)
            $("#form_apparatus_selection_number").val(apparatusNumber);
        else
            $("#form_apparatus_selection_number").val(-1);
        
        $("#form_apparatus_selection").submit();
    };
    
    self.changeView = function() {
        if (utility.checkWidth() === "desktop") {
            $("#apparatus_video_area").removeClass("touch_disable");
            $("#apparatus_video_area").hide();
            
            $("#control_container").show();
        }
        else {
            if (utility.getIsMobile() === false) {
                $("#apparatus_control_swipe_switch").parents(".bootstrap-switch").hide();
                $(".panel-heading").find(".apparatus_control_picture").hide();
            }
            else
                $("#control_container").hide();
            
            if (videoAreaEnabled === true) {
                $("#apparatus_video_area").show();
                $("#apparatus_video_area").addClass("touch_disable");
            }
        }
    };
    
    // Functions private
    function statusSend(form, lastSelectOption, createNew) {
        apparatusNumber = $("#form_apparatus_selection_number").val();
        
        ajax.send(
            true,
            true,
            form.prop("action"),
            form.prop("method"),
            JSON.stringify(form.serializeArray()),
            "json",
            false,
            function() {
                $("#apparatus_video_result").html("");
                $("#apparatus_control_result").html("");
                $("#apparatus_profile_result").html("");
                $("#apparatus_file_result").html("");
            },
            function(xhr) {
                if (createNew === true) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    apparatusNumber = lastSelectOption + 1;
                    
                    $("<option>", {
                        'value': apparatusNumber,
                        'text': "Camera " + apparatusNumber
                    }).appendTo($("#form_apparatus_selection_number"));
                    
                    $("#form_apparatus_selection_number").val(apparatusNumber);
                }
                else {
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.values !== undefined && xhr.response.values.motionDetectionStatus !== undefined)
                        labelStatus(xhr.response.values.motionDetectionStatus);
                    else
                        labelStatus();
                }

                $("#apparatus_video_result").load(window.url.root + "/Resources/views/render/video.php", function() {
                    video();
                });

                $("#apparatus_control_result").load(window.url.root + "/Resources/views/render/control.php", function() {
                    control();
                });

                $("#apparatus_profile_result").load(window.url.root + "/Resources/views/render/apparatus_profile.php", function() {
                    apparatusProfile();
                });

                $("#apparatus_file_result").load(window.url.root + "/Resources/views/render/file.php", function() {
                    file();
                });
                
                $("#apparatus_userProfile_result").load(window.url.root + "/Resources/views/render/userProfile.php", function() {
                    userProfile();
                });
                
                $("#apparatus_userManagement_result").load(window.url.root + "/Resources/views/render/userManagement.php", function() {
                    userManagement();
                });
                
                $("#apparatus_setting_result").load(window.url.root + "/Resources/views/render/setting.php", function() {
                    setting();
                });
            },
            null,
            null
        );
    }
    
    function video() {
        $("#apparatus_video").on("error", function() {
            utility.imageRefresh("#apparatus_video", 2);
        });
    }
    
    function control() {
        $("#apparatus_control_swipe_switch").bootstrapSwitch("state", false);
        
        if (utility.getIsMobile() === false)
            $("#apparatus_control_swipe_switch").parents(".bootstrap-switch").hide();
        
        move("#apparatus_control_move_up", new Array(0, 1));
        move("#apparatus_control_move_right_up", new Array(6, 7, 0, 1));
        move("#apparatus_control_move_right", new Array(6, 7));
        move("#apparatus_control_move_right_down", new Array(6, 7, 2, 3));
        move("#apparatus_control_move_down", new Array(2, 3));
        move("#apparatus_control_move_left_down", new Array(4, 5, 2, 3));
        move("#apparatus_control_move_left", new Array(4, 5));
        move("#apparatus_control_move_left_up", new Array(4, 5, 0, 1));
        
        $("#apparatus_control_swipe_switch").on("switchChange.bootstrapSwitch", "", function(event, state) {
            if (state === true && utility.getIsMobile() === true) {
                videoAreaEnabled = true;
                
                $("#apparatus_video_area").show();
                $("#apparatus_video_area").addClass("touch_disable");
                
                $("#apparatus_video_area").swipe({
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
                
                $("#apparatus_video_area").removeClass("touch_disable");
                $("#apparatus_video_area").hide();
            }
        });
        
        $(".apparatus_control_picture").on("click", "", function() {
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
        utility.wordTag("#form_apparatus_profile_userId");
        
        $("#form_apparatus_profile_motionDetectionStatus").bootstrapSwitch();
        
        $("#form_apparatus_profile_motionDetectionStatus").on("switchChange.bootstrapSwitch", "", function(event, state) {
            switchStatus(state);
        });
        
        switchStatus(detectionStatus);
        
        $("#form_apparatus_profile").on("submit", "", function(event) {
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
                    
                    labelStatus();
                },
                null,
                null
            );
        });
        
        $("#apparatus_deletion").on("click", "", function() {
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
                            ajax.reply(xhr, "");
                            
                            $("#form_apparatus_selection_number").find("option[value=" + apparatusNumber + "]").remove();
                            $("#form_apparatus_selection_number").val(-1);
                            
                            $("#apparatus_video_result").html("");
                            $("#apparatus_control_result").html("");
                            $("#apparatus_profile_result").html("");
                            $("#apparatus_file_result").html("");
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
        tableAndPagination.init(window.url.root + "/Requests/IpCameraRequest.php", "#apparatus_file_table", true);
        tableAndPagination.search(true);
        tableAndPagination.pagination(true);
        tableAndPagination.sort(true);
        
        $(document).on("click", "#apparatus_file_table .refresh", function() {
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
                    ajax.reply(xhr, "");
                    
                    tableAndPagination.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#apparatus_file_table .delete_all", function() {
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
        
        $(document).on("click", "#apparatus_file_table .apparatus_file_download", function() {
            var path = window.path.documentRoot + "/motion/camera_" + apparatusNumber;
            var name = $(this).parents("tr").find(".name_column").text();
            
            download.send(path, name);
        });
        
        $(document).on("click", "#apparatus_file_table .apparatus_file_delete", function() {
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
        
        $("#form_userProfile_data").on("submit", "", function(event) {
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
        $("#form_user_management_selection").on("submit", "", function(event) {
            event.preventDefault();
            
            var form = $(this);
            
            if (parseInt($("#form_user_management_selection_id").val()) === 0) {
                popupEasy.create(
                    window.text.warning,
                    "You would like create a new user?",
                    function() {
                        popupEasy.close();

                        userManagementLogic(form);
                    },
                    function() {
                        popupEasy.close();
                    }
                );
            }
            else
                userManagementLogic(form);
        });
    }
    
    function userManagementLogic(form) {
        ajax.send(
            true,
            true,
            form.prop("action"),
            form.prop("method"),
            JSON.stringify(form.serializeArray()),
            "json",
            false,
            function() {
                $("#user_management_selection_result").html("");
            },
            function(xhr) {
                ajax.reply(xhr, "#" + event.currentTarget.id);

                if (xhr.response.render !== undefined) {
                    $("#user_management_selection_result").load(window.url.root + "/Resources/views/render/" + xhr.response.render, function() {
                        utility.wordTag("#form_user_management_roleUserId");

                        $("#form_user_management").on("submit", "", function(event) {
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
                    });
                }
            },
            null,
            null
        );
    }
    
    function setting() {
        $("#form_apparatus_setting").on("submit", "", function(event) {
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
                    ajax.reply(xhr, "");
                },
                null,
                null
            );
        });
    }
    
    function labelStatus(value) {
        if (value === undefined)
            detectionStatus = $("#form_apparatus_profile_motionDetectionStatus").val();
        else
            detectionStatus = value;
        
        if (detectionStatus === "start")
            $("#apparatus_detection_status").text("Active.");
        else
            $("#apparatus_detection_status").text("Not active.");
    }
    
    function switchStatus(value) {
        if (value === true || value === "start") {
            if ($("#form_apparatus_profile_motionDetectionStatus").val() === "" || $("#form_apparatus_profile_motionDetectionStatus").val() === "pause")
                $("#form_apparatus_profile_motionDetectionStatus").click();
            
            $("#form_apparatus_profile_motionDetectionStatus").prop("value", "start");
            $("#form_apparatus_profile_motionDetectionStatus").parents(".bootstrap-switch-wrapper").next().prop("value", "start");
        }
        else {
            if ($("#form_apparatus_profile_motionDetectionStatus").val() === "start")
                $("#form_apparatus_profile_motionDetectionStatus").click();
            
            $("#form_apparatus_profile_motionDetectionStatus").prop("value", "pause");
            $("#form_apparatus_profile_motionDetectionStatus").parents(".bootstrap-switch-wrapper").next().prop("value", "pause");
        }
    }
    
    function move(tag, elements) {
        if (elements.length === 2) {
            $(tag).on("mousedown", "", function(event) {
                event.preventDefault();
                
                utility.postIframe(
                    window.url.apparatusControl + "&command=" + elements[0],
                    "post",
                    {
                        'command': elements[0]
                    }
                );
            });
            
            $(tag).on("mouseup", "", function(event) {
                event.preventDefault();
                
                utility.postIframe(
                    window.url.apparatusControl + "&command=" + elements[1],
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
                    window.url.apparatusControl + "&command=" + elements[0],
                    "post",
                    {
                        'command': elements[0]
                    }
                );
        
                utility.postIframe(
                    window.url.apparatusControl + "&command=" + elements[2],
                    "post",
                    {
                        'command': elements[2]
                    }
                );
            });
            
            $(tag).on("mouseup", "", function(event) {
                event.preventDefault();
                
                utility.postIframe(
                    window.url.apparatusControl + "&command=" + elements[1],
                    "post",
                    {
                        'command': elements[1]
                    }
                );
                
                utility.postIframe(
                    window.url.apparatusControl + "&command=" + elements[3],
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
            window.url.apparatusControl + "&command=" + value,
            "post",
            {
                'command': value
            }
        );
    }
    
    function swipeMoveEnd() {
        $("#apparatus_video_area").on("touchend touchcancel", "", function(event) {
            if (swipeMoveValue !== -1) {
                utility.postIframe(
                    window.url.apparatusControl + "&command=" + swipeMoveValue,
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