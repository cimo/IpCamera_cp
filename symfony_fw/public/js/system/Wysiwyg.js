"use strict";

/* global helper, materialDesign, popupEasy */

const wysiwyg = new Wysiwyg();

function Wysiwyg() {
    // Vars
    const self = this;
    
    let containerTag;
    
    let iframeBody;
    let iframeContent;
    
    let history;
    let historyPosition;
    let historyLimit;
    let historyRestore;
    
    // Properties
    
    // Functions public
    self.init = function() {
        containerTag = "";
        
        iframeBody = null;
        iframeContent = null;
        
        history = [];
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
        history = [];
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
                    body div {\n\
                        margin: 0;\n\
                        min-height: 19px;\n\
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
                href: window.url.root + "/css/library/Roboto+Mono_custom.css",
                type: "text/css"
            }));
            $($(".wysiwyg").find(".editor").contents().find("head")[0]).append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/css/library/Roboto_300_400_500_custom.css",
                type: "text/css"
            }));
            $($(".wysiwyg").find(".editor").contents().find("head")[0]).append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/css/library/material-icons_custom.css",
                type: "text/css"
            }));
            $($(".wysiwyg").find(".editor").contents().find("head")[0]).append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/css/library/material-components-web_custom.min.css",
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
            
            iframeContent.execCommand("defaultParagraphSeparator", false, "div");
            
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
                let html = $.trim($(iframeBody).html());
                
                $(containerTag).val(html);
                
                $(".wysiwyg").find(".source").text(html);
            }
        }
        else if (type === "editor") {
            let source = $(".wysiwyg").find(".source");
            
            if (source.length > 0)
                $(iframeBody).html(source.text());
        }
    }
    
    function toolbarEvent() {
        $(".wysiwyg").find(".toolbar .mdc-fab").off("click").on("click", "", function(event) {
            event.preventDefault();
            
            let target = $(event.target).parent().hasClass("mdc-fab") === true ? $(event.target).parent() : $(event.target);
            
            let command = target.find("span").data("command");
            
            if (command === "source")
                source();
            else if (command === "foreColor" || command === "backColor")
                executeCommand(command, target.next().val());
            else
                executeCommand(command);
        });
        
        $(".wysiwyg").find(".mdc-select .mdc-select__native-control").off("change").on("change", "", function(event) {
            event.preventDefault();
            
            let command = $(event.target).data("command");
            
            if (command === "formatBlock" || command === "fontSize")
                executeCommand(command, $(event.target).val());
        });
    }
    
    function source() {
        let show = $(".wysiwyg").find(".source").css("display") === "none" ? true : false;

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
        else if (command === "foreColor" || command === "backColor" || command === "unlink" || command === "formatBlock" || command === "fontSize") {
            iframeContent.execCommand(command, false, fieldValue);
            
            historySave();
        }
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
                    let value = $("#wysiwyg_popup").find(".mdc-text-field__input").val();
                    
                    iframeContent.execCommand(command, false, value);
                    
                    historySave();
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
                    let value = $("#wysiwyg_popup").find(".mdc-text-field__input").val();
                    
                    iframeContent.execCommand(command, false, value);
                    
                    historySave();
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
                    let label = $("#wysiwyg_popup").find(".mdc-text-field__input.label").val();
                    let link = $("#wysiwyg_popup").find(".mdc-text-field__input.link").val();
                    
                    let html = "";
                    
                    if (link === "")
                        html = "<button class=\"mdc-button mdc-button--dense mdc-button--raised\" type=\"button\" contenteditable=\"false\" style=\"display: block;\">" + label + "</button>";
                    else
                        html = "<a class=\"mdc-button mdc-button--dense mdc-button--raised\" href=\"" + link + "\" type=\"button\" contenteditable=\"false\">" + label + "</a>";
                    
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
                    let rowNumber = $("#wysiwyg_popup").find(".row_number").val();
                    let columnNumber = $("#wysiwyg_popup").find(".column_number").val();
                    
                    let html = "<div class=\"mdc-layout-grid\" contenteditable=\"false\">";
                        for (let a = 0; a < rowNumber; a ++) {
                            html += "<div class=\"mdc-layout-grid__inner\" contenteditable=\"false\">";
                                for (let b = 0; b < columnNumber; b ++) {
                                    html += "<div class=\"mdc-layout-grid__cell mdc-layout-grid__cell--span-2\" contenteditable=\"false\">&nbsp;</div>";
                                }
                            html += "</div>";
                        }
                    html += "</div>";
                    
                    addHtmlAtCaretPosition(html);
                    
                    historySave();
                }
            );
        }
        else {
            iframeContent.execCommand(command, false, null);
            
            historySave();
        }
    }
    
    function historyUndo() {
        if (historyPosition >= 0) {
            let element = history[-- historyPosition];
            
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
            let element = history[++ historyPosition];
            
            $(iframeBody).html(element);
            
            historyRestore = true;
	}
    }
    
    function historySave() {
        spaceAfterElement();
        
        removeDoubleSpace();
        
        let html = $(iframeBody).html();
        
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
        $(iframeBody).on("click", "", function(event) {
            historyRestore = false;
            
            let element = findElementAtCaretPosition();
            
            if ($(element).hasClass("mdc-layout-grid__cell") === false && $(element).parents(".mdc-layout-grid__cell").length === 0)
                $(iframeBody).find(".mdc-layout-grid__cell").prop("contenteditable", false);
        });
        
        $(iframeBody).on("dblclick", "", function(event) {
            historyRestore = false;
            
            let element = findElementAtCaretPosition();
            
            if ($(element).parents(".mdc-layout-grid__cell").length > 0)
                element = $(element).parents(".mdc-layout-grid__cell")[0];
            
            if ($(element).hasClass("mdc-layout-grid__cell") === true) {
                $(iframeBody).find(".mdc-layout-grid__cell").prop("contenteditable", false);
                
                $(element).prop("contenteditable", true);
                $(element).focus();
            }
        });
        
        $(iframeBody).on("keydown", "", function(event) {
            if ((event.ctrlKey || event.metaKey === true)) {
                if (event.shiftKey && event.keyCode === 90) {
                    historyRedo();
                    
                    return false;
                }
                else if (event.keyCode === 90) {
                    historyUndo();
                    
                    return false;
                } 
            }
        });
        
        $(iframeBody).on("keyup", "", function(event) {
            historySave();
        });
        
        $(iframeBody).contextmenu(function(event) {
            let type = "";
            let target = null;
            let content = "";
            
            if ($(event.target).hasClass("mdc-button") === true) {
                type = "button";
                target = $(event.target);
                
                if ($(event.target).is("button") === true) {
                    let label = $(event.target).text();
                    
                    content = "<div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input label\" type=\"text\" value=\"" + label + "\" autocomplete=\"off\" aria-label=\"label\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_10 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>";
                }
                else if ($(event.target).is("a") === true) {
                    let label = $(event.target).text();
                    let link = $(event.target).prop("href") === undefined ? "" : $(event.target).prop("href");
                    
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
                popupsetting(type, target, content);
            
            return false;
        });
    }
    
    function popupsetting(type, target, content) {
        popupEasy.create(
            window.textWysiwyg.label_15,
            "<div id=\"wysiwyg_popup\">" + content + "</div>",
            function() {
                if (type === "button") {
                    let label = $("#wysiwyg_popup").find(".mdc-text-field__input.label").val();
                    let link = $("#wysiwyg_popup").find(".mdc-text-field__input.link").val();
                    
                    target.text(label);
                    target.prop("href", link);
                }
                
                historySave();
            }
        );
        
        if (type === "table") {
            $("#row_add").off("click").on("click", "", function() {
                let columnNumber = target.parent().find(".mdc-layout-grid__cell").length;
                
                let html = "<div class=\"mdc-layout-grid__inner\" contenteditable=\"false\">";
                    for (let a = 0; a < columnNumber; a ++) {
                        html += "<div class=\"mdc-layout-grid__cell mdc-layout-grid__cell--span-2\" contenteditable=\"false\">&nbsp;</div>";
                    }
                html += "</div>";
                
                target.parent().after(html);
                
                popupEasy.close();
                
                historySave();
            });
            $("#row_remove").off("click").on("click", "", function() {
                target.parent().remove();
                
                popupEasy.close();
                
                historySave();
            });
            
            $("#column_add").off("click").on("click", "", function() {
                let columnIndex = target.index();
                
                let html = "<div class=\"mdc-layout-grid__cell mdc-layout-grid__cell--span-2\" contenteditable=\"false\">&nbsp;</div>";
                
                $.each(target.parents(".mdc-layout-grid").find(".mdc-layout-grid__inner"), function(key, value) {
                    $(value).find(".mdc-layout-grid__cell").eq(columnIndex).after(html);
                });
                
                popupEasy.close();
                
                historySave();
            });
            $("#column_remove").off("click").on("click", "", function() {
                let columnIndex = target.index();
                
                $.each(target.parents(".mdc-layout-grid").find(".mdc-layout-grid__inner"), function(key, value) {
                    if ($(value).find(".mdc-layout-grid__cell").length === 1) {
                        target.parents(".mdc-layout-grid").remove();
                        
                        return false;
                    }
                    else
                        $(value).find(".mdc-layout-grid__cell").eq(columnIndex).remove();
                });
                
                popupEasy.close();
                
                historySave();
            });
        }
    }
    
    function findElementAtCaretPosition() {
        let iframeDocument = window.frames[0].document;
        let selection = null;
        let containerNode = null;
        
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
        let iframeDocument = window.frames[0].document;
        let range = null;
        
        if (iframeDocument.getSelection) {
            selection = iframeDocument.getSelection();
            
            if (selection.getRangeAt && selection.rangeCount) {
                let htmlElement = window.frames[0].document.createElement("div");
                htmlElement.innerHTML = html;
                
                let fragment = window.frames[0].document.createDocumentFragment();
                let lastNode = null;
                
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
    
    function spaceAfterElement() {
        let elements = $(iframeContent).find(".mdc-button, .mdc-layout-grid");
        
        $.each(elements, function(key, value) {
            if ($(value).prev("br").length === 0)
                $(value).before("<br>");
            
            if ($(value).next("br").length === 0)
                $(value).after("<br>");
        });
    }
    
    function removeDoubleSpace() {
        let elements = $(iframeContent).find("br");
        
        $.each(elements, function(key, value) {
            if ($(value).prev("br").length === 1)
                $(value).prev("br").remove();
            
            if ($(value).next("br").length === 1)
                $(value).next("br").remove();
        });
    }
}