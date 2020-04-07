"use strict";

/* global helper, materialDesign, popupEasy */

class Wysiwyg {
    // Properties
    
    // Functions public
    constructor() {
        this.containerTag = "";
        
        this.wysiwyg = null;
        
        this.rowAdd = null;
        this.rowRemove = null;
        
        this.columnAdd = null;
        this.columnRemove = null;
        
        this.iframeBody = null;
        this.iframeContent = null;
        
        this.history = [];
        this.historyPosition = -1;
        this.historyLimit = 300;
        this.historyRestore = false;
    }
    
    create = (containerTag, saveElement) => {
        let timeoutEvent = setTimeout(() => {
            clearTimeout(timeoutEvent);
            
            this.containerTag = containerTag;

            $(this.containerTag).parent().css("margin", "0");
            $(this.containerTag).parent().hide();
            
            $(saveElement).click(() => {
                this._fillField("source");
            });
            
            this._iframe();
        }, 100);
    }
    
    historyClear = () => {
        this.history = [];
    }
    
    save = () => {
        this._fillField("source");
    }
    
    // Functions private
    _iframe = () => {
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
                href: `${window.url.root}/css/library/Roboto+Mono_custom.css`,
                type: "text/css"
            }));
            $($(".wysiwyg").find(".editor").contents().find("head")[0]).append($("<link/>", {
                rel: "stylesheet",
                href: `${window.url.root}/css/library/Roboto_300_400_500_custom.css`,
                type: "text/css"
            }));
            $($(".wysiwyg").find(".editor").contents().find("head")[0]).append($("<link/>", {
                rel: "stylesheet",
                href: `${window.url.root}/css/library/material-icons_custom.css`,
                type: "text/css"
            }));
            $($(".wysiwyg").find(".editor").contents().find("head")[0]).append($("<link/>", {
                rel: "stylesheet",
                href: `${window.url.root}/css/library/material-components-web_custom.min.css`,
                type: "text/css"
            }));
            $($(".wysiwyg").find(".editor").contents().find("head")[0]).append($("<link/>", {
                rel: "stylesheet",
                href: `${window.url.root}/css/system/${window.setting.template}.css`,
                type: "text/css"
            }));
            
            this.iframeBody = $(".wysiwyg").find(".editor").contents().find("body")[0];
            this.iframeContent = $(".wysiwyg").find(".editor").contents()[0];
            
            $(this.iframeBody).addClass("mdc-typography");
            
            $(this.iframeBody).prop("contenteditable", "true");
            
            $(this.iframeBody).on("click", "a", (event) => {
                event.preventDefault();
            });
            
            this.iframeContent.execCommand("defaultParagraphSeparator", false, "div");
            
            this._fillField("load");
            
            this._toolbarEvent();
            
            this._editorEvent();
        }
    }
    
    _fillField = (type) => {
        if (type === "load") {
            if ($(this.iframeBody).length > 0) {
                $(this.iframeBody).html($(this.containerTag).val());
                
                $(".wysiwyg").find(".source").text($(this.containerTag).val());
                
                this._historyLoad($(this.containerTag).val());
            }
        }
        else if (type === "source") {
            if ($(this.iframeBody).length > 0) {
                let html = $.trim($(this.iframeBody).html());
                
                $(this.containerTag).val(html);
                
                $(".wysiwyg").find(".source").text(html);
            }
        }
        else if (type === "editor") {
            let source = $(".wysiwyg").find(".source");
            
            if (source.length > 0)
                $(this.iframeBody).html(source.text());
        }
    }
    
    _toolbarEvent = () => {
        $(".wysiwyg").find(".toolbar .mdc-fab").on("click", "", (event) => {
            event.preventDefault();
            
            let target = $(event.target).parent().hasClass("mdc-fab") === true ? $(event.target).parent() : $(event.target);
            
            let command = target.find("span").data("command");
            
            if (command === "source")
                this._source();
            else if (command === "foreColor" || command === "backColor")
                this._executeCommand(command, target.next().val());
            else
                this._executeCommand(command);
        });
        
        $(".wysiwyg").find(".mdc-select .mdc-select__native-control").on("change", "", (event) => {
            event.preventDefault();
            
            let command = $(event.target).data("command");
            
            if (command === "formatBlock" || command === "fontSize")
                this._executeCommand(command, $(event.target).val());
        });
    }
    
    _source = () => {
        let show = $(".wysiwyg").find(".source").css("display") === "none" ? true : false;

        if (show === true) {
            this._fillField("source");
            
            $(".wysiwyg").find(".editor").hide();
            $(".wysiwyg").find(".source").show();
        }
        else {
            this._fillField("editor");
            
            $(".wysiwyg").find(".source").hide();
            $(".wysiwyg").find(".editor").show();
        }
    }
    
    _executeCommand = (command, fieldValue) => {
        if (command === "undo")
            this._historyUndo();
        else if (command === "redo")
            this._historyRedo();
        else if (command === "foreColor" || command === "backColor" || command === "unlink" || command === "formatBlock" || command === "fontSize") {
            this.iframeContent.execCommand(command, false, fieldValue);
            
            this._historySave();
        }
        else if (command === "createLink") {
            popupEasy.show(
                window.textWysiwyg.label_5,
                `<div id="wysiwyg_popup">
                    <div class="mdc-text-field mdc-text-field__basic mdc-text-field--dense" style="width: 100%;">
                        <input class="mdc-text-field__input" type="text" value="" autocomplete="off" aria-label="label"/>
                        <label class="mdc-floating-label">${window.textWysiwyg.label_6}</label>
                        <div class="mdc-line-ripple"></div>
                    </div>
                    <p class="mdc-text-field-helper-text" aria-hidden="true"></p>
                </div>`,
                () => {
                    let value = $("#wysiwyg_popup").find(".mdc-text-field__input").val();
                    
                    this.iframeContent.execCommand(command, false, value);
                    
                    this._historySave();
                }
            );
        }
        else if (command === "insertImage") {
            popupEasy.show(
                window.textWysiwyg.label_7,
                `<div id="wysiwyg_popup">
                    <div class="mdc-text-field mdc-text-field__basic mdc-text-field--dense" style="width: 100%;">
                        <input class="mdc-text-field__input" type="text" value="" autocomplete="off" aria-label="label"/>
                        <label class="mdc-floating-label">${window.textWysiwyg.label_8}</label>
                        <div class="mdc-line-ripple"></div>
                    </div>
                    <p class="mdc-text-field-helper-text" aria-hidden="true"></p>
                </div>`,
                () => {
                    let value = $("#wysiwyg_popup").find(".mdc-text-field__input").val();
                    
                    this.iframeContent.execCommand(command, false, value);
                    
                    this._historySave();
                }
            );
        }
        else if (command === "custom_button_add") {
            popupEasy.show(
                window.textWysiwyg.label_9,
                `<div id="wysiwyg_popup">
                    <div class="mdc-text-field mdc-text-field__basic mdc-text-field--dense" style="width: 100%;">
                        <input class="mdc-text-field__input label" type="text" value="" autocomplete="off" aria-label="label"/>
                        <label class="mdc-floating-label">${window.textWysiwyg.label_10}</label>
                        <div class="mdc-line-ripple"></div>
                    </div>
                    <p class="mdc-text-field-helper-text" aria-hidden="true"></p>
                    <div class="mdc-text-field mdc-text-field__basic mdc-text-field--dense" style="width: 100%;">
                        <input class="mdc-text-field__input link" type="text" value="" autocomplete="off" aria-label="label"/>
                        <label class=\"mdc-floating-label\">${window.textWysiwyg.label_11}</label>
                        <div class="mdc-line-ripple"></div>
                    </div>
                    <p class="mdc-text-field-helper-text" aria-hidden="true"></p>
                </div>`,
                () => {
                    let label = $("#wysiwyg_popup").find(".mdc-text-field__input.label").val();
                    let link = $("#wysiwyg_popup").find(".mdc-text-field__input.link").val();
                    
                    let html = "";
                    
                    if (link === "")
                        html = `<button class="mdc-button mdc-button--dense mdc-button--raised" type="button" contenteditable="false" style="display: block;">${label}</button>`;
                    else
                        html = `<a class="mdc-button mdc-button--dense mdc-button--raised" href="${link}" type="button" contenteditable="false">${label}</a>`;
                    
                    this._addHtmlAtCaretPosition(html);
                    
                    this._historySave();
                }
            );
        }
        else if (command === "custom_table_add") {
            popupEasy.show(
                window.textWysiwyg.label_12,
                `<div id="wysiwyg_popup">
                    <div class="mdc-text-field mdc-text-field__basic mdc-text-field--dense" style="width: 100%;">
                        <input class="mdc-text-field__input row_number" type="text" value="1" autocomplete="off" aria-label="label"/>
                        <label class="mdc-floating-label">${window.textWysiwyg.label_13}</label>
                        <div class="mdc-line-ripple"></div>
                    </div>
                    <p class="mdc-text-field-helper-text" aria-hidden="true"></p>
                    <div class="mdc-text-field mdc-text-field__basic mdc-text-field--dense" style="width: 100%;">
                        <input class="mdc-text-field__input column_number" type="text" value="4" autocomplete="off" aria-label="label"/>
                        <label class=\"mdc-floating-label\">${window.textWysiwyg.label_14}</label>
                        <div class="mdc-line-ripple"></div>
                    </div>
                    <p class="mdc-text-field-helper-text" aria-hidden="true"></p>
                </div>`,
                () => {
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
                    
                    this._addHtmlAtCaretPosition(html);
                    
                    this._historySave();
                }
            );
        }
        else {
            this.iframeContent.execCommand(command, false, null);
            
            this._historySave();
        }
    }
    
    _historyUndo = () => {
        if (this.historyPosition >= 0) {
            let element = this.history[-- this.historyPosition];
            
            if (this.historyPosition < 0) {
                this.historyPosition = 0;
                
                element = this.history[this.historyPosition];
            }
            
            $(this.iframeBody).html(element);
            
            this.historyRestore = true;
	}
    }
    
    _historyRedo = () => {
        if (this.historyPosition < (this.history.length - 1)) {
            let element = this.history[++ this.historyPosition];
            
            $(this.iframeBody).html(element);
            
            this.historyRestore = true;
	}
    }
    
    _historySave = () => {
        this._spaceAfterElement();
        
        this._removeDoubleSpace();
        
        let html = $(this.iframeBody).html();
        
        if (html !== this.history[this.historyPosition]) {
            if (this.historyPosition < (this.history.length - 1))
                this.history.splice(this.historyPosition + 1);
            
            this.history.push(html);
            this.historyPosition ++;
            
            if (this.historyPosition > this.historyLimit)
                this.history.shift();
        }
    }
    
    _historyLoad = (content) => {
        this.history.push(content);
        this.historyPosition = 0;
    }
    
    _editorEvent = () => {
        $(this.iframeBody).on("click", "", (event) => {
            this.historyRestore = false;
            
            let element = this._findElementAtCaretPosition();
            
            if ($(element).hasClass("mdc-layout-grid__cell") === false && $(element).parents(".mdc-layout-grid__cell").length === 0)
                $(this.iframeBody).find(".mdc-layout-grid__cell").prop("contenteditable", false);
        });
        
        $(this.iframeBody).on("dblclick", "", (event) => {
            this.historyRestore = false;
            
            let element = this._findElementAtCaretPosition();
            
            if ($(element).parents(".mdc-layout-grid__cell").length > 0)
                element = $(element).parents(".mdc-layout-grid__cell")[0];
            
            if ($(element).hasClass("mdc-layout-grid__cell") === true) {
                $(this.iframeBody).find(".mdc-layout-grid__cell").prop("contenteditable", false);
                
                $(element).prop("contenteditable", true);
                $(element).focus();
            }
        });
        
        $(this.iframeBody).on("keydown", "", (event) => {
            if ((event.ctrlKey || event.metaKey === true)) {
                if (event.shiftKey && event.keyCode === 90) {
                    this._historyRedo();
                    
                    return false;
                }
                else if (event.keyCode === 90) {
                    this._historyUndo();
                    
                    return false;
                } 
            }
        });
        
        $(this.iframeBody).on("keyup", "", (event) => {
            this._historySave();
        });
        
        $(this.iframeBody).contextmenu((event) => {
            let type = "";
            let target = null;
            let content = "";
            
            if ($(event.target).hasClass("mdc-button") === true) {
                type = "button";
                target = $(event.target);
                
                if ($(event.target).is("button") === true) {
                    let label = $(event.target).text();
                    
                    content = `<div class="mdc-text-field mdc-text-field__basic mdc-text-field--dense" style="width: 100%;">
                        <input class="mdc-text-field__input label" type="text" value="${label}" autocomplete="off" aria-label="label"/>
                        <label class="mdc-floating-label">${window.textWysiwyg.label_10}</label>
                        <div class="mdc-line-ripple"></div>
                    </div>
                    <p class="mdc-text-field-helper-text" aria-hidden="true"></p>`;
                }
                else if ($(event.target).is("a") === true) {
                    let label = $(event.target).text();
                    let link = $(event.target).prop("href") === undefined ? "" : $(event.target).prop("href");
                    
                    content = `<div class="mdc-text-field mdc-text-field__basic mdc-text-field--dense" style="width: 100%;">
                        <input class="mdc-text-field__input label" type="text" value="${label}" autocomplete="off" aria-label="label"/>
                        <label class="mdc-floating-label">{window.textWysiwyg.label_10}</label>
                        <div class="mdc-line-ripple"></div>
                    </div>
                    <p class="mdc-text-field-helper-text" aria-hidden="true"></p>
                    <div class="mdc-text-field mdc-text-field__basic mdc-text-field--dense" style="width: 100%;">
                        <input class="mdc-text-field__input link" type="text" value="${link}" autocomplete="off" aria-label="label"/>
                        <label class="mdc-floating-label">${window.textWysiwyg.label_11}</label>
                        <div class="mdc-line-ripple"></div>
                    </div>
                    <p class="mdc-text-field-helper-text" aria-hidden="true"></p>`;
                }
            }
            else if ($(event.target).hasClass("mdc-layout-grid__cell") === true || $(event.target).parents(".mdc-layout-grid__cell").length > 0) {
                target = $(event.target);
                
                if ($(event.target).parents(".mdc-layout-grid__cell").length > 0)
                    target = $(event.target).parents(".mdc-layout-grid__cell");
                
                type = "table";
                
                content = `<fieldset>
                    <legend>${window.textWysiwyg.label_16}</legend>
                    <button id="row_add" class="mdc-button mdc-button--dense mdc-button--raised" type="button">${window.textWysiwyg.label_17}</button>
                    <button id="row_remove" class="mdc-button mdc-button--dense mdc-button--raised" type="button">${window.textWysiwyg.label_18}</button>
                </fieldset>
                <fieldset>
                    <legend>${window.textWysiwyg.label_19}</legend>
                    <button id="column_add" class="mdc-button mdc-button--dense mdc-button--raised" type="button">${window.textWysiwyg.label_20}</button>
                    <button id="column_remove" class="mdc-button mdc-button--dense mdc-button--raised" type="button">${window.textWysiwyg.label_21}</button>
                </fieldset>`;
            }
            
            if (content !== "")
                this._popupsetting(type, target, content);
            
            return false;
        });
    }
    
    _popupsetting = (type, target, content) => {
        popupEasy.show(
            window.textWysiwyg.label_15,
            `<div id=\"wysiwyg_popup\">${content}</div>`,
            () => {
                if (type === "button") {
                    let label = $("#wysiwyg_popup").find(".mdc-text-field__input.label").val();
                    let link = $("#wysiwyg_popup").find(".mdc-text-field__input.link").val();
                    
                    target.text(label);
                    target.prop("href", link);
                }
                
                this._historySave();
            }
        );
        
        if (type === "table") {
            $("#row_add").on("click", "", (event) => {
                let columnNumber = target.parent().find(".mdc-layout-grid__cell").length;
                
                let html = "<div class=\"mdc-layout-grid__inner\" contenteditable=\"false\">";
                    for (let a = 0; a < columnNumber; a ++) {
                        html += "<div class=\"mdc-layout-grid__cell mdc-layout-grid__cell--span-2\" contenteditable=\"false\">&nbsp;</div>";
                    }
                html += "</div>";
                
                target.parent().after(html);
                
                popupEasy.close();
                
                this._historySave();
            });
            
            $("#row_remove").on("click", "", (event) => {
                target.parent().remove();
                
                popupEasy.close();
                
                this._historySave();
            });
            
            $("#column_add").on("click", "", (event) => {
                let columnIndex = target.index();
                
                let html = "<div class=\"mdc-layout-grid__cell mdc-layout-grid__cell--span-2\" contenteditable=\"false\">&nbsp;</div>";
                
                $.each(target.parents(".mdc-layout-grid").find(".mdc-layout-grid__inner"), (key, value) => {
                    $(value).find(".mdc-layout-grid__cell").eq(columnIndex).after(html);
                });
                
                popupEasy.close();
                
                this._historySave();
            });
            
            $("#column_remove").on("click", "", (event) => {
                let columnIndex = target.index();
                
                $.each(target.parents(".mdc-layout-grid").find(".mdc-layout-grid__inner"), (key, value) => {
                    if ($(value).find(".mdc-layout-grid__cell").length === 1) {
                        target.parents(".mdc-layout-grid").remove();
                        
                        return false;
                    }
                    else
                        $(value).find(".mdc-layout-grid__cell").eq(columnIndex).remove();
                });
                
                popupEasy.close();
                
                this._historySave();
            });
        }
    }
    
    _findElementAtCaretPosition = () => {
        let iframeDocument = window.frames[0].document;
        let selection = null;
        let containerNode = null;
        
        if (iframeDocument.getSelection()) {
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
    
    _addHtmlAtCaretPosition = (html) => {
        let iframeDocument = window.frames[0].document;
        let range = null;
        
        if (iframeDocument.getSelection()) {
            selection = iframeDocument.getSelection();
            
            if (selection.getRangeAt() && selection.rangeCount()) {
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
    
    _spaceAfterElement = () => {
        let elements = $(this.iframeContent).find(".mdc-button, .mdc-layout-grid");
        
        $.each(elements, (key, value) => {
            if ($(value).prev("br").length === 0)
                $(value).before("<br>");
            
            if ($(value).next("br").length === 0)
                $(value).after("<br>");
        });
    }
    
    _removeDoubleSpace = () => {
        let elements = $(this.iframeContent).find("br");
        
        $.each(elements, (key, value) => {
            if ($(value).prev("br").length === 1)
                $(value).prev("br").remove();
            
            if ($(value).next("br").length === 1)
                $(value).next("br").remove();
        });
    }
}