"use strict";

/* global ajax, popupEasy, wysiwyg, controlPanelPage */

class Language {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        $(document).on("click", "#language_text_container .mdc-list-item", (event) => {
            ajax.send(
                true,
                window.url.languageText,
                "post",
                {
                    'event': "languageText",
                    'languageTextCode': $(event.currentTarget).find("img").prop("class"),
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                (xhr) => {
                    ajax.reply(xhr, "");
                    
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined)
                        window.location.href = xhr.response.values.url;
                },
                null,
                null
            );
        });
    }
    
    page = () => {
        this.selectOnPage();
        
        $("#form_language_page").on("submit", "", (event) => {
            event.preventDefault();
            
            ajax.send(
                true,
                $(event.target).prop("action"),
                $(event.target).prop("method"),
                $(event.target).serialize(),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                (xhr) => {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined) {
                        wysiwyg.historyClear();
                        
                        $("#form_cp_page_profile").find("input[name='form_page[language]']").val(xhr.response.values.codePage);
                        $("#form_cp_page_profile").find("input[name='form_page[title]']").val(xhr.response.values.pageTitle);
                        $(".wysiwyg").find(".editor").contents().find("body").html(xhr.response.values.pageArgument);
                        $("#form_cp_page_profile").find("input[name='form_page[menuName]']").val(xhr.response.values.pageMenuName);
                    }   
                },
                null,
                null
            );
        });
    }
    
    // Functions private
    selectOnPage = () => {
        $("#language_page_container").find(".flag_" + window.session.languageTextCode).parent().addClass("mdc-chip--selected");
        $("#language_page_container").find("input[name='form_language[codePage]']").val(window.session.languageTextCode);
        
        $("#language_page_container .mdc-chip").on("click", "", (event) => {
            if (controlPanelPage.getProfileFocus === true) {
                popupEasy.create(
                    window.text.index_1,
                    window.textLanguagePage.label_1,
                    () => {
                        this.formPageFlagSubmit(event);
                    }
                );
            }
            else
                this.formPageFlagSubmit(event);
        });
    }
    
    formPageFlagSubmit = (event) => {
        controlPanelPage.setProfileFocus = false;
        
        let target = $(event.target).parent().hasClass("mdc-chip") === true ? $(event.target).parent() : $(event.target);
        
        $("#language_page_container").children().removeClass("mdc-chip--selected");
        target.addClass("mdc-chip--selected");
        
        let altSplit = target.find("img").prop("alt").split(".");

        $("#form_language_page").find("input[name='form_language[codePage]']").val(altSplit[0]);
        $("#form_language_page").submit();
    }
}