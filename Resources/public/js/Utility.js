/* global url, settings */

var utility = new Utility();

function Utility() {
    // Vars
    var self = this;
    
    var watchExecuted = false;
    
    var menuRootButtonOld = null;
    var modulePositionValue = "";
    
    // Properties
    
    // Functions public
    self.watch = function(tag, callback) {
        if (watchExecuted === false) {
            if (callback !== undefined)
                $(tag).bind("DOMSubtreeModified", callback());
            
            watchExecuted = true;
        }
    };
    
    self.linkPreventDefault = function() {
        $("a[href^='#']").on("click", "", function(event) {
            event.preventDefault();
        });
    };
    
    self.mobileCheck = function(fix) {
        var isMobile = false;
        
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) === true) {
            isMobile = true;
            
            if (fix === true)
                swipeFix();
        }
        
        return isMobile;
    };
    
    self.widthCheck = function(width) {
        if (window.matchMedia("(min-width: " + width + "px)").matches === true)
            return "desktop";
        else
            return "mobile";
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
    
    self.urlParameters = function(index) {
        var pathName = window.location.pathname;
        var pathNameSplit = pathName.split("/").filter(Boolean).reverse();
        
        if (index > (pathNameSplit.length - 1))
            return "";
        else
            return pathNameSplit[index];
        
        return "";
    };
    
    self.selectWithDisabledElement = function(id, xhr) {
        var options = $(id).find("option");
        
        var disabled = false;
        var optionLength = 0;
        
        $(options).each(function(key, val) {
            var optionValue = parseInt(val.value);
            var optionText = val.text;
            var idElementSelected = parseInt(xhr.response.values.id);
            
            if (optionValue === idElementSelected) {
                disabled = true;
                optionLength = optionText.length;
            }
            else if (optionText.length < optionLength)
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
            columnsObject.addClass("sortable_column_enabled");
            moduleSettingsObject.show();
            
            columnsObject.sortable({
                'cursor': "move",
                cursorAt: {
                    'top': 0,
                    'left': 0
                },
                'placeholder': "sortable_placeholder",
                'tolerance': "pointer",
                'revert': true,
                'connectWith': ".sortable_column",
                'handle': ".module_move",
                start: function(event, ui) {
                    ui.placeholder.height(ui.item.height());
                },
                helper: function(event, ui) {
                    var clone = $(ui).clone();
                    clone.css({'position': "absolute"});
                    return clone.get(0);
                },
                stop: function(event, ui) {
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
                
                $.each(columnsObject, function(key, value) {
                    var panels = $(value).find(".module_settings").parent();
                    $.each(panels, function(keySub, valueSub) {
                        var id = valueSub.id.replace("panel_id_", "");
                        
                        if (key === 0)
                            header.push(id);
                        else if (key === 1)
                            left.push(id);
                        else if (key === 2)
                            center.push(id);
                        else if (key === 3)
                            right.push(id);
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
        
        $(tag + "_field").focusout(function(event) {
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
    
    // Bootstrap fix
    self.bootstrapMenuFix = function(tag) {
        $("ul.dropdown-menu [data-toggle=dropdown]").on("click", "", function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            if ($(this).parent().hasClass("open") === false)
                $(this).parent().addClass("open");
            else
                $(this).parent().removeClass("open");
        });
        
        $(window).resize(function() {
            $(tag).find("li").removeClass("open");
        });
    };
    
    self.bootstrapMenuActiveFix = function(tag, menuTag) {
        var elements = $(tag).find("li a");
        
        var url = window.location.href;
        
        if (url.substring(url.length - 1) === "/")
            url = url.substring(0, url.length - 1);
        
        var lastUrlParameter = url.substring(url.lastIndexOf("/") + 1);
        
        $(elements).each(function(key, value) {
            var lastHrefParameter = $(value).prop("href").substring($(value).prop("href").lastIndexOf("/") + 1);
            
            if (lastHrefParameter === lastUrlParameter) {
                if (menuTag === true)
                    elements.parent().removeClass("active");
                
                if ($(value).parents(".dropdown") !== undefined)
                    $(value).parents(".dropdown").addClass("active");
                
                $(value).parent().addClass("active");
                
                menuRootButtonOld = $(value).parent();
            }
            else if (menuTag === false && url.indexOf("/" + lastHrefParameter + "/") === -1)
                $(value).parent().removeClass("active");
        });
        
        elements.on("click", "", function() {
            if ($(this).parents(".dropdown").hasClass("active") === false && $(this).parent().find("li").hasClass("active") === false)
                elements.parent().removeClass("active");
            
            if ($(this).prop("target") === undefined)
                $(this).parent().addClass("active");
        });
        
        $(document).mouseup(function(event) {
            var container = $(".nav.navbar-nav");
            
            if (container.is(event.target) === false && container.has(event.target).length === 0) {
                container.find("li").removeClass("active");
                
                if ($(menuRootButtonOld).parents(".dropdown") !== undefined)
                    $(menuRootButtonOld).parents(".dropdown").addClass("active");

                $(menuRootButtonOld).addClass("active");
            }
        });
    };
    
    self.bootstrapAddClassIsVisibleFix = function(idTarget, idResult, classCss) {
        checkIsBlockAndAddClass(idTarget, idResult, classCss);
        
        $(window).resize(function() {
            checkIsBlockAndAddClass(idTarget, idResult, classCss);
        });
    };
    
    // Functions private
    function checkIsBlockAndAddClass(idTarget, idResult, classCss) {
        if ($(idTarget).css("display") === "block")
            $(idResult).addClass(classCss);
        else
            $(idResult).removeClass(classCss);
    }
    
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
                            cancelTouch();

                            if (offsetX > 0)
                                options.left();
                            else
                                options.right();
                        }
                        else if (Math.abs(offsetY) >= (options.min.y || options.min)) {
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

                element.on("mousedown touchstart", onTouchStart);
            });
        };
    };
}