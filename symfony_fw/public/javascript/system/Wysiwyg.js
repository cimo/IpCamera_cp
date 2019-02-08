/* global utility, materialDesign, popupEasy */

var wysiwyg = new Wysiwyg();

function Wysiwyg() {
    // Vars
    var self = this;
    
    var containerTag;
    
    var iframeBody;
    var iframeContent;
    
    var history;
    var historyPosition;
    var historyLimit;
    var historyRestore;
    
    // Properties
    
    // Functions public
    self.init = function() {
        containerTag = "";
        
        iframeBody = null;
        iframeContent = null;
        
        history = new Array();
        historyPosition = -1;
        historyLimit = 300;
        historyRestore = false;
    };
    
    self.create = function(containerTagValue, saveElement) {
        setTimeout(function() {
            containerTag = containerTagValue;

            $(containerTag).parent().css("margin", "0");
            $(containerTag).parent().hide();

            $(saveElement).click(function() {
                fillField("source");
            });

            iframe();
        }, 100);
    };
    
    self.historyClear = function() {
        history = new Array();
    };
    
    self.save = function() {
        fillField("source");
    };
    
    // Functions private
    function iframe() {
        if ($(".wysiwyg").length > 0) {
            $(".wysiwyg").find(".editor").contents().find("head").append(
                "<style>\n\
                    html {\n\
                        padding: 5px !important;\n\
                        height: auto !important;\n\
                    }\n\
                    body .mdc-layout-grid {\n\
                        padding: 0;\n\
                    }\n\
                    body .mdc-layout-grid .mdc-layout-grid__cell {\n\
                        border: 1px solid #000000;\n\
                    }\n\
                </style>"
            );
            
            $($(".wysiwyg").find(".editor").contents().find("head")[0]).append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/css/library/Roboto+Mono.css",
                type: "text/css"
            }));
            $($(".wysiwyg").find(".editor").contents().find("head")[0]).append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/css/library/Roboto_300_400_500.css",
                type: "text/css"
            }));
            $($(".wysiwyg").find(".editor").contents().find("head")[0]).append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/css/library/material-icons.css",
                type: "text/css"
            }));
            $($(".wysiwyg").find(".editor").contents().find("head")[0]).append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/css/library/material-components-web.min.css",
                type: "text/css"
            }));
            $($(".wysiwyg").find(".editor").contents().find("head")[0]).append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/css/system/" + window.setting.template + ".css",
                type: "text/css"
            }));
            
            iframeBody = $(".wysiwyg").find(".editor").contents().find("body")[0];
            iframeContent = $(".wysiwyg").find(".editor").contents()[0];
            
            $(iframeBody).addClass("mdc-typography");
            
            $(iframeBody).prop("contenteditable", "true");
            
            $(iframeBody).off("click").on("click", "a", function(event) {
                event.preventDefault();
            });
            
            fillField("load");

            toolbarEvent();

            editorEvent();
        }
    }
    
    function fillField(type) {
        if (type === "load") {
            if ($(iframeBody).length > 0) {
                $(iframeBody).html($(containerTag).val());
                
                $(".wysiwyg").find(".source").text($(containerTag).val());
                
                historyLoad($(containerTag).val());
            }
        }
        else if (type === "source") {
            if ($(iframeBody).length > 0) {
                var html = $.trim($(iframeBody).html());
                
                $(containerTag).val(html);
                
                $(".wysiwyg").find(".source").text(html);
            }
        }
        else if (type === "editor") {
            var source = $(".wysiwyg").find(".source");
            
            if (source.length > 0)
                $(iframeBody).html(source.text());
        }
    }
    
    function toolbarEvent() {
        $(".wysiwyg").find(".toolbar .mdc-fab").off("click").on("click", "", function(event) {
            event.preventDefault();
            
            var target = $(event.target).parent().hasClass("mdc-fab") === true ? $(event.target).parent() : $(event.target);
            
            var command = target.find("span").data("command");
            
            if (command === "source")
                source();
            else if (command === "foreColor" || command === "backColor")
                executeCommand(command, target.next().val());
            else
                executeCommand(command);
        });
        
        $(".wysiwyg").find(".mdc-select .mdc-select__native-control").off("change").on("change", "", function(event) {
            event.preventDefault();
            
            var command = $(event.target).data("command");
            
            if (command === "formatBlock" || command === "fontSize")
                executeCommand(command, $(event.target).val());
        });
    }
    
    function source() {
        var show = $(".wysiwyg").find(".source").css("display") === "none" ? true : false;

        if (show === true) {
            fillField("source");
            
            $(".wysiwyg").find(".editor").hide();
            $(".wysiwyg").find(".source").show();
        }
        else {
            fillField("editor");
            
            $(".wysiwyg").find(".source").hide();
            $(".wysiwyg").find(".editor").show();
        }
    }
    
    function executeCommand(command, fieldValue) {
        if (command === "undo")
            historyUndo();
        else if (command === "redo")
            historyRedo();
        else if (command === "foreColor" || command === "backColor" || command === "unlink" || command === "formatBlock" || command === "fontSize")
            iframeContent.execCommand(command, false, fieldValue);
        else if (command === "createLink") {
            popupEasy.create(
                window.textWysiwyg.label_5,
                "<div id=\"wysiwyg_popup\">\n\
                    <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input\" type=\"text\" value=\"\" autocomplete=\"off\" aria-label=\"label\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_6 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                </div>",
                function() {
                    var value = $(".wysiwyg_popup").find(".mdc-text-field__input").val();
                    
                    iframeContent.execCommand(command, false, value);
                }
            );
        }
        else if (command === "insertImage") {
            popupEasy.create(
                window.textWysiwyg.label_7,
                "<div id=\"wysiwyg_popup\">\n\
                    <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input\" type=\"text\" value=\"\" autocomplete=\"off\" aria-label=\"label\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_8 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                </div>",
                function() {
                    var value = $(".wysiwyg_popup").find(".mdc-text-field__input").val();
                    
                    iframeContent.execCommand(command, false, value);
                }
            );
        }
        else if (command === "custom_button_add") {
            popupEasy.create(
                window.textWysiwyg.label_9,
                "<div id=\"wysiwyg_popup\">\n\
                    <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input label\" type=\"text\" value=\"\" autocomplete=\"off\" aria-label=\"label\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_10 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                    <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input link\" type=\"text\" value=\"\" autocomplete=\"off\" aria-label=\"label\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_11 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                </div>",
                function() {
                    var label = $(".wysiwyg_popup").find(".mdc-text-field__input.label").val();
                    var link = $(".wysiwyg_popup").find(".mdc-text-field__input.link").val();
                    
                    var html = "";
                    
                    if (link === "")
                        html = "<br><button class=\"mdc-button mdc-button--dense mdc-button--raised\" type=\"button\" contenteditable=\"false\" style=\"display: block;\">" + label + "</button><br>";
                    else
                        html = "<br><a class=\"mdc-button mdc-button--dense mdc-button--raised\" href=\"" + link + "\" type=\"button\" contenteditable=\"false\">" + label + "</a><br>";
                    
                    //iframeContent.execCommand("insertHTML", false, html);
                    addHtmlAtCaretPosition(html);
                    
                    historySave();
                }
            );
        }
        else if (command === "custom_table_add") {
            popupEasy.create(
                window.textWysiwyg.label_12,
                "<div id=\"wysiwyg_popup\">\n\
                    <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input row_number\" type=\"text\" value=\"1\" autocomplete=\"off\" aria-label=\"label\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_13 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                    <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input column_number\" type=\"text\" value=\"4\" autocomplete=\"off\" aria-label=\"label\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_14 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                </div>",
                function() {
                    var rowNumber = $(".wysiwyg_popup").find(".mdc-text-field__input.row_number").val();
                    var columnNumber = $(".wysiwyg_popup").find(".mdc-text-field__input.column_number").val();
                    
                    var html = "<br><div class=\"mdc-layout-grid\" contenteditable=\"false\">";
                        for (var a = 0; a < rowNumber; a ++) {
                            html += "<div class=\"mdc-layout-grid__inner\" contenteditable=\"false\">";
                                for (var b = 0; b < columnNumber; b ++) {
                                    html += "<div class=\"mdc-layout-grid__cell mdc-layout-grid__cell--span-2\" contenteditable=\"false\">&nbsp;</div>";
                                }
                            html += "</div>";
                        }
                    html += "</div><br>";
                    
                    //iframeContent.execCommand("insertHTML", false, html);
                    addHtmlAtCaretPosition(html);
                    
                    historySave();
                }
            );
        }
        else
            iframeContent.execCommand(command, false, null);
    }
    
    function historyUndo() {
        if (historyPosition >= 0) {
            var element = history[-- historyPosition];
            
            if (historyPosition < 0) {
                historyPosition = 0;
                
                element = history[historyPosition];
            }
            
            $(iframeBody).html(element);
            
            historyRestore = true;
	}
    }
    
    function historyRedo() {
        if (historyPosition < (history.length - 1)) {
            var element = history[++ historyPosition];
            
            $(iframeBody).html(element);
            
            historyRestore = true;
	}
    }
    
    function historySave() {
        var html = $(iframeBody).html();
        
        if (html !== history[historyPosition]) {
            if (historyPosition < (history.length - 1))
                history.splice(historyPosition + 1);

            history.push(html);
            historyPosition ++;

            if (historyPosition > historyLimit)
                history.shift();
        }
    }
    
    function historyLoad(content) {
        history.push(content);
        historyPosition = 0;
    }
    
    function editorEvent() {
        var mutationObserverElement = $(".wysiwyg").find(".editor").contents().find("body")[0];
        
        utility.mutationObserver(['characterData', 'childList'], mutationObserverElement, function() {
            if (historyRestore === false)
                historySave();
        });
        
        $(iframeBody).on("click", "", function(event) {
            historyRestore = false;
            
            var element = findElementAtCaretPosition();
            
            if ($(element).parents(".mdc-layout-grid__cell").length > 0)
                element = $(element).parents(".mdc-layout-grid__cell")[0];
            
            if ($(element).hasClass("mdc-layout-grid__cell") === false || $(element).hasClass("mdc-layout-grid__cell") === true && $(element).prop("contenteditable") === "false")
                $(iframeBody).find(".mdc-layout-grid__cell").prop("contenteditable", false);
        });
        
        $(iframeBody).on("dblclick", "", function(event) {
            var element = findElementAtCaretPosition();
            
            if ($(element).parents(".mdc-layout-grid__cell").length > 0)
                element = $(element).parents(".mdc-layout-grid__cell")[0];
            
            if ($(element).hasClass("mdc-layout-grid__cell") === true) {
                $(iframeBody).find(".mdc-layout-grid__cell").prop("contenteditable", false);

                $(element).prop("contenteditable", true);
                $(element).focus();
            }
        });
        
        $(iframeBody).on("keydown", "", function(event) {
            if (event.keyCode === 13)
                iframeContent.execCommand("defaultParagraphSeparator", false, "br");
            
            if ((event.ctrlKey || event.metaKey === true) && event.keyCode === 90) {
                historyUndo();
                
                return false;
            }
            else if ((event.ctrlKey || event.metaKey === true) && event.keyCode === 89) {
                historyRedo();
                
                return false;
            }
        });
        $(iframeBody).on("keyup", "", function(event) {
            var button = $(iframeContent).find(".mdc-button");

            if (button.length > 0) {
                if (button.prev("br").length === 0)
                    button.before("<br>");

                if (button.next("br").length === 0)
                    button.after("<br>");
            }

            var grid = $(iframeContent).find(".mdc-layout-grid");

            if (grid.length > 0) {
                if (grid.prev("br").length === 0)
                    grid.before("<br>");

                if (grid.next("br").length === 0)
                    grid.after("<br>");
            }
        });
        
        $(iframeBody).contextmenu(function(event) {
            var type = "";
            var target = null;
            var content = "";
            
            if ($(event.target).hasClass("mdc-button") === true) {
                type = "button";
                target = $(event.target);
                
                if ($(event.target).is("button") === true) {
                    var label = $(event.target).text();

                    content = "<div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input label\" type=\"text\" value=\"" + label + "\" autocomplete=\"off\" aria-label=\"label\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_10 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>";
                }
                else if ($(event.target).is("a") === true) {
                    var label = $(event.target).text();
                    var link = $(event.target).prop("href") === undefined ? "" : $(event.target).prop("href");

                    content = "<div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input label\" type=\"text\" value=\"" + label + "\" autocomplete=\"off\" aria-label=\"label\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_10 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                    <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input link\" type=\"text\" value=\"" + link + "\" autocomplete=\"off\" aria-label=\"label\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_11 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>";
                }
            }
            else if ($(event.target).hasClass("mdc-layout-grid__cell") === true || $(event.target).parents(".mdc-layout-grid__cell").length > 0) {
                target = $(event.target);
                
                if ($(event.target).parents(".mdc-layout-grid__cell").length > 0)
                    target = $(event.target).parents(".mdc-layout-grid__cell");
                
                type = "table";
                
                content = "<fieldset>\n\
                    <legend>" + window.textWysiwyg.label_16 + "</legend>\n\
                    <button id=\"row_add\" class=\"mdc-button mdc-button--dense mdc-button--raised\" type=\"button\">" + window.textWysiwyg.label_17 + "</button>\n\
                    <button id=\"row_remove\" class=\"mdc-button mdc-button--dense mdc-button--raised\" type=\"button\">" + window.textWysiwyg.label_18 + "</button>\n\
                </fieldset>\n\
                <fieldset>\n\
                    <legend>" + window.textWysiwyg.label_19 + "</legend>\n\
                    <button id=\"column_add\" class=\"mdc-button mdc-button--dense mdc-button--raised\" type=\"button\">" + window.textWysiwyg.label_20 + "</button>\n\
                    <button id=\"column_remove\" class=\"mdc-button mdc-button--dense mdc-button--raised\" type=\"button\">" + window.textWysiwyg.label_21 + "</button>\n\
                </fieldset>";
            }
            
            if (content !== "")
                popupSettings(type, target, content);
            
            return false;
        });
    } 
    
    function popupSettings(type, target, content) {
        popupEasy.create(
            window.textWysiwyg.label_15,
            "<div id=\"wysiwyg_popup\">" + content + "</div>",
            function() {
                if (type === "button") {
                    var label = $(".wysiwyg_popup").find(".mdc-text-field__input.label").val();
                    var link = $(".wysiwyg_popup").find(".mdc-text-field__input.link").val();

                    target.text(label);
                    target.prop("href", link);
                }
            }
        );
        
        if (type === "table") {
            $("#row_add").off("click").on("click", "", function() {
                var columnNumber = target.parent().find(".mdc-layout-grid__cell").length;

                var html = "<div class=\"mdc-layout-grid__inner\" contenteditable=\"false\">";
                    for (var a = 0; a < columnNumber; a ++) {
                        html += "<div class=\"mdc-layout-grid__cell mdc-layout-grid__cell--span-2\" contenteditable=\"false\">&nbsp;</div>";
                    }
                html += "</div>";

                target.parent().after(html);

                popupEasy.close();
            });
            $("#row_remove").off("click").on("click", "", function() {
                target.parent().remove();
                
                popupEasy.close();
            });

            $("#column_add").off("click").on("click", "", function() {
                var columnIndex = target.index();

                var html = "<div class=\"mdc-layout-grid__cell mdc-layout-grid__cell--span-2\" contenteditable=\"false\">&nbsp;</div>";

                $.each(target.parents(".mdc-layout-grid").find(".mdc-layout-grid__inner"), function(key, value) {
                    $(value).find(".mdc-layout-grid__cell").eq(columnIndex).after(html);
                });

                popupEasy.close();
            });
            $("#column_remove").off("click").on("click", "", function() {
                var columnIndex = target.index();
                
                $.each(target.parents(".mdc-layout-grid").find(".mdc-layout-grid__inner"), function(key, value) {
                    $(value).find(".mdc-layout-grid__cell").eq(columnIndex).remove();
                });
                
                popupEasy.close();
            });
        }
    }
    
    function findElementAtCaretPosition() {
        var iframeDocument = window.frames[0].document;
        var selection = null;
        var containerNode = null;
        
        if (iframeDocument.getSelection) {
            selection = iframeDocument.getSelection();

            containerNode = selection.anchorNode;
        }
        else if (iframeDocument.selection) {
            selection = iframeDocument.selection;
            
            containerNode = selection.anchorNode;
        }
        
        if (containerNode !== null)
            containerNode = containerNode.nodeType === 3 ? containerNode.parentNode : containerNode;   
        
        return containerNode;
    }
    
    function addHtmlAtCaretPosition(html) {
        var iframeDocument = window.frames[0].document;
        var range = null;
        
        if (iframeDocument.getSelection) {
            selection = iframeDocument.getSelection();
            
            if (selection.getRangeAt && selection.rangeCount) {
                var htmlElement = window.frames[0].document.createElement("div");
                htmlElement.innerHTML = html;
                
                var fragment = window.frames[0].document.createDocumentFragment();
                var lastNode = null;
                
                while ((node = htmlElement.firstChild)) {
                    lastNode = fragment.appendChild(node);
                }
                
                range = selection.getRangeAt(0);
                range.deleteContents();
                range.insertNode(fragment);
                
                if (lastNode) {
                    range = range.cloneRange();
                    range.setStartAfter(lastNode);
                    range.collapse(true);
                    
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            }
        }
        else if (iframeDocument.selection) {
            selection = iframeDocument.selection;
            
            if (selection.type !== "Control") {
                range = selection.createRange();

                range.pasteHTML(html);
            }
        }
    }
}