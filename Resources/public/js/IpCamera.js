/* global utility, ajax, popupEasy, download */

var ipCamera = new IpCamera();

function IpCamera() {
    // Vars
    var self = this;
    
    var currentId = window.session.ipCameraNumber;
    var swipeMoveValue = -1;
    
    // Properties
    
    // Functions public
    self.status = function() {
        var lastValue = parseInt($("#form_cameras_selection_id").find("option").last().val());
		
		$("#form_cameras_selection").on("submit", "", function(event) {
			event.preventDefault();
			
			var form = $(this);
			
			if (parseInt($("#form_cameras_selection_id").val()) === 0) {
				popupEasy.create(
					window.text.warning,
					window.text.ipCameraCreateNew,
					function() {
						popupEasy.close();
						
						createNew = true;
						
						statusSend(form, lastValue, true);
					},
					function() {
						popupEasy.close();
					}
				);
			}
			else
				statusSend(form, lastValue, false);
		});
		
		if (lastValue > 0) {
			$("#form_cameras_selection_id").val(currentId);
			$("#form_cameras_selection").submit();
		}
		else
			$("#form_cameras_selection_id").val(-1);
    };
    
    // Functions private
    function statusSend(form, lastValue, createNew) {
        currentId = $("#form_cameras_selection_id").val();
        
        ajax.send(
            form.attr("action"),
            form.attr("method"),
            JSON.stringify(form.serializeArray()),
            true,
            function() {
                $("#camera_video_result").html("");
                $("#camera_controls_result").html("");
                $("#camera_profile_result").html("");
                $("#camera_files_result").html("");
            },
            function(xhr) {
                if (createNew === true) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    currentId = lastValue + 1;
                    
                    $("<option>", {
                        'value': currentId,
                        'text': "Camera " + currentId
                    }).appendTo($("#form_cameras_selection_id"));
                    
                    $("#form_cameras_selection_id").val(currentId);
                }
                else
                    ajax.reply(xhr, "");

                $("#camera_video_result").load(window.url.root + "/Resources/views/render/video.php", function() {
                    video();
                });

                $("#camera_controls_result").load(window.url.root + "/Resources/views/render/controls.php", function() {
                    controls();
                });

                $("#camera_profile_result").load(window.url.root + "/Resources/views/render/profile.php", function() {
                    profile();
                });

                $("#camera_files_result").load(window.url.root + "/Resources/views/render/files.php", function() {
                    files();
                });
            },
            null,
            null
        );
    }
    
    function video() {
        
    }
    
    function controls() {
        $("#camera_control_swipe_switch").bootstrapSwitch("state", false);
        
        move("#camera_control_move_up", new Array(0, 1));
        move("#camera_control_move_right_up", new Array(6, 7, 0, 1));
        move("#camera_control_move_right", new Array(6, 7));
        move("#camera_control_move_right_down", new Array(6, 7, 2, 3));
        move("#camera_control_move_down", new Array(2, 3));
        move("#camera_control_move_left_down", new Array(4, 5, 2, 3));
        move("#camera_control_move_left", new Array(4, 5));
        move("#camera_control_move_left_up", new Array(4, 5, 0, 1));
        
        $("#camera_control_swipe_switch").on("switchChange.bootstrapSwitch", "", function(event, state) {
            if (state === true) {
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
                $("#camera_video_area").removeClass("touch_disable");
                $("#camera_video_area").hide();
            }
        });
        
        $(".camera_control_picture").on("click", "", function() {
            ajax.send(
                window.url.root + "/Requests/IpCameraRequest.php?controller=controlsAction",
                "post",
                JSON.stringify({
                    'event': "picture",
                    'token': window.session.token
                }),
                true,
                null,
                function(xhr) {
                    ajax.reply(xhr, "");
                },
                null,
                null
            );
        });
    }
    
    function profile() {
        labelStatus();
        
        $("#form_camera_profile_detection_active").bootstrapSwitch();
        
        $("#form_camera_profile_detection_active").on("switchChange.bootstrapSwitch", "", function(event, state) {
            if (state === true) {
                $("#form_camera_profile_detection_active").attr("value", "start");
                $("#form_camera_profile_detection_active").parents(".bootstrap-switch-wrapper").next().attr("value", "start");
            }
            else {
                $("#form_camera_profile_detection_active").attr("value", "pause");
                $("#form_camera_profile_detection_active").parents(".bootstrap-switch-wrapper").next().attr("value", "pause");
            }
        });
        
        $("#form_camera_profile").on("submit", "", function(event) {
            event.preventDefault();

            ajax.send(
                $(this).attr("action"),
                $(this).attr("method"),
                JSON.stringify($(this).serializeArray()),
                true,
                null,
                function(xhr) {
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
                window.text.ipCameraDelete,
                function() {
                    popupEasy.close();

                    ajax.send(
                        window.url.root + "/Requests/IpCameraRequest.php?controller=deleteAction",
                        "post",
                        JSON.stringify({
                            'token': window.session.token
                        }),
                        true,
                        null,
                        function(xhr) {
                            ajax.reply(xhr, "");
                            
                            $("#form_cameras_selection_id").find("option[value=" + currentId + "]").remove();
                            $("#form_cameras_selection_id").val(-1);
                            
                            $("#camera_video_result").html("");
                            $("#camera_controls_result").html("");
                            $("#camera_profile_result").html("");
                            $("#camera_files_result").html("");
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
    
    function files() {
        var table = new Table();
        table.init(window.url.root + "/Requests/IpCameraRequest.php", "#camera_files_table");
        table.search();
        table.pagination();
        table.sort();
        
        $("#camera_files_refresh").on("click", "", function() {
            ajax.send(
                window.url.root + "/Requests/IpCameraRequest.php?controller=filesAction",
                "post",
                JSON.stringify({
                    'event': "refresh",
                    'name': "",
                    'token': window.session.token
                }),
                true,
                null,
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    table.populate(xhr);
                },
                null,
                null
            );
        });
        
        $("#camera_files_delete_all").on("click", "", function() {
            popupEasy.create(
                window.text.warning,
                window.text.ipCameraDeleteAllFile,
                function() {
                    popupEasy.close();
                    
                    ajax.send(
                        window.url.root + "/Requests/IpCameraRequest.php?controller=filesAction",
                        "post",
                        JSON.stringify({
                            'event': "deleteAll",
                            'name': "",
                            'token': window.session.token
                        }),
                        true,
                        null,
                        function(xhr) {
                            ajax.reply(xhr, "");
                            
                            table.populate(xhr);
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
        
        $(document).on("click", ".camera_files_download", function() {
            var path = window.path.documentRoot + "/motion/camera_" + currentId;
            var name = $(this).parents("tr").find(".name_column").text();
            
            download.send(path, name);
        });
        
        $(document).on("click", ".camera_files_delete", function() {
            var name = $.trim($(this).parents("tr").find(".name_column").text());
            
            popupEasy.create(
                window.text.warning,
                window.text.ipCameraDeleteFile,
                function() {
                    popupEasy.close();
                    
                    ajax.send(
                        window.url.root + "/Requests/IpCameraRequest.php?controller=filesAction",
                        "post",
                        JSON.stringify({
                            'event': "delete",
                            'name': name,
                            'token': window.session.token
                        }),
                        true,
                        null,
                        function(xhr) {
                            ajax.reply(xhr, "");
                            
                            table.populate(xhr);
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
    
    function labelStatus() {
        var detectionActiveValue = $("#form_camera_profile_detection_active").val();
        
        if (detectionActiveValue === "start")
            $("#camera_detection_status").text(window.text.ipCameraStatusActive);
        else
            $("#camera_detection_status").text(window.text.ipCameraStatusNotActive);
    }
    
    function move(tag, elements) {
        if (elements.length === 2) {
            $(tag).on("mousedown", "", function(event) {
                event.preventDefault();
                
                utility.postIframe(
                    window.url.ipCameraControl + "&command=" + elements[0],
                    "post",
                    {
                        'command': elements[0]
                    }
                );
            });
            
            $(tag).on("mouseup", "", function(event) {
                event.preventDefault();
                
                utility.postIframe(
                    window.url.ipCameraControl + "&command=" + elements[1],
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
                    window.url.ipCameraControl + "&command=" + elements[0],
                    "post",
                    {
                        'command': elements[0]
                    }
                );
        
                utility.postIframe(
                    window.url.ipCameraControl + "&command=" + elements[2],
                    "post",
                    {
                        'command': elements[2]
                    }
                );
            });
            
            $(tag).on("mouseup", "", function(event) {
                event.preventDefault();
                
                utility.postIframe(
                    window.url.ipCameraControl + "&command=" + elements[1],
                    "post",
                    {
                        'command': elements[1]
                    }
                );
                
                utility.postIframe(
                    window.url.ipCameraControl + "&command=" + elements[3],
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
            window.url.ipCameraControl + "&command=" + value,
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
                    window.url.ipCameraControl + "&command=" + swipeMoveValue,
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