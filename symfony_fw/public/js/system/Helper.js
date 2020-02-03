"use strict";

/* global materialDesign, mdc */

const helper = new Helper();

function Helper() {
    // Vars
    let self = this;
    
    let touchMove;
    
    // Properties
    self.getTouchMove = function() {
        return touchMove;
    };
    
    // Functions public
    self.init = function() {
        touchMove = false;
    };
    
    self.linkPreventDefault = function() {
        $("a[href^='#']").on("click", "", function(event) {
            event.preventDefault();
        });
    };
    
    self.mutationObserver = function(type, element, callback) {
        let observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if ($.inArray(mutation.type, type) !== -1)
                    callback();
            });
        });
        
        observer.observe(element, {'attributes': true, 'childList': true, 'subtree': true, 'characterData': true});
    };
    
    self.checkMobile = function(fix) {
        let isMobile = false;
        
        let navigatorUserAgent = navigator.userAgent.toLowerCase();
        
        if (/(android|ipad|iphone|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(navigatorUserAgent)
            || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigatorUserAgent.substr(0, 4))) {
            
            isMobile = true;
            
            if (fix === true)
                swipeFix();
        }

        return isMobile;
    };
    
    self.checkWidthType = function(maxWidthOverride) {
        let widthType = "";
        
        let widthTmp = maxWidthOverride === undefined ? window.setting.widthMobile : maxWidthOverride;
        
        if (window.matchMedia("(max-width: " + widthTmp + "px)").matches === true)
            widthType = "mobile";
        else
            widthType = "desktop";
        
        return widthType;
    };
    
    self.postIframe = function(action, method, elements) {
        let iframeTag = "iframe_commands_" + (new Date()).getTime();
        
        $("<iframe>", {
            'id': iframeTag,
            'name': iframeTag,
            'style': "display: none;"
        }).appendTo("body");
        
        let formTag = "form_commands_" + + (new Date()).getTime();
        
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
        let href = window.location.href;
        
        let pageStart = href.indexOf("/" + language + "/");
        let split = href.substring(pageStart, href.length).split("/");
        split.shift();
        
        return split;
    };
    
    self.urlParameterValue = function(name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        
        let regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
        
        let parameters = regex.exec(window.location.search);
        
        return parameters === null ? "" : decodeURIComponent(parameters[1].replace(/\+/g, " "));
    };
    
    self.urlParameterRemove = function(url, target) {
        let result = url.split("?")[0];
        let parameter;
        let parameters = [];
        let query = (url.indexOf("?") !== -1) ? url.split("?")[1] : "";
        
        if (query !== "") {
            parameters = query.split("&");
            
            for (let a = parameters.length - 1; a >= 0; a -= 1) {
                parameter = parameters[a].split("=")[0];
                
                if (target === parameter)
                    parameters.splice(a, 1);
            }
            
            result += "?" + parameters.join("&");
        }
        
        return result;
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
        let array = $.map(items, function(elements) {
            return elements;
        });
        
        return array;
    };
    
    self.isIntoView = function(id) {
        if ($(id).length === 0)
            return false;
	
	let viewport = {
            'top' : $(window).scrollTop(),
            'left' : $(window).scrollLeft()
	};
	viewport.right = viewport.left + $(window).width();
	viewport.bottom = viewport.top + $(window).height();
	
	let bounds = $(id).offset();
        bounds.right = bounds.left + $(id).outerWidth();
        bounds.bottom = bounds.top + $(id).outerHeight();

        return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));
    };
    
    self.sortableElement = function(tagParent, tagInput) {
        populateSortableInput(tagParent, tagInput);
        
        if (self.checkWidthType() === "desktop") {
            $(".sort_result").find(".mdc-chip").removeClass("mdc-chip--selected");
            $(".sort_result").off("click");
            
            $(tagParent).find(".sort_list").sortable({
                'placeholder': "sortable_placeholder",
                'forcePlaceholderSize': true,
                'tolerance': "pointer",
                'handle': ".material-icons",
                'cancel': ".no_sortable",
                'start': function(event, ui) {
                    ui.placeholder.height(ui.item.height());
                },
                'stop': function(event, ui) {
                    ui.placeholder.height(0);
                    
                    populateSortableInput(tagParent, tagInput);
                }
            }).disableSelection();
        }
        else {
            if ($(tagParent).find(".sort_list").data("ui-sortable"))
                $(tagParent).find(".sort_list").sortable("destroy");

            $(".sort_result").off("click").on("click", ".mdc-chip", function(event) {
                let target = $(event.target).parent().hasClass("mdc-chip") === true ? $(event.target).parent() : $(event.target);

                if (target.hasClass("mdc-chip") === true) {
                    if (target.hasClass("mdc-chip--selected") === true) {
                        target.removeClass("mdc-chip--selected");

                        return;
                    }

                    $(".sort_result").find(".mdc-chip").removeClass("mdc-chip--selected");

                    target.hasClass("mdc-chip--selected") === true ? target.removeClass("mdc-chip--selected") : target.addClass("mdc-chip--selected");
                }
            });

            $(tagParent).find(".sort_control").find(".mdc-button").off("click").on("click", "", function(event) {
                let element = $(tagParent).find(".sort_list .mdc-chip--selected");

                if ($(event.target).find("i").hasClass("button_up") === true)
                    element.parent().insertBefore(element.parent().prev());
                else if ($(event.target).find("i").hasClass("button_down") === true)
                    element.parent().insertAfter(element.parent().next());

                populateSortableInput(tagParent, tagInput);
            });
        }
    };
    
    self.wordTag = function(tagParent, tagInput) {
        if ($(tagInput).val() !== undefined) {
            let inputValueSplit = $(tagInput).val().split(",");
            inputValueSplit.pop();
            
            let html = "";
            
            $.each(inputValueSplit, function(key, value) {
                html += "<div class=\"mdc-chip\">\n\
                    <i class=\"material-icons mdc-chip__icon mdc-chip__icon--leading\">delete</i>\n\
                    <div class=\"mdc-chip__text wordTag_elemet_data\" data-id=\"" + value + "\">" + $(tagInput + "_select").find("option[value=\"" + value + "\"]").text() + "</div>\n\
                </div>";
            });
            
            $(tagParent).find(".wordTag_result").html(html);
            
            $(tagInput + "_select").change(function(event) {
                if ($.inArray($(event.target).val(), inputValueSplit) === -1 && $(event.target).val() !== "") {
                    $(tagParent).find(".wordTag_result").append(
                        "<div class=\"mdc-chip\">\n\
                            <i class=\"material-icons mdc-chip__icon mdc-chip__icon--leading\">delete</i>\n\
                            <div class=\"mdc-chip__text wordTag_elemet_data\" data-id=\"" + $(event.target).val() + "\">" + $(event.target).find("option[value='" + $(event.target).val() + "']").text() + "</div>\n\
                        </div>"
                    );
                    
                    inputValueSplit.push($(event.target).val());
                    
                    $(tagInput).val(inputValueSplit.join(",") + ",");
                }
            });
            
            $(".wordTag_result").off("click").on("click", ".material-icons", function(event) {
                let removeItem = $(event.target).next().attr("data-id");

                inputValueSplit = $.grep(inputValueSplit, function(value) {
                    return value !== removeItem;
                });

                $(tagInput).val(inputValueSplit.join(",") + ",");

                $(event.target).parents(".mdc-chip").remove();
            });
        }
    };
    
    self.accordion = function(type) {
        let tag = "";
        
        if (type === "button")
            tag = ".button_accordion";
        else if (type === "icon")
            tag = ".icon_accordion";
        
        $(".accordion_container").find(tag).off("click").on("click", "", function() {
            let element = $(this);
            let accordion = $(this).next();
            
            $(".accordion_container").find(".accordion").not(accordion).prev().text(window.text.index_9);
            
            $(".accordion_container").find(".accordion").not(accordion).removeClass("accordion_active");
            
            if (type === "button") {
                if (accordion.hasClass("accordion_active") === false) {
                    element.text(window.text.index_10);

                    accordion.addClass("accordion_active");
                }
                else {
                    element.text(window.text.index_9);

                    accordion.removeClass("accordion_active");
                }
            }
            else if (type === "icon") {
                if (accordion.hasClass("accordion_active") === false) {
                    element.text("arrow_drop_up");

                    accordion.addClass("accordion_active");
                }
                else {
                    element.text("arrow_drop_down");

                    accordion.removeClass("accordion_active");
                }
            }
            
            materialDesign.refresh();
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
        let value = attribute.replace(/\\/g, "/");
        value = value.substring(value.lastIndexOf("/") + 1);
        
        return extension ? value.replace(/[?#].+$/, "") : value.split(".")[0];
    };
    
    self.imageError = function(elements) {
        elements.on("error", "", function() {
            $.each($(this), function(key, value) {
                $(value).prop("src", window.url.root + "/images/templates/" + window.setting.template + "/error_404.png");
            });
        });
    };
    
    self.imageRefresh = function(tag, length) {
        if (tag !== "") {
            let src = $(tag).prop("src");
            
            let srcSplit = src.split("?");

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
    
    self.menuRoot = function() {
        $(".menu_root_container").find(".mdc-list-item").on("click", "", function(event) {
            if ($(event.target).hasClass("parent_icon") === true)
                event.preventDefault();
        });
        
        $(".menu_root_container").off("click").on("click", ".parent_icon", function() {
            if ($(this).parent().next().css("display") !== "block")
                $(this).parent().next().show();
            else
                $(this).parent().next().hide();
        });
        
        if (window.location.href.indexOf("control_panel") === -1) {
            let parameters = self.urlParameters(window.session.languageTextCode);
            
            $(".menu_root_container").find(".target").removeClass("current");
            
            $.each($(".menu_root_container").find(".target"), function(key, value) {
                if ($(value).prop("href").indexOf(parameters[1]) !== -1) {
                    $(value).addClass("current");

                    return false;
                }
                else if (parseInt(parameters[1]) === 2 && key === 0) {
                    $(value).addClass("current");

                    return false;
                }
            });
        }
    };
    
    self.bodyProgress = function() {
        let linearProgressMdc = new mdc.linearProgress.MDCLinearProgress.attachTo($("#body_progress").find(".mdc-linear-progress")[0]);
        
        let performanceTiming = window.performance.timing;
        let estimatedTime = performanceTiming.loadEventEnd - performanceTiming.navigationStart;
        let time = parseInt((estimatedTime / 1000) % 60) * 100;
        let stepTime = Math.abs(Math.floor(time / 100));
        let current = 0;
        
        let interval = setInterval(function() {
            current += 0.1;
            
            linearProgressMdc.progress = current;
            
            if (current >= 2) {
                $("#body_progress").fadeOut("slow");
                
                clearInterval(interval);
            }
	}, stepTime);
    };
    
    self.uploadFakeClick = function() {
        $(document).on("click", ".material_upload button", function() {
            let button = $(this);
            let input = button.parent().find("input");
            let name = "";
            
            input.click();
            
            input.on("change", "", function() {
                if (input[0].files[0] !== undefined)
                    name = input[0].files[0].name;
                
                button.parent().find(".material_upload_label").text(name);
            });
        });
    };
    
    self.serializeJson = function(object) {
        let elements = {};
        
        let serializeArray = object.serializeArray();
        
        let jsonString = JSON.stringify(serializeArray);
        let json = JSON.parse(jsonString);
        
        let name = "";
        
        $.each(json, function(key, value) {
            $.each(value, function(keySub, valueSub) {
                if (keySub === "name") {
                    let newName = valueSub.substring(valueSub.lastIndexOf("[") + 1, valueSub.lastIndexOf("]"));
                    
                    newName = newName.replace("_token", "token");
                    
                    name = newName;
                }
                else
                    elements[name] = valueSub;
            });
        });
        
        return elements;
    };
    
    self.unitFormat = function(value) {
        let result = "";
        
        if (value === 0)
            result = "0 Bytes";
        else {
            let reference = 1024;
            let sizes = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];

            let index = Math.floor(Math.log(value) / Math.log(reference));

            result = parseFloat((value / Math.pow(reference, index)).toFixed(2)) + " " + sizes[index];
        }
        
        return result;
    };
    
    self.padZero = function(value) {
        return (value < 10 ? "0" : "") + value;
    };
    
    self.replaceUrlParameter = function(name, value) {
        let ulr = window.location.search;
        let regex = new RegExp("([?;&])" + name + "[^&;]*[;&]?");
        let query = ulr.replace(regex, "$1").replace(/&$/, "");
        
        let result = (query.length > 2 ? query + "&" : "?") + (value ? name + "=" + value : "");
        
        window.history.replaceState("", "", window.location.pathname + result);
    };
    
    self.createCookie = function(name, values, expire, secure) {
        let secureValue = secure === true ? "Secure;" : "";
        
        document.cookie = name + "=" + JSON.stringify(values) + ";expires=" + expire + ";path=/;domain=." + window.location.host.toString() + ";" + secureValue;
    };
    
    self.readCookie = function(name) {
        let result = document.cookie.match(new RegExp(name + "=([^;]+)"));
        
        result && (result = JSON.parse(result[1]));
        
        return result;
    };
    
    self.removeCookie = function(name) {
        if (self.readCookie(name) !== null)
            self.createCookie(name, null, "Thu, 01-Jan-1970 00:00:01 GMT", true);
    };
    
    self.blockMultiTab = function(active) {
        if (active === true) {
            let cookieValues = self.readCookie(window.session.name + "_blockMultiTab");
            
            if (cookieValues === null) {
                self.createCookie(window.session.name + "_blockMultiTab", 1, "Fri, 31 Dec 9999 23:59:59 GMT", true);
                
                $(window).on("unload", "", function(event) {
                    self.removeCookie(window.session.name + "_blockMultiTab");
                });
            }
            else {
                $("body").find(".mdc-layout-grid.main").html("<h1 style=\"position: absolute; top: 20%; left: 0; right: 0; text-align: center;\">"
                    + window.text.index_11 +
                "</h1>\n\
                <script nonce=\"" + window.session.xssProtectionValue + "\">\n\
                    \"use strict\";\n\
                    $(window).on(\"focus\", \"\", function() {\n\
                        alert(window.text.index_11);\n\
                        window.close();\n\
                        $(window).off(\"focus\");\n\
                        document.cookie = window.session.name + _blockMultiTab + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';\n\
                    });\n\
                </script>");
            }
        }
    };
    
    // Functions private
    function populateSortableInput(tagParent, tagInput) {
        let idList = "";

        $.each($(tagParent).find(".sort_elemet_data"), function(key, value) {
            idList += $(value).attr("data-id") + ",";
        });

        $(tagInput).val(idList);
    }
    
    function swipeFix() {
        let defaults = {
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
                let element = $(this);
                let startX;
                let startY;
                let isMoving = false;

                touchMove = false;

                function cancelTouch() {
                    element.off("mousemove.swipe touchmove.swipe", onTouchMove);
                    startX = null;
                    isMoving = false;
                }

                function onTouchMove(event) {
                    if (isMoving && event.touches !== undefined) {
                        let x = isTouch ? event.touches[0].pageX : event.pageX;
                        let y = isTouch ? event.touches[0].pageY : event.pageY;

                        let offsetX = startX - x;
                        let offsetY = startY - y;

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