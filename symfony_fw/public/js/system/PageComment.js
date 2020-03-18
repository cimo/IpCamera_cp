"use strict";

/* global helper, ajax */

class PageComment {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        let tableAndPagination = new TableAndPagination();
        tableAndPagination.create(window.url.pageCommentRender, "#pageComment_result", false);
        tableAndPagination.search();
        tableAndPagination.pagination();
        
        $("#form_pageComment").find(".button_reset").hide();
        
        $("#form_pageComment").val("new");
        
        $("#form_pageComment").on("submit", "", (event) => {
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
                    ajax.reply(xhr, `#${event.target.id}`);
                },
                null,
                null
            );
        });
        
        $("#form_pageComment").find(".button_reset").on("click", "", (event) => {
            $(event.target).hide();
            
            $("#form_pageComment_type").val("new");
            
            $("#form_pageComment").find(".mdc-text-field__input").next().removeClass("mdc-floating-label--float-above mdc-text-field--invalid");
            $("#form_pageComment").find(".mdc-text-field__input").val("");
        });
        
        $(document).on("click", "#pageComment_result .button_reply", (event) => {
            $("#form_pageComment").find(".button_reset").show();
            
            let id = $(event.currentTarget).parent().attr("data-comment");
            
            $("#form_pageComment_type").val("reply_" + id);
            
            $("#form_pageComment").find(".mdc-text-field__input").next().addClass("mdc-floating-label--float-above");
        });
        
        $(document).on("click", "#pageComment_result .button_edit", (event) => {
            $("#form_pageComment").find(".button_reset").show();
            
            let id = $(event.currentTarget).parent().attr("data-comment");
            let argument = $(event.currentTarget).parent().find(".argument").text().trim();
            
            $("#form_pageComment_type").val("edit_" + id);
            
            $("#form_pageComment").find(".mdc-text-field__input").next().addClass("mdc-floating-label--float-above");
            $("#form_pageComment").find(".mdc-text-field__input").val(argument);
        });
    }
    
    // Function private
}