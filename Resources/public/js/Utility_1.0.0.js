/* global url, settings */

var utility = new Utility();

function Utility() {
    // Vars
    var self = this;
    
    var watchExecuted = false;
    
    var isMobile = false;
    
    var modulePositionValue = "";
    
    var touchMove = false;
    
    var widthType = "";
    
    // Properties
    self.getIsMobile = function() {
        return isMobile;
    };
    
    self.getTouchMove = function() {
        return touchMove;
    };
    
    self.getWidthType = function() {
        return widthType;
    };
    
    // ---
    
    self.setWidthType = function(value) {
        widthType = value;
    };
    
    // Functions public
    self.linkPreventDefault = function() {
        $("a[href^='#']").on("click", "", function(event) {
            event.preventDefault();
        });
    };
    
    self.watch = function(tag, callback) {
        if (watchExecuted === false) {
            if (callback !== undefined)
                $(tag).bind("DOMSubtreeModified", callback());
            
            watchExecuted = true;
        }
    };
    
    self.checkMobile = function(fix) {
        isMobile = false;
        
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) === true) {
            isMobile = true;
            
            if (fix === true)
                swipeFix();
        }
        
        return isMobile;
    };
    
    self.checkWidth = function(width) {
        if (window.matchMedia("(max-width: " + width + "px)").matches === true)
            widthType = "mobile";
        else
            widthType = "desktop";
    };
    
    self.postIframe = function(action, method, elements) {
        var iframeTag = "iframe_commands_" + (new Date()).getTime();
        
        $("<iframe>", {
            'id': iframeTag,
            'name': iframeTag,
            'style': "display: none;"
        }).appendTo("body");
        
        var formTag = "form_commands_" + + (new Date()).getTime();
        
        $("<form>", {
            'id': formTag,
            'target': iframeTag,
            'action': action,
            'method': method
        }).appendTo("body");
        
        $.each(elements, function(key, value) {
            $("<input>", {
                'type': "hidden",
                'name': key,
                'value': value
            }).appendTo("#" + formTag);
        });
        
        $("#" + formTag).submit();
    };
    
    self.urlParameters = function(language) {
        var href = window.location.href;
        
        var start = href.indexOf("/" + language + "/");
        var split = href.substring(start, href.length).split("/");
        split.shift();
        
        return split;
    };
    
    self.selectWithDisabledElement = function(id, xhr) {
        var options = $(id).find("option");
        
        var disabled = false;
        var optionLength = 0;
        
        $.each(options, function(key, val) {
            var optionValue = parseInt(val.value);
            var optionText = val.text.substr(0, val.text.indexOf("-|") + 2);
            var idElementSelected = parseInt(xhr.response.values.id);
            
            if (optionValue === idElementSelected) {
                disabled = true;
                optionLength = optionText.length;
            }
            else if (optionText.length <= optionLength)
                disabled = false;
            
            if (disabled === true)
                $(id).find("option").eq(key).prop("disabled", true);
        });
    };
    
    self.removeElementAndResetIndex = function(element, index) {
        element.length = Object.keys(element).length;
        element.splice = [].splice;

        element.splice(index, 1);

        delete element.length;
        delete element.splice;
        
        return element;
    };
    
    self.objectToArray = function(items) {
        var array = $.map(items, function(elements) {
            return elements;
        });
        
        return array;
    };
    
    self.isIntoView = function(id) {
        if ($(id) === null)
            return false;
	
	var viewport = {
            'top' : $(window).scrollTop(),
            'left' : $(window).scrollLeft()
	};
	viewport.right = viewport.left + $(window).width();
	viewport.bottom = viewport.top + $(window).height();
	
	var bounds = $(id).offset();
        bounds.right = bounds.left + $(id).outerWidth();
        bounds.bottom = bounds.top + $(id).outerHeight();

        return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));
    };
    
    self.sortableDragModules = function(type, inputId) {
        var columnsObject = $(".sortable_column");
        var moduleSettingsObject = $(".module_settings");
        
        if (type === true) {
            var elementUi = null;
            
            columnsObject.addClass("sortable_column_enabled");
            moduleSettingsObject.show();
            
            columnsObject.sortable({
                'cursor': "move",
                'placeholder': "sortable_placeholder",
                'tolerance': "pointer",
                'revert': true,
                'connectWith': ".sortable_column",
                'handle': ".module_move",
                cursorAt: {
                    'top': 0,
                    'left': 0
                },
                start: function(event, ui) {
                    ui.placeholder.height(ui.item.height());
                },
                helper: function(event, ui) {
                    if ($(ui).hasClass("display_desktop") === true) {
                        elementUi = $(ui);
                        elementUi.removeClass("display_desktop");
                    }
                    else
                        elementUi = null;
                    
                    var clone = $(ui).clone();
                    clone.css({'position': "absolute"});
                    
                    return clone.get(0);
                },
                stop: function(event, ui) {
                    if (elementUi !== null)
                        elementUi.addClass("display_desktop");
                    
                    ui.placeholder.height(0);
                }
            }).disableSelection();
        }
        else {
            if (columnsObject.data("ui-sortable")) {
                columnsObject.sortable("destroy");
                
                columnsObject.removeClass("sortable_column_enabled");
                moduleSettingsObject.hide();
                
                var header = new Array();
                var left = new Array();
                var center = new Array();
                var right = new Array();
                
                $.each(columnsObject, function(keyA, valueA) {
                    var panels = $(valueA).children().find(".module_settings").parent();

                    $.each(panels, function(keyB, valueB) {
                        if ($(valueB).parent().hasClass("dropdown-menu") === false) {
                            var id = valueB.id.replace("panel_id_", "");
                            
                            if ($(valueA).parents(".container_header").length > 0)
                                header.push(id);
                            else if ($(valueA).parents(".container_left").length > 0)
                                left.push(id);
                            else if ($(valueA).parents(".container_center").length > 0)
                                center.push(id);
                            else if ($(valueA).parents(".container_right").length > 0)
                                right.push(id);
                        }
                    });
                });
                
                $(inputId + "Header").val(header);
                $(inputId + "Left").val(left);
                $(inputId + "Center").val(center);
                $(inputId + "Right").val(right);
            }
        }
    };
    
    self.sortableButtonModules = function(containerId, elementsId) {
        var containerTag = containerId.replace("#", "");
        
        sortModulesFieldsAssignment(containerTag, elementsId);

        $(containerId + "_button_up").on("click", "", function() {
            var current = $(containerId).find("input:checked").parent();
            current.prev().before(current);

            sortModulesFieldsAssignment(containerTag, elementsId);
        });

        $(containerId + "_button_down").on("click", "", function() {
            var current = $(containerId).find("input:checked").parent();
            current.next().after(current);

            sortModulesFieldsAssignment(containerTag, elementsId);
        });
    };
    
    self.selectOnlyOneElement = function(tag) {
        $(tag).on("click", "", function(event) {
            if ($(event.target).is("input") === true) {
                $.each($(tag).find("input"), function(key, value) {
                    $(value).not(event.target).prop("checked", false);
                });
            }
        });
    };
    
    self.fileNameFromSrc = function(attribute, extension) {
        var value = attribute.replace(/\\/g, "/");
        value = value.substring(value.lastIndexOf("/") + 1);
        
        return extension ? value.replace(/[?#].+$/, "") : value.split(".")[0];
    };
    
    self.wordTag = function(tag, type) {
        $(tag).hide();
        
        if ($(tag).val() !== undefined) {
            var inputValueSplit = $(tag).val().split(",");
            inputValueSplit.pop();
            
            $.each(inputValueSplit, function(key, value) {
                var index = value;
                
                if (type !== "input")
                    value = $(tag + "_field").find("option").eq(index).text();
                
                $(tag + "_field").before("<span id=\"wordTag_" + index + "\" class=\"wordTag_label\"><div class=\"display_inline\">" + value + "</div><i id=\"wordTag_close_" + index + "\" class=\"wordTag_close fa fa-remove\"></i></span>");
            
                $("#wordTag_close_" + index).on("click", "", function(event) {
                    var index = parseInt(event.target.id.replace("wordTag_close_", ""));
                    var value = $(tag).val().replace(index + ",", "");
                    
                    $(tag).val(value);
                    
                    $(event.target).parents(".wordTag_label").remove();
                });
            });
        }
        
        if (type === "input") {
            $(tag + "_field").on("keyup", "", function() {
                $(this).val($(this).val().toUpperCase());
            });
        }
        
        $(tag + "_field").change(function(event) {
            var inputValue = $(tag).val();
            var fieldValue = $(tag + "_field").val();
            
            if (fieldValue !== "" && inputValue.indexOf(fieldValue) === -1) {
                var inputValueSplit = $(tag).val().split(",");
                inputValueSplit.pop();
                
                $(tag).val(inputValue + fieldValue + ",");
                
                var index = inputValueSplit.length;
                
                if (type !== "input") {
                    index = $(event.target).val();
                    fieldValue = $(tag + "_field").find("option").eq($(event.target).val()).text();
                }
                
                $(tag + "_field").before("<span id=\"wordTag_" + index + "\" class=\"wordTag_label\"><div class=\"display_inline\">" + fieldValue + "</div><i id=\"wordTag_close_" + index + "\" class=\"wordTag_close fa fa-remove\"></i></span>");
                $(tag + "_field").val("");
                
                $("#wordTag_close_" + index).on("click", "", function(event) {
                    var index = parseInt(event.target.id.replace("wordTag_close_", ""));
                    var value = $(tag).val().replace(index + ",", "");
                    
                    $(tag).val(value);
                    
                    $(event.target).parents(".wordTag_label").remove();
                });
            }
        });
    };
    
    self.accordion = function() {
        $(".accordion_container").find(".title").on("click", "", function() {
            if ($(this).find(".icon").hasClass("fa-chevron-circle-down") === true) {
                $(this).find(".icon").removeClass("fa-chevron-circle-down");
                $(this).find(".icon").addClass("fa-chevron-circle-up");
            }
            else {
                $(this).find(".icon").removeClass("fa-chevron-circle-up");
                $(this).find(".icon").addClass("fa-chevron-circle-down");
            }
            
            $(this).next().slideToggle(500, function() {
            });
        });
    };
    
    self.progressBar = function(id, start, end) {
        if (start !== undefined && end !== undefined) {
            var progress = start / end;
            var percentage = Math.ceil(progress * 100);
        }
        else
            percentage = 0;
        
        $("#" + id).find(".progress-bar").css("width", percentage + "%");
        $("#" + id).find("span").text(percentage + "%");
    };
    
    self.imageError = function(elements) {
        elements.on("error", "", function() {
            $.each($(this), function(key, value) {
                $(value).prop("src", window.url.webBundle + "/images/templates/" + window.settings.template + "/error_404.png");
            });
        });
    };
    
    self.imageRefresh = function(tag, length) {
        if (tag !== "") {
            var src = $(tag).prop("src");
            
            var srcSplit = src.split("?");

            if (srcSplit.length > length)
                src = srcSplit[0] + "?" + srcSplit[1];
            
            $(tag).prop("src", src + "?" + new Date().getTime());
        }
    };
    
    // Bootstrap fix
    self.bootstrapMenuFix = function(tags) {
        var menuButtonsOld = new Array();
        
        $.each(tags, function(keyA, valueA) {
            var elements = $(valueA[0]).find("li a");

            var url = window.location.href;

            var urlElements = url.split("/").reverse();

            $.each(elements, function(keyB, valueB) {
                var lastHrefParameter = $(valueB).prop("href").substring($(valueB).prop("href").lastIndexOf("/") + 1);

                if (lastHrefParameter !== "" && $.inArray(lastHrefParameter, urlElements) !== -1) {
                    if ($(valueB).parents(".dropdown").length > 0)
                        $(valueB).parents(".dropdown").addClass("active");

                    $(valueB).parent().addClass("active");

                    menuButtonsOld[keyA] = $(valueB).parent();
                }

                if (menuButtonsOld[keyA] === "" && keyB === (elements.length - 1)) {
                    $(elements[0]).parent().addClass("active");

                    menuButtonsOld[keyA] = $(elements[0]).parent();
                }
            });
            
            var hasClass = false;
            
            $.each($(valueA[0]).find("li"), function(keyB, valueB) {
                if ($(valueB).hasClass("active") === true) {
                    hasClass = true;

                    return false;
                }
            });

            if (hasClass === false && $(valueA[0]).find("li").length > 0) {
                $(valueA[0]).find("li").eq(0).addClass("active");
                
                menuButtonsOld[0] = $(valueA[0]).find("li").eq(0);
            }
        });
        
        $("#menu_root_nav_button").on("click", "", function() {
            if ($(tags[0][0]).find(".navbar-nav").css("display") === "none") {
                $(this).removeClass("collapsed");
                
                $(tags[0][0]).find(".navbar-nav").show();
            }
            else {
                $(this).addClass("collapsed");
                
                $(tags[0][0]).find(".navbar-nav").hide();
            }
        });
        
        $("#menu_root_navbar").find(".dropdown-menu span").remove();
        
        $("#menu_root_navbar").find("ul li.dropdown").on("click", "", function(event) {
            if ($(event.target).hasClass("dropdown-toggle") === true) {
                event.preventDefault();
                event.stopPropagation();
                
                $("#menu_root_navbar").find(".dropdown-menu").parent("li.dropdown").not($(event.target).parents(".dropdown")).removeClass("open");
                
                if ($(this).hasClass("open") === false)
                    $(this).addClass("open");
                else
                   $(this).removeClass("open");
            }
        });
        
        $(document).on("click", "", function(event) {
            if (event.target.id !== "menu_root_navbar" && $(event.target).is("a") === false) {
                $.each(menuButtonsOld, function(key, value) {
                    $(menuButtonsOld[key]).addClass("active");
                    $(menuButtonsOld[key]).parents(".dropdown").addClass("active");
                });
            }
            else
                $(event.target).parents("li").addClass("active");
        });
    };
    
    self.bootstrapMenuFixChangeView = function(tag) {
        if (widthType === "desktop") {
            $(tag).find(".navbar-nav").show();
            $(tag).find(".open").removeClass("open");
        }
        else {
            $(tag).parent().find(".navbar-toggle").addClass("collapsed");
            $(tag).find(".navbar-nav").hide();
            $(tag).find(".open").removeClass("open");
        }
    };
    
    // Functions private
    function sortModulesFieldsAssignment(containerTag, elementsId) {
        var sortParentList = $("#" + containerTag).find("li");
        var sortListElements = new Array();
        
        $.each(sortParentList, function(key, value) {
            var id = $(value).prop("id").replace(containerTag + "_", "");
            
            sortListElements.push(id);
        });
        
        if ($("#" + containerTag).parent().css("display") === "none") {
            modulePositionValue = $(elementsId[0]).find("option:selected").val();
            
            $(elementsId[0]).find("option").removeAttr("selected");
            $(elementsId[1]).val("");
        }
        else {
            if (modulePositionValue !== "")
                $(elementsId[0]).find("option[value='" + modulePositionValue + "']").prop("selected", true);
            
            $(elementsId[1]).val(sortListElements);
        }
    }
    
    // Jquery mobile fix
    function swipeFix() {
        var defaults = {
            min: {
                'x': 20,
                'y': 20
            },
            'left': $.noop,
            'right': $.noop,
            'up': $.noop,
            'down': $.noop
        }, isTouch = "ontouchend" in document;
        
        // Fix jquery > 3
        //$.event.props.push("touches");
        
        $.fn.swipe = function(options) {
            options = $.extend({}, defaults, options);

            return this.each(function() {
                var element = $(this);
                var startX;
                var startY;
                var isMoving = false;

                touchMove = false;

                function cancelTouch() {
                    element.off("mousemove.swipe touchmove.swipe", onTouchMove);
                    startX = null;
                    isMoving = false;
                }

                function onTouchMove(event) {
                    if (isMoving && event.touches !== undefined) {
                        var x = isTouch ? event.touches[0].pageX : event.pageX;
                        var y = isTouch ? event.touches[0].pageY : event.pageY;

                        var offsetX = startX - x;
                        var offsetY = startY - y;

                        if (Math.abs(offsetX) >= (options.min.x || options.min)) {
                            touchMove = true;

                            cancelTouch();

                            if (offsetX > 0)
                                options.left();
                            else
                                options.right();
                        }
                        else if (Math.abs(offsetY) >= (options.min.y || options.min)) {
                            touchMove = true;

                            cancelTouch();

                            if (offsetY > 0)
                                options.up();
                            else
                                options.down();
                        }
                    }
                }

                function onTouchStart(event) {
                    event.preventDefault();

                    if (event.touches !== undefined) {
                        startX = isTouch ? event.touches[0].pageX : event.pageX;
                        startY = isTouch ? event.touches[0].pageY : event.pageY;

                        isMoving = true;

                        element.on("mousemove.swipe touchmove.swipe", onTouchMove);
                    }
                }

                function onTouchEnd(event) {
                    if (event.touches !== undefined)
                        touchMove = false;
                }

                element.on("mousedown touchstart", onTouchStart);
                element.on("mouseup touchend", onTouchEnd);
            });
        };
    };
}