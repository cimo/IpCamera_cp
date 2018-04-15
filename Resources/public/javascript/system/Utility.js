var utility = new Utility();

function Utility() {
    // Vars
    var self = this;
    
    var watchExecuted = false;
    
    var touchMove = false;
    
    // Properties
    self.getTouchMove = function() {
        return touchMove;
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
    
    self.checkMobile = function(fix = false) {
        var isMobile = false;
        
        var navigatorUserAgent = navigator.userAgent;
        
        if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(navigatorUserAgent)
            || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigatorUserAgent.substr(0, 4))) {
            
            isMobile = true;
            
            if (fix === true)
                swipeFix();
        }

        return isMobile;
    };
    
    self.checkWidthType = function() {
        var widthType = "";
        
        if (window.matchMedia("(max-width: " + window.setting.widthMobile + "px)").matches === true)
            widthType = "mobile";
        else
            widthType = "desktop";
        
        return widthType;
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
    
    self.selectSortable = function(tagSelect, buttonTarget, tagInput, isCreation) {
        var selected = $(tagSelect).find("option:selected");
        
        $(tagSelect).find("option").prop("disabled", "disabled");
        
        if (isCreation === true && buttonTarget === null) {
            selected = $(tagSelect).find("option[value='']");
            
            $(tagSelect).find("option[value='']").appendTo(tagSelect);
        }
        
        $(tagSelect).find("option[value='" + selected.val() + "']").not(":selected").remove();
        
        if (selected.length > 0) {
            if (buttonTarget !== null)
                (buttonTarget.prop("id").indexOf("up") !== -1) ? selected.first().prev().before(selected) : selected.last().next().after(selected);
            
            var list = "";
            
            $.each($(tagSelect).find("option"), function(key, value) {
                list += $(value).val() + ",";
            });
            
            $(tagInput).val(list);
        }
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
        if ($(tag).val() !== undefined) {
            var inputValueSplit = $(tag).val().split(",");
            inputValueSplit.pop();
            
            $(tag + "_field").parent().find(".wordTag_label").remove();
            
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
            
            $(this).val($(this).find("option").eq(0).val());
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
                $(value).prop("src", window.url.root + "/Resources/public/images/templates/" + window.setting.template + "/error_404.png");
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
    
    self.goToAnchor = function(tag) {
        $("html, body").animate({
            scrollTop: $(tag).offset().top
        }, 1000);
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
        if (utility.checkWidthType() === "desktop") {
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